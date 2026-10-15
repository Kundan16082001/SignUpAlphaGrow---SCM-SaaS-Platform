@extends('layouts.master')
@section('title', 'Backup & Restore')
@section('page-title', 'Backup & Restore Management')

@section('content')
    {{-- Prevent browser caching issues --}}
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
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

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                    <i class="bi bi-database fs-3"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Total Backups</small>
                    <h4 class="fw-bold mb-0">{{ $stats['total_backups'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-3">
                    <i class="bi bi-hdd fs-3"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Total Size</small>
                    <h4 class="fw-bold mb-0">{{ $stats['total_size'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center gap-3">
                <div class="bg-info bg-opacity-10 text-info p-3 rounded-3">
                    <i class="bi bi-clock-history fs-3"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Latest Backup</small>
                    <h6 class="fw-bold mb-0">{{ $stats['latest_backup'] }}</h6>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Backup Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0"><i class="bi bi-plus-circle me-2 text-primary"></i>Create New Backup</h6>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.backups.create') }}" id="createBackupForm">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Backup Type</label>
                        <select name="type" class="form-select" required>
                            <option value="database">Database Only (Fastest)</option>
                            <option value="files">Files Only</option>
                            <option value="full">Full Backup (Database + Files)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary btn-lg w-100" id="createBackupBtn">
                            <i class="bi bi-cloud-upload me-2"></i>Create Backup
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Backup List Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="bi bi-archive me-2 text-success"></i>Available Backups</h6>
            <span class="badge bg-light text-dark border">{{ count($backups) }} files</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">File Name</th>
                            <th>Created</th>
                            <th>Size</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups as $backup)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-file-earmark-zip text-primary fs-4"></i>
                                        <div>
                                            <div class="fw-semibold small">{{ $backup['file_name'] }}</div>
                                            <small class="text-muted">ZIP Archive</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold small">{{ date('d M Y', $backup['date']) }}</div>
                                    <small class="text-muted">{{ date('h:i A', $backup['date']) }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $backup['size_human'] }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.backups.download', $backup['file_name']) }}" class="btn btn-sm btn-outline-primary" title="Download">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#restoreModal{{ $loop->index }}" title="Restore">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.backups.destroy', $backup['file_name']) }}" class="d-inline" onsubmit="return confirm('Delete this backup permanently?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Restore Confirmation Modal --}}
                                    <div class="modal fade" id="restoreModal{{ $loop->index }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-warning text-dark">
                                                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Restore</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="alert alert-danger mb-3">
                                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                                        <strong>Warning:</strong> This will overwrite your current database with data from this backup.
                                                    </div>
                                                    <p class="mb-2"><strong>File:</strong> <span class="font-monospace small">{{ $backup['file_name'] }}</span></p>
                                                    <p class="mb-0"><strong>Date:</strong> {{ date('d M Y, h:i A', $backup['date']) }}</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <form method="POST" action="{{ route('admin.backups.restore', $backup['file_name']) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-warning">
                                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Restore Now
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-archive fs-1 d-block mb-2"></i>
                                    No backups found. Click "Create Backup" to get started.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Disable button and show spinner during backup creation
    document.getElementById('createBackupForm').addEventListener('submit', function() {
        const btn = document.getElementById('createBackupBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Backup...';
    });

    // Auto-refresh page after successful backup creation to show new file
    @if(session('success'))
        setTimeout(() => {
            window.location.reload(true);
        }, 1500);
    @endif
</script>
@endpush