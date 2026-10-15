@extends('layouts.master')
@section('title', $warehouse->name)
@section('page-title', $warehouse->name)

@section('content')
    {{-- ==================== HEADER ==================== --}}
    {{-- Find this section and add the button --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('operations.warehouses.index') }}" class="text-muted small text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Back to Warehouses
        </a>
        <div class="d-flex align-items-center gap-3 mt-2">
            <div class="warehouse-icon bg-{{ $warehouse->type_color }} bg-opacity-10 text-{{ $warehouse->type_color }}" style="width:56px; height:56px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:1.6rem;">
                <i class="bi {{ $warehouse->type_icon }}"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-1 text-dark">{{ $warehouse->name }}</h4>
                <small class="text-muted font-monospace">{{ $warehouse->code }} · {{ ucfirst(str_replace('_', ' ', $warehouse->type)) }}</small>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        {{-- ✅ NEW: Manage Bins Button --}}
        <a href="{{ route('operations.warehouse-bins.index', $warehouse) }}" class="btn btn-primary">
            <i class="bi bi-grid-3x3-gap me-1"></i> Manage Bins
        </a>
        <a href="{{ route('operations.warehouses.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

    {{-- ==================== STATS ROW ==================== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Total Bins</small>
                <h3 class="fw-bold mb-0">{{ $stats['total_bins'] }}</h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Occupied</small>
                <h3 class="fw-bold mb-0 text-warning">{{ $stats['occupied_bins'] }}</h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Available</small>
                <h3 class="fw-bold mb-0 text-success">{{ $stats['available_bins'] }}</h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Stock Value</small>
                <h3 class="fw-bold mb-0 text-primary">₹{{ number_format($stats['stock_value'] / 1000, 1) }}K</h3>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- ==================== LEFT: LOCATION & MANAGER ==================== --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-geo-alt me-2"></i>Location</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2 text-muted small">
                        <i class="bi bi-pin-map me-1"></i> {{ $warehouse->full_address }}
                    </p>
                    @if($warehouse->latitude && $warehouse->longitude)
                        <a href="https://www.google.com/maps?q={{ $warehouse->latitude }},{{ $warehouse->longitude }}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-map me-1"></i> Open in Google Maps
                        </a>
                    @endif
                </div>
            </div>

            @if($warehouse->manager_name)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0"><i class="bi bi-person-badge me-2"></i>Manager</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:48px; height:48px;">
                                {{ strtoupper(substr($warehouse->manager_name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-bold">{{ $warehouse->manager_name }}</div>
                                <small class="text-muted">Warehouse Manager</small>
                            </div>
                        </div>
                        @if($warehouse->manager_phone)
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-telephone text-muted"></i>
                                <a href="tel:{{ $warehouse->manager_phone }}" class="text-decoration-none">{{ $warehouse->manager_phone }}</a>
                            </div>
                        @endif
                        @if($warehouse->manager_email)
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-envelope text-muted"></i>
                                <a href="mailto:{{ $warehouse->manager_email }}" class="text-decoration-none">{{ $warehouse->manager_email }}</a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-speedometer me-2"></i>Utilization</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h2 class="fw-bold mb-0" style="color: {{ $stats['utilization'] > 85 ? '#ef4444' : ($stats['utilization'] > 60 ? '#f59e0b' : '#10b981') }};">
                            {{ $stats['utilization'] }}%
                        </h2>
                        <small class="text-muted">Capacity Utilization</small>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Pallet</span>
                            <span class="fw-bold">{{ $warehouse->pallet_capacity }}</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Shelf</span>
                            <span class="fw-bold">{{ $warehouse->shelf_capacity }}</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Cold</span>
                            <span class="fw-bold">{{ $warehouse->cold_capacity }}</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span>Hazmat</span>
                            <span class="fw-bold">{{ $warehouse->hazmat_capacity }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== RIGHT: BINS & BATCHES ==================== --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-grid-3x3-gap me-2"></i>Warehouse Bins</h6>
                    <span class="badge bg-primary">{{ $bins->total() }} bins</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Bin Code</th>
                                    <th>Type</th>
                                    <th>Zone-Aisle-Rack</th>
                                    <th class="text-center">Current / Max</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bins as $bin)
                                    <tr>
                                        <td class="ps-4 fw-bold font-monospace small">{{ $bin->bin_code }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ ucfirst($bin->type) }}</span></td>
                                        <td class="text-muted small">{{ $bin->zone }}-{{ $bin->aisle }}-{{ $bin->rack }}</td>
                                        <td class="text-center">
                                            <span class="fw-bold">{{ $bin->current_qty }}</span>
                                            <span class="text-muted">/ {{ $bin->max_capacity }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $colors = ['available' => 'success', 'occupied' => 'warning', 'reserved' => 'info'];
                                                $color = $colors[$bin->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }}">{{ ucfirst($bin->status) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            No bins configured for this warehouse.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($bins->hasPages())
                    <div class="card-footer bg-white border-top-0 pt-3">{{ $bins->links() }}</div>
                @endif
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-box-seam me-2"></i>Recent Batches</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Batch No.</th>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th>Mfg Date</th>
                                    <th>Expiry</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentBatches as $batch)
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary small font-monospace">{{ $batch->batch_no }}</td>
                                        <td>
                                            <div class="fw-semibold small">{{ $batch->product->name ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $batch->product->sku ?? '' }}</small>
                                        </td>
                                        <td class="text-center fw-bold">{{ $batch->current_qty }}</td>
                                        <td class="text-muted small">{{ $batch->mfg_date?->format('d M Y') ?? 'N/A' }}</td>
                                        <td class="text-muted small">{{ $batch->exp_date?->format('d M Y') ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $colors = ['active' => 'success', 'consumed' => 'secondary', 'quarantine' => 'danger'];
                                                $color = $colors[$batch->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }}">{{ ucfirst($batch->status) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No batches in this warehouse.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection