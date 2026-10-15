@extends('layouts.master')
@section('title', 'HR & Payroll')
@section('page-title', 'HR & Payroll Management')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75">Total Employees</p>
                        <h3 class="fw-bold mb-0">{{ $stats['total_employees'] }}</h3>
                        <small class="opacity-75">{{ $stats['total_departments'] }} departments</small>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75">Monthly Payroll</p>
                        <h3 class="fw-bold mb-0">₹{{ number_format($stats['monthly_payroll'], 0) }}</h3>
                        <small class="opacity-75">Gross salary</small>
                    </div>
                    <i class="bi bi-cash-stack fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75">Pending Leaves</p>
                        <h3 class="fw-bold mb-0">{{ $stats['pending_leaves'] }}</h3>
                        <small class="opacity-75">{{ $stats['on_leave_today'] }} on leave today</small>
                    </div>
                    <i class="bi bi-calendar-event fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75">Monthly PF</p>
                        <h3 class="fw-bold mb-0">₹{{ number_format($stats['total_pf_monthly'], 0) }}</h3>
                        <small class="opacity-75">Employee contribution</small>
                    </div>
                    <i class="bi bi-piggy-bank fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <ul class="nav nav-pills mb-4" id="hrTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'employees' ? 'active' : '' }}" href="{{ route('finance-hr.hr.index', ['tab' => 'employees']) }}">
                <i class="bi bi-people me-1"></i> Employees
                <span class="badge bg-light text-dark ms-1">{{ $stats['total_employees'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'payroll' ? 'active' : '' }}" href="{{ route('finance-hr.hr.index', ['tab' => 'payroll']) }}">
                <i class="bi bi-cash-stack me-1"></i> Payroll
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'leaves' ? 'active' : '' }}" href="{{ route('finance-hr.hr.index', ['tab' => 'leaves']) }}">
                <i class="bi bi-calendar-event me-1"></i> Leaves
                @if($stats['pending_leaves'] > 0)
                    <span class="badge bg-warning text-dark ms-1">{{ $stats['pending_leaves'] }}</span>
                @endif
            </a>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content">

        {{-- ==================== EMPLOYEES TAB ==================== --}}
        @if($tab == 'employees')
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h6 class="fw-bold mb-0"><i class="bi bi-people me-2"></i>Employee Directory</h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                        <i class="bi bi-building me-1"></i> Add Department
                    </button>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                        <i class="bi bi-person-plus me-1"></i> Add Employee
                    </button>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Emp Code</th>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>DOJ</th>
                                    <th>Experience</th>
                                    <th class="text-end">Gross Salary</th>
                                    <th>UAN</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $emp)
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">{{ $emp->emp_code }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:36px; height:36px;">
                                                    {{ strtoupper(substr($emp->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $emp->name }}</div>
                                                    @if($emp->esi_no)
                                                        <small class="text-muted">ESI: {{ $emp->esi_no }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($emp->department)
                                                <span class="badge bg-light text-dark border">{{ $emp->department->name }}</span>
                                            @else
                                                <span class="text-muted small">N/A</span>
                                            @endif
                                        </td>
                                        <td class="small">{{ $emp->designation }}</td>
                                        <td class="text-muted small">{{ $emp->doj->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info">{{ $emp->years_of_service }} yrs</span>
                                        </td>
                                        <td class="text-end fw-bold">₹{{ number_format($emp->gross_salary, 0) }}</td>
                                        <td><code class="small">{{ $emp->uan ?? 'N/A' }}</code></td>
                                        <td><span class="badge bg-success bg-opacity-10 text-success">Active</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center py-5 text-muted"><i class="bi bi-people fs-1 d-block mb-2"></i>No employees found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($employees->hasPages())
                    <div class="card-footer bg-white border-top-0 pt-3">{{ $employees->links() }}</div>
                @endif
            </div>
        @endif

        {{-- ==================== PAYROLL TAB ==================== --}}
        @if($tab == 'payroll')
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h6 class="fw-bold mb-0"><i class="bi bi-cash-stack me-2"></i>Payroll History</h6>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#processPayrollModal">
                    <i class="bi bi-calculator me-1"></i> Process New Payroll
                </button>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Payroll #</th>
                                    <th>Month</th>
                                    <th class="text-center">Employees</th>
                                    <th class="text-end">Gross</th>
                                    <th class="text-end">PF</th>
                                    <th class="text-end">ESI</th>
                                    <th class="text-end">TDS</th>
                                    <th class="text-end">Net Pay</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payrolls as $payroll)
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">{{ $payroll->payroll_no }}</td>
                                        <td class="fw-semibold">{{ $payroll->month_formatted }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary bg-opacity-10 text-primary">{{ $payroll->employee_count }}</span>
                                        </td>
                                        <td class="text-end">₹{{ number_format($payroll->total_gross, 0) }}</td>
                                        <td class="text-end text-muted">₹{{ number_format($payroll->total_pf, 0) }}</td>
                                        <td class="text-end text-muted">₹{{ number_format($payroll->total_esi, 0) }}</td>
                                        <td class="text-end text-muted">₹{{ number_format($payroll->total_tds, 0) }}</td>
                                        <td class="text-end fw-bold text-success">₹{{ number_format($payroll->total_net, 0) }}</td>
                                        <td><span class="badge bg-{{ $payroll->status_color }}">{{ ucfirst($payroll->status) }}</span></td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewPayrollModal{{ $payroll->id }}">
                                                <i class="bi bi-eye me-1"></i> View
                                            </button>
                                        </td>
                                    </tr>

                                    {{-- View Payroll Modal --}}
                                    <div class="modal fade" id="viewPayrollModal{{ $payroll->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Payroll Details - {{ $payroll->month_formatted }}</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="row g-3 mb-4">
                                                        <div class="col-md-3">
                                                            <div class="p-3 bg-light rounded-3 text-center">
                                                                <small class="text-muted d-block">Gross</small>
                                                                <h5 class="fw-bold mb-0">₹{{ number_format($payroll->total_gross, 0) }}</h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="p-3 bg-light rounded-3 text-center">
                                                                <small class="text-muted d-block">PF</small>
                                                                <h5 class="fw-bold mb-0 text-primary">₹{{ number_format($payroll->total_pf, 0) }}</h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="p-3 bg-light rounded-3 text-center">
                                                                <small class="text-muted d-block">ESI</small>
                                                                <h5 class="fw-bold mb-0 text-info">₹{{ number_format($payroll->total_esi, 0) }}</h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="p-3 bg-light rounded-3 text-center">
                                                                <small class="text-muted d-block">Net Pay</small>
                                                                <h5 class="fw-bold mb-0 text-success">₹{{ number_format($payroll->total_net, 0) }}</h5>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <table class="table table-bordered align-middle">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Employee</th>
                                                                <th class="text-end">Basic</th>
                                                                <th class="text-end">HRA</th>
                                                                <th class="text-end">Gross</th>
                                                                <th class="text-end">PF</th>
                                                                <th class="text-end">ESI</th>
                                                                <th class="text-end">TDS</th>
                                                                <th class="text-end">Net Pay</th>
                                                                <th class="text-center">Slip</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($payroll->details as $detail)
                                                                <tr>
                                                                    <td>
                                                                        <div class="fw-semibold small">{{ $detail->employee->name }}</div>
                                                                        <small class="text-muted">{{ $detail->employee->emp_code }}</small>
                                                                    </td>
                                                                    <td class="text-end">₹{{ number_format($detail->basic, 0) }}</td>
                                                                    <td class="text-end">₹{{ number_format($detail->hra, 0) }}</td>
                                                                    <td class="text-end fw-bold">₹{{ number_format($detail->gross, 0) }}</td>
                                                                    <td class="text-end text-muted">₹{{ number_format($detail->pf, 0) }}</td>
                                                                    <td class="text-end text-muted">₹{{ number_format($detail->esi, 0) }}</td>
                                                                    <td class="text-end text-muted">₹{{ number_format($detail->tds, 0) }}</td>
                                                                    <td class="text-end fw-bold text-success">₹{{ number_format($detail->net_pay, 0) }}</td>
                                                                    <td class="text-center">
                                                                        <a href="{{ route('finance-hr.hr.payroll.slip', [$payroll, $detail->employee]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                            <i class="bi bi-printer"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr><td colspan="10" class="text-center py-5 text-muted"><i class="bi bi-cash-stack fs-1 d-block mb-2"></i>No payroll records yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($payrolls->hasPages())
                    <div class="card-footer bg-white border-top-0 pt-3">{{ $payrolls->links() }}</div>
                @endif
            </div>
        @endif

        {{-- ==================== LEAVES TAB ==================== --}}
        @if($tab == 'leaves')
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h6 class="fw-bold mb-0"><i class="bi bi-calendar-event me-2"></i>Leave Applications</h6>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
                    <i class="bi bi-plus-lg me-1"></i> Apply Leave
                </button>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Leave #</th>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th class="text-center">Days</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaves as $leave)
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">{{ $leave->leave_no }}</td>
                                        <td>
                                            <div class="fw-semibold small">{{ $leave->employee->name }}</div>
                                            <small class="text-muted">{{ $leave->employee->emp_code }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $leave->type_color }} bg-opacity-10 text-{{ $leave->type_color }}">
                                                {{ ucfirst($leave->type) }}
                                            </span>
                                        </td>
                                        <td class="text-muted small">{{ $leave->from_date->format('d M Y') }}</td>
                                        <td class="text-muted small">{{ $leave->to_date->format('d M Y') }}</td>
                                        <td class="text-center fw-bold">{{ $leave->days }}</td>
                                        <td class="small">{{ Str::limit($leave->reason, 30) ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $leave->status_color }} bg-opacity-10 text-{{ $leave->status_color }}">
                                                {{ ucfirst($leave->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            @if($leave->status == 'pending')
                                                <form method="POST" action="{{ route('finance-hr.hr.leave.update-status', $leave) }}" class="d-inline">
                                                    @csrf @method('PATCH')
                                                    <select name="status" class="form-select form-select-sm" style="width:110px;" onchange="this.form.submit()">
                                                        <option value="pending">Pending</option>
                                                        <option value="approved">Approve</option>
                                                        <option value="rejected">Reject</option>
                                                    </select>
                                                </form>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center py-5 text-muted"><i class="bi bi-calendar-event fs-1 d-block mb-2"></i>No leave applications.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($leaves->hasPages())
                    <div class="card-footer bg-white border-top-0 pt-3">{{ $leaves->links() }}</div>
                @endif
            </div>
        @endif

    </div>

    {{-- ==================== MODALS ==================== --}}

    {{-- ADD EMPLOYEE MODAL --}}
    <div class="modal fade" id="addEmployeeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('finance-hr.hr.employee.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Add New Employee</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date of Joining *</label>
                            <input type="date" name="doj" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Department *</label>
                            <select name="department_id" class="form-select" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Designation *</label>
                            <input type="text" name="designation" class="form-control" placeholder="e.g., Software Engineer" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">UAN (Provident Fund)</label>
                            <input type="text" name="uan" class="form-control" placeholder="12-digit UAN">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ESI Number</label>
                            <input type="text" name="esi_no" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Basic Salary (₹) *</label>
                            <input type="number" name="basic" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">HRA (₹) *</label>
                            <input type="number" name="hra" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Employee</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ADD DEPARTMENT MODAL --}}
    <div class="modal fade" id="addDepartmentModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('finance-hr.hr.department.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title"><i class="bi bi-building me-2"></i>Add Department</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department Code *</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g., ENG, SAL, FIN" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary">Add Department</button>
                </div>
            </form>
        </div>
    </div>

    {{-- PROCESS PAYROLL MODAL --}}
    <div class="modal fade" id="processPayrollModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('finance-hr.hr.payroll.process') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-calculator me-2"></i>Process Monthly Payroll</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        This will auto-calculate PF (12% of Basic), ESI (1.75% if gross < ₹21,000), and TDS for all {{ $stats['total_employees'] }} active employees.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Month *</label>
                        <input type="month" name="month" class="form-control form-control-lg" value="{{ date('Y-m') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Process Payroll</button>
                </div>
            </form>
        </div>
    </div>

    {{-- APPLY LEAVE MODAL --}}
    <div class="modal fade" id="applyLeaveModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('finance-hr.hr.leave.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Apply for Leave</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Employee *</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->emp_code }} - {{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Leave Type *</label>
                        <select name="type" class="form-select" required>
                            <option value="casual">Casual Leave</option>
                            <option value="sick">Sick Leave</option>
                            <option value="privilege">Privilege Leave</option>
                            <option value="comp_off">Compensatory Off</option>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">From Date *</label>
                            <input type="date" name="from_date" class="form-control" min="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">To Date *</label>
                            <input type="date" name="to_date" class="form-control" min="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reason</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="Reason for leave..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Submit Application</button>
                </div>
            </form>
        </div>
    </div>
@endsection