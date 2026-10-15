@extends('layouts.master')
@section('title', 'Warehouses')
@section('page-title', 'Multi-Warehouse Management')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <style>
        .warehouse-card {
            background: #fff;
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 16px;
            transition: all 0.3s ease;
            height: 100%;
            overflow: hidden;
        }
        .warehouse-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px -8px rgba(0,0,0,0.1);
            border-color: rgba(99, 102, 241, 0.3);
        }
        .warehouse-header {
            padding: 1.25rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            position: relative;
        }
        .warehouse-icon {
            width: 52px; height: 52px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
        }
        .stat-mini {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed rgba(0,0,0,0.06);
        }
        .stat-mini:last-child { border-bottom: none; }
        .stat-mini-label { color: #64748b; font-size: 0.8rem; }
        .stat-mini-value { font-weight: 600; color: #1e293b; font-size: 0.85rem; }
        .utilization-bar {
            height: 6px;
            background: #f1f5f9;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 8px;
        }
        .utilization-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.6s ease;
        }
    </style>

    {{-- ==================== STATS ROW ==================== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                    <i class="bi bi-buildings fs-3"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Total Warehouses</small>
                    <h4 class="fw-bold mb-0">{{ $stats['total_warehouses'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-3">
                    <i class="bi bi-check-circle fs-3"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Active</small>
                    <h4 class="fw-bold mb-0">{{ $stats['active_warehouses'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center gap-3">
                <div class="bg-info bg-opacity-10 text-info p-3 rounded-3">
                    <i class="bi bi-grid-3x3-gap fs-3"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Total Bins</small>
                    <h4 class="fw-bold mb-0">{{ $stats['total_bins'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center gap-3">
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3">
                    <i class="bi bi-currency-rupee fs-3"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Total Stock Value</small>
                    <h4 class="fw-bold mb-0">₹{{ number_format($stats['total_stock_value'] / 100000, 1) }}L</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== FILTERS & ADD BUTTON ==================== --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('operations.warehouses.index') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold text-muted mb-1">Search Warehouse</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Name, code, city, state..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Type</label>
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        @foreach($types as $t)
                            <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $t)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWarehouseModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Warehouse
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== WAREHOUSE GRID ==================== --}}
    <div class="row g-4">
        @forelse($warehouses as $wh)
            <div class="col-xl-4 col-lg-6">
                <div class="warehouse-card">
                    <div class="warehouse-header">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center gap-3">
                                <div class="warehouse-icon bg-{{ $wh->type_color }} bg-opacity-10 text-{{ $wh->type_color }}">
                                    <i class="bi {{ $wh->type_icon }}"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1 text-dark" style="font-size: 1rem;">{{ $wh->name }}</h5>
                                    <small class="text-muted font-monospace">{{ $wh->code }}</small>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light rounded-circle" data-bs-toggle="dropdown" style="width:32px; height:32px;">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                                    <li><a class="dropdown-item" href="{{ route('operations.warehouses.show', $wh) }}"><i class="bi bi-eye me-2"></i>View Details</a></li>
                                    <li><a class="dropdown-item edit-warehouse-btn" href="#" data-bs-toggle="modal" data-bs-target="#editWarehouseModal"
                                        data-id="{{ $wh->id }}" data-name="{{ $wh->name }}" data-code="{{ $wh->code }}" data-type="{{ $wh->type }}"
                                        data-address="{{ $wh->address }}" data-city="{{ $wh->city }}" data-state="{{ $wh->state }}" data-pincode="{{ $wh->pincode }}"
                                        data-manager="{{ $wh->manager_name }}" data-phone="{{ $wh->manager_phone }}" data-email="{{ $wh->manager_email }}"
                                        data-status="{{ $wh->status }}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('operations.warehouses.destroy', $wh) }}" onsubmit="return confirm('Delete this warehouse?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-{{ $wh->type_color }} bg-opacity-10 text-{{ $wh->type_color }} me-1">
                                {{ ucfirst(str_replace('_', ' ', $wh->type)) }}
                            </span>
                            <span class="badge bg-{{ $wh->status === 'active' ? 'success' : ($wh->status === 'maintenance' ? 'warning' : 'secondary') }} bg-opacity-10 text-{{ $wh->status === 'active' ? 'success' : ($wh->status === 'maintenance' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($wh->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="p-3">
                        {{-- Location --}}
                        <div class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-geo-alt text-muted mt-1"></i>
                            <small class="text-muted">{{ $wh->full_address }}</small>
                        </div>

                        {{-- Manager --}}
                        @if($wh->manager_name)
                            <div class="d-flex align-items-start gap-2 mb-3">
                                <i class="bi bi-person-badge text-muted mt-1"></i>
                                <div>
                                    <small class="fw-semibold d-block">{{ $wh->manager_name }}</small>
                                    <small class="text-muted">{{ $wh->manager_phone ?? $wh->manager_email }}</small>
                                </div>
                            </div>
                        @endif

                        {{-- Stats --}}
                        <div class="stat-mini">
                            <span class="stat-mini-label">Total Bins</span>
                            <span class="stat-mini-value">{{ $wh->bins_count }}</span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-label">Total Area</span>
                            <span class="stat-mini-value">{{ number_format($wh->total_area_sqft) }} sqft</span>
                        </div>
                        <div class="stat-mini">
                            <span class="stat-mini-label">Capacity</span>
                            <span class="stat-mini-value">{{ number_format($wh->total_capacity) }} units</span>
                        </div>

                        {{-- Utilization --}}
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted fw-semibold">Utilization</small>
                                <small class="fw-bold">{{ $wh->utilization_percent }}%</small>
                            </div>
                            <div class="utilization-bar">
                                <div class="utilization-fill" style="width: {{ $wh->utilization_percent }}%; background: {{ $wh->utilization_percent > 85 ? '#ef4444' : ($wh->utilization_percent > 60 ? '#f59e0b' : '#10b981') }};"></div>
                            </div>
                        </div>

                        <a href="{{ route('operations.warehouses.show', $wh) }}" class="btn btn-outline-primary btn-sm w-100 mt-3 fw-semibold">
                            <i class="bi bi-arrow-right me-1"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm text-center py-5">
                    <i class="bi bi-building text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-muted">No warehouses found</h5>
                    <p class="text-muted small">Click "Add Warehouse" to create your first warehouse.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if($warehouses->hasPages())
        <div class="d-flex justify-content-center mt-4">{{ $warehouses->links() }}</div>
    @endif

    {{-- ==================== ADD WAREHOUSE MODAL ==================== --}}
    <div class="modal fade" id="addWarehouseModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form method="POST" action="{{ route('operations.warehouses.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Warehouse</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <h6 class="fw-bold border-bottom pb-2 mb-3">Basic Information</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Warehouse Code *</label>
                            <input type="text" name="code" class="form-control" placeholder="WH-MUM-001" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Warehouse Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="Mumbai Main Warehouse" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Type *</label>
                            <select name="type" class="form-select" required>
                                <option value="main">Main Warehouse</option>
                                <option value="branch">Branch Warehouse</option>
                                <option value="cold_storage">Cold Storage</option>
                                <option value="hazmat">Hazmat Storage</option>
                                <option value="return">Return Center</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="maintenance">Under Maintenance</option>
                            </select>
                        </div>
                    </div>

                    <h6 class="fw-bold border-bottom pb-2 mb-3">Location Details</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Full street address"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">City</label>
                            <input type="text" name="city" class="form-control" placeholder="Mumbai">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">State</label>
                            <input type="text" name="state" class="form-control" placeholder="Maharashtra">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Pincode</label>
                            <input type="text" name="pincode" class="form-control" placeholder="400001">
                        </div>
                    </div>

                    <h6 class="fw-bold border-bottom pb-2 mb-3">Manager Information</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Manager Name</label>
                            <input type="text" name="manager_name" class="form-control" placeholder="Rajesh Kumar">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="manager_phone" class="form-control" placeholder="+91 98765 43210">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="manager_email" class="form-control" placeholder="manager@example.com">
                        </div>
                    </div>

                    <h6 class="fw-bold border-bottom pb-2 mb-3">Capacity</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Total Area (sqft)</label>
                            <input type="number" name="total_area_sqft" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Pallet Capacity</label>
                            <input type="number" name="pallet_capacity" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Shelf Capacity</label>
                            <input type="number" name="shelf_capacity" class="form-control" value="0" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Create Warehouse</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== EDIT WAREHOUSE MODAL ==================== --}}
    <div class="modal fade" id="editWarehouseModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form method="POST" id="editWarehouseForm" class="modal-content">
                @csrf @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Warehouse</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Code</label>
                            <input type="text" id="edit_code" class="form-control" readonly>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Name *</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Type *</label>
                            <select name="type" id="edit_type" class="form-select" required>
                                <option value="main">Main Warehouse</option>
                                <option value="branch">Branch Warehouse</option>
                                <option value="cold_storage">Cold Storage</option>
                                <option value="hazmat">Hazmat Storage</option>
                                <option value="return">Return Center</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status *</label>
                            <select name="status" id="edit_status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="maintenance">Under Maintenance</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">City</label>
                            <input type="text" name="city" id="edit_city" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">State</label>
                            <input type="text" name="state" id="edit_state" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Pincode</label>
                            <input type="text" name="pincode" id="edit_pincode" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Manager Name</label>
                            <input type="text" name="manager_name" id="edit_manager" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="manager_phone" id="edit_phone" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="manager_email" id="edit_email" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i> Update Warehouse</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.edit-warehouse-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            document.getElementById('editWarehouseForm').action = `/operations/warehouses/${id}`;
            document.getElementById('edit_code').value = this.dataset.code;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_type').value = this.dataset.type;
            document.getElementById('edit_status').value = this.dataset.status;
            document.getElementById('edit_address').value = this.dataset.address || '';
            document.getElementById('edit_city').value = this.dataset.city || '';
            document.getElementById('edit_state').value = this.dataset.state || '';
            document.getElementById('edit_pincode').value = this.dataset.pincode || '';
            document.getElementById('edit_manager').value = this.dataset.manager || '';
            document.getElementById('edit_phone').value = this.dataset.phone || '';
            document.getElementById('edit_email').value = this.dataset.email || '';
        });
    });
</script>
@endpush