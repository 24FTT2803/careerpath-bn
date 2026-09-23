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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

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

        /* Footer */
        .admin-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .admin-footer p {
            font-size: 12px;
            color: var(--text-muted);
            margin: 0;
        }

        .admin-footer-logos {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .admin-footer-logo-link {
            display: inline-block;
            transition: var(--transition);
        }

        .admin-footer-logo-link:hover {
            transform: translateY(-2px);
        }

        .admin-footer-logo {
            height: 26px;
            width: auto;
            object-fit: contain;
            opacity: 0.75;
            transition: var(--transition);
        }

        .admin-footer-logo:hover {
            opacity: 1;
        }

        .admin-footer-logo-fallback {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            background: var(--gold-wash);
            border-radius: 100px;
            color: var(--accent-dark);
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
        }

        .admin-footer-credit {
            font-size: 11px;
            color: var(--text-muted);
        }

        @media (max-width: 768px) {
            .admin-footer {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        /* ============================================
           SHARED ADMIN THEME
           Matching the student profile page, so every admin
           screen reads as one product rather than several.
           ============================================ */

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-header h1 i {
            color: var(--accent);
        }

        .page-header .subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin-top: 2px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-family: inherit;
        }

        .btn-primary {
            background: var(--accent);
            color: var(--primary-dark);
        }

        .btn-primary:hover {
            background: var(--accent-light);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(201, 168, 76, 0.25);
        }

        .btn-outline {
            background: transparent;
            color: var(--text);
            border: 2px solid var(--border);
        }

        .btn-outline:hover {
            border-color: var(--accent);
            color: var(--accent);
            transform: translateY(-2px);
        }

        .btn-subtle {
            background: #f3f4f6;
            color: var(--text);
        }

        .btn-subtle:hover {
            background: #e5e7eb;
        }

        .btn-sm {
            padding: 7px 14px;
            font-size: 12px;
        }

        .card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 24px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        }

        .card-header {
            margin-bottom: 16px;
        }

        .card-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .card-header h3 i {
            color: var(--accent);
        }

        .card-heading {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary);
            margin: 0 0 14px;
        }

        .text-right {
            text-align: right;
        }

        .card-header .card-note {
            color: var(--text-muted);
            font-size: 13px;
            margin-top: 4px;
        }

        .info-banner {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 18px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            line-height: 1.5;
        }

        .info-banner-blue {
            background: #e8f0fe;
            border-left: 4px solid var(--primary-light);
            color: #1e4870;
        }

        .info-banner-gold {
            background: var(--gold-wash);
            border-left: 4px solid var(--accent);
            color: #8a6420;
        }

        .admin-table {
            width: 100%;
            font-size: 13px;
            border-collapse: collapse;
        }

        .admin-table thead tr {
            text-align: left;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
        }

        .admin-table th {
            padding: 8px 0;
            font-weight: 600;
            font-size: 12px;
        }

        .admin-table td {
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }

        .admin-table tbody tr:last-child td {
            border-bottom: none;
        }

        .cell-title {
            font-weight: 600;
            color: var(--text);
            display: block;
        }

        .cell-sub {
            font-size: 11px;
            color: var(--text-muted);
            display: block;
            margin-top: 1px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 11px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-pill-green {
            background: #e9f3ee;
            color: var(--success);
        }

        .status-pill-gold {
            background: var(--gold-wash);
            color: #8a6420;
        }

        .status-pill-blue {
            background: #e8f0fe;
            color: var(--primary-light);
        }

        .status-pill-muted {
            background: #f3f4f6;
            color: var(--text-muted);
        }

        .link {
            color: var(--primary-light);
            background: none;
            border: 0;
            padding: 0;
            font-family: inherit;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .link:hover {
            color: var(--accent);
            text-decoration: underline;
        }

        .link-danger {
            color: var(--danger);
        }

        .link-danger:hover {
            color: #a93226;
        }

        .link-disabled {
            color: #d1d5db;
            cursor: not-allowed;
        }

        .row-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
        }

        .row-actions form {
            display: inline;
        }

        .field-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .field-input {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            background: white;
            transition: all 0.3s ease;
        }

        .field-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(26, 58, 92, 0.08);
        }

        .field-hint {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .field-grid {
            display: grid;
            gap: 16px;
            margin-bottom: 16px;
        }

        .field-grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .field-grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        @media (max-width: 768px) {
            .field-grid-2,
            .field-grid-3 {
                grid-template-columns: 1fr;
            }
        }

        .empty-text {
            color: #9ca3af;
            font-size: 13px;
            text-align: center;
            padding: 24px 0;
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
        <div class="sidebar-brand" style="justify-content: center; padding: 20px 16px;">
    <img
        src="{{ asset('images/careerpath-logo-v2.png') }}"
        alt="CareerPath BN"
        style="height: 48px; width: auto; display: block; background: white; padding: 6px 10px; border-radius: 6px;"
    >
</div>

        <nav class="sidebar-nav">
            <div class="nav-label">Main</div>
            @php
                $isLecturerOnly = auth()->user()->role === 'lecturer';
                $dashboardRoute = $isLecturerOnly ? route('lecturer.dashboard') : route('admin.dashboard');
                $dashboardActive = request()->routeIs('admin.dashboard') || request()->routeIs('lecturer.dashboard');
            @endphp
            <a href="{{ $dashboardRoute }}" class="sidebar-link {{ $dashboardActive ? 'active' : '' }}">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
                        @if(auth()->user()->role === 'lecturer')
                <a
                    href="{{ route('lecturer.students.index') }}"
                    class="sidebar-link {{
                        request()->routeIs('lecturer.students.*')
                            ? 'active'
                            : ''
                    }}"
                >
                    <i class="fas fa-user-graduate"></i>
                    <span>My Students</span>
                </a>
            @else
                <a
                    href="{{ route('admin.students.index') }}"
                    class="sidebar-link {{
                        request()->routeIs('admin.students.*')
                            ? 'active'
                            : ''
                    }}"
                >
                    <i class="fas fa-user-graduate"></i>
                    <span>Students</span>
                </a>
            @endif
            <a href="{{ route('admin.careers.index') }}" class="sidebar-link {{ request()->routeIs('admin.careers.*') ? 'active' : '' }}">
                <i class="fas fa-briefcase"></i>
                <span>Careers</span>
            </a>
            @if(auth()->user()->role === 'admin')
                <div class="nav-label" style="margin-top:16px;">BIICF Management</div>
                <a href="{{ route('admin.biicf.sub-sectors') }}" class="sidebar-link {{ request()->routeIs('admin.biicf.sub-sectors*') ? 'active' : '' }}">
                    <i class="fas fa-layer-group"></i>
                    <span>Sub-Sectors <span class="badge">{{ \App\Models\BiicfSubSector::count() }}</span></span>
                </a>
                <a href="{{ route('admin.biicf.job-roles') }}" class="sidebar-link {{ request()->routeIs('admin.biicf.job-roles*') ? 'active' : '' }}">
                    <i class="fas fa-briefcase"></i>
                    <span>Job Roles <span class="badge">{{ \App\Models\BiicfJobRole::count() }}</span></span>
                </a>
                <a href="{{ route('admin.biicf.competencies') }}" class="sidebar-link {{ request()->routeIs('admin.biicf.competencies*') ? 'active' : '' }}">
                    <i class="fas fa-tools"></i>
                    <span>Competencies <span class="badge">{{ \App\Models\BiicfCompetency::count() }}</span></span>
                </a>
                <a href="{{ route('admin.biicf.trainings') }}" class="sidebar-link {{ request()->routeIs('admin.biicf.trainings*') ? 'active' : '' }}">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Trainings</span>
                </a>
            @endif
            @if(auth()->user()->role === 'admin')
                <div
                    class="nav-label"
                    style="margin-top:16px;"
                >
                    Business Management
                </div>

                <a
                    href="{{ route(
                        'admin.business.plans.index'
                    ) }}"
                    class="sidebar-link {{
                        request()->routeIs(
                            'admin.business.plans.*'
                        )
                            ? 'active'
                            : ''
                    }}"
                >
                    <i class="fas fa-sliders-h"></i>
                    <span>
                        Plans & Features
                    </span>
                </a>

                <a
                    href="{{ route(
                        'admin.business.advertisements.index'
                    ) }}"
                    class="sidebar-link {{
                        request()->routeIs(
                            'admin.business.advertisements.*'
                        )
                            ? 'active'
                            : ''
                    }}"
                >
                    <i class="fas fa-bullhorn"></i>
                    <span>
                        Advertisements
                    </span>
                </a>

                <a
                    href="{{ route(
                        'admin.business.grants.index'
                    ) }}"
                    class="sidebar-link {{
                        request()->routeIs(
                            'admin.business.grants.*'
                        )
                            ? 'active'
                            : ''
                    }}"
                >
                    <i class="fas fa-ticket"></i>
                    <span>
                        Access Grants
                    </span>
                </a>

                <a
                    href="{{ route(
                        'admin.business.groups.index'
                    ) }}"
                    class="sidebar-link {{
                        request()->routeIs(
                            'admin.business.groups.*'
                        )
                            ? 'active'
                            : ''
                    }}"
                >
                    <i class="fas fa-school"></i>
                    <span>
                        Academic Groups
                    </span>
                </a>

                <a
                    href="{{ route(
                        'admin.business.sponsorship.index'
                    ) }}"
                    class="sidebar-link {{
                        request()->routeIs(
                            'admin.business.sponsorship.*'
                        )
                            ? 'active'
                            : ''
                    }}"
                >
                    <i class="fas fa-handshake"></i>
                    <span>
                        Sponsorship
                    </span>
                </a>
            @endif
                        @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>Manage Users</span>
                </a>

                <a
                    href="{{ route(
                        'admin.business.innovation-lab.index'
                    ) }}"
                    class="sidebar-link {{
                        request()->routeIs(
                            'admin.business.innovation-lab.*'
                        )
                            ? 'active'
                            : ''
                    }}"
                >
                    <i class="fas fa-lightbulb"></i>
                    <span>
                        Innovation Lab
                    </span>
                </a>
            @endif
        </nav>

        <!-- Footer - Simple Logout Button -->
