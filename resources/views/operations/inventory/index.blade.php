
@extends('layouts.master')
@section('title', 'Inventory')
@section('page-title', 'Product Inventory')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters & Actions Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('operations.inventory.index') }}" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search SKU or Name..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="stock_status" class="form-select">
                        <option value="">All Stock Levels</option>
                        <option value="ok" {{ request('stock_status') == 'ok' ? 'selected' : '' }}>OK</option>
                        <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('operations.inventory.export', request()->query()) }}" class="btn btn-outline-success">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">SKU</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Stock Level</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            @php
                                $stockStatus = $product->stock_qty <= 0 ? 'out_of_stock' : ($product->stock_qty <= $product->reorder_level ? 'low' : 'ok');
                            @endphp
                            <tr>
                                <td class="ps-4 fw-bold text-primary">{{ $product->sku }}</td>
                                <td>{{ $product->name }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $product->category ?? 'Uncategorized' }}</span></td>
                                <td>
                                    @if($stockStatus == 'ok')
                                        <span class="badge bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle-fill me-1"></i>OK ({{ $product->stock_qty }})</span>
                                    @elseif($stockStatus == 'low')
                                        <span class="badge bg-warning bg-opacity-10 text-warning"><i class="bi bi-exclamation-triangle-fill me-1"></i>Low ({{ $product->stock_qty }})</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger"><i class="bi bi-x-circle-fill me-1"></i>Out of Stock</span>
                                    @endif
                                </td>
                                <td class="fw-semibold">₹{{ number_format($product->price, 2) }}</td>
                                <td><span class="badge bg-{{ $product->status == 'active' ? 'primary' : 'secondary' }}">{{ ucfirst($product->status) }}</span></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary edit-btn" 
                                        data-id="{{ $product->id }}" 
                                        data-sku="{{ $product->sku }}" 
                                        data-name="{{ $product->name }}" 
                                        data-category="{{ $product->category }}" 
                                        data-price="{{ $product->price }}" 
                                        data-cost="{{ $product->cost_price }}" 
                                        data-reorder="{{ $product->reorder_level }}"
                                        data-bs-toggle="modal" data-bs-target="#editProductModal">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
                                    No products found. Click "Add Product" to get started.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($products->hasPages())
            <div class="card-footer bg-white border-top-0 pt-3">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- ADD PRODUCT MODAL -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('operations.inventory.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Product</h5>
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
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category</label>
                            <input type="text" name="category" class="form-control" placeholder="e.g., Electronics">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Selling Price (₹) *</label>
                            <input type="number" name="price" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Cost Price (₹) *</label>
                            <input type="number" name="cost_price" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Initial Stock Qty *</label>
                            <input type="number" name="stock_qty" class="form-control" value="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Reorder Level *</label>
                            <input type="number" name="reorder_level" class="form-control" value="10" required>
                            <small class="text-muted">Alert when stock falls below this number.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT PRODUCT MODAL -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" id="editForm" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fw-semibold">SKU *</label><input type="text" name="sku" id="edit_sku" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Product Name *</label><input type="text" name="name" id="edit_name" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Category</label><input type="text" name="category" id="edit_category" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Selling Price (₹) *</label><input type="number" name="price" id="edit_price" class="form-control" step="0.01" required></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Cost Price (₹) *</label><input type="number" name="cost_price" id="edit_cost" class="form-control" step="0.01" required></div>
                        <div class="col-md-12"><label class="form-label fw-semibold">Reorder Level *</label><input type="number" name="reorder_level" id="edit_reorder" class="form-control" required></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Product</button>
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
            document.getElementById('editForm').action = `/operations/inventory/${id}`;
            document.getElementById('edit_sku').value = this.dataset.sku;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_category').value = this.dataset.category || '';
            document.getElementById('edit_price').value = this.dataset.price;
            document.getElementById('edit_cost').value = this.dataset.cost;
            document.getElementById('edit_reorder').value = this.dataset.reorder;
        });
    });
</script>
@endpush