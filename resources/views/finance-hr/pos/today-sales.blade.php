@extends('layouts.master')
@section('title', 'Today\'s Sales')
@section('page-title', 'Today\'s Sales Summary')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <h6 class="opacity-75">Total Sales</h6>
                    <h3 class="fw-bold">₹{{ number_format($summary['total_sales'], 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <h6 class="opacity-75">Transactions</h6>
                    <h3 class="fw-bold">{{ $summary['total_transactions'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <h6 class="opacity-75">Cash Sales</h6>
                    <h3 class="fw-bold">₹{{ number_format($summary['cash_sales'], 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body text-center">
                    <h6 class="opacity-75">Digital Payments</h6>
                    <h3 class="fw-bold">₹{{ number_format($summary['upi_sales'] + $summary['card_sales'] + $summary['net_banking_sales'], 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Today's Transactions</h6>
            <a href="{{ route('finance-hr.pos.index') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-arrow-left me-1"></i> Back to POS
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Time</th>
                            <th>Receipt #</th>
                            <th class="text-center">Items</th>
                            <th class="text-end">Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $t)
                            <tr>
                                <td class="ps-4 text-muted small">{{ $t->created_at->format('h:i A') }}</td>
                                <td class="fw-bold text-primary">{{ $t->receipt_no }}</td>
                                <td class="text-center">
                                    @php
                                        $itemCount = is_array($t->items) ? count($t->items) : 0;
                                    @endphp
                                    <span class="badge bg-light text-dark border">{{ $itemCount }}</span>
                                </td>
                                <td class="text-end fw-bold">₹{{ number_format($t->total, 2) }}</td>
                                <td>
                                    @php
                                        $payIcons = [
                                            'cash' => 'bi-cash-coin', 
                                            'upi' => 'bi-phone', 
                                            'card' => 'bi-credit-card', 
                                            'net_banking' => 'bi-bank'
                                        ];
                                    @endphp
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi {{ $payIcons[$t->payment_method] ?? 'bi-wallet2' }} me-1"></i>
                                        {{ ucfirst(str_replace('_', ' ', $t->payment_method)) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = ['completed' => 'success', 'refunded' => 'warning', 'void' => 'danger'];
                                        $color = $statusColors[$t->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }}">
                                        {{ ucfirst($t->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    {{-- ✅ FIX: Explicitly pass $t->receipt_no instead of the whole $t object --}}
                                    <a href="{{ route('finance-hr.pos.receipt', $t->receipt_no) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Print Receipt">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                                    No transactions today yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection