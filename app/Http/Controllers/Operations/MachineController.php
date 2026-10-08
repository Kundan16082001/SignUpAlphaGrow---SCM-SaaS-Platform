<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MachineController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $query = Machine::where('tenant_id', $tenantId);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('machine_code', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Type Filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $machines = $query->latest()->paginate(10)->withQueryString();
        $types = Machine::where('tenant_id', $tenantId)->distinct()->pluck('type')->filter();

        // Stats
        $stats = [
            'total' => Machine::where('tenant_id', $tenantId)->count(),
            'operational' => Machine::where('tenant_id', $tenantId)->where('status', 'operational')->count(),
            'maintenance' => Machine::where('tenant_id', $tenantId)->where('status', 'maintenance')->count(),
            'breakdown' => Machine::where('tenant_id', $tenantId)->where('status', 'breakdown')->count(),
            'idle' => Machine::where('tenant_id', $tenantId)->where('status', 'idle')->count(),
            'due_maintenance' => Machine::where('tenant_id', $tenantId)
                ->where('next_maintenance', '<=', now()->addDays(7))
                ->where('next_maintenance', '>=', now())
                ->count(),
            'avg_efficiency' => round(Machine::where('tenant_id', $tenantId)
                ->where('status', 'operational')
                ->avg('efficiency'), 1),
        ];

        return view('operations.machines.index', compact('machines', 'types', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'machine_code' => 'required|string|max:50|unique:machines,machine_code',
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'capacity' => 'nullable|integer|min:0',
            'efficiency' => 'nullable|numeric|min:0|max:100',
            'next_maintenance' => 'nullable|date|after_or_equal:today',
        ]);

        Machine::create([
            'tenant_id' => Auth::user()->tenant_id,
            'machine_code' => strtoupper($request->machine_code),
            'name' => $request->name,
            'type' => $request->type,
            'status' => 'operational',
            'efficiency' => $request->efficiency ?? 100,
            'next_maintenance' => $request->next_maintenance,
            'notes' => $request->notes,
        ]);

        return redirect()->route('operations.machines.index')->with('success', 'Machine added successfully!');
    }

    public function update(Request $request, Machine $machine)
    {
        if ($machine->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate([
            'machine_code' => 'required|string|max:50|unique:machines,machine_code,' . $machine->id,
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'status' => 'required|in:operational,maintenance,breakdown,idle',
            'efficiency' => 'nullable|numeric|min:0|max:100',
            'next_maintenance' => 'nullable|date',
        ]);

        $machine->update([
            'machine_code' => strtoupper($request->machine_code),
            'name' => $request->name,
            'type' => $request->type,
            'status' => $request->status,
            'efficiency' => $request->efficiency ?? $machine->efficiency,
            'next_maintenance' => $request->next_maintenance,
            'notes' => $request->notes,
        ]);

        return redirect()->route('operations.machines.index')->with('success', 'Machine updated successfully!');
    }

    public function destroy(Machine $machine)
    {
        if ($machine->tenant_id !== Auth::user()->tenant_id) abort(403);

        // Check if machine has active work orders
        if ($machine->workOrders()->whereNotIn('status', ['completed', 'cancelled'])->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete machine with active work orders.']);
        }

        $machine->delete();
        return redirect()->route('operations.machines.index')->with('success', 'Machine deleted successfully!');
    }
}