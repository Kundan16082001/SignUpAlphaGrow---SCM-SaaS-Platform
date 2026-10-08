<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $order->order_no }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .card { border: 1px solid #dee2e6 !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="p-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h4>Invoice Preview</h4>
            <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer me-2"></i>Print / Save as PDF</button>
        </div>

        <div class="card p-4">
            <div class="row mb-4">
                <div class="col-6">
                    <h2 class="fw-bold text-primary">AlphaGrow</h2>
                    <p class="mb-0">123 Business Park, Tech City</p>
                    <p>GSTIN: 27AABCU9603R1ZM</p>
                </div>
                <div class="col-6 text-end">
                    <h3 class="fw-bold">INVOICE</h3>
                    <p class="mb-0"><strong>Order No:</strong> {{ $order->order_no }}</p>
                    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($order->order_date)->format('d M Y') }}</p>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-6">
                    <h6 class="fw-bold text-muted text-uppercase">Bill To:</h6>
                    <h5 class="fw-bold">{{ $order->customer->name }}</h5>
                    <p class="mb-0">GSTIN: {{ $order->customer->gstin ?: 'N/A' }}</p>
                    <p>{{ $order->customer->phone }}</p>
                </div>
            </div>

            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>SKU</th>
                        <th>Qty</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Tax ({{ $order->items->first()->tax_rate ?? 18 }}%)</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->sku }}</td>
                            <td>{{ $item->qty }}</td>
                            <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end">₹{{ number_format($item->tax_amount, 2) }}</td>
                            <td class="text-end fw-bold">₹{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                        <td class="text-end">₹{{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end fw-bold">Total Tax:</td>
                        <td class="text-end">₹{{ number_format($order->tax, 2) }}</td>
                    </tr>
                    <tr class="table-primary">
                        <td colspan="4" class="text-end fw-bold fs-5">Grand Total:</td>
                        <td class="text-end fw-bold fs-5">₹{{ number_format($order->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            <div class="mt-5 pt-5 border-top text-center text-muted">
                <p class="mb-0">Thank you for your business!</p>
                <small>This is a computer-generated invoice.</small>
            </div>
        </div>
    </div>
</body>
</html>