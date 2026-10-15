@extends('layouts.master')
@section('title', 'Invoice ' . $invoice->invoice_no)
@section('page-title', 'Invoice Details')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show no-print">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(request('print'))
        <style>
            .no-print { display: none !important; }
            body { background: white !important; }
            .main-content { margin-left: 0 !important; }
            .topbar, .sidebar { display: none !important; }
        </style>
    @endif

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
    @endphp

    {{-- ==================== HEADER ==================== --}}
    <div class="d-flex justify-content-between align-items-start mb-4 no-print flex-wrap gap-3">
        <div>
            <a href="{{ route('finance-hr.finance.index') }}" class="text-muted small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Back to Invoices
            </a>
            <h4 class="fw-bold mt-2 mb-2 text-dark">{{ $invoice->invoice_no }}</h4>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} px-3 py-2">
                    <i class="bi {{ $icon }} me-1"></i>{{ ucfirst($invoice->status) }}
                </span>
                <span class="badge bg-light text-dark border px-3 py-2">
                    <i class="bi bi-calendar3 me-1"></i>{{ $invoice->created_at->format('d M Y') }}
                </span>
                <span class="badge bg-light text-dark border px-3 py-2 text-uppercase">
                    {{ $invoice->type ?? 'b2b' }}
                </span>
                <a href="{{ route('finance-hr.invoices.pdf', $invoice) }}" class="btn btn-outline-dark">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
                </a>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            {{-- Status Update Dropdown --}}
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-pencil-square me-1"></i> Change Status
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach(['draft' => 'Draft', 'sent' => 'Sent', 'paid' => 'Mark as Paid', 'overdue' => 'Overdue', 'cancelled' => 'Cancel Invoice'] as $status => $label)
                        <li>
                            <form method="POST" action="{{ route('finance-hr.invoices.updateStatus', $invoice) }}">
                                @csrf
                                <input type="hidden" name="status" value="{{ $status }}">
                                <button type="submit" class="dropdown-item {{ $status === 'paid' ? 'text-success' : ($status === 'cancelled' || $status === 'overdue' ? 'text-danger' : '') }}">
                                    <i class="bi {{ $statusIcons[$status] }} me-2"></i>{{ $label }}
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Email Button --}}
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#sendEmailModal">
                <i class="bi bi-envelope me-1"></i> Send via Email
            </button>

            {{-- Print Button --}}
            <a href="{{ route('finance-hr.invoices.show', $invoice) }}?print=1" target="_self" class="btn btn-outline-primary">
                <i class="bi bi-printer me-1"></i> Print
            </a>

            {{-- Delete Button --}}
            @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                <form method="POST" action="{{ route('finance-hr.invoices.destroy', $invoice) }}" 
                      class="d-inline" onsubmit="return confirm('Delete this invoice permanently?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- ==================== INVOICE CARD ==================== --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            
            {{-- Company & Customer Info --}}
            <div class="row mb-5">
                <div class="col-md-6 mb-4 mb-md-0">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" 
                             style="width: 56px; height: 56px;">
                            <i class="bi bi-building fs-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted text-uppercase small fw-bold mb-1">From</h6>
                            <h5 class="fw-bold mb-0">SignupAlphaGrow</h5>
                        </div>
                    </div>
                    <p class="text-muted small mb-1">123 Business Park, Andheri East</p>
                    <p class="text-muted small mb-1">Mumbai - 400069, Maharashtra</p>
                    <p class="text-muted small mb-1">GSTIN: 27AABCU9603R1ZM</p>
                    <p class="text-muted small mb-0">Phone: +91 98765 43210</p>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center" 
                             style="width: 56px; height: 56px;">
                            <i class="bi bi-person fs-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted text-uppercase small fw-bold mb-1">Billed To</h6>
                            <h5 class="fw-bold mb-0">{{ $invoice->customer->name ?? 'Walk-in Customer' }}</h5>
                        </div>
                    </div>
                    @if($invoice->customer)
                        <p class="text-muted small mb-1">{{ $invoice->customer->address ?? 'No address' }}</p>
                        <p class="text-muted small mb-1">
                            <strong>GSTIN:</strong> {{ $invoice->customer->gstin ?? 'N/A' }}
                        </p>
                        <p class="text-muted small mb-1">
                            <strong>Email:</strong> {{ $invoice->customer->email ?? 'N/A' }}
                        </p>
                        <p class="text-muted small mb-0">
                            <strong>Phone:</strong> {{ $invoice->customer->phone ?? 'N/A' }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Invoice Meta Info --}}
            <div class="row g-3 mb-4 p-3 bg-light rounded-3">
                <div class="col-md-3 col-6">
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Invoice Number</small>
                    <div class="fw-bold font-monospace">{{ $invoice->invoice_no }}</div>
                </div>
                <div class="col-md-3 col-6">
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Invoice Date</small>
                    <div class="fw-bold">{{ $invoice->created_at->format('d M Y') }}</div>
                </div>
                <div class="col-md-3 col-6">
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Due Date</small>
                    <div class="fw-bold {{ $invoice->due_date && $invoice->due_date->isPast() && $invoice->status !== 'paid' ? 'text-danger' : '' }}">
                        {{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Status</small>
                    <div>
                        <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} px-3 py-2">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="table-responsive mb-4">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Item</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Price</th>
                            <th class="text-center">GST %</th>
                            <th class="text-end">Tax</th>
                            <th class="text-end pe-3">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $items = is_array($invoice->items) ? $invoice->items : []; @endphp
                        @forelse($items as $index => $item)
                            <tr>
                                <td class="ps-3 text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $item['name'] ?? 'Product' }}</div>
                                    <small class="text-muted font-monospace">{{ $item['sku'] ?? '' }}</small>
                                </td>
                                <td class="text-center">{{ $item['qty'] ?? 0 }}</td>
                                <td class="text-end">₹{{ number_format($item['price'] ?? 0, 2) }}</td>
                                <td class="text-center">{{ $item['gst_rate'] ?? 0 }}%</td>
                                <td class="text-end">₹{{ number_format($item['tax'] ?? 0, 2) }}</td>
                                <td class="text-end pe-3 fw-bold">₹{{ number_format($item['total'] ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No items in this invoice.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Totals --}}
            <div class="row">
                <div class="col-md-7 mb-4 mb-md-0">
                    @if(!empty($invoice->notes))
                        <h6 class="text-muted text-uppercase small fw-bold mb-2">Notes</h6>
                        <p class="text-muted small mb-0 fst-italic">"{{ $invoice->notes }}"</p>
                    @endif
                </div>
                <div class="col-md-5">
                    <div class="bg-light rounded-3 p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-semibold">₹{{ number_format($invoice->subtotal ?? 0, 2) }}</span>
                        </div>
                        @if(($invoice->cgst ?? 0) > 0)
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">CGST</span>
                                <span class="small">₹{{ number_format($invoice->cgst, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">SGST</span>
                                <span class="small">₹{{ number_format($invoice->sgst, 2) }}</span>
                            </div>
                        @endif
                        @if(($invoice->igst ?? 0) > 0)
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">IGST</span>
                                <span class="small">₹{{ number_format($invoice->igst, 2) }}</span>
                            </div>
                        @endif
                        @if(($invoice->discount ?? 0) > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Discount</span>
                                <span class="fw-semibold text-danger">- ₹{{ number_format($invoice->discount, 2) }}</span>
                            </div>
                        @endif
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5">Grand Total</span>
                            <span class="fw-bold fs-3 text-primary">₹{{ number_format($invoice->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="text-center mt-5 pt-4 border-top">
                <p class="text-muted small mb-1">Thank you for your business!</p>
                <p class="text-muted small mb-0">This is a computer-generated invoice.</p>
            </div>
        </div>
    </div>

    {{-- ==================== EMAIL MODAL ==================== --}}
    <div class="modal fade no-print" id="sendEmailModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('invoices.send-email', $invoice) }}" class="modal-content">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-envelope me-2"></i>Send Invoice {{ $invoice->invoice_no }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-light border mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Customer:</span>
                            <strong>{{ $invoice->customer->name ?? 'N/A' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Amount:</span>
                            <strong class="text-success">₹{{ number_format($invoice->total, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">Due Date:</span>
                            <strong>{{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}</strong>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Recipient Email <span class="text-danger">*</span></label>
                        <input type="email" name="recipient_email" class="form-control" 
                               value="{{ $invoice->customer->email ?? '' }}" required>
                    </div>
                    
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Subject</label>
                        <input type="text" name="subject" class="form-control" 
                               value="Invoice {{ $invoice->invoice_no }} from SignupAlphaGrow">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-send me-1"></i> Send Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection