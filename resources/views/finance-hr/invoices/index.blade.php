@extends('layouts.master')
@section('title', 'Invoices')
@section('page-title', 'Invoice Management')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error') || $errors->has('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') ?? $errors->first('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ==================== STATS ROW ==================== --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--bs-primary) !important;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                        <i class="bi bi-receipt fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Total Invoices</small>
                        <h4 class="fw-bold mb-0 mt-1">{{ $stats['total'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--bs-success) !important;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                        <i class="bi bi-cash-coin fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Paid Amount</small>
                        <h4 class="fw-bold mb-0 mt-1 text-success">₹{{ number_format($stats['paid'] / 100000, 1) }}L</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--bs-warning) !important;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Pending Amount</small>
                        <h4 class="fw-bold mb-0 mt-1 text-warning">₹{{ number_format($stats['pending'] / 100000, 1) }}L</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--bs-danger) !important;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                        <i class="bi bi-exclamation-triangle fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Overdue</small>
                        <h4 class="fw-bold mb-0 mt-1 text-danger">{{ $stats['overdue'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== MAIN CARD (Filters + Table) ==================== --}}
    <div class="card border-0 shadow-sm">
        {{-- Filters Header --}}
        <div class="card-header bg-white border-bottom py-3">
            <form method="GET" action="{{ route('finance-hr.finance.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label small fw-semibold text-muted mb-1">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" 
                               placeholder="Invoice # or customer name..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                    <select name="status" class="form-select bg-light">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">From Date</label>
                    <input type="date" name="from_date" class="form-control bg-light" value="{{ request('from_date') }}">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">To Date</label>
                    <input type="date" name="to_date" class="form-control bg-light" value="{{ request('to_date') }}">
                </div>
                <div class="col-lg-2 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('finance-hr.finance.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- Table Header with Action --}}
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt me-2 text-primary"></i>All Invoices</h6>
            <div class="d-flex gap-2 align-items-center">
                <span class="badge bg-light text-dark border px-3 py-2">{{ $invoices->total() }} Records</span>
                <a href="{{ route('finance-hr.invoices.create') }}" class="btn btn-primary btn-sm shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> Create Invoice
                </a>
            </div>
        </div>

        {{-- Table Body --}}
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-muted small text-uppercase fw-bold">Invoice #</th>
                            <th class="py-3 text-muted small text-uppercase fw-bold">Customer</th>
                            <th class="py-3 text-muted small text-uppercase fw-bold">Type</th>
                            <th class="py-3 text-muted small text-uppercase fw-bold">Date</th>
                            <th class="py-3 text-muted small text-uppercase fw-bold">Due Date</th>
                            <th class="py-3 text-muted small text-uppercase fw-bold text-end">Amount</th>
                            <th class="py-3 text-muted small text-uppercase fw-bold text-center">Status</th>
                            <th class="py-3 text-muted small text-uppercase fw-bold text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            @php
                                $statusColors = [
                                    'paid' => 'success',
                                    'sent' => 'info',
                                    'overdue' => 'danger',
                                    'draft' => 'secondary',
                                    'cancelled' => 'dark'
                                ];
                                $statusIcons = [
                                    'paid' => 'bi-check-circle-fill',
                                    'sent' => 'bi-envelope-fill',
                                    'overdue' => 'bi-exclamation-triangle-fill',
                                    'draft' => 'bi-file-earmark',
                                    'cancelled' => 'bi-x-circle-fill'
                                ];
                                $color = $statusColors[$invoice->status] ?? 'secondary';
                                $icon = $statusIcons[$invoice->status] ?? 'bi-circle';
                                
                                // Generate initials for avatar
                                $initials = strtoupper(substr($invoice->customer->name ?? 'W', 0, 1));
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <a href="{{ route('finance-hr.invoices.show', $invoice) }}" class="text-decoration-none">
                                        <div class="fw-bold text-primary font-monospace">{{ $invoice->invoice_no }}</div>
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold small text-dark">{{ $invoice->customer->name ?? 'Walk-in Customer' }}</div>
                                            <small class="text-muted" style="font-size: 0.75rem;">{{ $invoice->customer->email ?? 'No email provided' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border text-uppercase fw-normal" style="font-size: 0.7rem;">
                                        {{ strtoupper($invoice->type ?? 'B2B') }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $invoice->created_at->format('d M Y') }}</td>
                                <td class="small {{ $invoice->status === 'overdue' ? 'text-danger fw-semibold' : 'text-muted' }}">
                                    {{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}
                                </td>
                                <td class="text-end fw-bold text-dark pe-4">₹{{ number_format($invoice->total, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} px-3 py-2 rounded-pill">
                                        <i class="bi {{ $icon }} me-1"></i>{{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('finance-hr.invoices.show', $invoice) }}" 
                                           class="btn btn-sm btn-light text-primary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-light text-success" 
                                                title="Send via Email"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#sendEmailModal{{ $invoice->id }}">
                                            <i class="bi bi-envelope"></i>
                                        </button>
                                        <a href="{{ route('finance-hr.invoices.show', $invoice) }}?print=1" 
                                           target="_blank"
                                           class="btn btn-sm btn-light text-secondary" title="Print">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <a href="{{ route('finance-hr.invoices.pdf', $invoice) }}" class="btn btn-sm btn-light text-dark" title="Download PDF">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                        @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                                            <form method="POST" action="{{ route('finance-hr.invoices.destroy', $invoice) }}" 
                                                  class="d-inline" onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light text-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    {{-- Email Modal for this invoice --}}
                                    <div class="modal fade" id="sendEmailModal{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <form method="POST" action="{{ route('invoices.send-email', $invoice) }}" class="modal-content border-0 shadow">
                                                @csrf
                                                <div class="modal-header bg-success text-white border-0">
                                                    <h5 class="modal-title fw-bold">
                                                        <i class="bi bi-envelope me-2"></i>Send Invoice
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="alert alert-light border mb-4 rounded-3">
                                                        <div class="d-flex justify-content-between small mb-2">
                                                            <span class="text-muted">Invoice:</span>
                                                            <strong class="font-monospace">{{ $invoice->invoice_no }}</strong>
                                                        </div>
                                                        <div class="d-flex justify-content-between small mb-2">
                                                            <span class="text-muted">Customer:</span>
                                                            <strong>{{ $invoice->customer->name ?? 'N/A' }}</strong>
                                                        </div>
                                                        <div class="d-flex justify-content-between small">
                                                            <span class="text-muted">Total Amount:</span>
                                                            <strong class="text-success fs-5">₹{{ number_format($invoice->total, 2) }}</strong>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-uppercase text-muted">Recipient Email</label>
                                                        <input type="email" name="recipient_email" class="form-control form-control-lg" 
                                                               value="{{ $invoice->customer->email ?? old('recipient_email') }}" 
                                                               placeholder="customer@example.com" required>
                                                    </div>
                                                    
                                                    <div class="mb-0">
                                                        <label class="form-label fw-semibold small text-uppercase text-muted">Subject</label>
                                                        <input type="text" name="subject" class="form-control" 
                                                               value="Invoice {{ $invoice->invoice_no }} from SignupAlphaGrow">
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success px-4">
                                                        <i class="bi bi-send me-1"></i> Send Invoice
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted mb-3">
                                        <i class="bi bi-receipt fs-1 d-block mb-2 opacity-50"></i>
                                        <h5 class="fw-bold">No invoices found</h5>
                                        <p class="small">Try adjusting your filters or create a new invoice to get started.</p>
                                    </div>
                                    <a href="{{ route('finance-hr.invoices.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i> Create Your First Invoice
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- Pagination --}}
        @if($invoices->hasPages())
            <div class="card-footer bg-white border-top-0 py-3">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
@endsection