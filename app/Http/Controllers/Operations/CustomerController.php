<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $query = Customer::where('tenant_id', $tenantId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('gstin', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $customers = $query->latest()->paginate(10)->withQueryString();

        return view('operations.customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'gstin' => 'nullable|string|size:15',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'credit_limit' => 'required|numeric|min:0',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $customerCode = 'CUST-' . strtoupper(substr($request->name, 0, 3)) . rand(100, 999);

        Customer::create([
            'tenant_id' => $tenantId,
            'customer_code' => $customerCode,
            'name' => $request->name,
            'gstin' => $request->gstin,
            'phone' => $request->phone,
            'email' => $request->email,
            'credit_limit' => $request->credit_limit,
            'outstanding_amount' => 0,
            'status' => 'active',
        ]);

        return redirect()->route('operations.customers.index')->with('success', 'Customer added successfully!');
    }

    public function update(Request $request, Customer $customer)
    {
        if ($customer->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'gstin' => 'nullable|string|size:15',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'credit_limit' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $customer->update($request->only(['name', 'gstin', 'phone', 'email', 'credit_limit', 'status']));

        return redirect()->route('operations.customers.index')->with('success', 'Customer updated successfully!');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->tenant_id !== Auth::user()->tenant_id) abort(403);
        
        // Optional: Check if customer has orders before deleting
        if ($customer->orders()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete customer with existing orders.']);
        }

        $customer->delete();
        return redirect()->route('operations.customers.index')->with('success', 'Customer deleted successfully!');
    }
}