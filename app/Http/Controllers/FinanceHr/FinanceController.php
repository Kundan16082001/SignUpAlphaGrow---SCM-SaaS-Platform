<?php
namespace App\Http\Controllers\FinanceHr;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $query = Invoice::with('customer')->where('tenant_id', $tenantId);

        if ($request->filled('type')) $query->where('type', $request->type);
        if ($request->filled('status')) $query->where('status', $request->status);

        $invoices = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'total_revenue' => Invoice::where('tenant_id', $tenantId)->where('status', 'paid')->sum('total'),
            'pending_payment' => Invoice::where('tenant_id', $tenantId)->whereIn('status', ['sent', 'overdue'])->sum('total'),
            'overdue_count' => Invoice::where('tenant_id', $tenantId)->overdue()->count(),
            'total_invoices' => Invoice::where('tenant_id', $tenantId)->count(),
        ];

        return view('finance-hr.finance.index', compact('invoices', 'stats'));
    }
}