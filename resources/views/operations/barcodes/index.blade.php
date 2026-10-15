@extends('layouts.master')
@section('title', 'Barcode Generator')
@section('page-title', 'Barcode & Label Printing')

@section('content')
    <form method="POST" action="{{ route('operations.barcodes.generate') }}" target="_blank">
        @csrf
        
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-upc-scan me-2"></i>Select Products</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @forelse($products as $product)
                        <div class="col-md-4 col-lg-3">
                            <div class="form-check border rounded-3 p-3 h-100">
                                <input class="form-check-input" type="checkbox" name="products[]" value="{{ $product->id }}" id="prod_{{ $product->id }}">
                                <label class="form-check-label w-100" for="prod_{{ $product->id }}">
                                    <div class="fw-semibold small">{{ $product->name }}</div>
                                    <small class="text-muted font-monospace">{{ $product->sku }}</small>
                                    <div class="mt-1">
                                        <span class="badge bg-light text-dark border">₹{{ number_format($product->price, 0) }}</span>
                                        <span class="badge bg-light text-dark border">Stock: {{ $product->stock_qty }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No products available.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-gear me-2"></i>Label Settings</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Label Size *</label>
                        <select name="label_size" class="form-select" required>
                            <option value="small">Small (50mm x 25mm)</option>
                            <option value="medium" selected>Medium (75mm x 35mm)</option>
                            <option value="large">Large (100mm x 50mm)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Barcode Type *</label>
                        <select name="barcode_type" class="form-select" required>
                            <option value="code128">Code 128 (Standard)</option>
                            <option value="qr">QR Code</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Labels per Product *</label>
                        <input type="number" name="qty_per_product" class="form-control" value="1" min="1" max="100" required>
                        <small class="text-muted">How many labels for each selected product</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-3">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-printer me-2"></i> Generate & Print Labels
            </button>
            <a href="{{ route('operations.inventory.index') }}" class="btn btn-outline-secondary btn-lg">
                <i class="bi bi-arrow-left me-2"></i> Back to Inventory
            </a>
        </div>
    </form>
@endsection