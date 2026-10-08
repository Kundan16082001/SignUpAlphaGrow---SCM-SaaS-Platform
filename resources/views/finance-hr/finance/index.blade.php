@extends('layouts.master')
@section('title', 'Finance')
@section('page-title', 'Finance & Invoices')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="small mb-1 opacity-75">Total Revenue</p><h3 class="fw-bold mb-0">₹{{ number_format($stats['total_revenue'], 0) }}</h3></div>
                    <i class="bi bi-currency-rupee fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="small mb-1 opacity-75">Pending Payment</p><h3 class="fw-bold mb-0">₹{{ number_format($stats['pending_payment'], 0) }}</h3></div>
                    <i class="bi bi-hourglass-split fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="small mb-1 opacity-75">Overdue Invoices</p><h3 class="fw-bold mb-0">{{ $stats['overdue_count'] }}</h3></div>
                    <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="small mb-1 opacity-75">Total Invoices</p><h3 class="fw-bold mb-0">{{ $stats['total_invoices'] }}</h3></div>
                    <i class="bi bi-receipt fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="bi bi-receipt me-2"></i>Invoice List</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Invoice #</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th class="text-end">CGST</th>
                            <th class="text-end">SGST</th>
                            <th class="text-end">IGST</th>
                            <th class="text-end">Total</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            <tr>
                                <td class="ps-4 fw-bold text-primary">{{ $inv->invoice_no }}</td>
                                <td>{{ $inv->customer->name ?? 'N/A' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ strtoupper($inv->type) }}</span></td>
                                <td class="text-end">₹{{ number_format($inv->cgst, 2) }}</td>
                                <td class="text-end">₹{{ number_format($inv->sgst, 2) }}</td>
                                <td class="text-end">₹{{ number_format($inv->igst, 2) }}</td>
                                <td class="text-end fw-bold">₹{{ number_format($inv->total, 2) }}</td>
                                <td class="text-muted small">{{ $inv->due_date?->format('d M Y') ?? 'N/A' }}</td>
                                <td><span class="badge bg-{{ $inv->status == 'paid' ? 'success' : ($inv->status == 'overdue' ? 'danger' : 'warning') }}">{{ ucfirst($inv->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center py-5 text-muted">No invoices found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($invoices->hasPages())<div class="card-footer bg-white border-top-0 pt-3">{{ $invoices->links() }}</div>@endif
    </div>
@endsection