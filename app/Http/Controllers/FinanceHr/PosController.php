<?php

namespace App\Http\Controllers\FinanceHr;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PosTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class PosController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;
        
        // Today's stats
        $today = PosTransaction::where('tenant_id', $tenantId)
            ->where('user_id', Auth::id())
            ->whereDate('created_at', today())
            ->where('status', 'completed');

        $stats = [
            'today_sales' => $today->sum('total'),
            'today_transactions' => $today->count(),
            'today_items' => $today->get()->sum(function($t) { 
                return is_array($t->items) ? count($t->items) : 0; 
            }),
            'avg_transaction' => $today->count() > 0 ? $today->sum('total') / $today->count() : 0,
        ];

        // Recent transactions
        $recentTransactions = PosTransaction::where('tenant_id', $tenantId)
            ->where('user_id', Auth::id())
            ->latest()
            ->take(5)
            ->get();

        // Low stock alerts
        $lowStockProducts = Product::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereColumn('stock_qty', '<=', 'reorder_level')
            ->take(5)
            ->get();

        // ✅ NEW: Fetch active products with stock for the clickable grid
        $products = Product::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('stock_qty', '>', 0)
            ->orderBy('name')
            ->get();

        return view('finance-hr.pos.index', compact('stats', 'recentTransactions', 'lowStockProducts', 'products'));
    }

    // AJAX: Search product by SKU/barcode or name
    public function searchProduct(Request $request)
    {
        try {
            $request->validate(['query' => 'required|string|max:100']);
            $tenantId = Auth::user()->tenant_id;
            $query = $request->input('query');

            $product = Product::where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->where(function($q) use ($query) {
                    $q->where('sku', $query)
                      ->orWhere('name', 'like', "%{$query}%")
                      ->orWhere('sku', 'like', "%{$query}%");
                })
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false, 
                    'message' => "Product '{$query}' not found. Check SKU or name."
                ]);
            }

            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'gst_rate' => (float) $product->gst_rate,
                    'stock_qty' => $product->stock_qty,
                    'unit' => $product->unit,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('POS Search Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error during search.'
            ], 500);
        }
    }

    // Process POS Transaction
    public function processTransaction(Request $request)
    {
        try {
            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.qty' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.gst_rate' => 'required|numeric|min:0',
                'payment_method' => 'required|in:cash,upi,card,net_banking',
                'amount_received' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
            ]);

            $tenantId = Auth::user()->tenant_id;

            DB::beginTransaction();
            try {
                $subtotal = 0;
                $totalTax = 0;
                $cartItems = [];

                foreach ($request->items as $item) {
                    $product = Product::find($item['product_id']);
                    
                    if (!$product) {
                        throw new \Exception("Product ID {$item['product_id']} not found");
                    }

                    if ($product->tenant_id != $tenantId) {
                        throw new \Exception("Product {$product->name} does not belong to your tenant");
                    }

                    if ($product->stock_qty < $item['qty']) {
                        throw new \Exception("Insufficient stock for {$product->name}. Available: {$product->stock_qty}, Requested: {$item['qty']}");
                    }

                    $lineSubtotal = $item['qty'] * $item['price'];
                    $lineTax = $lineSubtotal * ($item['gst_rate'] / 100);
                    $lineTotal = $lineSubtotal + $lineTax;

                    $subtotal += $lineSubtotal;
                    $totalTax += $lineTax;

                    $cartItems[] = [
                        'product_id' => $product->id,
                        'sku' => $product->sku,
                        'name' => $product->name,
                        'qty' => $item['qty'],
                        'price' => $item['price'],
                        'gst_rate' => $item['gst_rate'],
                        'tax' => $lineTax,
                        'total' => $lineTotal,
                    ];

                    // Deduct stock
                    $product->decrement('stock_qty', $item['qty']);
                    // Check if stock is now low
                    $product->refresh();
                    if ($product->stock_qty <= $product->reorder_level && $product->stock_qty > 0) {
                        \App\Services\NotificationService::lowStock($product->name, $product->stock_qty, $product->reorder_level);
                    }
                }

                $discount = $request->discount ?? 0;
                $grandTotal = ($subtotal + $totalTax) - $discount;
                $amountReceived = $request->amount_received ?? $grandTotal;
                $change = max(0, $amountReceived - $grandTotal);

                // Generate receipt number
                $receiptNo = 'RCPT-' . now()->format('YmdHis') . '-' . rand(100, 999);

                // Create transaction
                PosTransaction::create([
                    'tenant_id' => $tenantId,
                    'receipt_no' => $receiptNo,
                    'user_id' => Auth::id(),
                    'subtotal' => $subtotal,
                    'tax' => $totalTax,
                    'total' => $grandTotal,
                    'payment_method' => $request->payment_method,
                    'status' => 'completed',
                    'items' => $cartItems,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'receipt_no' => $receiptNo,
                    'payment_method' => $request->payment_method,
                    'subtotal' => $subtotal,
                    'tax' => $totalTax,
                    'discount' => $discount,
                    'total' => $grandTotal,
                    'amount_received' => $amountReceived,
                    'change' => $change,
                    'items' => $cartItems,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('POS Transaction Error: ' . $e->getMessage(), [
                    'user_id' => Auth::id(),
                    'items' => $request->items,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->flatten()->first();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $errors
            ], 422);
        } catch (\Exception $e) {
            Log::error('POS Critical Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

        // Print Receipt
        // Print Receipt
       // Print Receipt
    public function printReceipt($receipt_no)
    {
        $tenantId = Auth::user()->tenant_id;
        
        // ✅ Search by the string receipt number, not the numeric ID
        $transaction = PosTransaction::where('tenant_id', $tenantId)
            ->where('receipt_no', $receipt_no)
            ->firstOrFail();
            
        $transaction->load('user');
        
        return view('finance-hr.pos.receipt', compact('transaction'));
    }

    // Today's Sales Summary
    public function todaySales()
    {
        $tenantId = Auth::user()->tenant_id;
        
        $transactions = PosTransaction::where('tenant_id', $tenantId)
            ->where('user_id', Auth::id())
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->latest()
            ->get();

        $summary = [
            'total_sales' => $transactions->sum('total'),
            'total_transactions' => $transactions->count(),
            'cash_sales' => $transactions->where('payment_method', 'cash')->sum('total'),
            'upi_sales' => $transactions->where('payment_method', 'upi')->sum('total'),
            'card_sales' => $transactions->where('payment_method', 'card')->sum('total'),
            'net_banking_sales' => $transactions->where('payment_method', 'net_banking')->sum('total'),
        ];

        return view('finance-hr.pos.today-sales', compact('transactions', 'summary'));
    }

    /**
     * Download POS Receipt as PDF
     */
    public function downloadReceiptPdf($receipt_no)
    {
        $tenantId = Auth::user()->tenant_id;
        
        $transaction = PosTransaction::where('tenant_id', $tenantId)
            ->where('receipt_no', $receipt_no)
            ->firstOrFail();
            
        $transaction->load('user');
        
        $pdf = Pdf::loadView('finance-hr.pos.pdf', compact('transaction'))
            // ✅ FIX: Use A4 paper size (210mm x 297mm) to guarantee no cutoff
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        return $pdf->download('Receipt-' . $receipt_no . '.pdf');
    }
}