<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Batch;
use App\Models\Inventory;
use App\Models\Warehouse;
use App\Models\GrqrPosting;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        
        $query = Product::where('tenant_id', $tenantId)->where('status', 'active');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Stock status filter
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->whereColumn('stock_qty', '<=', 'reorder_level');
            } elseif ($request->stock_status === 'out') {
                $query->where('stock_qty', 0);
            } elseif ($request->stock_status === 'healthy') {
                $query->whereColumn('stock_qty', '>', 'reorder_level');
            }
        }

        // Warehouse filter
        $selectedWarehouse = null;
        if ($request->filled('warehouse_id') && $request->warehouse_id !== 'all') {
            $selectedWarehouse = Warehouse::where('id', $request->warehouse_id)
                ->where('tenant_id', $tenantId)
                ->first();
            
            if ($selectedWarehouse) {
                $productIds = Batch::where('warehouse_id', $selectedWarehouse->id)
                    ->where('current_qty', '>', 0)
                    ->pluck('product_id')
                    ->unique();
                
                $query->whereIn('id', $productIds);
            }
        }

        $products = $query->withCount('batches')->latest()->paginate(20);

        // Calculate warehouse-specific stock for each product
        $warehouses = Warehouse::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $products->getCollection()->transform(function($product) use ($tenantId, $selectedWarehouse) {
            if ($selectedWarehouse) {
                $product->warehouse_stock = Batch::where('product_id', $product->id)
                    ->where('warehouse_id', $selectedWarehouse->id)
                    ->sum('current_qty');
                $product->warehouses = collect([$selectedWarehouse]);
            } else {
                $product->warehouse_stock = Batch::where('product_id', $product->id)
                    ->where('tenant_id', $tenantId)
                    ->sum('current_qty');
                
                $product->warehouses = Warehouse::where('tenant_id', $tenantId)
                    ->whereHas('batches', function($q) use ($product) {
                        $q->where('product_id', $product->id)->where('current_qty', '>', 0);
                    })
                    ->withCount(['batches as stock' => function($q) use ($product) {
                        $q->where('product_id', $product->id)->select(DB::raw('SUM(current_qty)'));
                    }])
                    ->get();
            }
            return $product;
        });

        $categories = Product::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        $stats = [
            'total_products' => Product::where('tenant_id', $tenantId)->where('status', 'active')->count(),
            'total_stock_value' => Product::where('tenant_id', $tenantId)->selectRaw('SUM(stock_qty * cost_price) as total')->value('total') ?? 0,
            'low_stock_count' => Product::where('tenant_id', $tenantId)->whereColumn('stock_qty', '<=', 'reorder_level')->count(),
            'out_of_stock' => Product::where('tenant_id', $tenantId)->where('stock_qty', 0)->count(),
        ];

        return view('operations.inventory.index', compact(
            'products', 'categories', 'warehouses', 'stats', 'selectedWarehouse'
        ));
    }

    public function show(Product $product)
    {
        if ($product->tenant_id !== Auth::user()->tenant_id) abort(403);
        $product->load('batches.warehouse');

        $warehouseStock = $product->batches->groupBy('warehouse_id')->map(function($batches, $warehouseId) {
            $warehouse = $batches->first()->warehouse;
            return [
                'warehouse' => $warehouse,
                'warehouse_name' => $warehouse ? $warehouse->name : 'Unassigned Warehouse',
                'warehouse_code' => $warehouse ? $warehouse->code : 'N/A',
                'warehouse_city' => $warehouse ? $warehouse->city : 'Unknown',
                'warehouse_state' => $warehouse ? $warehouse->state : 'Unknown',
                'total_qty' => $batches->sum('current_qty'),
                'batches' => $batches,
            ];
        });

        return view('operations.inventory.show', compact('product', 'warehouseStock'));
    }

        public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|unique:products,sku',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:20',
            'gst_rate' => 'nullable|numeric|min:0|max:100',
            'hsn_code' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'stock_qty' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'warehouse_id' => 'required|exists:warehouses,id', // ✅ NEW
        ]);

        $tenantId = Auth::user()->tenant_id;

        $product = Product::create([
            'tenant_id' => $tenantId,
            'sku' => $request->sku,
            'name' => $request->name,
            'category' => $request->category,
            'unit' => $request->unit ?? 'PCS',
            'gst_rate' => $request->gst_rate ?? 18.00,
            'hsn_code' => $request->hsn_code,
            'price' => $request->price,
            'cost_price' => $request->cost_price,
            'stock_qty' => $request->stock_qty,
            'reorder_level' => $request->reorder_level,
            'status' => 'active',
        ]);

        if ($request->stock_qty > 0) {
            $batch = Batch::create([
                'tenant_id' => $tenantId,
                'product_id' => $product->id,
                'warehouse_id' => $request->warehouse_id, // ✅ LINKED TO WAREHOUSE
                'batch_no' => 'BATCH-' . strtoupper($request->sku) . '-' . now()->format('Ymd'),
                'initial_qty' => $request->stock_qty,
                'current_qty' => $request->stock_qty,
            ]);

            // If you use the Inventory model, update it too:
            // Inventory::create([... 'warehouse_id' => $request->warehouse_id, ...]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product added successfully!',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'unit' => $product->unit,
                    'warehouse_stock' => $request->stock_qty,
                ]
            ]);
        }

        return redirect()->route('operations.inventory.index')->with('success', 'Product added successfully!');
    }

    public function update(Request $request, Product $product)
    {
        if ($product->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate([
            'sku' => 'required|unique:products,sku,' . $product->id,
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0',
        ]);

        $product->update($request->only(['sku', 'name', 'category', 'unit', 'gst_rate', 'hsn_code', 'price', 'cost_price', 'reorder_level', 'status']));

        return redirect()->route('operations.inventory.index')->with('success', 'Product updated successfully!');
    }

    public function exportCsv(Request $request)
    {
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=inventory_" . date('Y-m-d') . ".csv",
            "Pragma" => "no-cache",
            "Expires" => "0"
        ];

        $products = Product::where('tenant_id', Auth::user()->tenant_id)->get();

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['SKU', 'Name', 'Category', 'Stock', 'Reorder Level', 'Price', 'Status']);

            foreach ($products as $product) {
                $status = $product->stock_qty <= 0 ? 'Out of Stock' : ($product->stock_qty <= $product->reorder_level ? 'Low' : 'OK');
                fputcsv($file, [
                    $product->sku, $product->name, $product->category, 
                    $product->stock_qty, $product->reorder_level, $product->price, $status
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * ✅ NEW: AJAX endpoint to get detailed stats, shipments, and returns for a specific product
     */
    public function getDetails($productId)
    {
        $tenantId = Auth::user()->tenant_id;
        $product = Product::where('id', $productId)->where('tenant_id', $tenantId)->firstOrFail();

        // 1. KPI: Total Remaining Stock (sum of current_qty in batches)
        $totalStock = Batch::where('product_id', $productId)->sum('current_qty');

        // 2. KPI: Damaged / Defected Quantity (from GR/QR Postings)
        $damagedQty = GrqrPosting::whereHas('batch', function($q) use ($productId) {
                $q->where('product_id', $productId);
            })->sum('damaged_qty');

        // 3. KPI: Total Returned Quantity (from Return Orders)
        // Note: Adjust 'type' => 'return' if your return logic uses a different model or status
        $returnedQty = Order::where('tenant_id', $tenantId)
            ->where('type', 'return') 
            ->whereHas('items', function($q) use ($productId) {
                $q->where('product_id', $productId);
            })->with('items')->get()->sum(function($order) use ($productId) {
                return $order->items->firstWhere('product_id', $productId)?->qty ?? 0;
            });

        // 4. Shipment History (Last 5)
        $shipments = Shipment::where('tenant_id', $tenantId)
            ->whereHas('batches', function($q) use ($productId) {
                $q->where('product_id', $productId);
            })->with(['batches' => function($q) use ($productId) {
                $q->where('product_id', $productId);
            }])->latest()->take(5)->get();

        // 5. Return Order Records (Last 5)
        $returns = Order::where('tenant_id', $tenantId)
            ->where('type', 'return')
            ->whereHas('items', function($q) use ($productId) {
                $q->where('product_id', $productId);
            })->with(['customer', 'items'])->latest()->take(5)->get();

        return response()->json([
            'product' => $product,
            'kpis' => [
                'total_stock' => $totalStock,
                'damaged_qty' => $damagedQty,
                'returned_qty' => $returnedQty,
                'active_shipments' => $shipments->where('status', '!=', 'delivered')->count(),
            ],
            'shipments' => $shipments,
            'returns' => $returns,
        ]);
    }

        public function stockIn(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'qty' => 'required|integer|min:1',
            'batch_no' => 'nullable|string|max:50',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $productId = $request->product_id;
        $warehouseId = $request->warehouse_id;
        $qty = $request->qty;

        DB::beginTransaction();
        try {
            // 1. Find or Create Batch for this Product in this Warehouse
            $batch = Batch::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->first();

            if (!$batch) {
                $batch = Batch::create([
                    'tenant_id' => $tenantId,
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'batch_no' => $request->batch_no ?? 'STOCK-IN-' . now()->format('Ymd-His'),
                    'initial_qty' => 0,
                    'current_qty' => 0,
                ]);
            }

            // 2. Update Batch Quantity
            $batch->increment('current_qty', $qty);

            // 3. Update Global Product Stock
            $product = Product::find($productId);
            $product->increment('stock_qty', $qty);

            DB::commit();
            return back()->with('success', "Successfully added {$qty} units of {$product->name} to the warehouse!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to add stock: ' . $e->getMessage()]);
        }
    }
}