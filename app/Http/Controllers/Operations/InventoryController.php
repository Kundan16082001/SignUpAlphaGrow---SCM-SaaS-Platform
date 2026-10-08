<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Batch;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $query = Product::where('tenant_id', $tenantId);

        // SKU / Name Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Category Filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Stock Status Filter
        if ($request->filled('stock_status')) {
            if ($request->stock_status == 'out_of_stock') {
                $query->where('stock_qty', '<=', 0);
            } elseif ($request->stock_status == 'low') {
                $query->where('stock_qty', '>', 0)->whereColumn('stock_qty', '<=', 'reorder_level');
            } elseif ($request->stock_status == 'ok') {
                $query->whereColumn('stock_qty', '>', 'reorder_level');
            }
        }

        $products = $query->latest()->paginate(10)->withQueryString();
        $categories = Product::where('tenant_id', $tenantId)->distinct()->pluck('category')->filter();

        return view('operations.inventory.index', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|unique:products,sku',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'stock_qty' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
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

        // Auto-create initial batch and inventory record if stock > 0
        if ($request->stock_qty > 0) {
            $batch = Batch::create([
                'tenant_id' => $tenantId,
                'product_id' => $product->id,
                'batch_no' => 'BATCH-' . strtoupper($request->sku) . '-' . now()->format('Ymd'),
                'initial_qty' => $request->stock_qty,
                'current_qty' => $request->stock_qty,
            ]);

            Inventory::create([
                'tenant_id' => $tenantId,
                'product_id' => $product->id,
                'batch_id' => $batch->id,
                'warehouse_name' => 'Main Warehouse',
                'qty' => $request->stock_qty,
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
}