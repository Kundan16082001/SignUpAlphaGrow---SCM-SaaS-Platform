<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\WarehouseBin;
use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $query = Warehouse::where('tenant_id', $tenantId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $warehouses = $query->withCount('bins')->latest()->paginate(12);

        // Summary stats
        $stats = [
            'total_warehouses' => Warehouse::where('tenant_id', $tenantId)->count(),
            'active_warehouses' => Warehouse::where('tenant_id', $tenantId)->where('status', 'active')->count(),
            'total_bins' => WarehouseBin::where('tenant_id', $tenantId)->count(),
            'total_stock_value' => Batch::where('batches.tenant_id', $tenantId) // ✅ FIXED: Explicitly specify table
                ->join('products', 'batches.product_id', '=', 'products.id')
                ->sum(DB::raw('batches.current_qty * products.cost_price')),
        ];

        $types = ['all', 'main', 'branch', 'cold_storage', 'hazmat', 'return'];

        return view('operations.warehouses.index', compact('warehouses', 'stats', 'types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:warehouses,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:main,branch,cold_storage,hazmat,return',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'manager_name' => 'nullable|string|max:255',
            'manager_phone' => 'nullable|string|max:20',
            'manager_email' => 'nullable|email|max:255',
            'total_area_sqft' => 'nullable|integer|min:0',
            'pallet_capacity' => 'nullable|integer|min:0',
            'shelf_capacity' => 'nullable|integer|min:0',
        ]);

        Warehouse::create(array_merge(
            $request->only([
                'code', 'name', 'type', 'address', 'city', 'state', 'pincode', 'country',
                'latitude', 'longitude', 'manager_name', 'manager_phone', 'manager_email',
                'total_area_sqft', 'pallet_capacity', 'shelf_capacity', 'cold_capacity',
                'hazmat_capacity', 'max_weight_tons', 'status'
            ]),
            ['tenant_id' => Auth::user()->tenant_id]
        ));

        return redirect()->back()->with('success', 'Warehouse created successfully!');
    }

    public function show(Warehouse $warehouse)
    {
        if ($warehouse->tenant_id !== Auth::user()->tenant_id) abort(403);

        $warehouse->loadCount('bins');

        $bins = WarehouseBin::where('warehouse_id', $warehouse->id)
            ->orderBy('zone')
            ->orderBy('aisle')
            ->orderBy('rack')
            ->paginate(20);

        $recentBatches = Batch::with('product')
            ->where('warehouse_id', $warehouse->id)
            ->latest()
            ->take(10)
            ->get();

        $stats = [
            'total_bins' => $warehouse->bins_count,
            'occupied_bins' => $warehouse->bins()->where('status', 'occupied')->count(),
            'available_bins' => $warehouse->bins()->where('status', 'available')->count(),
            'stock_value' => $warehouse->stock_value,
            'utilization' => $warehouse->utilization_percent,
        ];

        return view('operations.warehouses.show', compact('warehouse', 'bins', 'recentBatches', 'stats'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        if ($warehouse->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:main,branch,cold_storage,hazmat,return',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'manager_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,maintenance',
        ]);

        $warehouse->update($request->only([
            'name', 'type', 'address', 'city', 'state', 'pincode', 'country',
            'latitude', 'longitude', 'manager_name', 'manager_phone', 'manager_email',
            'total_area_sqft', 'pallet_capacity', 'shelf_capacity', 'cold_capacity',
            'hazmat_capacity', 'max_weight_tons', 'status'
        ]));

        return redirect()->back()->with('success', 'Warehouse updated successfully!');
    }

    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->tenant_id !== Auth::user()->tenant_id) abort(403);

        if ($warehouse->bins()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete warehouse with existing bins. Remove all bins first.']);
        }

        $warehouse->delete();
        return redirect()->back()->with('success', 'Warehouse deleted successfully!');
    }
}