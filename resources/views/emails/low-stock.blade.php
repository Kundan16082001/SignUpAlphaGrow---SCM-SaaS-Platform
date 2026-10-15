<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; }
        .body { padding: 30px; }
        .alert-box { background: #fef2f2; border-left: 4px solid #ef4444; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .product-name { font-size: 20px; font-weight: 700; color: #1e293b; margin-bottom: 10px; }
        .stat { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #fecaca; }
        .btn { display: inline-block; background: #ef4444; color: white !important; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 15px; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Low Stock Alert</h1>
            <p style="margin: 8px 0 0; opacity: 0.9;">Immediate action required</p>
        </div>
        <div class="body">
            <p style="color: #475569;">A product in your inventory has fallen below the reorder level:</p>
            
            <div class="alert-box">
                <div class="product-name">{{ $product->name }}</div>
                <div class="stat">
                    <span style="color: #64748b;">SKU:</span>
                    <strong>{{ $product->sku }}</strong>
                </div>
                <div class="stat">
                    <span style="color: #64748b;">Current Stock:</span>
                    <strong style="color: #ef4444;">{{ $product->stock_qty }} {{ $product->unit }}</strong>
                </div>
                <div class="stat" style="border-bottom: none;">
                    <span style="color: #64748b;">Reorder Level:</span>
                    <strong>{{ $product->reorder_level }} {{ $product->unit }}</strong>
                </div>
            </div>

            <p style="color: #475569;">Please place a purchase order to replenish stock before it runs out.</p>
            
            <div style="text-align: center;">
                <a href="{{ url('/operations/inventory') }}" class="btn">View Inventory</a>
            </div>
        </div>
        <div class="footer">
            <p><strong>SignupAlphaGrow</strong> · Automated inventory alert</p>
        </div>
    </div>
</body>
</html>