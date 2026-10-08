<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Models\WarehouseBin;
use App\Models\WarehouseCapacityConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehouseCapacityController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;

        // Get or create config
        $config = WarehouseCapacityConfig::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'warehouse_name' => 'Main Warehouse',
                'total_area_sqft' => 10000,
                'pallet_capacity' => 500,
                'shelf_capacity' => 1000,
                'cold_capacity' => 200,
                'hazmat_capacity' => 100,
                'max_weight_tons' => 50,
            ]
        );

        // Bins
        $bins = WarehouseBin::where('tenant_id', $tenantId)->get();

        // Calculate utilization
        $totalCapacity = $config->total_capacity;
        $usedCapacity = $bins->sum('current_qty');
        $utilizationPercent = $totalCapacity > 0 ? round(($usedCapacity / $totalCapacity) * 100, 1) : 0;

        // Capacity by type
        $capacityByType = [
            'pallet' => ['total' => $config->pallet_capacity, 'used' => $bins->where('type', 'pallet')->sum('current_qty')],
            'shelf' => ['total' => $config->shelf_capacity, 'used' => $bins->where('type', 'shelf')->sum('current_qty')],
            'cold' => ['total' => $config->cold_capacity, 'used' => $bins->where('type', 'cold')->sum('current_qty')],
            'hazmat' => ['total' => $config->hazmat_capacity, 'used' => $bins->where('type', 'hazmat')->sum('current_qty')],
        ];

        // Bin stats
        $binStats = [
            'total' => $bins->count(),
            'available' => $bins->where('status', 'available')->count(),
            'occupied' => $bins->where('status', 'occupied')->count(),
            'reserved' => $bins->where('status', 'reserved')->count(),
            'maintenance' => $bins->where('status', 'maintenance')->count(),
        ];

        return view('scm.warehouse-capacity.index', compact('config', 'bins', 'utilizationPercent', 'capacityByType', 'binStats'));
    }

    public function storeBin(Request $request)
    {
        $request->validate([
            'bin_code' => 'required|string|unique:warehouse_bins,bin_code',
            'type' => 'required|in:pallet,shelf,cold,hazmat',
            'max_capacity' => 'required|integer|min:1',
        ]);

        WarehouseBin::create([
            'tenant_id' => Auth::user()->tenant_id,
            'bin_code' => strtoupper($request->bin_code),
            'type' => $request->type,
            'zone' => $request->zone,
            'aisle' => $request->aisle,
            'rack' => $request->rack,
            'max_capacity' => $request->max_capacity,
            'current_qty' => 0,
            'temperature_zone' => $request->temperature_zone,
            'status' => 'available',
        ]);

        return redirect()->route('scm.warehouse-capacity.index')->with('success', 'Bin added successfully!');
    }

    public function updateConfig(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $config = WarehouseCapacityConfig::where('tenant_id', $tenantId)->first();

        $request->validate([
            'warehouse_name' => 'required|string|max:255',
            'total_area_sqft' => 'required|numeric|min:0',
            'pallet_capacity' => 'required|integer|min:0',
            'shelf_capacity' => 'required|integer|min:0',
            'cold_capacity' => 'required|integer|min:0',
            'hazmat_capacity' => 'required|integer|min:0',
            'max_weight_tons' => 'required|numeric|min:0',
        ]);

        $config->update($request->only([
            'warehouse_name', 'total_area_sqft', 'pallet_capacity',
            'shelf_capacity', 'cold_capacity', 'hazmat_capacity', 'max_weight_tons'
        ]));

        return redirect()->route('scm.warehouse-capacity.index')->with('success', 'Capacity configuration updated!');
    }
}