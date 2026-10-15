@extends('layouts.master')
@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- ==================== LEFT: PROFILE SETTINGS ==================== --}}
        <div class="col-lg-8">
            
            {{-- Profile Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-person-circle me-2 text-primary"></i>Profile Information</h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('settings.profile.update') }}">
                        @csrf @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Full Name</label>
                                <input type="text" name="name" class="form-control" value="{{ auth()->user()->name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" name="email" class="form-control" value="{{ auth()->user()->email }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="{{ auth()->user()->phone ?? '' }}" placeholder="+91 98765 43210">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Role</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->role }}" readonly disabled>
                                <small class="text-muted">Contact admin to change role</small>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Update Profile
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Change Password --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-shield-lock me-2 text-warning"></i>Change Password</h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('settings.password.update') }}">
                        @csrf @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">New Password</label>
                                <input type="password" name="password" class="form-control" required minlength="8">
                                <small class="text-muted">Minimum 8 characters</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-key me-1"></i> Update Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Company Information (Read-only) --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-building me-2 text-info"></i>Company Information</h6>
                </div>
                <div class="card-body p-4">
                    @php
                        $company = \App\Models\CompanySetting::where('tenant_id', auth()->user()->tenant_id)->first();
                    @endphp
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted small">Company Name</label>
                            <div class="fw-semibold">{{ $company->company_name ?? 'SignupAlphaGrow' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted small">GSTIN / Tax ID</label>
                            <div class="fw-semibold">{{ $company->tax_id ?? 'Not set' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted small">Phone</label>
                            <div class="fw-semibold">{{ $company->phone ?? 'Not set' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted small">Currency</label>
                            <div class="fw-semibold">{{ strtoupper($company->currency ?? 'INR') }}</div>
                        </div>
                        <div class="col-12">
                            <a href="{{ route('admin.company.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil me-1"></i> Edit Company Details (Admin Only)
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== RIGHT: QUICK ACTIONS & BACKUP ==================== --}}
        <div class="col-lg-4">
            
            {{-- User Avatar Card --}}
            <div class="card border-0 shadow-sm mb-4 text-center">
                <div class="card-body p-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow" 
                         style="width: 80px; height: 80px; font-size: 2rem; font-weight: 700;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <h5 class="fw-bold mb-1">{{ auth()->user()->name }}</h5>
                    <p class="text-muted small mb-2">{{ auth()->user()->email }}</p>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 text-uppercase">
                        {{ auth()->user()->role }} · {{ auth()->user()->wing }} Wing
                    </span>
                    <div class="mt-3 pt-3 border-top">
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i>
                            Member since {{ auth()->user()->created_at->format('M Y') }}
                        </small>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-lightning-charge me-2 text-warning"></i>Quick Actions</h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-grid gap-2">
                        {{-- ✅ BACKUP & RESTORE --}}
                        <a href="{{ route('admin.backups.index') }}" class="btn btn-outline-primary text-start d-flex align-items-center gap-3 py-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" 
                                 style="width: 44px; height: 44px; flex-shrink: 0;">
                                <i class="bi bi-database-check fs-5"></i>
                            </div>
                            <div class="text-start">
                                <div class="fw-semibold small">Backup & Restore</div>
                                <small class="text-muted" style="font-size: 0.7rem;">Manage database backups</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted ms-auto"></i>
                        </a>

                        {{-- Company Setup --}}
                        <a href="{{ route('admin.company.index') }}" class="btn btn-outline-info text-start d-flex align-items-center gap-3 py-3">
                            <div class="bg-info bg-opacity-10 text-info rounded-3 d-flex align-items-center justify-content-center" 
                                 style="width: 44px; height: 44px; flex-shrink: 0;">
                                <i class="bi bi-building-gear fs-5"></i>
                            </div>
                            <div class="text-start">
                                <div class="fw-semibold small">Company Setup</div>
                                <small class="text-muted" style="font-size: 0.7rem;">Update company details</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted ms-auto"></i>
                        </a>

                        {{-- User Management --}}
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-success text-start d-flex align-items-center gap-3 py-3">
                            <div class="bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center" 
                                 style="width: 44px; height: 44px; flex-shrink: 0;">
                                <i class="bi bi-people fs-5"></i>
                            </div>
                            <div class="text-start">
                                <div class="fw-semibold small">User Management</div>
                                <small class="text-muted" style="font-size: 0.7rem;">Manage team members</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted ms-auto"></i>
                        </a>

                        {{-- Roles & Permissions --}}
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-warning text-start d-flex align-items-center gap-3 py-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-3 d-flex align-items-center justify-content-center" 
                                 style="width: 44px; height: 44px; flex-shrink: 0;">
                                <i class="bi bi-shield-lock fs-5"></i>
                            </div>
                            <div class="text-start">
                                <div class="fw-semibold small">Roles & Permissions</div>
                                <small class="text-muted" style="font-size: 0.7rem;">Configure access levels</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted ms-auto"></i>
                        </a>

                        {{-- Audit Trail --}}
                        <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-secondary text-start d-flex align-items-center gap-3 py-3">
                            <div class="bg-secondary bg-opacity-10 text-secondary rounded-3 d-flex align-items-center justify-content-center" 
                                 style="width: 44px; height: 44px; flex-shrink: 0;">
                                <i class="bi bi-journal-text fs-5"></i>
                            </div>
                            <div class="text-start">
                                <div class="fw-semibold small">Audit Trail</div>
                                <small class="text-muted" style="font-size: 0.7rem;">View system activity logs</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted ms-auto"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- System Info --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-2 text-muted"></i>System Info</h6>
                </div>
                <div class="card-body p-3">
                    <ul class="list-unstyled mb-0 small">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">App Version</span>
                            <span class="fw-semibold">v1.0.0</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">PHP Version</span>
                            <span class="fw-semibold">{{ phpversion() }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Laravel Version</span>
                            <span class="fw-semibold">{{ app()->version() }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span class="text-muted">Server Time</span>
                            <span class="fw-semibold">{{ now()->format('d M Y, h:i A') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection