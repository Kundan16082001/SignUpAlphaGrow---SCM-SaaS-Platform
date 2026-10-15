@extends('layouts.master')
@section('title', 'Bins - ' . $warehouse->name)
@section('page-title', $warehouse->name . ' - Bin Management')

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

    {{-- ==================== HEADER ==================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('operations.warehouses.show', $warehouse) }}" class="text-muted small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Back to {{ $warehouse->name }}
            </a>
            <h5 class="fw-bold mt-2 mb-1 text-dark">Bin Management</h5>
            <p class="text-muted small mb-0">Manage storage locations within this warehouse.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bulkCreateModal">
                <i class="bi bi-grid-3x3 me-1"></i> Bulk Create
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBinModal">
                <i class="bi bi-plus-lg me-1"></i> Add Bin
            </button>
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
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Available</small>
                <h3 class="fw-bold mb-0 text-success">{{ $stats['available'] }}</h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Occupied</small>
                <h3 class="fw-bold mb-0 text-warning">{{ $stats['occupied'] }}</h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Reserved</small>
                <h3 class="fw-bold mb-0 text-info">{{ $stats['reserved'] }}</h3>
            </div>
        </div>
    </div>

    {{-- ==================== BINS TABLE ==================== --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Bin Code</th>
                            <th>Type</th>
                            <th>Zone</th>
                            <th>Aisle</th>
                            <th>Rack</th>
                            <th class="text-center">Current / Max</th>
                            <th>Utilization</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bins as $bin)
                            <tr>
                                <td class="ps-4 fw-bold font-monospace small">{{ $bin->bin_code }}</td>
                                <td>
                                    @php
                                        $typeColors = ['shelf' => 'secondary', 'pallet' => 'primary', 'cold' => 'info', 'hazmat' => 'danger'];
                                        $typeIcons = ['shelf' => 'bi-grid', 'pallet' => 'bi-box', 'cold' => 'bi-snow', 'hazmat' => 'bi-exclamation-diamond'];
                                    @endphp
                                    <span class="badge bg-{{ $typeColors[$bin->type] }} bg-opacity-10 text-{{ $typeColors[$bin->type] }}">
                                        <i class="bi {{ $typeIcons[$bin->type] }} me-1"></i>{{ ucfirst($bin->type) }}
                                    </span>
                                </td>
                                <td class="fw-semibold">{{ $bin->zone }}</td>
                                <td>{{ $bin->aisle }}</td>
                                <td>{{ $bin->rack }}</td>
                                <td class="text-center">
                                    <span class="fw-bold">{{ $bin->current_qty }}</span>
                                    <span class="text-muted">/ {{ $bin->max_capacity }}</span>
                                </td>
                                <td style="width: 120px;">
                                    @php
                                        $utilization = $bin->max_capacity > 0 ? round(($bin->current_qty / $bin->max_capacity) * 100, 1) : 0;
                                        $color = $utilization > 85 ? 'danger' : ($utilization > 60 ? 'warning' : 'success');
                                    @endphp
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-{{ $color }}" style="width: {{ $utilization }}%"></div>
                                        </div>
                                        <small class="fw-bold">{{ $utilization }}%</small>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusColors = ['available' => 'success', 'occupied' => 'warning', 'reserved' => 'info'];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$bin->status] }} bg-opacity-10 text-{{ $statusColors[$bin->status] }}">
                                        {{ ucfirst($bin->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary edit-bin-btn" data-bs-toggle="modal" data-bs-target="#editBinModal"
                                        data-id="{{ $bin->id }}" data-type="{{ $bin->type }}" data-zone="{{ $bin->zone }}"
                                        data-aisle="{{ $bin->aisle }}" data-rack="{{ $bin->rack }}"
                                        data-capacity="{{ $bin->max_capacity }}" data-status="{{ $bin->status }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('operations.warehouse-bins.destroy', $bin) }}" class="d-inline" onsubmit="return confirm('Delete this bin?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" {{ $bin->current_qty > 0 ? 'disabled title=Cannot delete bin with stock' : '' }}>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-grid-3x3-gap fs-1 d-block mb-2"></i>
                                    No bins configured. Click "Add Bin" or "Bulk Create" to get started.
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

    {{-- ==================== ADD BIN MODAL ==================== --}}
    <div class="modal fade" id="addBinModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('operations.warehouse-bins.store', $warehouse) }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Bin</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bin Code *</label>
                        <input type="text" name="bin_code" class="form-control" placeholder="WH-MUM-001-A01-01" required>
                        <small class="text-muted">Unique identifier (e.g., WH-MUM-001-A01-01)</small>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Type *</label>
                            <select name="type" class="form-select" required>
                                <option value="shelf">Shelf</option>
                                <option value="pallet">Pallet</option>
                                <option value="cold">Cold Storage</option>
                                <option value="hazmat">Hazmat</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Max Capacity *</label>
                            <input type="number" name="max_capacity" class="form-control" min="1" value="100" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Zone *</label>
                            <input type="text" name="zone" class="form-control" maxlength="1" placeholder="A" required>
                            <small class="text-muted">Single letter (A-Z)</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Aisle *</label>
                            <input type="text" name="aisle" class="form-control" maxlength="2" placeholder="01" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Rack *</label>
                            <input type="text" name="rack" class="form-control" maxlength="2" placeholder="01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Initial Status</label>
                        <select name="status" class="form-select">
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="reserved">Reserved</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Create Bin</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== EDIT BIN MODAL ==================== --}}
    <div class="modal fade" id="editBinModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" id="editBinForm" class="modal-content">
                @csrf @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Bin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Type *</label>
                            <select name="type" id="edit_type" class="form-select" required>
                                <option value="shelf">Shelf</option>
                                <option value="pallet">Pallet</option>
                                <option value="cold">Cold Storage</option>
                                <option value="hazmat">Hazmat</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Max Capacity *</label>
                            <input type="number" name="max_capacity" id="edit_capacity" class="form-control" min="1" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Zone *</label>
                            <input type="text" name="zone" id="edit_zone" class="form-control" maxlength="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Aisle *</label>
                            <input type="text" name="aisle" id="edit_aisle" class="form-control" maxlength="2" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Rack *</label>
                            <input type="text" name="rack" id="edit_rack" class="form-control" maxlength="2" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="reserved">Reserved</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i> Update Bin</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== BULK CREATE MODAL ==================== --}}
    <div class="modal fade" id="bulkCreateModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('operations.warehouse-bins.bulk', $warehouse) }}" class="modal-content">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-grid-3x3 me-2"></i>Bulk Create Bins</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        This will automatically generate bins for all combinations of zones, aisles, and racks.
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Zone Start *</label>
                            <input type="text" name="zone_start" class="form-control" maxlength="1" placeholder="A" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Zone End *</label>
                            <input type="text" name="zone_end" class="form-control" maxlength="1" placeholder="C" required>
                            <small class="text-muted">Creates zones A, B, C</small>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Aisles per Zone *</label>
                            <input type="number" name="aisles" class="form-control" min="1" max="99" value="5" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Racks per Aisle *</label>
                            <input type="number" name="racks_per_aisle" class="form-control" min="1" max="99" value="5" required>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Bin Type *</label>
                            <select name="type" class="form-select" required>
                                <option value="shelf">Shelf</option>
                                <option value="pallet">Pallet</option>
                                <option value="cold">Cold Storage</option>
                                <option value="hazmat">Hazmat</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Max Capacity *</label>
                            <input type="number" name="max_capacity" class="form-control" min="1" value="100" required>
                        </div>
                    </div>
                    <div class="mt-3 p-3 bg-light rounded">
                        <small class="text-muted">
                            <strong>Example:</strong> Zones A-C, 5 aisles, 5 racks = <strong id="totalBinsPreview">75</strong> bins
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-grid-3x3 me-1"></i> Create Bins</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Edit bin modal population
    document.querySelectorAll('.edit-bin-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            document.getElementById('editBinForm').action = `/bins/${id}`;
            document.getElementById('edit_type').value = this.dataset.type;
            document.getElementById('edit_zone').value = this.dataset.zone;
            document.getElementById('edit_aisle').value = this.dataset.aisle;
            document.getElementById('edit_rack').value = this.dataset.rack;
            document.getElementById('edit_capacity').value = this.dataset.capacity;
            document.getElementById('edit_status').value = this.dataset.status;
        });
    });

    // Bulk create preview calculator
    document.querySelectorAll('#bulkCreateModal input').forEach(input => {
        input.addEventListener('input', updateBulkPreview);
    });

    function updateBulkPreview() {
        const zoneStart = document.querySelector('input[name="zone_start"]').value.toUpperCase();
        const zoneEnd = document.querySelector('input[name="zone_end"]').value.toUpperCase();
        const aisles = parseInt(document.querySelector('input[name="aisles"]').value) || 0;
        const racks = parseInt(document.querySelector('input[name="racks_per_aisle"]').value) || 0;

        if (zoneStart && zoneEnd && zoneStart <= zoneEnd) {
            const zones = zoneEnd.charCodeAt(0) - zoneStart.charCodeAt(0) + 1;
            const total = zones * aisles * racks;
            document.getElementById('totalBinsPreview').textContent = total;
        } else {
            document.getElementById('totalBinsPreview').textContent = '0';
        }
    }
</script>
@endpush