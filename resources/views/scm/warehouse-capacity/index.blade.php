@extends('layouts.master')
@section('title', 'Warehouse Capacity')
@section('page-title', 'Warehouse Capacity Management')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-4 mb-4">
        {{-- Circular SVG Gauge --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-speedometer2 me-2"></i>Overall Utilization</h6>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#configModal"><i class="bi bi-gear me-1"></i>Configure</button>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                    <svg width="200" height="200" viewBox="0 0 200 200">
                        <circle cx="100" cy="100" r="85" fill="none" stroke="#e9ecef" stroke-width="15"/>
                        @php
                            $dashArray = 2 * pi() * 85;
                            $dashOffset = $dashArray - ($dashArray * $utilizationPercent / 100);
                            $gaugeColor = $utilizationPercent >= 90 ? '#dc3545' : ($utilizationPercent >= 70 ? '#ffc107' : '#198754');
                        @endphp
                        <circle cx="100" cy="100" r="85" fill="none" stroke="{{ $gaugeColor }}" stroke-width="15"
                            stroke-dasharray="{{ $dashArray }}" stroke-dashoffset="{{ $dashOffset }}"
                            stroke-linecap="round" transform="rotate(-90 100 100)"
                            style="transition: stroke-dashoffset 1s ease-in-out;"/>
                        <text x="100" y="95" text-anchor="middle" font-size="32" font-weight="bold" fill="#1e293b">{{ $utilizationPercent }}%</text>
                        <text x="100" y="118" text-anchor="middle" font-size="12" fill="#6c757d">Utilized</text>
                    </svg>
                    <div class="mt-3 text-center">
                        <p class="mb-1"><strong>{{ $config->warehouse_name }}</strong></p>
                        <small class="text-muted">{{ number_format($config->total_area_sqft) }} sq ft | {{ $config->max_weight_tons }} tons max</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Capacity Bars by Type --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart me-2"></i>Capacity by Type</h6>
                </div>
                <div class="card-body">
                    @php
                        $typeConfig = [
                            'pallet' => ['label' => 'Pallet Storage', 'icon' => 'bi-palette', 'color' => 'primary'],
                            'shelf' => ['label' => 'Shelf Storage', 'icon' => 'bi-bookshelf', 'color' => 'info'],
                            'cold' => ['label' => 'Cold Storage', 'icon' => 'bi-snow', 'color' => 'success'],
                            'hazmat' => ['label' => 'Hazmat Storage', 'icon' => 'bi-exclamation-diamond', 'color' => 'danger'],
                        ];
                    @endphp
                    @foreach($capacityByType as $type => $data)
                        @php $pct = $data['total'] > 0 ? round(($data['used'] / $data['total']) * 100) : 0; @endphp
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold"><i class="bi {{ $typeConfig[$type]['icon'] }} me-2 text-{{ $typeConfig[$type]['color'] }}"></i>{{ $typeConfig[$type]['label'] }}</span>
                                <span class="small text-muted">{{ $data['used'] }} / {{ $data['total'] }} ({{ $pct }}%)</span>
                            </div>
                            <div class="progress" style="height: 12px;">
                                <div class="progress-bar bg-{{ $pct >= 90 ? 'danger' : ($pct >= 70 ? 'warning' : $typeConfig[$type]['color']) }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Bin Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6"><div class="card border-0 shadow-sm text-center py-3"><h4 class="fw-bold text-primary mb-0">{{ $binStats['total'] }}</h4><small class="text-muted">Total Bins</small></div></div>
        <div class="col-md-3 col-sm-6"><div class="card border-0 shadow-sm text-center py-3"><h4 class="fw-bold text-success mb-0">{{ $binStats['available'] }}</h4><small class="text-muted">Available</small></div></div>
        <div class="col-md-3 col-sm-6"><div class="card border-0 shadow-sm text-center py-3"><h4 class="fw-bold text-info mb-0">{{ $binStats['occupied'] }}</h4><small class="text-muted">Occupied</small></div></div>
        <div class="col-md-3 col-sm-6"><div class="card border-0 shadow-sm text-center py-3"><h4 class="fw-bold text-warning mb-0">{{ $binStats['reserved'] }}</h4><small class="text-muted">Reserved</small></div></div>
    </div>

    {{-- Bin Grid --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="bi bi-grid-3x3-gap me-2"></i>Bin Grid</h6>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBinModal"><i class="bi bi-plus-lg me-1"></i> Add Bin</button>
        </div>
        <div class="card-body">
            <div class="row g-2">
                @forelse($bins as $bin)
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <div class="card border border-{{ $bin->status_color }} border-opacity-50 h-100">
                            <div class="card-body p-3 text-center">
                                <i class="bi {{ $bin->type_icon }} fs-3 text-{{ $bin->status_color }} d-block mb-2"></i>
                                <h6 class="fw-bold mb-1">{{ $bin->bin_code }}</h6>
                                <small class="text-muted d-block mb-2">{{ ucfirst($bin->type) }} | {{ $bin->zone ?? 'N/A' }}</small>
                                <div class="progress mb-2" style="height: 6px;">
                                    <div class="progress-bar bg-{{ $bin->utilization_color }}" style="width: {{ $bin->utilization }}%"></div>
                                </div>
                                <small class="fw-semibold">{{ $bin->current_qty }}/{{ $bin->max_capacity }}</small>
                                <br><span class="badge bg-{{ $bin->status_color }} bg-opacity-10 text-{{ $bin->status_color }} mt-1">{{ ucfirst($bin->status) }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5 text-muted">
                        <i class="bi bi-grid-3x3-gap fs-1 d-block mb-2"></i>No bins configured. Click "Add Bin" to create storage locations.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ADD BIN MODAL --}}
    <div class="modal fade" id="addBinModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('scm.warehouse-capacity.store-bin') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Storage Bin</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fw-semibold">Bin Code *</label><input type="text" name="bin_code" class="form-control" placeholder="e.g., A-01-01" required></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Type *</label><select name="type" class="form-select" required><option value="shelf">Shelf</option><option value="pallet">Pallet</option><option value="cold">Cold</option><option value="hazmat">Hazmat</option></select></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Zone</label><input type="text" name="zone" class="form-control" placeholder="A"></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Aisle</label><input type="text" name="aisle" class="form-control" placeholder="01"></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Rack</label><input type="text" name="rack" class="form-control" placeholder="01"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Max Capacity *</label><input type="number" name="max_capacity" class="form-control" min="1" required></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Temperature Zone</label><select name="temperature_zone" class="form-select"><option value="">N/A</option><option value="ambient">Ambient</option><option value="chilled">Chilled</option><option value="frozen">Frozen</option></select></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Bin</button></div>
            </form>
        </div>
    </div>

    {{-- CONFIGURE CAPACITY MODAL --}}
    <div class="modal fade" id="configModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('scm.warehouse-capacity.update-config') }}" class="modal-content">
                @csrf @method('PUT')
                <div class="modal-header bg-warning"><h5 class="modal-title"><i class="bi bi-gear me-2"></i>Configure Warehouse Capacity</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fw-semibold">Warehouse Name *</label><input type="text" name="warehouse_name" class="form-control" value="{{ $config->warehouse_name }}" required></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Total Area (sq ft) *</label><input type="number" name="total_area_sqft" class="form-control" value="{{ $config->total_area_sqft }}" required></div>
                        <div class="col-md-3"><label class="form-label fw-semibold">Pallet Capacity</label><input type="number" name="pallet_capacity" class="form-control" value="{{ $config->pallet_capacity }}"></div>
                        <div class="col-md-3"><label class="form-label fw-semibold">Shelf Capacity</label><input type="number" name="shelf_capacity" class="form-control" value="{{ $config->shelf_capacity }}"></div>
                        <div class="col-md-3"><label class="form-label fw-semibold">Cold Capacity</label><input type="number" name="cold_capacity" class="form-control" value="{{ $config->cold_capacity }}"></div>
                        <div class="col-md-3"><label class="form-label fw-semibold">Hazmat Capacity</label><input type="number" name="hazmat_capacity" class="form-control" value="{{ $config->hazmat_capacity }}"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Max Weight (Tons)</label><input type="number" name="max_weight_tons" class="form-control" value="{{ $config->max_weight_tons }}" step="0.1"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-warning">Save Configuration</button></div>
            </form>
        </div>
    </div>
@endsection