@extends('layouts.master')
@section('title', 'Inventory Management')
@section('page-title', 'Inventory & Project Details')

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

    <div class="row g-4">
        {{-- ==================== LEFT: Product List & Filters ==================== --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 90px; z-index: 10;">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-box-seam me-2 text-primary"></i>Products</h6>
                    <div class="d-flex gap-2">
                        <a href="{{ route('operations.inventory.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                        {{-- ✅ Stock In Button --}}
                        <button class="btn btn-sm btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#stockInModal">
                            <i class="bi bi-box-arrow-in-down me-1"></i> Stock In
                        </button>
                        {{-- ✅ Add Product Button --}}
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="bi bi-plus-lg me-1"></i> Add Product
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('operations.inventory.index') }}" class="mb-3">
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Search name, SKU, category..." value="{{ request('search') }}">
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="all">All Categories</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <select name="stock_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Stock</option>
                                    <option value="healthy" {{ request('stock_status') == 'healthy' ? 'selected' : '' }}>Healthy</option>
                                    <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock</option>
                                    <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                                </select>
                            </div>
                            @if($warehouses->count() > 1)
                                <div class="col-12">
                                    <select name="warehouse_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="all">All Warehouses</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                    </form>

                    <div class="list-group list-group-flush" id="productList" style="max-height: 500px; overflow-y: auto;">
                        @forelse($products as $prod)
                            <a href="#" class="list-group-item list-group-item-action border-0 rounded-3 mb-2 product-item bg-white" 
                               data-id="{{ $prod->id }}" style="transition: all 0.2s; border: 1px solid #eee !important;">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 fw-semibold text-dark">{{ $prod->name }}</h6>
                                        <small class="text-muted font-monospace">{{ $prod->sku }}</small>
                                    </div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        {{ $prod->warehouse_stock ?? $prod->stock_qty }} {{ $prod->unit ?? 'pcs' }}
                                    </span>
                                </div>
                            </a>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No products found.
                            </div>
                        @endforelse
                    </div>
                    
                    @if($products->hasPages())
                        <div class="mt-3">{{ $products->appends(request()->query())->links() }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ==================== RIGHT: Detailed View Panel ==================== --}}
        <div class="col-lg-8">
            
            {{-- Empty State (Shown initially) --}}
            <div id="emptyState" class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="bi bi-cursor text-primary" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 fw-bold">Select a Product</h5>
                    <p class="text-muted">Click on any product from the left list to view its detailed inventory, shipment history, and return records.</p>
                </div>
            </div>

            {{-- Loading Spinner --}}
            <div id="loadingSpinner" class="d-none text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Fetching product details...</p>
            </div>

            {{-- Details Panel (Populated via AJAX) --}}
            <div id="detailsPanel" class="d-none">
                {{-- Header --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h4 class="fw-bold mb-1" id="detailName">Product Name</h4>
                            <span class="badge bg-light text-dark border font-monospace" id="detailSku">SKU-000</span>
                            <span class="badge bg-success bg-opacity-10 text-success ms-2">Active</span>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">Total Remaining Stock</small>
                            <h2 class="fw-bold text-primary mb-0" id="detailStock">0</h2>
                        </div>
                    </div>
                </div>

                {{-- KPI Cards --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-danger bg-opacity-10 h-100">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <div class="bg-danger text-white p-2 rounded-circle">
                                    <i class="bi bi-exclamation-triangle fs-5"></i>
                                </div>
                                <div>
                                    <small class="text-muted fw-bold text-uppercase">Damaged / Defected</small>
                                    <h4 class="fw-bold mb-0 text-danger" id="kpiDamaged">0</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-warning bg-opacity-10 h-100">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <div class="bg-warning text-dark p-2 rounded-circle">
                                    <i class="bi bi-arrow-counterclockwise fs-5"></i>
                                </div>
                                <div>
                                    <small class="text-muted fw-bold text-uppercase">Total Returned</small>
                                    <h4 class="fw-bold mb-0 text-warning" id="kpiReturned">0</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-info bg-opacity-10 h-100">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <div class="bg-info text-white p-2 rounded-circle">
                                    <i class="bi bi-truck fs-5"></i>
                                </div>
                                <div>
                                    <small class="text-muted fw-bold text-uppercase">Active Shipments</small>
                                    <h4 class="fw-bold mb-0 text-info" id="kpiShipments">0</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabs: Shipments & Returns --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <ul class="nav nav-tabs card-header-tabs" id="detailTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#tabShipments">
                                    <i class="bi bi-truck me-1"></i> Shipment History
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tabReturns">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Return Orders
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="tab-content">
                            {{-- Shipment History Tab --}}
                            <div class="tab-pane fade show active p-3" id="tabShipments">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Shipment #</th>
                                                <th>Route</th>
                                                <th>Batch No</th>
                                                <th>Qty</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody id="shipmentTableBody">
                                            <!-- Populated via JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Return Orders Tab --}}
                            <div class="tab-pane fade p-3" id="tabReturns">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Order #</th>
                                                <th>Customer</th>
                                                <th>Returned Qty</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="returnTableBody">
                                            <!-- Populated via JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== ADD PRODUCT MODAL ==================== --}}
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form id="addProductForm" class="modal-content">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>Add New Product</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">SKU *</label>
                            <input type="text" name="sku" class="form-control" placeholder="e.g., PRD-001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Product Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., A4 Paper Bundle" required>
                        </div>
                        
                        {{-- ✅ NEW: Warehouse Dropdown --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Store in Warehouse *</label>
                            <select name="warehouse_id" class="form-select" required>
                                <option value="">Select Warehouse</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }} ({{ $warehouse->code ?? 'WH' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Category</label>
                            <input type="text" name="category" class="form-control" placeholder="e.g., Stationery">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Unit</label>
                            <select name="unit" class="form-select">
                                <option value="PCS">Pieces (PCS)</option>
                                <option value="KG">Kilograms (KG)</option>
                                <option value="LTR">Liters (LTR)</option>
                                <option value="BOX">Boxes (BOX)</option>
                                <option value="SET">Sets (SET)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">HSN Code</label>
                            <input type="text" name="hsn_code" class="form-control" placeholder="e.g., 4802">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Selling Price (₹) *</label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Cost Price (₹) *</label>
                            <input type="number" name="cost_price" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">GST Rate (%)</label>
                            <select name="gst_rate" class="form-select">
                                <option value="0">0%</option>
                                <option value="5">5%</option>
                                <option value="12">12%</option>
                                <option value="18" selected>18%</option>
                                <option value="28">28%</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-primary">Initial Stock Qty *</label>
                            <input type="number" name="stock_qty" class="form-control" min="0" value="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-warning">Reorder Level *</label>
                            <input type="number" name="reorder_level" class="form-control" min="0" value="10" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="submitProductBtn">
                        <i class="bi bi-check-lg me-1"></i> Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== STOCK IN MODAL ==================== --}}
    <div class="modal fade" id="stockInModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('operations.inventory.stock-in') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-box-arrow-in-down me-2"></i>Add Stock to Warehouse</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Product *</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">Choose Product</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Warehouse *</label>
                        <select name="warehouse_id" class="form-select" required>
                            <option value="">Choose Warehouse</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code ?? 'WH' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Quantity to Add *</label>
                            <input type="number" name="qty" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Batch No (Optional)</label>
                            <input type="text" name="batch_no" class="form-control" placeholder="Auto-generated if empty">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold">
                        <i class="bi bi-check-lg me-1"></i> Add Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // ==================== 1. PRODUCT DETAILS AJAX ====================
    document.querySelectorAll('.product-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            document.querySelectorAll('.product-item').forEach(i => {
                i.classList.remove('active', 'bg-primary', 'bg-opacity-10');
                i.style.borderColor = '#eee';
            });
            this.classList.add('active', 'bg-primary', 'bg-opacity-10');
            this.style.borderColor = 'var(--bs-primary)';

            const productId = this.dataset.id;
            const detailsPanel = document.getElementById('detailsPanel');
            const emptyState = document.getElementById('emptyState');
            const loadingSpinner = document.getElementById('loadingSpinner');

            emptyState.classList.add('d-none');
            detailsPanel.classList.add('d-none');
            loadingSpinner.classList.remove('d-none');

            fetch(`/operations/inventory/${productId}/details`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                loadingSpinner.classList.add('d-none');
                detailsPanel.classList.remove('d-none');

                document.getElementById('detailName').textContent = data.product.name;
                document.getElementById('detailSku').textContent = data.product.sku;
                document.getElementById('detailStock').textContent = data.kpis.total_stock + ' ' + (data.product.unit || 'pcs');
                
                document.getElementById('kpiDamaged').textContent = data.kpis.damaged_qty;
                document.getElementById('kpiReturned').textContent = data.kpis.returned_qty;
                document.getElementById('kpiShipments').textContent = data.kpis.active_shipments;

                // Shipments Table
                const shipmentTbody = document.getElementById('shipmentTableBody');
                shipmentTbody.innerHTML = '';
                if (data.shipments.length === 0) {
                    shipmentTbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">No shipment history found.</td></tr>';
                } else {
                    data.shipments.forEach(ship => {
                        const batch = ship.batches[0] || {};
                        const statusColor = ship.status === 'delivered' ? 'success' : (ship.status === 'in_transit' ? 'primary' : 'warning');
                        shipmentTbody.innerHTML += `
                            <tr>
                                <td class="fw-semibold text-primary">${ship.shipment_no}</td>
                                <td><small>${ship.origin} <i class="bi bi-arrow-right mx-1"></i> ${ship.destination}</small></td>
                                <td><span class="badge bg-light text-dark border">${batch.batch_no || 'N/A'}</span></td>
                                <td class="fw-bold">${batch.expected_qty || 0}</td>
                                <td><span class="badge bg-${statusColor} bg-opacity-10 text-${statusColor}">${ship.status.replace('_', ' ').toUpperCase()}</span></td>
                                <td class="text-muted small">${new Date(ship.created_at).toLocaleDateString()}</td>
                            </tr>
                        `;
                    });
                }

                // Returns Table
                const returnTbody = document.getElementById('returnTableBody');
                returnTbody.innerHTML = '';
                if (data.returns.length === 0) {
                    returnTbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No return records found.</td></tr>';
                } else {
                    data.returns.forEach(ret => {
                        const item = ret.items.find(i => i.product_id == productId) || {};
                        returnTbody.innerHTML += `
                            <tr>
                                <td class="fw-semibold text-danger">${ret.order_no}</td>
                                <td>${ret.customer ? ret.customer.name : 'Walk-in'}</td>
                                <td class="fw-bold text-warning">-${item.qty || 0}</td>
                                <td class="text-muted small">${new Date(ret.created_at).toLocaleDateString()}</td>
                                <td><span class="badge bg-secondary bg-opacity-10 text-secondary">Returned</span></td>
                            </tr>
                        `;
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching details:', error);
                loadingSpinner.classList.add('d-none');
                detailsPanel.classList.remove('d-none');
                document.getElementById('detailName').textContent = 'Error loading data';
            });
        });
    });

    // ==================== 2. SMOOTH ADD PRODUCT AJAX ====================
    document.getElementById('addProductForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('submitProductBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

        const formData = new FormData(this);

        fetch("{{ route('operations.inventory.index') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value
            }
        })
        .then(async response => {
            const data = await response.json().catch(() => ({ error: 'Server returned an invalid response.' }));
            if (!response.ok) {
                const errorMsg = data.errors ? Object.values(data.errors).flat().join('\n') : (data.message || `HTTP Error: ${response.status}`);
                throw new Error(errorMsg);
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                const modalEl = document.getElementById('addProductModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                this.reset();

                const alertHtml = `
                    <div class="alert alert-success alert-dismissible fade show shadow-sm mt-3" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                const mainRow = document.querySelector('.row.g-4');
                if (mainRow) mainRow.insertAdjacentHTML('beforebegin', alertHtml);

                const productList = document.getElementById('productList');
                const newItemHtml = `
                    <a href="#" class="list-group-item list-group-item-action border-0 rounded-3 mb-2 product-item bg-primary bg-opacity-10" 
                       data-id="${data.product.id}" style="transition: all 0.2s; border: 1px solid var(--bs-primary) !important;">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 fw-semibold text-dark">${data.product.name}</h6>
                                <small class="text-muted font-monospace">${data.product.sku}</small>
                            </div>
                            <span class="badge bg-primary text-white">${data.product.warehouse_stock} ${data.product.unit}</span>
                        </div>
                    </a>
                `;
                productList.insertAdjacentHTML('afterbegin', newItemHtml);

                const newItem = productList.querySelector(`[data-id="${data.product.id}"]`);
                if (newItem) newItem.click();
            }
        })
        .catch(error => {
            console.error('❌ Save Error:', error);
            alert('Failed to save product:\n\n' + error.message);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });
</script>
@endpush