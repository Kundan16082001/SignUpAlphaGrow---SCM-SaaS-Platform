<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $transaction->receipt_no }}</title>
    <style>
        /* ✅ Updated for A4 paper size */
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            font-size: 12px; 
            color: #000; 
            margin: 20px;
            padding: 0;
        }
        .header { 
            text-align: center; 
            border-bottom: 2px dashed #000; 
            padding-bottom: 15px; 
            margin-bottom: 15px; 
        }
        .header h2 { 
            margin: 0 0 8px 0; 
            font-size: 20px; 
            font-weight: bold;
        }
        .header p { 
            margin: 3px 0; 
            font-size: 11px;
        }
        .meta { 
            margin-bottom: 15px; 
            font-size: 11px; 
        }
        .meta-row { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 5px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px; 
        }
        th { 
            text-align: left; 
            border-bottom: 2px dashed #000; 
            padding: 8px 0; 
            font-size: 11px; 
            font-weight: bold;
        }
        td { 
            padding: 8px 0; 
            border-bottom: 1px dotted #ccc; 
            font-size: 11px;
        }
        .text-right { 
            text-align: right; 
        }
        .text-center { 
            text-align: center; 
        }
        .totals { 
            border-top: 2px dashed #000; 
            padding-top: 10px; 
            margin-top: 10px;
        }
        .totals-row { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 5px; 
            font-size: 12px;
        }
        .grand-total { 
            font-size: 18px; 
            font-weight: bold; 
            margin-top: 10px; 
            border-top: 2px solid #000; 
            padding-top: 10px; 
        }
        .footer { 
            text-align: center; 
            margin-top: 20px; 
            font-size: 10px; 
            border-top: 2px dashed #000; 
            padding-top: 15px; 
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>SIGNUPALPHAGROW</h2>
        <p>123 Business Park, Tech City</p>
        <p>GSTIN: 27AABCU9603R1ZM</p>
        <p>Tel: +91 98765 43210</p>
    </div>

    <div class="meta">
        <div class="meta-row">
            <span><strong>Receipt #:</strong></span>
            <span>{{ $transaction->receipt_no }}</span>
        </div>
        <div class="meta-row">
            <span><strong>Date:</strong></span>
            <span>{{ $transaction->created_at->format('d M Y, h:i A') }}</span>
        </div>
        <div class="meta-row">
            <span><strong>Cashier:</strong></span>
            <span>{{ $transaction->user->name ?? 'System' }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item['name'] }}</strong><br>
                        <span style="font-size: 10px; color: #666;">{{ $item['sku'] }} @ ₹{{ number_format($item['price'], 2) }}</span>
                    </td>
                    <td class="text-center">{{ $item['qty'] }}</td>
                    <td class="text-right">₹{{ number_format($item['total'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-row">
            <span>Subtotal:</span>
            <span>₹{{ number_format($transaction->subtotal, 2) }}</span>
        </div>
        <div class="totals-row">
            <span>Tax (GST):</span>
            <span>₹{{ number_format($transaction->tax, 2) }}</span>
        </div>
        <div class="totals-row grand-total">
            <span>TOTAL:</span>
            <span>₹{{ number_format($transaction->total, 2) }}</span>
        </div>
        <div class="totals-row" style="margin-top: 10px;">
            <span>Payment:</span>
            <span>{{ strtoupper(str_replace('_', ' ', $transaction->payment_method)) }}</span>
        </div>
    </div>

    <div class="footer">
        <p style="font-weight: bold;">*** Thank You for Your Purchase ***</p>
        <p>Goods once sold will not be taken back or exchanged.</p>
        <p style="margin-top: 10px;">Powered by AlphaGrow POS System</p>
    </div>
</body>
</html>