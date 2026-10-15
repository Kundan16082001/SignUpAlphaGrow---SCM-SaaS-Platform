<?php

namespace App\Http\Controllers\FinanceHr;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $status = $request->get('status', 'all');
        $categoryId = $request->get('category');

        $query = Expense::with('category')->where('tenant_id', $tenantId);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $expenses = $query->latest()->paginate(10)->withQueryString();
        $categories = ExpenseCategory::where('tenant_id', $tenantId)->active()->get();

        // Stats
        $stats = [
            'total_amount' => Expense::where('tenant_id', $tenantId)->sum('total'),
            'pending_amount' => Expense::where('tenant_id', $tenantId)->where('status', 'pending')->sum('total'),
            'approved_amount' => Expense::where('tenant_id', $tenantId)->where('status', 'approved')->sum('total'),
            'paid_amount' => Expense::where('tenant_id', $tenantId)->where('status', 'paid')->sum('total'),
            'total_expenses' => Expense::where('tenant_id', $tenantId)->count(),
            'pending_count' => Expense::where('tenant_id', $tenantId)->where('status', 'pending')->count(),
            'total_gst' => Expense::where('tenant_id', $tenantId)->sum('gst'),
            'total_tds' => Expense::where('tenant_id', $tenantId)->sum('tds'),
        ];

        // Category-wise summary
        $categorySummary = Expense::with('category')
            ->where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->selectRaw('category_id, SUM(total) as total_amount, COUNT(*) as count')
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) use ($categories) {
                $cat = $categories->firstWhere('id', $item->category_id);
                return [
                    'name' => $cat->name ?? 'Uncategorized',
                    'icon' => $cat->icon ?? 'bi-receipt',
                    'color' => $cat->color ?? 'secondary',
                    'total' => $item->total_amount,
                    'count' => $item->count,
                ];
            });

        return view('finance-hr.expenses.index', compact('expenses', 'categories', 'stats', 'categorySummary', 'status', 'categoryId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'vendor' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'gst' => 'required|numeric|min:0',
            'tds' => 'required|numeric|min:0',
            'payment_mode' => 'required|in:cash,bank,upi,cheque,card',
            'description' => 'nullable|string|max:500',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $expenseNo = 'EXP-' . now()->format('Ymd') . '-' . rand(1000, 9999);

        $amount = $request->amount;
        $gst = $request->gst;
        $tds = $request->tds;
        $total = $amount + $gst - $tds;

        Expense::create([
            'tenant_id' => $tenantId,
            'expense_no' => $expenseNo,
            'category_id' => $request->category_id,
            'vendor' => $request->vendor,
            'amount' => $amount,
            'gst' => $gst,
            'tds' => $tds,
            'total' => $total,
            'payment_mode' => $request->payment_mode,
            'status' => 'pending',
            'description' => $request->description,
        ]);

        return redirect()->route('finance-hr.expenses.index')->with('success', 'Expense submitted for approval!');
    }

    public function updateStatus(Request $request, Expense $expense)
    {
        if ($expense->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate(['status' => 'required|in:pending,approved,paid,rejected']);
        $expense->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Expense status updated!');
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        ExpenseCategory::create([
            'tenant_id' => Auth::user()->tenant_id,
            'name' => $request->name,
            'icon' => $request->icon ?? 'bi-receipt',
            'color' => $request->color ?? 'primary',
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', 'Category added!');
    }
}