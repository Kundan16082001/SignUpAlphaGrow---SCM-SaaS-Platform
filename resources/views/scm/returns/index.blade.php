@extends('layouts.master')
@section('title', 'Returns')
@section('page-title', 'Returns Management')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show"><ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="small mb-1 opacity-75">Active Returns</p><h3 class="fw-bold mb-0">{{ $stats['active'] }}</h3></div>
                    <i class="bi bi-arrow-counterclockwise fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="small mb-1 opacity-75">Completed</p><h3 class="fw-bold mb-0">{{ $stats['completed'] }}</h3></div>
                    <i class="bi bi-check-circle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="small mb-1 opacity-75">Value at Risk</p><h3 class="fw-bold mb-0">₹{{ number_format($stats['total_value_at_risk'], 0) }}</h3></div>
                    <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="small mb-1 opacity-75">Refund Pending</p><h3 class="fw-bold mb-0">₹{{ number_format($stats['total_refund_pending'], 0) }}</h3></div>
                    <i class="bi bi-currency-rupee fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Reverse Flow Diagram --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-diagram-2 me-2"></i>Reverse Supply Chain Flow</h6>
            <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap py-3">
                <div class="text-center px-4 py-3 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-25" style="min-width: 140px;">
                    <i class="bi bi-shop fs-2 text-primary d-block mb-1"></i>
                    <span class="fw-bold text-primary">Customer</span>
                </div>
                <i class="bi bi-arrow-left fs-3 text-muted"></i>
                <div class="text-center px-4 py-3 bg-warning bg-opacity-10 rounded-3 border border-warning border-opacity-25" style="min-width: 140px;">
                    <i class="bi bi-building fs-2 text-warning d-block mb-1"></i>
                    <span class="fw-bold text-warning">Distributor</span>
                    <br><small class="text-muted">{{ $reasonCounts['Defect'] ?? 0 }} defects</small>
                </div>
                <i class="bi bi-arrow-left fs-3 text-muted"></i>
                <div class="text-center px-4 py-3 bg-info bg-opacity-10 rounded-3 border border-info border-opacity-25" style="min-width: 140px;">
                    <i class="bi bi-box-seam fs-2 text-info d-block mb-1"></i>
                    <span class="fw-bold text-info">Warehouse</span>
                    <br><small class="text-muted">{{ $reasonCounts['Damage'] ?? 0 }} damages</small>
                </div>
                <i class="bi bi-arrow-left fs-3 text-muted"></i>
                <div class="text-center px-4 py-3 bg-success bg-opacity-10 rounded-3 border border-success border-opacity-25" style="min-width: 140px;">
                    <i class="bi bi-gear-wide-connected fs-2 text-success d-block mb-1"></i>
                    <span class="fw-bold text-success">Manufacturer</span>
                    <br><small class="text-muted">{{ $reasonCounts['Recall'] ?? 0 }} recalls</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Reason Chips & Tabs --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <ul class="nav nav-pills" id="returnTabs">
            <li class="nav-item"><a class="nav-link {{ $tab == 'active' ? 'active' : '' }}" href="{{ route('scm.returns.index', ['tab' => 'active']) }}">Active <span class="badge bg-light text-dark ms-1">{{ $stats['active'] }}</span></a></li>
            <li class="nav-item"><a class="nav-link {{ $tab == 'completed' ? 'active' : '' }}" href="{{ route('scm.returns.index', ['tab' => 'completed']) }}">Completed</a></li>
            <li class="nav-item"><a class="nav-link {{ $tab == 'all' ? 'active' : '' }}" href="{{ route('scm.returns.index', ['tab' => 'all']) }}">All</a></li>
        </ul>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            @foreach($reasons as $reason)
                <a href="{{ route('scm.returns.index', ['tab' => $tab, 'reason' => $reason]) }}" class="badge bg-{{ $reason == request('reason') ? 'primary' : 'light text-dark border' }} text-decoration-none px-3 py-2">
                    {{ $reason }} ({{ $reasonCounts[$reason] }})
                </a>
            @endforeach
            <button class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#newReturnModal"><i class="bi bi-plus-lg me-1"></i> Initiate Return</button>
        </div>
    </div>

    {{-- Returns Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Return #</th>
                            <th>Direction</th>
                            <th>Reason</th>
                            <th>Route</th>
                            <th class="text-center">Qty</th>
                            <th>Value</th>
                            <th>Refund</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $ret)
                            <tr>
                                <td class="ps-4 fw-bold text-primary">{{ $ret->return_no }}</td>
                                <td><small class="text-muted">{{ $ret->direction_label }}</small></td>
                                <td><span class="badge bg-{{ $ret->reason_color }} bg-opacity-10 text-{{ $ret->reason_color }}">{{ $ret->reason }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-1 small">
                                        <span>{{ Str::limit($ret->origin, 12) }}</span>
                                        <i class="bi bi-arrow-left text-danger"></i>
                                        <span class="fw-semibold">{{ Str::limit($ret->destination, 12) }}</span>
                                    </div>
                                </td>
                                <td class="text-center fw-bold">{{ $ret->qty }}</td>
                                <td class="fw-semibold">₹{{ number_format($ret->value, 2) }}</td>
                                <td class="text-success fw-semibold">₹{{ number_format($ret->refund_amount, 2) }}</td>
                                <td><span class="badge bg-{{ $ret->status_color }}">{{ ucfirst($ret->status) }}</span></td>
                                <td class="text-end pe-4">
                                    <form method="POST" action="{{ route('scm.returns.update-status', $ret) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <select name="status" class="form-select form-select-sm" style="width:130px;" onchange="this.form.submit()">
                                            <option value="initiated" {{ $ret->status == 'initiated' ? 'selected' : '' }}>Initiated</option>
                                            <option value="in_transit" {{ $ret->status == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                            <option value="received" {{ $ret->status == 'received' ? 'selected' : '' }}>Received</option>
                                            <option value="inspected" {{ $ret->status == 'inspected' ? 'selected' : '' }}>Inspected</option>
                                            <option value="approved" {{ $ret->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="rejected" {{ $ret->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                            <option value="completed" {{ $ret->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center py-5 text-muted"><i class="bi bi-arrow-counterclockwise fs-1 d-block mb-2"></i>No returns found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($returns->hasPages())<div class="card-footer bg-white border-top-0 pt-3">{{ $returns->links() }}</div>@endif
    </div>

    {{-- INITIATE RETURN MODAL --}}
    <div class="modal fade" id="newReturnModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="{{ route('scm.returns.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-arrow-counterclockwise me-2"></i>Initiate Return</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Reason *</label>
                            <select name="reason" class="form-select" required>
                                <option value="Defect">Defect</option>
                                <option value="QC Fail">QC Fail</option>
                                <option value="Damage">Damage</option>
                                <option value="Recall">Recall</option>
                                <option value="Expired">Expired</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Direction *</label>
                            <select name="direction" class="form-select" required>
                                <option value="customer_to_distributor">Customer → Distributor</option>
                                <option value="distributor_to_warehouse">Distributor → Warehouse</option>
                                <option value="warehouse_to_manufacturer">Warehouse → Manufacturer</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Origin *</label>
                            <input type="text" name="origin" class="form-control" placeholder="e.g., Customer Site" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Destination *</label>
                            <input type="text" name="destination" class="form-control" placeholder="e.g., Main Warehouse" required>
                        </div>
                    </div>
                    <h6 class="fw-bold border-bottom pb-2 mb-3">Return Items</h6>
                    <div id="returnItemsContainer">
                        <div class="row g-2 item-row mb-2">
                            <div class="col-md-4"><select name="items[0][product_id]" class="form-select" required><option value="">Select Product</option>@foreach($products as $p)<option value="{{ $p->id }}" data-price="{{ $p->price }}">{{ $p->sku }} - {{ $p->name }}</option>@endforeach</select></div>
                            <div class="col-md-2"><input type="number" name="items[0][qty]" class="form-control" placeholder="Qty" min="1" required></div>
                            <div class="col-md-2"><input type="number" name="items[0][unit_price]" class="form-control price-input" placeholder="Price" step="0.01" required></div>
                            <div class="col-md-2"><select name="items[0][condition]" class="form-select"><option value="good">Good</option><option value="damaged">Damaged</option><option value="defective">Defective</option><option value="expired">Expired</option></select></div>
                            <div class="col-md-2 d-flex align-items-center"><button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest('.item-row').remove()"><i class="bi bi-trash"></i></button></div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addReturnItem()"><i class="bi bi-plus-circle me-1"></i> Add Item</button>
                    <div class="col-12 mt-3"><label class="form-label fw-semibold">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-arrow-counterclockwise me-1"></i> Initiate Return</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let returnItemCount = 1;
    function addReturnItem() {
        const c = document.getElementById('returnItemsContainer');
        const r = document.createElement('div');
        r.className = 'row g-2 item-row mb-2';
        r.innerHTML = `<div class="col-md-4"><select name="items[${returnItemCount}][product_id]" class="form-select" required><option value="">Select Product</option>@foreach($products as $p)<option value="{{ $p->id }}" data-price="{{ $p->price }}">{{ $p->sku }} - {{ $p->name }}</option>@endforeach</select></div><div class="col-md-2"><input type="number" name="items[${returnItemCount}][qty]" class="form-control" placeholder="Qty" min="1" required></div><div class="col-md-2"><input type="number" name="items[${returnItemCount}][unit_price]" class="form-control price-input" placeholder="Price" step="0.01" required></div><div class="col-md-2"><select name="items[${returnItemCount}][condition]" class="form-select"><option value="good">Good</option><option value="damaged">Damaged</option><option value="defective">Defective</option><option value="expired">Expired</option></select></div><div class="col-md-2 d-flex align-items-center"><button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest('.item-row').remove()"><i class="bi bi-trash"></i></button></div>`;
        c.appendChild(r);
        returnItemCount++;
    }
    document.addEventListener('change', function(e) {
        if (e.target.tagName === 'SELECT' && e.target.querySelector('option[data-price]')) {
            const opt = e.target.options[e.target.selectedIndex];
            if (opt.dataset.price) e.target.closest('.item-row').querySelector('.price-input').value = opt.dataset.price;
        }
    });
</script>
@endpush