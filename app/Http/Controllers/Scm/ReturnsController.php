<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Models\ReturnOrder;
use App\Models\ReturnItem;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturnsController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $query = ReturnOrder::with(['order', 'items.product'])->where('tenant_id', $tenantId);

        // Tab filter
        $tab = $request->get('tab', 'active');
        if ($tab === 'active') {
            $query->active();
        } elseif ($tab === 'completed') {
            $query->whereIn('status', ['completed', 'rejected']);
        }

        // Reason filter
        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }

        $returns = $query->latest()->paginate(10)->withQueryString();
        $products = Product::where('tenant_id', $tenantId)->where('status', 'active')->get();

        // Stats
        $stats = [
            'active' => ReturnOrder::where('tenant_id', $tenantId)->active()->count(),
            'completed' => ReturnOrder::where('tenant_id', $tenantId)->whereIn('status', ['completed', 'rejected'])->count(),
            'total_value_at_risk' => ReturnOrder::where('tenant_id', $tenantId)->active()->sum('value'),
            'total_refund_pending' => ReturnOrder::where('tenant_id', $tenantId)->whereIn('status', ['approved', 'received', 'inspected'])->sum('refund_amount'),
        ];

        // Reason breakdown
        $reasons = ['Defect', 'QC Fail', 'Damage', 'Recall', 'Expired'];
        $reasonCounts = [];
        foreach ($reasons as $reason) {
            $reasonCounts[$reason] = ReturnOrder::where('tenant_id', $tenantId)->where('reason', $reason)->count();
        }

        return view('scm.returns.index', compact('returns', 'products', 'stats', 'reasons', 'reasonCounts', 'tab'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string',
            'direction' => 'required|string',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.condition' => 'required|in:good,damaged,defective,expired',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $returnNo = 'RET-' . now()->format('Ymd') . '-' . rand(1000, 9999);

        DB::beginTransaction();
        try {
            $totalQty = 0;
            $totalValue = 0;

            $return = ReturnOrder::create([
                'tenant_id' => $tenantId,
                'return_no' => $returnNo,
                'reason' => $request->reason,
                'direction' => $request->direction,
                'origin' => $request->origin,
                'destination' => $request->destination,
                'status' => 'initiated',
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $lineTotal = $item['qty'] * $item['unit_price'];
                $totalQty += $item['qty'];
                $totalValue += $lineTotal;

                ReturnItem::create([
                    'return_id' => $return->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'refund_amount' => $lineTotal,
                    'condition' => $item['condition'],
                ]);
            }

            $return->update([
                'qty' => $totalQty,
                'value' => $totalValue,
                'refund_amount' => $totalValue,
            ]);

            DB::commit();
            return redirect()->route('scm.returns.index')->with('success', 'Return initiated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, ReturnOrder $return)
    {
        if ($return->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate(['status' => 'required|in:initiated,in_transit,received,inspected,approved,rejected,completed']);
        $return->update(['status' => $request->status]);

        return redirect()->route('scm.returns.index', ['tab' => 'active'])->with('success', 'Return status updated!');
    }
}