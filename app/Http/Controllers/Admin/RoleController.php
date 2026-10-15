<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    // Define all available permissions grouped by module
    public function getPermissionsMatrix()
    {
        return [
            'Dashboard' => [
                'view_dashboard' => 'View Main Dashboard',
            ],
            'Operations' => [
                'view_inventory' => 'View Inventory',
                'manage_inventory' => 'Add/Edit/Delete Inventory',
                'view_orders' => 'View Orders',
                'manage_orders' => 'Create/Edit Orders',
                'view_production' => 'View Production & BOM',
                'manage_production' => 'Manage Work Orders',
                'view_logistics' => 'View Logistics & Shipments',
            ],
            'Supply Chain (SCM)' => [
                'view_traceability' => 'View Traceability',
                'manage_grqr' => 'Post GR/QR Receipts',
                'manage_returns' => 'Manage Returns',
                'manage_warehouse' => 'Manage Warehouse Bins',
            ],
            'Finance & HR' => [
                'view_finance' => 'View Invoices & Finance',
                'manage_invoices' => 'Create/Edit Invoices',
                'use_pos' => 'Use Point of Sale (POS)',
                'view_procurement' => 'View Vendors & POs',
                'manage_expenses' => 'Approve/Manage Expenses',
                'view_hr' => 'View Employees & Leaves',
                'manage_payroll' => 'Process Payroll',
                'view_crm' => 'View CRM & Tickets',
            ],
            'Intelligence' => [
                'view_analytics' => 'View Analytics Charts',
                'view_bi' => 'View BI Dashboard',
                'view_reports' => 'Generate & Export Reports',
                'manage_dms' => 'Upload/Delete Documents',
            ],
            'Administration' => [
                'manage_company' => 'Edit Company Settings',
                'manage_users' => 'Invite/Manage Users',
                'manage_roles' => 'Edit Roles & Permissions',
                'view_audit' => 'View Audit Trail',
            ],
        ];
    }

    public function index()
    {
        $tenantId = Auth::user()->tenant_id;
        $roles = Role::where('tenant_id', $tenantId)->latest()->get();
        $permissionsMatrix = $this->getPermissionsMatrix();
        
        return view('admin.roles.index', compact('roles', 'permissionsMatrix'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        Role::create([
            'tenant_id' => Auth::user()->tenant_id,
            'name' => $request->name,
            'description' => $request->description,
            'permissions' => $request->permissions ?? [],
        ]);

        return redirect()->back()->with('success', 'Role created successfully!');
    }

    public function update(Request $request, Role $role)
    {
        if ($role->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        $role->update([
            'name' => $request->name,
            'description' => $request->description,
            'permissions' => $request->permissions ?? [], // If no permissions checked, save empty array
        ]);

        return redirect()->back()->with('success', 'Role permissions updated successfully!');
    }

    public function destroy(Role $role)
    {
        if ($role->tenant_id !== Auth::user()->tenant_id) abort(403);
        
        // Prevent deleting the Super Admin role
        if (strtolower($role->name) === 'super admin') {
            return back()->withErrors(['error' => 'Cannot delete the Super Admin role.']);
        }
        
        $role->delete();
        return redirect()->back()->with('success', 'Role deleted successfully!');
    }
}