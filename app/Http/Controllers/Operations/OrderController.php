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
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $query = Order::with(['customer', 'items.product'])->where('tenant_id', $tenantId);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();
        $customers = Customer::where('tenant_id', $tenantId)->where('status', 'active')->get();
        $products = Product::where('tenant_id', $tenantId)->where('status', 'active')->get();

        return view('operations.orders.index', compact('orders', 'customers', 'products'));
    }

    public function store(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $isInbound = $request->type === 'inbound';

        // Dynamic Validation Rules
        $rules = [
            'type' => 'required|in:inbound,outbound',
            'order_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
        ];

        if ($request->customer_id === 'new') {
            $rules['new_customer_name'] = 'required|string|max:255';
            $rules['new_customer_phone'] = 'nullable|string|max:20';
            $rules['new_customer_gstin'] = 'nullable|string|max:20';
        } else {
            $rules['customer_id'] = 'required|exists:customers,id';
        }

        foreach ($request->items as $index => $item) {
            if (isset($item['product_id']) && $item['product_id'] === 'new') {
                $rules["items.$index.new_product_name"] = 'required|string|max:255';
                $rules["items.$index.new_product_sku"] = [
                    'required', 'string', 'max:50',
                    Rule::unique('products', 'sku')->where('tenant_id', $tenantId),
                ];
            } else {
                $rules["items.$index.product_id"] = 'required|exists:products,id';
            }
            $rules["items.$index.qty"] = 'required|integer|min:1';
            $rules["items.$index.unit_price"] = 'required|numeric|min:0';
            $rules["items.$index.gst_rate"] = 'required|numeric|min:0|max:100';
        }

        $request->validate($rules);

        DB::beginTransaction();
        try {
            // 1. Handle Customer / Supplier
            $customerId = $request->customer_id;
            $customerName = 'Walk-in';
            
            if ($customerId === 'new') {
                $customer = Customer::create([
                    'tenant_id' => $tenantId,
                    'name' => $request->new_customer_name,
                    'phone' => $request->new_customer_phone,
                    'gstin' => $request->new_customer_gstin,
                    'type' => $isInbound ? 'supplier' : 'customer',
                    'status' => 'active',
                ]);
                $customerId = $customer->id;
                $customerName = $customer->name;
            } else {
                $customer = Customer::find($customerId);
                $customerName = $customer->name;
            }

            // 2. Create Order
            $orderNo = 'ORD-' . strtoupper(substr($request->type, 0, 1)) . '-' . now()->format('Ymd') . '-' . rand(1000, 9999);
            $order = Order::create([
                'tenant_id' => $tenantId,
                'customer_id' => $customerId,
                'order_no' => $orderNo,
                'type' => $request->type,
                'order_date' => $request->order_date,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'notes' => $request->notes,
            ]);

            $subtotal = 0;
            $tax = 0;

            // 3. Handle Items (and create new products if needed)
            foreach ($request->items as $item) {
                $productId = $item['product_id'];
                
                if ($productId === 'new') {
                    $newProduct = Product::create([
                        'tenant_id' => $tenantId,
                        'sku' => $item['new_product_sku'],
                        'name' => $item['new_product_name'],
                        'price' => $item['unit_price'],
                        'cost_price' => $isInbound ? $item['unit_price'] : 0,
                        'gst_rate' => $item['gst_rate'],
                        'unit' => 'PCS',
                        'stock_qty' => 0,
                        'reorder_level' => 0,
                        'status' => 'active',
                    ]);
                    $productId = $newProduct->id;
                }

                $product = Product::find($productId);
                $lineTotal = $item['qty'] * $item['unit_price'];
                $lineTax = $lineTotal * ($item['gst_rate'] / 100);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'sku' => $product->sku,
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['gst_rate'],
                    'tax_amount' => $lineTax,
                    'total' => $lineTotal + $lineTax,
                ]);

                $subtotal += $lineTotal;
                $tax += $lineTax;
            }

            $finalTotal = $subtotal + $tax;
            $order->update(['subtotal' => $subtotal, 'tax' => $tax, 'total' => $finalTotal]);

            // 4. Trigger Notification
            \App\Services\NotificationService::newOrder($orderNo, $customerName, (float) $finalTotal);

            DB::commit();
            return redirect()->route('operations.orders.index')->with('success', 'Order created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create order: ' . $e->getMessage()])->withInput();
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
        $order->load('customer', 'items');
        return view('operations.orders.invoice', compact('order'));
    }
}