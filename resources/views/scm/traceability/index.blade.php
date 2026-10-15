@extends('layouts.master')
@section('title', 'Traceability')
@section('page-title', 'End-to-End Traceability')

@section('content')
    <style>
        /* Premium Timeline Styles */
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 24px;
        }
        .timeline-item:last-child { margin-bottom: 0; }
        .timeline-icon {
            position: absolute;
            left: -38px;
            top: 0;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            border: 3px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 2;
        }
        .timeline-content {
            background: #f8fafc;
            border-radius: 12px;
            padding: 16px;
            border: 1px solid rgba(0,0,0,0.04);
        }
        .timeline-content h6 { margin-bottom: 8px; }
        .timeline-detail {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            margin-bottom: 4px;
        }
        .timeline-detail span:first-child { color: #64748b; }
        .timeline-detail span:last-child { font-weight: 600; color: #1e293b; }
    </style>

    {{-- ==================== FILTERS ==================== --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('scm.traceability.index') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold text-muted mb-1">Search Batch or Product</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="e.g., BATCH-PRD-LAPTOP-001" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active / In Stock</option>
                        <option value="consumed" {{ request('status') == 'consumed' ? 'selected' : '' }}>Consumed / Sold</option>
                        <option value="quarantine" {{ request('status') == 'quarantine' ? 'selected' : '' }}>Quarantine</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('scm.traceability.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== TRACEABILITY TABLE ==================== --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0"><i class="bi bi-diagram-3 me-2 text-primary"></i>Batch Tracking Ledger</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Batch No.</th>
                            <th>Product</th>
                            <th>Current Location</th>
                            <th class="text-center">Qty</th>
                            <th>Mfg / Receipt</th>
                            <th>Expiry</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-primary small">{{ $batch->batch_no }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold small">{{ $batch->product->name ?? 'Unknown' }}</div>
                                    <small class="text-muted">{{ $batch->product->sku ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    @if($batch->warehouseBin)
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-geo-alt me-1"></i>
                                            {{ $batch->warehouseBin->zone }}-{{ $batch->warehouseBin->aisle }}-{{ $batch->warehouseBin->rack }}
                                        </span>
                                    @else
                                        <span class="text-muted small">In Transit / N/A</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">{{ $batch->current_qty }} <small class="text-muted fw-normal">{{ $batch->product->unit ?? '' }}</small></td>
                                <td class="text-muted small">{{ $batch->mfg_date?->format('d M Y') ?? 'N/A' }}</td>
                                <td class="text-muted small">{{ $batch->exp_date?->format('d M Y') ?? 'N/A' }}</td>
                                <td>
                                    @php
                                        $statusColors = ['active' => 'success', 'consumed' => 'secondary', 'quarantine' => 'danger', 'pending' => 'warning'];
                                        $color = $statusColors[$batch->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }}">
                                        {{ ucfirst($batch->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary view-journey-btn" data-batch-id="{{ $batch->id }}" data-bs-toggle="modal" data-bs-target="#journeyModal">
                                        <i class="bi bi-eye me-1"></i> View Journey
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-search fs-1 d-block mb-2"></i>
                                    No batches found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($batches->hasPages())
            <div class="card-footer bg-white border-top-0 pt-3">{{ $batches->links() }}</div>
        @endif
    </div>

    {{-- ==================== JOURNEY MODAL ==================== --}}
    <div class="modal fade" id="journeyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-diagram-3 me-2"></i>Batch Journey Timeline</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="journeyModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Loading journey data...</p>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.view-journey-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const batchId = this.dataset.batchId;
            const modalBody = document.getElementById('journeyModalBody');
            
            // Show loading state
            modalBody.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Loading journey data...</p>
                </div>
            `;

            // Fetch journey data
            fetch(`/scm/traceability/journey/${batchId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.timeline.length === 0) {
                        modalBody.innerHTML = `
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                <p>No journey records found for this batch.</p>
                            </div>
                        `;
                        return;
                    }

                    let html = `
                        <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-light rounded-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                                <i class="bi bi-box-seam fs-3"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1 text-dark">${data.batch.product.name}</h5>
                                <small class="text-muted">Batch: <strong>${data.batch.batch_no}</strong> | SKU: ${data.batch.product.sku}</small>
                            </div>
                        </div>
                        <div class="timeline">
                    `;

                    data.timeline.forEach(step => {
                        const date = new Date(step.date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                        
                        let detailsHtml = '';
                        for (const [key, value] of Object.entries(step.details)) {
                            detailsHtml += `
                                <div class="timeline-detail">
                                    <span>${key}:</span>
                                    <span>${value}</span>
                                </div>
                            `;
                        }

                        html += `
                            <div class="timeline-item">
                                <div class="timeline-icon bg-${step.color} text-white">
                                    <i class="bi ${step.icon}"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0 text-dark">${step.title}</h6>
                                        <small class="text-muted fw-semibold">${date}</small>
                                    </div>
                                    ${detailsHtml}
                                </div>
                            </div>
                        `;
                    });

                    html += `</div>`;
                    modalBody.innerHTML = html;
                })
                .catch(err => {
                    console.error(err);
                    modalBody.innerHTML = `
                        <div class="text-center py-4 text-danger">
                            <i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i>
                            <p>Failed to load journey data. Please try again.</p>
                        </div>
                    `;
                });
        });
    });
</script>
@endpush