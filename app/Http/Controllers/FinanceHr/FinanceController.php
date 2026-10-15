<?php

namespace App\Http\Controllers\FinanceHr;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PosTransaction;
use App\Models\Expense;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $tab = $request->get('tab', 'invoices');

        // 1. Invoices
        $invoices = Invoice::with('customer')->where('tenant_id', $tenantId)->latest()->paginate(10, ['*'], 'inv_page');
        
        // 2. POS Transactions
        $posTransactions = PosTransaction::with('user')->where('tenant_id', $tenantId)->latest()->paginate(10, ['*'], 'pos_page');
        
        // 3. Expenses
        $expenses = Expense::with('category')->where('tenant_id', $tenantId)->latest()->paginate(10, ['*'], 'exp_page');

        $customers = Customer::where('tenant_id', $tenantId)->where('status', 'active')->get();
        $products = Product::where('tenant_id', $tenantId)->where('status', 'active')->get();

        // Unified Stats
        $stats = [
            'total_revenue' => Invoice::where('tenant_id', $tenantId)->where('status', 'paid')->sum('total') + 
                              PosTransaction::where('tenant_id', $tenantId)->where('status', 'completed')->sum('total'),
            'total_expenses' => Expense::where('tenant_id', $tenantId)->where('status', 'paid')->sum('total'),
            'pending_invoices' => Invoice::where('tenant_id', $tenantId)->whereIn('status', ['sent', 'overdue'])->count(),
            'pending_amount' => Invoice::where('tenant_id', $tenantId)->whereIn('status', ['sent', 'overdue'])->sum('total'),
        ];

        return view('finance-hr.finance.index', compact(
            'invoices', 'posTransactions', 'expenses', 'stats', 'customers', 'products', 'tab'
        ));
    }

    public function storeInvoice(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|in:b2b,b2c,export',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.gst_rate' => 'required|numeric|min:0',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $invoiceNo = 'INV-' . now()->format('Ymd') . '-' . rand(1000, 9999);

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $totalCgst = 0;
            $totalSgst = 0;
            $totalIgst = 0;

            $invoice = Invoice::create([
                'tenant_id' => $tenantId,
                'invoice_no' => $invoiceNo,
                'customer_id' => $request->customer_id,
                'type' => $request->type,
                'status' => 'sent',
                'due_date' => $request->due_date,
            ]);

            foreach ($request->items as $item) {
                $taxableValue = $item['qty'] * $item['rate'];
                $taxAmount = $taxableValue * ($item['gst_rate'] / 100);
                
                // Simple logic: if export, IGST. Else, split CGST/SGST
                $cgst = $request->type === 'export' ? 0 : $taxAmount / 2;
                $sgst = $request->type === 'export' ? 0 : $taxAmount / 2;
                $igst = $request->type === 'export' ? $taxAmount : 0;

                $lineTotal = $taxableValue + $taxAmount;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'qty' => $item['qty'],
                    'rate' => $item['rate'],
                    'taxable_value' => $taxableValue,
                    'gst_rate' => $item['gst_rate'],
                    'cgst' => $cgst,
                    'sgst' => $sgst,
                    'igst' => $igst,
                    'total' => $lineTotal,
                ]);

                $subtotal += $taxableValue;
                $totalCgst += $cgst;
                $totalSgst += $sgst;
                $totalIgst += $igst;
            }

            $invoice->update([
                'cgst' => $totalCgst,
                'sgst' => $totalSgst,
                'igst' => $totalIgst,
                'total' => $subtotal + $totalCgst + $totalSgst + $totalIgst,
            ]);

            DB::commit();
            return redirect()->route('finance-hr.finance.index', ['tab' => 'invoices'])->with('success', 'Invoice created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create invoice: ' . $e->getMessage()]);
        }
    }

    public function updateInvoice(Request $request, Invoice $invoice)
    {
        if ($invoice->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate([
            'type' => 'required|in:b2b,b2c,export',
            'due_date' => 'required|date',
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
        ]);

        $invoice->update([
            'type' => $request->type,
            'due_date' => $request->due_date,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Invoice updated successfully!');
    }

    public function destroyInvoice(Invoice $invoice)
    {
        if ($invoice->tenant_id !== Auth::user()->tenant_id) abort(403);
        $invoice->delete();
        return redirect()->back()->with('success', 'Invoice deleted successfully!');
    }
}