<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SignupController extends Controller
{
    /**
     * Show the signup form
     */
    public function showSignupForm()
    {
        // Check if an admin already exists in the database
        $hasAdmin = User::where('role', 'admin')->exists();
        
        return view('auth.signup', compact('hasAdmin'));
    }

    /**
     * Handle the signup registration
     */
    public function register(Request $request)
    {
        $hasAdmin = User::where('role', 'admin')->exists();

        // Base validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ];

        // Dynamic Role Validation based on Admin existence
        if ($hasAdmin) {
            // Admin exists: Restrict to non-admin roles only
            $rules['role'] = 'required|in:manager,superior,employee';
        } else {
            // No admin exists: Allow Admin creation (or default to it)
            $rules['role'] = 'required|in:admin,manager,superior,employee';
        }

        $request->validate($rules);

        // Create the user
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => 'active',
            // 'tenant_id' => null, // Optional: Leave null for the global Super Admin, or assign as needed
        ]);

        return redirect()->route('login')->with('success', 'Account created successfully! Please log in.');
    }
}