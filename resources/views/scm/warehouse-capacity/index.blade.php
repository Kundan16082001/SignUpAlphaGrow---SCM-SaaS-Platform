@extends('layouts.master')
@section('title', 'Warehouse Capacity')
@section('page-title', 'Warehouse Capacity Management')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        .utilization-ring {
            position: relative;
            width: 120px;
            height: 120px;
        }
        .utilization-ring canvas {
            position: absolute;
            top: 0;
            left: 0;
        }
        .utilization-ring .center-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }
        .warehouse-compare-card {
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.04);
        }
        .warehouse-compare-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -8px rgba(0,0,0,0.1);
        }
        .zone-bar {
            height: 28px;
            background: #f1f5f9;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }
        .zone-fill {
            height: 100%;
            border-radius: 8px;
            transition: width 0.8s ease;
            display: flex;
            align-items: center;
            padding-left: 10px;
            color: white;
            font-weight: 600;
            font-size: 0.75rem;
        }
        .type-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
    </style>

    {{-- ==================== FILTER ==================== --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('scm.warehouse-capacity.index') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted mb-1">Filter by Warehouse</label>
                    <select name="warehouse_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Warehouses (Consolidated View)</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }} ({{ $wh->city }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 text-end">
                    @if($selectedWarehouse)
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                            <i class="bi bi-geo-alt me-1"></i> Viewing: {{ $selectedWarehouse->name }}
                        </span>
                        <a href="{{ route('scm.warehouse-capacity.index') }}" class="btn btn-sm btn-outline-secondary ms-2">
                            <i class="bi bi-x-lg"></i> Clear Filter
                        </a>
                    @else
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                            <i class="bi bi-buildings me-1"></i> Viewing: All {{ $warehouses->count() }} Warehouses
                        </span>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== OVERALL STATS ==================== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="type-icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </div>
                </div>
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Total Bins</small>
                <h3 class="fw-bold mb-0">{{ number_format($overallStats['total_bins']) }}</h3>
                <small class="text-muted">{{ $overallStats['available_bins'] }} available</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="type-icon-box bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                </div>
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Utilization</small>
                <h3 class="fw-bold mb-0" style="color: {{ $overallStats['utilization'] > 85 ? '#ef4444' : ($overallStats['utilization'] > 60 ? '#f59e0b' : '#10b981') }}">
                    {{ $overallStats['utilization'] }}%
                </h3>
                <small class="text-muted">{{ number_format($overallStats['used_capacity']) }} / {{ number_format($overallStats['total_capacity']) }}</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="type-icon-box bg-success bg-opacity-10 text-success">
                        <i class="bi bi-currency-rupee"></i>
                    </div>
                </div>
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Stock Value</small>
                <h3 class="fw-bold mb-0">₹{{ number_format($overallStats['stock_value'] / 100000, 1) }}L</h3>
                <small class="text-muted">Across all bins</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="type-icon-box bg-info bg-opacity-10 text-info">
                        <i class="bi bi-rulers"></i>
                    </div>
                </div>
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Total Area</small>
                <h3 class="fw-bold mb-0">{{ number_format($overallStats['total_area']) }}</h3>
                <small class="text-muted">Square feet</small>
            </div>
        </div>
    </div>

    {{-- ==================== CHARTS ROW ==================== --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4">
                <h6 class="fw-bold mb-4"><i class="bi bi-pie-chart me-2 text-primary"></i>Bin Status Distribution</h6>
                <div style="height: 260px;">
                    <canvas id="binStatusChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm p-4">
                <h6 class="fw-bold mb-4"><i class="bi bi-bar-chart me-2 text-success"></i>Bin Type Utilization</h6>
                <div style="height: 260px;">
                    <canvas id="binTypeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== WAREHOUSE COMPARISON (Only if no filter) ==================== --}}
    @if(!$selectedWarehouse && $warehouseStats->count() > 1)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-buildings me-2 text-primary"></i>Warehouse Comparison</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    @foreach($warehouseStats as $ws)
                        <div class="col-lg-4 col-md-6">
                            <div class="warehouse-compare-card rounded-3 p-3 h-100">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ $ws['warehouse']->name }}</h6>
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt me-1"></i>{{ $ws['warehouse']->city }}
                                        </small>
                                    </div>
                                    <span class="badge bg-{{ $ws['warehouse']->type_color }} bg-opacity-10 text-{{ $ws['warehouse']->type_color }}">
                                        {{ ucfirst(str_replace('_', ' ', $ws['warehouse']->type)) }}
                                    </span>
                                </div>

                                {{-- Utilization Ring --}}
                                <div class="text-center my-3">
                                    <div class="utilization-ring mx-auto">
                                        <canvas id="ring-{{ $ws['warehouse']->id }}" width="120" height="120"></canvas>
                                        <div class="center-text">
                                            <div class="fw-bold fs-4" style="color: {{ $ws['utilization'] > 85 ? '#ef4444' : ($ws['utilization'] > 60 ? '#f59e0b' : '#10b981') }}">
                                                {{ $ws['utilization'] }}%
                                            </div>
                                            <small class="text-muted">Utilized</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 text-center mt-3">
                                    <div class="col-4">
                                        <div class="fw-bold text-success">{{ $ws['available'] }}</div>
                                        <small class="text-muted" style="font-size: 0.7rem;">Available</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="fw-bold text-warning">{{ $ws['occupied'] }}</div>
                                        <small class="text-muted" style="font-size: 0.7rem;">Occupied</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="fw-bold text-info">{{ $ws['reserved'] }}</div>
                                        <small class="text-muted" style="font-size: 0.7rem;">Reserved</small>
                                    </div>
                                </div>

                                <div class="mt-3 pt-3 border-top">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-muted">Stock Value</span>
                                        <span class="fw-bold">₹{{ number_format($ws['stock_value'] / 1000, 1) }}K</span>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-muted">Total Bins</span>
                                        <span class="fw-bold">{{ $ws['total_bins'] }}</span>
                                    </div>
                                </div>

                                <a href="{{ route('operations.warehouse-bins.index', $ws['warehouse']) }}" class="btn btn-sm btn-outline-primary w-100 mt-3">
                                    <i class="bi bi-arrow-right me-1"></i> View Bins
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== ZONE-WISE UTILIZATION ==================== --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0"><i class="bi bi-map me-2 text-info"></i>Zone-wise Utilization</h6>
        </div>
        <div class="card-body p-4">
            @forelse($zoneStats as $zone)
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-dark text-white fw-bold" style="min-width: 40px;">{{ $zone->zone }}</span>
                            <small class="text-muted">{{ $zone->total_bins }} bins</small>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <small class="text-muted">
                                {{ number_format($zone->used_capacity) }} / {{ number_format($zone->total_capacity) }}
                            </small>
                            <span class="fw-bold" style="color: {{ $zone->utilization > 85 ? '#ef4444' : ($zone->utilization > 60 ? '#f59e0b' : '#10b981') }}; min-width: 50px; text-align: right;">
                                {{ $zone->utilization }}%
                            </span>
                        </div>
                    </div>
                    <div class="zone-bar">
                        <div class="zone-fill" style="width: {{ $zone->utilization }}%; background: {{ $zone->utilization > 85 ? '#ef4444' : ($zone->utilization > 60 ? '#f59e0b' : '#10b981') }};">
                            @if($zone->utilization > 15)
                                {{ $zone->utilization }}%
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No zone data available.
                </div>
            @endforelse
        </div>
    </div>

    {{-- ==================== BIN TYPE BREAKDOWN TABLE ==================== --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0"><i class="bi bi-grid-3x3 me-2 text-warning"></i>Bin Type Breakdown</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Bin Type</th>
                            <th class="text-center">Total Bins</th>
                            <th class="text-center">Total Capacity</th>
                            <th class="text-center">Used Capacity</th>
                            <th>Utilization</th>
                            <th class="text-end pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $typeConfig = [
                                'shelf' => ['icon' => 'bi-grid', 'color' => 'secondary', 'label' => 'Shelf'],
                                'pallet' => ['icon' => 'bi-box', 'color' => 'primary', 'label' => 'Pallet'],
                                'cold' => ['icon' => 'bi-snow', 'color' => 'info', 'label' => 'Cold Storage'],
                                'hazmat' => ['icon' => 'bi-exclamation-diamond', 'color' => 'danger', 'label' => 'Hazmat'],
                            ];
                        @endphp
                        @forelse($binTypeStats as $type)
                            @php $config = $typeConfig[$type->type] ?? ['icon' => 'bi-box', 'color' => 'secondary', 'label' => ucfirst($type->type)]; @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="type-icon-box bg-{{ $config['color'] }} bg-opacity-10 text-{{ $config['color'] }}">
                                            <i class="bi {{ $config['icon'] }}"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $config['label'] }}</div>
                                            <small class="text-muted">Storage type</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center fw-bold">{{ number_format($type->total_bins) }}</td>
                                <td class="text-center">{{ number_format($type->total_capacity) }}</td>
                                <td class="text-center">{{ number_format($type->used_capacity) }}</td>
                                <td style="width: 200px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-{{ $type->utilization > 85 ? 'danger' : ($type->utilization > 60 ? 'warning' : 'success') }}" 
                                                 style="width: {{ $type->utilization }}%"></div>
                                        </div>
                                        <small class="fw-bold" style="min-width: 45px;">{{ $type->utilization }}%</small>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    @if($type->utilization > 85)
                                        <span class="badge bg-danger bg-opacity-10 text-danger">Critical</span>
                                    @elseif($type->utilization > 60)
                                        <span class="badge bg-warning bg-opacity-10 text-warning">Moderate</span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success">Healthy</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No bin data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // ==================== BIN STATUS PIE CHART ====================
    const statusCtx = document.getElementById('binStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Available', 'Occupied', 'Reserved'],
            datasets: [{
                data: [
                    {{ $overallStats['available_bins'] }},
                    {{ $overallStats['occupied_bins'] }},
                    {{ $overallStats['reserved_bins'] }}
                ],
                backgroundColor: ['#10b981', '#f59e0b', '#0ea5e9'],
                borderWidth: 0,
                hoverOffset: 10,
                spacing: 4,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 15, usePointStyle: true, font: { size: 12 } }
                }
            }
        }
    });

    // ==================== BIN TYPE BAR CHART ====================
    const typeCtx = document.getElementById('binTypeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($binTypeStats->map(fn($t) => ucfirst($t->type))) !!},
            datasets: [
                {
                    label: 'Used Capacity',
                    data: {!! json_encode($binTypeStats->pluck('used_capacity')) !!},
                    backgroundColor: 'rgba(99, 102, 241, 0.8)',
                    borderRadius: 8,
                    borderSkipped: false,
                },
                {
                    label: 'Available Capacity',
                    data: {!! json_encode($binTypeStats->map(fn($t) => $t->total_capacity - $t->used_capacity)) !!},
                    backgroundColor: 'rgba(226, 232, 240, 0.8)',
                    borderRadius: 8,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 15, usePointStyle: true, font: { size: 12 } }
                }
            },
            scales: {
                x: {
                    stacked: true,
                    border: { display: false },
                    grid: { display: false }
                },
                y: {
                    stacked: true,
                    border: { display: false },
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    beginAtZero: true
                }
            }
        }
    });

    // ==================== UTILIZATION RINGS FOR EACH WAREHOUSE ====================
    @if(!$selectedWarehouse)
        @foreach($warehouseStats as $ws)
            (function() {
                const ctx = document.getElementById('ring-{{ $ws['warehouse']->id }}').getContext('2d');
                const utilization = {{ $ws['utilization'] }};
                const color = utilization > 85 ? '#ef4444' : (utilization > 60 ? '#f59e0b' : '#10b981');
                
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [utilization, 100 - utilization],
                            backgroundColor: [color, '#f1f5f9'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: false,
                        cutout: '80%',
                        plugins: { legend: { display: false }, tooltip: { enabled: false } }
                    }
                });
            })();
        @endforeach
    @endif
</script>
@endpush