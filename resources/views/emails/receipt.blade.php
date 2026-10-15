<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; }
        .body { padding: 30px; }
        .receipt-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .receipt-table th { background: #f8fafc; padding: 10px; text-align: left; font-size: 13px; color: #64748b; border-bottom: 2px solid #e2e8f0; }
        .receipt-table td { padding: 12px 10px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; }
        .total-row { font-size: 18px; font-weight: 700; color: #10b981; text-align: right; padding: 15px 0; border-top: 2px solid #10b981; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #64748b; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: #d1fae5; color: #065f46; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ Payment Successful</h1>
            <p style="margin: 8px 0 0; opacity: 0.9;">Receipt #{{ $transaction->receipt_no }}</p>
        </div>
        <div class="body">
            <p style="color: #475569;">Thank you for your purchase! Here's your receipt:</p>
            
            <table class="receipt-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item['name'] }}</strong><br>
                                <small style="color: #94a3b8;">{{ $item['sku'] }}</small>
                            </td>
                            <td style="text-align: center;">{{ $item['qty'] }}</td>
                            <td style="text-align: right;">₹{{ number_format($item['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="display: flex; justify-content: space-between; padding: 10px 0; color: #64748b;">
                <span>Subtotal:</span>
                <span>₹{{ number_format($transaction->subtotal, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 5px 0; color: #64748b;">
                <span>Tax (GST):</span>
                <span>₹{{ number_format($transaction->tax, 2) }}</span>
            </div>
            <div class="total-row" style="display: flex; justify-content: space-between;">
                <span>Total Paid:</span>
                <span>₹{{ number_format($transaction->total, 2) }}</span>
            </div>

            <p style="text-align: center; margin-top: 20px;">
                <span class="badge">Paid via {{ strtoupper(str_replace('_', ' ', $transaction->payment_method)) }}</span>
            </p>
        </div>
        <div class="footer">
            <p><strong>SignupAlphaGrow</strong> · Thank you for shopping with us!</p>
        </div>
    </div>
</body>
</html>