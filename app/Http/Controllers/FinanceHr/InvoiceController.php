<?php

namespace App\Http\Controllers\FinanceHr;

use App\Http\Controllers\Controller;
use App\Models\{Invoice, Customer, Product};
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * List all invoices with filters & stats
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $query = Invoice::with('customer')
            ->where('tenant_id', $tenantId);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $invoices = $query->latest()->paginate(15);

        // Stats
        $stats = [
            'total' => Invoice::where('tenant_id', $tenantId)->count(),
            'paid' => Invoice::where('tenant_id', $tenantId)->where('status', 'paid')->sum('total'),
            'pending' => Invoice::where('tenant_id', $tenantId)->whereIn('status', ['sent', 'draft'])->sum('total'),
            'overdue' => Invoice::where('tenant_id', $tenantId)->where('status', 'overdue')->count(),
        ];

        return view('finance-hr.invoices.index', compact('invoices', 'stats'));
    }

    /**
     * Show create invoice form
     */
    public function create()
    {
        $tenantId = Auth::user()->tenant_id;
        $customers = Customer::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        $products = Product::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('stock_qty', '>', 0)
            ->orderBy('name')
            ->get();

        return view('finance-hr.invoices.create', compact('customers', 'products'));
    }

    /**
     * Store a new invoice
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|in:b2b,b2c',
            'due_date' => 'nullable|date|after_or_equal:today',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.gst_rate' => 'required|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $tenantId = Auth::user()->tenant_id;

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = 0;
            $totalTax = 0;
            $invoiceItems = [];

            foreach ($request->items as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('tenant_id', $tenantId)
                    ->firstOrFail();

                $lineSubtotal = $item['qty'] * $item['price'];
                $lineTax = $lineSubtotal * ($item['gst_rate'] / 100);
                $lineTotal = $lineSubtotal + $lineTax;

                $subtotal += $lineSubtotal;
                $totalTax += $lineTax;

                $invoiceItems[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'gst_rate' => $item['gst_rate'],
                    'tax' => $lineTax,
                    'total' => $lineTotal,
                ];
            }

            $discount = $request->discount ?? 0;
            $grandTotal = $subtotal + $totalTax - $discount;

            // Determine CGST/SGST/IGST split (assuming intra-state for simplicity)
            $cgst = $totalTax / 2;
            $sgst = $totalTax / 2;
            $igst = 0;

            // Generate invoice number
            $invoiceNo = 'INV-' . now()->format('Ymd') . '-' . str_pad(
                Invoice::where('tenant_id', $tenantId)->count() + 1, 
                4, '0', STR_PAD_LEFT
            );

            // Create invoice
            $invoice = Invoice::create([
                'tenant_id' => $tenantId,
                'invoice_no' => $invoiceNo,
                'customer_id' => $request->customer_id,
                'type' => $request->type,
                'subtotal' => $subtotal,
                'tax' => $totalTax,
                'cgst' => $cgst,
                'sgst' => $sgst,
                'igst' => $igst,
                'discount' => $discount,
                'total' => $grandTotal,
                'status' => 'draft',
                'due_date' => $request->due_date ?? now()->addDays(30),
                'items' => $invoiceItems,
                'notes' => $request->notes,
            ]);

            DB::commit();

            return redirect()
                ->route('finance-hr.invoices.show', $invoice)
                ->with('success', "Invoice {$invoiceNo} created successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Failed to create invoice: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show invoice details
     */
    public function show(Invoice $invoice)
    {
        if ($invoice->tenant_id !== Auth::user()->tenant_id) abort(403);
        $invoice->load('customer');
        return view('finance-hr.invoices.show', compact('invoice'));
    }

    /**
     * Update invoice status (mark as paid, sent, etc.)
     */
    public function updateStatus(Request $request, Invoice $invoice)
    {
        if ($invoice->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate([
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
        ]);

        $invoice->update(['status' => $request->status]);

        // If marked as paid, send notification
        if ($request->status === 'paid') {
            NotificationService::paymentReceived($invoice->invoice_no, $invoice->total);
        }

        return back()->with('success', "Invoice status updated to " . ucfirst($request->status));
    }

    /**
     * Delete invoice
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->tenant_id !== Auth::user()->tenant_id) abort(403);

        if ($invoice->status === 'paid') {
            return back()->withErrors(['error' => 'Cannot delete a paid invoice. Mark as cancelled instead.']);
        }

        $invoice->delete();
        return back()->with('success', 'Invoice deleted successfully.');
    }

    /**
     * AJAX: Get product details for invoice form
     */
    public function getProduct(Product $product)
    {
        if ($product->tenant_id !== Auth::user()->tenant_id) {
            return response()->json(['success' => false], 403);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => (float) $product->price,
                'gst_rate' => (float) $product->gst_rate,
                'stock_qty' => $product->stock_qty,
                'unit' => $product->unit,
            ]
        ]);
    }

        /**
     * Download Invoice as PDF
     */
    public function downloadPdf(Invoice $invoice)
    {
        if ($invoice->tenant_id !== Auth::user()->tenant_id) abort(403);
        
        $invoice->load('customer');
        
        $pdf = Pdf::loadView('finance-hr.invoices.pdf', compact('invoice'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

        return $pdf->download('Invoice-' . $invoice->invoice_no . '.pdf');
    }
}