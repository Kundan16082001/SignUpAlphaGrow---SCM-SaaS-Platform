<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Models\{Warehouse, WarehouseBin, Batch};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WarehouseCapacityController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        // Get all active warehouses
        $warehouses = Warehouse::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->withCount('bins')
            ->get();

        // Filter by specific warehouse if selected
        $selectedWarehouse = null;
        if ($request->filled('warehouse_id')) {
            $selectedWarehouse = Warehouse::where('id', $request->warehouse_id)
                ->where('tenant_id', $tenantId)
                ->first();
        }

        // Calculate overall stats
        $overallStats = $this->calculateOverallStats($tenantId, $selectedWarehouse);

        // Calculate per-warehouse stats for comparison
        $warehouseStats = $warehouses->map(function($wh) use ($tenantId) {
            return $this->getWarehouseStats($wh, $tenantId);
        });

        // Bin type breakdown
        $binTypeStats = $this->getBinTypeStats($tenantId, $selectedWarehouse);

        // Zone-wise utilization
        $zoneStats = $this->getZoneStats($tenantId, $selectedWarehouse);

        return view('scm.warehouse-capacity.index', compact(
            'warehouses', 'selectedWarehouse', 'overallStats', 'warehouseStats', 'binTypeStats', 'zoneStats'
        ));
    }

    private function calculateOverallStats($tenantId, $selectedWarehouse = null)
    {
        $query = WarehouseBin::where('tenant_id', $tenantId);
        $batchQuery = Batch::where('batches.tenant_id', $tenantId); // ✅ FIXED: Explicit table name
        if ($selectedWarehouse) {
            $query->where('warehouse_id', $selectedWarehouse->id);
            $batchQuery->where('warehouse_id', $selectedWarehouse->id);
        }

        $totalBins = (clone $query)->count();
        $availableBins = (clone $query)->where('status', 'available')->count();
        $occupiedBins = (clone $query)->where('status', 'occupied')->count();
        $reservedBins = (clone $query)->where('status', 'reserved')->count();

        $totalCapacity = (clone $query)->sum('max_capacity');
        $usedCapacity = (clone $query)->sum('current_qty');
        $utilization = $totalCapacity > 0 ? round(($usedCapacity / $totalCapacity) * 100, 1) : 0;

        $stockValue = (clone $batchQuery)
            ->join('products', 'batches.product_id', '=', 'products.id')
            ->sum(DB::raw('batches.current_qty * products.cost_price'));

        $totalArea = $selectedWarehouse 
            ? $selectedWarehouse->total_area_sqft 
            : Warehouse::where('tenant_id', $tenantId)->sum('total_area_sqft');

        return [
            'total_bins' => $totalBins,
            'available_bins' => $availableBins,
            'occupied_bins' => $occupiedBins,
            'reserved_bins' => $reservedBins,
            'total_capacity' => $totalCapacity,
            'used_capacity' => $usedCapacity,
            'utilization' => $utilization,
            'stock_value' => (float) $stockValue,
            'total_area' => $totalArea,
        ];
    }

    private function getWarehouseStats(Warehouse $wh, $tenantId)
    {
        $bins = WarehouseBin::where('warehouse_id', $wh->id)->get();
        
        $totalCapacity = $bins->sum('max_capacity');
        $usedCapacity = $bins->sum('current_qty');
        $utilization = $totalCapacity > 0 ? round(($usedCapacity / $totalCapacity) * 100, 1) : 0;

        $stockValue = Batch::where('warehouse_id', $wh->id)
            ->join('products', 'batches.product_id', '=', 'products.id')
            ->where('batches.tenant_id', $tenantId)
            ->sum(DB::raw('batches.current_qty * products.cost_price'));
        return [
            'warehouse' => $wh,
            'total_bins' => $bins->count(),
            'available' => $bins->where('status', 'available')->count(),
            'occupied' => $bins->where('status', 'occupied')->count(),
            'reserved' => $bins->where('status', 'reserved')->count(),
            'total_capacity' => $totalCapacity,
            'used_capacity' => $usedCapacity,
            'utilization' => $utilization,
            'stock_value' => (float) $stockValue,
            'area' => $wh->total_area_sqft,
        ];
    }

    private function getBinTypeStats($tenantId, $selectedWarehouse = null)
    {
        $query = WarehouseBin::where('tenant_id', $tenantId);
        
        if ($selectedWarehouse) {
            $query->where('warehouse_id', $selectedWarehouse->id);
        }

        return $query->select('type', 
                DB::raw('COUNT(*) as total_bins'),
                DB::raw('SUM(max_capacity) as total_capacity'),
                DB::raw('SUM(current_qty) as used_capacity')
            )
            ->groupBy('type')
            ->get()
            ->map(function($item) {
                $item->utilization = $item->total_capacity > 0 
                    ? round(($item->used_capacity / $item->total_capacity) * 100, 1) 
                    : 0;
                return $item;
            });
    }

    private function getZoneStats($tenantId, $selectedWarehouse = null)
    {
        $query = WarehouseBin::where('tenant_id', $tenantId);
        
        if ($selectedWarehouse) {
            $query->where('warehouse_id', $selectedWarehouse->id);
        }

        return $query->select('zone',
                DB::raw('COUNT(*) as total_bins'),
                DB::raw('SUM(max_capacity) as total_capacity'),
                DB::raw('SUM(current_qty) as used_capacity')
            )
            ->groupBy('zone')
            ->orderBy('zone')
            ->get()
            ->map(function($item) {
                $item->utilization = $item->total_capacity > 0 
                    ? round(($item->used_capacity / $item->total_capacity) * 100, 1) 
                    : 0;
                return $item;
            });
    }
}