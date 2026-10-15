@extends('layouts.master')
@section('title', 'CRM')
@section('page-title', 'Customer Relationship Management')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ==================== KPI CARDS ==================== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75">Active Leads</p>
                        <h3 class="fw-bold mb-0">{{ $leadStats['new'] + $leadStats['contacted'] + $leadStats['quoted'] }}</h3>
                        <small class="opacity-75">Pipeline: ₹{{ number_format($leadStats['pipeline_value']/100000, 1) }}L</small>
                    </div>
                    <i class="bi bi-megaphone fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75">Active Dealers</p>
                        <h3 class="fw-bold mb-0">{{ $dealerStats['active'] }}</h3>
                        <small class="opacity-75">Business: ₹{{ number_format($dealerStats['total_business']/100000, 1) }}L</small>
                    </div>
                    <i class="bi bi-shop fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75">Open Tickets</p>
                        <h3 class="fw-bold mb-0">{{ $ticketStats['open'] + $ticketStats['in_progress'] }}</h3>
                        <small class="opacity-75">{{ $ticketStats['urgent'] }} urgent</small>
                    </div>
                    <i class="bi bi-headset fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75">Overdue Follow-ups</p>
                        <h3 class="fw-bold mb-0">{{ $leadStats['overdue_followups'] }}</h3>
                        <small class="opacity-75">Need attention</small>
                    </div>
                    <i class="bi bi-alarm fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== TAB NAVIGATION ==================== --}}
    <ul class="nav nav-pills mb-4" id="crmTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'leads' ? 'active' : '' }}" href="{{ route('finance-hr.crm.index', ['tab' => 'leads']) }}">
                <i class="bi bi-megaphone me-1"></i> Leads Pipeline
                <span class="badge bg-light text-dark ms-1">{{ $leadStats['total'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'dealers' ? 'active' : '' }}" href="{{ route('finance-hr.crm.index', ['tab' => 'dealers']) }}">
                <i class="bi bi-shop me-1"></i> Dealers
                <span class="badge bg-light text-dark ms-1">{{ $dealerStats['total'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'tickets' ? 'active' : '' }}" href="{{ route('finance-hr.crm.index', ['tab' => 'tickets']) }}">
                <i class="bi bi-headset me-1"></i> Support Tickets
                <span class="badge bg-light text-dark ms-1">{{ $ticketStats['total'] }}</span>
            </a>
        </li>
    </ul>

    {{-- ==================== TAB CONTENT ==================== --}}
    <div class="tab-content">

        {{-- ==================== LEADS TAB ==================== --}}
        @if($tab == 'leads')
            {{-- Pipeline Visualization --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-funnel me-2"></i>Sales Pipeline</h6>
                    <div class="row g-2 text-center">
                        @php
                            $pipeline = [
                                'new' => ['label' => 'New', 'color' => 'info', 'icon' => 'bi-stars'],
                                'contacted' => ['label' => 'Contacted', 'color' => 'primary', 'icon' => 'bi-telephone'],
                                'quoted' => ['label' => 'Quoted', 'color' => 'warning', 'icon' => 'bi-file-earmark-text'],
                                'won' => ['label' => 'Won', 'color' => 'success', 'icon' => 'bi-trophy'],
                                'lost' => ['label' => 'Lost', 'color' => 'danger', 'icon' => 'bi-x-circle'],
                            ];
                        @endphp
                        @foreach($pipeline as $status => $config)
                            <div class="col">
                                <div class="p-3 rounded-3 border border-{{ $config['color'] }} border-opacity-25 bg-{{ $config['color'] }} bg-opacity-10">
                                    <i class="bi {{ $config['icon'] }} fs-3 text-{{ $config['color'] }} d-block mb-1"></i>
                                    <h4 class="fw-bold mb-0 text-{{ $config['color'] }}">{{ $leadStats[$status] }}</h4>
                                    <small class="text-muted">{{ $config['label'] }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Leads Table --}}
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeadModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Lead
                </button>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Lead Code</th>
                                    <th>Company</th>
                                    <th>Contact</th>
                                    <th>Source</th>
                                    <th class="text-end">Value</th>
                                    <th>Follow-up</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leads as $lead)
                                    <tr class="{{ $lead->is_overdue ? 'table-warning' : '' }}">
                                        <td class="ps-4 fw-bold text-primary">{{ $lead->lead_code }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $lead->company_name }}</div>
                                        </td>
                                        <td>
                                            @if($lead->contact_person)
                                                <i class="bi bi-person me-1 text-muted"></i>{{ $lead->contact_person }}
                                            @else
                                                <span class="text-muted small">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $lead->source ?? 'N/A' }}</span>
                                        </td>
                                        <td class="text-end fw-bold">₹{{ number_format($lead->value, 0) }}</td>
                                        <td>
                                            @if($lead->follow_up_date)
                                                @if($lead->is_overdue)
                                                    <span class="text-danger fw-bold small">
                                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                        {{ $lead->follow_up_date->format('d M') }}
                                                    </span>
                                                    <br><small class="text-danger">Overdue</small>
                                                @else
                                                    <span class="text-muted small">{{ $lead->follow_up_date->format('d M Y') }}</span>
                                                @endif
                                            @else
                                                <span class="text-muted small">Not set</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $lead->status_color }} bg-opacity-10 text-{{ $lead->status_color }}">
                                                {{ ucfirst($lead->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <form method="POST" action="{{ route('finance-hr.crm.lead.update-status', $lead) }}" class="d-inline">
                                                @csrf @method('PATCH')
                                                <select name="status" class="form-select form-select-sm" style="width:130px;" onchange="this.form.submit()">
                                                    <option value="new" {{ $lead->status == 'new' ? 'selected' : '' }}>New</option>
                                                    <option value="contacted" {{ $lead->status == 'contacted' ? 'selected' : '' }}>Contacted</option>
                                                    <option value="quoted" {{ $lead->status == 'quoted' ? 'selected' : '' }}>Quoted</option>
                                                    <option value="won" {{ $lead->status == 'won' ? 'selected' : '' }}>Won</option>
                                                    <option value="lost" {{ $lead->status == 'lost' ? 'selected' : '' }}>Lost</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-megaphone fs-1 d-block mb-2"></i>No leads yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($leads->hasPages())<div class="card-footer bg-white border-top-0 pt-3">{{ $leads->links() }}</div>@endif
            </div>
        @endif

        {{-- ==================== DEALERS TAB ==================== --}}
        @if($tab == 'dealers')
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDealerModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Dealer
                </button>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Dealer Code</th>
                                    <th>Name</th>
                                    <th>Region</th>
                                    <th class="text-center">Orders</th>
                                    <th class="text-end">Business</th>
                                    <th>Credit</th>
                                    <th>Tier</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dealers as $dealer)
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">{{ $dealer->dealer_code }}</td>
                                        <td class="fw-semibold">{{ $dealer->name }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $dealer->region ?? 'N/A' }}</span></td>
                                        <td class="text-center fw-bold">{{ $dealer->total_orders }}</td>
                                        <td class="text-end fw-bold text-success">₹{{ number_format($dealer->total_business, 0) }}</td>
                                        <td>
                                            @php $util = $dealer->credit_utilization; @endphp
                                            <div class="small">
                                                <div class="fw-semibold">₹{{ number_format($dealer->outstanding, 0) }} / ₹{{ number_format($dealer->credit_limit, 0) }}</div>
                                                <div class="progress" style="height: 4px;">
                                                    <div class="progress-bar bg-{{ $util >= 90 ? 'danger' : ($util >= 70 ? 'warning' : 'success') }}" style="width: {{ $util }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $util }}% used</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $dealer->tier_color }}">{{ $dealer->performance_tier }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $dealer->status == 'active' ? 'success' : 'secondary' }}">{{ ucfirst($dealer->status) }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-outline-primary edit-dealer-btn"
                                                data-id="{{ $dealer->id }}"
                                                data-orders="{{ $dealer->total_orders }}"
                                                data-business="{{ $dealer->total_business }}"
                                                data-outstanding="{{ $dealer->outstanding }}"
                                                data-status="{{ $dealer->status }}"
                                                data-bs-toggle="modal" data-bs-target="#editDealerModal">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center py-5 text-muted"><i class="bi bi-shop fs-1 d-block mb-2"></i>No dealers yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($dealers->hasPages())<div class="card-footer bg-white border-top-0 pt-3">{{ $dealers->links() }}</div>@endif
            </div>
        @endif

        {{-- ==================== TICKETS TAB ==================== --}}
        @if($tab == 'tickets')
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTicketModal">
                    <i class="bi bi-plus-lg me-1"></i> Create Ticket
                </button>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Ticket #</th>
                                    <th>Customer</th>
                                    <th>Subject</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Resolution</th>
                                    <th class="text-end pe-4">Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tickets as $ticket)
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">{{ $ticket->ticket_no }}</td>
                                        <td class="fw-semibold">{{ $ticket->customer_name }}</td>
                                        <td>
                                            <div>{{ $ticket->subject }}</div>
                                            @if($ticket->description)
                                                <small class="text-muted">{{ Str::limit($ticket->description, 40) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $priorityIcons = ['low' => 'bi-arrow-down-circle', 'medium' => 'bi-dash-circle', 'high' => 'bi-arrow-up-circle', 'urgent' => 'bi-exclamation-octagon-fill'];
                                            @endphp
                                            <span class="badge bg-{{ $ticket->priority_color }} bg-opacity-10 text-{{ $ticket->priority_color }}">
                                                <i class="bi {{ $priorityIcons[$ticket->priority] }} me-1"></i>
                                                {{ ucfirst($ticket->priority) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $ticket->status_color }} bg-opacity-10 text-{{ $ticket->status_color }}">
                                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                            </span>
                                        </td>
                                        <td class="text-muted small">{{ $ticket->created_at->diffForHumans() }}</td>
                                        <td>
                                            @if($ticket->resolution)
                                                <small class="text-muted">{{ Str::limit($ticket->resolution, 30) }}</small>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <form method="POST" action="{{ route('finance-hr.crm.ticket.update-status', $ticket) }}" class="d-inline">
                                                @csrf @method('PATCH')
                                                <select name="status" class="form-select form-select-sm" style="width:140px;" onchange="this.form.submit()">
                                                    <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open</option>
                                                    <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                    <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                                    <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Closed</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-headset fs-1 d-block mb-2"></i>No tickets yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($tickets->hasPages())<div class="card-footer bg-white border-top-0 pt-3">{{ $tickets->links() }}</div>@endif
            </div>
        @endif

    </div>

    {{-- ==================== MODALS ==================== --}}

    {{-- ADD LEAD MODAL --}}
    <div class="modal fade" id="addLeadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('finance-hr.crm.lead.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-megaphone me-2"></i>Add New Lead</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company Name *</label>
                            <input type="text" name="company_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lead Source</label>
                            <select name="source" class="form-select">
                                <option value="Website">Website</option>
                                <option value="Referral">Referral</option>
                                <option value="Trade Show">Trade Show</option>
                                <option value="Cold Call">Cold Call</option>
                                <option value="Email">Email Campaign</option>
                                <option value="Social Media">Social Media</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Potential Value (₹)</label>
                            <input type="number" name="value" class="form-control" step="0.01" min="0" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Follow-up Date</label>
                            <input type="date" name="follow_up_date" class="form-control" min="{{ date('Y-m-d') }}">
                            <small class="text-muted">Set reminder for next follow-up</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Lead</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ADD DEALER MODAL --}}
    <div class="modal fade" id="addDealerModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('finance-hr.crm.dealer.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-shop me-2"></i>Add New Dealer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Dealer Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Region</label>
                            <select name="region" class="form-select">
                                <option value="North India">North India</option>
                                <option value="South India">South India</option>
                                <option value="East India">East India</option>
                                <option value="West India">West India</option>
                                <option value="Central India">Central India</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Credit Limit (₹)</label>
                            <input type="number" name="credit_limit" class="form-control" step="0.01" min="0" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Dealer</button>
                </div>
            </form>
        </div>
    </div>

    {{-- EDIT DEALER MODAL --}}
    <div class="modal fade" id="editDealerModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" id="editDealerForm" class="modal-content">
                @csrf @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Update Dealer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Total Orders</label>
                            <input type="number" name="total_orders" id="edit_orders" class="form-control" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Total Business (₹)</label>
                            <input type="number" name="total_business" id="edit_business" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Outstanding (₹)</label>
                            <input type="number" name="outstanding" id="edit_outstanding" class="form-control" step="0.01" min="0">
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
                    <button type="submit" class="btn btn-warning">Update Dealer</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ADD TICKET MODAL --}}
    <div class="modal fade" id="addTicketModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('finance-hr.crm.ticket.store') }}" class="modal-content">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-headset me-2"></i>Create Support Ticket</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Customer Name *</label>
                            <input type="text" name="customer_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Priority *</label>
                            <select name="priority" class="form-select" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Subject *</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Detailed description of the issue..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Create Ticket</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Edit Dealer Modal
    document.querySelectorAll('.edit-dealer-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            document.getElementById('editDealerForm').action = `/finance-hr/crm/dealer/${id}`;
            document.getElementById('edit_orders').value = this.dataset.orders;
            document.getElementById('edit_business').value = this.dataset.business;
            document.getElementById('edit_outstanding').value = this.dataset.outstanding;
            document.getElementById('edit_status').value = this.dataset.status;
        });
    });
</script>
@endpush