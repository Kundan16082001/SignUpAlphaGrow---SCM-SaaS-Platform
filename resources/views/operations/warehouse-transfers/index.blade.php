@extends('layouts.master')
@section('title', 'Warehouse Transfers')
@section('page-title', 'Inter-Warehouse Transfers')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <style>
        .transfer-card { transition: all 0.2s ease; }
        .transfer-card:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(0,0,0,0.08); }
    </style>

    {{-- ==================== HEADER & ACTIONS ==================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-1 text-dark">Transfer Ledger</h5>
            <p class="text-muted small mb-0">Move stock between warehouses and track in-transit shipments.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTransferModal">
            <i class="bi bi-plus-lg me-1"></i> New Transfer
        </button>
    </div>

    {{-- ==================== FILTERS ==================== --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('operations.warehouse-transfers.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Transfers</option>
                        <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== TRANSFERS TABLE ==================== --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Transfer No.</th>
                            <th>Date</th>
                            <th>Source Warehouse</th>
                            <th><i class="bi bi-arrow-right text-muted mx-1"></i></th>
                            <th>Destination Warehouse</th>
                            <th class="text-center">Items</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $trf)
                            <tr class="transfer-card">
                                <td class="ps-4">
                                    <div class="fw-bold text-primary font-monospace small">{{ $trf->transfer_no }}</div>
                                    <small class="text-muted">by {{ $trf->user->name }}</small>
                                </td>
                                <td class="text-muted small">{{ $trf->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="fw-semibold small">{{ $trf->sourceWarehouse->name }}</div>
                                    <small class="text-muted">{{ $trf->sourceWarehouse->city }}</small>
                                </td>
                                <td class="text-center text-muted"><i class="bi bi-arrow-right-circle fs-5"></i></td>
                                <td>
                                    <div class="fw-semibold small">{{ $trf->destinationWarehouse->name }}</div>
                                    <small class="text-muted">{{ $trf->destinationWarehouse->city }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $trf->items->count() }} Items</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $trf->status_color }} bg-opacity-10 text-{{ $trf->status_color }} px-3 py-2">
                                        {{ ucfirst(str_replace('_', ' ', $trf->status)) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    @if($trf->status === 'in_transit')
                                        <form method="POST" action="{{ route('operations.warehouse-transfers.receive', $trf) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Confirm receipt of this transfer? Stock will be added to destination.')">
                                                <i class="bi bi-check-circle me-1"></i> Receive
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-truck fs-1 d-block mb-2"></i>
                                    No warehouse transfers found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($transfers->hasPages())
            <div class="card-footer bg-white border-top-0 pt-3">{{ $transfers->links() }}</div>
        @endif
    </div>

    {{-- ==================== CREATE TRANSFER MODAL ==================== --}}
    <div class="modal fade" id="createTransferModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form method="POST" action="{{ route('operations.warehouse-transfers.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-truck me-2"></i>Create New Transfer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Source Warehouse *</label>
                            <select name="source_warehouse_id" id="source_warehouse" class="form-select" required onchange="loadProducts()">
                                <option value="">Select Source</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->city }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Destination Warehouse *</label>
                            <select name="destination_warehouse_id" class="form-select" required>
                                <option value="">Select Destination</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->city }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <h6 class="fw-bold border-bottom pb-2 mb-3">Transfer Items</h6>
                    <div id="transferItemsContainer">
                        <div class="row g-3 mb-3 transfer-item-row">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Product *</label>
                                <select name="items[0][product_id]" class="form-select product-select" required onchange="loadBatches(0)">
                                    <option value="">Select Product</option>
                                    @foreach(\App\Models\Product::where('tenant_id', Auth::user()->tenant_id)->where('status', 'active')->get() as $prod)
                                        <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->sku }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Batch *</label>
                                <select name="items[0][batch_id]" class="form-select batch-select" required>
                                    <option value="">Select Source First</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Quantity *</label>
                                <input type="number" name="items[0][qty]" class="form-control" min="1" required placeholder="Qty">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-danger w-100 mb-1" onclick="removeItemRow(this)" title="Remove">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItemRow()">
                        <i class="bi bi-plus-circle me-1"></i> Add Another Item
                    </button>

                    <div class="mt-4">
                        <label class="form-label fw-semibold">Notes / Reason</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g., Replenishing Delhi branch stock"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i> Initiate Transfer</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let itemIndex = 1;

    function addItemRow() {
        const container = document.getElementById('transferItemsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'row g-3 mb-3 transfer-item-row';
        newRow.innerHTML = `
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Product *</label>
                <select name="items[${itemIndex}][product_id]" class="form-select product-select" required onchange="loadBatches(${itemIndex})">
                    <option value="">Select Product</option>
                    @foreach(\App\Models\Product::where('tenant_id', Auth::user()->tenant_id)->where('status', 'active')->get() as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->sku }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Batch *</label>
                <select name="items[${itemIndex}][batch_id]" class="form-select batch-select" required>
                    <option value="">Select Source & Product First</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Quantity *</label>
                <input type="number" name="items[${itemIndex}][qty]" class="form-control" min="1" required placeholder="Qty">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger w-100 mb-1" onclick="removeItemRow(this)" title="Remove">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(newRow);
        itemIndex++;
    }

    function removeItemRow(btn) {
        btn.closest('.transfer-item-row').remove();
    }

    function loadBatches(index) {
        const warehouseId = document.getElementById('source_warehouse').value;
        const productId = document.querySelector(`select[name="items[${index}][product_id]"]`).value;
        const batchSelect = document.querySelector(`select[name="items[${index}][batch_id]"]`);

        if (!warehouseId || !productId) {
            batchSelect.innerHTML = '<option value="">Select Source & Product First</option>';
            return;
        }

        batchSelect.innerHTML = '<option value="">Loading batches...</option>';

        fetch(`{{ route('operations.warehouse-transfers.batches') }}?warehouse_id=${warehouseId}&product_id=${productId}`)
            .then(res => res.json())
            .then(data => {
                batchSelect.innerHTML = '<option value="">Select Batch</option>';
                if (data.batches.length === 0) {
                    batchSelect.innerHTML += '<option value="" disabled>No stock available for this product</option>';
                } else {
                    data.batches.forEach(batch => {
                        batchSelect.innerHTML += `<option value="${batch.id}">${batch.batch_no} (Qty: ${batch.current_qty})</option>`;
                    });
                }
            })
            .catch(err => {
                console.error(err);
                batchSelect.innerHTML = '<option value="">Error loading batches</option>';
            });
    }
</script>
@endpush