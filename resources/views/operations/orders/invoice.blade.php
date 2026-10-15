<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $order->order_no }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; padding: 0; }
            .card { border: none !important; box-shadow: none !important; }
        }
        .company-header { border-bottom: 2px solid #0d6efd; padding-bottom: 15px; margin-bottom: 20px; }
        .invoice-title { color: #0d6efd; font-weight: 700; }
        .table thead th { background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; font-size: 0.85rem; text-transform: uppercase; }
        .total-box { background-color: #f8f9fa; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body class="p-4 p-md-5">
    <div class="container" style="max-width: 800px;">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h4 class="mb-0">Invoice Preview</h4>
            <!-- add back to order button -->
             <a href="{{ route('operations.orders.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i>Back to Orders</a>
            <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer me-2"></i>Print / Save as PDF</button>
        </div>

        <div class="card p-4 shadow-sm">
            {{-- Header --}}
            <div class="row mb-4 company-header">
                <div class="col-6">
                    <h2 class="fw-bold text-primary mb-1">SIGNUPALPHAGROW</h2>
                    <p class="mb-1 small">123 Business Park, Tech City</p>
                    <p class="mb-0 small">GSTIN: 27AABCU9603R1ZM | Tel: +91 98765 43210</p>
                </div>
                <div class="col-6 text-end">
                    <h3 class="invoice-title mb-2">INVOICE</h3>
                    <p class="mb-1"><strong>Order No:</strong> {{ $order->order_no }}</p>
                    <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($order->order_date)->format('d M Y') }}</p>
                    <p class="mb-0"><strong>Type:</strong> {{ ucfirst($order->type) }}</p>
                </div>
            </div>

            {{-- Bill To --}}
            <div class="row mb-4">
                <div class="col-6">
                    <h6 class="fw-bold text-muted text-uppercase small mb-2">Bill To:</h6>
                    <h5 class="fw-bold mb-1">{{ $order->customer->name ?? 'N/A' }}</h5>
                    <p class="mb-1 small">GSTIN: {{ $order->customer->gstin ?: 'N/A' }}</p>
                    <p class="mb-0 small">Phone: {{ $order->customer->phone ?: 'N/A' }}</p>
                </div>
                @if($order->notes)
                <div class="col-6 text-end">
                    <h6 class="fw-bold text-muted text-uppercase small mb-2">Notes:</h6>
                    <p class="fst-italic small text-muted">"{{ $order->notes }}"</p>
                </div>
                @endif
            </div>

            {{-- Items Table --}}
            <table class="table table-bordered mb-4">
                <thead>
                    <tr>
                        <th style="width: 10%;">#</th>
                        <th style="width: 35%;">SKU / Product</th>
                        <th class="text-center" style="width: 10%;">Qty</th>
                        <th class="text-end" style="width: 15%;">Unit Price</th>
                        <th class="text-end" style="width: 15%;">Tax (GST)</th>
                        <th class="text-end" style="width: 15%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $item->sku }}</div>
                                <small class="text-muted">{{ $item->product->name ?? 'Product' }}</small>
                            </td>
                            <td class="text-center">{{ $item->qty }}</td>
                            <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end">₹{{ number_format($item->tax_amount, 2) }} <small class="text-muted">({{ $item->tax_rate }}%)</small></td>
                            <td class="text-end fw-bold">₹{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Subtotal:</td>
                        <td class="text-end">₹{{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Total Tax:</td>
                        <td class="text-end">₹{{ number_format($order->tax, 2) }}</td>
                    </tr>
                    <tr class="table-primary">
                        <td colspan="5" class="text-end fw-bold fs-5">Grand Total:</td>
                        <td class="text-end fw-bold fs-5">₹{{ number_format($order->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            {{-- Footer --}}
            <div class="mt-5 pt-4 border-top text-center text-muted">
                <p class="fw-bold mb-1">Thank you for your business!</p>
                <small class="d-block">This is a computer-generated invoice and does not require a signature.</small>
                <small class="d-block">Powered by AlphaGrow</small>
            </div>
        </div>
    </div>
</body>
</html>