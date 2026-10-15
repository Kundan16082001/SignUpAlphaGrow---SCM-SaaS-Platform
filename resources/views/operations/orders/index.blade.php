@extends('layouts.master')
@section('title', 'Orders')
@section('page-title', 'Order Management')

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

    <!-- Tabs & Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <ul class="nav nav-pills" id="orderTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ request('type') != 'inbound' ? 'active' : '' }}" href="{{ route('operations.orders.index', array_merge(request()->query(), ['type' => 'outbound'])) }}">
                    <i class="bi bi-box-arrow-up me-1"></i> Outbound (Sales)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('type') == 'inbound' ? 'active' : '' }}" href="{{ route('operations.orders.index', array_merge(request()->query(), ['type' => 'inbound'])) }}">
                    <i class="bi bi-box-arrow-in-down me-1"></i> Inbound (Purchase)
                </a>
            </li>
        </ul>
        <div class="d-flex gap-2">
            <a href="{{ route('operations.inventory.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-box-seam me-1"></i> View Inventory
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newOrderModal">
                <i class="bi bi-plus-lg me-1"></i> New Order
            </button>
        </div>
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
                            <th>{{ request('type') == 'inbound' ? 'Supplier' : 'Customer' }}</th>
                            <th>GSTIN</th>
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
                                <td>{{ $order->customer->name ?? 'N/A' }}</td>
                                <td><span class="text-muted small font-monospace">{{ $order->customer->gstin ?: 'N/A' }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($order->order_date)->format('d M Y') }}</td>
                                <td class="fw-semibold">₹{{ number_format($order->total, 2) }}</td>
                                <td>
                                    <form method="POST" action="{{ route('operations.orders.update-status', $order) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        @php
                                            $colors = ['pending' => 'warning', 'processing' => 'info', 'shipped' => 'primary', 'delivered' => 'success', 'cancelled' => 'danger'];
                                        @endphp
                                        <select name="status" class="form-select form-select-sm border-0 bg-transparent fw-bold" 
                                            style="color: var(--bs-{{ $colors[$order->status] ?? 'secondary' }})"
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
                                    <a href="{{ route('operations.orders.invoice', $order) }}" target="_self" class="btn btn-sm btn-outline-secondary" title="Print Invoice">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-cart-x fs-1 d-block mb-2"></i> No orders found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($orders->hasPages())
            <div class="card-footer bg-white border-top-0 pt-3">{{ $orders->links() }}</div>
        @endif
    </div>

    <!-- NEW ORDER MODAL -->
    <div class="modal fade" id="newOrderModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="{{ route('operations.orders.store') }}" id="orderForm" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-cart-plus me-2"></i>Create New Order</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Order Type *</label>
                            <select name="type" id="orderType" class="form-select" required>
                                <option value="outbound">Outbound (Sales)</option>
                                <option value="inbound">Inbound (Purchase)</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">
                                <span id="customerLabel">Customer</span> *
                            </label>
                            <select name="customer_id" id="customerSelect" class="form-select" required onchange="toggleNewCustomerFields(this.value)">
                                <option value="">Select {{ request('type') == 'inbound' ? 'Supplier' : 'Customer' }}</option>
                                <option value="new">+ Add New {{ request('type') == 'inbound' ? 'Supplier' : 'Customer' }}</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->gstin ?: 'No GSTIN' }})</option>
                                @endforeach
                            </select>
                            
                            <!-- Hidden New Customer Fields -->
                            <div id="newCustomerFields" class="mt-2 p-3 bg-light rounded border d-none">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <input type="text" name="new_customer_name" class="form-control form-control-sm" placeholder="Full Name / Company Name">
                                    </div>
                                    <div class="col-6">
                                        <input type="text" name="new_customer_phone" class="form-control form-control-sm" placeholder="Phone Number">
                                    </div>
                                    <div class="col-6">
                                        <input type="text" name="new_customer_gstin" class="form-control form-control-sm" placeholder="GSTIN (Optional)">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Order Date *</label>
                            <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Order Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Special instructions, delivery notes, etc."></textarea>
                        </div>
                    </div>

                    <h6 class="fw-bold border-bottom pb-2 mb-3">Order Items</h6>
                    <div id="orderItemsContainer">
                        <!-- Initial Row will be injected by JS on load -->
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addOrderItem()">
                        <i class="bi bi-plus-circle me-1"></i> Add Another Item
                    </button>

                    <!-- Grand Total Display -->
                    <div class="d-flex justify-content-end align-items-center mt-4 pt-3 border-top">
                        <h5 class="mb-0 me-3 text-muted">Grand Total:</h5>
                        <h4 class="mb-0 text-primary fw-bold" id="grandTotalDisplay">₹0.00</h4>
                    </div>
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
    let itemCount = 0;
    const productsData = @json($products);

    // Initialize first row
    document.addEventListener('DOMContentLoaded', () => {
        addOrderItem();
        // Set initial label based on URL
        updateCustomerLabel();
    });

    document.getElementById('orderType').addEventListener('change', updateCustomerLabel);

    function updateCustomerLabel() {
        const type = document.getElementById('orderType').value;
        const label = type === 'inbound' ? 'Supplier' : 'Customer';
        document.getElementById('customerLabel').textContent = label;
        
        // Update the "Add New" option text dynamically
        const newOption = document.querySelector('#customerSelect option[value="new"]');
        if(newOption) newOption.textContent = `+ Add New ${label}`;
    }

    function toggleNewCustomerFields(val) {
        const fields = document.getElementById('newCustomerFields');
        if (val === 'new') {
            fields.classList.remove('d-none');
            fields.querySelector('input[name="new_customer_name"]').required = true;
        } else {
            fields.classList.add('d-none');
            fields.querySelector('input[name="new_customer_name"]').required = false;
        }
    }

    function addOrderItem() {
        const container = document.getElementById('orderItemsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'row g-2 item-row mb-2 align-items-end';
        
        let productOptions = '<option value="">Select Product</option><option value="new">+ Add New Product</option>';
        productsData.forEach(p => {
            productOptions += `<option value="${p.id}" data-price="${p.price}" data-gst="${p.gst_rate}">${p.sku} - ${p.name}</option>`;
        });

        newRow.innerHTML = `
            <div class="col-md-3">
                <label class="form-label small text-muted">Product *</label>
                <select name="items[${itemCount}][product_id]" class="form-select product-select" required onchange="handleProductChange(this, ${itemCount})">
                    ${productOptions}
                </select>
                <div class="new-product-fields d-none mt-2 p-2 bg-light rounded border">
                    <input type="text" name="items[${itemCount}][new_product_name]" class="form-control form-control-sm mb-2" placeholder="New Product Name">
                    <div class="row g-2">
                        <div class="col-6"><input type="text" name="items[${itemCount}][new_product_sku]" class="form-control form-control-sm" placeholder="SKU"></div>
                        <div class="col-6"><input type="number" name="items[${itemCount}][gst_rate]" class="form-control form-control-sm" placeholder="GST %" value="18"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Qty *</label>
                <input type="number" name="items[${itemCount}][qty]" class="form-control qty-input" placeholder="1" value="1" min="1" required oninput="calculateRowTotal(this)">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Unit Price *</label>
                <input type="number" name="items[${itemCount}][unit_price]" class="form-control unit-price" placeholder="0.00" step="0.01" required oninput="calculateRowTotal(this)">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">GST % *</label>
                <input type="number" name="items[${itemCount}][gst_rate]" class="form-control gst-rate" placeholder="18" step="0.01" value="18" required oninput="calculateRowTotal(this)">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Total</label>
                <input type="text" class="form-control row-total bg-light fw-bold" placeholder="0.00" readonly>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger btn-sm w-100 mb-2" onclick="removeOrderItem(this)"><i class="bi bi-trash"></i></button>
            </div>
        `;
        container.appendChild(newRow);
        itemCount++;
    }

    function handleProductChange(select, index) {
        const row = select.closest('.item-row');
        const newProductFields = row.querySelector('.new-product-fields');
        const priceInput = row.querySelector('.unit-price');
        const gstInput = row.querySelector('.gst-rate');

        if (select.value === 'new') {
            newProductFields.classList.remove('d-none');
            priceInput.readOnly = false;
            newProductFields.querySelector('input[name*="new_product_name"]').required = true;
            newProductFields.querySelector('input[name*="new_product_sku"]').required = true;
        } else {
            newProductFields.classList.add('d-none');
            newProductFields.querySelector('input[name*="new_product_name"]').required = false;
            newProductFields.querySelector('input[name*="new_product_sku"]').required = false;
            
            if (select.value) {
                const option = select.options[select.selectedIndex];
                priceInput.value = option.dataset.price || 0;
                gstInput.value = option.dataset.gst || 18;
            }
        }
        calculateRowTotal(row.querySelector('.qty-input'));
    }

    function calculateRowTotal(inputElement) {
        const row = inputElement.closest('.item-row');
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.unit-price').value) || 0;
        const gst = parseFloat(row.querySelector('.gst-rate').value) || 0;
        
        const subtotal = qty * price;
        const tax = subtotal * (gst / 100);
        const total = subtotal + tax;
        
        row.querySelector('.row-total').value = total.toFixed(2);
        calculateGrandTotal();
    }

    function removeOrderItem(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length > 1) {
            btn.closest('.item-row').remove();
            calculateGrandTotal();
        } else {
            alert('An order must have at least one item.');
        }
    }

    function calculateGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
            const price = parseFloat(row.querySelector('.unit-price')?.value) || 0;
            const gst = parseFloat(row.querySelector('.gst-rate')?.value) || 0;
            const subtotal = qty * price;
            grandTotal += subtotal + (subtotal * (gst / 100));
        });
        document.getElementById('grandTotalDisplay').innerText = '₹' + grandTotal.toFixed(2);
    }
</script>
@endpush