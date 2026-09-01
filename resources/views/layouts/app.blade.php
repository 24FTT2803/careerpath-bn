<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'CareerPath BN'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
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

            /* Edit Profile Variables */
            --paper: #faf8f2;
            --ink: #0d1a2b;
            --ink-dim: #5b6675;
            --line: #e7e2d4;
            --gold: #cf9a3d;
            --gold-bright: #e9b95a;
            --gold-wash: #fbf1de;
            --rose: #c65b4e;
            --rose-wash: #fbeceb;
            --green: #4c8a68;
            --green-wash: #e9f3ee;
            --purple: #7a5ea8;
            --purple-wash: #f1ecf7;
            --font-display: 'Fraunces', Georgia, serif;
            --font-body: 'IBM Plex Sans', ui-sans-serif, system-ui, sans-serif;
            --font-mono: 'IBM Plex Mono', ui-monospace, monospace;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* ============================================
           NAVIGATION
           ============================================ */
        .site-nav {
            background: white;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(12px);
        }

        .nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .nav-brand .icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
            font-size: 16px;
        }

        .nav-brand .text {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 18px;
            color: var(--primary);
        }

        .nav-brand .text span {
            color: var(--accent);
        }

        .nav-brand .sub {
            display: block;
            font-size: 9px;
            font-weight: 500;
            color: var(--text-muted);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-top: -2px;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .nav-link {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: var(--transition);
            padding: 6px 12px;
            border-radius: 6px;
        }

        .nav-link:hover {
            color: var(--primary);
            background: var(--bg);
        }

        .nav-link i {
            margin-right: 6px;
        }

        .nav-link.active {
            color: var(--primary);
            background: rgba(26, 58, 92, 0.06);
        }

        .nav-notif {
            position: relative;
            color: var(--text-muted);
            font-size: 18px;
            transition: var(--transition);
            padding: 4px;
        }

        .nav-notif:hover {
            color: var(--primary);
        }

        .nav-notif .badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: var(--danger);
            color: white;
            font-size: 9px;
            font-weight: 700;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ============================================
           USER DROPDOWN - CLICK TOGGLE ONLY (NO HOVER)
           ============================================ */

        .nav-user-wrapper {
            position: relative;
            display: inline-block;
            z-index: 1000;
        }

        .nav-user {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 12px 4px 4px;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            user-select: none;
        }

        .nav-user:hover {
            background: var(--bg);
        }

        .nav-user .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 13px;
            flex-shrink: 0;
        }

        .nav-user .name {
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
        }

        .nav-user .chevron {
            font-size: 12px;
            color: var(--text-muted);
            transition: var(--transition);
        }

        .nav-user.active .chevron {
            transform: rotate(180deg);
        }

        /* Dropdown - hidden by default (NO HOVER) */
        .nav-user-wrapper .dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            right: 0;
            min-width: 220px;
            z-index: 9999;
        }

        /* Show dropdown ONLY when active class is added */
        .nav-user-wrapper.active .dropdown {
            display: block;
        }

        .nav-user-wrapper .dropdown .dropdown-menu {
            background: white;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-hover);
            padding: 6px;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 6px;
            color: var(--text);
            text-decoration: none;
            font-size: 13px;
            transition: var(--transition);
        }

        .dropdown-item:hover {
            background: var(--bg);
        }

        .dropdown-item i {
            width: 18px;
            color: var(--text-muted);
        }

        .dropdown-divider {
            height: 1px;
            background: var(--border);
            margin: 4px 8px;
        }

        .dropdown-item.danger {
            color: var(--danger);
        }

        .dropdown-item.danger i {
            color: var(--danger);
        }

        .dropdown-item.danger:hover {
            background: rgba(192, 57, 43, 0.08);
        }

        .dropdown-item .badge-count {
            background: var(--danger);
            color: white;
            font-size: 10px;
            padding: 1px 8px;
            border-radius: 100px;
            margin-left: auto;
        }

        /* ============================================
           ALERTS
           ============================================ */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin: 16px 0 0;
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

        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 768px) {
            .nav-link { display: none; }
            .nav-brand .sub { display: none; }
            .nav-user .name { display: none; }
        }

        @media (max-width: 480px) {
            .container { padding: 0 16px; }
            .nav-user .name { display: none !important; }
        }

        /* ============================================
        CAREER ADVISER FLOATING ACCESS
        ============================================ */

        .career-adviser-fab {
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 900;

            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;

            padding: 12px 18px;

            background: var(--primary);
            color: white;

            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 100px;

            box-shadow:
                0 8px 28px rgba(26, 58, 92, 0.22);

            font-size: 13px;
            font-weight: 600;
            text-decoration: none;

            transition: var(--transition);
        }

        .career-adviser-fab:hover {
            transform: translateY(-2px);
            color: white;

            box-shadow:
                0 12px 34px rgba(26, 58, 92, 0.28);
        }

        .career-adviser-fab i {
            color: var(--accent);
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .career-adviser-fab {
                width: 52px;
                height: 52px;

                right: 18px;
                bottom: 18px;

                padding: 0;
                border-radius: 50%;
            }

            .career-adviser-fab span {
                display: none;
            }

            .career-adviser-fab i {
                font-size: 17px;
            }
        }
    </style>
