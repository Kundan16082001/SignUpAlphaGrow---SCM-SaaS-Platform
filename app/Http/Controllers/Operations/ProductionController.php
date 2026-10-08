<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\Machine;
use App\Models\Bom;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductionController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        // Machine stats
        $machineStats = [
            'total' => Machine::where('tenant_id', $tenantId)->count(),
            'operational' => Machine::where('tenant_id', $tenantId)->where('status', 'operational')->count(),
            'maintenance' => Machine::where('tenant_id', $tenantId)->where('status', 'maintenance')->count(),
            'breakdown' => Machine::where('tenant_id', $tenantId)->where('status', 'breakdown')->count(),
            'idle' => Machine::where('tenant_id', $tenantId)->where('status', 'idle')->count(),
        ];

        // Work order stats
        $woStats = [
            'total' => WorkOrder::where('tenant_id', $tenantId)->count(),
            'in_progress' => WorkOrder::where('tenant_id', $tenantId)->where('status', 'in_progress')->count(),
            'overdue' => WorkOrder::where('tenant_id', $tenantId)->overdue()->count(),
            'due_soon' => WorkOrder::where('tenant_id', $tenantId)->dueSoon()->count(),
        ];

        // Work orders query
        $query = WorkOrder::with(['product', 'bom', 'machine'])->where('tenant_id', $tenantId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('wo_no', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%");
            });
        }

        $workOrders = $query->orderByRaw("FIELD(priority, 1, 2, 3)")
                            ->orderBy('deadline', 'asc')
                            ->paginate(10)
                            ->withQueryString();

        $machines = Machine::where('tenant_id', $tenantId)->get();
        $boms = Bom::where('tenant_id', $tenantId)->where('status', 'active')->with('components')->get();
        $products = Product::where('tenant_id', $tenantId)->where('status', 'active')->get();

        return view('operations.production.index', compact(
            'machineStats', 'woStats', 'workOrders', 'machines', 'boms', 'products'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'bom_id' => 'nullable|exists:boms,id',
            'machine_id' => 'nullable|exists:machines,id',
            'target_qty' => 'required|integer|min:1',
            'deadline' => 'required|date|after_or_equal:today',
            'priority' => 'required|in:1,2,3',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $product = Product::find($request->product_id);

        $woNo = 'WO-' . now()->format('Ymd') . '-' . rand(1000, 9999);

        WorkOrder::create([
            'tenant_id' => $tenantId,
            'wo_no' => $woNo,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'bom_id' => $request->bom_id,
            'machine_id' => $request->machine_id,
            'target_qty' => $request->target_qty,
            'status' => 'planned',
            'start_date' => now()->toDateString(),
            'deadline' => $request->deadline,
            'priority' => $request->priority,
            'notes' => $request->notes,
        ]);

        return redirect()->route('operations.production.index')->with('success', 'Work order created successfully!');
    }

    public function updateProgress(Request $request, WorkOrder $workOrder)
    {
        if ($workOrder->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate([
            'completed_qty' => 'required|integer|min:0|max:' . $workOrder->target_qty,
            'scrap_qty' => 'required|integer|min:0',
            'status' => 'required|in:planned,in_progress,completed,on_hold,cancelled',
        ]);

        $workOrder->update([
            'completed_qty' => $request->completed_qty,
            'scrap_qty' => $request->scrap_qty,
            'status' => $request->status,
        ]);

        return redirect()->route('operations.production.index')->with('success', 'Progress updated!');
    }

    public function showBomDetails(Bom $bom)
    {
        if ($bom->tenant_id !== Auth::user()->tenant_id) abort(403);
        $bom->load('components', 'product');
        return response()->json($bom);
    }
}