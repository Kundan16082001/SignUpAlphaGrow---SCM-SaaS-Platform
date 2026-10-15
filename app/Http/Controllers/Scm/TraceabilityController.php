<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Product;
use App\Models\GrqrPosting;
use App\Models\WorkOrder;
use App\Models\Shipment;
use App\Models\Invoice;
use App\Models\WarehouseBin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TraceabilityController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        
        $query = Batch::with(['product', 'warehouseBin'])
            ->where('tenant_id', $tenantId);

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('batch_no', 'like', "%{$search}%")
                  ->orWhereHas('product', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $batches = $query->latest()->paginate(15);

        return view('scm.traceability.index', compact('batches'));
    }

    public function showJourney(Batch $batch)
    {
        if ($batch->tenant_id !== Auth::user()->tenant_id) abort(403);

        $tenantId = Auth::user()->tenant_id;
        $timeline = [];

        // 1. Goods Receipt (Inbound)
        $gr = GrqrPosting::where('tenant_id', $tenantId)
            ->where('batch_id', $batch->id)
            ->where('type', 'gr')
            ->first();
            
        if ($gr) {
            $timeline[] = [
                'step' => 1,
                'title' => 'Goods Receipt (Inbound)',
                'date' => $gr->created_at,
                'icon' => 'bi-box-arrow-in-down',
                'color' => 'success',
                'details' => [
                    'Received Qty' => $gr->received_qty . ' ' . ($batch->product->unit ?? 'Units'),
                    'QC Result' => ucfirst($gr->qc_result) . (isset($gr->qc_score) ? " (Score: {$gr->qc_score}%)" : ''),
                    'Reference' => $gr->posting_no,
                ]
            ];
        }

        // 2. Production / Work Order (If applicable)
        $wo = WorkOrder::where('tenant_id', $tenantId)
            ->where('product_id', $batch->product_id)
            ->whereIn('status', ['in_progress', 'completed'])
            ->whereDate('created_at', '<=', $batch->created_at) // Simplified logic
            ->first();

        if ($wo) {
            $timeline[] = [
                'step' => 2,
                'title' => 'Production / Assembly',
                'date' => $wo->start_date,
                'icon' => 'bi-gear-wide-connected',
                'color' => 'primary',
                'details' => [
                    'Work Order' => $wo->wo_no,
                    'Machine' => $wo->machine?->name ?? 'N/A',
                    'Completed Qty' => $wo->completed_qty . ' / ' . $wo->target_qty,
                ]
            ];
        }

        // 3. Warehousing (Current Location)
        if ($batch->warehouseBin) {
            $timeline[] = [
                'step' => 3,
                'title' => 'Warehousing',
                'date' => $batch->updated_at,
                'icon' => 'bi-building',
                'color' => 'info',
                'details' => [
                    'Location' => $batch->warehouseBin->zone . '-' . $batch->warehouseBin->aisle . '-' . $batch->warehouseBin->rack,
                    'Bin Type' => ucfirst($batch->warehouseBin->type),
                    'Current Stock' => $batch->current_qty . ' ' . ($batch->product->unit ?? 'Units'),
                ]
            ];
        }

        // 4. Dispatch / Shipment (Outbound)
        // Note: In a real app, you'd link batches to shipments via shipment_items. 
        // Here we simulate by finding shipments around the batch creation time for demo.
        $shipment = Shipment::where('tenant_id', $tenantId)
            ->where('direction', 'outbound')
            ->whereDate('created_at', '>=', $batch->created_at)
            ->first();

        if ($shipment) {
            $timeline[] = [
                'step' => 4,
                'title' => 'Dispatch / Shipment',
                'date' => $shipment->created_at,
                'icon' => 'bi-truck',
                'color' => 'warning',
                'details' => [
                    'Shipment No' => $shipment->shipment_no,
                    'Carrier' => $shipment->carrier?->name ?? 'N/A',
                    'Destination' => $shipment->destination,
                    'Status' => ucfirst(str_replace('_', ' ', $shipment->status)),
                ]
            ];
        }

        // 5. Final Delivery / Invoice
        $invoice = Invoice::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->whereDate('created_at', '>=', $batch->created_at)
            ->first();

        if ($invoice) {
            $timeline[] = [
                'step' => 5,
                'title' => 'Final Delivery / Sale',
                'date' => $invoice->created_at,
                'icon' => 'bi-check-circle-fill',
                'color' => 'success',
                'details' => [
                    'Invoice No' => $invoice->invoice_no,
                    'Customer' => $invoice->customer?->name ?? 'Walk-in Customer',
                    'Amount' => '₹' . number_format($invoice->total, 2),
                ]
            ];
        }

        // Sort timeline by date
        usort($timeline, function($a, $b) {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });

        return response()->json([
            'batch' => $batch->load('product'),
            'timeline' => $timeline
        ]);
    }
}