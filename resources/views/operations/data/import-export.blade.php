@extends('layouts.master')
@section('title', 'Import/Export Data')
@section('page-title', 'Bulk Import & Export')

@section('content')
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

    <div class="row g-4">
        {{-- ============ EXPORT SECTION ============ --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-download me-2"></i>Export Data</h6>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-4">Download your data in Excel format for backup or analysis.</p>
                    
                    <div class="d-grid gap-3">
                        <a href="{{ route('operations.export.products') }}" class="btn btn-outline-success btn-lg text-start d-flex align-items-center gap-3">
                            <i class="bi bi-box-seam fs-3"></i>
                            <div>
                                <div class="fw-bold">Export Products</div>
                                <small class="text-muted">Download all products with stock details</small>
                            </div>
                        </a>
                        <a href="{{ route('operations.export.customers') }}" class="btn btn-outline-success btn-lg text-start d-flex align-items-center gap-3">
                            <i class="bi bi-people fs-3"></i>
                            <div>
                                <div class="fw-bold">Export Customers</div>
                                <small class="text-muted">Download customer list with contact info</small>
                            </div>
                        </a>
                        <a href="{{ route('operations.export.orders') }}" class="btn btn-outline-success btn-lg text-start d-flex align-items-center gap-3">
                            <i class="bi bi-cart-check fs-3"></i>
                            <div>
                                <div class="fw-bold">Export Orders</div>
                                <small class="text-muted">Download all orders with payment status</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ IMPORT SECTION ============ --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-upload me-2"></i>Import Data</h6>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-4">Upload Excel files to bulk import data. Download templates first.</p>

                    {{-- Products Import --}}
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0">Import Products</h6>
                            <a href="{{ route('operations.import.template.product') }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-earmark-excel me-1"></i> Download Template
                            </a>
                        </div>
                        <form method="POST" action="{{ route('operations.import.products') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="input-group">
                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload me-1"></i> Import
                                </button>
                            </div>
                            <small class="text-muted mt-1 d-block">Max file size: 10MB. Formats: XLSX, XLS, CSV</small>
                        </form>
                    </div>

                    {{-- Customers Import --}}
                    <div class="border rounded-3 p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0">Import Customers</h6>
                            <a href="{{ route('operations.import.template.customer') }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-earmark-excel me-1"></i> Download Template
                            </a>
                        </div>
                        <form method="POST" action="{{ route('operations.import.customers') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="input-group">
                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload me-1"></i> Import
                                </button>
                            </div>
                            <small class="text-muted mt-1 d-block">Max file size: 10MB. Formats: XLSX, XLS, CSV</small>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ INSTRUCTIONS ============ --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-light py-3">
            <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-2"></i>Important Notes</h6>
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li class="mb-2"><strong>Download Templates First:</strong> Always use the provided templates to ensure correct column format.</li>
                <li class="mb-2"><strong>Unique Fields:</strong> SKU (products) and Customer Code (customers) must be unique. Duplicates will be rejected.</li>
                <li class="mb-2"><strong>Required Fields:</strong> Make sure all required fields are filled. Check template for mandatory columns.</li>
                <li class="mb-2"><strong>File Size:</strong> Maximum 10MB per file. For larger datasets, split into multiple files.</li>
                <li><strong>Error Handling:</strong> If import fails, you'll see detailed error messages with row numbers.</li>
            </ul>
        </div>
    </div>
@endsection