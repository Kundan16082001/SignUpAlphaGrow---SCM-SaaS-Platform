@extends('layouts.master')
@section('title', 'Production')
@section('page-title', 'Production Management')

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

    {{-- ==================== MACHINE STATUS CARDS ==================== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1 text-uppercase fw-bold">Operational</p>
                            <h2 class="fw-bold mb-0 text-success">{{ $machineStats['operational'] }}</h2>
                            <small class="text-muted">of {{ $machineStats['total'] }} machines</small>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                            <i class="bi bi-gear-wide-connected fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1 text-uppercase fw-bold">Maintenance</p>
                            <h2 class="fw-bold mb-0 text-warning">{{ $machineStats['maintenance'] }}</h2>
                            <small class="text-muted">scheduled service</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                            <i class="bi bi-tools fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1 text-uppercase fw-bold">Breakdown</p>
                            <h2 class="fw-bold mb-0 text-danger">{{ $machineStats['breakdown'] }}</h2>
                            <small class="text-muted">needs attention</small>
                        </div>
                        <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                            <i class="bi bi-exclamation-triangle fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1 text-uppercase fw-bold">Idle</p>
                            <h2 class="fw-bold mb-0 text-info">{{ $machineStats['idle'] }}</h2>
                            <small class="text-muted">available</small>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                            <i class="bi bi-pause-circle fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== WORK ORDER KPIs ==================== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="small mb-1 opacity-75">Total Work Orders</p>
                            <h3 class="fw-bold mb-0">{{ $woStats['total'] }}</h3>
                        </div>
                        <i class="bi bi-clipboard-data fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="small mb-1 opacity-75">In Progress</p>
                            <h3 class="fw-bold mb-0">{{ $woStats['in_progress'] }}</h3>
                        </div>
                        <i class="bi bi-play-circle fs-1 opacity-50"></i>
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
                            <h3 class="fw-bold mb-0">{{ $woStats['overdue'] }}</h3>
                        </div>
                        <i class="bi bi-clock-history fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="small mb-1 opacity-75">Due in 3 Days</p>
                            <h3 class="fw-bold mb-0">{{ $woStats['due_soon'] }}</h3>
                        </div>
                        <i class="bi bi-bell fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== FILTERS & ACTIONS ==================== --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('operations.production.index') }}" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search WO# or Product..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="planned" {{ request('status') == 'planned' ? 'selected' : '' }}>Planned</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-5 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-outline-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newWorkOrderModal">
                        <i class="bi bi-plus-lg me-1"></i> New Work Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== WORK ORDERS TABLE ==================== --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">WO Number</th>
                            <th>Product</th>
                            <th>BOM</th>
                            <th>Machine</th>
                            <th style="min-width: 220px;">Progress</th>
                            <th>Scrap</th>
                            <th>Deadline</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workOrders as $wo)
                            <tr class="{{ $wo->is_overdue ? 'table-danger' : '' }}">
                                <td class="ps-4 fw-bold text-primary">{{ $wo->wo_no }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $wo->product_name }}</div>
                                    <small class="text-muted">{{ $wo->product?->sku ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    @if($wo->bom)
                                        <button class="btn btn-sm btn-outline-secondary bom-details-btn" 
                                            data-bom-id="{{ $wo->bom_id }}" 
                                            data-bs-toggle="modal" data-bs-target="#bomDetailsModal">
                                            <i class="bi bi-diagram-3 me-1"></i>{{ $wo->bom->bom_no }}
                                        </button>
                                    @else
                                        <span class="text-muted small">No BOM</span>
                                    @endif
                                </td>
                                <td>
                                    @if($wo->machine)
                                        <span class="badge bg-{{ $wo->machine->status_color }}">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>
                                            {{ $wo->machine->name }}
                                        </span>
                                    @else
                                        <span class="text-muted small">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 10px; background-color: #e9ecef;">
                                            <div class="progress-bar {{ $wo->progress_percentage == 100 ? 'bg-success' : 'bg-primary' }}" 
                                                role="progressbar" 
                                                style="width: {{ $wo->progress_percentage }}%"
                                                aria-valuenow="{{ $wo->progress_percentage }}" 
                                                aria-valuemin="0" 
                                                aria-valuemax="100"></div>
                                        </div>
                                        <span class="fw-bold small" style="min-width: 40px;">{{ $wo->progress_percentage }}%</span>
                                    </div>
                                    <small class="text-muted">{{ $wo->completed_qty }} / {{ $wo->target_qty }} units</small>
                                </td>
                                <td>
                                    @if($wo->scrap_qty > 0)
                                        <span class="badge bg-danger bg-opacity-10 text-danger">
                                            <i class="bi bi-trash me-1"></i>{{ $wo->scrap_qty }}
                                        </span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if($wo->is_overdue)
                                        <span class="text-danger fw-bold small">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                            {{ $wo->deadline->format('d M Y') }}
                                        </span>
                                        <br><small class="text-danger">{{ abs($wo->days_remaining) }}d overdue</small>
                                    @elseif($wo->days_remaining !== null && $wo->days_remaining <= 3 && $wo->days_remaining >= 0)
                                        <span class="text-warning fw-bold small">
                                            {{ $wo->deadline->format('d M Y') }}
                                        </span>
                                        <br><small class="text-warning"><i class="bi bi-clock me-1"></i>{{ $wo->days_remaining }}d left</small>
                                    @else
                                        <span class="text-muted small">
                                            {{ $wo->deadline?->format('d M Y') ?? 'N/A' }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $wo->priority_color }}">{{ $wo->priority_label }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'planned' => 'secondary',
                                            'in_progress' => 'primary',
                                            'completed' => 'success',
                                            'on_hold' => 'warning',
                                            'cancelled' => 'dark',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$wo->status] ?? 'secondary' }}">
                                        {{ str_replace('_', ' ', ucfirst($wo->status)) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary update-progress-btn"
                                        data-id="{{ $wo->id }}"
                                        data-target="{{ $wo->target_qty }}"
                                        data-completed="{{ $wo->completed_qty }}"
                                        data-scrap="{{ $wo->scrap_qty }}"
                                        data-status="{{ $wo->status }}"
                                        data-bs-toggle="modal" data-bs-target="#updateProgressModal"
                                        title="Update Progress">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="bi bi-clipboard-data fs-1 d-block mb-2"></i>
                                    No work orders found. Click "New Work Order" to start production.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($workOrders->hasPages())
            <div class="card-footer bg-white border-top-0 pt-3">
                {{ $workOrders->links() }}
            </div>
        @endif
    </div>

    {{-- ==================== NEW WORK ORDER MODAL ==================== --}}
    <div class="modal fade" id="newWorkOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('operations.production.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Create New Work Order</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Product *</label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">BOM Recipe</label>
                            <select name="bom_id" class="form-select">
                                <option value="">No BOM</option>
                                @foreach($boms as $bom)
                                    <option value="{{ $bom->id }}">{{ $bom->bom_no }} - {{ $bom->product_name }} (v{{ $bom->version }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assigned Machine</label>
                            <select name="machine_id" class="form-select">
                                <option value="">Unassigned</option>
                                @foreach($machines as $machine)
                                    <option value="{{ $machine->id }}" {{ $machine->status !== 'operational' ? 'disabled' : '' }}>
                                        {{ $machine->name }} ({{ ucfirst($machine->status) }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Only operational machines can be assigned.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Priority *</label>
                            <select name="priority" class="form-select" required>
                                <option value="2">Medium</option>
                                <option value="1">High</option>
                                <option value="3">Low</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Target Quantity *</label>
                            <input type="number" name="target_qty" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Deadline *</label>
                            <input type="date" name="deadline" class="form-control" min="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Special instructions..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Work Order</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== UPDATE PROGRESS MODAL ==================== --}}
    <div class="modal fade" id="updateProgressModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" id="progressForm" class="modal-content">
                @csrf
                @method('PATCH')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Update Production Progress</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Completed Quantity</label>
                        <input type="number" name="completed_qty" id="progress_completed" class="form-control" min="0" required>
                        <small class="text-muted">Total units successfully produced so far.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Scrap Quantity</label>
                        <input type="number" name="scrap_qty" id="progress_scrap" class="form-control" min="0" value="0" required>
                        <small class="text-muted">Defective / rejected units count.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" id="progress_status" class="form-select" required>
                            <option value="planned">Planned</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="on_hold">On Hold</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Progress</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== BOM DETAILS MODAL ==================== --}}
    <div class="modal fade" id="bomDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-diagram-3 me-2"></i>BOM Component Breakdown</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="bomDetailsBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2">Loading BOM details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Update Progress Modal - Populate data
    document.querySelectorAll('.update-progress-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            document.getElementById('progressForm').action = `/operations/production/${id}/progress`;
            document.getElementById('progress_completed').value = this.dataset.completed;
            document.getElementById('progress_completed').max = this.dataset.target;
            document.getElementById('progress_scrap').value = this.dataset.scrap;
            document.getElementById('progress_status').value = this.dataset.status;
        });
    });

    // BOM Details Modal - Fetch via AJAX
    document.querySelectorAll('.bom-details-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const bomId = this.dataset.bomId;
            const body = document.getElementById('bomDetailsBody');
            body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading BOM details...</p></div>';

            fetch(`/operations/production/bom/${bomId}/details`)
                .then(res => res.json())
                .then(data => {
                    let html = `
                        <div class="d-flex justify-content-between mb-4">
                            <div>
                                <h5 class="fw-bold mb-1">${data.bom_no}</h5>
                                <p class="text-muted mb-0">${data.product_name} (${data.product_sku})</p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-info mb-2">Version ${data.version}</span>
                                <h4 class="fw-bold text-primary mb-0">₹${parseFloat(data.unit_cost).toFixed(2)}</h4>
                                <small class="text-muted">Unit Cost</small>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Component</th>
                                        <th>SKU</th>
                                        <th class="text-center">Qty Required</th>
                                        <th class="text-end">Unit Cost</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-center">Wastage</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    data.components.forEach(c => {
                        html += `
                            <tr>
                                <td class="fw-semibold">${c.component_name}</td>
                                <td><code>${c.component_sku}</code></td>
                                <td class="text-center">${c.qty_required} ${c.unit}</td>
                                <td class="text-end">₹${parseFloat(c.unit_cost).toFixed(2)}</td>
                                <td class="text-end fw-bold">₹${parseFloat(c.total_cost).toFixed(2)}</td>
                                <td class="text-center">${c.wastage_percentage}%</td>
                            </tr>
                        `;
                    });
                    html += `</tbody>
                        <tfoot class="table-info">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Total Components: ${data.components.length}</td>
                                <td class="text-end fw-bold fs-5">₹${parseFloat(data.unit_cost).toFixed(2)}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    </div>`;
                    body.innerHTML = html;
                })
                .catch(err => {
                    body.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Failed to load BOM details.</div>';
                });
        });
    });
</script>
@endpush