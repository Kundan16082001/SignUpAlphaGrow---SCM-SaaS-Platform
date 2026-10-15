<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Models\{GrqrPosting, QualityCheck, Shipment, Batch, Movement};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GrqrController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        
        // Pending GR (awaiting posting)
        $pendingGr = GrqrPosting::with(['batch.product', 'shipment'])
            ->where('tenant_id', $tenantId)
            ->where('type', 'gr')
            ->where('status', 'pending')
            ->latest()
            ->paginate(10, ['*'], 'pending_page');

        // Posted GR/QR History
        $postedGrqr = GrqrPosting::with(['batch.product', 'shipment'])
            ->where('tenant_id', $tenantId)
            ->where('status', 'posted')
            ->latest()
            ->paginate(10, ['*'], 'posted_page');

        // For the "Create New" modal: Get active shipments with batches
        $availableShipments = Shipment::with(['batches.product'])
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['in_transit', 'arrived', 'pending'])
            ->latest()
            ->get();

        // Stats
        $stats = [
            'pending_gr' => GrqrPosting::where('tenant_id', $tenantId)->where('status', 'pending')->count(),
            'posted_gr' => GrqrPosting::where('tenant_id', $tenantId)->where('status', 'posted')->count(),
            'total_received' => GrqrPosting::where('tenant_id', $tenantId)->where('status', 'posted')->sum('received_qty'),
            'total_damaged' => GrqrPosting::where('tenant_id', $tenantId)->where('status', 'posted')->sum('damaged_qty'),
        ];

        return view('scm.grqr.index', compact('pendingGr', 'postedGrqr', 'availableShipments', 'stats'));
    }

    public function storePosting(Request $request)
    {
        $request->validate([
            'shipment_id' => 'required|exists:shipments,id',
            'batch_id' => 'required|exists:batches,id',
            'expected_qty' => 'required|integer|min:1',
            'received_qty' => 'required|integer|min:0',
            'damaged_qty' => 'required|integer|min:0',
            'qc_result' => 'required|in:pass,fail,pending',
            'qc_score' => 'nullable|numeric|min:0|max:100',
        ]);

        // ✅ Smart Validation: Prevent over-receiving
        if (($request->received_qty + $request->damaged_qty) > $request->expected_qty) {
            return back()->withErrors(['error' => 'Received + Damaged quantity cannot exceed Expected quantity.']);
        }

        $tenantId = Auth::user()->tenant_id;
        $postingNo = 'GR-' . now()->format('Ymd') . '-' . str_pad(GrqrPosting::count() + 1, 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            // 1. Create GR/QR Posting
            $posting = GrqrPosting::create([
                'tenant_id' => $tenantId,
                'posting_no' => $postingNo,
                'type' => 'gr',
                'shipment_id' => $request->shipment_id,
                'batch_id' => $request->batch_id,
                'expected_qty' => $request->expected_qty,
                'received_qty' => $request->received_qty,
                'damaged_qty' => $request->damaged_qty,
                'qc_result' => $request->qc_result,
                'qc_score' => $request->qc_score,
                'status' => 'posted',
            ]);

            // 2. Create Quality Check Record
            QualityCheck::create([
                'tenant_id' => $tenantId,
                'qc_no' => 'QC-' . now()->format('Ymd') . '-' . rand(100, 999),
                'batch_id' => $request->batch_id,
                'inspector_name' => Auth::user()->name,
                'sample_size' => $request->received_qty,
                'defect_count' => $request->damaged_qty,
                'qc_score' => $request->qc_score ?? 0,
                'result' => $request->qc_result,
                'parameters' => 'Visual, Weight, Dimensions',
            ]);

            // 3. Record Movement & Update Stock (if received > 0)
            if ($request->received_qty > 0) {
                $shipment = Shipment::find($request->shipment_id);
                Movement::create([
                    'tenant_id' => $tenantId,
                    'batch_id' => $request->batch_id,
                    'shipment_id' => $request->shipment_id,
                    'action' => 'received',
                    'from_location' => $shipment->origin ?? 'Transit',
                    'to_location' => 'Main Warehouse',
                    'from_wing' => $shipment->origin_wing ?? 'manufacturer',
                    'to_wing' => 'warehouse',
                    'qty' => $request->received_qty,
                    'actor_id' => Auth::id(),
                    'status' => 'completed',
                ]);

                $batch = Batch::find($request->batch_id);
                $batch->increment('current_qty', $request->received_qty);
            }

            // 4. Mark original pending record as posted (if it exists)
            GrqrPosting::where('tenant_id', $tenantId)
                ->where('shipment_id', $request->shipment_id)
                ->where('batch_id', $request->batch_id)
                ->where('status', 'pending')
                ->update(['status' => 'posted']);

            DB::commit();
            return redirect()->route('scm.gr-qr.index')->with('success', "GR/QR Posted successfully! Posting #: {$postingNo}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to post: ' . $e->getMessage()]);
        }
    }

    public function printPosting($id)
    {
        $posting = GrqrPosting::with(['batch.product', 'shipment'])->findOrFail($id);
        if ($posting->tenant_id !== Auth::user()->tenant_id) abort(403);
        
        return view('scm.grqr.print', compact('posting'));
    }
}