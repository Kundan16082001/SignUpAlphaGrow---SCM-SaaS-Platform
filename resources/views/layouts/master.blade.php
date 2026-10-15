<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - SignupAlphaGrow</title>
    
    <!-- OFFLINE BOOTSTRAP CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <!-- BOOTSTRAP ICONS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- CUSTOM CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}?v=5">
    
    <style>
        /* ==================== ROOT VARIABLES ==================== */
        :root {
            --sidebar-bg: #0f172a;
            --sidebar-hover: rgba(255, 255, 255, 0.05);
            --sidebar-active-bg: rgba(251, 191, 36, 0.15);
            --sidebar-active-text: #fbbf24;
            --sidebar-text: #94a3b8;
            --sidebar-width: 280px;
            --main-bg: #f8fafc;
            --card-bg: #ffffff;
            --border-color: rgba(0, 0, 0, 0.06);
            --primary-color: #6366f1;
            --accent-color: #fbbf24;
        }

        /* ==================== BASE STYLES ==================== */
        body {
            background-color: var(--main-bg);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #334155;
            -webkit-font-smoothing: antialiased;
            margin: 0;
            padding: 0;
        }

        /* ==================== SIDEBAR ==================== */
        .sidebar {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            height: 100vh;
            width: var(--sidebar-width);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.1);
        }

        .sidebar-brand {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            flex-shrink: 0;
            text-align: center;
        }

        .sidebar-brand img {
            width: 56px;
            height: 56px;
            object-fit: contain;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px;
            border-radius: 12px;
            margin-bottom: 0.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .sidebar-brand h5 {
            color: #ffffff;
            margin-bottom: 0.25rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            font-size: 1.1rem;
        }

        .sidebar-brand small {
            color: var(--sidebar-text);
            text-transform: uppercase;
            font-size: 0.65rem;
            letter-spacing: 1.5px;
            font-weight: 600;
        }

        .sidebar-scroll {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 1rem 0;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.15) transparent;
        }

        .sidebar-scroll::-webkit-scrollbar { width: 6px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.15); border-radius: 10px; }

        .sidebar-group-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.65rem 1.25rem;
            color: #64748b;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 700;
            cursor: pointer;
            border-radius: 8px;
            margin: 0.5rem 0.75rem 0.25rem;
            transition: all 0.2s ease;
            text-decoration: none;
            user-select: none;
        }

        .sidebar-group-header:hover {
            background-color: var(--sidebar-hover);
            color: #cbd5e1;
        }

        .sidebar-group-header .chevron {
            transition: transform 0.3s ease;
            font-size: 0.7rem;
        }

        .sidebar-group-header[aria-expanded="true"] .chevron {
            transform: rotate(180deg);
        }

        .sidebar .nav-link {
            color: var(--sidebar-text);
            padding: 0.6rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 8px;
            margin: 0.25rem 0.75rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            border-left: 3px solid transparent;
            text-decoration: none;
        }

        .sidebar .nav-link:hover {
            background-color: var(--sidebar-hover);
            color: #e2e8f0;
            transform: translateX(2px);
        }

        .sidebar .nav-link.active {
            background-color: var(--sidebar-active-bg);
            color: var(--sidebar-active-text);
            border-left-color: var(--sidebar-active-text);
            margin-left: calc(0.75rem - 3px);
            font-weight: 600;
        }

        .sidebar .nav-link i {
            font-size: 1.1rem;
            margin-right: 0.75rem;
            opacity: 0.9;
            width: 22px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            flex-shrink: 0;
        }

        .sidebar-footer .nav-link {
            background: rgba(239, 68, 68, 0.08);
            color: #ef4444;
            border-radius: 8px;
            padding: 0.6rem 1rem;
            font-weight: 600;
            transition: all 0.2s ease;
            border: none;
            width: 100%;
            text-align: left;
        }

        .sidebar-footer .nav-link:hover {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            transform: translateY(-1px);
        }

        /* ==================== MAIN CONTENT ==================== */
        .main-content {
            margin-left: var(--sidebar-width);
            background-color: var(--main-bg);
            min-height: 100vh;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ✅ Full width for Setup/Guest pages */
        .main-content.full-width {
            margin-left: 0;
        }

        .topbar {
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 0 2rem;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 900;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .topbar h5 {
            color: #1e293b;
            font-size: 1.15rem;
            font-weight: 700;
            margin: 0;
        }

        /* ==================== PAGINATION ==================== */
        .pagination {
            margin: 1.5rem 0 0 0 !important;
            justify-content: center;
            gap: 4px;
        }

        .pagination .page-link {
            padding: 8px 14px !important;
            font-size: 0.875rem !important;
            color: #64748b !important;
            background-color: #ffffff !important;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 8px !important;
            font-weight: 500 !important;
            min-width: 38px !important;
            height: 38px !important;
            transition: all 0.2s ease !important;
        }

        .pagination .page-link:hover {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
            border-color: #6366f1 !important;
            color: #ffffff !important;
            font-weight: 600 !important;
        }

        .pagination .page-item.disabled .page-link {
            color: #cbd5e1 !important;
            background-color: #f8fafc !important;
            border-color: #e2e8f0 !important;
        }

        /* ==================== CARDS ==================== */
        .card {
            border: 1px solid var(--border-color);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            border-radius: 12px;
            background-color: var(--card-bg);
            transition: all 0.2s ease;
        }

        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* ==================== MOBILE RESPONSIVE ==================== */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .topbar { padding: 0 1rem; }
        }
    </style>
</head>
<body>

@php
    // ✅ Detect if we are on any guest/auth page (Setup, Signup, or Login) to hide sidebar/topbar
    $isGuestPage = request()->is('setup') || request()->is('signup') || request()->is('login') || 
                   request()->routeIs('setup.*') || request()->routeIs('signup.*') || request()->routeIs('login');
@endphp

@if(!$isGuestPage)
    <!-- Sidebar (Hidden on Setup, Signup, and Login pages) -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/logo.png') }}" alt="AlphaGrow Logo">
            <h5>AlphaGrow</h5>
            <small>{{ auth()->check() ? (auth()->user()->wing ?? 'System') : 'System' }} Wing</small>
        </div>

        <div class="sidebar-scroll">
            @php
                $isCore = request()->is('dashboard');
                $isOperations = request()->is('operations/*');
                $isScm = request()->is('scm/*') || request()->is('operations/warehouses*') || request()->is('operations/warehouse-transfers*');
                $isFinance = request()->is('finance-hr/*');
                $isIntelligence = request()->is('intelligence/*');
                $isAdmin = request()->is('admin/*') || request()->is('settings*');
            @endphp

            <!-- 1. Core System -->
            <a class="sidebar-group-header" data-bs-toggle="collapse" href="#collapseCore" role="button" aria-expanded="{{ $isCore ? 'true' : 'false' }}">
                <span>Core System</span>
                <i class="bi bi-chevron-down chevron"></i>
            </a>
            <div class="collapse {{ $isCore ? 'show' : '' }}" id="collapseCore">
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                </ul>
            </div>

            <!-- 2. Operations -->
            <a class="sidebar-group-header" data-bs-toggle="collapse" href="#collapseOperations" role="button" aria-expanded="{{ $isOperations ? 'true' : 'false' }}">
                <span>Operations</span>
                <i class="bi bi-chevron-down chevron"></i>
            </a>
            <div class="collapse {{ $isOperations ? 'show' : '' }}" id="collapseOperations">
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('operations/inventory*') ? 'active' : '' }}" href="{{ route('operations.inventory.index') }}">
                            <i class="bi bi-box-seam"></i> Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('operations/orders*') ? 'active' : '' }}" href="{{ route('operations.orders.index') }}">
                            <i class="bi bi-cart-check"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('operations/production*') ? 'active' : '' }}" href="{{ route('operations.production.index') }}">
                            <i class="bi bi-gear-wide-connected"></i> Production
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('operations/bom*') ? 'active' : '' }}" href="{{ route('operations.bom.index') }}">
                            <i class="bi bi-diagram-3"></i> BOM
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('operations/machines*') ? 'active' : '' }}" href="{{ route('operations.machines.index') }}">
                            <i class="bi bi-cpu"></i> Machines
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('operations/customers*') ? 'active' : '' }}" href="{{ route('operations.customers.index') }}">
                            <i class="bi bi-people"></i> Customers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('operations/logistics*') ? 'active' : '' }}" href="{{ route('operations.logistics.index') }}">
                            <i class="bi bi-truck"></i> Logistics
                        </a>
                    </li>
                </ul>
            </div>

            <!-- 3. Supply Chain (SCM) -->
            <a class="sidebar-group-header" data-bs-toggle="collapse" href="#collapseSCM" role="button" aria-expanded="{{ $isScm ? 'true' : 'false' }}">
                <span>Supply Chain</span>
                <i class="bi bi-chevron-down chevron"></i>
            </a>
            <div class="collapse {{ $isScm ? 'show' : '' }}" id="collapseSCM">
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('scm/gr-qr*') ? 'active' : '' }}" href="{{ route('scm.gr-qr.index') }}">
                            <i class="bi bi-clipboard2-check"></i> GR/QR Posting
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('operations/warehouses*') ? 'active' : '' }}" href="{{ route('operations.warehouses.index') }}">
                            <i class="bi bi-buildings"></i> Warehouses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('operations/warehouse-transfers*') ? 'active' : '' }}" href="{{ route('operations.warehouse-transfers.index') }}">
                            <i class="bi bi-arrow-left-right"></i> WH Transfers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('scm/warehouse-capacity*') ? 'active' : '' }}" href="{{ route('scm.warehouse-capacity.index') }}">
                            <i class="bi bi-building"></i> WH Capacity
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('scm/traceability*') ? 'active' : '' }}" href="{{ route('scm.traceability.index') }}">
                            <i class="bi bi-diagram-3"></i> Traceability
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('scm/returns*') ? 'active' : '' }}" href="{{ route('scm.returns.index') }}">
                            <i class="bi bi-arrow-counterclockwise"></i> Returns
                        </a>
                    </li>
                </ul>
            </div>

            <!-- 4. Finance & HR -->
            <a class="sidebar-group-header" data-bs-toggle="collapse" href="#collapseFinance" role="button" aria-expanded="{{ $isFinance ? 'true' : 'false' }}">
                <span>Finance & HR</span>
                <i class="bi bi-chevron-down chevron"></i>
            </a>
            <div class="collapse {{ $isFinance ? 'show' : '' }}" id="collapseFinance">
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('finance-hr/finance*') ? 'active' : '' }}" href="{{ route('finance-hr.finance.index') }}">
                            <i class="bi bi-cash-coin"></i> Finance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('finance-hr/procurement*') ? 'active' : '' }}" href="{{ route('finance-hr.procurement.index') }}">
                            <i class="bi bi-bag-check"></i> Procurement
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('finance-hr/expenses*') ? 'active' : '' }}" href="{{ route('finance-hr.expenses.index') }}">
                            <i class="bi bi-receipt"></i> Expenses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('finance-hr/pos*') ? 'active' : '' }}" href="{{ route('finance-hr.pos.index') }}">
                            <i class="bi bi-upc-scan"></i> POS
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('finance-hr/hr*') ? 'active' : '' }}" href="{{ route('finance-hr.hr.index') }}">
                            <i class="bi bi-people"></i> HR & Payroll
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('finance-hr/crm*') ? 'active' : '' }}" href="{{ route('finance-hr.crm.index') }}">
                            <i class="bi bi-person-lines-fill"></i> CRM
                        </a>
                    </li>
                </ul>
            </div>

            <!-- 5. Intelligence -->
            <a class="sidebar-group-header" data-bs-toggle="collapse" href="#collapseIntelligence" role="button" aria-expanded="{{ $isIntelligence ? 'true' : 'false' }}">
                <span>Intelligence</span>
                <i class="bi bi-chevron-down chevron"></i>
            </a>
            <div class="collapse {{ $isIntelligence ? 'show' : '' }}" id="collapseIntelligence">
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('intelligence/analytics*') ? 'active' : '' }}" href="{{ route('intelligence.analytics.index') }}">
                            <i class="bi bi-graph-up"></i> Analytics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('intelligence/bi*') ? 'active' : '' }}" href="{{ route('intelligence.bi.index') }}">
                            <i class="bi bi-pie-chart"></i> BI Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('intelligence/reports*') ? 'active' : '' }}" href="{{ route('intelligence.reports.index') }}">
                            <i class="bi bi-file-earmark-bar-graph"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('intelligence/dms*') ? 'active' : '' }}" href="{{ route('intelligence.dms.index') }}">
                            <i class="bi bi-folder2-open"></i> DMS
                        </a>
                    </li>
                </ul>
            </div>

            <!-- 6. Administration -->
            <a class="sidebar-group-header" data-bs-toggle="collapse" href="#collapseAdmin" role="button" aria-expanded="{{ $isAdmin ? 'true' : 'false' }}">
                <span>Administration</span>
                <i class="bi bi-chevron-down chevron"></i>
            </a>
            <div class="collapse {{ $isAdmin ? 'show' : '' }}" id="collapseAdmin">
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                            <i class="bi bi-person-gear"></i> User Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('admin/roles*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">
                            <i class="bi bi-shield-lock"></i> Roles & Permissions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('admin/audit*') ? 'active' : '' }}" href="{{ route('admin.audit.index') }}">
                            <i class="bi bi-journal-text"></i> Audit Trail
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('settings*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Logout at bottom -->
        @auth
        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
        @endauth
    </nav>
@endif

    <!-- Main Content -->
    <div class="main-content {{ $isGuestPage ? 'full-width' : '' }}">
        
        @if(!$isGuestPage)
        <!-- Topbar (Hidden on Setup, Signup, and Login pages) -->
        <header class="topbar">
            <!-- Mobile Sidebar Toggle -->
            <button class="btn btn-light d-md-none shadow-sm" onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>
            
            <!-- Page Title -->
            <h5 class="d-none d-md-block">@yield('page-title', 'Dashboard')</h5>
            
            <!-- Right Side: Notifications + User Info -->
            @auth
            <div class="d-flex align-items-center gap-3">
                {{-- 🔔 NOTIFICATION BELL --}}
                <div class="dropdown notification-dropdown">
                    <button class="btn btn-light position-relative rounded-circle d-flex align-items-center justify-content-center" 
                            type="button" data-bs-toggle="dropdown" 
                            style="width: 42px; height: 42px;"
                            id="notificationBell">
                        <i class="bi bi-bell" style="font-size: 1.2rem;"></i>
                        @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                            id="notificationBadge" 
                            style="font-size: 0.65rem; {{ $unreadCount == 0 ? 'display:none;' : '' }}">
                            {{ $unreadCount }}
                        </span>
                    </button>
                    
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" 
                        style="width: 380px; max-height: 500px; border-radius: 12px; padding: 0; overflow: hidden;">
                        
                        <li class="p-3 border-bottom bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0">Notifications</h6>
                                @if($unreadCount > 0)
                                    <form method="POST" action="{{ route('notifications.markAllRead') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-link text-primary p-0 text-decoration-none">
                                            Mark all as read
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </li>
                        
                        <li style="max-height: 400px; overflow-y: auto;">
                            @forelse(auth()->user()->notifications->take(8) as $notif)
                                <a href="{{ route('notifications.read', $notif->id) }}" 
                                class="dropdown-item p-3 border-bottom text-decoration-none {{ $notif->read_at ? '' : 'bg-primary bg-opacity-10' }}"
                                style="white-space: normal;">
                                    <div class="d-flex gap-2">
                                        @php
                                            $typeColors = [
                                                'success' => 'success', 'warning' => 'warning', 'danger' => 'danger',
                                                'info' => 'info', 'order' => 'primary', 'stock' => 'warning', 'payment' => 'success'
                                            ];
                                            $color = $typeColors[$notif->data['type'] ?? 'info'] ?? 'secondary';
                                        @endphp
                                        <div class="bg-{{ $color }} bg-opacity-10 text-{{ $color }} rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 36px; height: 36px;">
                                            <i class="bi {{ $notif->data['icon'] ?? 'bi-bell-fill' }}"></i>
                                        </div>
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <div class="fw-semibold small text-dark" style="font-size: 0.85rem;">
                                                {{ $notif->data['title'] ?? 'Notification' }}
                                            </div>
                                            <div class="text-muted" style="font-size: 0.75rem; line-height: 1.3;">
                                                {{ Str::limit($notif->data['message'] ?? '', 60) }}
                                            </div>
                                            <small class="text-muted" style="font-size: 0.7rem;">
                                                {{ $notif->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                        @if($notif->read_at === null)
                                            <span class="bg-primary rounded-circle flex-shrink-0" style="width: 8px; height: 8px; margin-top: 6px;"></span>
                                        @endif
                                    </div>
                                </a>
                            @empty
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-bell-slash" style="font-size: 2rem;"></i>
                                    <p class="small mt-2 mb-0">No notifications yet</p>
                                </div>
                            @endforelse
                        </li>
                        
                        <li class="p-2 border-top bg-light text-center">
                            <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary w-100">
                                View All Notifications
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- 👤 USER DROPDOWN --}}
                <span class="badge bg-light text-dark border text-uppercase fw-semibold d-none d-md-inline" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                    {{ auth()->user()->role ?? 'User' }}
                </span>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" 
                            style="width:38px; height:38px; font-size: 0.9rem; font-weight: 600;">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <span class="fw-semibold d-none d-md-inline" style="font-size: 0.9rem;">{{ auth()->user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius: 10px; margin-top: 8px;">
                        <li><a class="dropdown-item py-2" href="{{ route('settings.index') }}"><i class="bi bi-person me-2 text-muted"></i> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
            @endauth
        </header>
        @endif

        <!-- Page Content Area -->
        <div class="{{ $isGuestPage ? '' : 'p-4' }}">
            @yield('content')
        </div>
    </div>

    <!-- OFFLINE BOOTSTRAP JS -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    
    <!-- Page Specific Scripts Stack -->
    @stack('scripts')
</body>
</html>