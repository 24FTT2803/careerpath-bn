<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard — CareerPath BN</title>

    @fonts

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        :root{
            --ink:#0d1a2b;
            --ink-2:#132540;
            --ink-3:#1c3355;
            --paper:#f5f1e6;
            --paper-dim:#c7c2b4;
            --gold:#cf9a3d;
            --gold-bright:#e9b95a;
            --rose:#c65b4e;
            --line:rgba(245,241,230,0.14);
            --font-display:'Fraunces', Georgia, serif;
            --font-body:'IBM Plex Sans', ui-sans-serif, system-ui, sans-serif;
            --font-mono:'IBM Plex Mono', ui-monospace, monospace;
        }

        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{
            background:var(--ink);
            color:var(--paper);
            font-family:var(--font-body);
            font-size:16px;
            line-height:1.6;
            -webkit-font-smoothing:antialiased;
            min-height:100vh;
        }
        a{color:inherit;text-decoration:none}
        .wrap{max-width:1180px;margin-inline:auto;padding-inline:28px}

        .reveal{opacity:0;transform:translateY(14px);animation:reveal .7s cubic-bezier(.2,.7,.2,1) forwards}
        @keyframes reveal{to{opacity:1;transform:translateY(0)}}
        @media (prefers-reduced-motion:reduce){.reveal{animation:none;opacity:1;transform:none}}

        /* header / app nav */
        header.site{
            position:sticky;top:0;z-index:50;
            border-bottom:1px solid var(--line);
            background:rgba(13,26,43,0.86);
            backdrop-filter:blur(10px);
        }
        .nav{display:flex;align-items:center;justify-content:space-between;padding-block:16px}
        .brand{display:flex;align-items:center;gap:10px;font-family:var(--font-display);font-size:20px;font-weight:600}
        .brand-mark{width:30px;height:30px;flex-shrink:0}
        .brand small{display:block;font-family:var(--font-mono);font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:var(--gold-bright);font-weight:400;margin-top:1px}

        nav.links{display:flex;align-items:center;gap:28px}
        nav.links a{font-size:14px;color:var(--paper-dim);transition:color .2s}
        nav.links a:hover, nav.links a.active{color:var(--paper)}
        nav.links a.active{color:var(--gold-bright)}

        .nav-actions{display:flex;align-items:center;gap:16px}
        .nav-actions .who{font-size:13.5px;color:var(--paper-dim)}
        .nav-actions .who strong{color:var(--paper);font-weight:500}
        .btn{
            display:inline-flex;align-items:center;justify-content:center;gap:8px;
            padding:9px 18px;border-radius:3px;font-size:13.5px;font-weight:500;
            border:1px solid var(--line);background:transparent;color:var(--paper);
            transition:all .2s;cursor:pointer;white-space:nowrap;font-family:inherit;
        }
        .btn:hover{border-color:var(--gold-bright);color:var(--gold-bright)}

        /* page header */
        .page-head{padding-block:44px 8px}
        .eyebrow{
            display:inline-flex;align-items:center;gap:8px;
            font-family:var(--font-mono);font-size:11.5px;letter-spacing:.12em;text-transform:uppercase;
            color:var(--gold-bright);margin-bottom:12px;
        }
        .eyebrow::before{content:'';width:16px;height:1px;background:var(--gold-bright)}
        .page-head h1{font-family:var(--font-display);font-size:clamp(28px,3.4vw,38px);font-weight:600;letter-spacing:-.01em}
        .page-head p{color:var(--paper-dim);margin-top:10px;font-size:15px;max-width:56ch}

        /* content */
        .dash-main{padding-block:32px 80px}

        .grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--line);border:1px solid var(--line);border-radius:4px;overflow:hidden;margin-bottom:32px}
        .metric{background:var(--ink-2);padding:26px 24px}
        .metric .k{font-family:var(--font-mono);font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:var(--paper-dim)}
        .metric .v{font-family:var(--font-mono);font-size:32px;color:var(--gold-bright);font-weight:500;margin-top:10px}
        .metric .v small{font-size:14px;color:var(--paper-dim);font-weight:400}
        .metric .d{font-size:13px;color:var(--paper-dim);margin-top:6px}

        .panel{
            background:var(--ink-2);border:1px solid var(--line);border-radius:4px;
            padding:36px;margin-bottom:24px;
        }
        .panel-head{display:flex;align-items:baseline;justify-content:space-between;gap:16px;margin-bottom:18px;flex-wrap:wrap}
        .panel-head h2{font-family:var(--font-display);font-size:22px;font-weight:600}
        .panel-head .tag{font-family:var(--font-mono);font-size:11px;color:var(--paper-dim);letter-spacing:.06em;text-transform:uppercase}
        .panel p{color:var(--paper-dim);font-size:14.5px;max-width:64ch}
        .panel .btn-gold{
            display:inline-flex;margin-top:20px;background:var(--gold);color:var(--ink);border:none;
            padding:11px 20px;border-radius:3px;font-size:14px;font-weight:500;cursor:pointer;
        }
        .panel .btn-gold:hover{background:var(--gold-bright)}

        .split{display:grid;grid-template-columns:1.3fr 0.7fr;gap:24px}

        .empty-list{list-style:none}
        .empty-list li{
            display:flex;align-items:center;gap:12px;
            padding-block:12px;border-top:1px solid var(--line);
            font-size:14px;color:var(--paper-dim);
        }
        .empty-list li:first-child{border-top:none}
        .empty-list .dot{width:6px;height:6px;border-radius:50%;background:var(--line);flex-shrink:0}

        @media (max-width:900px){
            nav.links{display:none}
            .grid-3{grid-template-columns:1fr}
            .split{grid-template-columns:1fr}
        }
    </style>
