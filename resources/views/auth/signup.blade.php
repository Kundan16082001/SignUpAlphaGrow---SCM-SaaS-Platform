@extends('layouts.master')
@section('title', 'Sign Up')
@section('page-title', '')

@section('content')
<div class="signup-wrapper">
    <div class="signup-container">
        
        {{-- Header --}}
        <div class="signup-header text-center mb-4">
            <div class="logo-mark mx-auto mb-3">
                <i class="bi bi-person-plus-fill"></i>
            </div>
            <h2>Create Your Account</h2>
            <p class="text-muted">Join the SignupAlphaGrow workspace.</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('signup.register') }}" class="signup-form">
            @csrf

            <div class="form-group mb-3">
                <label class="form-label fw-semibold">Full Name</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="John Doe" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group mb-3">
                <label class="form-label fw-semibold">Email Address</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="you@company.com" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group mb-3">
                <label class="form-label fw-semibold">Account Role</label>
                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                    @if(!$hasAdmin)
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator (First User)</option>
                    @endif
                    <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="superior" {{ old('role') == 'superior' ? 'selected' : '' }}>Superior</option>
                    <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>Employee</option>
                </select>
                
                {{-- Dynamic Helper Text --}}
                @if($hasAdmin)
                    <small class="text-muted mt-1 d-block">
                        <i class="bi bi-info-circle me-1"></i> An administrator already exists. You can only register as a non-admin role.
                    </small>
                @else
                    <small class="text-success mt-1 d-block">
                        <i class="bi bi-shield-check me-1"></i> You are the first user! You will be registered as the System Administrator.
                    </small>
                @endif
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group mb-4">
                <label class="form-label fw-semibold">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-primary w-100 mb-3">
                Create Account <i class="bi bi-arrow-right ms-1"></i>
            </button>

            <div class="text-center">
                <p class="text-muted mb-0">Already have an account? <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Log in here</a></p>
            </div>
        </form>
    </div>
</div>

<style>
    /* Reuse the beautiful emerald theme from your setup page */
    .signup-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        background: linear-gradient(135deg, #064e3b 0%, #059669 50%, #10b981 100%);
        font-family: 'Inter', -apple-system, sans-serif;
    }

    .signup-container {
        background: #ffffff;
        width: 100%;
        max-width: 480px;
        border-radius: 24px;
        padding: 2.5rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .logo-mark {
        width: 64px; height: 64px; background: #ecfdf5; color: #10b981;
        border-radius: 16px; display: flex; align-items: center;
        justify-content: center; font-size: 1.75rem;
    }

    .signup-header h2 { font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem; }
    
    .form-label { font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem; }
    
    .form-control, .form-select {
        padding: 0.75rem 1rem; border: 1.5px solid #e5e7eb; border-radius: 10px;
        font-size: 0.95rem; color: #111827; background: #f9fafb;
        transition: all 0.2s ease; outline: none;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #10b981; background: #ffffff; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
    }

    .btn-primary {
        background: #10b981; color: #ffffff; border: none; padding: 0.85rem 1.5rem;
        border-radius: 10px; font-size: 0.95rem; font-weight: 600; cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
        transition: all 0.2s ease; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
    }

    .btn-primary:hover {
        background: #059669; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
    }
</style>
@endsection