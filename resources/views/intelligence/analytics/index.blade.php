@extends('layouts.master')
@section('title', 'Analytics')
@section('page-title', 'Business Intelligence')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    {{-- ✅ FIX: Pass all data via hidden JSON script tags (no @json in JS) --}}
    <script id="chart-data" type="application/json">
        {!! json_encode([
            'labels' => $labels,
            'invoiceData' => $invoiceData,
            'posData' => $posData,
            'paymentMethods' => $paymentMethods,
            'expenseByCategory' => $expenseByCategory,
            'topProducts' => $topProducts,
        ]) !!}
    </script>

    <style>
        /* ==================== DYNAMIC UI STYLES ==================== */
        :root {
            --c-primary: #6366f1;
            --c-success: #10b981;
            --c-warning: #f59e0b;
            --c-danger:  #ef4444;
            --c-slate:   #64748b;
            --c-bg:      #f8fafc;
        }

        .bento-card {
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.04);
            border-radius: 16px;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .bento-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px -8px rgba(0,0,0,0.08);
            border-color: rgba(0,0,0,0.08);
        }

        .bento-card .card-body { flex: 1; display: flex; flex-direction: column; }

        /* Staggered Fade-In Animation */
        .fade-in-up {
            opacity: 0;
            animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        .delay-5 { animation-delay: 0.5s; }

        /* KPI Trend Pills */
        .trend-pill {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .trend-up { background: rgba(16, 185, 129, 0.1); color: #059669; }
        .trend-down { background: rgba(239, 68, 68, 0.1); color: #dc2626; }

        /* Leaderboard styling */
        .leaderboard-item {
            transition: all 0.2s ease;
            border-radius: 12px;
            padding: 12px;
        }
        .leaderboard-item:hover {
            background: var(--c-bg);
        }

        /* Custom Scrollbar for lists */
        .custom-scroll::-webkit-scrollbar { width: 4px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

        /* Icon Box */
        .icon-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }
    </style>

    {{-- ==================== HEADER & FILTERS ==================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4 fade-in-up">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Performance Overview</h4>
            <p class="text-muted mb-0 small">Track your revenue, expenses, and growth metrics in real-time.</p>
        </div>
        <form method="GET" action="{{ route('intelligence.analytics.index') }}">
            <select name="months" class="form-select form-select-sm border-0 bg-white shadow-sm fw-semibold text-muted" style="width: 160px; border-radius: 10px;" onchange="this.form.submit()">
                <option value="3" {{ $months == 3 ? 'selected' : '' }}>Last 3 Months</option>
                <option value="6" {{ $months == 6 ? 'selected' : '' }}>Last 6 Months</option>
                <option value="12" {{ $months == 12 ? 'selected' : '' }}>Last 12 Months</option>
                <option value="24" {{ $months == 24 ? 'selected' : '' }}>Last 24 Months</option>
            </select>
        </form>
    </div>

    {{-- ==================== KPI CARDS (ROW 1) ==================== --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3 fade-in-up delay-1">
            <div class="bento-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box" style="background: rgba(99, 102, 241, 0.1);">
                        <i class="bi bi-currency-rupee" style="color: var(--c-primary);"></i>
                    </div>
                    <span class="trend-pill {{ $stats['revenue_growth'] >= 0 ? 'trend-up' : 'trend-down' }}">
                        <i class="bi bi-arrow-{{ $stats['revenue_growth'] >= 0 ? 'up-right' : 'down-right' }}"></i>
                        {{ abs($stats['revenue_growth']) }}%
                    </span>
                </div>
                <h6 class="text-muted fw-semibold small mb-1">Total Revenue</h6>
                <h3 class="fw-bold mb-0 text-dark counter" data-target="{{ $stats['revenue'] }}">₹0</h3>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3 fade-in-up delay-2">
            <div class="bento-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box" style="background: rgba(239, 68, 68, 0.1);">
                        <i class="bi bi-receipt" style="color: var(--c-danger);"></i>
                    </div>
                    <span class="trend-pill {{ $stats['expense_growth'] <= 0 ? 'trend-up' : 'trend-down' }}">
                        <i class="bi bi-arrow-{{ $stats['expense_growth'] <= 0 ? 'down-right' : 'up-right' }}"></i>
                        {{ abs($stats['expense_growth']) }}%
                    </span>
                </div>
                <h6 class="text-muted fw-semibold small mb-1">Total Expenses</h6>
                <h3 class="fw-bold mb-0 text-dark counter" data-target="{{ $stats['expenses'] }}">₹0</h3>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 fade-in-up delay-3">
            <div class="bento-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box" style="background: rgba(16, 185, 129, 0.1);">
                        <i class="bi bi-graph-up-arrow" style="color: var(--c-success);"></i>
                    </div>
                    <span class="trend-pill trend-up">
                        {{ $stats['profit_margin'] }}% margin
                    </span>
                </div>
                <h6 class="text-muted fw-semibold small mb-1">Net Profit</h6>
                <h3 class="fw-bold mb-0 text-success counter" data-target="{{ $stats['profit'] }}">₹0</h3>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 fade-in-up delay-4">
            <div class="bento-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box" style="background: rgba(245, 158, 11, 0.1);">
                        <i class="bi bi-people" style="color: var(--c-warning);"></i>
                    </div>
                    <span class="trend-pill" style="background: rgba(100,116,139,0.1); color: var(--c-slate);">
                        <i class="bi bi-plus"></i> {{ $stats['new_customers'] }} new
                    </span>
                </div>
                <h6 class="text-muted fw-semibold small mb-1">Total Orders</h6>
                <h3 class="fw-bold mb-0 text-dark counter" data-target="{{ $stats['orders'] }}">0</h3>
            </div>
        </div>
    </div>

    {{-- ==================== MAIN CHARTS (ROW 2) ==================== --}}
    <div class="row g-4 mb-4">
        {{-- Revenue Trend (Large) --}}
        <div class="col-lg-8 fade-in-up delay-3">
            <div class="bento-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0 text-dark">Revenue Trend</h6>
                    <div class="d-flex gap-3 small fw-semibold text-muted">
                        <span><span class="d-inline-block rounded-circle me-1" style="width:8px; height:8px; background: var(--c-success);"></span> Invoices</span>
                        <span><span class="d-inline-block rounded-circle me-1" style="width:8px; height:8px; background: var(--c-primary);"></span> POS</span>
                    </div>
                </div>
                <div class="flex-grow-1" style="min-height: 300px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Payment Methods (Small) --}}
        <div class="col-lg-4 fade-in-up delay-4">
            <div class="bento-card p-4">
                <h6 class="fw-bold mb-4 text-dark">Payment Channels</h6>
                <div class="flex-grow-1 d-flex align-items-center justify-content-center" style="min-height: 200px;">
                    <canvas id="paymentChart"></canvas>
                </div>
                <div class="mt-4 pt-3 border-top">
                    @foreach($paymentMethods as $pm)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted fw-semibold">{{ $pm['method'] }}</span>
                            <span class="small fw-bold text-dark">₹{{ number_format($pm['total'], 0) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== SECONDARY CHARTS (ROW 3) ==================== --}}
    <div class="row g-4">
        {{-- Top Products --}}
        <div class="col-lg-4 fade-in-up delay-3">
            <div class="bento-card p-4">
                <h6 class="fw-bold mb-4 text-dark">Top Products</h6>
                <div class="flex-grow-1" style="min-height: 250px;">
                    <canvas id="productChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Expenses --}}
        <div class="col-lg-4 fade-in-up delay-4">
            <div class="bento-card p-4">
                <h6 class="fw-bold mb-4 text-dark">Expense Breakdown</h6>
                <div class="flex-grow-1" style="min-height: 250px;">
                    <canvas id="expenseChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Top Customers Leaderboard --}}
        <div class="col-lg-4 fade-in-up delay-5">
            <div class="bento-card p-4">
                <h6 class="fw-bold mb-3 text-dark">Top Customers</h6>
                <div class="flex-grow-1 custom-scroll" style="max-height: 320px; overflow-y: auto;">
                    @php $totalTopRevenue = $topCustomers->sum('revenue'); @endphp
                    @forelse($topCustomers as $index => $cust)
                        <div class="leaderboard-item d-flex align-items-center gap-3 mb-2">
                            <div class="fw-bold text-muted" style="width: 20px;">
                                @if($index == 0) <i class="bi bi-trophy-fill text-warning"></i>
                                @elseif($index == 1) <i class="bi bi-award-fill text-secondary"></i>
                                @elseif($index == 2) <i class="bi bi-award-fill" style="color: #cd7f32;"></i>
                                @else <span class="small">{{ $index + 1 }}</span>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold small text-dark mb-1">{{ $cust['customer']->name ?? 'Unknown' }}</div>
                                <div class="progress" style="height: 4px; border-radius: 10px;">
                                    @php $pct = $totalTopRevenue > 0 ? ($cust['revenue'] / $totalTopRevenue) * 100 : 0; @endphp
                                    <div class="progress-bar" style="width: {{ $pct }}%; background: var(--c-primary); border-radius: 10px;"></div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold small text-dark">₹{{ number_format($cust['revenue'], 0) }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">{{ $cust['orders'] }} orders</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted small">No customer data yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // ✅ FOOLPROOF LARAVEL APPROACH (One variable per line prevents parser errors)
    const labels = @json($labels);
    const invoiceData = @json($invoiceData);
    const posData = @json($posData);
    const paymentData = @json($paymentMethods);
    const expenseData = @json($expenseByCategory);
    const productData = @json($topProducts);

    // ==================== ANIMATED COUNTERS ====================
    document.querySelectorAll('.counter').forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const duration = 1500;
        const step = target / (duration / 16);
        let current = 0;
        
        const updateCounter = () => {
            current += step;
            if (current < target) {
                counter.innerText = (target > 1000 ? '₹' : '') + Math.ceil(current).toLocaleString('en-IN');
                requestAnimationFrame(updateCounter);
            } else {
                counter.innerText = (target > 1000 ? '₹' : '') + target.toLocaleString('en-IN');
            }
        };
        updateCounter();
    });

    // ==================== CHART.JS GLOBAL CONFIG ====================
    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.plugins.legend.display = false;
    Chart.defaults.plugins.tooltip.backgroundColor = '#1e293b';
    Chart.defaults.plugins.tooltip.padding = 12;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
    Chart.defaults.plugins.tooltip.titleFont = { weight: '600', size: 13 };
    Chart.defaults.plugins.tooltip.bodyFont = { size: 12 };

    // ==================== 1. REVENUE TREND ====================
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    
    const gradientInvoice = revenueCtx.createLinearGradient(0, 0, 0, 300);
    gradientInvoice.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
    gradientInvoice.addColorStop(1, 'rgba(16, 185, 129, 0)');

    const gradientPos = revenueCtx.createLinearGradient(0, 0, 0, 300);
    gradientPos.addColorStop(0, 'rgba(99, 102, 241, 0.2)');
    gradientPos.addColorStop(1, 'rgba(99, 102, 241, 0)');

    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Invoices',
                    data: invoiceData,
                    borderColor: '#10b981',
                    backgroundColor: gradientInvoice,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#10b981',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3,
                },
                {
                    label: 'POS',
                    data: posData,
                    borderColor: '#6366f1',
                    backgroundColor: gradientPos,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#6366f1',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: { color: 'rgba(0,0,0,0.03)', drawBorder: false },
                    ticks: { 
                        callback: function(v) { return '₹' + (v >= 100000 ? (v/100000).toFixed(1)+'L' : (v/1000).toFixed(0)+'K'); },
                        padding: 10
                    }
                },
                x: {
                    border: { display: false },
                    grid: { display: false },
                    ticks: { padding: 10 }
                }
            }
        }
    });

    // ==================== 2. PAYMENT CHANNELS ====================
    new Chart(document.getElementById('paymentChart'), {
        type: 'doughnut',
        data: {
            labels: paymentData.map(function(p) { return p.method; }),
            datasets: [{
                data: paymentData.map(function(p) { return p.total; }),
                backgroundColor: ['#10b981', '#6366f1', '#f59e0b', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 10,
                spacing: 4,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                tooltip: {
                    callbacks: { 
                        label: function(ctx) { return ctx.label + ': ₹' + ctx.parsed.toLocaleString('en-IN'); } 
                    }
                }
            }
        }
    });

    // ==================== 3. TOP PRODUCTS ====================
    new Chart(document.getElementById('productChart'), {
        type: 'bar',
        data: {
            labels: productData.map(function(p) { return p.name.length > 15 ? p.name.substring(0,15)+'...' : p.name; }),
            datasets: [{
                data: productData.map(function(p) { return p.revenue; }),
                backgroundColor: 'rgba(245, 158, 11, 0.8)',
                borderRadius: 8,
                borderSkipped: false,
                barThickness: 20
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: { color: 'rgba(0,0,0,0.03)' },
                    ticks: { callback: function(v) { return '₹' + (v/1000).toFixed(0) + 'K'; } }
                },
                y: {
                    border: { display: false },
                    grid: { display: false }
                }
            }
        }
    });

    // ==================== 4. EXPENSES ====================
    const expColors = { 
        'primary': '#6366f1', 'success': '#10b981', 'warning': '#f59e0b', 
        'danger': '#ef4444', 'info': '#0ea5e9', 'secondary': '#64748b', 'dark': '#1e293b' 
    };

    new Chart(document.getElementById('expenseChart'), {
        type: 'bar',
        data: {
            labels: expenseData.map(function(e) { return e.name; }),
            datasets: [{
                data: expenseData.map(function(e) { return e.total; }),
                backgroundColor: expenseData.map(function(e) { return expColors[e.color] || '#94a3b8'; }),
                borderRadius: 8,
                borderSkipped: false,
                barThickness: 24
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: { color: 'rgba(0,0,0,0.03)' },
                    ticks: { callback: function(v) { return '₹' + (v/1000).toFixed(0) + 'K'; } }
                },
                x: {
                    border: { display: false },
                    grid: { display: false }
                }
            }
        }
    });
</script>
@endpush