@extends('layouts.master')
@section('title', 'Company Setup')
@section('page-title', 'Company Settings')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3"><h6 class="fw-bold mb-0"><i class="bi bi-building-gear me-2"></i>General Information</h6></div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.company.update') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Company Name *</label>
                                <input type="text" name="company_name" class="form-control" value="{{ $settings->company_name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tax ID / GSTIN</label>
                                <input type="text" name="tax_id" class="form-control" value="{{ $settings->tax_id }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ $settings->email }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ $settings->phone }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Address</label>
                                <textarea name="address" class="form-control" rows="2">{{ $settings->address }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Default Currency</label>
                                <select name="currency" class="form-select">
                                    <option value="INR" {{ $settings->currency == 'INR' ? 'selected' : '' }}>INR (₹)</option>
                                    <option value="USD" {{ $settings->currency == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                    <option value="EUR" {{ $settings->currency == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Company Logo</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                                @if($settings->logo_path)
                                    <img src="{{ Storage::url($settings->logo_path) }}" class="mt-2 rounded" style="height: 50px;">
                                @endif
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection