@extends('layouts.master')
@section('title', 'Audit Trail')
@section('page-title', 'System Audit Trail')

@section('content')
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.audit.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Action</label>
                    <select name="action" class="form-select form-select-sm">
                        <option value="">All Actions</option>
                        @foreach($actions as $action)<option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">User</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">All Users</option>
                        @foreach($users as $user)<option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Target</th>
                            <th>IP Address</th>
                            <th class="text-end pe-4">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="ps-4 text-muted small">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    <div class="fw-semibold small">{{ $log->user->name ?? 'System' }}</div>
                                </td>
                                <td>
                                    @php
                                        $colors = ['created' => 'success', 'updated' => 'info', 'deleted' => 'danger', 'login' => 'primary', 'logout' => 'secondary'];
                                    @endphp
                                    <span class="badge bg-{{ $colors[$log->action] ?? 'secondary' }} bg-opacity-10 text-{{ $colors[$log->action] ?? 'secondary' }}">{{ ucfirst($log->action) }}</span>
                                </td>
                                <td>
                                    @if($log->model_type)
                                        <code class="small">{{ class_basename($log->model_type) }} #{{ $log->model_id }}</code>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                                <td class="text-muted small font-monospace">{{ $log->ip_address }}</td>
                                <td class="text-end pe-4">
                                    @if($log->old_values || $log->new_values)
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#logModal{{ $log->id }}">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        {{-- Simple Modal for JSON diff --}}
                                        <div class="modal fade" id="logModal{{ $log->id }}">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header"><h5 class="modal-title">Change Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                                    <div class="modal-body">
                                                        <h6>Old Values:</h6>
                                                        <pre class="bg-light p-3 rounded">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) ?: 'None' }}</pre>
                                                        <h6>New Values:</h6>
                                                        <pre class="bg-light p-3 rounded">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) ?: 'None' }}</pre>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-shield-check fs-1 d-block mb-2"></i>No audit logs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())<div class="card-footer bg-white border-top-0 pt-3">{{ $logs->links() }}</div>@endif
    </div>
@endsection