<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $transaction->receipt_no }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Print-specific styles */
        @media print {
            .no-print { display: none !important; }
            body { background: white; margin: 0; padding: 0; }
            .receipt { 
                width: 80mm; 
                margin: 0 auto; 
                font-family: 'Courier New', Courier, monospace; 
                font-size: 12px; 
                box-shadow: none;
                padding: 0;
            }
        }
        
        /* Screen preview styles */
        body { 
            background: #e9ecef; 
            font-family: 'Inter', system-ui, sans-serif;
        }
        .receipt {
            width: 80mm;
            margin: 20px auto;
            background: white;
            padding: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
        }
        
        .receipt-header { 
            text-align: center; 
            border-bottom: 1px dashed #000; 
            padding-bottom: 12px; 
            margin-bottom: 12px; 
        }
        .receipt-header h3 { 
            margin: 0; 
            font-size: 20px; 
            font-weight: 900; 
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .receipt-header p {
            margin: 4px 0;
            font-size: 11px;
            color: #333;
        }

        .receipt-info {
            margin-bottom: 12px;
            font-size: 11px;
            line-height: 1.6;
        }

        .receipt-items { 
            border-bottom: 1px dashed #000; 
            padding: 8px 0; 
        }
        
        .receipt-table {
            width: 100%;
            border-collapse: collapse;
        }
        .receipt-table th {
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
        }
        .receipt-table th.text-center { text-align: center; }
        .receipt-table th.text-end { text-align: right; }
        
        .receipt-table td {
            padding: 6px 0;
            font-size: 11px;
            vertical-align: top;
        }
        .receipt-table td.text-center { text-align: center; }
        .receipt-table td.text-end { text-align: right; }

        .receipt-total { 
            padding: 10px 0; 
            border-bottom: 1px dashed #000; 
        }
        .receipt-total table {
            width: 100%;
        }
        .receipt-total td {
            padding: 3px 0;
            font-size: 12px;
        }
        .receipt-total .grand-total {
            font-size: 16px;
            font-weight: 900;
            border-top: 1px solid #000;
            padding-top: 6px;
            margin-top: 4px;
        }

        .receipt-footer { 
            text-align: center; 
            padding-top: 12px; 
            font-size: 10px;
            line-height: 1.5;
        }
        .receipt-footer .barcode {
            font-family: 'Libre Barcode 39', cursive; /* Fallback to standard if not loaded */
            font-size: 24px;
            margin-top: 8px;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    {{-- Print/Close Buttons (Hidden when printing) --}}
    <div class="text-center mb-4 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-lg px-4">
            <i class="bi bi-printer me-2"></i>Print Receipt
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary btn-lg px-4 ms-2">
            <i class="bi bi-x-circle me-2"></i>Close
        </button>
    </div>

    {{-- Actual Receipt --}}
    <div class="receipt">
        <div class="receipt-header">
            {{-- ✅ DYNAMIC COMPANY NAME (Defaults to SIGNUPALPHAGROW if not set) --}}
            <h3>{{ strtoupper($company->company_name ?? 'SIGNUPALPHAGROW') }}</h3>
            
            @if(!empty($company->address))
                <p>{{ $company->address }}</p>
            @else
                <p>123 Business Park, Tech City</p>
            @endif
            
            @if(!empty($company->tax_id))
                <p>{{ strtoupper($company->currency ?? 'INR') === 'INR' ? 'GSTIN' : 'TAX ID' }}: {{ $company->tax_id }}</p>
            @endif
            
            @if(!empty($company->phone))
                <p>Tel: {{ $company->phone }}</p>
            @endif
        </div>

        <div class="receipt-info">
            <div class="d-flex justify-content-between">
                <span><strong>Receipt #:</strong></span>
                <span>{{ $transaction->receipt_no }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span><strong>Date:</strong></span>
                <span>{{ $transaction->created_at->format('d M Y, h:i A') }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span><strong>Cashier:</strong></span>
                <span>{{ $transaction->user->name ?? 'System' }}</span>
            </div>
        </div>

        <div class="receipt-items">
            <table class="receipt-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->items as $item)
                        <tr>
                            <td>
                                <div style="font-weight: bold;">{{ $item['name'] }}</div>
                                <div style="font-size: 10px; color: #555;">{{ $item['sku'] }} @ ₹{{ number_format($item['price'], 2) }}</div>
                            </td>
                            <td class="text-center">{{ $item['qty'] }}</td>
                            <td class="text-end" style="font-weight: bold;">₹{{ number_format($item['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="receipt-total">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-end">₹{{ number_format($transaction->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td>Tax (GST):</td>
                    <td class="text-end">₹{{ number_format($transaction->tax, 2) }}</td>
                </tr>
                @if($transaction->discount > 0)
                <tr>
                    <td>Discount:</td>
                    <td class="text-end">- ₹{{ number_format($transaction->discount, 2) }}</td>
                </tr>
                @endif
                <tr class="grand-total">
                    <td>TOTAL:</td>
                    <td class="text-end">₹{{ number_format($transaction->total, 2) }}</td>
                </tr>
                <tr>
                    <td>Payment:</td>
                    <td class="text-end" style="text-transform: uppercase;">{{ str_replace('_', ' ', $transaction->payment_method) }}</td>
                </tr>
            </table>
        </div>

        <div class="receipt-footer">
            <p style="font-weight: bold; font-size: 11px;">*** Thank You for Your Purchase! ***</p>
            <p>Goods once sold will not be taken back or exchanged.</p>
            <p style="margin-top: 8px; font-size: 9px; color: #666;">Powered by AlphaGrow POS System</p>
            
            {{-- Optional: Visual Barcode of Receipt Number --}}
            <div class="barcode" style="font-family: monospace; font-size: 14px; margin-top: 10px;">
                ||| {{ $transaction->receipt_no }} |||
            </div>
        </div>
    </div>

    <script>
        // Optional: Auto-trigger print dialog when page loads
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>