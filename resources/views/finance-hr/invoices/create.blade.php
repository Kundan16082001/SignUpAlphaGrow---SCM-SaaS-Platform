@extends('layouts.master')
@section('title', 'Create Invoice')
@section('page-title', 'Create New Invoice')

@section('content')
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('finance-hr.invoices.store') }}" id="invoiceForm" novalidate>
        @csrf

        <div class="row g-4">
            {{-- LEFT COLUMN: Invoice Details & Items --}}
            <div class="col-lg-8">
                {{-- Customer Details --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0"><i class="bi bi-person-badge me-2 text-primary"></i>Customer Details</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Customer *</label>
                                <select name="customer_id" class="form-select" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $cust)
                                        <option value="{{ $cust->id }}" {{ old('customer_id') == $cust->id ? 'selected' : '' }}>
                                            {{ $cust->name }} ({{ $cust->customer_code ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Invoice Type *</label>
                                <select name="type" class="form-select" required>
                                    <option value="b2b" {{ old('type') == 'b2b' ? 'selected' : '' }}>B2B</option>
                                    <option value="b2c" {{ old('type') == 'b2c' ? 'selected' : '' }}>B2C</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Due Date</label>
                                <input type="date" name="due_date" class="form-control" 
                                       value="{{ old('due_date', now()->addDays(30)->format('Y-m-d')) }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Invoice Items --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0"><i class="bi bi-box-seam me-2 text-primary"></i>Invoice Items</h6>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addItemRow()">
                            <i class="bi bi-plus-lg me-1"></i> Add Item
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4" style="width: 35%;">Product</th>
                                        <th style="width: 12%;">Qty</th>
                                        <th style="width: 15%;">Price (₹)</th>
                                        <th style="width: 12%;">GST %</th>
                                        <th class="text-end" style="width: 18%;">Total (₹)</th>
                                        <th style="width: 8%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsContainer">
                                    {{-- Item rows will be injected here via JS --}}
                                </tbody>
                            </table>
                        </div>
                        @error('items')
                            <div class="text-danger small px-4 py-2">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="card-footer bg-white border-top-0 py-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItemRow()">
                            <i class="bi bi-plus-circle me-1"></i> Add Another Item
                        </button>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: Summary & Actions --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 90px; z-index: 10;">
                    <div class="card-header bg-primary text-white py-3">
                        <h6 class="fw-bold mb-0"><i class="bi bi-calculator me-2"></i>Invoice Summary</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-semibold" id="subtotalDisplay">₹0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Tax (GST)</span>
                                <span class="fw-semibold" id="taxDisplay">₹0.00</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">Discount</span>
                                <div class="input-group input-group-sm" style="width: 130px;">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" name="discount" id="discountInput" class="form-control text-end" 
                                           value="{{ old('discount', 0) }}" min="0" step="0.01">
                                </div>
                            </div>
                            <hr class="my-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold fs-5">Grand Total</span>
                                <span class="fw-bold fs-4 text-success" id="grandTotalDisplay">₹0.00</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Payment terms, delivery notes, etc...">{{ old('notes') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100 py-3 fw-bold shadow-sm">
                            <i class="bi bi-check-circle me-2"></i>Create Invoice
                        </button>

                        <a href="{{ route('finance-hr.finance.index') }}" class="btn btn-outline-secondary w-100 mt-3">
    <i class="bi bi-x-lg me-1"></i> Cancel
</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    let itemIndex = 0;
    
    // Safely inject products data from controller without tripping JS/TS parsing in Blade files
    const products = JSON.parse('{!! json_encode($products, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}');

    function addItemRow() {
        const container = document.getElementById('itemsContainer');
        const row = document.createElement('tr');
        row.id = `item-row-${itemIndex}`;
        row.className = 'item-row';
        
        let productOptions = '<option value="">Select Product</option>';
        products.forEach(p => {
            const price = Number(p.price || 0);
            const gstRate = Number(p.gst_rate || 0);
            productOptions += `<option value="${p.id}" data-price="${price}" data-gst="${gstRate}">${p.name} (${p.sku}) - Stock: ${p.stock_qty}</option>`;
        });

        row.innerHTML = `
            <td class="ps-4">
                <select name="items[${itemIndex}][product_id]" class="form-select form-select-sm item-product" required onchange="onProductChange(${itemIndex}, this.value)">
                    ${productOptions}
                </select>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][qty]" class="form-control form-control-sm item-qty" min="1" value="1" required oninput="calculateRowTotal(${itemIndex})">
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][price]" class="form-control form-control-sm item-price" step="0.01" min="0" value="0" required oninput="calculateRowTotal(${itemIndex})">
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][gst_rate]" class="form-control form-control-sm item-gst" step="0.01" min="0" max="100" value="18" required oninput="calculateRowTotal(${itemIndex})">
            </td>
            <td class="text-end fw-bold item-total" id="row-total-${itemIndex}">₹0.00</td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItemRow(${itemIndex})" title="Remove Item">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        container.appendChild(row);
        itemIndex++;
        updateGrandTotal();
    }

    function removeItemRow(index) {
        const row = document.getElementById(`item-row-${index}`);
        if (row) {
            // Prevent removing the last row to ensure at least one item is present for validation
            const rows = document.querySelectorAll('.item-row');
            if (rows.length > 1) {
                row.remove();
                updateGrandTotal();
            } else {
                alert('An invoice must have at least one item.');
            }
        }
    }

    function onProductChange(index, productId) {
        const product = products.find(p => p.id == productId);
        if (!product) return;
        
        const row = document.getElementById(`item-row-${index}`);
        row.querySelector('.item-price').value = product.price;
        row.querySelector('.item-gst').value = product.gst_rate;
        calculateRowTotal(index);
    }

    function calculateRowTotal(index) {
        const row = document.getElementById(`item-row-${index}`);
        if (!row) return;

        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const gst = parseFloat(row.querySelector('.item-gst').value) || 0;
        
        const lineSubtotal = qty * price;
        const lineTax = lineSubtotal * (gst / 100);
        const lineTotal = lineSubtotal + lineTax;
        
        row.querySelector('.item-total').textContent = '₹' + lineTotal.toFixed(2);
        updateGrandTotal();
    }

    function updateGrandTotal() {
        let subtotal = 0;
        let totalTax = 0;
        
        // Robustly iterate through all rows using CSS classes
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
            const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
            const gst = parseFloat(row.querySelector('.item-gst')?.value) || 0;
            
            const lineSubtotal = qty * price;
            subtotal += lineSubtotal;
            totalTax += lineSubtotal * (gst / 100);
        });
        
        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        
        // Prevent negative grand total if discount exceeds subtotal + tax
        const grandTotal = Math.max(0, subtotal + totalTax - discount);
        
        document.getElementById('subtotalDisplay').textContent = '₹' + subtotal.toFixed(2);
        document.getElementById('taxDisplay').textContent = '₹' + totalTax.toFixed(2);
        document.getElementById('grandTotalDisplay').textContent = '₹' + grandTotal.toFixed(2);
    }

    // Listen for discount changes
    document.getElementById('discountInput').addEventListener('input', updateGrandTotal);

    // Add the first row automatically when the page loads
    document.addEventListener('DOMContentLoaded', () => {
        addItemRow();
    });
</script>
@endpush