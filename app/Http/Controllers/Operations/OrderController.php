<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem; 
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $query = Order::with(['customer', 'items'])->where('tenant_id', $tenantId);

        // Filter by Type (Inbound/Outbound)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(10)->withQueryString();
        $customers = Customer::where('tenant_id', $tenantId)->where('status', 'active')->get();
        $products = Product::where('tenant_id', $tenantId)->where('status', 'active')->get();

        return view('operations.orders.index', compact('orders', 'customers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|in:inbound,outbound',
            'order_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $orderNo = 'ORD-' . strtoupper(substr($request->type, 0, 1)) . '-' . now()->format('Ymd') . '-' . rand(1000, 9999);

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $tax = 0;

            // Create Order
            $order = Order::create([
                'tenant_id' => $tenantId,
                'customer_id' => $request->customer_id,
                'order_no' => $orderNo,
                'type' => $request->type,
                'order_date' => $request->order_date,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            // Create Order Items
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $lineTotal = $item['qty'] * $item['unit_price'];
                $lineTax = $lineTotal * ($product->gst_rate / 100);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $product->gst_rate,
                    'tax_amount' => $lineTax,
                    'total' => $lineTotal + $lineTax,
                ]);

                $subtotal += $lineTotal;
                $tax += $lineTax;
            }

            // Update Order Totals
            $order->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $subtotal + $tax,
            ]);

            DB::commit();
            return redirect()->route('operations.orders.index')->with('success', 'Order created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create order: ' . $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        if ($order->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate(['status' => 'required|in:pending,processing,shipped,delivered,cancelled']);
        $order->update(['status' => $request->status]);

        return redirect()->route('operations.orders.index')->with('success', 'Order status updated!');
    }

    public function printInvoice(Order $order)
    {
        if ($order->tenant_id !== Auth::user()->tenant_id) abort(403);
        return view('operations.orders.invoice', compact('order'));
    }
}