</head>
<body>

    <header class="site">
        <div class="wrap nav">
            <a href="{{ url('/') }}" class="brand">
                <svg class="brand-mark" viewBox="0 0 32 32" fill="none">
                    <circle cx="16" cy="16" r="15" stroke="#cf9a3d" stroke-width="1.4"/>
                    <path d="M16 6 L19 15 L16 26 L13 15 Z" fill="#e9b95a"/>
                    <circle cx="16" cy="16" r="2" fill="#0d1a2b"/>
                </svg>
                <span>CareerPath BN<small>Politeknik Brunei</small></span>
            </a>

            <nav class="links">
                <a href="{{ url('/dashboard') }}" class="active">Dashboard</a>
                <a href="#">My profile</a>
                <a href="#">Career matches</a>
                <a href="#">Development plan</a>
            </nav>

            <div class="nav-actions">
                <span class="who">Hi, <strong>{{ Auth::user()->name ?? 'Student' }}</strong></span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn">Log out</button>
                </form>
            </div>
        </div>
    </header>

    <main>
        <div class="wrap page-head reveal">
            <span class="eyebrow">Dashboard</span>
            <h1>Welcome back, {{ Auth::user()->name ?? 'Student' }}.</h1>
            <p>You're logged in. Build out your profile to get your first BIICF-aligned career matches and a readiness score.</p>
        </div>

        <div class="wrap dash-main">
            <div class="grid-3 reveal" style="animation-delay:.08s">
                <div class="metric">
                    <div class="k">Readiness score</div>
                    <div class="v">— <small>/ 100</small></div>
                    <div class="d">Complete your profile to calculate this.</div>
                </div>
                <div class="metric">
                    <div class="k">Career matches</div>
                    <div class="v">0</div>
                    <div class="d">No matches generated yet.</div>
                </div>
                <div class="metric">
                    <div class="k">Milestones tracked</div>
                    <div class="v">0</div>
                    <div class="d">Nothing added to your plan yet.</div>
                </div>
            </div>

            <div class="split">
                <div class="panel reveal" style="animation-delay:.14s">
                    <div class="panel-head">
                        <h2>Build your career profile</h2>
                        <span class="tag">Step 1 of 4</span>
                    </div>
                    <p>Tell us about your interests, academic programme, competencies, project experience and career aspirations. This is what the matching engine uses to rank BIICF-aligned career pathways for you, with reasons for each recommendation.</p>
                    <button type="button" class="btn-gold">Start my profile</button>
                </div>

                <div class="panel reveal" style="animation-delay:.2s">
                    <div class="panel-head">
                        <h2 style="font-size:18px">Recent activity</h2>
                    </div>
                    <ul class="empty-list">
                        <li><span class="dot"></span>No activity yet</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

</body>
</html>