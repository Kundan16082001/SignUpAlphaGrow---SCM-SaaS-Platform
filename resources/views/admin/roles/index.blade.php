@extends('layouts.master')
@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <style>
        .role-card {
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 16px;
            transition: all 0.3s ease;
            height: 100%;
        }
        .role-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -8px rgba(0,0,0,0.08);
            border-color: rgba(99, 102, 241, 0.3);
        }
        .permission-group {
            background: #f8fafc;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .permission-group h6 {
            font-size: 0.85rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
        }
        .form-check {
            padding-left: 0;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }
        .form-check-input {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        .form-check-label {
            font-size: 0.88rem;
            color: #334155;
            cursor: pointer;
        }
        .badge-count {
            font-size: 0.7rem;
            padding: 4px 8px;
            border-radius: 50px;
        }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-1 text-dark">Access Control Matrix</h5>
            <p class="text-muted small mb-0">Define what each role can see and do across the platform.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
            <i class="bi bi-shield-plus me-1"></i> Create New Role
        </button>
    </div>

    {{-- ==================== ROLES GRID ==================== --}}
    <div class="row g-4 mb-4">
        @forelse($roles as $role)
            <div class="col-lg-4 col-md-6">
                <div class="role-card p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                <i class="bi bi-shield-lock fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">{{ $role->name }}</h5>
                                <small class="text-muted">{{ $role->description ?? 'No description' }}</small>
                            </div>
                        </div>
                        @if(strtolower($role->name) !== 'super admin')
                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Delete this role?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger rounded-circle" style="width:32px; height:32px;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-4">
                        <span class="badge bg-primary bg-opacity-10 text-primary badge-count">
                            {{ count($role->permissions ?? []) }} Permissions
                        </span>
                        @if(strtolower($role->name) === 'super admin')
                            <span class="badge bg-warning bg-opacity-10 text-warning badge-count">
                                <i class="bi bi-star-fill me-1"></i> System Default
                            </span>
                        @endif
                    </div>

                    <button class="btn btn-outline-primary w-100 fw-semibold" data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id }}">
                        <i class="bi bi-gear me-1"></i> Manage Permissions
                    </button>
                </div>
            </div>

            {{-- ==================== EDIT ROLE MODAL ==================== --}}
            <div class="modal fade" id="editRoleModal{{ $role->id }}" tabindex="-1">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="modal-content">
                        @csrf @method('PUT')
                        <div class="modal-header bg-white border-bottom">
                            <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock me-2 text-primary"></i>Edit Role: {{ $role->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4 bg-light">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Role Name *</label>
                                    <input type="text" name="name" class="form-control" value="{{ $role->name }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Description</label>
                                    <input type="text" name="description" class="form-control" value="{{ $role->description }}">
                                </div>
                            </div>

                            <h6 class="fw-bold text-dark mb-3">Assign Permissions</h6>
                            
                            <div class="row g-3">
                                @foreach($permissionsMatrix as $module => $permissions)
                                    <div class="col-md-6 col-lg-4">
                                        <div class="permission-group">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">{{ $module }}</h6>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input module-toggle" type="checkbox" role="switch" data-module="{{ Str::slug($module) }}">
                                                    <label class="form-check-label small text-primary fw-bold" style="cursor:pointer;">All</label>
                                                </div>
                                            </div>
                                            @foreach($permissions as $key => $label)
                                                <div class="form-check">
                                                    <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]" value="{{ $key }}" id="edit_{{ $role->id }}_{{ $key }}" data-module="{{ Str::slug($module) }}"
                                                        {{ in_array($key, $role->permissions ?? []) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="edit_{{ $role->id }}_{{ $key }}">{{ $label }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer bg-white border-top">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Permissions</button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-shield-exclamation fs-1 text-muted d-block mb-2"></i>
                <p class="text-muted">No roles created yet.</p>
            </div>
        @endforelse
    </div>

    {{-- ==================== CREATE ROLE MODAL ==================== --}}
    <div class="modal fade" id="createRoleModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.roles.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-shield-plus me-2"></i>Create New Role</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Warehouse Manager" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="Brief description of responsibilities">
                    </div>
                    <small class="text-muted">You can assign specific permissions after creating the role.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Role</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Handle "Select All" toggle for each module
    document.querySelectorAll('.module-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const moduleSlug = this.getAttribute('data-module');
            const checkboxes = document.querySelectorAll(`.perm-checkbox[data-module="${moduleSlug}"]`);
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    });

    // Auto-check the module toggle if all its checkboxes are checked manually
    document.querySelectorAll('.perm-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const moduleSlug = this.getAttribute('data-module');
            const checkboxes = document.querySelectorAll(`.perm-checkbox[data-module="${moduleSlug}"]`);
            const toggle = document.querySelector(`.module-toggle[data-module="${moduleSlug}"]`);
            
            const allChecked = Array.from(checkboxes).every(c => c.checked);
            const someChecked = Array.from(checkboxes).some(c => c.checked);
            
            toggle.checked = allChecked;
            toggle.indeterminate = someChecked && !allChecked;
        });
    });

    // Initialize toggle states on page load for Edit Modals
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.module-toggle').forEach(toggle => {
            const moduleSlug = toggle.getAttribute('data-module');
            const modal = toggle.closest('.modal');
            if (!modal) return;
            
            const checkboxes = modal.querySelectorAll(`.perm-checkbox[data-module="${moduleSlug}"]`);
            if (checkboxes.length === 0) return;

            const allChecked = Array.from(checkboxes).every(c => c.checked);
            const someChecked = Array.from(checkboxes).some(c => c.checked);
            
            toggle.checked = allChecked;
            toggle.indeterminate = someChecked && !allChecked;
        });
    });
</script>
@endpush