<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\{Warehouse, WarehouseBin};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehouseBinController extends Controller
{
    public function index(Warehouse $warehouse)
    {
        if ($warehouse->tenant_id !== Auth::user()->tenant_id) abort(403);

        $bins = WarehouseBin::where('warehouse_id', $warehouse->id)
            ->orderBy('zone')
            ->orderBy('aisle')
            ->orderBy('rack')
            ->paginate(20);

        $stats = [
            'total_bins' => $bins->total(),
            'available' => $warehouse->bins()->where('status', 'available')->count(),
            'occupied' => $warehouse->bins()->where('status', 'occupied')->count(),
            'reserved' => $warehouse->bins()->where('status', 'reserved')->count(),
        ];

        return view('operations.warehouse-bins.index', compact('warehouse', 'bins', 'stats'));
    }

    public function store(Request $request, Warehouse $warehouse)
    {
        if ($warehouse->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate([
            'bin_code' => 'required|string|max:50|unique:warehouse_bins,bin_code',
            'type' => 'required|in:shelf,pallet,cold,hazmat',
            'zone' => 'required|string|max:10',
            'aisle' => 'required|string|max:10',
            'rack' => 'required|string|max:10',
            'max_capacity' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,reserved',
        ]);

        WarehouseBin::create([
            'tenant_id' => Auth::user()->tenant_id,
            'warehouse_id' => $warehouse->id,
            'bin_code' => $request->bin_code,
            'type' => $request->type,
            'zone' => $request->zone,
            'aisle' => $request->aisle,
            'rack' => $request->rack,
            'max_capacity' => $request->max_capacity,
            'current_qty' => 0,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Bin created successfully!');
    }

    public function update(Request $request, WarehouseBin $bin)
    {
        if ($bin->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate([
            'type' => 'required|in:shelf,pallet,cold,hazmat',
            'zone' => 'required|string|max:10',
            'aisle' => 'required|string|max:10',
            'rack' => 'required|string|max:10',
            'max_capacity' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,reserved',
        ]);

        $bin->update($request->only(['type', 'zone', 'aisle', 'rack', 'max_capacity', 'status']));

        return redirect()->back()->with('success', 'Bin updated successfully!');
    }

    public function destroy(WarehouseBin $bin)
    {
        if ($bin->tenant_id !== Auth::user()->tenant_id) abort(403);

        if ($bin->current_qty > 0) {
            return back()->withErrors(['error' => 'Cannot delete bin with stock. Move stock first.']);
        }

        $bin->delete();
        return redirect()->back()->with('success', 'Bin deleted successfully!');
    }

    public function bulkCreate(Request $request, Warehouse $warehouse)
    {
        if ($warehouse->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate([
            'zone_start' => 'required|string|max:1',
            'zone_end' => 'required|string|max:1',
            'aisles' => 'required|integer|min:1|max:99',
            'racks_per_aisle' => 'required|integer|min:1|max:99',
            'type' => 'required|in:shelf,pallet,cold,hazmat',
            'max_capacity' => 'required|integer|min:1',
        ]);

        $created = 0;
        $zoneStart = ord(strtoupper($request->zone_start));
        $zoneEnd = ord(strtoupper($request->zone_end));

        for ($zone = $zoneStart; $zone <= $zoneEnd; $zone++) {
            $zoneLetter = chr($zone);
            for ($aisle = 1; $aisle <= $request->aisles; $aisle++) {
                for ($rack = 1; $rack <= $request->racks_per_aisle; $rack++) {
                    $binCode = $warehouse->code . '-' . $zoneLetter . str_pad($aisle, 2, '0', STR_PAD_LEFT) . '-' . str_pad($rack, 2, '0', STR_PAD_LEFT);
                    
                    if (!WarehouseBin::where('bin_code', $binCode)->exists()) {
                        WarehouseBin::create([
                            'tenant_id' => Auth::user()->tenant_id,
                            'warehouse_id' => $warehouse->id,
                            'bin_code' => $binCode,
                            'type' => $request->type,
                            'zone' => $zoneLetter,
                            'aisle' => str_pad($aisle, 2, '0', STR_PAD_LEFT),
                            'rack' => str_pad($rack, 2, '0', STR_PAD_LEFT),
                            'max_capacity' => $request->max_capacity,
                            'current_qty' => 0,
                            'status' => 'available',
                        ]);
                        $created++;
                    }
                }
            }
        }

        return redirect()->back()->with('success', "Created {$created} bins successfully!");
    }
}