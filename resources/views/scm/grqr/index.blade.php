@extends('layouts.master')
@section('title', 'GR/QR Posting')
@section('page-title', 'Goods & Quality Receipt')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ==================== KPI CARDS ==================== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75 fw-bold text-uppercase">Pending Receipts</p>
                        <h3 class="fw-bold mb-0">{{ $stats['pending_gr'] }}</h3>
                    </div>
                    <i class="bi bi-hourglass-split fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75 fw-bold text-uppercase">Posted Receipts</p>
                        <h3 class="fw-bold mb-0">{{ $stats['posted_gr'] }}</h3>
                    </div>
                    <i class="bi bi-check-circle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75 fw-bold text-uppercase">Total Received</p>
                        <h3 class="fw-bold mb-0">{{ number_format($stats['total_received']) }}</h3>
                        <small class="opacity-75">Units Added to Stock</small>
                    </div>
                    <i class="bi bi-box-seam fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-danger text-white h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75 fw-bold text-uppercase">Damaged / Rejected</p>
                        <h3 class="fw-bold mb-0">{{ number_format($stats['total_damaged']) }}</h3>
                        <small class="opacity-75">QC Failed Units</small>
                    </div>
                    <i class="bi bi-x-circle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== HEADER & ACTIONS ==================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <ul class="nav nav-pills" id="grqrTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#pending">
                    <i class="bi bi-hourglass-split me-1"></i> Pending
                    <span class="badge bg-warning text-dark ms-1">{{ $stats['pending_gr'] }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#posted">
                    <i class="bi bi-clock-history me-1"></i> History
                    <span class="badge bg-success ms-1">{{ $stats['posted_gr'] }}</span>
                </button>
            </li>
        </ul>
        {{-- ✅ NEW: Create Goods Receipt Button --}}
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#postGrqrModal" onclick="resetModalForCreate()">
            <i class="bi bi-plus-lg me-1"></i> Create Goods Receipt
        </button>
    </div>

    {{-- ==================== TAB CONTENT ==================== --}}
    <div class="tab-content">
        {{-- PENDING TAB --}}
        <div class="tab-pane fade show active" id="pending">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Posting #</th>
                                    <th>Shipment</th>
                                    <th>Batch / Product</th>
                                    <th class="text-center">Expected Qty</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingGr as $gr)
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">{{ $gr->posting_no }}</td>
                                        <td>
                                            @if($gr->shipment)
                                                <span class="fw-semibold small">{{ $gr->shipment->shipment_no }}</span>
                                                <br><small class="text-muted">{{ $gr->shipment->origin }} → {{ $gr->shipment->destination }}</small>
                                            @else
                                                <span class="text-muted small">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $gr->batch->batch_no }}</div>
                                            <small class="text-muted">{{ $gr->batch->product->name ?? 'Unknown' }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border fs-6">{{ $gr->expected_qty }}</span>
                                        </td>
                                        <td><span class="badge bg-warning bg-opacity-10 text-warning">Pending</span></td>
                                        <td class="text-muted small">{{ $gr->created_at->diffForHumans() }}</td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#postGrqrModal"
                                                data-shipment="{{ $gr->shipment_id }}"
                                                data-batch="{{ $gr->batch_id }}"
                                                data-batchno="{{ $gr->batch->batch_no }}"
                                                data-product="{{ $gr->batch->product->name ?? 'Unknown' }}"
                                                data-expected="{{ $gr->expected_qty }}"
                                                onclick="populateModal(this)">
                                                <i class="bi bi-pencil-square me-1"></i> Post GR/QR
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                                            All caught up! No pending receipts.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($pendingGr->hasPages())
                    <div class="card-footer bg-white border-top-0 pt-3">{{ $pendingGr->links() }}</div>
                @endif
            </div>
        </div>

        {{-- POSTED HISTORY TAB --}}
        <div class="tab-pane fade" id="posted">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Posting #</th>
                                    <th>Batch / Product</th>
                                    <th>Shipment</th>
                                    <th class="text-center">Expected</th>
                                    <th class="text-center">Received</th>
                                    <th class="text-center">Damaged</th>
                                    <th class="text-center">QC Result</th>
                                    <th class="text-center">QC Score</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($postedGrqr as $post)
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">{{ $post->posting_no }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $post->batch->batch_no }}</div>
                                            <small class="text-muted">{{ $post->batch->product->name ?? 'Unknown' }}</small>
                                        </td>
                                        <td><span class="small">{{ $post->shipment->shipment_no ?? 'N/A' }}</span></td>
                                        <td class="text-center text-muted">{{ $post->expected_qty }}</td>
                                        <td class="text-center fw-bold text-success">{{ $post->received_qty }}</td>
                                        <td class="text-center">
                                            @if($post->damaged_qty > 0)
                                                <span class="fw-bold text-danger">{{ $post->damaged_qty }}</span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $qcColors = ['pass' => 'success', 'fail' => 'danger', 'pending' => 'warning'];
                                                $qcIcons = ['pass' => 'bi-check-circle-fill', 'fail' => 'bi-x-circle-fill', 'pending' => 'bi-hourglass-split'];
                                            @endphp
                                            <span class="badge bg-{{ $qcColors[$post->qc_result] }} bg-opacity-10 text-{{ $qcColors[$post->qc_result] }}">
                                                <i class="bi {{ $qcIcons[$post->qc_result] }} me-1"></i> {{ strtoupper($post->qc_result) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($post->qc_score)
                                                @php $scoreColor = $post->qc_score >= 90 ? 'success' : ($post->qc_score >= 70 ? 'warning' : 'danger'); @endphp
                                                <span class="fw-bold text-{{ $scoreColor }}">{{ $post->qc_score }}%</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('scm.gr-qr.print', $post->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print Receipt">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            No posting history yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($postedGrqr->hasPages())
                    <div class="card-footer bg-white border-top-0 pt-3">{{ $postedGrqr->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- ==================== POST GR/QR MODAL ==================== --}}
    <div class="modal fade" id="postGrqrModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('scm.gr-qr.store') }}" class="modal-content" id="grqrForm" onsubmit="return validateGrqr()">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-clipboard2-check me-2"></i>Post Goods & Quality Receipt</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="shipment_id" id="modal_shipment_id">
                    <input type="hidden" name="batch_id" id="modal_batch_id">
                    
                    <div class="alert alert-info mb-4 d-flex align-items-center">
                        <i class="bi bi-info-circle fs-4 me-3"></i> 
                        <div>
                            <div>Posting for: <strong id="modal_batch_no">Select a batch</strong></div>
                            <small>Expected Qty: <strong id="modal_expected_qty">0</strong></small>
                        </div>
                    </div>

                    <div class="row g-4">
                        {{-- Manual Selection (Hidden if coming from Pending list) --}}
                        <div class="col-12" id="manualSelectSection" style="display: none;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Select Shipment *</label>
                                    <select name="shipment_id_manual" id="manual_shipment_id" class="form-select" onchange="loadBatchesForShipment(this.value)">
                                        <option value="">-- Choose Shipment --</option>
                                        @foreach($availableShipments as $ship)
                                            <option value="{{ $ship->id }}">{{ $ship->shipment_no }} ({{ $ship->origin }} → {{ $ship->destination }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Select Batch *</label>
                                    <select name="batch_id_manual" id="manual_batch_id" class="form-select" disabled onchange="updateExpectedQty(this)">
                                        <option value="">-- Choose Batch --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Goods Receipt --}}
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-box-seam me-2"></i>Goods Receipt (GR)</h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Received Quantity *</label>
                                <input type="number" name="received_qty" id="received_qty" class="form-control form-control-lg" min="0" value="0" required oninput="calculateRemaining()">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-danger">Damaged / Rejected Quantity *</label>
                                <input type="number" name="damaged_qty" id="damaged_qty" class="form-control" min="0" value="0" required oninput="calculateRemaining()">
                                <small class="text-muted">These will not be added to available stock.</small>
                            </div>
                            <div class="alert alert-light border mt-3">
                                <small>Remaining to Receive: <strong id="remaining_qty" class="text-primary">0</strong></small>
                            </div>
                        </div>

                        {{-- Quality Receipt --}}
                        <div class="col-md-6">
                            <h6 class="fw-bold text-success mb-3"><i class="bi bi-clipboard2-check me-2"></i>Quality Receipt (QR)</h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">QC Result *</label>
                                <select name="qc_result" class="form-select form-select-lg" required>
                                    <option value="pass">✅ PASS</option>
                                    <option value="fail">❌ FAIL</option>
                                    <option value="pending">⏳ PENDING Further Review</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">QC Score (%)</label>
                                <input type="number" name="qc_score" class="form-control" min="0" max="100" step="0.1" placeholder="e.g., 98.5">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Confirm Posting</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let isManualMode = false;
    const shipments = @json($availableShipments);

    function resetModalForCreate() {
        isManualMode = true;
        document.getElementById('manualSelectSection').style.display = 'block';
        document.getElementById('modal_batch_no').textContent = 'Select a batch below';
        document.getElementById('modal_expected_qty').textContent = '0';
        document.getElementById('received_qty').value = 0;
        document.getElementById('damaged_qty').value = 0;
        document.getElementById('remaining_qty').textContent = '0';
        
        // Clear hidden fields
        document.getElementById('modal_shipment_id').value = '';
        document.getElementById('modal_batch_id').value = '';
    }

    function populateModal(btn) {
        isManualMode = false;
        document.getElementById('manualSelectSection').style.display = 'none';
        
        document.getElementById('modal_shipment_id').value = btn.dataset.shipment;
        document.getElementById('modal_batch_id').value = btn.dataset.batch;
        document.getElementById('modal_batch_no').textContent = btn.dataset.batchno + ' (' + btn.dataset.product + ')';
        document.getElementById('modal_expected_qty').textContent = btn.dataset.expected;
        document.getElementById('received_qty').value = btn.dataset.expected;
        document.getElementById('damaged_qty').value = 0;
        calculateRemaining();
    }

    function loadBatchesForShipment(shipmentId) {
        const batchSelect = document.getElementById('manual_batch_id');
        batchSelect.innerHTML = '<option value="">-- Choose Batch --</option>';
        batchSelect.disabled = true;

        if (!shipmentId) return;

        const shipment = shipments.find(s => s.id == shipmentId);
        if (shipment && shipment.batches) {
            shipment.batches.forEach(batch => {
                const option = document.createElement('option');
                option.value = batch.id;
                option.dataset.expected = batch.expected_qty || 0; // Adjust based on your batch model
                option.dataset.batchno = batch.batch_no;
                option.dataset.product = batch.product ? batch.product.name : 'Unknown';
                option.textContent = `${batch.batch_no} - ${batch.product ? batch.product.name : 'Unknown'}`;
                batchSelect.appendChild(option);
            });
            batchSelect.disabled = false;
        }
    }

    function updateExpectedQty(select) {
        const option = select.options[select.selectedIndex];
        if (option.value) {
            document.getElementById('modal_batch_id').value = option.value;
            document.getElementById('modal_shipment_id').value = document.getElementById('manual_shipment_id').value;
            document.getElementById('modal_batch_no').textContent = option.dataset.batchno + ' (' + option.dataset.product + ')';
            document.getElementById('modal_expected_qty').textContent = option.dataset.expected;
            document.getElementById('received_qty').value = option.dataset.expected;
            document.getElementById('damaged_qty').value = 0;
            calculateRemaining();
        }
    }

    function calculateRemaining() {
        const expected = parseInt(document.getElementById('modal_expected_qty').textContent) || 0;
        const received = parseInt(document.getElementById('received_qty').value) || 0;
        const damaged = parseInt(document.getElementById('damaged_qty').value) || 0;
        
        const remaining = expected - (received + damaged);
        document.getElementById('remaining_qty').textContent = remaining;
        
        if (remaining < 0) {
            document.getElementById('remaining_qty').classList.add('text-danger');
            document.getElementById('remaining_qty').classList.remove('text-primary');
        } else {
            document.getElementById('remaining_qty').classList.remove('text-danger');
            document.getElementById('remaining_qty').classList.add('text-primary');
        }
    }

    function validateGrqr() {
        const expected = parseInt(document.getElementById('modal_expected_qty').textContent) || 0;
        const received = parseInt(document.getElementById('received_qty').value) || 0;
        const damaged = parseInt(document.getElementById('damaged_qty').value) || 0;

        if ((received + damaged) > expected) {
            alert('Error: Received + Damaged quantity cannot exceed Expected quantity!');
            return false;
        }
        return true;
    }
</script>
@endpush