<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Product Labels</title>
    <style>
        @page {
            margin: 5mm;
            size: A4;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 5mm;
            background: white;
        }
        
        .label-container {
            display: grid;
            gap: 4mm;
            page-break-inside: avoid;
        }
        
        /* Label Sizes */
        .label-small {
            grid-template-columns: repeat(4, 1fr);
        }
        
        .label-medium {
            grid-template-columns: repeat(3, 1fr);
        }
        
        .label-large {
            grid-template-columns: repeat(2, 1fr);
        }
        
        /* Base Label Styles */
        .label {
            border: 1.5px solid #000;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 2mm;
            overflow: hidden;
            position: relative;
        }
        
        .label-small .label {
            width: 50mm;
            height: 25mm;
            padding: 1.5mm;
        }
        
        .label-medium .label {
            width: 75mm;
            height: 35mm;
            padding: 2mm;
        }
        
        .label-large .label {
            width: 100mm;
            height: 50mm;
            padding: 3mm;
        }
        
        /* Label Header */
        .label-header {
            font-size: 7pt;
            font-weight: bold;
            text-align: center;
            border-bottom: 1px solid #ccc;
            padding-bottom: 0.5mm;
            margin-bottom: 0.5mm;
            flex-shrink: 0;
        }
        
        /* Product Name */
        .label-product {
            font-size: 6pt;
            text-align: center;
            margin-bottom: 0.5mm;
            line-height: 1.2;
            flex-shrink: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Barcode Container - THIS IS THE FIX */
        .label-barcode {
            text-align: center;
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 1mm 0;
            min-height: 0;
        }
        
        .label-barcode img,
        .label-barcode svg,
        .label-barcode canvas {
            max-width: 100% !important;
            max-height: 100% !important;
            width: auto !important;
            height: auto !important;
            display: block;
            margin: 0 auto;
        }
        
        /* SKU */
        .label-sku {
            font-size: 5pt;
            text-align: center;
            color: #666;
            flex-shrink: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Price */
        .label-price {
            font-size: 9pt;
            font-weight: bold;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 0.5mm;
            margin-top: 0.5mm;
            flex-shrink: 0;
        }
        
        /* Small Label Adjustments */
        .label-small .label-header {
            font-size: 6pt;
        }
        
        .label-small .label-product {
            font-size: 5pt;
        }
        
        .label-small .label-price {
            font-size: 8pt;
        }
        
        /* Large Label Adjustments */
        .label-large .label-header {
            font-size: 9pt;
        }
        
        .label-large .label-product {
            font-size: 8pt;
        }
        
        .label-large .label-price {
            font-size: 12pt;
        }
        
        .label-large .label-sku {
            font-size: 7pt;
        }
        
        /* Print Styles */
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                padding: 0;
            }
            
            .label {
                page-break-inside: avoid;
            }
        }
        
        /* Print Button */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .print-button button {
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            margin-left: 10px;
        }
        
        .print-button button:first-child {
            background: #0d6efd;
            color: white;
        }
        
        .print-button button:last-child {
            background: #6c757d;
            color: white;
        }
    </style>
</head>
<body>
    <div class="no-print print-button">
        <button onclick="window.print()">🖨️ Print Labels</button>
        <button onclick="window.close()">✖ Close</button>
    </div>

    <div class="label-container label-{{ $labelSize }}">
        @foreach($labels as $product)
            <div class="label">
                <div class="label-header">SignupAlphaGrow</div>
                <div class="label-product" title="{{ $product->name }}">{{ $product->name }}</div>
                <div class="label-barcode">
                    @if($barcodeType === 'code128')
                        {!! DNS1D::getBarcodeHTML($product->sku, 'C128', 1, 25, 'black', false) !!}
                    @else
                        {!! DNS2D::getBarcodeHTML($product->sku, 'QRCODE', 2, 2) !!}
                    @endif
                </div>
                <div class="label-sku" title="{{ $product->sku }}">{{ $product->sku }}</div>
                <div class="label-price">₹{{ number_format($product->price, 0) }}</div>
            </div>
        @endforeach
    </div>
</body>
</html>