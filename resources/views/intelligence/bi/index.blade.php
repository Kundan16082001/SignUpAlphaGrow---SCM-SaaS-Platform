@extends('layouts.master')
@section('title', 'BI Dashboard')
@section('page-title', 'Business Intelligence')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        /* ==================== PREMIUM DESIGN SYSTEM ==================== */
        :root {
            --c-indigo: #6366f1;
            --c-emerald: #10b981;
            --c-amber: #f59e0b;
            --c-rose: #ef4444;
            --c-sky: #0ea5e9;
            --c-slate-900: #0f172a;
            --c-slate-800: #1e293b;
            --c-slate-700: #334155;
            --c-slate-500: #64748b;
            --c-slate-100: #f1f5f9;
            --c-slate-50: #f8fafc;
        }

        /* Premium Card with Glass Effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            box-shadow: 
                0 1px 3px rgba(0, 0, 0, 0.04),
                0 8px 24px -4px rgba(0, 0, 0, 0.04);
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
            position: relative;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.8), transparent);
        }

        .glass-card:hover {
            transform: translateY(-6px);
            box-shadow: 
                0 4px 12px rgba(0, 0, 0, 0.05),
                0 20px 40px -12px rgba(0, 0, 0, 0.12);
        }

        /* Dark Premium Card */
        .dark-premium {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .dark-premium::before {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        }

        /* Animated Stagger */
        .animate-in {
            opacity: 0;
            transform: translateY(30px);
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes slideUp {
            to { opacity: 1; transform: translateY(0); }
        }
        .d-1 { animation-delay: 0.05s; }
        .d-2 { animation-delay: 0.15s; }
        .d-3 { animation-delay: 0.25s; }
        .d-4 { animation-delay: 0.35s; }
        .d-5 { animation-delay: 0.45s; }
        .d-6 { animation-delay: 0.55s; }

        /* Gradient Orbs (Background Decoration) */
        .gradient-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.4;
            pointer-events: none;
            z-index: 0;
        }

        /* Icon Container */
        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            position: relative;
            z-index: 1;
        }

        /* Pulse Dot */
        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            position: relative;
            display: inline-block;
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

        /* Funnel Step */
        .funnel-step {
            position: relative;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .funnel-step:hover {
            transform: translateX(4px);
        }
        .funnel-step::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            border-radius: 0 4px 4px 0;
        }

        /* Insight Card */
        .insight-card {
            border-radius: 16px;
            padding: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.04);
            background: white;
        }
        .insight-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
        }
        .insight-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px -8px rgba(0,0,0,0.1);
        }
        .insight-danger::before { background: linear-gradient(180deg, #ef4444, #dc2626); }
        .insight-warning::before { background: linear-gradient(180deg, #f59e0b, #d97706); }
        .insight-info::before { background: linear-gradient(180deg, #0ea5e9, #0284c7); }
        .insight-success::before { background: linear-gradient(180deg, #10b981, #059669); }

        /* Stat Pill */
        .stat-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .pill-up { background: rgba(16, 185, 129, 0.1); color: #059669; }
        .pill-down { background: rgba(239, 68, 68, 0.1); color: #dc2626; }
        .pill-neutral { background: rgba(100, 116, 139, 0.1); color: #475569; }

        /* Section Header */
        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .section-header .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--c-indigo);
        }
        .section-header h6 {
            margin: 0;
            font-weight: 700;
            color: var(--c-slate-900);
            letter-spacing: -0.3px;
        }

        /* Live Badge */
        .live-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #059669;
        }
    </style>

    {{-- Background Gradient Orbs --}}
    <div class="gradient-orb" style="width: 400px; height: 400px; background: #6366f1; top: -100px; right: -100px;"></div>
    <div class="gradient-orb" style="width: 300px; height: 300px; background: #10b981; bottom: 100px; left: -100px;"></div>

    {{-- ==================== HEADER ==================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4 animate-in d-1" style="position: relative; z-index: 1;">
        <div>
            <h4 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.5px;">Executive Overview</h4>
            <p class="text-muted mb-0 small">Real-time business health, targets & actionable insights.</p>
        </div>
        <div class="live-badge">
            <span class="pulse-dot"></span>
            Live Data
        </div>
    </div>

    {{-- ==================== NORTH STAR METRICS ==================== --}}
    <div class="row g-4 mb-4" style="position: relative; z-index: 1;">
        
        {{-- Revenue Target Gauge (Premium) --}}
        <div class="col-md-6 col-lg-3 animate-in d-1">
            <div class="glass-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <small class="text-uppercase fw-bold text-muted" style="font-size: 0.68rem; letter-spacing: 1.2px;">Revenue Target</small>
                    <span class="stat-pill {{ $targetAchievement >= 100 ? 'pill-up' : 'pill-neutral' }}">
                        {{ $targetAchievement }}%
                    </span>
                </div>
                <div class="d-flex align-items-center justify-content-center my-3" style="height: 140px; position: relative;">
                    <canvas id="targetGauge"></canvas>
                    <div style="position: absolute; text-align: center;">
                        <div class="fw-bold text-dark" style="font-size: 1.6rem; letter-spacing: -1px;">
                            ₹{{ number_format($totalRevenue / 100000, 1) }}L
                        </div>
                        <small class="text-muted" style="font-size: 0.7rem;">of ₹{{ number_format($revenueTarget / 100000, 1) }}L</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Conversion Rate --}}
        <div class="col-md-6 col-lg-3 animate-in d-2">
            <div class="glass-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box" style="background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(99,102,241,0.05));">
                        <i class="bi bi-funnel-fill" style="color: var(--c-indigo);"></i>
                    </div>
                </div>
                <small class="text-uppercase fw-bold text-muted d-block mb-2" style="font-size: 0.68rem; letter-spacing: 1.2px;">Lead Conversion</small>
                <h2 class="fw-bold mb-1 text-dark" style="letter-spacing: -1px;">{{ $conversionRate }}%</h2>
                <div class="d-flex align-items-center gap-2 mt-2">
                    <div class="flex-grow-1">
                        <div class="progress" style="height: 6px; border-radius: 10px; background: var(--c-slate-100);">
                            <div class="progress-bar" style="width: {{ $conversionRate }}%; background: linear-gradient(90deg, #6366f1, #8b5cf6); border-radius: 10px; transition: width 1.5s ease;"></div>
                        </div>
                    </div>
                    <small class="text-muted fw-semibold" style="font-size: 0.72rem;">{{ $wonLeads }}/{{ $totalLeads }}</small>
                </div>
            </div>
        </div>

        {{-- Inventory Health --}}
        <div class="col-md-6 col-lg-3 animate-in d-3">
            <div class="glass-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box" style="background: linear-gradient(135deg, rgba(16,185,129,0.15), rgba(16,185,129,0.05));">
                        <i class="bi bi-box-seam-fill" style="color: var(--c-emerald);"></i>
                    </div>
                    <span class="stat-pill {{ $inventoryHealth == 'Healthy' ? 'pill-up' : 'pill-down' }}">
                        {{ $inventoryHealth }}
                    </span>
                </div>
                <small class="text-uppercase fw-bold text-muted d-block mb-2" style="font-size: 0.68rem; letter-spacing: 1.2px;">Inventory Value</small>
                <h2 class="fw-bold mb-1 text-dark" style="letter-spacing: -1px;">₹{{ number_format($totalStockValue / 100000, 1) }}L</h2>
                <div class="d-flex gap-2 mt-2">
                    <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 0.68rem;">
                        <i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i>{{ $healthyStock }} Healthy
                    </span>
                    <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size: 0.68rem;">
                        <i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i>{{ $outOfStock }} Out
                    </span>
                </div>
            </div>
        </div>

        {{-- Smart Alerts (Dark Premium) --}}
        <div class="col-md-6 col-lg-3 animate-in d-4">
            <div class="glass-card dark-premium p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box" style="background: rgba(255,255,255,0.08);">
                        <i class="bi bi-bell-fill" style="color: #fbbf24;"></i>
                    </div>
                    <span class="stat-pill" style="background: rgba(251,191,36,0.15); color: #fbbf24;">
                        {{ count($insights) }} Active
                    </span>
                </div>
                <small class="text-uppercase fw-bold d-block mb-2 opacity-60" style="font-size: 0.68rem; letter-spacing: 1.2px;">Action Required</small>
                <h2 class="fw-bold mb-3" style="letter-spacing: -1px;">{{ count($insights) }}</h2>
                <a href="#insights-section" class="btn btn-sm w-100 fw-semibold" style="background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.15); backdrop-filter: blur(10px);">
                    Review Insights <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- ==================== MAIN BI VISUALIZATIONS ==================== --}}
    <div class="row g-4 mb-4" style="position: relative; z-index: 1;">
        
        {{-- Sales Funnel (True Funnel Visualization) --}}
        <div class="col-lg-7 animate-in d-3">
            <div class="glass-card p-4 h-100">
                <div class="section-header">
                    <span class="dot"></span>
                    <h6>Sales Pipeline Funnel</h6>
                </div>
                @php
                    $funnelMax = max(array_values($funnelData));
                    $funnelColors = [
                        'New Leads' => ['#e0e7ff', '#6366f1'],
                        'Contacted' => ['#c7d2fe', '#4f46e5'],
                        'Quoted' => ['#a5b4fc', '#4338ca'],
                        'Won' => ['#10b981', '#059669'],
                    ];
                    $funnelIcons = [
                        'New Leads' => 'bi-stars',
                        'Contacted' => 'bi-telephone-fill',
                        'Quoted' => 'bi-file-earmark-text-fill',
                        'Won' => 'bi-trophy-fill',
                    ];
                @endphp
                <div class="py-2">
                    @foreach($funnelData as $stage => $count)
                        @php 
                            $width = $funnelMax > 0 ? ($count / $funnelMax) * 100 : 0;
                            $colors = $funnelColors[$stage] ?? ['#e2e8f0', '#64748b'];
                            $icon = $funnelIcons[$stage] ?? 'bi-circle';
                        @endphp
                        <div class="funnel-step" style="background: {{ $colors[0] }}20;">
                            <div class="funnel-step" style="position: absolute; left: 0; top: 0; bottom: 0; width: {{ $width }}%; background: linear-gradient(90deg, {{ $colors[0] }}, {{ $colors[0] }}80); border-radius: 12px; transition: width 1s ease;"></div>
                            <div style="position: relative; z-index: 1; display: flex; align-items: center; gap: 12px;">
                                <div class="icon-box" style="width: 36px; height: 36px; font-size: 1rem; background: {{ $colors[1] }}20;">
                                    <i class="bi {{ $icon }}" style="color: {{ $colors[1] }};"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $stage }}</div>
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ round($width) }}% of pipeline</small>
                                </div>
                            </div>
                            <div style="position: relative; z-index: 1; text-align: right;">
                                <div class="fw-bold" style="font-size: 1.3rem; color: {{ $colors[1] }}; letter-spacing: -0.5px;">{{ $count }}</div>
                                <small class="text-muted" style="font-size: 0.68rem;">leads</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Inventory Distribution --}}
        <div class="col-lg-5 animate-in d-4">
            <div class="glass-card p-4 h-100">
                <div class="section-header">
                    <span class="dot" style="background: var(--c-emerald);"></span>
                    <h6>Stock Health Distribution</h6>
                </div>
                <div class="d-flex align-items-center" style="min-height: 240px;">
                    <div style="width: 55%; position: relative;">
                        <canvas id="inventoryChart"></canvas>
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                            <div class="fw-bold text-dark" style="font-size: 1.4rem; letter-spacing: -0.5px;">{{ $healthyStock + $lowStock + $outOfStock }}</div>
                            <small class="text-muted" style="font-size: 0.68rem;">Total SKUs</small>
                        </div>
                    </div>
                    <div style="width: 45%; padding-left: 16px;">
                        @php
                            $total = max(1, $healthyStock + $lowStock + $outOfStock);
                            $invItems = [
                                ['label' => 'Healthy', 'count' => $healthyStock, 'color' => '#10b981', 'pct' => round(($healthyStock/$total)*100)],
                                ['label' => 'Low Stock', 'count' => $lowStock, 'color' => '#f59e0b', 'pct' => round(($lowStock/$total)*100)],
                                ['label' => 'Out of Stock', 'count' => $outOfStock, 'color' => '#ef4444', 'pct' => round(($outOfStock/$total)*100)],
                            ];
                        @endphp
                        @foreach($invItems as $item)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $item['color'] }}; display: inline-block;"></span>
                                        <small class="fw-semibold text-dark" style="font-size: 0.78rem;">{{ $item['label'] }}</small>
                                    </div>
                                    <small class="fw-bold text-dark" style="font-size: 0.78rem;">{{ $item['count'] }}</small>
                                </div>
                                <div class="progress" style="height: 4px; background: var(--c-slate-100); border-radius: 10px;">
                                    <div style="width: {{ $item['pct'] }}%; height: 100%; background: {{ $item['color'] }}; border-radius: 10px; transition: width 1.5s ease;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== SMART INSIGHTS ==================== --}}
    <div id="insights-section" class="animate-in d-5" style="position: relative; z-index: 1;">
        <div class="section-header">
            <span class="dot" style="background: var(--c-amber);"></span>
            <h6>Smart Insights & Recommendations</h6>
            <small class="text-muted ms-auto" style="font-size: 0.75rem;">{{ count($insights) }} items need attention</small>
        </div>
        <div class="row g-3">
            @foreach($insights as $insight)
                <div class="col-md-6 col-lg-4 animate-in d-{{ min(6, $loop->iteration + 2) }}">
                    <div class="insight-card insight-{{ $insight['type'] }} h-100">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="icon-box" style="width: 40px; height: 40px; font-size: 1.1rem; background: 
                                {{ $insight['type'] == 'danger' ? 'rgba(239,68,68,0.1)' : 
                                   ($insight['type'] == 'warning' ? 'rgba(245,158,11,0.1)' : 
                                   ($insight['type'] == 'info' ? 'rgba(14,165,233,0.1)' : 'rgba(16,185,129,0.1)')) }};">
                                <i class="bi {{ $insight['icon'] }}" style="color: 
                                    {{ $insight['type'] == 'danger' ? '#ef4444' : 
                                       ($insight['type'] == 'warning' ? '#f59e0b' : 
                                       ($insight['type'] == 'info' ? '#0ea5e9' : '#10b981')) }};"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1 text-dark" style="font-size: 0.95rem;">{{ $insight['title'] }}</h6>
                                <p class="small text-muted mb-0" style="font-size: 0.82rem; line-height: 1.5;">{{ $insight['message'] }}</p>
                            </div>
                        </div>
                        <a href="{{ $insight['link'] }}" class="btn btn-sm w-100 fw-semibold" style="background: 
                            {{ $insight['type'] == 'danger' ? 'rgba(239,68,68,0.08)' : 
                               ($insight['type'] == 'warning' ? 'rgba(245,158,11,0.08)' : 
                               ($insight['type'] == 'info' ? 'rgba(14,165,233,0.08)' : 'rgba(16,185,129,0.08)')) }}; 
                            color: 
                            {{ $insight['type'] == 'danger' ? '#dc2626' : 
                               ($insight['type'] == 'warning' ? '#d97706' : 
                               ($insight['type'] == 'info' ? '#0284c7' : '#059669')) }}; 
                            border: none;">
                            {{ $insight['action'] }} <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
