<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Goods Receipt {{ $posting->posting_no }}</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #0d6efd; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0 0 5px 0; color: #0d6efd; font-size: 22px; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .meta-box { background: #f8f9fa; padding: 15px; border-radius: 6px; width: 48%; }
        .meta-box h3 { margin: 0 0 10px 0; font-size: 14px; color: #0d6efd; text-transform: uppercase; }
        .meta-box p { margin: 4px 0; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #0d6efd; color: white; padding: 10px; text-align: left; font-size: 11px; text-transform: uppercase; }
        td { padding: 10px; border-bottom: 1px solid #dee2e6; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals { width: 40%; margin-left: auto; margin-top: 20px; }
        .totals-row { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px dashed #ccc; }
        .totals-row.grand { font-size: 16px; font-weight: bold; color: #0d6efd; border-top: 2px solid #333; border-bottom: none; padding-top: 10px; margin-top: 5px; }
        .footer { text-align: center; margin-top: 40px; padding-top: 15px; border-top: 1px solid #dee2e6; color: #6c757d; font-size: 10px; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-pass { background: #d1e7dd; color: #0f5132; }
        .badge-fail { background: #f8d7da; color: #842029; }
        .badge-pending { background: #fff3cd; color: #664d03; }
        @media print { .no-print { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0d6efd; color: white; border: none; border-radius: 4px; cursor: pointer;">🖨️ Print Receipt</button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">✖ Close</button>
    </div>

    <div class="header">
        <h1>GOODS RECEIPT (GR)</h1>
        <p>SignupAlphaGrow Supply Chain Management</p>
    </div>

    <div class="meta">
        <div class="meta-box">
            <h3>Receipt Details</h3>
            <p><strong>Posting #:</strong> {{ $posting->posting_no }}</p>
            <p><strong>Date:</strong> {{ $posting->created_at->format('d M Y, h:i A') }}</p>
            <p><strong>Inspector:</strong> {{ Auth::user()->name }}</p>
        </div>
        <div class="meta-box">
            <h3>Shipment Info</h3>
            <p><strong>Shipment #:</strong> {{ $posting->shipment->shipment_no ?? 'N/A' }}</p>
            <p><strong>Route:</strong> {{ $posting->shipment->origin ?? 'Unknown' }} → {{ $posting->shipment->destination ?? 'Main Warehouse' }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Batch No</th>
                <th>Product</th>
                <th class="text-center">Expected</th>
                <th class="text-center">Received</th>
                <th class="text-center">Damaged</th>
                <th class="text-center">QC Result</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="fw-bold">{{ $posting->batch->batch_no }}</td>
                <td>{{ $posting->batch->product->name ?? 'Unknown Product' }}<br><small style="color:#6c757d;">{{ $posting->batch->product->sku ?? '' }}</small></td>
                <td class="text-center">{{ $posting->expected_qty }}</td>
                <td class="text-center fw-bold text-success">{{ $posting->received_qty }}</td>
                <td class="text-center fw-bold text-danger">{{ $posting->damaged_qty }}</td>
                <td class="text-center">
                    <span class="badge badge-{{ $posting->qc_result }}">{{ strtoupper($posting->qc_result) }}</span>
                    @if($posting->qc_score)
                        <br><small>{{ $posting->qc_score }}%</small>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>This is a system-generated Goods Receipt document.</p>
        <p>© {{ date('Y') }} SignupAlphaGrow. All rights reserved.</p>
    </div>
</body>
</html>