@extends('layouts.master')
@section('title', 'Traceability')
@section('page-title', 'Supply Chain Traceability')

@section('content')
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('scm.traceability.index') }}" class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by Batch No, SKU, Product Name, or Shipment #" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-6 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Track Movement</button>
                </div>
            </form>
        </div
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Date & Time</th>
                            <th>Action</th>
                            <th>Batch / Product</th>
                            <th>Route (From → To)</th>
                            <th>Shipment</th>
                            <th>Qty</th>
                            <th>Actor</th>
                            <th class="text-end pe-4">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $movement)
                            <tr>
                                <td class="ps-4 text-muted small">{{ $movement->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    @php
                                        $actionIcons = ['produced' => 'bi-gear', 'dispatched' => 'bi-truck', 'received' => 'bi-box-arrow-in-down', 'returned' => 'bi-arrow-counterclockwise'];
                                        $actionColors = ['produced' => 'primary', 'dispatched' => 'info', 'received' => 'success', 'returned' => 'warning'];
                                    @endphp
                                    <span class="badge bg-{{ $actionColors[$movement->action] ?? 'secondary' }} bg-opacity-10 text-{{ $actionColors[$movement->action] ?? 'secondary' }}">
                                        <i class="bi {{ $actionIcons[$movement->action] ?? 'bi-circle' }} me-1"></i>
                                        {{ ucfirst($movement->action) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $movement->batch->batch_no }}</div>
                                    <small class="text-muted">{{ $movement->batch->product->sku ?? 'Unknown' }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2 small">
                                        <span class="text-muted">{{ $movement->from_location ?? 'N/A' }}</span>
                                        <i class="bi bi-arrow-right text-primary"></i>
                                        <span class="fw-semibold">{{ $movement->to_location ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($movement->shipment)
                                        <a href="#" class="text-decoration-none small fw-semibold">{{ $movement->shipment->shipment_no }}</a>
                                    @else
                                        <span class="text-muted small">Internal</span>
                                    @endif
                                </td>
                                <td class="fw-bold">{{ $movement->qty }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:24px; height:24px; font-size:0.7rem;">
                                            {{ strtoupper(substr($movement->actor->name, 0, 1)) }}
                                        </div>
                                        <small>{{ $movement->actor->name }}</small>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('scm.traceability.show', $movement->batch) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-diagram-3 me-1"></i> Timeline
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-diagram-2 fs-1 d-block mb-2"></i>
                                    No movements found. Try a different search term.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($movements->hasPages())
            <div class="card-footer bg-white border-top-0 pt-3">{{ $movements->links() }}</div>
        @endif
    </div>
@endsection