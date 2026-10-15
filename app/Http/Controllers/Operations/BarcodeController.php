<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Milon\Barcode\Facades\DNS1D;
use Milon\Barcode\Facades\DNS2D;

class BarcodeController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;
        $products = Product::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('operations.barcodes.index', compact('products'));
    }

        public function generate(Request $request)
    {
        $request->validate([
            'products' => 'required|array|min:1',
            'products.*' => 'exists:products,id',
            'label_size' => 'required|in:small,medium,large',
            'barcode_type' => 'required|in:code128,qr',
            'qty_per_product' => 'required|integer|min:1|max:100',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $products = Product::whereIn('id', $request->products)
            ->where('tenant_id', $tenantId)
            ->get();

        $labels = [];
        foreach ($products as $product) {
            for ($i = 0; $i < $request->qty_per_product; $i++) {
                $labels[] = $product;
            }
        }

        return view('operations.barcodes.print', [
            'labels' => $labels,
            'labelSize' => $request->label_size,
            'barcodeType' => $request->barcode_type,
        ]);
    }
}