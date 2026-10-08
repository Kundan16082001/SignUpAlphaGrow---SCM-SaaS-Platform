@extends('layouts.master')
@section('title', 'Machines')
@section('page-title', 'Machine Management')

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
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ==================== KPI CARDS ==================== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1 text-uppercase fw-bold">Operational</p>
                            <h2 class="fw-bold mb-0 text-success">{{ $stats['operational'] }}</h2>
                            <small class="text-muted">of {{ $stats['total'] }} total</small>
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
                            <h2 class="fw-bold mb-0 text-warning">{{ $stats['maintenance'] }}</h2>
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
                            <h2 class="fw-bold mb-0 text-danger">{{ $stats['breakdown'] }}</h2>
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
                            <p class="text-muted small mb-1 text-uppercase fw-bold">Avg Efficiency</p>
                            <h2 class="fw-bold mb-0 text-info">{{ $stats['avg_efficiency'] }}%</h2>
                            <small class="text-muted">{{ $stats['due_maintenance'] }} due soon</small>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                            <i class="bi bi-speedometer2 fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== FILTERS & ACTIONS ==================== --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('operations.machines.index') }}" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search Name, Code, or Type..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="operational" {{ request('status') == 'operational' ? 'selected' : '' }}>Operational</option>
                        <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="breakdown" {{ request('status') == 'breakdown' ? 'selected' : '' }}>Breakdown</option>
                        <option value="idle" {{ request('status') == 'idle' ? 'selected' : '' }}>Idle</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-outline-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMachineModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Machine
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== MACHINES TABLE ==================== --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Code</th>
                            <th>Machine Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th style="min-width: 180px;">Efficiency</th>
                            <th>Next Maintenance</th>
                            <th>Active Work Orders</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($machines as $machine)
                            @php
                                $statusColors = [
                                    'operational' => 'success',
                                    'maintenance' => 'warning',
                                    'breakdown' => 'danger',
                                    'idle' => 'secondary',
                                ];
                                $statusIcons = [
                                    'operational' => 'bi-check-circle-fill',
                                    'maintenance' => 'bi-tools',
                                    'breakdown' => 'bi-exclamation-triangle-fill',
                                    'idle' => 'bi-pause-circle-fill',
                                ];
                                $activeWOs = $machine->workOrders()->whereNotIn('status', ['completed', 'cancelled'])->count();
                            @endphp
                            <tr>
                                <td class="ps-4 fw-bold text-primary">{{ $machine->machine_code }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $machine->name }}</div>
                                    @if($machine->notes)
                                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>{{ Str::limit($machine->notes, 30) }}</small>
                                    @endif
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $machine->type ?? 'General' }}</span></td>
                                <td>
                                    <span class="badge bg-{{ $statusColors[$machine->status] }} bg-opacity-10 text-{{ $statusColors[$machine->status] }}">
                                        <i class="bi {{ $statusIcons[$machine->status] }} me-1"></i>
                                        {{ ucfirst($machine->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            @php
                                                $effColor = $machine->efficiency >= 80 ? 'success' : ($machine->efficiency >= 50 ? 'warning' : 'danger');
                                            @endphp
                                            <div class="progress-bar bg-{{ $effColor }}" style="width: {{ $machine->efficiency }}%"></div>
                                        </div>
                                        <span class="fw-bold small" style="min-width: 40px;">{{ $machine->efficiency }}%</span>
                                    </div>
                                </td>
                                <td>
                                    @if($machine->next_maintenance)
                                        @if($machine->next_maintenance->isPast())
                                            <span class="text-danger fw-bold small">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                OVERDUE ({{ $machine->next_maintenance->format('d M Y') }})
                                            </span>
                                        @elseif($machine->next_maintenance->lte(now()->addDays(7)))
                                            <span class="text-warning fw-bold small">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $machine->next_maintenance->format('d M Y') }}
                                            </span>
                                            <br><small class="text-warning">{{ now()->diffInDays($machine->next_maintenance) }}d left</small>
                                        @else
                                            <span class="text-muted small">{{ $machine->next_maintenance->format('d M Y') }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted small">Not scheduled</span>
                                    @endif
                                </td>
                                <td>
                                    @if($activeWOs > 0)
                                        <a href="{{ route('operations.production.index') }}" class="badge bg-primary bg-opacity-10 text-primary text-decoration-none">
                                            <i class="bi bi-clipboard-data me-1"></i>{{ $activeWOs }} active
                                        </a>
                                    @else
                                        <span class="text-muted small">None</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary edit-btn"
                                        data-id="{{ $machine->id }}"
                                        data-code="{{ $machine->machine_code }}"
                                        data-name="{{ $machine->name }}"
                                        data-type="{{ $machine->type }}"
                                        data-status="{{ $machine->status }}"
                                        data-efficiency="{{ $machine->efficiency }}"
                                        data-maintenance="{{ $machine->next_maintenance?->format('Y-m-d') }}"
                                        data-notes="{{ $machine->notes }}"
                                        data-bs-toggle="modal" data-bs-target="#editMachineModal"
                                        title="Edit Machine">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('operations.machines.destroy', $machine) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this machine?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Machine">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-gear-wide-connected fs-1 d-block mb-2"></i>
                                    No machines found. Click "Add Machine" to register your first machine.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($machines->hasPages())
            <div class="card-footer bg-white border-top-0 pt-3">
                {{ $machines->links() }}
            </div>
        @endif
    </div>

    {{-- ==================== ADD MACHINE MODAL ==================== --}}
    <div class="modal fade" id="addMachineModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('operations.machines.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Register New Machine</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Machine Code *</label>
                            <input type="text" name="machine_code" class="form-control" placeholder="e.g., MCH-001" required>
                            <small class="text-muted">Unique identifier (auto-uppercase)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Machine Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., CNC Lathe A" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Machine Type</label>
                            <select name="type" class="form-select">
                                <option value="">Select Type</option>
                                <option value="CNC">CNC</option>
                                <option value="Assembly">Assembly Line</option>
                                <option value="Welding">Welding</option>
                                <option value="Packaging">Packaging</option>
                                <option value="Cutting">Cutting</option>
                                <option value="Printing">Printing</option>
                                <option value="Molding">Molding</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Efficiency (%)</label>
                            <input type="number" name="efficiency" class="form-control" min="0" max="100" value="100" step="0.1">
                            <small class="text-muted">Current operational efficiency</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Next Maintenance Date</label>
                            <input type="date" name="next_maintenance" class="form-control" min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Initial Status</label>
                            <input type="text" class="form-control bg-light" value="Operational" disabled>
                            <small class="text-muted">New machines default to Operational</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Model number, location, special instructions..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Register Machine</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== EDIT MACHINE MODAL ==================== --}}
    <div class="modal fade" id="editMachineModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" id="editForm" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Machine</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Machine Code *</label>
                            <input type="text" name="machine_code" id="edit_code" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Machine Name *</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Machine Type</label>
                            <input type="text" name="type" id="edit_type" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status *</label>
                            <select name="status" id="edit_status" class="form-select" required>
                                <option value="operational">Operational</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="breakdown">Breakdown</option>
                                <option value="idle">Idle</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Efficiency (%)</label>
                            <input type="number" name="efficiency" id="edit_efficiency" class="form-control" min="0" max="100" step="0.1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Next Maintenance Date</label>
                            <input type="date" name="next_maintenance" id="edit_maintenance" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Machine</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Populate Edit Modal dynamically
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;
            document.getElementById('editForm').action = `/operations/machines/${id}`;
            document.getElementById('edit_code').value = this.dataset.code;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_type').value = this.dataset.type || '';
            document.getElementById('edit_status').value = this.dataset.status;
            document.getElementById('edit_efficiency').value = this.dataset.efficiency;
            document.getElementById('edit_maintenance').value = this.dataset.maintenance || '';
            document.getElementById('edit_notes').value = this.dataset.notes || '';
        });
    });
</script>
@endpush