<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid #6366f1; padding-bottom: 15px; }
        .company-info h1 { color: #6366f1; margin: 0 0 5px 0; font-size: 24px; }
        .company-info p { margin: 2px 0; color: #666; }
        .invoice-meta { text-align: right; }
        .invoice-meta h2 { margin: 0 0 10px 0; color: #333; }
        .invoice-meta p { margin: 3px 0; }
        .customer-info { margin-bottom: 30px; background: #f8fafc; padding: 15px; border-radius: 5px; }
        .customer-info h3 { margin: 0 0 10px 0; font-size: 14px; color: #6366f1; text-transform: uppercase; }
        .customer-info p { margin: 3px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #6366f1; color: white; padding: 10px; text-align: left; font-size: 11px; text-transform: uppercase; }
        td { padding: 10px; border-bottom: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals { width: 40%; margin-left: auto; }
        .totals-row { display: flex; justify-content: space-between; padding: 5px 0; }
        .totals-row.grand { font-size: 16px; font-weight: bold; color: #6366f1; border-top: 2px solid #333; padding-top: 10px; margin-top: 5px; }
        .footer { text-align: center; margin-top: 40px; padding-top: 15px; border-top: 1px solid #e2e8f0; color: #666; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <h1>SignupAlphaGrow</h1>
            <p>123 Business Park, Andheri East</p>
            <p>Mumbai - 400069, Maharashtra</p>
            <p>GSTIN: 27AABCU9603R1ZM</p>
            <p>Phone: +91 98765 43210</p>
        </div>
        <div class="invoice-meta">
            <h2>INVOICE</h2>
            <p><strong>Invoice #:</strong> {{ $invoice->invoice_no }}</p>
            <p><strong>Date:</strong> {{ $invoice->created_at->format('d M Y') }}</p>
            <p><strong>Due Date:</strong> {{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}</p>
            <p><strong>Status:</strong> {{ strtoupper($invoice->status) }}</p>
        </div>
    </div>

    <div class="customer-info">
        <h3>Bill To</h3>
        <p><strong>{{ $invoice->customer->name ?? 'Walk-in Customer' }}</strong></p>
        <p>{{ $invoice->customer->address ?? 'No address provided' }}</p>
        <p>GSTIN: {{ $invoice->customer->gstin ?? 'N/A' }}</p>
        <p>Email: {{ $invoice->customer->email ?? 'N/A' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 40%;">Item</th>
                <th class="text-center" style="width: 10%;">Qty</th>
                <th class="text-right" style="width: 15%;">Price</th>
                <th class="text-center" style="width: 10%;">GST</th>
                <th class="text-right" style="width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $items = is_array($invoice->items) ? $invoice->items : []; @endphp
            @foreach($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item['name'] ?? 'Product' }}</strong><br>
                        <small style="color: #666;">{{ $item['sku'] ?? '' }}</small>
                    </td>
                    <td class="text-center">{{ $item['qty'] ?? 0 }}</td>
                    <td class="text-right">₹{{ number_format($item['price'] ?? 0, 2) }}</td>
                    <td class="text-center">{{ $item['gst_rate'] ?? 0 }}%</td>
                    <td class="text-right">₹{{ number_format($item['total'] ?? 0, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-row">
            <span>Subtotal:</span>
            <span>₹{{ number_format($invoice->subtotal ?? 0, 2) }}</span>
        </div>
        @if(($invoice->cgst ?? 0) > 0)
            <div class="totals-row">
                <span>CGST:</span>
                <span>₹{{ number_format($invoice->cgst, 2) }}</span>
            </div>
            <div class="totals-row">
                <span>SGST:</span>
                <span>₹{{ number_format($invoice->sgst, 2) }}</span>
            </div>
        @endif
        @if(($invoice->discount ?? 0) > 0)
            <div class="totals-row">
                <span>Discount:</span>
                <span style="color: #ef4444;">- ₹{{ number_format($invoice->discount, 2) }}</span>
            </div>
        @endif
        <div class="totals-row grand">
            <span>Grand Total:</span>
            <span>₹{{ number_format($invoice->total, 2) }}</span>
        </div>
    </div>

    @if(!empty($invoice->notes))
        <div style="margin-top: 30px; padding: 15px; background: #f8fafc; border-left: 3px solid #6366f1;">
            <strong>Notes:</strong><br>
            <em style="color: #666;">{{ $invoice->notes }}</em>
        </div>
    @endif

    <div class="footer">
        <p>Thank you for your business! | This is a computer-generated document.</p>
        <p>© {{ date('Y') }} SignupAlphaGrow. All rights reserved.</p>
    </div>
</body>
</html>