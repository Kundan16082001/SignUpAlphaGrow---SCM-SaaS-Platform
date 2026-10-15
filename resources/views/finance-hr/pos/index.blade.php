@extends('layouts.master')
@section('title', 'POS')
@section('page-title', 'Point of Sale')

@section('content')
    <style>
        .hover-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
            border: 1px solid #6366f1 !important;
        }
    </style>

    {{-- ==================== TOP STATS BAR ==================== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Today's Sales</small>
                            <h4 class="fw-bold mb-0">₹{{ number_format($stats['today_sales'], 0) }}</h4>
                        </div>
                        <i class="bi bi-currency-rupee fs-2 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Transactions</small>
                            <h4 class="fw-bold mb-0">{{ $stats['today_transactions'] }}</h4>
                        </div>
                        <i class="bi bi-receipt fs-2 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Items Sold</small>
                            <h4 class="fw-bold mb-0">{{ $stats['today_items'] }}</h4>
                        </div>
                        <i class="bi bi-box-seam fs-2 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Avg Transaction</small>
                            <h4 class="fw-bold mb-0">₹{{ number_format($stats['avg_transaction'], 0) }}</h4>
                        </div>
                        <i class="bi bi-graph-up fs-2 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- ==================== LEFT: PRODUCT SEARCH & CART ==================== --}}
        <div class="col-lg-8">
            {{-- Barcode Scanner Input --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-3">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-success text-white">
                            <i class="bi bi-upc-scan fs-4"></i>
                        </span>
                        <input type="text" id="barcodeInput" class="form-control fw-bold" 
                            placeholder="🔍 Scan barcode or type SKU/product name... (Press Enter)"
                            autocomplete="off" autofocus>
                        <button class="btn btn-outline-secondary" type="button" id="clearScanner">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>Tip:</strong> Just scan the barcode — product will auto-add to cart! Or type manually and press Enter.
                    </small>
                </div>
            </div>

            {{-- Shopping Cart --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-cart3 me-2"></i>Shopping Cart
                        <span class="badge bg-primary ms-2" id="cartCount">0 items</span>
                    </h6>
                    <button class="btn btn-sm btn-outline-danger" id="clearCart">
                        <i class="bi bi-trash me-1"></i> Clear All
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-4">Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">GST</th>
                                    <th class="text-end">Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="cartItems">
                                <tr id="emptyCartRow">
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-cart fs-1 d-block mb-2"></i>
                                        Cart is empty. Scan or search for products.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ==================== PRODUCT CATALOG GRID ==================== --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-grid-3x3-gap me-2"></i>Product Catalog
                        <span class="badge bg-secondary ms-2">{{ $products->count() }} Items</span>
                    </h6>
                    <input type="text" id="productSearch" class="form-control form-control-sm" style="width: 250px;" placeholder="🔍 Filter products...">
                </div>
                <div class="card-body p-3">
                    <div class="row g-3" id="productGrid" style="max-height: 400px; overflow-y: auto;">
                        @forelse($products as $prod)
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 product-item" data-name="{{ strtolower($prod->name) }}" data-sku="{{ strtolower($prod->sku) }}">
                                <div class="card h-100 border-0 shadow-sm hover-card" style="cursor: pointer; transition: all 0.2s;" onclick="addToCartById({{ $prod->id }}, '{{ $prod->sku }}', '{{ addslashes($prod->name) }}', {{ $prod->price }}, {{ $prod->gst_rate }}, {{ $prod->stock_qty }}, '{{ $prod->unit }}')">
                                    <div class="card-body p-3 text-center">
                                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px;">
                                            <i class="bi bi-box-seam text-primary fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold small mb-1 text-truncate" title="{{ $prod->name }}">{{ $prod->name }}</h6>
                                        <small class="text-muted d-block mb-2">{{ $prod->sku }}</small>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-success">₹{{ number_format($prod->price, 0) }}</span>
                                            <span class="badge bg-light text-dark border">Stock: {{ $prod->stock_qty }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No active products with stock found.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== RIGHT: PAYMENT & SUMMARY ==================== --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 90px;">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-calculator me-2"></i>Payment Summary</h6>
                </div>
                <div class="card-body p-4">
                    {{-- Totals --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-semibold" id="subtotalDisplay">₹0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">GST</span>
                            <span class="fw-semibold" id="taxDisplay">₹0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Discount</span>
                            <div class="input-group input-group-sm" style="width: 120px;">
                                <input type="number" id="discountInput" class="form-control text-end" value="0" min="0" step="0.01">
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5">Grand Total</span>
                            <span class="fw-bold fs-4 text-success" id="grandTotalDisplay">₹0.00</span>
                        </div>
                    </div>

                    {{-- Payment Method --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Method</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" name="payment_method" value="cash" id="pay_cash" class="btn-check" checked>
                                <label for="pay_cash" class="btn btn-outline-primary w-100 py-2">
                                    <i class="bi bi-cash-coin me-1"></i> Cash
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" name="payment_method" value="upi" id="pay_upi" class="btn-check">
                                <label for="pay_upi" class="btn btn-outline-primary w-100 py-2">
                                    <i class="bi bi-phone me-1"></i> UPI
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" name="payment_method" value="card" id="pay_card" class="btn-check">
                                <label for="pay_card" class="btn btn-outline-primary w-100 py-2">
                                    <i class="bi bi-credit-card me-1"></i> Card
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" name="payment_method" value="net_banking" id="pay_net" class="btn-check">
                                <label for="pay_net" class="btn btn-outline-primary w-100 py-2">
                                    <i class="bi bi-bank me-1"></i> Net Banking
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Amount Received (for cash) --}}
                    <div class="mb-3" id="cashSection">
                        <label class="form-label fw-semibold">Amount Received</label>
                        <input type="number" id="amountReceived" class="form-control form-control-lg text-end fw-bold" step="0.01" min="0">
                        <div class="d-flex justify-content-between mt-2">
                            <span class="text-muted">Change</span>
                            <span class="fw-bold text-success fs-5" id="changeDisplay">₹0.00</span>
                        </div>
                    </div>

                    {{-- Checkout Button --}}
                    <button class="btn btn-success btn-lg w-100 py-3 fw-bold" id="checkoutBtn" disabled>
                        <i class="bi bi-check-circle me-2"></i>COMPLETE SALE (F12)
                    </button>

                    <div class="mt-3 text-center">
                        <a href="{{ route('finance-hr.pos.today-sales') }}" class="text-muted small">
                            <i class="bi bi-clock-history me-1"></i> View Today's Sales
                        </a>
                    </div>
                </div>
            </div>

            {{-- Low Stock Alerts --}}
            @if($lowStockProducts->count() > 0)
                <div class="card border-0 shadow-sm mt-3 border-start border-warning border-4">
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-2 text-warning">
                            <i class="bi bi-exclamation-triangle me-1"></i> Low Stock Alert
                        </h6>
                        <ul class="list-unstyled mb-0 small">
                            @foreach($lowStockProducts as $product)
                                <li class="mb-1">
                                    <span class="fw-semibold">{{ $product->sku }}</span>
                                    <span class="text-muted">- {{ $product->name }}</span>
                                    <span class="badge bg-warning text-dark ms-1">{{ $product->stock_qty }} left</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ==================== SUCCESS MODAL ==================== --}}
    <div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Sale Completed!</h5>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <i class="bi bi-bag-check-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-1">Transaction Successful</h4>
                    <p class="text-muted mb-3">Receipt: <strong id="receiptNo"></strong></p>
                    <div class="bg-light rounded p-3 mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Total Amount:</span>
                            <strong id="receiptTotal"></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Payment Method:</span>
                            <strong id="receiptPayment"></strong>
                        </div>
                        <div class="d-flex justify-content-between" id="changeRow" style="display: none;">
                            <span>Change:</span>
                            <strong class="text-success" id="receiptChange"></strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    {{-- ✅ PDF Download Button --}}
                    <a href="#" id="downloadPdfBtn" target="_blank" class="btn btn-outline-dark">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
                    </a>
                    <button type="button" class="btn btn-outline-primary" id="printReceiptBtn">
                        <i class="bi bi-printer me-1"></i> Print
                    </button>
                    <button type="button" class="btn btn-success" id="newSaleBtn">
                        <i class="bi bi-plus-circle me-1"></i> New Sale
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // ==================== CART STATE ====================
    let cart = [];
    let lastReceiptNo = null;

    // ==================== BARCODE SCANNER DETECTION ====================
    const barcodeInput = document.getElementById('barcodeInput');
    let scanBuffer = '';
    let lastKeyTime = 0;
    const SCAN_THRESHOLD = 50;

    document.addEventListener('keydown', function(e) {
        if (e.target.tagName === 'INPUT' && e.target.type !== 'text') return;
        if (e.target.tagName === 'TEXTAREA') return;
        if (e.target.tagName === 'SELECT') return;
        
        if (e.key === 'F12') {
            e.preventDefault();
            document.getElementById('checkoutBtn').click();
            return;
        }

        if (!barcodeInput.matches(':focus') && e.key.length === 1) {
            barcodeInput.focus();
        }
    });

    barcodeInput.addEventListener('keydown', function(e) {
        const currentTime = new Date().getTime();
        if (e.key === 'Enter') {
            e.preventDefault();
            const query = this.value.trim();
            if (query) {
                searchProduct(query);
                this.value = '';
            }
            scanBuffer = '';
            return;
        }

        if (e.key.length === 1) {
            const timeDiff = currentTime - lastKeyTime;
            if (timeDiff < SCAN_THRESHOLD || scanBuffer === '') {
                scanBuffer += e.key;
            } else {
                scanBuffer = e.key;
            }
            lastKeyTime = currentTime;
        }
    });

    document.getElementById('clearScanner').addEventListener('click', function() {
        barcodeInput.value = '';
        barcodeInput.focus();
    });

    // ==================== PRODUCT SEARCH ====================
    function searchProduct(query) {
        const url = `{{ route('finance-hr.pos.search') }}?query=${encodeURIComponent(query)}`;
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    addToCart(data.product);
                } else {
                    alert('❌ ' + (data.message || 'Product not found'));
                }
            })
            .catch(err => {
                console.error('❌ Search error:', err);
                alert('❌ Error searching product. Check console.');
            });
    }

    // ==================== CART MANAGEMENT ====================
    function addToCart(product) {
        console.log('Adding via scanner/search:', product);
        const productId = Number(product.id);
        const existing = cart.find(item => item.id === productId);
        
        if (existing) {
            if (existing.qty >= product.stock_qty) {
                alert(`⚠️ Only ${product.stock_qty} items available in stock!`);
                return;
            }
            existing.qty++;
        } else {
            if (product.stock_qty <= 0) {
                alert('❌ Product out of stock!');
                return;
            }
            cart.push({
                id: productId,
                sku: product.sku,
                name: product.name,
                price: Number(product.price),
                gst_rate: Number(product.gst_rate),
                stock_qty: Number(product.stock_qty),
                qty: 1,
                unit: product.unit,
            });
        }
        renderCart();
        barcodeInput.focus();
    }

    function addToCartById(id, sku, name, price, gstRate, stockQty, unit) {
        console.log('Adding via grid click:', id, name);
        const productId = Number(id);
        const existing = cart.find(item => item.id === productId);
        
        if (existing) {
            if (existing.qty >= stockQty) {
                alert(`⚠️ Only ${stockQty} items available in stock!`);
                return;
            }
            existing.qty++;
        } else {
            if (stockQty <= 0) {
                alert('❌ Product out of stock!');
                return;
            }
            cart.push({ 
                id: productId, 
                sku: sku, 
                name: name, 
                price: Number(price), 
                gst_rate: Number(gstRate), 
                stock_qty: Number(stockQty), 
                qty: 1, 
                unit: unit 
            });
        }
        renderCart();
        document.querySelector('.table-responsive').scrollIntoView({ behavior: 'smooth' });
    }

    function updateQty(productId, newQty) {
        const item = cart.find(i => i.id === Number(productId));
        if (!item) return;
        if (newQty <= 0) { removeFromCart(Number(productId)); return; }
        if (newQty > item.stock_qty) {
            alert(`⚠️ Only ${item.stock_qty} items available!`);
            newQty = item.stock_qty;
        }
        item.qty = newQty;
        renderCart();
    }

    function removeFromCart(productId) {
        cart = cart.filter(item => item.id !== Number(productId));
        renderCart();
    }

    function renderCart() {
        const tbody = document.getElementById('cartItems');
        
        if (cart.length === 0) {
            tbody.innerHTML = `
                <tr id="emptyCartRow">
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-cart fs-1 d-block mb-2"></i>
                        Cart is empty. Scan, search, or click products.
                    </td>
                </tr>
            `;
            document.getElementById('cartCount').textContent = '0 items';
            updateTotals();
            return;
        }
        
        let html = '';
        let totalItems = 0;
        
        cart.forEach(item => {
            const lineSubtotal = item.qty * item.price;
            const lineTax = lineSubtotal * (item.gst_rate / 100);
            const lineTotal = lineSubtotal + lineTax;
            totalItems += item.qty;
            
            html += `
                <tr>
                    <td class="ps-4">
                        <div class="fw-semibold">${item.name}</div>
                        <small class="text-muted">${item.sku} · ${item.unit}</small>
                    </td>
                    <td class="text-center">
                        <div class="input-group input-group-sm" style="width: 110px;">
                            <button class="btn btn-outline-secondary" onclick="updateQty(${item.id}, ${item.qty - 1})">−</button>
                            <input type="number" class="form-control text-center" value="${item.qty}" min="1" max="${item.stock_qty}" onchange="updateQty(${item.id}, parseInt(this.value))">
                            <button class="btn btn-outline-secondary" onclick="updateQty(${item.id}, ${item.qty + 1})">+</button>
                        </div>
                    </td>
                    <td class="text-end">₹${item.price.toFixed(2)}</td>
                    <td class="text-end text-muted">₹${lineTax.toFixed(2)}</td>
                    <td class="text-end fw-bold">₹${lineTotal.toFixed(2)}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${item.id})"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
        document.getElementById('cartCount').textContent = `${totalItems} item${totalItems > 1 ? 's' : ''}`;
        updateTotals();
    }

    // ==================== TOTALS CALCULATION ====================
    function updateTotals() {
        let subtotal = 0, tax = 0;
        cart.forEach(item => {
            subtotal += item.qty * item.price;
            tax += (item.qty * item.price) * (item.gst_rate / 100);
        });
        
        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const grandTotal = subtotal + tax - discount;
        
        document.getElementById('subtotalDisplay').textContent = '₹' + subtotal.toFixed(2);
        document.getElementById('taxDisplay').textContent = '₹' + tax.toFixed(2);
        document.getElementById('grandTotalDisplay').textContent = '₹' + grandTotal.toFixed(2);
        
        updateChange();
        updateCheckoutButton();
    }

    function updateChange() {
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        const cashSection = document.getElementById('cashSection');
        
        if (paymentMethod === 'cash') {
            cashSection.style.display = '';
            const received = parseFloat(document.getElementById('amountReceived').value) || 0;
            const grandTotal = parseFloat(document.getElementById('grandTotalDisplay').textContent.replace('₹', '')) || 0;
            const change = Math.max(0, received - grandTotal);
            document.getElementById('changeDisplay').textContent = '₹' + change.toFixed(2);
        } else {
            cashSection.style.display = 'none';
        }
    }

    function updateCheckoutButton() {
        const btn = document.getElementById('checkoutBtn');
        const grandTotal = parseFloat(document.getElementById('grandTotalDisplay').textContent.replace('₹', '')) || 0;
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        const received = parseFloat(document.getElementById('amountReceived').value) || 0;
        
        let canCheckout = cart.length > 0 && grandTotal > 0;
        if (paymentMethod === 'cash') {
            canCheckout = canCheckout && received >= grandTotal;
        }
        btn.disabled = !canCheckout;
    }

    // ==================== EVENT LISTENERS ====================
    document.getElementById('discountInput').addEventListener('input', updateTotals);
    
    document.getElementById('amountReceived').addEventListener('input', function() {
        updateChange();
        updateCheckoutButton(); 
    });
    
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', () => {
            updateChange();
            updateCheckoutButton();
        });
    });

    document.getElementById('clearCart').addEventListener('click', function() {
        if (cart.length === 0) return;
        if (confirm('Clear all items from cart?')) {
            cart = [];
            renderCart();
        }
    });

    document.getElementById('productSearch').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll('.product-item').forEach(item => {
            const name = item.dataset.name;
            const sku = item.dataset.sku;
            item.style.display = (name.includes(query) || sku.includes(query)) ? '' : 'none';
        });
    });

    // ==================== CHECKOUT ====================
    document.getElementById('checkoutBtn').addEventListener('click', async function() {
        if (cart.length === 0) { alert('❌ Cart is empty!'); return; }
        
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        const grandTotal = parseFloat(document.getElementById('grandTotalDisplay').textContent.replace('₹', ''));
        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const amountReceived = paymentMethod === 'cash' ? (parseFloat(document.getElementById('amountReceived').value) || grandTotal) : grandTotal;
        
        const payload = {
            items: cart.map(item => ({ 
                product_id: item.id, 
                qty: item.qty, 
                price: item.price, 
                gst_rate: item.gst_rate 
            })),
            payment_method: paymentMethod,
            discount: discount,
            amount_received: amountReceived,
        };
        
        console.log('📤 Sending Payload:', payload);
        
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        
        try {
            const response = await fetch('{{ route('finance-hr.pos.transaction') }}', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                    'Accept': 'application/json' 
                },
                body: JSON.stringify(payload)
            });
            
            const data = await response.json();
            console.log('📥 Server Response:', data);
            
            if (!response.ok) {
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            }
            
            if (data.success) {
                lastReceiptNo = data.receipt_no;
                
                // ✅ CRITICAL FIX: Dynamically set the PDF download URL based on the generated receipt number
                document.getElementById('downloadPdfBtn').href = `/finance-hr/pos/receipt/${data.receipt_no}/pdf`;

                document.getElementById('receiptNo').textContent = data.receipt_no;
                document.getElementById('receiptTotal').textContent = '₹' + data.total.toFixed(2);
                document.getElementById('receiptPayment').textContent = data.payment_method.toUpperCase();
                
                if (paymentMethod === 'cash' && data.change > 0) {
                    document.getElementById('changeRow').style.display = 'flex';
                    document.getElementById('receiptChange').textContent = '₹' + data.change.toFixed(2);
                } else {
                    document.getElementById('changeRow').style.display = 'none';
                }
                
                new bootstrap.Modal(document.getElementById('successModal')).show();
                cart = [];
                renderCart();
                document.getElementById('discountInput').value = 0;
                document.getElementById('amountReceived').value = '';
            } else {
                alert('❌ Transaction Failed:\n\n' + (data.message || 'Unknown error'));
            }
        } catch (err) {
            console.error('❌ Checkout error:', err);
            alert('❌ Error processing transaction:\n\n' + err.message + '\n\nPress F12 and check the Console tab for exact details.');
        } finally {
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-check-circle me-2"></i>COMPLETE SALE (F12)';
            updateCheckoutButton();
        }
    });

    document.getElementById('printReceiptBtn').addEventListener('click', function() {
        if (lastReceiptNo) window.open(`/finance-hr/pos/receipt/${lastReceiptNo}`, '_blank');
        else alert('❌ No receipt available to print!');
    });

    document.getElementById('newSaleBtn').addEventListener('click', function() {
        bootstrap.Modal.getInstance(document.getElementById('successModal')).hide();
        barcodeInput.focus();
    });

    // Initial focus
    barcodeInput.focus();
</script>
@endpush