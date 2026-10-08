<?php
namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Models\GrqrPosting;
use App\Models\QualityCheck;
use App\Models\Shipment;
use App\Models\Batch;
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

    // ✅ NEW: Posted GR/QR History
    $postedGrqr = GrqrPosting::with(['batch.product', 'shipment'])
        ->where('tenant_id', $tenantId)
        ->where('status', 'posted')
        ->latest()
        ->paginate(10, ['*'], 'posted_page');

    // Recent QC checks
    $recentQc = QualityCheck::with('batch.product')
        ->where('tenant_id', $tenantId)
        ->latest()
        ->take(5)
        ->get();

    // Stats
    $stats = [
        'pending_gr' => GrqrPosting::where('tenant_id', $tenantId)->where('status', 'pending')->count(),
        'posted_gr' => GrqrPosting::where('tenant_id', $tenantId)->where('status', 'posted')->count(),
        'passed_qc' => QualityCheck::where('tenant_id', $tenantId)->where('result', 'pass')->count(),
        'failed_qc' => QualityCheck::where('tenant_id', $tenantId)->where('result', 'fail')->count(),
        'total_received' => GrqrPosting::where('tenant_id', $tenantId)->where('status', 'posted')->sum('received_qty'),
        'total_damaged' => GrqrPosting::where('tenant_id', $tenantId)->where('status', 'posted')->sum('damaged_qty'),
    ];

    return view('scm.grqr.index', compact('pendingGr', 'postedGrqr', 'recentQc', 'stats'));
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

        $tenantId = Auth::user()->tenant_id;
        $postingNo = 'GRQR-' . now()->format('Ymd') . '-' . rand(1000, 9999);

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

            // 3. Record Movement
            $shipment = Shipment::find($request->shipment_id);
            \App\Models\Movement::create([
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

            // 4. Update Batch Qty
            $batch = Batch::find($request->batch_id);
            $batch->increment('current_qty', $request->received_qty - $request->damaged_qty);

            DB::commit();
            return redirect()->route('scm.gr-qr.index')->with('success', 'GR/QR Posted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to post: ' . $e->getMessage()]);
        }
    }
}