<script>
    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.color = '#64748b';

    // ============ REVENUE TARGET GAUGE ============
    const targetAchievement = {{ $targetAchievement }};
    const gaugeColor = targetAchievement >= 100 ? '#10b981' : (targetAchievement >= 70 ? '#6366f1' : (targetAchievement >= 40 ? '#f59e0b' : '#ef4444'));
    
    const gaugeCtx = document.getElementById('targetGauge').getContext('2d');
    const gaugeGradient = gaugeCtx.createLinearGradient(0, 0, 200, 0);
    gaugeGradient.addColorStop(0, '#6366f1');
    gaugeGradient.addColorStop(1, gaugeColor);

    new Chart(gaugeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Achieved', 'Remaining'],
            datasets: [{
                data: [Math.min(targetAchievement, 100), Math.max(0, 100 - Math.min(targetAchievement, 100))],
                backgroundColor: [gaugeGradient, '#f1f5f9'],
                borderWidth: 0,
                circumference: 240,
                rotation: 210,
                borderRadius: 20,
                spacing: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '78%',
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            },
            animation: {
                animateRotate: true,
                duration: 1500,
                easing: 'easeOutQuart'
            }
        }
    });

    // ============ INVENTORY DISTRIBUTION ============
    new Chart(document.getElementById('inventoryChart'), {
        type: 'doughnut',
        data: {
            labels: ['Healthy', 'Low', 'Out of Stock'],
            datasets: [{
                data: [{{ $healthyStock }}, {{ $lowStock }}, {{ $outOfStock }}],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 12,
                spacing: 6,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: { weight: '600', size: 13 },
                    bodyFont: { size: 12 }
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true,
                duration: 1500,
                easing: 'easeOutQuart'
            }
        }
    });
</script>
@endpush