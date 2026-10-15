@extends('layouts.master')
@section('title', 'Document Management')
@section('page-title', 'Document Management System')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <style>
        .doc-card {
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 12px;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .doc-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px -8px rgba(0,0,0,0.1);
            border-color: rgba(99, 102, 241, 0.3);
        }
        .doc-icon {
            width: 48px; height: 48px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            background: #f1f5f9;
        }
    </style>

    {{-- ==================== STATS & ACTIONS ==================== --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                    <i class="bi bi-files fs-3"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Total Documents</small>
                    <h4 class="fw-bold mb-0">{{ $stats['total_docs'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-3">
                    <i class="bi bi-hdd fs-3"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Storage Used</small>
                    <h4 class="fw-bold mb-0">{{ number_format($stats['total_size'] / 1024, 2) }} MB</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center gap-3">
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3">
                    <i class="bi bi-clock-history fs-3"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Uploaded Today</small>
                    <h4 class="fw-bold mb-0">{{ $stats['recent_uploads'] }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== FILTERS & UPLOAD ==================== --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('intelligence.dms.index') }}" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search documents by name..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select" onchange="this.form.submit()">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }} Documents</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bi bi-cloud-upload me-1"></i> Upload Document
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== DOCUMENT GRID ==================== --}}
    <div class="row g-4">
        @forelse($documents as $doc)
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="doc-card p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="doc-icon">
                            <i class="bi {{ $doc->file_icon }}"></i>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light rounded-circle" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                                <li>
                                    <a class="dropdown-item" href="{{ Storage::url($doc->file_path) }}" target="_blank">
                                        <i class="bi bi-eye me-2"></i> View
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ Storage::url($doc->file_path) }}" download>
                                        <i class="bi bi-download me-2"></i> Download
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('intelligence.dms.destroy', $doc) }}" onsubmit="return confirm('Are you sure you want to delete this document?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-trash me-2"></i> Delete
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <h6 class="fw-bold mb-1 text-truncate" title="{{ $doc->name }}">{{ $doc->name }}</h6>
                    <div class="d-flex gap-2 mb-3">
                        <span class="badge bg-light text-dark border">{{ strtoupper($doc->file_type) }}</span>
                        <span class="badge bg-light text-dark border">{{ $doc->file_size_formatted }}</span>
                    </div>

                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">{{ $doc->category }}</small>
                            <small class="text-muted" style="font-size: 0.7rem;">by {{ $doc->uploader->name ?? 'Unknown' }}</small>
                        </div>
                        <small class="text-muted" style="font-size: 0.75rem;">{{ $doc->created_at->diffForHumans() }}</small>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm text-center py-5">
                    <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">No documents found</h5>
                    <p class="text-muted small">Try adjusting your filters or upload a new document.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if($documents->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $documents->links() }}
        </div>
    @endif

    {{-- ==================== UPLOAD MODAL ==================== --}}
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('intelligence.dms.store') }}" class="modal-content" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i>Upload Document</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category *</label>
                        <select name="category" class="form-select" required>
                            @foreach($categories as $cat)
                                @if($cat !== 'All')
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Display Name (Optional)</label>
                        <input type="text" name="name" class="form-control" placeholder="Leave blank to use original filename">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select File *</label>
                        <input type="file" name="document_file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.jpg,.jpeg,.png,.zip" required>
                        <small class="text-muted">Max file size: 10 MB. Allowed: PDF, Word, Excel, Images, ZIP.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Document</button>
                </div>
            </form>
        </div>
    </div>
@endsection