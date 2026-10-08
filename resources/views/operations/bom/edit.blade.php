@extends('layouts.master')
@section('title', 'Edit BOM')
@section('page-title', 'Edit BOM: ' . $bom->bom_no)

@section('content')
    <div class="alert alert-info">
        <i class="bi bi-info-circle-fill me-2"></i>
        <strong>Version Control:</strong> Editing this BOM will create a new version (v{{ $bom->version + 1 }}). The current version (v{{ $bom->version }}) will be archived.
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-warning">
            <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit BOM Recipe (Creates New Version)</h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('operations.bom.update', $bom) }}" id="bomForm">
                @csrf
                @method('PUT')

                {{-- Product Selection --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Select Product *</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">Choose a product...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ $product->id == $bom->product_id ? 'selected' : '' }}>
                                    {{ $product->sku }} - {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Current Version</label>
                        <input type="text" class="form-control bg-light" value="v{{ $bom->version }} (will become v{{ $bom->version + 1 }})" disabled>
                    </div>
                </div>

                <hr class="my-4">

                {{-- Components Section --}}
                <h6 class="fw-bold mb-3"><i class="bi bi-box me-2"></i>Components / Raw Materials</h6>
                <div id="componentsContainer">
                    @foreach($bom->components as $index => $comp)
                        <div class="component-row card mb-3 border">
                            <div class="card-body">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Component SKU *</label>
                                        <input type="text" name="components[{{ $index }}][component_sku]" class="form-control" value="{{ $comp->component_sku }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Component Name *</label>
                                        <input type="text" name="components[{{ $index }}][component_name]" class="form-control" value="{{ $comp->component_name }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small fw-semibold">Qty Required *</label>
                                        <input type="number" name="components[{{ $index }}][qty_required]" class="form-control qty-input" step="0.001" min="0.001" value="{{ $comp->qty_required }}" required>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label small fw-semibold">Unit</label>
                                        <select name="components[{{ $index }}][unit]" class="form-select">
                                            <option value="PCS" {{ $comp->unit == 'PCS' ? 'selected' : '' }}>PCS</option>
                                            <option value="KG" {{ $comp->unit == 'KG' ? 'selected' : '' }}>KG</option>
                                            <option value="G" {{ $comp->unit == 'G' ? 'selected' : '' }}>G</option>
                                            <option value="L" {{ $comp->unit == 'L' ? 'selected' : '' }}>L</option>
                                            <option value="M" {{ $comp->unit == 'M' ? 'selected' : '' }}>M</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small fw-semibold">Unit Cost (₹) *</label>
                                        <input type="number" name="components[{{ $index }}][unit_cost]" class="form-control cost-input" step="0.01" min="0" value="{{ $comp->unit_cost }}" required>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label small fw-semibold">Wastage %</label>
                                        <input type="number" name="components[{{ $index }}][wastage_percentage]" class="form-control wastage-input" step="0.01" min="0" max="100" value="{{ $comp->wastage_percentage }}">
                                    </div>
                                    @if($index > 0)
                                        <div class="col-md-12 text-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.component-row').remove(); calculateCosts();">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-outline-primary btn-sm mb-4" onclick="addComponent()">
                    <i class="bi bi-plus-circle me-1"></i> Add Another Component
                </button>

                <hr class="my-4">

                {{-- Cost Summary --}}
                <div class="card bg-light border-0 mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3"><i class="bi bi-calculator me-2"></i>Cost Summary (Auto-Calculated)</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1 text-muted small">Base Material Cost</p>
                                <h4 class="fw-bold text-primary mb-0" id="baseCost">₹0.00</h4>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1 text-muted small">Wastage Cost</p>
                                <h4 class="fw-bold text-warning mb-0" id="wastageCost">₹0.00</h4>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1 text-muted small">Total Unit Cost</p>
                                <h4 class="fw-bold text-success mb-0" id="totalCost">₹0.00</h4>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('operations.bom.show', $bom) }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save me-1"></i> Update BOM (Create v{{ $bom->version + 1 }})
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let componentIndex = {{ $bom->components->count() }};

    function addComponent() {
        const container = document.getElementById('componentsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'component-row card mb-3 border';
        newRow.innerHTML = `
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Component SKU *</label>
                        <input type="text" name="components[${componentIndex}][component_sku]" class="form-control" placeholder="RAW-001" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Component Name *</label>
                        <input type="text" name="components[${componentIndex}][component_name]" class="form-control" placeholder="Steel Rod" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Qty Required *</label>
                        <input type="number" name="components[${componentIndex}][qty_required]" class="form-control qty-input" step="0.001" min="0.001" value="1" required>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small fw-semibold">Unit</label>
                        <select name="components[${componentIndex}][unit]" class="form-select">
                            <option value="PCS">PCS</option>
                            <option value="KG">KG</option>
                            <option value="G">G</option>
                            <option value="L">L</option>
                            <option value="M">M</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Unit Cost (₹) *</label>
                        <input type="number" name="components[${componentIndex}][unit_cost]" class="form-control cost-input" step="0.01" min="0" value="0" required>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small fw-semibold">Wastage %</label>
                        <input type="number" name="components[${componentIndex}][wastage_percentage]" class="form-control wastage-input" step="0.01" min="0" max="100" value="0">
                    </div>
                    <div class="col-md-12 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.component-row').remove(); calculateCosts();">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(newRow);
        componentIndex++;
        attachCostListeners();
    }

    function calculateCosts() {
        let baseCost = 0;
        let wastageCost = 0;

        document.querySelectorAll('.component-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
            const wastage = parseFloat(row.querySelector('.wastage-input')?.value) || 0;

            const lineTotal = qty * cost;
            const lineWastage = lineTotal * (wastage / 100);

            baseCost += lineTotal;
            wastageCost += lineWastage;
        });

        const totalCost = baseCost + wastageCost;

        document.getElementById('baseCost').textContent = '₹' + baseCost.toFixed(2);
        document.getElementById('wastageCost').textContent = '₹' + wastageCost.toFixed(2);
        document.getElementById('totalCost').textContent = '₹' + totalCost.toFixed(2);
    }

    function attachCostListeners() {
        document.querySelectorAll('.qty-input, .cost-input, .wastage-input').forEach(input => {
            input.addEventListener('input', calculateCosts);
        });
    }

    // Initial calculation on page load
    calculateCosts();
    attachCostListeners();
</script>
@endpush