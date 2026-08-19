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
        .cpbn-admin{
            --ink:#0d1a2b; --ink-dim:#5b6675; --paper:#faf8f2; --card:#ffffff; --line:#e7e2d4;
            --gold:#cf9a3d; --gold-bright:#e9b95a; --gold-wash:#fbf1de;
            --rose:#c65b4e; --rose-wash:#fbeceb; --green:#4c8a68; --green-wash:#e9f3ee;
            --purple:#7a5ea8; --purple-wash:#f1ecf7;
            --font-display:'Fraunces', Georgia, serif; --font-body:'IBM Plex Sans', ui-sans-serif, system-ui, sans-serif;
            --font-mono:'IBM Plex Mono', ui-monospace, monospace;
        }
        .cpbn-admin, .cpbn-admin *{box-sizing:border-box}
        .cpbn-admin{font-family:var(--font-body);color:var(--ink);min-height:100vh;display:flex}
        .cpbn-admin a{text-decoration:none;color:inherit}

        /* ---------- sidebar ---------- */
        .cpbn-sidebar{
            width:258px;flex-shrink:0;min-height:100vh;background:var(--ink);color:#f5f1e6;
            display:flex;flex-direction:column;position:relative;
        }
        .cpbn-sb-brand{padding:22px 22px 18px;display:flex;align-items:center;gap:10px;border-bottom:1px solid rgba(245,241,230,0.1)}
        .cpbn-sb-brand svg{width:28px;height:28px;flex-shrink:0}
        .cpbn-sb-brand-text{font-family:var(--font-display);font-weight:600;font-size:17px;line-height:1.15}
        .cpbn-sb-brand-sub{display:block;font-family:var(--font-mono);font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--gold-bright);margin-top:2px}

        .cpbn-sb-nav{padding:18px 14px;flex:1}
        .cpbn-sb-link{
            display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:5px;font-size:14px;
            color:#c7c2b4;margin-bottom:3px;transition:background .15s,color .15s;
        }
        .cpbn-sb-link svg{width:16px;height:16px;flex-shrink:0}
        .cpbn-sb-link:hover{background:rgba(245,241,230,0.06);color:#f5f1e6}
        .cpbn-sb-link.active{background:var(--gold-wash);color:#8a6420;font-weight:600}
        .cpbn-sb-link.active svg{color:#8a6420}

        .cpbn-sb-footer{padding:16px 18px;border-top:1px solid rgba(245,241,230,0.1)}
        .cpbn-sb-user{display:flex;align-items:center;gap:11px}
        .cpbn-sb-avatar{
            width:38px;height:38px;border-radius:50%;background:var(--gold);color:var(--ink);flex-shrink:0;
            display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:600;font-size:15px;
        }
        .cpbn-sb-user .n{font-weight:600;font-size:13.5px;color:#f5f1e6}
        .cpbn-sb-user .r{font-family:var(--font-mono);font-size:10.5px;color:#a39d8a;text-transform:uppercase;letter-spacing:.04em}
        .cpbn-sb-logout{all:unset;display:flex;align-items:center;gap:8px;font-size:13px;color:#e0a89f;cursor:pointer;margin-top:14px}
        .cpbn-sb-logout svg{width:14px;height:14px}
        .cpbn-sb-logout:hover{color:var(--rose)}

        /* ---------- main ---------- */
        .cpbn-main{flex:1;background:var(--paper);min-height:100vh}
        .cpbn-main-inner{padding:32px 36px 56px;max-width:1300px}

        /* ---------- alerts ---------- */
        .cpbn-alert{padding:13px 16px;border-radius:5px;font-size:13.5px;margin-bottom:20px;display:flex;align-items:flex-start;gap:10px}
        .cpbn-alert svg{width:16px;height:16px;flex-shrink:0;margin-top:1px}
        .cpbn-alert-success{background:var(--green-wash);color:#2e5c43;border:1px solid rgba(76,138,104,0.25)}
        .cpbn-alert-error{background:var(--rose-wash);color:#8f3a30;border:1px solid rgba(198,91,78,0.25)}
        .cpbn-alert-info{background:var(--gold-wash);color:#8a6420;border:1px solid rgba(207,154,61,0.28)}

        /* ---------- headers ---------- */
        .cpbn-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:24px}
        .cpbn-head h1{font-family:var(--font-display);font-weight:600;font-size:23px;letter-spacing:-.01em;display:flex;align-items:center;gap:10px}
        .cpbn-head h1 svg{width:21px;height:21px;color:var(--gold)}
        .cpbn-head p.sub{color:var(--ink-dim);margin-top:4px;font-size:14px}
        .cpbn-note{font-size:13px;color:var(--ink-dim);display:flex;align-items:center;gap:6px}
        .cpbn-note svg{width:13px;height:13px}

        /* ---------- buttons ---------- */
        .cpbn-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:5px;font-size:13.5px;font-weight:500;border:none;cursor:pointer;transition:background .15s}
        .cpbn-btn svg{width:13px;height:13px}
        .cpbn-btn-primary{background:var(--gold);color:var(--ink)}
        .cpbn-btn-primary:hover{background:var(--gold-bright)}
        .cpbn-btn-muted{background:#eee9db;color:var(--ink)}
        .cpbn-btn-muted:hover{background:#e4dfcd}
        .cpbn-btn-sm{padding:7px 13px;font-size:12.5px}

        /* ---------- cards ---------- */
        .cpbn-card{background:var(--card);border:1px solid var(--line);border-radius:6px;padding:22px}
        .cpbn-card + .cpbn-card{margin-top:20px}
        .cpbn-card-title{font-family:var(--font-display);font-size:15.5px;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:9px}
        .cpbn-card-title svg{width:16px;height:16px;color:var(--gold)}

        /* ---------- filter bar ---------- */
        .cpbn-filterbar{background:var(--card);border:1px solid var(--line);border-radius:6px;padding:16px;margin-bottom:20px;display:flex;flex-wrap:wrap;gap:12px;align-items:center}
        .cpbn-filterbar input[type="text"],.cpbn-filterbar select{
            padding:10px 13px;border-radius:4px;border:1px solid var(--line);background:#fff;font-family:var(--font-body);font-size:13.5px;color:var(--ink);
        }
        .cpbn-filterbar input[type="text"]{flex:1;min-width:220px}
        .cpbn-filterbar input:focus,.cpbn-filterbar select:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(207,154,61,0.15)}

        /* ---------- table ---------- */
        .cpbn-table-wrap{background:var(--card);border:1px solid var(--line);border-radius:6px;overflow:hidden}
        table.cpbn-table{width:100%;border-collapse:collapse;font-size:13.5px}
        table.cpbn-table thead{background:#f5f2e9}
        table.cpbn-table th{
            text-align:left;padding:12px 16px;font-family:var(--font-mono);font-size:11px;letter-spacing:.05em;text-transform:uppercase;
            color:var(--ink-dim);font-weight:500;
        }
        table.cpbn-table th.center{text-align:center}
        table.cpbn-table td{padding:13px 16px;border-top:1px solid var(--line)}
        table.cpbn-table td.center{text-align:center}
        table.cpbn-table tr:hover td{background:#faf8f0}
        table.cpbn-table a.link{color:#8a6420;font-weight:500}
        table.cpbn-table a.link:hover{text-decoration:underline}
        .cpbn-empty-row{text-align:center;padding:44px 20px;color:var(--ink-dim)}
        .cpbn-empty-row svg{width:32px;height:32px;color:var(--line);margin-inline:auto;margin-bottom:10px;display:block}

        /* ---------- badges/pills ---------- */
        .cpbn-pill{font-family:var(--font-mono);font-size:11px;padding:4px 11px;border-radius:100px;display:inline-block;white-space:nowrap}
        .pill-gold{background:var(--gold-wash);color:#8a6420}
        .pill-rose{background:var(--rose-wash);color:#8f3a30}
        .pill-green{background:var(--green-wash);color:#2e5c43}
        .pill-purple{background:var(--purple-wash);color:#5a4180}
        .pill-neutral{background:#eef1f5;color:var(--ink-dim)}
        .cpbn-tag{font-family:var(--font-mono);font-size:12.5px;padding:6px 12px;border-radius:100px;display:inline-flex;align-items:center;gap:6px;margin:3px}

        /* ---------- stat cards ---------- */
        .cpbn-stats{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:22px}
        .cpbn-stat{background:var(--card);border:1px solid var(--line);border-radius:6px;padding:18px;transition:box-shadow .2s}
        .cpbn-stat:hover{box-shadow:0 8px 22px -12px rgba(13,26,43,0.14)}
        .cpbn-stat-top{display:flex;justify-content:space-between;align-items:flex-start}
        .cpbn-stat-label{font-size:12px;color:var(--ink-dim)}
        .cpbn-stat-num{font-family:var(--font-mono);font-size:24px;font-weight:500;margin-top:5px}
        .cpbn-icon-badge{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .cpbn-icon-badge svg{width:16px;height:16px}
        .ib-gold{background:var(--gold-wash);color:#a97a1f}
        .ib-green{background:var(--green-wash);color:var(--green)}
        .ib-rose{background:var(--rose-wash);color:var(--rose)}
        .ib-purple{background:var(--purple-wash);color:var(--purple)}
        .ib-ink{background:#eef1f5;color:var(--ink-dim)}

        /* ---------- progress bars ---------- */
        .cpbn-prog-row{margin-bottom:14px}
        .cpbn-prog-row:last-child{margin-bottom:0}
        .cpbn-prog-top{display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px}
        .cpbn-prog-top span:last-child{font-family:var(--font-mono);color:var(--ink-dim)}
        .cpbn-bar{width:100%;background:#eee9db;border-radius:100px;height:7px;overflow:hidden}
        .cpbn-bar-fill{height:100%;border-radius:100px;transition:width .5s ease}
        .fill-gold{background:var(--gold)}
        .fill-green{background:var(--green)}
        .fill-rose{background:var(--rose)}

        /* ---------- empty text ---------- */
        .cpbn-empty-note{text-align:center;padding:20px;color:var(--ink-dim);font-size:13.5px;display:flex;align-items:center;justify-content:center;gap:7px}
        .cpbn-empty-note svg{width:14px;height:14px;flex-shrink:0}

        /* ---------- forms ---------- */
        .cpbn-field{margin-bottom:16px}
        .cpbn-field label{display:block;font-size:13px;font-weight:500;color:var(--ink);margin-bottom:6px}
        .cpbn-field label .req{color:var(--rose)}
        .cpbn-field input[type="text"],
        .cpbn-field input[type="email"],
        .cpbn-field input[type="password"],
        .cpbn-field select{
            width:100%;padding:10px 13px;border-radius:4px;border:1px solid var(--line);background:#fff;
            font-family:var(--font-body);font-size:14px;color:var(--ink);
        }
        .cpbn-field input:focus,.cpbn-field select:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(207,154,61,0.15)}
        .cpbn-field .err{color:var(--rose);font-size:12px;margin-top:5px}
        .cpbn-hint{font-size:11.5px;color:var(--ink-dim);margin-top:5px}
        .cpbn-form-actions{display:flex;justify-content:flex-end;margin-top:22px}

        /* ---------- profile hero ---------- */
        .cpbn-avatar-lg{
            width:88px;height:88px;border-radius:50%;background:var(--gold-wash);color:#8a6420;
            display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:600;font-size:32px;margin-inline:auto;
        }
        .cpbn-profile-row{display:flex;justify-content:space-between;padding:10px 0;font-size:13.5px;border-top:1px solid var(--line)}
        .cpbn-profile-row:first-child{border-top:none}
        .cpbn-profile-row span:first-child{color:var(--ink-dim)}
        .cpbn-profile-row span:last-child{font-weight:500}

        /* ---------- pagination (best-effort override of default Laravel markup) ---------- */
        .cpbn-pagination{margin-top:16px;font-family:var(--font-mono);font-size:12.5px}
        .cpbn-pagination nav{display:flex;justify-content:center}
        .cpbn-pagination a,.cpbn-pagination span{color:var(--ink-dim) !important}

        @media (max-width:1100px){
            .cpbn-stats{grid-template-columns:repeat(3,1fr)}
        }
        @media (max-width:820px){
            .cpbn-sidebar{width:210px}
            .cpbn-stats{grid-template-columns:repeat(2,1fr)}
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="cpbn-admin">
        <!-- Sidebar -->
        <div class="cpbn-sidebar">
            <a href="{{ route('admin.dashboard') }}" class="cpbn-sb-brand">
                <svg viewBox="0 0 32 32" fill="none">
                    <circle cx="16" cy="16" r="15" stroke="#cf9a3d" stroke-width="1.4"/>
                    <path d="M16 6 L19 15 L16 26 L13 15 Z" fill="#e9b95a"/>
                    <circle cx="16" cy="16" r="2" fill="#0d1a2b"/>
                </svg>
                <span>
                    <span class="cpbn-sb-brand-text">CareerPath BN</span>
                    <span class="cpbn-sb-brand-sub">Administration Panel</span>
                </span>
            </a>

            <nav class="cpbn-sb-nav">
                <a href="{{ route('admin.dashboard') }}" class="cpbn-sb-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 15l4-6 3 3 5-8"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.students.index') }}" class="cpbn-sb-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="3.5"/><path d="M2 21c0-3.9 3.1-7 7-7s7 3.1 7 7"/><path d="M16 4.2a3.5 3.5 0 0 1 0 6.8"/><path d="M22 21c0-3-2-5.5-4.5-6.6"/></svg>
                    Students
                </a>
                <a href="{{ route('admin.careers.index') }}" class="cpbn-sb-link {{ request()->routeIs('admin.careers.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 12h18"/></svg>
                    Careers
                </a>
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.users.index') }}" class="cpbn-sb-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/><path d="M19 8l1.5 1.5L23 7"/></svg>
                        Users
                    </a>
                @endif
            </nav>

            <div class="cpbn-sb-footer">
                <div class="cpbn-sb-user">
                    <div class="cpbn-sb-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                    <div>
                        <p class="n">{{ auth()->user()->name }}</p>
                        <p class="r">{{ ucfirst(auth()->user()->role) }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="cpbn-sb-logout">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="cpbn-main">
            <div class="cpbn-main-inner">
                @if(session('success'))
                    <div class="cpbn-alert cpbn-alert-success">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="cpbn-alert cpbn-alert-error">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>