<?php
namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Models\Movement;
use App\Models\Batch;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TraceabilityController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $query = Movement::with(['batch.product', 'shipment', 'actor'])
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('batch', function($q) use ($search) {
                $q->where('batch_no', 'like', "%{$search}%")
                  ->orWhereHas('product', function($pq) use ($search) {
                      $pq->where('sku', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            })->orWhereHas('shipment', function($q) use ($search) {
                $q->where('shipment_no', 'like', "%{$search}%");
            });
        }

        $movements = $query->paginate(15)->withQueryString();
        return view('scm.traceability.index', compact('movements'));
    }

    public function show(Batch $batch)
    {
        if ($batch->tenant_id !== Auth::user()->tenant_id) abort(403);
        
        $batch->load(['product', 'movements.shipment', 'movements.actor']);
        $timeline = $batch->movements->sortBy('created_at');
        
        return view('scm.traceability.show', compact('batch', 'timeline'));
    }
}