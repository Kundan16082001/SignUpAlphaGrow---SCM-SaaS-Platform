<?php

namespace App\Http\Controllers\FinanceHr;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProcurementController extends Controller
{
    public function index(Request $request)
{
    $tenantId = Auth::user()->tenant_id;
    $tab = $request->get('tab', 'vendors');

    // ✅ FIX: Always initialize both variables
    $vendors = collect();
    $purchaseOrders = collect();

    if ($tab === 'vendors') {
        $vendors = Vendor::where('tenant_id', $tenantId)->latest()->paginate(10);
    } else {
        $purchaseOrders = PurchaseOrder::with('vendor')
            ->where('tenant_id', $tenantId)
            ->latest()
            ->paginate(10);
    }

    $stats = [
        'total_vendors' => Vendor::where('tenant_id', $tenantId)->count(),
        'active_vendors' => Vendor::where('tenant_id', $tenantId)->active()->count(),
        'total_pos' => PurchaseOrder::where('tenant_id', $tenantId)->count(),
        'pending_pos' => PurchaseOrder::where('tenant_id', $tenantId)->pending()->count(),
        'total_purchase_value' => PurchaseOrder::where('tenant_id', $tenantId)->sum('total'),
    ];

    return view('finance-hr.procurement.index', compact('vendors', 'purchaseOrders', 'stats', 'tab'));
}

    public function storeVendor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'gstin' => 'nullable|string|size:15',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $vendorCode = 'VND-' . strtoupper(substr($request->name, 0, 3)) . rand(100, 999);

        Vendor::create([
            'tenant_id' => $tenantId,
            'vendor_code' => $vendorCode,
            'name' => $request->name,
            'gstin' => $request->gstin,
            'contact_person' => $request->contact_person,
            'phone' => $request->phone,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'ifsc_code' => $request->ifsc_code,
            'credit_limit' => $request->credit_limit ?? 0,
            'rating' => $request->rating ?? 5.00,
            'status' => 'active',
        ]);

        return redirect()->route('finance-hr.procurement.index', ['tab' => 'vendors'])->with('success', 'Vendor added successfully!');
    }

    public function storePurchaseOrder(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'po_date' => 'required|date',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $poNo = 'PO-' . now()->format('Ymd') . '-' . rand(1000, 9999);

        PurchaseOrder::create([
            'tenant_id' => $tenantId,
            'po_no' => $poNo,
            'vendor_id' => $request->vendor_id,
            'po_date' => $request->po_date,
            'subtotal' => $request->subtotal,
            'tax' => $request->tax,
            'total' => $request->subtotal + $request->tax,
            'status' => 'draft',
        ]);

        return redirect()->route('finance-hr.procurement.index', ['tab' => 'pos'])->with('success', 'Purchase Order created!');
    }

    public function updatePOStatus(Request $request, PurchaseOrder $po)
    {
        if ($po->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate(['status' => 'required|in:draft,approved,received,cancelled']);
        $po->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'PO status updated!');
    }
}