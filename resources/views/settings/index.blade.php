@extends('layouts.master')
@section('title', 'Settings')
@section('page-title', 'Account Settings')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Settings Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="settings-tab" role="tablist">
                        <a class="list-group-item list-group-item-action active border-0 py-3" id="profile-tab" data-bs-toggle="list" href="#profile" role="tab">
                            <i class="bi bi-person-circle me-2"></i> Profile Information
                        </a>
                        <a class="list-group-item list-group-item-action border-0 py-3" id="security-tab" data-bs-toggle="list" href="#security" role="tab">
                            <i class="bi bi-shield-lock me-2"></i> Password & Security
                        </a>
                        <a class="list-group-item list-group-item-action border-0 py-3" id="preferences-tab" data-bs-toggle="list" href="#preferences" role="tab">
                            <i class="bi bi-sliders me-2"></i> Preferences
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="col-md-9">
            <div class="tab-content" id="settings-tabContent">
                
                <!-- 1. Profile Tab -->
                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold">Profile Information</h5>
                            <small class="text-muted">Update your account's profile information and email address.</small>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" action="{{ route('settings.profile.update') }}">
                                @csrf
                                @method('PUT')
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Full Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email Address</label>
                                        <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
                                        @error('email')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-muted">Assigned Wing</label>
                                        <input type="text" class="form-control bg-light" value="{{ ucfirst(auth()->user()->wing) }}" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-muted">Role</label>
                                        <input type="text" class="form-control bg-light" value="{{ ucfirst(auth()->user()->role) }}" disabled>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 2. Security Tab -->
                <div class="tab-pane fade" id="security" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold">Update Password</h5>
                            <small class="text-muted">Ensure your account is using a long, random password to stay secure.</small>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" action="{{ route('settings.password.update') }}">
                                @csrf
                                @method('PUT')
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                    @error('current_password')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">New Password</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Confirm New Password</label>
                                        <input type="password" name="password_confirmation" class="form-control" required>
                                        @error('password')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary px-4">Update Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 3. Preferences Tab -->
                <div class="tab-pane fade" id="preferences" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold">Application Preferences</h5>
                            <small class="text-muted">Manage your UI and notification settings.</small>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">Appearance</h6>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="darkModeToggle">
                                    <label class="form-check-label" for="darkModeToggle">Enable Dark Mode (UI Preview)</label>
                                </div>
                                <small class="text-muted">Note: Full dark mode integration will be applied globally in the next UI update.</small>
                            </div>

                            <hr class="my-4">

                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">Notifications</h6>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" checked id="emailNotif">
                                    <label class="form-check-label" for="emailNotif">Email me about low stock alerts</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" checked id="orderNotif">
                                    <label class="form-check-label" for="orderNotif">Email me about new order assignments</label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-primary px-4" onclick="alert('Preferences saved locally!')">Save Preferences</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection