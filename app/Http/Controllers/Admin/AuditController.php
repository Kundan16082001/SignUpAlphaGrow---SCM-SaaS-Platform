<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $query = AuditLog::with('user')->where('tenant_id', $tenantId);

        if ($request->filled('action')) $query->where('action', $request->action);
        if ($request->filled('user_id')) $query->where('user_id', $request->user_id);

        $logs = $query->latest()->paginate(15);
        $users = \App\Models\User::where('tenant_id', $tenantId)->get();
        $actions = ['created', 'updated', 'deleted', 'login', 'logout'];

        return view('admin.audit.index', compact('logs', 'users', 'actions'));
    }
}