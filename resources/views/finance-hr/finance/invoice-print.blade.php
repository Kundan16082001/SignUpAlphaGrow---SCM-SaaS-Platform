<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->invoice_no }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .card { border: 1px solid #dee2e6 !important; box-shadow: none !important; }
        }
        body { font-size: 14px; }
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
                    <h3 class="fw-bold">TAX INVOICE</h3>
                    <p class="mb-0"><strong>Invoice #:</strong> {{ $invoice->invoice_no }}</p>
                    <p><strong>Date:</strong> {{ $invoice->created_at->format('d M Y') }}</p>
                    <p><strong>Due Date:</strong> {{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-6">
                    <h6 class="fw-bold text-muted text-uppercase">Bill To:</h6>
                    <h5 class="fw-bold">{{ $invoice->customer->name ?? 'N/A' }}</h5>
                    <p class="mb-0">GSTIN: {{ $invoice->customer->gstin ?? 'N/A' }}</p>
                    <p>{{ $invoice->customer->phone }}</p>
                </div>
                <div class="col-6 text-end">
                    <h6 class="fw-bold text-muted text-uppercase">Invoice Type:</h6>
                    <h5 class="fw-bold">{{ strtoupper($invoice->type) }}</h5>
                    <span class="badge bg-{{ $invoice->status_color }} fs-6">{{ ucfirst($invoice->status) }}</span>
                </div>
            </div>

            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th>HSN</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Rate</th>
                        <th class="text-end">Taxable</th>
                        <th class="text-end">CGST</th>
                        <th class="text-end">SGST</th>
                        <th class="text-end">IGST</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td><code>{{ $item->hsn_code ?? 'N/A' }}</code></td>
                            <td class="text-center">{{ $item->qty }}</td>
                            <td class="text-end">₹{{ number_format($item->rate, 2) }}</td>
                            <td class="text-end">₹{{ number_format($item->taxable_value, 2) }}</td>
                            <td class="text-end">₹{{ number_format($item->cgst, 2) }}</td>
                            <td class="text-end">₹{{ number_format($item->sgst, 2) }}</td>
                            <td class="text-end">₹{{ number_format($item->igst, 2) }}</td>
                            <td class="text-end fw-bold">₹{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                        <td class="text-end">₹{{ number_format($invoice->total - $invoice->total_gst, 2) }}</td>
                        <td class="text-end">₹{{ number_format($invoice->cgst, 2) }}</td>
                        <td class="text-end">₹{{ number_format($invoice->sgst, 2) }}</td>
                        <td class="text-end">₹{{ number_format($invoice->igst, 2) }}</td>
                        <td></td>
                    </tr>
                    <tr class="table-primary">
                        <td colspan="8" class="text-end fw-bold fs-5">Grand Total:</td>
                        <td class="text-end fw-bold fs-5">₹{{ number_format($invoice->total, 2) }}</td>
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