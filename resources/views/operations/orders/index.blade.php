@extends('layouts.master')
@section('title', 'Orders')
@section('page-title', 'Order Management')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <!-- Tabs & Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <ul class="nav nav-pills" id="orderTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ request('type') != 'inbound' ? 'active' : '' }}" href="{{ route('operations.orders.index', array_merge(request()->query(), ['type' => 'outbound'])) }}">Outbound (Sales)</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('type') == 'inbound' ? 'active' : '' }}" href="{{ route('operations.orders.index', array_merge(request()->query(), ['type' => 'inbound'])) }}">Inbound (Purchase)</a>
            </li>
        </ul>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newOrderModal"><i class="bi bi-plus-lg me-1"></i> New Order</button>
    </div>

    <!-- Status Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('operations.orders.index') }}" class="row g-3 align-items-center">
                <input type="hidden" name="type" value="{{ request('type', 'outbound') }}">
                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Order No</th>
                            <th>Customer</th>
                            <th>Customer GSTIN</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="ps-4 fw-bold text-primary">{{ $order->order_no }}</td>
                                <td>{{ $order->customer->name }}</td>
                                <td><span class="text-muted small">{{ $order->customer->gstin ?: 'N/A' }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($order->order_date)->format('d M Y') }}</td>
                                <td class="fw-semibold">₹{{ number_format($order->total, 2) }}</td>
                                <td>
                                    <form method="POST" action="{{ route('operations.orders.update-status', $order) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="form-select form-select-sm border-0 bg-transparent fw-bold" 
                                            style="color: {{ $order->status == 'delivered' ? '#198754' : ($order->status == 'pending' ? '#ffc107' : '#0d6efd') }}"
                                            onchange="this.form.submit()">
                                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                            <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                            <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('operations.orders.invoice', $order) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print Invoice">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-cart-x fs-1 d-block mb-2"></i>
                                    No orders found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($orders->hasPages())
            <div class="card-footer bg-white border-top-0 pt-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    <!-- NEW ORDER MODAL -->
    <div class="modal fade" id="newOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('operations.orders.store') }}" id="orderForm" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-cart-plus me-2"></i>Create New Order</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Order Type</label>
                            <select name="type" class="form-select" required>
                                <option value="outbound">Outbound (Sales)</option>
                                <option value="inbound">Inbound (Purchase)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Customer</label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->gstin }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Order Date</label>
                            <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <h6 class="fw-bold border-bottom pb-2 mb-3">Order Items</h6>
                    <div id="orderItemsContainer">
                        <div class="row g-2 item-row mb-2">
                            <div class="col-md-5">
                                <select name="items[0][product_id]" class="form-select product-select" required onchange="updatePrice(this)">
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-gst="{{ $product->gst_rate }}">{{ $product->sku }} - {{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="items[0][qty]" class="form-control" placeholder="Qty" value="1" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="items[0][unit_price]" class="form-control unit-price" placeholder="Price" step="0.01" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-center">
                                <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest('.item-row').remove()"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addOrderItem()"><i class="bi bi-plus-circle me-1"></i> Add Another Item</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Order</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let itemCount = 1;
    function addOrderItem() {
        const container = document.getElementById('orderItemsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'row g-2 item-row mb-2';
        newRow.innerHTML = `
            <div class="col-md-5">
                <select name="items[${itemCount}][product_id]" class="form-select product-select" required onchange="updatePrice(this)">
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-gst="{{ $product->gst_rate }}">{{ $product->sku }} - {{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="items[${itemCount}][qty]" class="form-control" placeholder="Qty" value="1" min="1" required>
            </div>
            <div class="col-md-3">
                <input type="number" name="items[${itemCount}][unit_price]" class="form-control unit-price" placeholder="Price" step="0.01" required>
            </div>
            <div class="col-md-2 d-flex align-items-center">
                <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest('.item-row').remove()"><i class="bi bi-trash"></i></button>
            </div>
        `;
        container.appendChild(newRow);
        itemCount++;
    }

    function updatePrice(selectElement) {
        const price = selectElement.options[selectElement.selectedIndex].dataset.price;
        const row = selectElement.closest('.item-row');
        row.querySelector('.unit-price').value = price;
    }
</script>
@endpush