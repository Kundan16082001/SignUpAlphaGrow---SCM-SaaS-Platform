@extends('layouts.master')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        /* ==================== PREMIUM DASHBOARD STYLES ==================== */
        .premium-card {
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.04);
            border-radius: 16px;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            height: 100%;
            overflow: hidden;
            position: relative;
        }
        .premium-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px -8px rgba(0,0,0,0.08);
            border-color: rgba(0,0,0,0.06);
        }
        .premium-card .card-body { flex: 1; display: flex; flex-direction: column; }

        .icon-box {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
        }

        /* Gradient Header Card */
        .gradient-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
            color: white;
            border: none;
            overflow: hidden;
        }
        .gradient-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        .gradient-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
        }

        /* KPI Stats */
        .kpi-card {
            position: relative;
            overflow: hidden;
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(99,102,241,0.05) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Trend Pill */
        .trend-pill {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            letter-spacing: 0.3px;
        }
        .trend-up { background: rgba(16, 185, 129, 0.1); color: #059669; }
        .trend-down { background: rgba(239, 68, 68, 0.1); color: #dc2626; }
        .trend-neutral { background: rgba(100,116,139,0.1); color: #475569; }

        /* Activity Item */
        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(0,0,0,0.04);
            transition: all 0.2s ease;
        }
        .activity-item:last-child { border-bottom: none; }
        .activity-item:hover { background: #f8fafc; margin: 0 -16px; padding-left: 16px; padding-right: 16px; border-radius: 8px; }
        
        .activity-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        /* Pending Action Card */
        .pending-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 12px;
            transition: all 0.2s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            border: 1px solid transparent;
        }
        .pending-item:hover {
            background: #f8fafc;
            border-color: rgba(0,0,0,0.06);
            transform: translateX(4px);
        }

        /* Quick Action Button */
        .action-btn {
            transition: all 0.25s ease;
            border: 1px solid rgba(0,0,0,0.06);
            background: #fff;
            padding: 16px;
            border-radius: 12px;
            text-align: center;
            text-decoration: none;
            color: inherit;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .action-btn:hover {
            border-color: #6366f1;
            background: rgba(99, 102, 241, 0.03);
            transform: translateY(-2px);
            color: #6366f1;
            box-shadow: 0 8px 16px -4px rgba(99,102,241,0.15);
        }
        .action-btn i { font-size: 1.6rem; }

        /* Pulse Dot */
        .pulse-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #10b981;
            display: inline-block;
            position: relative;
        }
        .pulse-dot::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: #10b981;
            opacity: 0.4;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.8); opacity: 0.4; }
            50% { transform: scale(1.4); opacity: 0; }
            100% { transform: scale(0.8); opacity: 0; }
        }

        /* Animations */
        .fade-in-up {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }
        .d-1 { animation-delay: 0.05s; }
        .d-2 { animation-delay: 0.1s; }
        .d-3 { animation-delay: 0.15s; }
        .d-4 { animation-delay: 0.2s; }
        .d-5 { animation-delay: 0.25s; }
        .d-6 { animation-delay: 0.3s; }

        /* Status badge */
        .status-pill {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 50px;
        }

        /* Scrollbar */
        .custom-scroll::-webkit-scrollbar { width: 4px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>

    {{-- ==================== WELCOME HEADER ==================== --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8 fade-in-up d-1">
            <div class="premium-card gradient-header p-4 p-md-5">
                <div style="position: relative; z-index: 1;">
                    <h3 class="fw-bold mb-2" style="letter-spacing: -0.5px;">
                        Welcome back, {{ explode(' ', $user->name)[0] }}! 👋
                    </h3>
                    <p class="mb-3 opacity-90" style="max-width: 500px; line-height: 1.6;">
                        Here's what's happening with <strong>AlphaGrow</strong> today. You have
                        @if($stats['pending_invoices'] > 0 || $stats['low_stock_count'] > 0)
                            <strong>{{ $stats['pending_invoices'] + $stats['low_stock_count'] }}</strong> items that need attention.
                        @else
                            everything running smoothly.
                        @endif
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('finance-hr.pos.index') }}" class="btn btn-light fw-semibold px-4">
                            <i class="bi bi-upc-scan me-2"></i>New POS Sale
                        </a>
                        <a href="{{ route('intelligence.analytics.index') }}" class="btn btn-outline-light fw-semibold px-4">
                            <i class="bi bi-graph-up me-2"></i>View Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 fade-in-up d-2">
            <div class="premium-card p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">
                            {{ now()->format('l, d M Y') }}
                        </small>
                        <span class="d-flex align-items-center gap-2 small text-success fw-semibold">
                            <span class="pulse-dot"></span> Live
                        </span>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">{{ $stats['active_employees'] }} Active Employees</h5>
                    <p class="text-muted small mb-0">Working across your organization</p>
                </div>
                <div class="mt-3 pt-3 border-top">
                    <div class="d-flex justify-content-between align-items-center small">
                        <span class="text-muted">Open Support Tickets</span>
                        <span class="fw-bold text-dark">{{ $stats['open_tickets'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== KPI STATS GRID ==================== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3 fade-in-up d-2">
            <div class="premium-card kpi-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box" style="background: rgba(16, 185, 129, 0.1);">
                        <i class="bi bi-currency-rupee" style="color: #10b981;"></i>
                    </div>
                    <span class="trend-pill trend-up">
                        <i class="bi bi-arrow-up-short"></i> 12.5%
                    </span>
                </div>
                <h6 class="text-muted fw-semibold small mb-1">Total Revenue</h6>
                <h3 class="fw-bold mb-0 text-dark counter" data-target="{{ $stats['total_revenue'] }}">₹0</h3>
                <small class="text-muted mt-1 d-block">Invoices + POS</small>
            </div>
        </div>

        <div class="col-md-6 col-xl-3 fade-in-up d-3">
            <div class="premium-card kpi-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box" style="background: rgba(99, 102, 241, 0.1);">
                        <i class="bi bi-cart3" style="color: #6366f1;"></i>
                    </div>
                    <span class="trend-pill trend-up">
                        <i class="bi bi-arrow-up-short"></i> 8.2%
                    </span>
                </div>
                <h6 class="text-muted fw-semibold small mb-1">Total Orders</h6>
                <h3 class="fw-bold mb-0 text-dark counter" data-target="{{ $stats['total_orders'] }}">0</h3>
                <small class="text-muted mt-1 d-block">All time</small>
            </div>
        </div>

        <div class="col-md-6 col-xl-3 fade-in-up d-4">
            <div class="premium-card kpi-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box" style="background: rgba(245, 158, 11, 0.1);">
                        <i class="bi bi-hourglass-split" style="color: #f59e0b;"></i>
                    </div>
                    <span class="trend-pill trend-neutral">
                        <i class="bi bi-dash"></i> Action needed
                    </span>
                </div>
                <h6 class="text-muted fw-semibold small mb-1">Pending Invoices</h6>
                <h3 class="fw-bold mb-0 text-dark counter" data-target="{{ $stats['pending_invoices'] }}">0</h3>
                <small class="text-muted mt-1 d-block">Awaiting payment</small>
            </div>
        </div>

        <div class="col-md-6 col-xl-3 fade-in-up d-5">
            <div class="premium-card kpi-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box" style="background: rgba(239, 68, 68, 0.1);">
                        <i class="bi bi-exclamation-triangle" style="color: #ef4444;"></i>
                    </div>
                    <span class="trend-pill {{ $stats['low_stock_count'] > 0 ? 'trend-down' : 'trend-up' }}">
                        {{ $stats['low_stock_count'] > 0 ? 'Alert' : 'Healthy' }}
                    </span>
                </div>
                <h6 class="text-muted fw-semibold small mb-1">Low Stock Items</h6>
                <h3 class="fw-bold mb-0 text-{{ $stats['low_stock_count'] > 0 ? 'danger' : 'success' }} counter" data-target="{{ $stats['low_stock_count'] }}">0</h3>
                <small class="text-muted mt-1 d-block">Below reorder level</small>
            </div>
        </div>
    </div>

    {{-- ==================== MAIN CONTENT GRID ==================== --}}
    <div class="row g-4 mb-4">
        {{-- Revenue Trend Chart --}}
        <div class="col-lg-8 fade-in-up d-3">
            <div class="premium-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Revenue Trend</h6>
                        <small class="text-muted">Last 7 days performance</small>
                    </div>
                    <span class="badge bg-light text-dark border">Daily</span>
                </div>
                <div style="height: 260px;">
                    <canvas id="revenueTrendChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Pending Actions --}}
        <div class="col-lg-4 fade-in-up d-4">
            <div class="premium-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-dark">Needs Attention</h6>
                    @if(count($pendingItems) > 0)
                        <span class="badge bg-danger bg-opacity-10 text-danger">{{ count($pendingItems) }}</span>
                    @endif
                </div>
                <div class="custom-scroll" style="max-height: 280px; overflow-y: auto;">
                    @forelse($pendingItems as $item)
                        <a href="{{ $item['link'] }}" class="pending-item mb-2 d-block">
                            <div class="activity-icon" style="background: rgba({{ $item['color'] === 'warning' ? '245,158,11' : ($item['color'] === 'info' ? '14,165,233' : '239,68,68') }}, 0.1); color: {{ $item['color'] === 'warning' ? '#f59e0b' : ($item['color'] === 'info' ? '#0ea5e9' : '#ef4444') }};">
                                <i class="bi {{ $item['icon'] }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold small text-dark">{{ $item['title'] }}</div>
                                <small class="text-muted">Click to review →</small>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-2"></i>
                            <p class="text-muted small mb-0">All caught up! No pending items.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== SECONDARY ROW ==================== --}}
    <div class="row g-4 mb-4">
        {{-- Recent Orders --}}
        <div class="col-lg-8 fade-in-up d-4">
            <div class="premium-card">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt me-2 text-primary"></i>Recent Orders</h6>
                    <a href="{{ route('operations.orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 small text-uppercase text-muted fw-bold">Order</th>
                                    <th class="small text-uppercase text-muted fw-bold">Customer</th>
                                    <th class="small text-uppercase text-muted fw-bold">Date</th>
                                    <th class="text-end small text-uppercase text-muted fw-bold">Amount</th>
                                    <th class="text-center small text-uppercase text-muted fw-bold">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                    <tr>
                                        <td class="ps-4 fw-semibold text-primary small">{{ $order->order_no }}</td>
                                        <td class="small">{{ $order->customer->name ?? 'Walk-in Customer' }}</td>
                                        <td class="text-muted small">{{ \Carbon\Carbon::parse($order->order_date)->format('d M Y') }}</td>
                                        <td class="text-end fw-bold small">₹{{ number_format($order->total, 0) }}</td>
                                        <td class="text-center">
                                            @php
                                                $colors = [
                                                    'pending' => 'warning',
                                                    'processing' => 'info',
                                                    'shipped' => 'primary',
                                                    'delivered' => 'success'
                                                ];
                                                $color = $colors[$order->status] ?? 'secondary';
                                            @endphp
                                            <span class="status-pill bg-{{ $color }} bg-opacity-10 text-{{ $color }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            No recent orders yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Products --}}
        <div class="col-lg-4 fade-in-up d-5">
            <div class="premium-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-trophy me-2 text-warning"></i>Top Products</h6>
                </div>
                <div>
                    @forelse($topProducts as $index => $prod)
                        <div class="d-flex align-items-center gap-3 mb-3 pb-3 {{ $loop->last ? '' : 'border-bottom' }}">
                            <div class="fw-bold {{ $index === 0 ? 'text-warning' : ($index === 1 ? 'text-secondary' : 'text-muted') }}" style="width: 24px; text-align: center;">
                                @if($index == 0) <i class="bi bi-trophy-fill"></i>
                                @elseif($index == 1) <i class="bi bi-award-fill"></i>
                                @elseif($index == 2) <i class="bi bi-award"></i>
                                @else {{ $index + 1 }}
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold small text-dark text-truncate">{{ $prod['name'] }}</div>
                                <small class="text-muted">{{ $prod['qty'] }} sold</small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold small text-success">₹{{ number_format($prod['revenue'], 0) }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-box-seam fs-2 d-block mb-2"></i>
                            <small>No sales data yet.</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== BOTTOM ROW ==================== --}}
    <div class="row g-4">
        {{-- Low Stock Alerts --}}
        <div class="col-lg-4 fade-in-up d-5">
            <div class="premium-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-exclamation-octagon me-2 text-danger"></i>Stock Alerts</h6>
                    <a href="{{ route('operations.inventory.index') }}" class="btn btn-sm btn-outline-danger">View All</a>
                </div>
                <div class="custom-scroll" style="max-height: 300px; overflow-y: auto;">
                    @forelse($lowStockProducts as $product)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <div class="fw-semibold small text-dark">{{ $product->name }}</div>
                                <small class="text-muted">{{ $product->sku }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-danger bg-opacity-10 text-danger fw-bold">
                                    {{ $product->stock_qty }} left
                                </span>
                                <div class="text-muted" style="font-size: 0.7rem;">Min: {{ $product->reorder_level }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-2"></i>
                            <small class="text-muted">All stock levels are healthy!</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="col-lg-4 fade-in-up d-6">
            <div class="premium-card p-4 h-100">
                <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-lightning-charge me-2 text-warning"></i>Quick Actions</h6>
                <div class="row g-2">
                    <div class="col-6">
                        <a href="{{ route('finance-hr.pos.index') }}" class="action-btn">
                            <i class="bi bi-upc-scan text-primary"></i>
                            <small class="fw-semibold">POS Sale</small>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('finance-hr.finance.index') }}" class="action-btn">
                            <i class="bi bi-file-earmark-plus text-success"></i>
                            <small class="fw-semibold">New Invoice</small>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('operations.inventory.index') }}" class="action-btn">
                            <i class="bi bi-box-seam text-info"></i>
                            <small class="fw-semibold">Inventory</small>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('finance-hr.crm.index', ['tab' => 'leads']) }}" class="action-btn">
                            <i class="bi bi-person-plus text-warning"></i>
                            <small class="fw-semibold">New Lead</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="col-lg-4 fade-in-up d-6">
            <div class="premium-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-activity me-2 text-info"></i>Recent Activity</h6>
                    <a href="{{ route('admin.audit.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
                </div>
                <div class="custom-scroll" style="max-height: 300px; overflow-y: auto;">
                    @forelse($recentActivity as $log)
                        <div class="activity-item">
                            @php
                                $colors = ['created' => 'success', 'updated' => 'info', 'deleted' => 'danger', 'login' => 'primary'];
                                $color = $colors[$log->action] ?? 'secondary';
                                $colorRgb = ['success' => '16,185,129', 'info' => '14,165,233', 'danger' => '239,68,68', 'primary' => '99,102,241', 'secondary' => '100,116,139'][$color];
                            @endphp
                            <div class="activity-icon" style="background: rgba({{ $colorRgb }}, 0.1); color: var(--bs-{{ $color }});">
                                <i class="bi bi-{{ $log->action === 'created' ? 'plus' : ($log->action === 'updated' ? 'pencil' : ($log->action === 'deleted' ? 'trash' : 'person')) }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small">
                                    <span class="fw-semibold text-dark">{{ $log->user->name ?? 'System' }}</span>
                                    <span class="text-muted">{{ $log->action }}</span>
                                    @if($log->model_type)
                                        <span class="fw-semibold text-dark">{{ class_basename($log->model_type) }}</span>
                                    @endif
                                </div>
                                <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-clock-history fs-2 d-block mb-2"></i>
                            <small>No recent activity.</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // ==================== ANIMATED COUNTERS ====================
    document.querySelectorAll('.counter').forEach(counter => {
        const target = parseFloat(counter.getAttribute('data-target'));
        const isRevenue = target > 1000;
        const duration = 1200;
        const step = target / (duration / 16);
        let current = 0;
        
        const updateCounter = () => {
            current += step;
            if (current < target) {
                counter.innerText = (isRevenue ? '₹' : '') + Math.ceil(current).toLocaleString('en-IN');
                requestAnimationFrame(updateCounter);
            } else {
                counter.innerText = (isRevenue ? '₹' : '') + target.toLocaleString('en-IN');
            }
        };
        updateCounter();
    });

    // ==================== REVENUE TREND CHART ====================
    const trendData = @json($revenueTrend);
    const ctx = document.getElementById('revenueTrendChart').getContext('2d');

    // Create gradient
    const gradient = ctx.createLinearGradient(0, 0, 0, 260);
    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.25)');
    gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');

    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.color = '#94a3b8';

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendData.labels,
            datasets: [{
                label: 'Revenue',
                data: trendData.data,
                borderColor: '#6366f1',
                backgroundColor: gradient,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#6366f1',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: { weight: '600', size: 13 },
                    bodyFont: { size: 12 },
                    callbacks: {
                        label: function(ctx) {
                            return '₹' + ctx.parsed.y.toLocaleString('en-IN');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                    ticks: {
                        callback: function(value) {
                            if (value >= 100000) return '₹' + (value/100000).toFixed(1) + 'L';
                            if (value >= 1000) return '₹' + (value/1000).toFixed(0) + 'K';
                            return '₹' + value;
                        },
                        padding: 10,
                        font: { size: 11 }
                    }
                },
                x: {
                    border: { display: false },
                    grid: { display: false },
                    ticks: { padding: 10, font: { size: 11, weight: '500' } }
                }
            }
        }
    });
</script>
@endpush