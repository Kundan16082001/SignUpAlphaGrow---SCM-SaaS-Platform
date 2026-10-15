@extends('layouts.master')
@section('title', 'Procurement')
@section('page-title', 'Procurement Management')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="small mb-1 opacity-75">Total Vendors</p><h3 class="fw-bold mb-0">{{ $stats['total_vendors'] }}</h3></div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="small mb-1 opacity-75">Active Vendors</p><h3 class="fw-bold mb-0">{{ $stats['active_vendors'] }}</h3></div>
                    <i class="bi bi-check-circle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="small mb-1 opacity-75">Total POs</p><h3 class="fw-bold mb-0">{{ $stats['total_pos'] }}</h3></div>
                    <i class="bi bi-receipt fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="small mb-1 opacity-75">Purchase Value</p><h3 class="fw-bold mb-0">₹{{ number_format($stats['total_purchase_value'], 0) }}</h3></div>
                    <i class="bi bi-currency-rupee fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'vendors' ? 'active' : '' }}" href="{{ route('finance-hr.procurement.index', ['tab' => 'vendors']) }}">
                <i class="bi bi-people me-1"></i> Vendors
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'pos' ? 'active' : '' }}" href="{{ route('finance-hr.procurement.index', ['tab' => 'pos']) }}">
                <i class="bi bi-receipt me-1"></i> Purchase Orders
            </a>
        </li>
    </ul>

    @if($tab === 'vendors')
        {{-- VENDORS TAB --}}
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVendorModal"><i class="bi bi-plus-lg me-1"></i> Add Vendor</button>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Vendor Code</th>
                                <th>Name</th>
                                <th>GSTIN</th>
                                <th>Contact</th>
                                <th>Phone</th>
                                <th>Credit Limit</th>
                                <th>Rating</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vendors as $vendor)
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">{{ $vendor->vendor_code }}</td>
                                    <td class="fw-semibold">{{ $vendor->name }}</td>
                                    <td><code class="small">{{ $vendor->gstin ?? 'N/A' }}</code></td>
                                    <td>{{ $vendor->contact_person ?? 'N/A' }}</td>
                                    <td>{{ $vendor->phone ?? 'N/A' }}</td>
                                    <td class="fw-semibold">₹{{ number_format($vendor->credit_limit, 2) }}</td>
                                    <td>
                                        <span class="text-warning">{{ $vendor->rating_stars }}</span>
                                        <small class="text-muted ms-1">{{ $vendor->rating }}</small>
                                    </td>
                                    <td><span class="badge bg-{{ $vendor->status == 'active' ? 'success' : 'secondary' }}">{{ ucfirst($vendor->status) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center py-5 text-muted">No vendors found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($vendors->hasPages())<div class="card-footer bg-white border-top-0 pt-3">{{ $vendors->links() }}</div>@endif
        </div>
    @else
        {{-- PURCHASE ORDERS TAB --}}
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPOModal"><i class="bi bi-plus-lg me-1"></i> Create PO</button>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">PO Number</th>
                                <th>Vendor</th>
                                <th>PO Date</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-end">Tax</th>
                                <th class="text-end">Total</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseOrders as $po)
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">{{ $po->po_no }}</td>
                                    <td class="fw-semibold">{{ $po->vendor->name ?? 'N/A' }}</td>
                                    <td class="text-muted small">{{ $po->po_date->format('d M Y') }}</td>
                                    <td class="text-end">₹{{ number_format($po->subtotal, 2) }}</td>
                                    <td class="text-end">₹{{ number_format($po->tax, 2) }}</td>
                                    <td class="text-end fw-bold">₹{{ number_format($po->total, 2) }}</td>
                                    <td><span class="badge bg-{{ $po->status_color }}">{{ ucfirst($po->status) }}</span></td>
                                    <td class="text-end pe-4">
                                        <form method="POST" action="{{ route('finance-hr.procurement.po.update-status', $po) }}" class="d-inline">
                                            @csrf @method('PATCH')
                                            <select name="status" class="form-select form-select-sm" style="width:120px;" onchange="this.form.submit()">
                                                <option value="draft" {{ $po->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                                <option value="approved" {{ $po->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                                <option value="received" {{ $po->status == 'received' ? 'selected' : '' }}>Received</option>
                                                <option value="cancelled" {{ $po->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center py-5 text-muted">No purchase orders found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($purchaseOrders->hasPages())<div class="card-footer bg-white border-top-0 pt-3">{{ $purchaseOrders->links() }}</div>@endif
        </div>
    @endif

    {{-- ADD VENDOR MODAL --}}
    <div class="modal fade" id="addVendorModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('finance-hr.procurement.vendor.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Add New Vendor</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fw-semibold">Vendor Name *</label><input type="text" name="name" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">GSTIN</label><input type="text" name="gstin" class="form-control" maxlength="15"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Contact Person</label><input type="text" name="contact_person" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Phone</label><input type="text" name="phone" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Bank Name</label><input type="text" name="bank_name" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Account No</label><input type="text" name="account_number" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">IFSC Code</label><input type="text" name="ifsc_code" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Credit Limit (₹)</label><input type="number" name="credit_limit" class="form-control" step="0.01" value="0"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Rating (1-5)</label><input type="number" name="rating" class="form-control" min="1" max="5" step="0.1" value="5"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Vendor</button></div>
            </form>
        </div>
    </div>

    {{-- ADD PO MODAL --}}
    <div class="modal fade" id="addPOModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('finance-hr.procurement.po.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Create Purchase Order</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label fw-semibold">Vendor *</label><select name="vendor_id" class="form-select" required><option value="">Select Vendor</option>@foreach($vendors as $v)<option value="{{ $v->id }}">{{ $v->name }}</option>@endforeach</select></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">PO Date *</label><input type="date" name="po_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Subtotal (₹) *</label><input type="number" name="subtotal" class="form-control" step="0.01" min="0" required></div>
                        <div class="col-12"><label class="form-label fw-semibold">Tax (₹) *</label><input type="number" name="tax" class="form-control" step="0.01" min="0" value="0" required></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Create PO</button></div>
            </form>
        </div>
    </div>
@endsection