</head>
<body>

    <nav class="site-nav">
        <div class="container">
            <div class="nav-inner">
                <a href="{{ route('student.dashboard') }}" class="nav-brand">
                    <div class="icon"><i class="fas fa-compass"></i></div>
                    <div>
                        <span class="text">CareerPath <span>BN</span></span>
                        <span class="sub">Politeknik Brunei</span>
                    </div>
                </a>

                <div class="nav-right">
                    <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                    <a href="{{ route('student.profile') }}" class="nav-link {{ request()->routeIs('student.profile*') ? 'active' : '' }}">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a href="{{ route('student.milestones') }}" class="nav-link {{ request()->routeIs('student.milestones*') ? 'active' : '' }}">
                        <i class="fas fa-flag-checkered"></i> Milestones
                    </a>
                    <a href="{{ route('student.biicf-explorer.index') }}" class="nav-link {{ request()->routeIs('student.biicf-explorer*') ? 'active' : '' }}">
                        <i class="fas fa-compass"></i> BIICF
                    </a>

                    <a href="{{ route('student.notifications') }}" class="nav-notif">
                        <i class="fas fa-bell"></i>
                        @auth
                            @if(Auth::user()->unreadNotifications()->count() > 0)
                                <span class="badge">{{ Auth::user()->unreadNotifications()->count() }}</span>
                            @endif
                        @endauth
                    </a>

                    <!-- User Dropdown - Click Toggle (NO HOVER) -->
                    <div class="nav-user-wrapper" id="navUserWrapper">
                        <div class="nav-user" id="navUserToggle">
                            <div class="avatar">
                                {{ substr(Auth::user()->first_name ?? Auth::user()->name, 0, 1) }}
                            </div>
                            <span class="name">{{ Auth::user()->first_name ?? Auth::user()->name }}</span>
                            <span class="chevron"><i class="fas fa-chevron-down"></i></span>
                        </div>

                        <div class="dropdown" id="dropdownMenu">
                            <div class="dropdown-menu">
                                <a href="{{ route('student.profile') }}" class="dropdown-item">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                                <a href="{{ route('student.settings') }}" class="dropdown-item">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                                <a href="{{ route('student.notifications') }}" class="dropdown-item">
                                    <i class="fas fa-bell"></i> Notifications
                                    @if(Auth::user()->unreadNotifications()->count() > 0)
                                        <span class="badge-count">{{ Auth::user()->unreadNotifications()->count() }}</span>
                                    @endif
                                </a>
                                <div class="dropdown-divider"></div>

                                @if(Auth::user()->role === 'student')
                                    <a href="{{ route('student.career-adviser') }}" class="dropdown-item">
                                        <i class="fas fa-comments"></i> Career Adviser
                                    </a>
                                @endif

                                <a href="{{ route('student.milestones') }}" class="dropdown-item">
                                    <i class="fas fa-flag-checkered"></i> Milestones
                                </a>

                                <a href="{{ route('student.biicf-explorer.index') }}" class="dropdown-item">
                                    <i class="fas fa-compass"></i> BIICF Explorer
                                </a>
                                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'lecturer')
                                    <div class="dropdown-divider"></div>
                                    <a href="{{ route('admin.dashboard') }}" class="dropdown-item">
                                        <i class="fas fa-crown" style="color:var(--accent);"></i> Admin Panel
                                    </a>
                                @endif
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item danger" style="width:100%;border:none;background:transparent;cursor:pointer;font-family:inherit;font-size:13px;text-align:left;">
                                        <i class="fas fa-sign-out-alt"></i> Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <div class="container">
            @if (session('warning'))
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ session('warning') }}
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    @if(
        Auth::check()
        && Auth::user()->role === 'student'
        && !request()->routeIs('student.career-adviser')
    )
        <a
            href="{{ route('student.career-adviser') }}"
            class="career-adviser-fab"
            aria-label="Open Career Adviser"
            title="Career Adviser"
        >
            <i class="fas fa-comments"></i>
            <span>Career Adviser</span>
        </a>
    @endif

    @include('layouts.footer')

    <!-- ============================================ -->
    <!-- DROPDOWN - CLICK TOGGLE JAVASCRIPT          -->
    <!-- ============================================ -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const wrapper = document.getElementById('navUserWrapper');
            const toggle = document.getElementById('navUserToggle');
            
            if (wrapper && toggle) {
                // Toggle dropdown on click - ONLY click, no hover
                toggle.addEventListener('click', function(e) {
                    // Don't close if clicking on a dropdown item or form
                    if (e.target.closest('.dropdown-item') || e.target.closest('form')) {
                        return;
                    }
                    wrapper.classList.toggle('active');
                    e.stopPropagation();
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!wrapper.contains(e.target)) {
                        wrapper.classList.remove('active');
                    }
                });
                
                // Close dropdown on Escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        wrapper.classList.remove('active');
                    }
                });
            }
        });
    </script>

</body>
</html>