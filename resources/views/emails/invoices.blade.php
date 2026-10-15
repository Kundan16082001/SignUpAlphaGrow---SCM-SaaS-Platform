<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .header p { margin: 8px 0 0; opacity: 0.9; font-size: 14px; }
        .body { padding: 30px; }
        .greeting { font-size: 16px; color: #334155; margin-bottom: 20px; }
        .invoice-box { background: #f8fafc; border-left: 4px solid #6366f1; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .invoice-box .row { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .invoice-box .label { color: #64748b; font-size: 13px; }
        .invoice-box .value { color: #1e293b; font-weight: 600; font-size: 14px; }
        .total { font-size: 24px; color: #6366f1; font-weight: 700; }
        .btn { display: inline-block; background: #6366f1; color: white !important; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 15px; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoice {{ $invoice->invoice_no }}</h1>
            <p>Thank you for your business</p>
        </div>
        <div class="body">
            <p class="greeting">Hello {{ $invoice->customer->name ?? 'Valued Customer' }},</p>
            <p style="color: #475569; line-height: 1.6;">
                Please find your invoice details below. Kindly process the payment at your earliest convenience.
            </p>
            
            <div class="invoice-box">
                <div class="row">
                    <span class="label">Invoice Number</span>
                    <span class="value">{{ $invoice->invoice_no }}</span>
                </div>
                <div class="row">
                    <span class="label">Invoice Date</span>
                    <span class="value">{{ $invoice->created_at->format('d M Y') }}</span>
                </div>
                <div class="row">
                    <span class="label">Due Date</span>
                    <span class="value">{{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}</span>
                </div>
                <div class="row">
                    <span class="label">Status</span>
                    <span class="value" style="text-transform: uppercase; color: {{ $invoice->status === 'paid' ? '#10b981' : '#f59e0b' }};">
                        {{ $invoice->status }}
                    </span>
                </div>
                <hr style="border: none; border-top: 1px dashed #cbd5e1; margin: 15px 0;">
                <div class="row">
                    <span class="label" style="font-size: 15px;">Amount Due</span>
                    <span class="total">₹{{ number_format($invoice->total, 2) }}</span>
                </div>
            </div>

            <p style="color: #475569; font-size: 14px;">
                If you have any questions about this invoice, please don't hesitate to contact us.
            </p>
            
            <div style="text-align: center;">
                <a href="{{ url('/') }}" class="btn">View Invoice Online</a>
            </div>
        </div>
        <div class="footer">
            <p><strong>SignupAlphaGrow</strong> · This is an automated email, please do not reply.</p>
            <p>© {{ date('Y') }} SignupAlphaGrow. All rights reserved.</p>
        </div>
    </div>
</body>
</html>