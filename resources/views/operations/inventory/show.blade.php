@extends('layouts.master')
@section('title', $product->name)
@section('page-title', $product->name)

@section('content')
    {{-- ==================== HEADER ==================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('operations.inventory.index') }}" class="text-muted small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Back to Inventory
            </a>
            <h4 class="fw-bold mt-2 mb-1 text-dark">{{ $product->name }}</h4>
            <div class="d-flex gap-2">
                <span class="badge bg-light text-dark border font-monospace">{{ $product->sku }}</span>
                <span class="badge bg-light text-dark border">{{ $product->category }}</span>
                <span class="badge bg-light text-dark border">{{ $product->unit }}</span>
            </div>
        </div>
    </div>

    {{-- ==================== PRODUCT STATS ==================== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Total Stock</small>
                <h3 class="fw-bold mb-0">{{ $product->stock_qty }} {{ $product->unit }}</h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Unit Cost</small>
                <h3 class="fw-bold mb-0">₹{{ number_format($product->cost_price, 2) }}</h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Selling Price</small>
                <h3 class="fw-bold mb-0 text-success">₹{{ number_format($product->price, 2) }}</h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Total Value</small>
                <h3 class="fw-bold mb-0 text-primary">₹{{ number_format($product->stock_qty * $product->cost_price, 0) }}</h3>
            </div>
        </div>
    </div>

    {{-- ==================== WAREHOUSE BREAKDOWN ==================== --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0"><i class="bi bi-buildings me-2"></i>Stock by Warehouse</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Warehouse</th>
                            <th>Location</th>
                            <th class="text-center">Total Stock</th>
                            <th class="text-center">Batches</th>
                            <th class="text-end">Value</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warehouseStock as $stock)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold">{{ $stock['warehouse_name'] }}</div>
                                    <small class="text-muted font-monospace">{{ $stock['warehouse_code'] }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-geo-alt text-muted"></i>
                                        <span>{{ $stock['warehouse_city'] }}, {{ $stock['warehouse_state'] }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold fs-5">{{ $stock['total_qty'] }}</span>
                                    <small class="text-muted d-block">{{ $product->unit }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ count($stock['batches']) }} batches</span>
                                </td>
                                <td class="text-end fw-bold">₹{{ number_format($stock['total_qty'] * $product->cost_price, 0) }}</td>
                                                                <td class="text-end pe-4">
                                    @if($stock['warehouse'])
                                        <a href="{{ route('operations.warehouse-bins.index', $stock['warehouse']) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-grid-3x3-gap me-1"></i> View Bins
                                        </a>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No stock in any warehouse.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ==================== BATCH DETAILS ==================== --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0"><i class="bi bi-box-seam me-2"></i>Batch Details</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Batch No.</th>
                            <th>Warehouse</th>
                            <th class="text-center">Qty</th>
                            <th>Mfg Date</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($product->batches as $batch)
                            <tr>
                                <td class="ps-4 fw-bold font-monospace small">{{ $batch->batch_no }}</td>
                                <td>
                                    <div class="fw-semibold small">{{ $batch->warehouse->name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $batch->warehouse->city ?? '' }}</small>
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
                                <td colspan="6" class="text-center py-4 text-muted">No batch records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection