<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - AlphaGrow</title>
    
    <!-- OFFLINE BOOTSTRAP CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <!-- BOOTSTRAP ICONS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- CUSTOM CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    
    <style>
        :root {
            --sidebar-bg: #1e2126;
            --sidebar-hover: rgba(255, 255, 255, 0.06);
            --sidebar-active-bg: rgba(217, 119, 6, 0.1); /* Vintage Amber tint */
            --sidebar-active-text: #fbbf24; /* Vintage Amber */
            --sidebar-text: #9ca3af;
            --main-bg: #f8f9fa; /* Warm off-white */
            --card-bg: #ffffff;
            --border-color: rgba(0, 0, 0, 0.06);
        }

        body {
            background-color: var(--main-bg);
            font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            color: #334155;
            -webkit-font-smoothing: antialiased;
        }

        /* --- Sidebar Styling --- */
        .sidebar {
            background-color: var(--sidebar-bg);
            height: 100vh; /* 🔑 FIXED: Changed from min-height to exact 100vh */
            width: 260px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            flex-direction: column;
            overflow: hidden; /* 🔑 FIXED: Prevents the whole sidebar from expanding */
        }

        .sidebar-brand {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            flex-shrink: 0; /* Prevents header from shrinking */
        }

        /* 🔑 FIXED: This is the ONLY part that scrolls */
        .sidebar-scroll {
            flex: 1; /* Takes up all remaining space */
            overflow-y: auto; /* Enables vertical scrolling */
            overflow-x: hidden;
            padding-bottom: 1rem;
            
            /* Elegant Custom Scrollbar */
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.15) transparent;
        }

        .sidebar-scroll::-webkit-scrollbar { 
            width: 5px; 
        }
        .sidebar-scroll::-webkit-scrollbar-track { 
            background: transparent; 
        }
        .sidebar-scroll::-webkit-scrollbar-thumb { 
            background: rgba(255,255,255,0.15); 
            border-radius: 10px; 
        }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.3);
        }

        /* Collapsible Group Headers */
        .sidebar-group-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.6rem 1.25rem;
            color: #64748b;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 700;
            cursor: pointer;
            border-radius: 6px;
            margin: 0 0.75rem 0.25rem;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .sidebar-group-header:hover {
            background-color: var(--sidebar-hover);
            color: #cbd5e1;
        }

        .sidebar-group-header .chevron {
            transition: transform 0.3s ease;
            font-size: 0.75rem;
        }

        .sidebar-group-header[aria-expanded="true"] .chevron {
            transform: rotate(180deg);
        }

        /* Nav Links */
        .sidebar .nav-link {
            color: var(--sidebar-text);
            padding: 0.55rem 1.25rem;
            font-size: 0.85rem;
            font-weight: 500;
            border-radius: 6px;
            margin: 2px 0.75rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            border-left: 3px solid transparent;
        }

        .sidebar .nav-link:hover {
            background-color: var(--sidebar-hover);
            color: #e2e8f0;
        }

        .sidebar .nav-link.active {
            background-color: var(--sidebar-active-bg);
            color: var(--sidebar-active-text);
            border-left-color: var(--sidebar-active-text);
            margin-left: calc(0.75rem - 3px);
        }

        .sidebar .nav-link i {
            font-size: 1rem;
            margin-right: 0.75rem;
            opacity: 0.8;
            width: 20px;
            text-align: center;
        }

        /* --- Main Content & Topbar --- */
        .main-content {
            margin-left: 260px;
            background-color: var(--main-bg);
            min-height: 100vh;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        /* Vintage Minimal Card Touch */
        .card {
            border: 1px solid var(--border-color);
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            border-radius: 8px;
            background-color: var(--card-bg);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                box-shadow: 4px 0 15px rgba(0,0,0,0.1);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .topbar {
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand text-center">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="mb-2 rounded" style="width: 42px; height: 42px; object-fit: contain; background: rgba(255,255,255,0.08); padding: 6px;">
            <h5 class="text-white mb-0 fw-bold" style="letter-spacing: 0.5px;">AlphaGrow</h5>
            <small class="text-muted text-uppercase" style="font-size: 0.65rem; letter-spacing: 1.5px;">{{ auth()->user()->wing }} Wing</small>
        </div>

        <div class="sidebar-scroll">
            
            <!-- 1. Core System -->
            <a class="sidebar-group-header" data-bs-toggle="collapse" href="#collapseCore" role="button" aria-expanded="true">
                <span>Core System</span>
                <i class="bi bi-chevron-down chevron"></i>
            </a>
            <div class="collapse show" id="collapseCore">
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('settings*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    </li>
                </ul>
            </div>

            <!-- 2. Operations -->
            <a class="sidebar-group-header" data-bs-toggle="collapse" href="#collapseOperations" role="button" aria-expanded="true">
                <span>Operations</span>
                <i class="bi bi-chevron-down chevron"></i>
            </a>
            <div class="collapse show" id="collapseOperations">
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
                        <a class="nav-link {{ request()->is('operations/customers*') ? 'active' : '' }}" href="{{ route('operations.customers.index') }}">
                            <i class="bi bi-people"></i> Customers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('operations/production*') ? 'active' : '' }}" href="{{ route('operations.production.index') }}">
                            <i class="bi bi-gear-wide-connected"></i> Production
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('operations/machines*') ? 'active' : '' }}" href="{{ route('operations.machines.index') }}">
                            <i class="bi bi-cpu"></i> Machines
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('operations/bom*') ? 'active' : '' }}" href="{{ route('operations.bom.index') }}">
                            <i class="bi bi-diagram-3"></i> BOM
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('operations/logistics*') ? 'active' : '' }}" href="{{ route('operations.logistics.index') }}">
                            <i class="bi bi-truck"></i> Logistics
                        </a>
                    </li>
                </ul>
            </div>

            <!-- 3. SCM -->
            <a class="sidebar-group-header" data-bs-toggle="collapse" href="#collapseSCM" role="button" aria-expanded="true">
                <span>Supply Chain</span>
                <i class="bi bi-chevron-down chevron"></i>
            </a>
            <div class="collapse show" id="collapseSCM">
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('scm/traceability*') ? 'active' : '' }}" href="{{ route('scm.traceability.index') }}">
                            <i class="bi bi-diagram-2"></i> Traceability
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('scm/gr-qr*') ? 'active' : '' }}" href="{{ route('scm.gr-qr.index') }}">
                            <i class="bi bi-clipboard2-check"></i> GR/QR Posting
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('scm/returns*') ? 'active' : '' }}" href="{{ route('scm.returns.index') }}">
                            <i class="bi bi-arrow-counterclockwise"></i> Returns
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('scm/warehouse-capacity*') ? 'active' : '' }}" href="{{ route('scm.warehouse-capacity.index') }}">
                            <i class="bi bi-building"></i> WH Capacity
                        </a>
                    </li>
                </ul>
            </div>

            <!-- 4. Finance & HR -->
            <a class="sidebar-group-header" data-bs-toggle="collapse" href="#collapseFinance" role="button" aria-expanded="false">
                <span>Finance & HR</span>
                <i class="bi bi-chevron-down chevron"></i>
            </a>
            <div class="collapse" id="collapseFinance">
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('finance-hr/finance*') ? 'active' : '' }}" href="#">
                            <i class="bi bi-cash-coin"></i> Finance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('finance-hr/procurement*') ? 'active' : '' }}" href="#">
                            <i class="bi bi-bag-check"></i> Procurement
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('finance-hr/expenses*') ? 'active' : '' }}" href="#">
                            <i class="bi bi-receipt"></i> Expenses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('finance-hr/hr*') ? 'active' : '' }}" href="#">
                            <i class="bi bi-people"></i> HR & Payroll
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('finance-hr/crm*') ? 'active' : '' }}" href="#">
                            <i class="bi bi-person-lines-fill"></i> CRM
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('finance-hr/pos*') ? 'active' : '' }}" href="#">
                            <i class="bi bi-upc-scan"></i> POS
                        </a>
                    </li>
                </ul>
            </div>

            <!-- 5. Intelligence -->
            <a class="sidebar-group-header" data-bs-toggle="collapse" href="#collapseIntelligence" role="button" aria-expanded="false">
                <span>Intelligence</span>
                <i class="bi bi-chevron-down chevron"></i>
            </a>
            <div class="collapse" id="collapseIntelligence">
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('intelligence/analytics*') ? 'active' : '' }}" href="#">
                            <i class="bi bi-graph-up"></i> Analytics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('intelligence/bi*') ? 'active' : '' }}" href="#">
                            <i class="bi bi-pie-chart"></i> BI Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('intelligence/reports*') ? 'active' : '' }}" href="#">
                            <i class="bi bi-file-earmark-bar-graph"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('intelligence/dms*') ? 'active' : '' }}" href="#">
                            <i class="bi bi-folder2-open"></i> DMS
                        </a>
                    </li>
                </ul>
            </div>

            <!-- 6. Administration -->
            <a class="sidebar-group-header" data-bs-toggle="collapse" href="#collapseAdmin" role="button" aria-expanded="false">
                <span>Administration</span>
                <i class="bi bi-chevron-down chevron"></i>
            </a>
            <div class="collapse" id="collapseAdmin">
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('admin/company*') ? 'active' : '' }}" href="#">
                            <i class="bi bi-building-gear"></i> Company Setup
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}" href="#">
                            <i class="bi bi-person-gear"></i> User Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('admin/roles*') ? 'active' : '' }}" href="#">
                            <i class="bi bi-shield-lock"></i> Roles & Permissions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('admin/audit*') ? 'active' : '' }}" href="#">
                            <i class="bi bi-journal-text"></i> Audit Trail
                        </a>
                    </li>
                </ul>
            </div>

        </div>

        <!-- Logout at bottom -->
        <div class="p-3 border-top" style="border-color: rgba(255,255,255,0.05) !important;">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link w-100 text-danger fw-semibold" style="background: rgba(239, 68, 68, 0.05); border-radius: 6px;">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <!-- Mobile Sidebar Toggle -->
            <button class="btn btn-light d-md-none shadow-sm" onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>
            
            <!-- Page Title -->
            <h5 class="mb-0 fw-bold d-none d-md-block" style="color: #1e293b; font-size: 1.1rem;">@yield('page-title', 'Dashboard')</h5>
            
            <!-- Right Side: User Info -->
            <div class="d-flex align-items-center">
                <span class="badge bg-light text-dark border me-3 text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                    {{ auth()->user()->role }}
                </span>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width:36px; height:36px; font-size: 0.85rem; font-weight: 600;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span class="fw-semibold d-none d-md-inline" style="font-size: 0.9rem;">{{ auth()->user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius: 8px; margin-top: 8px;">
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
        </header>

        <!-- Page Content Area -->
        <div class="p-4">
            @yield('content')
        </div>
    </div>

    <!-- OFFLINE BOOTSTRAP JS -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    
    <!-- Page Specific Scripts Stack -->
    @stack('scripts')
</body>
</html>