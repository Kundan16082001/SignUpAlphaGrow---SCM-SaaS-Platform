<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Carrier;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogisticsController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        // KPI Stats
        $stats = [
            'total_shipments' => Shipment::where('tenant_id', $tenantId)->count(),
            'in_transit' => Shipment::where('tenant_id', $tenantId)->inTransit()->count(),
            'delivered' => Shipment::where('tenant_id', $tenantId)->where('status', 'delivered')->count(),
            'overdue' => Shipment::where('tenant_id', $tenantId)->overdue()->count(),
            'total_freight' => Shipment::where('tenant_id', $tenantId)->sum('freight_cost'),
            'active_carriers' => Carrier::where('tenant_id', $tenantId)->active()->count(),
            'active_drivers' => Driver::where('tenant_id', $tenantId)->active()->count(),
            'available_vehicles' => Vehicle::where('tenant_id', $tenantId)->available()->count(),
        ];

        // Shipments Query
        $shipmentsQuery = Shipment::with(['carrier', 'driver', 'vehicle'])
            ->where('tenant_id', $tenantId);

        if ($request->filled('direction')) {
            $shipmentsQuery->where('direction', $request->direction);
        }
        if ($request->filled('status')) {
            $shipmentsQuery->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $shipmentsQuery->where(function($q) use ($search) {
                $q->where('shipment_no', 'like', "%{$search}%")
                  ->orWhere('origin', 'like', "%{$search}%")
                  ->orWhere('destination', 'like', "%{$search}%");
            });
        }

        $shipments = $shipmentsQuery->latest()->paginate(10)->withQueryString();

        // Other entities
        $carriers = Carrier::where('tenant_id', $tenantId)->latest()->paginate(10);
        $drivers = Driver::where('tenant_id', $tenantId)->latest()->paginate(10);
        $vehicles = Vehicle::where('tenant_id', $tenantId)->latest()->paginate(10);

        return view('operations.logistics.index', compact(
            'stats', 'shipments', 'carriers', 'drivers', 'vehicles'
        ));
    }

    public function storeShipment(Request $request)
    {
        $request->validate([
            'direction' => 'required|in:inbound,outbound,return',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'carrier_id' => 'nullable|exists:carriers,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'freight_cost' => 'required|numeric|min:0',
            'eta_date' => 'required|date|after_or_equal:today',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $shipmentNo = 'SHP-' . strtoupper(substr($request->direction, 0, 1)) . '-' . now()->format('Ymd') . '-' . rand(1000, 9999);

        Shipment::create([
            'tenant_id' => $tenantId,
            'shipment_no' => $shipmentNo,
            'direction' => $request->direction,
            'origin' => $request->origin,
            'destination' => $request->destination,
            'carrier_id' => $request->carrier_id,
            'driver_id' => $request->driver_id,
            'vehicle_id' => $request->vehicle_id,
            'freight_cost' => $request->freight_cost,
            'eta_date' => $request->eta_date,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        return redirect()->route('operations.logistics.index')->with('success', 'Shipment created successfully!');
    }

    public function updateShipmentStatus(Request $request, Shipment $shipment)
    {
        if ($shipment->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate([
            'status' => 'required|in:pending,in_transit,delivered,delayed,cancelled',
        ]);

        $shipment->update(['status' => $request->status]);

        // Update vehicle status if delivered
        if ($request->status === 'delivered' && $shipment->vehicle_id) {
            $shipment->vehicle->update(['status' => 'available']);
        } elseif ($request->status === 'in_transit' && $shipment->vehicle_id) {
            $shipment->vehicle->update(['status' => 'in_transit']);
        }

        return redirect()->route('operations.logistics.index')->with('success', 'Shipment status updated!');
    }

    public function storeCarrier(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'contact' => 'nullable|string|max:50',
            'gstin' => 'nullable|string|size:15',
        ]);

        Carrier::create([
            'tenant_id' => Auth::user()->tenant_id,
            'name' => $request->name,
            'type' => $request->type,
            'contact' => $request->contact,
            'gstin' => $request->gstin,
            'coverage' => $request->coverage,
            'rating' => $request->rating ?? 5.00,
            'status' => 'active',
        ]);

        return redirect()->route('operations.logistics.index')->with('success', 'Carrier added!');
    }

    public function storeDriver(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'license' => 'required|string|unique:drivers,license',
            'phone' => 'nullable|string|max:20',
        ]);

        Driver::create([
            'tenant_id' => Auth::user()->tenant_id,
            'name' => $request->name,
            'license' => $request->license,
            'phone' => $request->phone,
            'vehicle_type' => $request->vehicle_type,
            'vehicle_no' => $request->vehicle_no,
            'capacity' => $request->capacity ?? 0,
            'experience_years' => $request->experience_years ?? 0,
            'rating' => $request->rating ?? 5.00,
            'status' => 'active',
        ]);

        return redirect()->route('operations.logistics.index')->with('success', 'Driver added!');
    }

    public function storeVehicle(Request $request)
    {
        $request->validate([
            'reg_no' => 'required|string|unique:vehicles,reg_no',
            'type' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
        ]);

        Vehicle::create([
            'tenant_id' => Auth::user()->tenant_id,
            'reg_no' => $request->reg_no,
            'type' => $request->type,
            'model' => $request->model,
            'capacity' => $request->capacity ?? 0,
            'fuel_type' => $request->fuel_type,
            'year' => $request->year,
            'insurance_expiry' => $request->insurance_expiry,
            'fitness_expiry' => $request->fitness_expiry,
            'status' => 'available',
        ]);

        return redirect()->route('operations.logistics.index')->with('success', 'Vehicle added!');
    }
}