@extends('layouts.master')
@section('title', 'User Management')
@section('page-title', 'User Management')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="fw-bold mb-0"><i class="bi bi-people me-2"></i>Team Members</h6>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="bi bi-person-plus me-1"></i> Invite User</button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">User</th>
                            <th>Role</th>
                            <th>Wing</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:36px; height:36px;">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                        <div>
                                            <div class="fw-semibold">{{ $user->name }}</div>
                                            <small class="text-muted">{{ $user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ ucfirst($user->role) }}</span></td>
                                <td><span class="badge bg-info bg-opacity-10 text-info">{{ ucfirst($user->wing) }}</span></td>
                                <td class="text-muted small">{{ $user->created_at->format('d M Y') }}</td>
                                <td><span class="badge bg-success bg-opacity-10 text-success">Active</span></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-danger" onclick="alert('Feature coming soon')"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($users->hasPages())<div class="card-footer bg-white border-top-0 pt-3">{{ $users->links() }}</div>@endif
    </div>

    {{-- ADD USER MODAL --}}
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.users.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Invite New User</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-4">
                    <div class="mb-3"><label class="form-label fw-semibold">Full Name *</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Email Address *</label><input type="email" name="email" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Temporary Password *</label><input type="text" name="password" class="form-control" value="Welcome@123" required></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role *</label>
                            <select name="role" class="form-select" required>
                                <option value="admin">Admin</option>
                                <option value="manager">Manager</option>
                                <option value="staff">Staff</option>
                                <option value="cashier">Cashier</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Wing *</label>
                            <select name="wing" class="form-select" required>
                                <option value="manufacturer">Manufacturer</option>
                                <option value="warehouse">Warehouse</option>
                                <option value="distributor">Distributor</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Send Invitation</button></div>
            </form>
        </div>
    </div>
@endsection