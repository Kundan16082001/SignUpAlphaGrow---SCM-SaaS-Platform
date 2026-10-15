<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\{Warehouse, WarehouseTransfer, WarehouseTransferItem, Batch, Product};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WarehouseTransferController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $query = WarehouseTransfer::with(['sourceWarehouse', 'destinationWarehouse', 'user'])
            ->where('tenant_id', $tenantId);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $transfers = $query->latest()->paginate(15);
        $warehouses = Warehouse::where('tenant_id', $tenantId)->where('status', 'active')->get();

        return view('operations.warehouse-transfers.index', compact('transfers', 'warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id|different:source_warehouse_id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_id' => 'nullable|exists:batches,id',
            'items.*.qty' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $tenantId = Auth::user()->tenant_id;

        DB::beginTransaction();
        try {
            // 1. Validate Stock & Deduct from Source
            foreach ($request->items as $item) {
                $batch = Batch::where('id', $item['batch_id'])
                    ->where('tenant_id', $tenantId)
                    ->where('warehouse_id', $request->source_warehouse_id)
                    ->firstOrFail();

                if ($batch->current_qty < $item['qty']) {
                    throw new \Exception("Insufficient stock for {$batch->product->name} in source warehouse. Available: {$batch->current_qty}");
                }

                // Deduct from source batch
                $batch->decrement('current_qty', $item['qty']);
            }

            // 2. Create Transfer Record
            $transferNo = 'TRF-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            
            $transfer = WarehouseTransfer::create([
                'tenant_id' => $tenantId,
                'transfer_no' => $transferNo,
                'source_warehouse_id' => $request->source_warehouse_id,
                'destination_warehouse_id' => $request->destination_warehouse_id,
                'user_id' => Auth::id(),
                'status' => 'in_transit', // Auto-approve on creation for simplicity, or 'pending'
                'notes' => $request->notes,
            ]);

            // 3. Create Transfer Items
            foreach ($request->items as $item) {
                WarehouseTransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'],
                    'qty' => $item['qty'],
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', "Transfer {$transferNo} created and stock deducted from source.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function receive(WarehouseTransfer $transfer)
    {
        if ($transfer->tenant_id !== Auth::user()->tenant_id) abort(403);
        if ($transfer->status !== 'in_transit') {
            return back()->withErrors(['error' => 'Only in-transit transfers can be received.']);
        }

        DB::beginTransaction();
        try {
            foreach ($transfer->items as $item) {
                // Find or create batch at destination
                $destBatch = Batch::where('tenant_id', $transfer->tenant_id)
                    ->where('product_id', $item->product_id)
                    ->where('batch_no', $item->batch?->batch_no) // Match by batch number if it exists
                    ->where('warehouse_id', $transfer->destination_warehouse_id)
                    ->first();

                if ($destBatch) {
                    $destBatch->increment('current_qty', $item->qty);
                } else {
                    // Create new batch record at destination
                    Batch::create([
                        'tenant_id' => $transfer->tenant_id,
                        'product_id' => $item->product_id,
                        'warehouse_id' => $transfer->destination_warehouse_id,
                        'batch_no' => $item->batch?->batch_no ?? ('TRF-' . $transfer->id . '-' . $item->product_id),
                        'mfg_date' => $item->batch?->mfg_date ?? now(),
                        'exp_date' => $item->batch?->exp_date ?? now()->addYears(2),
                        'initial_qty' => $item->qty,
                        'current_qty' => $item->qty,
                        'status' => 'active',
                    ]);
                }
            }

            $transfer->update(['status' => 'completed']);

        \App\Services\NotificationService::transferCompleted(
            $transfer->transfer_no,
            $transfer->sourceWarehouse->name,
            $transfer->destinationWarehouse->name
        );

            DB::commit();

            return redirect()->back()->with('success', "Transfer {$transfer->transfer_no} received successfully. Stock added to destination.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to receive transfer: ' . $e->getMessage()]);
        }
    }

    public function getBatches(Request $request)
    {
        $request->validate(['warehouse_id' => 'required|exists:warehouses,id', 'product_id' => 'required|exists:products,id']);
        
        $batches = Batch::where('warehouse_id', $request->warehouse_id)
            ->where('product_id', $request->product_id)
            ->where('current_qty', '>', 0)
            ->select('id', 'batch_no', 'current_qty', 'exp_date')
            ->get();

        return response()->json(['success' => true, 'batches' => $batches]);
    }
}