<div class="sidebar-footer" style="padding:16px 20px;border-top:1px solid rgba(255,255,255,0.06);">
    <div style="display:flex;align-items:center;gap:10px;">
        <!-- Avatar -->
        <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg, #c9a84c, #e8d4a0);color:#0d1f33;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;">
            {{ substr(auth()->user()->first_name ?? auth()->user()->name, 0, 1) }}
        </div>
        
        <!-- User Info -->
        <div style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:500;color:#ffffff;line-height:1.3;">{{ auth()->user()->first_name ?? auth()->user()->name }}</div>
            <div style="font-family:'IBM Plex Mono',ui-monospace,monospace;font-size:10px;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:0.04em;line-height:1.3;">{{ ucfirst(auth()->user()->role) }}</div>
        </div>
        
        <!-- Logout Button -->
        <button onclick="confirmLogout()" style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.1);color:#f5f1e6;padding:6px 14px;border-radius:6px;cursor:pointer;font-size:12px;transition:all 0.3s ease;font-family:inherit;white-space:nowrap;">
            <i class="fas fa-sign-out-alt" style="color:#c9a84c;margin-right:4px;"></i> Logout
        </button>
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

                <!-- Footer -->
        <footer class="admin-footer">
            <p>&copy; {{ date('Y') }} CareerPath BN &middot; Politeknik Brunei</p>
            <div class="admin-footer-logos">
                @if(file_exists(public_path('images/politeknik-logo.png')))
                    <a href="https://www.pb.edu.bn" target="_blank" class="admin-footer-logo-link">
                        <img src="{{ asset('images/politeknik-logo.png') }}" alt="Politeknik Brunei" class="admin-footer-logo">
                    </a>
                @else
                    <a href="https://www.pb.edu.bn" target="_blank" class="admin-footer-logo-fallback">
                        <i class="fas fa-university"></i> Politeknik Brunei
                    </a>
                @endif
                @if(file_exists(public_path('images/biicf-logo.png')))
                    <a href="https://www.biicf.bn" target="_blank" class="admin-footer-logo-link">
                        <img src="{{ asset('images/biicf-logo.png') }}" alt="BIICF" class="admin-footer-logo">
                    </a>
                @else
                    <a href="https://www.biicf.bn" target="_blank" class="admin-footer-logo-fallback">
                        <i class="fas fa-certificate"></i> BIICF
                    </a>
                @endif
                @if(file_exists(public_path('images/aiti-logo.png')))
                    <a href="https://www.aiti.gov.bn" target="_blank" class="admin-footer-logo-link">
                        <img src="{{ asset('images/aiti-logo.png') }}" alt="AITI" class="admin-footer-logo">
                    </a>
                @else
                    <a href="https://www.aiti.gov.bn" target="_blank" class="admin-footer-logo-fallback">
                        <i class="fas fa-satellite-dish"></i> AITI
                    </a>
                @endif
            </div>
        </footer>
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
        if (confirm('Are you sure you want to log out?')) {
            document.getElementById('logout-form').submit();
        }
    }
}
    </script>
    
<!-- Hidden Logout Form -->
<form method="POST" action="{{ route('logout') }}" id="logout-form" style="display:none;">
    @csrf
</form>
</body>
</html>