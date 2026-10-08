@extends('layouts.master')
@section('title', 'Customers')
@section('page-title', 'Customer Management')

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

    <!-- Filters & Actions -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('operations.customers.index') }}" class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search Name, Code, or GSTIN..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex justify-content-end">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Customer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Customer Code</th>
                            <th>Customer Name</th>
                            <th>GSTIN</th>
                            <th>Contact</th>
                            <th>Credit Limit</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td class="ps-4 fw-bold text-primary">{{ $customer->customer_code }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $customer->name }}</div>
                                    @if($customer->email)
                                        <small class="text-muted">{{ $customer->email }}</small>
                                    @endif
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $customer->gstin ?: 'N/A' }}</span></td>
                                <td>{{ $customer->phone ?: 'N/A' }}</td>
                                <td class="fw-semibold">₹{{ number_format($customer->credit_limit, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $customer->status == 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($customer->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary edit-btn" 
                                        data-id="{{ $customer->id }}" 
                                        data-name="{{ $customer->name }}" 
                                        data-gstin="{{ $customer->gstin }}" 
                                        data-phone="{{ $customer->phone }}" 
                                        data-email="{{ $customer->email }}" 
                                        data-credit="{{ $customer->credit_limit }}"
                                        data-status="{{ $customer->status }}"
                                        data-bs-toggle="modal" data-bs-target="#editCustomerModal">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('operations.customers.destroy', $customer) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                                    No customers found. Click "Add Customer" to get started.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($customers->hasPages())
            <div class="card-footer bg-white border-top-0 pt-3">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    <!-- ADD CUSTOMER MODAL -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('operations.customers.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Add New Customer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Customer Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., Acme Corp" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">GSTIN (15 chars)</label>
                            <input type="text" name="gstin" class="form-control" placeholder="27AABCU9603R1ZM" maxlength="15">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+91 98765 43210">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="contact@acme.com">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Credit Limit (₹) *</label>
                            <input type="number" name="credit_limit" class="form-control" value="0" step="0.01" required>
                            <small class="text-muted">Maximum credit allowed for this customer.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT CUSTOMER MODAL -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" id="editForm" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Customer Name *</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">GSTIN</label>
                            <input type="text" name="gstin" id="edit_gstin" class="form-control" maxlength="15">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" id="edit_email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Credit Limit (₹) *</label>
                            <input type="number" name="credit_limit" id="edit_credit" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Customer</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Populate Edit Modal dynamically
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;
            document.getElementById('editForm').action = `/operations/customers/${id}`;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_gstin').value = this.dataset.gstin;
            document.getElementById('edit_phone').value = this.dataset.phone;
            document.getElementById('edit_email').value = this.dataset.email;
            document.getElementById('edit_credit').value = this.dataset.credit;
            document.getElementById('edit_status').value = this.dataset.status;
        });
    });
</script>
@endpush