@extends('layouts.master')
@section('title', 'Logistics')
@section('page-title', 'Logistics Management')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="small mb-1 opacity-75">Total Shipments</p>
                            <h3 class="fw-bold mb-0">{{ $stats['total_shipments'] }}</h3>
                        </div>
                        <i class="bi bi-truck fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="small mb-1 opacity-75">In Transit</p>
                            <h3 class="fw-bold mb-0">{{ $stats['in_transit'] }}</h3>
                        </div>
                        <i class="bi bi-geo-alt fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="small mb-1 opacity-75">Overdue</p>
                            <h3 class="fw-bold mb-0">{{ $stats['overdue'] }}</h3>
                        </div>
                        <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="small mb-1 opacity-75">Total Freight Cost</p>
                            <h3 class="fw-bold mb-0">₹{{ number_format($stats['total_freight'], 0) }}</h3>
                        </div>
                        <i class="bi bi-currency-rupee fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3 d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-circle">
                        <i class="bi bi-building fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted">Active Carriers</small>
                        <h5 class="fw-bold mb-0">{{ $stats['active_carriers'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3 d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-circle">
                        <i class="bi bi-person-badge fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted">Active Drivers</small>
                        <h5 class="fw-bold mb-0">{{ $stats['active_drivers'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3 d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 text-success p-2 rounded-circle">
                        <i class="bi bi-car-front fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted">Available Vehicles</small>
                        <h5 class="fw-bold mb-0">{{ $stats['available_vehicles'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <ul class="nav nav-pills mb-4" id="logisticsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="shipments-tab" data-bs-toggle="pill" data-bs-target="#shipments" type="button" role="tab">
                <i class="bi bi-truck me-1"></i> Shipments
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="carriers-tab" data-bs-toggle="pill" data-bs-target="#carriers" type="button" role="tab">
                <i class="bi bi-building me-1"></i> Carriers
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="drivers-tab" data-bs-toggle="pill" data-bs-target="#drivers" type="button" role="tab">
                <i class="bi bi-person-badge me-1"></i> Drivers
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="vehicles-tab" data-bs-toggle="pill" data-bs-target="#vehicles" type="button" role="tab">
                <i class="bi bi-car-front me-1"></i> Vehicles
            </button>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content" id="logisticsTabContent">

        {{-- ==================== SHIPMENTS TAB ==================== --}}
        <div class="tab-pane fade show active" id="shipments" role="tabpanel">
            {{-- Filters --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('operations.logistics.index') }}" class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control" placeholder="Search Shipment#, Route..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select name="direction" class="form-select">
                                <option value="">All Directions</option>
                                <option value="inbound" {{ request('direction') == 'inbound' ? 'selected' : '' }}>Inbound</option>
                                <option value="outbound" {{ request('direction') == 'outbound' ? 'selected' : '' }}>Outbound</option>
                                <option value="return" {{ request('direction') == 'return' ? 'selected' : '' }}>Return</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="delayed" {{ request('status') == 'delayed' ? 'selected' : '' }}>Delayed</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-outline-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newShipmentModal">
                                <i class="bi bi-plus-lg me-1"></i> New Shipment
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Shipments Table --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Shipment #</th>
                                    <th>Direction</th>
                                    <th>Route</th>
                                    <th>Carrier</th>
                                    <th>Driver / Vehicle</th>
                                    <th>ETA</th>
                                    <th>Freight</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shipments as $shipment)
                                    <tr class="{{ $shipment->is_overdue ? 'table-danger' : '' }}">
                                        <td class="ps-4 fw-bold text-primary">{{ $shipment->shipment_no }}</td>
                                        <td>
                                            @php
                                                $dirColors = ['inbound' => 'info', 'outbound' => 'primary', 'return' => 'warning'];
                                                $dirIcons = ['inbound' => 'bi-box-arrow-in-down', 'outbound' => 'bi-box-arrow-up', 'return' => 'bi-arrow-counterclockwise'];
                                            @endphp
                                            <span class="badge bg-{{ $dirColors[$shipment->direction] ?? 'secondary' }}">
                                                <i class="bi {{ $dirIcons[$shipment->direction] ?? 'bi-truck' }} me-1"></i>
                                                {{ ucfirst($shipment->direction) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="fw-semibold small">{{ Str::limit($shipment->origin, 15) }}</span>
                                                <i class="bi bi-arrow-right text-primary"></i>
                                                <span class="fw-semibold small">{{ Str::limit($shipment->destination, 15) }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($shipment->carrier)
                                                <span class="fw-semibold small">{{ $shipment->carrier->name }}</span>
                                            @else
                                                <span class="text-muted small">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($shipment->driver)
                                                <div class="small fw-semibold">{{ $shipment->driver->name }}</div>
                                            @endif
                                            @if($shipment->vehicle)
                                                <span class="badge bg-light text-dark border small">{{ $shipment->vehicle->reg_no }}</span>
                                            @endif
                                            @if(!$shipment->driver && !$shipment->vehicle)
                                                <span class="text-muted small">Unassigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($shipment->is_overdue)
                                                <span class="text-danger fw-bold small">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $shipment->eta_date->format('d M') }}
                                                </span>
                                                <br><small class="text-danger">{{ abs($shipment->days_until_eta) }}d overdue</small>
                                            @elseif($shipment->days_until_eta !== null && $shipment->days_until_eta <= 2)
                                                <span class="text-warning fw-bold small">{{ $shipment->eta_date->format('d M Y') }}</span>
                                                <br><small class="text-warning"><i class="bi bi-clock me-1"></i>{{ $shipment->days_until_eta }}d left</small>
                                            @else
                                                <span class="text-muted small">{{ $shipment->eta_date?->format('d M Y') }}</span>
                                            @endif
                                        </td>
                                        <td class="fw-semibold small">₹{{ number_format($shipment->freight_cost, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $shipment->status_color }}">{{ ucfirst(str_replace('_', ' ', $shipment->status)) }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <form method="POST" action="{{ route('operations.logistics.shipment.update-status', $shipment) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" class="form-select form-select-sm" style="width: 130px;" onchange="this.form.submit()">
                                                    <option value="pending" {{ $shipment->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="in_transit" {{ $shipment->status == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                                    <option value="delivered" {{ $shipment->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                                    <option value="delayed" {{ $shipment->status == 'delayed' ? 'selected' : '' }}>Delayed</option>
                                                    <option value="cancelled" {{ $shipment->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="bi bi-truck fs-1 d-block mb-2"></i>
                                            No shipments found. Click "New Shipment" to create one.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($shipments->hasPages())
                    <div class="card-footer bg-white border-top-0 pt-3">
                        {{ $shipments->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- ==================== CARRIERS TAB ==================== --}}
        <div class="tab-pane fade" id="carriers" role="tabpanel">
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCarrierModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Carrier
                </button>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Carrier Name</th>
                                    <th>Type</th>
                                    <th>Contact</th>
                                    <th>GSTIN</th>
                                    <th>Coverage</th>
                                    <th>Rating</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($carriers as $carrier)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $carrier->name }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $carrier->type ?? 'N/A' }}</span></td>
                                        <td>{{ $carrier->contact ?? 'N/A' }}</td>
                                        <td><code class="small">{{ $carrier->gstin ?? 'N/A' }}</code></td>
                                        <td>{{ $carrier->coverage ?? 'N/A' }}</td>
                                        <td>
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="bi bi-star{{ $i <= round($carrier->rating) ? '-fill' : '' }} text-warning" style="font-size: 0.85rem;"></i>
                                            @endfor
                                            <small class="text-muted ms-1">{{ $carrier->rating }}</small>
                                        </td>
                                        <td><span class="badge bg-{{ $carrier->status == 'active' ? 'success' : 'secondary' }}">{{ ucfirst($carrier->status) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-building fs-1 d-block mb-2"></i>No carriers found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($carriers->hasPages())
                    <div class="card-footer bg-white border-top-0 pt-3">{{ $carriers->links() }}</div>
                @endif
            </div>
        </div>

        {{-- ==================== DRIVERS TAB ==================== --}}
        <div class="tab-pane fade" id="drivers" role="tabpanel">
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newDriverModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Driver
                </button>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Driver Name</th>
                                    <th>License No</th>
                                    <th>Phone</th>
                                    <th>Vehicle Type</th>
                                    <th>Vehicle No</th>
                                    <th>Experience</th>
                                    <th>Rating</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($drivers as $driver)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $driver->name }}</td>
                                        <td><code class="small">{{ $driver->license }}</code></td>
                                        <td>{{ $driver->phone ?? 'N/A' }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $driver->vehicle_type ?? 'N/A' }}</span></td>
                                        <td>{{ $driver->vehicle_no ?? 'N/A' }}</td>
                                        <td>{{ $driver->experience_years }} yrs</td>
                                        <td>
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="bi bi-star{{ $i <= round($driver->rating) ? '-fill' : '' }} text-warning" style="font-size: 0.85rem;"></i>
                                            @endfor
                                        </td>
                                        <td>
                                            @php
                                                $driverStatusColors = ['active' => 'success', 'inactive' => 'secondary', 'on_leave' => 'warning'];
                                            @endphp
                                            <span class="badge bg-{{ $driverStatusColors[$driver->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $driver->status)) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="bi bi-person-badge fs-1 d-block mb-2"></i>No drivers found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($drivers->hasPages())
                    <div class="card-footer bg-white border-top-0 pt-3">{{ $drivers->links() }}</div>
                @endif
            </div>
        </div>

        {{-- ==================== VEHICLES TAB ==================== --}}
        <div class="tab-pane fade" id="vehicles" role="tabpanel">
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newVehicleModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Vehicle
                </button>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Reg No</th>
                                    <th>Type</th>
                                    <th>Model</th>
                                    <th>Capacity (kg)</th>
                                    <th>Fuel</th>
                                    <th>Year</th>
                                    <th>Insurance Expiry</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vehicles as $vehicle)
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">{{ $vehicle->reg_no }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $vehicle->type ?? 'N/A' }}</span></td>
                                        <td>{{ $vehicle->model ?? 'N/A' }}</td>
                                        <td>{{ number_format($vehicle->capacity) }}</td>
                                        <td>{{ $vehicle->fuel_type ?? 'N/A' }}</td>
                                        <td>{{ $vehicle->year ?? 'N/A' }}</td>
                                        <td>
                                            @if($vehicle->is_insurance_expiring)
                                                <span class="text-danger fw-bold small"><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $vehicle->insurance_expiry->format('d M Y') }}</span>
                                            @else
                                                <span class="text-muted small">{{ $vehicle->insurance_expiry?->format('d M Y') ?? 'N/A' }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $vehicleStatusColors = ['available' => 'success', 'in_transit' => 'primary', 'maintenance' => 'warning'];
                                            @endphp
                                            <span class="badge bg-{{ $vehicleStatusColors[$vehicle->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $vehicle->status)) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="bi bi-car-front fs-1 d-block mb-2"></i>No vehicles found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($vehicles->hasPages())
                    <div class="card-footer bg-white border-top-0 pt-3">{{ $vehicles->links() }}</div>
                @endif
            </div>
        </div>

    </div>

    {{-- ==================== MODALS ==================== --}}

    {{-- NEW SHIPMENT MODAL --}}
    <div class="modal fade" id="newShipmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('operations.logistics.shipment.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-truck me-2"></i>Create New Shipment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Direction *</label>
                            <select name="direction" class="form-select" required>
                                <option value="outbound">Outbound</option>
                                <option value="inbound">Inbound</option>
                                <option value="return">Return</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Origin *</label>
                            <input type="text" name="origin" class="form-control" placeholder="e.g., Mumbai Warehouse" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Destination *</label>
                            <input type="text" name="destination" class="form-control" placeholder="e.g., Delhi Hub" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Carrier</label>
                            <select name="carrier_id" class="form-select">
                                <option value="">Select Carrier</option>
                                @foreach($carriers as $carrier)
                                    <option value="{{ $carrier->id }}">{{ $carrier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Driver</label>
                            <select name="driver_id" class="form-select">
                                <option value="">Select Driver</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}">{{ $driver->name }} ({{ $driver->license }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Vehicle</label>
                            <select name="vehicle_id" class="form-select">
                                <option value="">Select Vehicle</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" {{ $vehicle->status !== 'available' ? 'disabled' : '' }}>
                                        {{ $vehicle->reg_no }} - {{ $vehicle->type }} ({{ ucfirst($vehicle->status) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Freight Cost (₹) *</label>
                            <input type="number" name="freight_cost" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">ETA Date *</label>
                            <input type="date" name="eta_date" class="form-control" min="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Notes</label>
                            <input type="text" name="notes" class="form-control" placeholder="Special instructions...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Shipment</button>
                </div>
            </form>
        </div>
    </div>

    {{-- NEW CARRIER MODAL --}}
    <div class="modal fade" id="newCarrierModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('operations.logistics.carrier.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-building me-2"></i>Add New Carrier</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Carrier Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Type</label>
                            <select name="type" class="form-select">
                                <option value="Road">Road</option>
                                <option value="Air">Air</option>
                                <option value="Rail">Rail</option>
                                <option value="Sea">Sea</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contact</label>
                            <input type="text" name="contact" class="form-control" placeholder="Phone number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">GSTIN</label>
                            <input type="text" name="gstin" class="form-control" maxlength="15">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Coverage Area</label>
                            <input type="text" name="coverage" class="form-control" placeholder="e.g., Pan India">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Rating (1-5)</label>
                            <input type="number" name="rating" class="form-control" min="1" max="5" step="0.1" value="5">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Carrier</button>
                </div>
            </form>
        </div>
    </div>

    {{-- NEW DRIVER MODAL --}}
    <div class="modal fade" id="newDriverModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('operations.logistics.driver.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-person-badge me-2"></i>Add New Driver</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Driver Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">License No *</label>
                            <input type="text" name="license" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vehicle Type</label>
                            <input type="text" name="vehicle_type" class="form-control" placeholder="e.g., Truck">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vehicle No</label>
                            <input type="text" name="vehicle_no" class="form-control" placeholder="e.g., MH-12-AB-1234">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Capacity (kg)</label>
                            <input type="number" name="capacity" class="form-control" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Experience (Years)</label>
                            <input type="number" name="experience_years" class="form-control" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Rating (1-5)</label>
                            <input type="number" name="rating" class="form-control" min="1" max="5" step="0.1" value="5">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Driver</button>
                </div>
            </form>
        </div>
    </div>

    {{-- NEW VEHICLE MODAL --}}
    <div class="modal fade" id="newVehicleModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('operations.logistics.vehicle.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-car-front me-2"></i>Add New Vehicle</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Registration No *</label>
                            <input type="text" name="reg_no" class="form-control" placeholder="e.g., MH-12-AB-1234" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Type</label>
                            <select name="type" class="form-select">
                                <option value="Truck">Truck</option>
                                <option value="Van">Van</option>
                                <option value="Container">Container</option>
                                <option value="Tempo">Tempo</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Model</label>
                            <input type="text" name="model" class="form-control" placeholder="e.g., Tata 407">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Capacity (kg)</label>
                            <input type="number" name="capacity" class="form-control" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fuel Type</label>
                            <select name="fuel_type" class="form-select">
                                <option value="Diesel">Diesel</option>
                                <option value="Petrol">Petrol</option>
                                <option value="CNG">CNG</option>
                                <option value="Electric">Electric</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Year</label>
                            <input type="number" name="year" class="form-control" min="2000" max="2030">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Insurance Expiry</label>
                            <input type="date" name="insurance_expiry" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fitness Expiry</label>
                            <input type="date" name="fitness_expiry" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Vehicle</button>
                </div>
            </form>
        </div>
    </div>

@endsection