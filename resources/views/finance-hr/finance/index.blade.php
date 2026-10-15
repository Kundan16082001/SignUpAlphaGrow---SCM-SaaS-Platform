@extends('layouts.master')
@section('title', 'Finance')
@section('page-title', 'Finance Dashboard')

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
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="small mb-1 opacity-75">Total Revenue (Inv + POS)</p><h3 class="fw-bold mb-0">₹{{ number_format($stats['total_revenue'], 0) }}</h3></div>
                    <i class="bi bi-currency-rupee fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="small mb-1 opacity-75">Total Expenses</p><h3 class="fw-bold mb-0">₹{{ number_format($stats['total_expenses'], 0) }}</h3></div>
                    <i class="bi bi-receipt fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="small mb-1 opacity-75">Pending Invoices</p><h3 class="fw-bold mb-0">{{ $stats['pending_invoices'] }}</h3></div>
                    <i class="bi bi-hourglass-split fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="small mb-1 opacity-75">Pending Amount</p><h3 class="fw-bold mb-0">₹{{ number_format($stats['pending_amount'], 0) }}</h3></div>
                    <i class="bi bi-wallet2 fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <ul class="nav nav-pills mb-4" id="financeTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'invoices' ? 'active' : '' }}" href="{{ route('finance-hr.finance.index', ['tab' => 'invoices']) }}">
                <i class="bi bi-file-earmark-text me-1"></i> Invoices
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'pos' ? 'active' : '' }}" href="{{ route('finance-hr.finance.index', ['tab' => 'pos']) }}">
                <i class="bi bi-upc-scan me-1"></i> POS History
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'expenses' ? 'active' : '' }}" href="{{ route('finance-hr.finance.index', ['tab' => 'expenses']) }}">
                <i class="bi bi-wallet me-1"></i> Expenses
            </a>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content">
        
        {{-- ==================== INVOICES TAB ==================== --}}
        @if($tab == 'invoices')
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-file-earmark-text me-2"></i>Invoice History</h6>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInvoiceModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Invoice
                </button>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Invoice #</th>
                                    <th>Customer</th>
                                    <th>Type</th>
                                    <th class="text-end">Total</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $inv)
                                    <tr class="{{ $inv->is_overdue ? 'table-danger' : '' }}">
                                        <td class="ps-4 fw-bold text-primary">{{ $inv->invoice_no }}</td>
                                        <td>{{ $inv->customer->name ?? 'N/A' }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ strtoupper($inv->type) }}</span></td>
                                        <td class="text-end fw-bold">₹{{ number_format($inv->total, 2) }}</td>
                                        <td class="text-muted small">{{ $inv->due_date?->format('d M Y') ?? 'N/A' }}</td>
                                        <td><span class="badge bg-{{ $inv->status_color }}">{{ ucfirst($inv->status) }}</span></td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editInvoiceModal"
                                                data-id="{{ $inv->id }}" data-type="{{ $inv->type }}" data-due="{{ $inv->due_date?->format('Y-m-d') }}" data-status="{{ $inv->status }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="{{ route('finance-hr.finance.invoice.destroy', $inv) }}" class="d-inline" onsubmit="return confirm('Delete this invoice?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center py-5 text-muted">No invoices found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($invoices->hasPages())<div class="card-footer bg-white border-top-0 pt-3">{{ $invoices->links() }}</div>@endif
            </div>
        @endif

        {{-- ==================== POS TAB ==================== --}}
        @if($tab == 'pos')
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Receipt #</th>
                                    <th>Date & Time</th>
                                    <th>Cashier</th>
                                    <th class="text-center">Items</th>
                                    <th class="text-end">Total</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($posTransactions as $pos)
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">{{ $pos->receipt_no }}</td>
                                        <td class="text-muted small">{{ $pos->created_at->format('d M Y, h:i A') }}</td>
                                        <td>{{ $pos->user->name ?? 'N/A' }}</td>
                                        <td class="text-center"><span class="badge bg-light text-dark border">{{ $pos->item_count }}</span></td>
                                        <td class="text-end fw-bold">₹{{ number_format($pos->total, 2) }}</td>
                                        <td><span class="badge bg-info bg-opacity-10 text-info"><i class="bi {{ $pos->payment_method_icon }} me-1"></i>{{ ucfirst($pos->payment_method) }}</span></td>
                                        <td><span class="badge bg-{{ $pos->status_color }}">{{ ucfirst($pos->status) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center py-5 text-muted">No POS transactions found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($posTransactions->hasPages())<div class="card-footer bg-white border-top-0 pt-3">{{ $posTransactions->links() }}</div>@endif
            </div>
        @endif

        {{-- ==================== EXPENSES TAB ==================== --}}
        @if($tab == 'expenses')
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Expense #</th>
                                    <th>Category</th>
                                    <th>Vendor</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Total (incl. Tax)</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenses as $exp)
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">{{ $exp->expense_no }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $exp->category->name ?? 'Uncategorized' }}</span></td>
                                        <td>{{ $exp->vendor ?? 'N/A' }}</td>
                                        <td class="text-end">₹{{ number_format($exp->amount, 2) }}</td>
                                        <td class="text-end fw-bold">₹{{ number_format($exp->total, 2) }}</td>
                                        <td><span class="badge bg-secondary bg-opacity-10 text-secondary">{{ ucfirst($exp->payment_mode) }}</span></td>
                                        <td><span class="badge bg-{{ $exp->status_color }}">{{ ucfirst($exp->status) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center py-5 text-muted">No expenses found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($expenses->hasPages())<div class="card-footer bg-white border-top-0 pt-3">{{ $expenses->links() }}</div>@endif
            </div>
        @endif

    </div>

    {{-- ==================== ADD INVOICE MODAL ==================== --}}
    <div class="modal fade" id="addInvoiceModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="{{ route('finance-hr.finance.invoice.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Create New Invoice</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Customer *</label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $cust)
                                    <option value="{{ $cust->id }}">{{ $cust->name }} ({{ $cust->gstin }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Invoice Type *</label>
                            <select name="type" class="form-select" required>
                                <option value="b2b">B2B (CGST + SGST)</option>
                                <option value="b2c">B2C (CGST + SGST)</option>
                                <option value="export">Export (IGST)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Invoice Date *</label>
                            <input type="date" name="invoice_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Due Date *</label>
                            <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                        </div>
                    </div>

                    <h6 class="fw-bold border-bottom pb-2 mb-3">Invoice Items</h6>
                    <!-- ... rest of the modal remains exactly the same ... -->
                    <div id="invoiceItemsContainer">
                        <div class="row g-2 item-row mb-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Product / Description *</label>
                                <select name="items[0][product_id]" class="form-select product-select" onchange="updateItemDetails(this, 0)">
                                    <option value="">Custom Item</option>
                                    @foreach($products as $prod)
                                        <option value="{{ $prod->id }}" data-rate="{{ $prod->price }}" data-gst="{{ $prod->gst_rate }}" data-name="{{ $prod->name }}">{{ $prod->sku }} - {{ $prod->name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="items[0][description]" class="desc-input" value="">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Qty *</label>
                                <input type="number" name="items[0][qty]" class="form-control qty-input" value="1" min="1" required oninput="calculateInvoiceTotal()">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Rate (₹) *</label>
                                <input type="number" name="items[0][rate]" class="form-control rate-input" step="0.01" min="0" required oninput="calculateInvoiceTotal()">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">GST % *</label>
                                <input type="number" name="items[0][gst_rate]" class="form-control gst-input" step="0.01" min="0" value="18" required oninput="calculateInvoiceTotal()">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 mb-1" onclick="this.closest('.item-row').remove(); calculateInvoiceTotal();"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addInvoiceItem()"><i class="bi bi-plus-circle me-1"></i> Add Item</button>

                    <div class="mt-4 p-3 bg-light rounded d-flex justify-content-end">
                        <div class="text-end">
                            <small class="text-muted d-block">Total Taxable: <strong id="invSubtotal">₹0.00</strong></small>
                            <small class="text-muted d-block">Total GST: <strong id="invTax">₹0.00</strong></small>
                            <h4 class="fw-bold text-primary mb-0 mt-2">Grand Total: <span id="invGrandTotal">₹0.00</span></h4>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Invoice</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== EDIT INVOICE MODAL ==================== --}}
    <div class="modal fade" id="editInvoiceModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" id="editInvoiceForm" class="modal-content">
                @csrf @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Invoice Type</label>
                        <select name="type" id="edit_type" class="form-select" required>
                            <option value="b2b">B2B</option>
                            <option value="b2c">B2C</option>
                            <option value="export">Export</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Due Date</label>
                        <input type="date" name="due_date" id="edit_due" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" id="edit_status" class="form-select" required>
                            <option value="draft">Draft</option>
                            <option value="sent">Sent</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Invoice</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let itemIndex = 1;

    function addInvoiceItem() {
        const container = document.getElementById('invoiceItemsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'row g-2 item-row mb-2 align-items-end';
        newRow.innerHTML = `
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Product / Description *</label>
                <select name="items[${itemIndex}][product_id]" class="form-select product-select" onchange="updateItemDetails(this, ${itemIndex})">
                    <option value="">Custom Item</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}" data-rate="{{ $prod->price }}" data-gst="{{ $prod->gst_rate }}" data-name="{{ $prod->name }}">{{ $prod->sku }} - {{ $prod->name }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="items[${itemIndex}][description]" class="desc-input" value="">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Qty *</label>
                <input type="number" name="items[${itemIndex}][qty]" class="form-control qty-input" value="1" min="1" required oninput="calculateInvoiceTotal()">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Rate (₹) *</label>
                <input type="number" name="items[${itemIndex}][rate]" class="form-control rate-input" step="0.01" min="0" required oninput="calculateInvoiceTotal()">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">GST % *</label>
                <input type="number" name="items[${itemIndex}][gst_rate]" class="form-control gst-input" step="0.01" min="0" value="18" required oninput="calculateInvoiceTotal()">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger btn-sm w-100 mb-1" onclick="this.closest('.item-row').remove(); calculateInvoiceTotal();"><i class="bi bi-trash"></i></button>
            </div>
        `;
        container.appendChild(newRow);
        itemIndex++;
    }

    function updateItemDetails(selectElem, index) {
        const option = selectElem.options[selectElem.selectedIndex];
        const row = selectElem.closest('.item-row');
        
        if (option.value) {
            row.querySelector('.rate-input').value = option.dataset.rate;
            row.querySelector('.gst-input').value = option.dataset.gst;
            row.querySelector('.desc-input').value = option.dataset.name;
        } else {
            row.querySelector('.desc-input').value = "Custom Item";
        }
        calculateInvoiceTotal();
    }

    function calculateInvoiceTotal() {
        let subtotal = 0;
        let totalTax = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const rate = parseFloat(row.querySelector('.rate-input').value) || 0;
            const gst = parseFloat(row.querySelector('.gst-input').value) || 0;

            const lineSubtotal = qty * rate;
            const lineTax = lineSubtotal * (gst / 100);

            subtotal += lineSubtotal;
            totalTax += lineTax;
        });

        document.getElementById('invSubtotal').textContent = '₹' + subtotal.toFixed(2);
        document.getElementById('invTax').textContent = '₹' + totalTax.toFixed(2);
        document.getElementById('invGrandTotal').textContent = '₹' + (subtotal + totalTax).toFixed(2);
    }

    // Edit Modal Population
    document.querySelectorAll('[data-bs-target="#editInvoiceModal"]').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            document.getElementById('editInvoiceForm').action = `/finance-hr/finance/invoice/${id}`;
            document.getElementById('edit_type').value = this.dataset.type;
            document.getElementById('edit_due').value = this.dataset.due;
            document.getElementById('edit_status').value = this.dataset.status;
        });
    });

    // Initial calculation
    calculateInvoiceTotal();
</script>
@endpush