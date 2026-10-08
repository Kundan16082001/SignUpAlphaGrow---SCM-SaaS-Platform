@extends('layouts.master')
@section('title', 'BOM Details')
@section('page-title', 'BOM: ' . $bom->bom_no)

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- BOM Header Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                            <i class="bi bi-diagram-3 fs-2"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1">{{ $bom->bom_no }}</h4>
                            <p class="text-muted mb-0">{{ $bom->product_name }} ({{ $bom->product_sku }})</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <span class="badge bg-{{ $bom->status_color }} fs-6 px-3 py-2">{{ ucfirst($bom->status) }}</span>
                        <span class="badge bg-light text-dark border fs-6 px-3 py-2">Version {{ $bom->version }}</span>
                        <span class="badge bg-info bg-opacity-10 text-info fs-6 px-3 py-2">
                            <i class="bi bi-box-seam me-1"></i>{{ $bom->components_count }} Components
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <h2 class="fw-bold text-success mb-1">₹{{ number_format($bom->unit_cost, 2) }}</h2>
                    <p class="text-muted mb-3">Total Unit Cost</p>
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('operations.bom.edit', $bom) }}" class="btn btn-warning">
                            <i class="bi bi-pencil me-1"></i> Edit (New Version)
                        </a>
                        <a href="{{ route('operations.bom.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Component Breakdown Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-list-check me-2"></i>Component Breakdown & Stock Availability</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Component SKU</th>
                            <th>Component Name</th>
                            <th class="text-center">Qty Required</th>
                            <th>Unit</th>
                            <th class="text-end">Unit Cost</th>
                            <th class="text-end">Total Cost</th>
                            <th class="text-center">Wastage %</th>
                            <th class="text-center">Available Stock</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($componentsWithStock as $index => $comp)
                            <tr>
                                <td class="ps-4 fw-bold text-muted">{{ $index + 1 }}</td>
                                <td><code class="fw-semibold">{{ $comp->component_sku }}</code></td>
                                <td class="fw-semibold">{{ $comp->component_name }}</td>
                                <td class="text-center fw-bold">{{ number_format($comp->qty_required, 3) }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $comp->unit }}</span></td>
                                <td class="text-end">₹{{ number_format($comp->unit_cost, 2) }}</td>
                                <td class="text-end fw-bold">₹{{ number_format($comp->total_cost, 2) }}</td>
                                <td class="text-center">
                                    @if($comp->wastage_percentage > 0)
                                        <span class="badge bg-warning bg-opacity-10 text-warning">{{ $comp->wastage_percentage }}%</span>
                                    @else
                                        <span class="text-muted">0%</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold {{ $comp->is_sufficient ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($comp->available_stock, 0) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($comp->is_sufficient)
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-check-circle-fill me-1"></i>Sufficient
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Short by {{ number_format($comp->shortage, 3) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="6" class="text-end fw-bold ps-4">Total Components: {{ $componentsWithStock->count() }}</td>
                            <td class="text-end fw-bold fs-5 text-primary">₹{{ number_format($bom->unit_cost, 2) }}</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Stock Summary --}}
    @php
        $sufficientCount = $componentsWithStock->where('is_sufficient', true)->count();
        $shortageCount = $componentsWithStock->where('is_sufficient', false)->count();
    @endphp
    <div class="row g-3 mt-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-check-circle-fill fs-2 text-success"></i>
                        <div>
                            <h5 class="fw-bold mb-0 text-success">{{ $sufficientCount }} Components</h5>
                            <small class="text-muted">Stock is sufficient for production</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-exclamation-triangle-fill fs-2 text-danger"></i>
                        <div>
                            <h5 class="fw-bold mb-0 text-danger">{{ $shortageCount }} Components</h5>
                            <small class="text-muted">Need to procure before production</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection