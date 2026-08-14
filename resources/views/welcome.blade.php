<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CareerPath BN — AI Career Guidance for Politeknik Brunei</title>
    <meta name="description" content="CareerPath BN maps your interests, competencies and academic profile to real BIICF-aligned careers, shows exactly what's missing, and builds the plan to close the gap.">

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
        html{scroll-behavior:smooth}
        body{
            background:var(--ink);
            color:var(--paper);
            font-family:var(--font-body);
            font-size:16px;
            line-height:1.6;
            -webkit-font-smoothing:antialiased;
            overflow-x:hidden;
        }
        img,svg{display:block;max-width:100%}
        a{color:inherit;text-decoration:none}
        ul{list-style:none}
        .wrap{max-width:1180px;margin-inline:auto;padding-inline:28px}
        section{position:relative}

        /* ---------- utility: reveal on load ---------- */
        .reveal{opacity:0;transform:translateY(14px);animation:reveal .8s cubic-bezier(.2,.7,.2,1) forwards}
        @keyframes reveal{to{opacity:1;transform:translateY(0)}}
        @media (prefers-reduced-motion:reduce){
            .reveal{animation:none;opacity:1;transform:none}
            html{scroll-behavior:auto}
        }

        /* ---------- header ---------- */
        header.site{
            position:sticky;top:0;z-index:50;
            background:rgba(13,26,43,0.86);
            backdrop-filter:blur(10px);
            border-bottom:1px solid var(--line);
        }
        .nav{
            display:flex;align-items:center;justify-content:space-between;
            padding-block:16px;
        }
        .brand{display:flex;align-items:center;gap:10px;font-family:var(--font-display);font-size:20px;font-weight:600;letter-spacing:.01em}
        .brand-mark{width:30px;height:30px;flex-shrink:0}
        .brand small{display:block;font-family:var(--font-mono);font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:var(--gold-bright);font-weight:400;margin-top:1px}

        nav.links{display:flex;align-items:center;gap:32px}
        nav.links a{font-size:14px;color:var(--paper-dim);transition:color .2s}
        nav.links a:hover{color:var(--paper)}

        .nav-actions{display:flex;align-items:center;gap:12px}
        .btn{
            display:inline-flex;align-items:center;justify-content:center;gap:8px;
            padding:10px 20px;border-radius:3px;font-size:14px;font-weight:500;
            border:1px solid transparent;transition:all .2s;cursor:pointer;white-space:nowrap;
        }
        .btn-ghost{color:var(--paper);border-color:var(--line)}
        .btn-ghost:hover{border-color:var(--gold-bright);color:var(--gold-bright)}
        .btn-gold{background:var(--gold);color:var(--ink)}
        .btn-gold:hover{background:var(--gold-bright)}
        .nav-toggle{display:none}

        /* ---------- hero ---------- */
        .hero{padding-top:88px;padding-bottom:40px;overflow:hidden}
        .hero-inner{display:grid;grid-template-columns:1fr;gap:24px;text-align:left;max-width:760px}
        .eyebrow{
            display:inline-flex;align-items:center;gap:8px;
            font-family:var(--font-mono);font-size:12px;letter-spacing:.12em;text-transform:uppercase;
            color:var(--gold-bright);
        }
        .eyebrow::before{content:'';width:16px;height:1px;background:var(--gold-bright)}
        h1.headline{
            font-family:var(--font-display);
            font-weight:600;
            font-size:clamp(38px,6vw,64px);
            line-height:1.06;
            letter-spacing:-0.01em;
            color:var(--paper);
        }
        h1.headline em{color:var(--gold-bright);font-style:italic}
        .sub{
            font-size:18px;color:var(--paper-dim);max-width:56ch;
        }
        .hero-ctas{display:flex;flex-wrap:wrap;gap:14px;margin-top:8px}
        .btn-lg{padding:14px 26px;font-size:15px;border-radius:3px}

        /* route map svg */
        .route-wrap{margin-top:56px;position:relative}
        .route-svg{width:100%;height:auto}
        .route-label{
            font-family:var(--font-mono);
            font-size:11px;
            letter-spacing:.06em;
            text-transform:uppercase;
        }

        /* ---------- stats strip ---------- */
        .stats{
            border-top:1px solid var(--line);border-bottom:1px solid var(--line);
            background:var(--ink-2);
        }
        .stats-grid{
            display:grid;grid-template-columns:repeat(4,1fr);
        }
        .stat{
            padding:28px 20px;text-align:center;border-right:1px solid var(--line);
        }
        .stat:last-child{border-right:none}
        .stat .n{font-family:var(--font-mono);font-size:clamp(24px,3vw,34px);color:var(--gold-bright);font-weight:500}
        .stat .l{font-size:12.5px;color:var(--paper-dim);margin-top:4px;letter-spacing:.02em}

        /* ---------- section headers ---------- */
        .section{padding-block:96px}
        .section-head{max-width:620px;margin-bottom:56px}
        .section-head .eyebrow{margin-bottom:14px}
        h2.h{
            font-family:var(--font-display);
            font-size:clamp(28px,3.4vw,40px);
            font-weight:600;letter-spacing:-.01em;line-height:1.15;
        }
        .section-head p{color:var(--paper-dim);margin-top:14px;font-size:16px}

        /* ---------- problem section ---------- */
        .problem{background:var(--ink)}
        .problem-grid{display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:start}
        .problem-copy p{color:var(--paper-dim);margin-bottom:16px;font-size:16px}
        .problem-copy p strong{color:var(--paper);font-weight:500}
        .quote-card{
            background:var(--ink-2);border:1px solid var(--line);border-left:3px solid var(--rose);
            padding:28px;border-radius:2px;
        }
        .quote-card p{font-family:var(--font-display);font-size:19px;font-style:italic;color:var(--paper);line-height:1.5}
        .quote-card span{display:block;margin-top:14px;font-family:var(--font-mono);font-size:11.5px;color:var(--paper-dim);text-transform:uppercase;letter-spacing:.08em}

        /* ---------- how it works ---------- */
        .how{background:var(--ink-2);border-top:1px solid var(--line);border-bottom:1px solid var(--line)}
        .steps{display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:var(--line);border:1px solid var(--line);border-radius:4px;overflow:hidden}
        .step{background:var(--ink-2);padding:32px 26px;position:relative}
        .step .num{font-family:var(--font-mono);font-size:13px;color:var(--gold-bright);letter-spacing:.06em}
        .step h3{font-family:var(--font-display);font-size:21px;font-weight:600;margin-top:14px;margin-bottom:10px}
        .step p{font-size:14.5px;color:var(--paper-dim)}

        /* ---------- features ---------- */
        .features-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:var(--line);border:1px solid var(--line);border-radius:4px;overflow:hidden}
        .feat{background:var(--ink);padding:28px 24px;transition:background .2s}
        .feat:hover{background:var(--ink-2)}
        .feat .ic{width:22px;height:22px;color:var(--gold-bright);margin-bottom:16px}
        .feat h3{font-size:16px;font-weight:600;margin-bottom:8px;font-family:var(--font-body)}
        .feat p{font-size:13.5px;color:var(--paper-dim);line-height:1.55}

        /* ---------- BIICF section ---------- */
        .biicf{background:var(--ink-3);position:relative}
        .biicf-inner{display:grid;grid-template-columns:1.1fr 0.9fr;gap:56px;align-items:center}
        .biicf-copy p{color:var(--paper-dim);margin-bottom:16px}
        .chip-row{display:flex;flex-wrap:wrap;gap:8px;margin-top:24px}
        .chip{
            font-family:var(--font-mono);font-size:12px;padding:7px 12px;border:1px solid var(--line);
            border-radius:100px;color:var(--paper-dim);
        }
        .biicf-panel{
            background:var(--ink-2);border:1px solid var(--line);border-radius:4px;padding:32px;
        }
        .biicf-panel .row{display:flex;justify-content:space-between;align-items:baseline;padding-block:14px;border-bottom:1px solid var(--line)}
        .biicf-panel .row:last-child{border-bottom:none}
        .biicf-panel .row .k{font-size:14px;color:var(--paper-dim)}
        .biicf-panel .row .v{font-family:var(--font-mono);color:var(--gold-bright);font-size:15px}
        .biicf-panel .src{margin-top:20px;font-size:11.5px;color:var(--paper-dim);font-family:var(--font-mono)}

        /* ---------- CTA banner ---------- */
        .cta{
            background:linear-gradient(135deg, var(--ink-3), var(--ink));
            border-top:1px solid var(--line);
            padding-block:88px;text-align:center;
        }
        .cta h2{font-family:var(--font-display);font-size:clamp(28px,4vw,42px);font-weight:600;max-width:640px;margin-inline:auto}
        .cta p{color:var(--paper-dim);margin-top:16px;max-width:480px;margin-inline:auto}
        .cta .hero-ctas{justify-content:center;margin-top:32px}

        /* ---------- footer ---------- */
        footer{border-top:1px solid var(--line);padding-block:48px}
        .foot-grid{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px}
        .foot-note{font-size:13px;color:var(--paper-dim)}
        .foot-links{display:flex;gap:24px}
        .foot-links a{font-size:13px;color:var(--paper-dim);transition:color .2s}
        .foot-links a:hover{color:var(--paper)}

        /* ---------- responsive ---------- */
        @media (max-width:960px){
            .stats-grid{grid-template-columns:repeat(2,1fr)}
            .stat:nth-child(2){border-right:none}
            .problem-grid{grid-template-columns:1fr}
            .steps{grid-template-columns:repeat(2,1fr)}
            .features-grid{grid-template-columns:repeat(2,1fr)}
            .biicf-inner{grid-template-columns:1fr}
        }
        @media (max-width:680px){
            nav.links{display:none}
            .stats-grid{grid-template-columns:repeat(2,1fr)}
            .steps{grid-template-columns:1fr}
            .features-grid{grid-template-columns:1fr}
            .section{padding-block:64px}
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
                <a href="#how">How it works</a>
                <a href="#features">Platform</a>
                <a href="#biicf">BIICF alignment</a>
                <a href="#about">About</a>
            </nav>

            <div class="nav-actions">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-gold">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-ghost">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-gold">Get started</a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <main>
        <!-- HERO -->
        <section class="hero">
            <div class="wrap">
                <div class="hero-inner reveal">
                    <span class="eyebrow">AI career guidance · Built for Politeknik Brunei</span>
                    <h1 class="headline">Stop guessing your career.<br>Start <em>mapping</em> it.</h1>
                    <p class="sub">CareerPath BN reads your interests, academic record and competencies, matches them against real ICT job roles under the Brunei ICT Industry Competency Framework, and shows you exactly what stands between you and each one.</p>
                    <div class="hero-ctas">
                        <a href="{{ Route::has('register') ? route('register') : '#' }}" class="btn btn-gold btn-lg">Build my profile</a>
                        <a href="#how" class="btn btn-ghost btn-lg">See how matching works</a>
                    </div>
                </div>

                <div class="route-wrap reveal" style="animation-delay:.15s">
                    <svg class="route-svg" viewBox="0 0 1120 260" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 210 C 160 210, 180 60, 300 60 S 440 210, 560 210 S 700 60, 820 60 S 960 210, 1100 130"
                              stroke="#cf9a3d" stroke-width="1.5" stroke-dasharray="2 8" stroke-linecap="round"/>

                        <g>
                            <circle cx="20" cy="210" r="5" fill="#e9b95a"/>
                            <text x="20" y="238" fill="#c7c2b4" class="route-label" text-anchor="start">01 · PROFILE</text>
                        </g>
                        <g>
                            <circle cx="300" cy="60" r="5" fill="#e9b95a"/>
                            <text x="300" y="34" fill="#c7c2b4" class="route-label" text-anchor="middle">02 · MATCH</text>
                        </g>
                        <g>
                            <circle cx="560" cy="210" r="5" fill="#c65b4e"/>
                            <text x="560" y="238" fill="#c7c2b4" class="route-label" text-anchor="middle">03 · GAPS</text>
                        </g>
                        <g>
                            <circle cx="820" cy="60" r="5" fill="#e9b95a"/>
                            <text x="820" y="34" fill="#c7c2b4" class="route-label" text-anchor="middle">04 · PLAN</text>
                        </g>
                        <g>
                            <circle cx="1100" cy="130" r="6.5" fill="none" stroke="#e9b95a" stroke-width="1.5"/>
                            <circle cx="1100" cy="130" r="2.5" fill="#e9b95a"/>
                            <text x="1080" y="112" fill="#f5f1e6" class="route-label" text-anchor="end">CAREER</text>
                        </g>
                    </svg>
                </div>
            </div>
        </section>

        <!-- STATS -->
        <section class="stats">
            <div class="wrap stats-grid">
                <div class="stat"><div class="n">3</div><div class="l">ICT job roles mapped</div></div>
                <div class="stat"><div class="n">10</div><div class="l">Competencies referenced</div></div>
                <div class="stat"><div class="n">2</div><div class="l">ICT subsectors covered</div></div>
                <div class="stat"><div class="n">1</div><div class="l">Framework: BIICF (AITI)</div></div>
            </div>
        </section>

        <!-- PROBLEM -->
        <section class="problem section" id="about">
            <div class="wrap">
                <div class="section-head">
                    <span class="eyebrow">The problem</span>
                    <h2 class="h">Generic questionnaires don't know your transcript.</h2>
                </div>
                <div class="problem-grid">
                    <div class="problem-copy">
                        <p>Most career guidance today still runs on <strong>generic questionnaires and manual counselling sessions</strong> — a format that can't reasonably account for a student's actual academic performance, project history, competencies and career aspirations at the same time.</p>
                        <p>Industry competency frameworks like BIICF already define what each role actually requires. The gap isn't information — it's <strong>translation</strong>: turning a dense framework document into something a student can see themselves in.</p>
                        <p>CareerPath BN is that translation layer — for every academic programme, starting with ICT.</p>
                    </div>
                    <div class="quote-card">
                        <p>"Students may have limited awareness of the competencies, qualifications and professional development required for different occupations — even where the framework already exists."</p>
                        <span>Project brief · CareerPath BN, FYP</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- HOW IT WORKS -->
        <section class="how" id="how">
            <div class="wrap section">
                <div class="section-head">
                    <span class="eyebrow">How it works</span>
                    <h2 class="h">From profile to career plan, in four moves.</h2>
                    <p>Each stage feeds the next — nothing is a black box, and every recommendation comes with a reason.</p>
                </div>
                <div class="steps">
                    <div class="step">
                        <div class="num">01</div>
                        <h3>Build your profile</h3>
                        <p>Interests, preferred work activities, programme, academic record, competencies, projects and aspirations — captured once, structured properly.</p>
                    </div>
                    <div class="step">
                        <div class="num">02</div>
                        <h3>Get matched, with reasons</h3>
                        <p>An explainable recommendation engine ranks suitable BIICF career pathways and states plainly why each one fits.</p>
                    </div>
                    <div class="step">
                        <div class="num">03</div>
                        <h3>See the gap</h3>
                        <p>Your current competencies plotted against what the role requires — a readiness score, not a guess.</p>
                    </div>
                    <div class="step">
                        <div class="num">04</div>
                        <h3>Follow the plan</h3>
                        <p>Milestones, certifications and projects generated to close the gap — tracked as you complete them.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FEATURES -->
        <section class="section" id="features">
            <div class="wrap">
                <div class="section-head">
                    <span class="eyebrow">The platform</span>
                    <h2 class="h">Everything a student — and an adviser — needs in one place.</h2>
                </div>
                <div class="features-grid">
                    <div class="feat">
                        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
                        <h3>Career profiling form</h3>
                        <p>Interests, preferences, academic history, competencies and aspirations, structured for matching.</p>
                    </div>
                    <div class="feat">
                        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2 3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                        <h3>AI career matching</h3>
                        <p>Ranks suitable pathways with a clear, explainable reason behind every recommendation.</p>
                    </div>
                    <div class="feat">
                        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        <h3>Multi-programme support</h3>
                        <p>Built for every academic programme at PB — ICT ships first as the pilot on BIICF.</p>
                    </div>
                    <div class="feat">
                        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2 2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                        <h3>BIICF career alignment</h3>
                        <p>Maps ICT students directly to BIICF roles, technical and soft-skill competencies, and proficiency levels.</p>
                    </div>
                    <div class="feat">
                        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                        <h3>Career readiness score</h3>
                        <p>One number combining academics, competencies, certifications, projects and experience.</p>
                    </div>
                    <div class="feat">
                        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 12h4l3 8 4-16 3 8h4"/></svg>
                        <h3>Competency gap visualisation</h3>
                        <p>Current vs. required competencies for a chosen career, laid out clearly — no spreadsheet required.</p>
                    </div>
                    <div class="feat">
                        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19V6a2 2 0 0 1 2-2h9l5 5v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/><path d="M9 21v-6h6v6"/></svg>
                        <h3>Personalised development plan</h3>
                        <p>AI-generated milestones — competencies, projects, certifications and learning activities.</p>
                    </div>
                    <div class="feat">
                        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <h3>AI career adviser</h3>
                        <p>A source-grounded assistant that answers using verified BIICF and institutional documents only.</p>
                    </div>
                    <div class="feat">
                        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 3v18h18"/><path d="M7 15l4-6 3 3 5-8"/></svg>
                        <h3>Adviser dashboard</h3>
                        <p>Lecturers review interests, readiness levels, common gaps and progress across their cohort.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- BIICF -->
        <section class="biicf" id="biicf">
            <div class="wrap section">
                <div class="biicf-inner">
                    <div class="biicf-copy">
                        <span class="eyebrow">Framework alignment</span>
                        <h2 class="h" style="margin-top:14px;margin-bottom:18px">Grounded in an official industry standard — not a survey.</h2>
                        <p>The Brunei ICT Industry Competency Framework (BIICF) already defines job roles, technical and soft-skill competencies, proficiency levels, entry requirements and certification pathways for the local ICT sector.</p>
                        <p>CareerPath BN doesn't reinvent that — it makes it navigable, mapping each student's profile onto it directly, with room to bring in other programme and industry frameworks in later phases.</p>
                        <div class="chip-row">
                            <span class="chip">Software Development</span>
                            <span class="chip">Networking &amp; Infrastructure</span>
                            <span class="chip">Cybersecurity</span>
                            <span class="chip">Data &amp; AI</span>
                            <span class="chip">IT Support</span>
                            <span class="chip">Digital Media</span>
                        </div>
                    </div>
                    <div class="biicf-panel">
                        <div class="row"><span class="k">Framework</span><span class="v">BIICF</span></div>
                        <div class="row"><span class="k">Issuing body</span><span class="v">AITI</span></div>
                        <div class="row"><span class="k">Job roles</span><span class="v">3</span></div>
                        <div class="row"><span class="k">Competencies</span><span class="v">10</span></div>
                        <div class="row"><span class="k">Subsectors</span><span class="v">2</span></div>
                        <div class="row"><span class="k">Pilot programme</span><span class="v">ICT</span></div>
                        <p class="src">Source: AITI — ICT Industry Competency Framework</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="cta">
            <div class="wrap">
                <h2 class="reveal">Your career shouldn't start with a guess.</h2>
                <p class="reveal" style="animation-delay:.1s">Build your profile and see which BIICF-aligned careers actually fit — and exactly what to do next.</p>
                <div class="hero-ctas reveal" style="animation-delay:.2s">
                    <a href="{{ Route::has('register') ? route('register') : '#' }}" class="btn btn-gold btn-lg">Get started free</a>
                    <a href="#how" class="btn btn-ghost btn-lg">Learn more</a>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="wrap foot-grid">
            <div class="foot-note">CareerPath BN — a Final Year Project at Politeknik Brunei, aligned with AITI's BIICF.</div>
            <div class="foot-links">
                <a href="#how">How it works</a>
                <a href="#features">Platform</a>
                <a href="#biicf">BIICF</a>
                @if (Route::has('login'))
                    <a href="{{ route('login') }}">Log in</a>
                @endif
            </div>
        </div>
    </footer>

</body>
</html>