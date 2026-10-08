@extends('layouts.master')
@section('title', 'BOM Management')
@section('page-title', 'Bill of Materials')

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

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="small mb-1 opacity-75">Total BOMs</p>
                            <h3 class="fw-bold mb-0">{{ $stats['total'] }}</h3>
                        </div>
                        <i class="bi bi-diagram-3 fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="small mb-1 opacity-75">Active</p>
                            <h3 class="fw-bold mb-0">{{ $stats['active'] }}</h3>
                        </div>
                        <i class="bi bi-check-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="small mb-1 opacity-75">Draft</p>
                            <h3 class="fw-bold mb-0">{{ $stats['draft'] }}</h3>
                        </div>
                        <i class="bi bi-pencil fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-dark text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="small mb-1 opacity-75">Archived</p>
                            <h3 class="fw-bold mb-0">{{ $stats['archived'] }}</h3>
                        </div>
                        <i class="bi bi-archive fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters & Actions --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('operations.bom.index') }}" class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search BOM#, Product Name, SKU..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-outline-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newBomModal">
                        <i class="bi bi-plus-lg me-1"></i> Create BOM
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- BOM Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">BOM Number</th>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Components</th>
                            <th>Unit Cost</th>
                            <th>Version</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($boms as $bom)
                            <tr>
                                <td class="ps-4 fw-bold text-primary">{{ $bom->bom_no }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $bom->product_name }}</div>
                                    @if($bom->product)
                                        <small class="text-muted">ID: {{ $bom->product->id }}</small>
                                    @endif
                                </td>
                                <td><code>{{ $bom->product_sku }}</code></td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        <i class="bi bi-box-seam me-1"></i>{{ $bom->components_count }}
                                    </span>
                                </td>
                                <td class="fw-semibold">₹{{ number_format($bom->unit_cost, 2) }}</td>
                                <td><span class="badge bg-light text-dark border">v{{ $bom->version }}</span></td>
                                <td>
                                    <span class="badge bg-{{ $bom->status_color }}">{{ ucfirst($bom->status) }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('operations.bom.show', $bom) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form method="POST" action="{{ route('operations.bom.destroy', $bom) }}" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete BOM">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-diagram-3 fs-1 d-block mb-2"></i>
                                    No BOMs found. Click "Create BOM" to define your first recipe.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($boms->hasPages())
            <div class="card-footer bg-white border-top-0 pt-3">{{ $boms->links() }}</div>
        @endif
    </div>

    {{-- NEW BOM MODAL --}}
    <div class="modal fade" id="newBomModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="{{ route('operations.bom.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Create New BOM</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Select Product *</label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Choose a product...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <h6 class="fw-bold border-bottom pb-2 mb-3">Components</h6>
                    <div id="bomComponentsContainer">
                        <div class="row g-2 component-row mb-2">
                            <div class="col-md-3">
                                <input type="text" name="components[0][component_sku]" class="form-control" placeholder="SKU" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="components[0][component_name]" class="form-control" placeholder="Component Name" required>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="components[0][qty_required]" class="form-control" placeholder="Qty" step="0.001" min="0.001" required>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="components[0][unit_cost]" class="form-control unit-cost" placeholder="Unit Cost" step="0.01" min="0" required onchange="calculateTotal()">
                            </div>
                            <div class="col-md-1">
                                <input type="text" name="components[0][unit]" class="form-control" placeholder="Unit" value="PCS">
                            </div>
                            <div class="col-md-1 d-flex align-items-center">
                                <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest('.component-row').remove(); calculateTotal();"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addComponentRow()"><i class="bi bi-plus-circle me-1"></i> Add Component</button>

                    <div class="mt-4 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Total Unit Cost:</span>
                            <h4 class="fw-bold text-primary mb-0" id="totalUnitCost">₹0.00</h4>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create BOM</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let componentCount = 1;

    function addComponentRow() {
        const container = document.getElementById('bomComponentsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'row g-2 component-row mb-2';
        newRow.innerHTML = `
            <div class="col-md-3">
                <input type="text" name="components[${componentCount}][component_sku]" class="form-control" placeholder="SKU" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="components[${componentCount}][component_name]" class="form-control" placeholder="Component Name" required>
            </div>
            <div class="col-md-2">
                <input type="number" name="components[${componentCount}][qty_required]" class="form-control" placeholder="Qty" step="0.001" min="0.001" required>
            </div>
            <div class="col-md-2">
                <input type="number" name="components[${componentCount}][unit_cost]" class="form-control unit-cost" placeholder="Unit Cost" step="0.01" min="0" required onchange="calculateTotal()">
            </div>
            <div class="col-md-1">
                <input type="text" name="components[${componentCount}][unit]" class="form-control" placeholder="Unit" value="PCS">
            </div>
            <div class="col-md-1 d-flex align-items-center">
                <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest('.component-row').remove(); calculateTotal();"><i class="bi bi-trash"></i></button>
            </div>
        `;
        container.appendChild(newRow);
        componentCount++;
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.component-row').forEach(row => {
            const qty = parseFloat(row.querySelector('input[name*="qty_required"]').value) || 0;
            const cost = parseFloat(row.querySelector('input[name*="unit_cost"]').value) || 0;
            total += qty * cost;
        });
        document.getElementById('totalUnitCost').textContent = '₹' + total.toFixed(2);
    }
</script>
@endpush