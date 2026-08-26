<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - CareerPath BN')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        /* ============================================
           ADMIN LAYOUT - FOLLOWING STUDENT DESIGN
           ============================================ */

        :root {
            --primary: #1a3a5c;
            --primary-light: #2a5a8c;
            --primary-dark: #0d1f33;
            --accent: #c9a84c;
            --accent-light: #e8d4a0;
            --accent-dark: #a88830;
            --bg: #f4f6f9;
            --card: #ffffff;
            --text: #1a1a2e;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --shadow: 0 4px 24px rgba(26, 58, 92, 0.08);
            --shadow-hover: 0 8px 40px rgba(26, 58, 92, 0.15);
            --radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --success: #2d8f5c;
            --danger: #c0392b;
            --warning: #e67e22;
            --gold: #c9a84c;
            --gold-wash: #fbf1de;
            --font-display: 'Playfair Display', serif;
            --font-body: 'Inter', -apple-system, sans-serif;
            --font-mono: 'IBM Plex Mono', ui-monospace, monospace;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: var(--font-body);
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            -webkit-font-smoothing: antialiased;
        }

        /* ---------- Sidebar ---------- */
        .admin-sidebar {
            width: 260px;
            min-height: 100vh;
            background: var(--primary-dark);
            color: #f5f1e6;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            flex-shrink: 0;
        }

        .sidebar-brand {
            padding: 24px 24px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(245, 241, 230, 0.1);
        }

        .sidebar-brand .brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
            font-size: 20px;
            flex-shrink: 0;
        }

        .sidebar-brand .brand-text {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 18px;
            color: white;
        }

        .sidebar-brand .brand-text span {
            color: var(--accent);
        }

        .sidebar-brand .brand-sub {
            font-family: var(--font-mono);
            font-size: 10px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--accent-light);
            display: block;
            margin-top: -2px;
        }

        .sidebar-nav {
            padding: 16px 14px;
            flex: 1;
        }

        .sidebar-nav .nav-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: rgba(255, 255, 255, 0.3);
            padding: 12px 14px 8px;
            font-family: var(--font-mono);
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 6px;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 13.5px;
            transition: all 0.2s ease;
            margin-bottom: 2px;
        }

        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.06);
            color: white;
        }

        .sidebar-link.active {
            background: rgba(201, 168, 76, 0.15);
            color: var(--accent-light);
            font-weight: 500;
        }

        .sidebar-link i {
            width: 18px;
            font-size: 14px;
        }

        .sidebar-link .badge {
            margin-left: auto;
            background: var(--accent);
            color: var(--primary-dark);
            padding: 1px 10px;
            border-radius: 100px;
            font-size: 10px;
            font-weight: 600;
        }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        .sidebar-footer .nav-user-wrapper {
            position: relative;
            display: block;
            width: 100%;
        }

        .sidebar-footer .nav-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 12px 6px 6px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s ease;
            user-select: none;
            width: 100%;
        }

        .sidebar-footer .nav-user:hover {
            background: rgba(255, 255, 255, 0.06);
        }

        .sidebar-footer .nav-user .avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

        .sidebar-footer .nav-user .user-info {
            flex: 1;
            min-width: 0;
        }

        .sidebar-footer .nav-user .name {
            font-size: 13px;
            font-weight: 500;
            color: #f5f1e6;
            display: block;
            line-height: 1.3;
        }

        .sidebar-footer .nav-user .role {
            font-family: var(--font-mono);
            font-size: 10px;
            color: rgba(255, 255, 255, 0.4);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            display: block;
            line-height: 1.3;
        }

        .sidebar-footer .nav-user .chevron {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.4);
            transition: transform 0.3s ease;
            flex-shrink: 0;
        }

        .sidebar-footer .nav-user.active .chevron {
            transform: rotate(180deg);
        }

        /* Dropdown - hidden by default, shows only Logout */
        .sidebar-footer .nav-user-wrapper .dropdown {
            display: none;
            position: absolute;
            bottom: calc(100% + 8px);
            right: 0;
            min-width: 180px;
            z-index: 9999;
        }

        .sidebar-footer .nav-user-wrapper.active .dropdown {
            display: block;
        }

        .sidebar-footer .nav-user-wrapper .dropdown .dropdown-menu {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 8px 40px rgba(26, 58, 92, 0.15);
            padding: 6px;
        }

        .sidebar-footer .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 6px;
            color: #1a1a2e;
            text-decoration: none;
            font-size: 13px;
            transition: background 0.2s ease;
            cursor: pointer;
            width: 100%;
            border: none;
            background: transparent;
            text-align: left;
            font-family: var(--font-body);
        }

        .sidebar-footer .dropdown-item:hover {
            background: #f4f6f9;
        }

        .sidebar-footer .dropdown-item i {
            width: 18px;
            color: #6b7280;
            font-size: 14px;
        }

        .sidebar-footer .dropdown-item.danger {
            color: #c0392b;
        }

        .sidebar-footer .dropdown-item.danger i {
            color: #c0392b;
        }

        .sidebar-footer .dropdown-item.danger:hover {
            background: rgba(192, 57, 43, 0.08);
        }

        /* ---------- Main Content ---------- */
        .admin-main {
            flex: 1;
            padding: 32px 36px 56px;
            max-width: calc(100% - 260px);
            overflow-y: auto;
        }

        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffc107;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 200px;
            }
            .admin-main {
                padding: 20px 16px 40px;
                max-width: 100%;
            }
            .sidebar-brand .brand-sub {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .admin-sidebar {
                width: 60px;
            }
            .admin-sidebar .brand-text,
            .admin-sidebar .brand-sub,
            .admin-sidebar .sidebar-link span,
            .admin-sidebar .sidebar-footer .nav-user .user-info,
            .admin-sidebar .sidebar-link .badge,
            .admin-sidebar .nav-label {
                display: none;
            }
            .admin-sidebar .sidebar-link {
                justify-content: center;
                padding: 12px;
            }
            .admin-sidebar .sidebar-link i {
                font-size: 18px;
                width: auto;
            }
            .admin-main {
                padding: 16px 12px 32px;
                max-width: 100%;
            }
            .sidebar-brand {
                padding: 16px;
                justify-content: center;
            }
            .sidebar-footer .nav-user {
                padding: 6px !important;
                justify-content: center;
            }
            .sidebar-footer .nav-user .avatar {
                width: 32px;
                height: 32px;
                font-size: 13px;
            }
            .sidebar-footer .nav-user .chevron {
                display: none;
            }
            .sidebar-footer .nav-user-wrapper .dropdown {
                right: auto;
                left: 50%;
                transform: translateX(-50%);
                min-width: 160px;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="fas fa-crown"></i></div>
            <div>
                <span class="brand-text">CareerPath <span>BN</span></span>
                <span class="brand-sub">Administration</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-label">Main</div>
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.students.index') }}" class="sidebar-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                <i class="fas fa-user-graduate"></i>
                <span>Students</span>
            </a>
            <a href="{{ route('admin.careers.index') }}" class="sidebar-link {{ request()->routeIs('admin.careers.*') ? 'active' : '' }}">
                <i class="fas fa-briefcase"></i>
                <span>Careers</span>
            </a>
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>Manage Users</span>
                </a>
            @endif
        </nav>

        <!-- Footer - Clean dropdown with only Logout -->
        <div class="sidebar-footer">
            <div class="nav-user-wrapper" id="navUserWrapper">
                <div class="nav-user" id="navUserToggle">
                    <div class="avatar">
                        {{ substr(auth()->user()->first_name ?? auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="user-info">
                        <span class="name">{{ auth()->user()->first_name ?? auth()->user()->name }}</span>
                        <span class="role">{{ ucfirst(auth()->user()->role) }}</span>
                    </div>
                    <span class="chevron"><i class="fas fa-chevron-down"></i></span>
                </div>

                <!-- Dropdown - Only Logout with Confirmation -->
                <div class="dropdown" id="dropdownMenu">
                    <div class="dropdown-menu">
                        <form method="POST" action="{{ route('logout') }}" id="logout-form" style="margin:0;">
                            @csrf
                            <button type="button" class="dropdown-item danger" onclick="confirmLogout()" style="width:100%;border:none;background:transparent;cursor:pointer;font-family:inherit;font-size:13px;text-align:left;">
                                <i class="fas fa-sign-out-alt"></i> Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                {{ session('warning') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Dropdown Toggle JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const wrapper = document.getElementById('navUserWrapper');
            const toggle = document.getElementById('navUserToggle');

            if (wrapper && toggle) {
                toggle.addEventListener('click', function(e) {
                    if (e.target.closest('.dropdown-item') || e.target.closest('form')) {
                        return;
                    }
                    wrapper.classList.toggle('active');
                    e.stopPropagation();
                });

                document.addEventListener('click', function(e) {
                    if (!wrapper.contains(e.target)) {
                        wrapper.classList.remove('active');
                    }
                });

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        wrapper.classList.remove('active');
                    }
                });
            }
        });

        // Logout confirmation using the existing modal system
        function confirmLogout() {
            if (typeof showConfirmModal === 'function') {
                showConfirmModal({
                    title: 'Confirm Logout',
                    message: 'Are you sure you want to log out?',
                    confirmText: 'Yes, Log Out',
                    cancelText: 'Cancel',
                    type: 'warning',
                    onConfirm: function() {
                        document.getElementById('logout-form').submit();
                    }
                });
            } else {
                // Fallback if modal function not available
                if (confirm('Are you sure you want to log out?')) {
                    document.getElementById('logout-form').submit();
                }
            }
        }
    </script>

</body>
</html>