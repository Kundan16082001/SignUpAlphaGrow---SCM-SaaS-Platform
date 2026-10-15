<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;
        $users = User::where('tenant_id', $tenantId)->latest()->paginate(10);
        $roles = Role::where('tenant_id', $tenantId)->get();
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string',
            'wing' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'tenant_id' => Auth::user()->tenant_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'wing' => $request->wing,
        ]);

        return redirect()->back()->with('success', 'User created successfully!');
    }

    public function toggleStatus(User $user)
    {
        if ($user->tenant_id !== Auth::user()->tenant_id) abort(403);
        $user->update(['is_active' => !$user->is_active]); // Assuming you add is_active boolean to users table, or just use a status column
        return redirect()->back()->with('success', 'User status updated!');
    }
}