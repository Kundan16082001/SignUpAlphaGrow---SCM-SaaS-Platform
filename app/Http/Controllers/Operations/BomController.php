<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Bom;
use App\Models\BomComponent;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BomController extends Controller
{
    public function index(Request $request)
{
    $tenantId = Auth::user()->tenant_id;
    $query = Bom::with('product')->where('tenant_id', $tenantId);

    // Search
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('bom_no', 'like', "%{$search}%")
              ->orWhere('product_name', 'like', "%{$search}%")
              ->orWhere('product_sku', 'like', "%{$search}%");
        });
    }

    // Status Filter
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $boms = $query->latest()->paginate(10)->withQueryString();

    // Stats
    $stats = [
        'total' => Bom::where('tenant_id', $tenantId)->count(),
        'active' => Bom::where('tenant_id', $tenantId)->where('status', 'active')->count(),
        'draft' => Bom::where('tenant_id', $tenantId)->where('status', 'draft')->count(),
        'archived' => Bom::where('tenant_id', $tenantId)->where('status', 'archived')->count(),
    ];

    // ✅ ADD THIS LINE - Pass products for the modal
    $products = Product::where('tenant_id', $tenantId)->where('status', 'active')->get();

    return view('operations.bom.index', compact('boms', 'stats', 'products'));
}

    public function create()
    {
        $tenantId = Auth::user()->tenant_id;
        $products = Product::where('tenant_id', $tenantId)->where('status', 'active')->get();
        return view('operations.bom.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'components' => 'required|array|min:1',
            'components.*.component_sku' => 'required|string',
            'components.*.component_name' => 'required|string',
            'components.*.qty_required' => 'required|numeric|min:0.001',
            'components.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $product = Product::find($request->product_id);

        DB::beginTransaction();
        try {
            // Calculate total unit cost
            $unitCost = 0;
            foreach ($request->components as $comp) {
                $lineTotal = $comp['qty_required'] * $comp['unit_cost'];
                $wastage = $lineTotal * (($comp['wastage_percentage'] ?? 0) / 100);
                $unitCost += $lineTotal + $wastage;
            }

            // Generate BOM number
            $bomNo = 'BOM-' . strtoupper(substr($product->sku, 0, 3)) . '-' . now()->format('Ymd') . '-' . rand(100, 999);

            // Create BOM
            $bom = Bom::create([
                'tenant_id' => $tenantId,
                'bom_no' => $bomNo,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'unit_cost' => $unitCost,
                'components_count' => count($request->components),
                'status' => 'draft',
                'version' => 1,
            ]);

            // Create Components
            foreach ($request->components as $comp) {
                $lineTotal = $comp['qty_required'] * $comp['unit_cost'];
                $wastageAmount = $lineTotal * (($comp['wastage_percentage'] ?? 0) / 100);

                BomComponent::create([
                    'bom_id' => $bom->id,
                    'component_sku' => $comp['component_sku'],
                    'component_name' => $comp['component_name'],
                    'qty_required' => $comp['qty_required'],
                    'unit' => $comp['unit'] ?? 'PCS',
                    'unit_cost' => $comp['unit_cost'],
                    'total_cost' => $lineTotal,
                    'wastage_percentage' => $comp['wastage_percentage'] ?? 0,
                ]);
            }

            DB::commit();
            return redirect()->route('operations.bom.index')->with('success', 'BOM created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create BOM: ' . $e->getMessage()]);
        }
    }

    public function show(Bom $bom)
    {
        if ($bom->tenant_id !== Auth::user()->tenant_id) abort(403);

        $bom->load('components', 'product');

        // Stock availability check per component
        $componentsWithStock = $bom->components->map(function ($comp) {
            // Find product by SKU to check stock
            $product = Product::where('sku', $comp->component_sku)->first();
            $availableStock = $product ? $product->stock_qty : 0;

            $comp->available_stock = $availableStock;
            $comp->is_sufficient = $availableStock >= $comp->qty_required;
            $comp->shortage = max(0, $comp->qty_required - $availableStock);
            return $comp;
        });

        return view('operations.bom.show', compact('bom', 'componentsWithStock'));
    }

    public function edit(Bom $bom)
    {
        if ($bom->tenant_id !== Auth::user()->tenant_id) abort(403);

        $tenantId = Auth::user()->tenant_id;
        $products = Product::where('tenant_id', $tenantId)->where('status', 'active')->get();
        $bom->load('components');

        return view('operations.bom.edit', compact('bom', 'products'));
    }

    public function update(Request $request, Bom $bom)
    {
        if ($bom->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'components' => 'required|array|min:1',
            'components.*.component_sku' => 'required|string',
            'components.*.component_name' => 'required|string',
            'components.*.qty_required' => 'required|numeric|min:0.001',
            'components.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::find($request->product_id);

            // Version Control: Create new version
            $newVersion = $bom->version + 1;

            // Archive old BOM
            $bom->update(['status' => 'archived']);

            // Calculate new unit cost
            $unitCost = 0;
            foreach ($request->components as $comp) {
                $lineTotal = $comp['qty_required'] * $comp['unit_cost'];
                $wastage = $lineTotal * (($comp['wastage_percentage'] ?? 0) / 100);
                $unitCost += $lineTotal + $wastage;
            }

            // Generate new BOM number with version
            $bomNo = 'BOM-' . strtoupper(substr($product->sku, 0, 3)) . '-' . now()->format('Ymd') . '-' . rand(100, 999);

            // Create new BOM version
            $newBom = Bom::create([
                'tenant_id' => $bom->tenant_id,
                'bom_no' => $bomNo,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'unit_cost' => $unitCost,
                'components_count' => count($request->components),
                'status' => 'active',
                'version' => $newVersion,
            ]);

            // Create new Components
            foreach ($request->components as $comp) {
                $lineTotal = $comp['qty_required'] * $comp['unit_cost'];

                BomComponent::create([
                    'bom_id' => $newBom->id,
                    'component_sku' => $comp['component_sku'],
                    'component_name' => $comp['component_name'],
                    'qty_required' => $comp['qty_required'],
                    'unit' => $comp['unit'] ?? 'PCS',
                    'unit_cost' => $comp['unit_cost'],
                    'total_cost' => $lineTotal,
                    'wastage_percentage' => $comp['wastage_percentage'] ?? 0,
                ]);
            }

            DB::commit();
            return redirect()->route('operations.bom.show', $newBom)->with('success', 'BOM updated to version ' . $newVersion . '!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update BOM: ' . $e->getMessage()]);
        }
    }

    public function destroy(Bom $bom)
    {
        if ($bom->tenant_id !== Auth::user()->tenant_id) abort(403);

        // Check if BOM is used in active work orders
        if ($bom->workOrders()->whereNotIn('status', ['completed', 'cancelled'])->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete BOM with active work orders.']);
        }

        $bom->delete();
        return redirect()->route('operations.bom.index')->with('success', 'BOM deleted successfully!');
    }

    public function activate(Bom $bom)
    {
        if ($bom->tenant_id !== Auth::user()->tenant_id) abort(403);

        $bom->update(['status' => 'active']);
        return redirect()->route('operations.bom.index')->with('success', 'BOM activated!');
    }
}