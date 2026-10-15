<?php

namespace App\Http\Controllers\FinanceHr;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\Leave;
use App\Models\LeaveBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HrPayrollController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $tab = $request->get('tab', 'employees');

        // Employees
        $employees = Employee::with('department')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->latest()
            ->paginate(10, ['*'], 'emp_page');

        // Payrolls
        $payrolls = Payroll::where('tenant_id', $tenantId)
            ->latest()
            ->paginate(10, ['*'], 'payroll_page');

        // Leaves
        $leaves = Leave::with('employee')
            ->where('tenant_id', $tenantId)
            ->latest()
            ->paginate(10, ['*'], 'leave_page');

        // Stats
        $stats = [
            'total_employees' => Employee::where('tenant_id', $tenantId)->active()->count(),
            'total_departments' => Department::where('tenant_id', $tenantId)->active()->count(),
            'monthly_payroll' => Employee::where('tenant_id', $tenantId)->active()->sum('gross_salary'),
            'pending_leaves' => Leave::where('tenant_id', $tenantId)->where('status', 'pending')->count(),
            'on_leave_today' => Leave::where('tenant_id', $tenantId)
                ->where('status', 'approved')
                ->where('from_date', '<=', now())
                ->where('to_date', '>=', now())
                ->count(),
            'total_pf_monthly' => Employee::where('tenant_id', $tenantId)->active()->sum('basic') * 0.12,
        ];

        $departments = Department::where('tenant_id', $tenantId)->active()->get();

        return view('finance-hr.hr-payroll.index', compact(
            'employees', 'payrolls', 'leaves', 'stats', 'departments', 'tab'
        ));
    }

    public function storeEmployee(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'doj' => 'required|date',
            'department_id' => 'required|exists:departments,id',
            'designation' => 'required|string|max:100',
            'basic' => 'required|numeric|min:0',
            'hra' => 'required|numeric|min:0',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $empCount = Employee::where('tenant_id', $tenantId)->count() + 1;
        $empCode = 'EMP-' . str_pad($empCount, 3, '0', STR_PAD_LEFT);

        Employee::create([
            'tenant_id' => $tenantId,
            'emp_code' => $empCode,
            'name' => $request->name,
            'doj' => $request->doj,
            'department_id' => $request->department_id,
            'designation' => $request->designation,
            'uan' => $request->uan,
            'esi_no' => $request->esi_no,
            'basic' => $request->basic,
            'hra' => $request->hra,
            'gross_salary' => $request->basic + $request->hra,
            'status' => 'active',
        ]);

        // Create leave balance for current year
        $emp = Employee::where('emp_code', $empCode)->first();
        LeaveBalance::firstOrCreate(
            ['employee_id' => $emp->id, 'year' => now()->year],
            [
                'casual_earned' => 12, 'casual_used' => 0,
                'sick_earned' => 10, 'sick_used' => 0,
                'privilege_earned' => 15, 'privilege_used' => 0,
            ]
        );

        return redirect()->route('finance-hr.hr.index')->with('success', 'Employee added successfully!');
    }

    public function storeDepartment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:departments,code',
        ]);

        Department::create([
            'tenant_id' => Auth::user()->tenant_id,
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', 'Department added!');
    }

    public function processPayroll(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $month = $request->month;

        // Check if payroll already exists for this month
        $existing = Payroll::where('tenant_id', $tenantId)->where('month', $month)->first();
        if ($existing) {
            return back()->withErrors(['error' => 'Payroll for ' . $month . ' already processed.']);
        }

        $employees = Employee::where('tenant_id', $tenantId)->active()->get();

        DB::beginTransaction();
        try {
            $totalGross = 0;
            $totalPf = 0;
            $totalEsi = 0;
            $totalTds = 0;
            $totalNet = 0;

            // Create Payroll header
            $payrollNo = 'PAY-' . str_replace('-', '', $month) . '-' . rand(100, 999);
            $payroll = Payroll::create([
                'tenant_id' => $tenantId,
                'payroll_no' => $payrollNo,
                'month' => $month,
                'employee_count' => $employees->count(),
                'status' => 'processed',
            ]);

            foreach ($employees as $emp) {
                $basic = $emp->basic;
                $hra = $emp->hra;
                $gross = $basic + $hra;

                // PF: 12% of basic
                $pf = $basic * 0.12;

                // ESI: 1.75% of gross (only if gross < 21000)
                $esi = $gross < 21000 ? $gross * 0.0175 : 0;

                // TDS: Simplified - 10% of (gross - pf - esi) above 50000/month
                $taxableIncome = $gross - $pf - $esi;
                $tds = $taxableIncome > 50000 ? ($taxableIncome - 50000) * 0.10 : 0;

                $netPay = $gross - $pf - $esi - $tds;

                PayrollDetail::create([
                    'payroll_id' => $payroll->id,
                    'employee_id' => $emp->id,
                    'basic' => $basic,
                    'hra' => $hra,
                    'allowances' => 0,
                    'overtime' => 0,
                    'gross' => $gross,
                    'pf' => $pf,
                    'esi' => $esi,
                    'tds' => $tds,
                    'net_pay' => $netPay,
                    'working_days' => 26,
                    'lop_days' => 0,
                ]);

                $totalGross += $gross;
                $totalPf += $pf;
                $totalEsi += $esi;
                $totalTds += $tds;
                $totalNet += $netPay;
            }

            $payroll->update([
                'total_gross' => $totalGross,
                'total_deductions' => $totalPf + $totalEsi + $totalTds,
                'total_pf' => $totalPf,
                'total_esi' => $totalEsi,
                'total_tds' => $totalTds,
                'total_net' => $totalNet,
            ]);

            DB::commit();
            return redirect()->route('finance-hr.hr.index', ['tab' => 'payroll'])
                ->with('success', "Payroll for {$month} processed successfully! {$employees->count()} salaries calculated.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to process payroll: ' . $e->getMessage()]);
        }
    }

    public function showPayrollSlip(Payroll $payroll, Employee $employee)
    {
        if ($payroll->tenant_id !== Auth::user()->tenant_id) abort(403);
        $detail = PayrollDetail::where('payroll_id', $payroll->id)
            ->where('employee_id', $employee->id)
            ->firstOrFail();
        return view('finance-hr.hr-payroll.salary-slip', compact('payroll', 'employee', 'detail'));
    }

    public function applyLeave(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:casual,sick,privilege,comp_off',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'nullable|string|max:500',
        ]);

        $fromDate = \Carbon\Carbon::parse($request->from_date);
        $toDate = \Carbon\Carbon::parse($request->to_date);
        $days = $fromDate->diffInDays($toDate) + 1;

        $leaveNo = 'LV-' . now()->format('Ymd') . '-' . rand(100, 999);

        Leave::create([
            'tenant_id' => Auth::user()->tenant_id,
            'employee_id' => $request->employee_id,
            'leave_no' => $leaveNo,
            'type' => $request->type,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'days' => $days,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('finance-hr.hr.index', ['tab' => 'leaves'])
            ->with('success', 'Leave application submitted!');
    }

    public function updateLeaveStatus(Request $request, Leave $leave)
    {
        if ($leave->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate(['status' => 'required|in:pending,approved,rejected']);
        
        $oldStatus = $leave->status;
        $leave->update(['status' => $request->status]);

        // Update leave balance if approved
        if ($request->status === 'approved' && $oldStatus !== 'approved') {
            $balance = LeaveBalance::where('employee_id', $leave->employee_id)
                ->where('year', now()->year)
                ->first();
            
            if ($balance) {
                $typeMap = [
                    'casual' => 'casual_used',
                    'sick' => 'sick_used',
                    'privilege' => 'privilege_used',
                ];
                if (isset($typeMap[$leave->type])) {
                    $balance->increment($typeMap[$leave->type], $leave->days);
                }
            }
        } elseif ($oldStatus === 'approved' && $request->status === 'rejected') {
            // Reverse the balance deduction
            $balance = LeaveBalance::where('employee_id', $leave->employee_id)
                ->where('year', now()->year)
                ->first();
            
            if ($balance) {
                $typeMap = [
                    'casual' => 'casual_used',
                    'sick' => 'sick_used',
                    'privilege' => 'privilege_used',
                ];
                if (isset($typeMap[$leave->type])) {
                    $balance->decrement($typeMap[$leave->type], $leave->days);
                }
            }
        }

        return redirect()->back()->with('success', 'Leave status updated!');
    }
}