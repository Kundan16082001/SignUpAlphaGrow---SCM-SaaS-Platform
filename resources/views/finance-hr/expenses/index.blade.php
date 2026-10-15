@extends('layouts.master')
@section('title', 'Expenses')
@section('page-title', 'Expense Management')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75">Total Expenses</p>
                        <h3 class="fw-bold mb-0">₹{{ number_format($stats['total_amount'], 0) }}</h3>
                        <small class="opacity-75">{{ $stats['total_expenses'] }} expenses</small>
                    </div>
                    <i class="bi bi-receipt fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75">Pending Approval</p>
                        <h3 class="fw-bold mb-0">₹{{ number_format($stats['pending_amount'], 0) }}</h3>
                        <small class="opacity-75">{{ $stats['pending_count'] }} awaiting</small>
                    </div>
                    <i class="bi bi-hourglass-split fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75">Total GST</p>
                        <h3 class="fw-bold mb-0">₹{{ number_format($stats['total_gst'], 0) }}</h3>
                        <small class="opacity-75">Input credit</small>
                    </div>
                    <i class="bi bi-file-earmark-text fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75">Total TDS</p>
                        <h3 class="fw-bold mb-0">₹{{ number_format($stats['total_tds'], 0) }}</h3>
                        <small class="opacity-75">Deducted</small>
                    </div>
                    <i class="bi bi-calculator fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Category-wise Summary --}}
    @if($categorySummary->count() > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart me-2"></i>Category-wise Breakdown</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($categorySummary as $cat)
                        <div class="col-md-3 col-sm-6">
                            <div class="d-flex align-items-center gap-3 p-3 rounded-3 bg-light">
                                <div class="bg-{{ $cat['color'] }} bg-opacity-10 text-{{ $cat['color'] }} p-2 rounded-circle">
                                    <i class="bi {{ $cat['icon'] }} fs-4"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">{{ $cat['name'] }}</small>
                                    <h5 class="fw-bold mb-0">₹{{ number_format($cat['total'], 0) }}</h5>
                                    <small class="text-muted">{{ $cat['count'] }} expenses</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Status Tabs & Actions --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link {{ $status == 'all' ? 'active' : '' }}" href="{{ route('finance-hr.expenses.index', ['status' => 'all']) }}">
                    All <span class="badge bg-light text-dark ms-1">{{ $stats['total_expenses'] }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status == 'pending' ? 'active' : '' }}" href="{{ route('finance-hr.expenses.index', ['status' => 'pending']) }}">
                    Pending <span class="badge bg-warning text-dark ms-1">{{ $stats['pending_count'] }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status == 'approved' ? 'active' : '' }}" href="{{ route('finance-hr.expenses.index', ['status' => 'approved']) }}">Approved</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status == 'paid' ? 'active' : '' }}" href="{{ route('finance-hr.expenses.index', ['status' => 'paid']) }}">Paid</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status == 'rejected' ? 'active' : '' }}" href="{{ route('finance-hr.expenses.index', ['status' => 'rejected']) }}">Rejected</a>
            </li>
        </ul>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="bi bi-tags me-1"></i> Add Category
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="bi bi-plus-lg me-1"></i> Add Expense
            </button>
        </div>
    </div>

    {{-- Expenses Table --}}
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
                            <th class="text-end">GST</th>
                            <th class="text-end">TDS</th>
                            <th class="text-end">Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td class="ps-4 fw-bold text-primary">{{ $expense->expense_no }}</td>
                                <td>
                                    @if($expense->category)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-{{ $expense->category->color }} bg-opacity-10 text-{{ $expense->category->color }} p-2 rounded-circle" style="width:32px; height:32px; display:flex; align-items:center; justify-content:center;">
                                                <i class="bi {{ $expense->category->icon }}"></i>
                                            </div>
                                            <span class="fw-semibold small">{{ $expense->category->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted small">Uncategorized</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold small">{{ $expense->vendor ?? 'N/A' }}</div>
                                    @if($expense->description)
                                        <small class="text-muted">{{ Str::limit($expense->description, 30) }}</small>
                                    @endif
                                </td>
                                <td class="text-end">₹{{ number_format($expense->amount, 2) }}</td>
                                <td class="text-end text-muted">₹{{ number_format($expense->gst, 2) }}</td>
                                <td class="text-end text-muted">₹{{ number_format($expense->tds, 2) }}</td>
                                <td class="text-end fw-bold">₹{{ number_format($expense->total, 2) }}</td>
                                <td>
                                    @php
                                        $paymentIcons = [
                                            'cash' => 'bi-cash-coin',
                                            'bank' => 'bi-bank',
                                            'upi' => 'bi-phone',
                                            'cheque' => 'bi-credit-card',
                                            'card' => 'bi-credit-card-2-front',
                                        ];
                                    @endphp
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi {{ $paymentIcons[$expense->payment_mode] ?? 'bi-wallet2' }} me-1"></i>
                                        {{ ucfirst($expense->payment_mode) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $expense->status_color }} bg-opacity-10 text-{{ $expense->status_color }}">
                                        {{ ucfirst($expense->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <form method="POST" action="{{ route('finance-hr.expenses.update-status', $expense) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <select name="status" class="form-select form-select-sm" style="width:120px;" onchange="this.form.submit()">
                                            <option value="pending" {{ $expense->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="approved" {{ $expense->status == 'approved' ? 'selected' : '' }}>Approve</option>
                                            <option value="paid" {{ $expense->status == 'paid' ? 'selected' : '' }}>Paid</option>
                                            <option value="rejected" {{ $expense->status == 'rejected' ? 'selected' : '' }}>Reject</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                                    No expenses found. Click "Add Expense" to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($expenses->hasPages())
            <div class="card-footer bg-white border-top-0 pt-3">{{ $expenses->links() }}</div>
        @endif
    </div>

    {{-- ADD EXPENSE MODAL --}}
    <div class="modal fade" id="addExpenseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('finance-hr.expenses.store') }}" class="modal-content" id="expenseForm">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Expense</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vendor / Payee</label>
                            <input type="text" name="vendor" class="form-control" placeholder="e.g., Office Supplies Co.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Base Amount (₹) *</label>
                            <input type="number" name="amount" id="exp_amount" class="form-control" step="0.01" min="0" required oninput="calculateTotal()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">GST (₹) *</label>
                            <input type="number" name="gst" id="exp_gst" class="form-control" step="0.01" min="0" value="0" required oninput="calculateTotal()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">TDS (₹) *</label>
                            <input type="number" name="tds" id="exp_tds" class="form-control" step="0.01" min="0" value="0" required oninput="calculateTotal()">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Mode *</label>
                            <select name="payment_mode" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="upi">UPI</option>
                                <option value="cheque">Cheque</option>
                                <option value="card">Card</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Total Payable</label>
                            <input type="text" id="exp_total" class="form-control bg-light fw-bold text-success" readonly value="₹0.00">
                            <small class="text-muted">Auto-calculated: Amount + GST - TDS</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description / Notes</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Purpose of expense..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i> Submit for Approval</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ADD CATEGORY MODAL --}}
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('finance-hr.expenses.category.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title"><i class="bi bi-tags me-2"></i>Add Expense Category</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Travel, Office, Marketing" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Icon (Bootstrap Icon)</label>
                            <select name="icon" class="form-select">
                                <option value="bi-receipt">📄 Receipt</option>
                                <option value="bi-airplane">✈️ Travel</option>
                                <option value="bi-lightning">⚡ Utilities</option>
                                <option value="bi-laptop">💻 IT/Equipment</option>
                                <option value="bi-megaphone">📢 Marketing</option>
                                <option value="bi-building">🏢 Office Rent</option>
                                <option value="bi-people">👥 Salaries</option>
                                <option value="bi-tools">🔧 Maintenance</option>
                                <option value="bi-truck">🚚 Logistics</option>
                                <option value="bi-gift">🎁 Misc</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Color</label>
                            <select name="color" class="form-select">
                                <option value="primary">Blue</option>
                                <option value="success">Green</option>
                                <option value="warning">Yellow</option>
                                <option value="danger">Red</option>
                                <option value="info">Cyan</option>
                                <option value="secondary">Gray</option>
                                <option value="dark">Dark</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function calculateTotal() {
        const amount = parseFloat(document.getElementById('exp_amount').value) || 0;
        const gst = parseFloat(document.getElementById('exp_gst').value) || 0;
        const tds = parseFloat(document.getElementById('exp_tds').value) || 0;
        const total = amount + gst - tds;
        document.getElementById('exp_total').value = '₹' + total.toFixed(2);
    }
</script>
@endpush