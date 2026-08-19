@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
    .cpbn-dash{
        --ink:#0d1a2b;
        --ink-dim:#5b6675;
        --paper:#faf8f2;
        --card:#ffffff;
        --line:#e7e2d4;
        --gold:#cf9a3d;
        --gold-bright:#e9b95a;
        --gold-wash:#fbf1de;
        --rose:#c65b4e;
        --rose-wash:#fbeceb;
        --green:#4c8a68;
        --green-wash:#e9f3ee;
        --font-display:'Fraunces', Georgia, serif;
        --font-body:'IBM Plex Sans', ui-sans-serif, system-ui, sans-serif;
        --font-mono:'IBM Plex Mono', ui-monospace, monospace;

        background:var(--paper);
        color:var(--ink);
        font-family:var(--font-body);
        margin:-24px -16px 0;
        padding:32px 20px 56px;
    }
    .cpbn-dash *{box-sizing:border-box}
    .cpbn-dash a{text-decoration:none;color:inherit}
    .cpbn-wrap{max-width:1180px;margin-inline:auto}

    .cpbn-head{
        display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;
        margin-bottom:32px;
    }
    .cpbn-head h1{
        font-family:var(--font-display);font-weight:600;font-size:26px;letter-spacing:-.01em;color:var(--ink);
    }
    .cpbn-head p.sub{color:var(--ink-dim);margin-top:4px;font-size:14.5px}
    .cpbn-badges{display:flex;gap:10px;flex-wrap:wrap}
    .cpbn-chip{
        display:inline-flex;align-items:center;gap:6px;
        font-family:var(--font-mono);font-size:12px;letter-spacing:.02em;
        padding:7px 13px;border-radius:100px;white-space:nowrap;
    }
    .cpbn-chip svg{width:13px;height:13px}
    .cpbn-chip-gold{background:var(--gold-wash);color:#8a6420}
    .cpbn-chip-ink{background:#eef1f5;color:var(--ink-dim)}
    .cpbn-chip-green{background:var(--green-wash);color:var(--green)}
    .cpbn-chip-rose{background:var(--rose-wash);color:var(--rose)}

    .cpbn-stats{
        display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;
    }
    .cpbn-card{
        background:var(--card);border:1px solid var(--line);border-radius:6px;padding:22px;
        transition:box-shadow .2s, border-color .2s;
    }
    .cpbn-card:hover{box-shadow:0 8px 24px -12px rgba(13,26,43,0.12);border-color:#d8d2c0}
    .cpbn-stat-top{display:flex;justify-content:space-between;align-items:flex-start}
    .cpbn-stat-label{font-size:12.5px;color:var(--ink-dim)}
    .cpbn-stat-num{font-family:var(--font-mono);font-size:28px;font-weight:500;margin-top:6px}
    .cpbn-icon-badge{
        width:38px;height:38px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;
    }
    .cpbn-icon-badge svg{width:18px;height:18px}
    .ib-gold{background:var(--gold-wash);color:#a97a1f}
    .ib-green{background:var(--green-wash);color:var(--green)}
    .ib-rose{background:var(--rose-wash);color:var(--rose)}
    .ib-ink{background:#eef1f5;color:var(--ink-dim)}

    .cpbn-bar{width:100%;background:#eee9db;border-radius:100px;height:6px;margin-top:14px;overflow:hidden}
    .cpbn-bar-fill{height:100%;background:var(--gold);border-radius:100px}

    .cpbn-actions{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:32px}
    .cpbn-btn{
        display:inline-flex;align-items:center;gap:9px;padding:11px 20px;border-radius:5px;
        font-size:14px;font-weight:500;border:1px solid transparent;transition:all .15s;cursor:pointer;
    }
    .cpbn-btn svg{width:15px;height:15px}
    .cpbn-btn-primary{background:var(--gold);color:var(--ink)}
    .cpbn-btn-primary:hover{background:var(--gold-bright)}
    .cpbn-btn-outline{background:var(--card);border-color:var(--line);color:var(--ink)}
    .cpbn-btn-outline:hover{border-color:var(--gold);color:#8a6420}

    .cpbn-cols{display:grid;grid-template-columns:2fr 1fr;gap:20px}
    .cpbn-panel{background:var(--card);border:1px solid var(--line);border-radius:6px;padding:24px}
    .cpbn-panel + .cpbn-panel{margin-top:20px}
    .cpbn-panel-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}
    .cpbn-panel-head h3{
        font-family:var(--font-display);font-size:17px;font-weight:600;display:flex;align-items:center;gap:8px;
    }
    .cpbn-panel-head h3 svg{width:16px;height:16px;color:var(--gold)}
    .cpbn-panel-head a{font-size:12.5px;color:var(--ink-dim);display:flex;align-items:center;gap:5px}
    .cpbn-panel-head a:hover{color:var(--ink)}

    .cpbn-rec{
        border:1px solid var(--line);border-radius:6px;padding:16px 18px;margin-bottom:12px;transition:border-color .15s;
    }
    .cpbn-rec:hover{border-color:var(--gold)}
    .cpbn-rec-top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px}
    .cpbn-rec-title{font-weight:600;font-size:15px}
    .cpbn-rec-sub{font-size:13px;color:var(--ink-dim);margin-top:3px;display:flex;align-items:center;gap:5px}
    .cpbn-rec-sub svg{width:12px;height:12px}
    .cpbn-match{
        font-family:var(--font-mono);font-size:12.5px;background:var(--gold-wash);color:#8a6420;
        padding:5px 11px;border-radius:100px;white-space:nowrap;flex-shrink:0;
    }
    .cpbn-rec-links{display:flex;gap:16px;margin-top:12px}
    .cpbn-rec-links a{font-size:12.5px;color:var(--ink-dim);display:flex;align-items:center;gap:5px}
    .cpbn-rec-links a:hover{color:#8a6420}
    .cpbn-rec-links svg{width:12px;height:12px}

    .cpbn-empty{text-align:center;padding:40px 20px}
    .cpbn-empty svg{width:34px;height:34px;color:var(--gold);margin-inline:auto;margin-bottom:14px}
    .cpbn-empty p.t{color:var(--ink);font-size:14.5px;font-weight:500}
    .cpbn-empty p.s{color:var(--ink-dim);font-size:13px;margin-top:4px}
    .cpbn-empty .cpbn-btn{margin-top:16px}

    .cpbn-prow{display:flex;justify-content:space-between;font-size:13px;color:var(--ink-dim);margin-bottom:6px}
    .cpbn-prow span:last-child{font-weight:600;color:var(--ink);font-family:var(--font-mono)}

    .cpbn-activity{padding-block:11px;border-bottom:1px solid var(--line)}
    .cpbn-activity:last-child{border-bottom:none;padding-bottom:0}
    .cpbn-activity:first-child{padding-top:0}
    .cpbn-activity p.m{font-size:13.5px;color:var(--ink)}
    .cpbn-activity p.t{font-size:11.5px;color:var(--ink-dim);font-family:var(--font-mono);margin-top:2px}
    .cpbn-empty-sm{text-align:center;color:var(--ink-dim);font-size:13px;padding-block:20px}

    @media (max-width:960px){
        .cpbn-stats{grid-template-columns:repeat(2,1fr)}
        .cpbn-cols{grid-template-columns:1fr}
    }
    @media (max-width:560px){
        .cpbn-stats{grid-template-columns:1fr}
    }
</style>

<div class="cpbn-dash">
    <div class="cpbn-wrap">

        <!-- Welcome Header -->
        <div class="cpbn-head">
            <div>
                <h1>Welcome back, {{ Auth::user()->first_name ?? Auth::user()->name }} 👋</h1>
                <p class="sub">{{ Auth::user()->programme ?? 'Complete your profile to get started' }}</p>
            </div>
            <div class="cpbn-badges">
                <span class="cpbn-chip cpbn-chip-gold">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c0 1.5 3 3 6 3s6-1.5 6-3v-5"/></svg>
                    Student
                </span>
                <span class="cpbn-chip cpbn-chip-ink">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    {{ now()->format('d M Y') }}
                </span>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="cpbn-stats">
            <!-- Profile Completion -->
            <div class="cpbn-card">
                <div class="cpbn-stat-top">
                    <div>
                        <p class="cpbn-stat-label">Profile Completion</p>
                        <p class="cpbn-stat-num">{{ $profileCompletion ?? 0 }}%</p>
                    </div>
                    <div class="cpbn-icon-badge ib-gold">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
                    </div>
                </div>
                <div class="cpbn-bar"><div class="cpbn-bar-fill" style="width: {{ $profileCompletion ?? 0 }}%"></div></div>
            </div>

            <!-- Recommendations -->
            <div class="cpbn-card">
                <div class="cpbn-stat-top">
                    <div>
                        <p class="cpbn-stat-label">Career Recommendations</p>
                        <p class="cpbn-stat-num">{{ $recommendationCount ?? 0 }}</p>
                    </div>
                    <div class="cpbn-icon-badge ib-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4.5"/><circle cx="12" cy="12" r="1"/></svg>
                    </div>
                </div>
            </div>

            <!-- Readiness Score -->
            <div class="cpbn-card">
                <div class="cpbn-stat-top">
                    <div>
                        <p class="cpbn-stat-label">Career Readiness</p>
                        <p class="cpbn-stat-num">{{ $readinessScore ?? 0 }}%</p>
                    </div>
                    <div class="cpbn-icon-badge ib-rose">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/></svg>
                    </div>
                </div>
            </div>

            <!-- Milestones -->
            <div class="cpbn-card">
                <div class="cpbn-stat-top">
                    <div>
                        <p class="cpbn-stat-label">Milestones Completed</p>
                        <p class="cpbn-stat-num">{{ $milestoneCount ?? 0 }}</p>
                    </div>
                    <div class="cpbn-icon-badge ib-ink">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 21V4a1 1 0 0 1 1-1h9l5 5v13"/><path d="M9 21v-6h6v6"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="cpbn-actions">
            <a href="{{ route('student.profile') }}" class="cpbn-btn cpbn-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                Update Profile
            </a>
            <a href="#" class="cpbn-btn cpbn-btn-outline">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2 3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                Career Assessment
            </a>
            <a href="#" class="cpbn-btn cpbn-btn-outline">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                AI Career Adviser
            </a>
            <a href="{{ route('student.milestones') }}" class="cpbn-btn cpbn-btn-outline">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22V4"/><path d="M4 4h14l-3 4 3 4H4"/></svg>
                Track Milestones
            </a>
        </div>

        <!-- Two Column Layout -->
        <div class="cpbn-cols">
            <!-- Left: Recommendations -->
            <div class="cpbn-panel">
                <div class="cpbn-panel-head">
                    <h3>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l2.9 6.6 7.1.7-5.4 4.7 1.7 7-6.3-3.8L5.7 21l1.7-7-5.4-4.7 7.1-.7z"/></svg>
                        Top Career Matches
                    </h3>
                    <a href="#">
                        View all
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>

                @if(isset($recommendations) && count($recommendations) > 0)
                    @foreach($recommendations as $rec)
                        <div class="cpbn-rec">
                            <div class="cpbn-rec-top">
                                <div>
                                    <div class="cpbn-rec-title">{{ $rec->career->job_title ?? 'Career' }}</div>
                                    <div class="cpbn-rec-sub">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1m4 0h1m-6 4h1m4 0h1"/></svg>
                                        {{ $rec->career->subsector ?? '' }}
                                    </div>
                                </div>
                                <span class="cpbn-match">{{ $rec->match_score ?? 0 }}% Match</span>
                            </div>
                            <div class="cpbn-rec-links">
                                <a href="#">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 16v-5M12 8h.01"/></svg>
                                    Details
                                </a>
                                <a href="#">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h4l3 8 4-16 3 8h4"/></svg>
                                    Skill Gaps
                                </a>
                                <a href="#">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22V4"/><path d="M4 4h14l-3 4 3 4H4"/></svg>
                                    Development Plan
                                </a>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="cpbn-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l2.9 6.6 7.1.7-5.4 4.7 1.7 7-6.3-3.8L5.7 21l1.7-7-5.4-4.7 7.1-.7z"/></svg>
                        <p class="t">No recommendations yet.</p>
                        <p class="s">Complete your profile and run a career assessment.</p>
                        <a href="{{ route('student.profile') }}" class="cpbn-btn cpbn-btn-primary">Complete Profile</a>
                    </div>
                @endif
            </div>

            <!-- Right: Profile Status & Activity -->
            <div>
                <!-- Profile Status -->
                <div class="cpbn-panel">
                    <div class="cpbn-panel-head" style="margin-bottom:14px">
                        <h3 style="font-size:15px">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
                            Profile Status
                        </h3>
                    </div>
                    <div class="cpbn-prow">
                        <span>Completion</span>
                        <span>{{ $profileCompletion ?? 0 }}%</span>
                    </div>
                    <div class="cpbn-bar"><div class="cpbn-bar-fill" style="width: {{ $profileCompletion ?? 0 }}%"></div></div>
                    <div style="margin-top:14px">
                        @if(($profileCompletion ?? 0) >= 70)
                            <span class="cpbn-chip cpbn-chip-green">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                                Profile Complete
                            </span>
                        @else
                            <span class="cpbn-chip cpbn-chip-rose">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01"/><circle cx="12" cy="12" r="9"/></svg>
                                Incomplete &middot; {{ 100 - ($profileCompletion ?? 0) }}% remaining
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="cpbn-panel">
                    <div class="cpbn-panel-head" style="margin-bottom:8px">
                        <h3 style="font-size:15px">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                            Recent Activity
                        </h3>
                    </div>
                    @if(isset($recentActivities) && count($recentActivities) > 0)
                        @foreach($recentActivities as $activity)
                            <div class="cpbn-activity">
                                <p class="m">{{ $activity['message'] ?? 'Activity logged' }}</p>
                                <p class="t">{{ $activity['time'] ?? 'Just now' }}</p>
                            </div>
                        @endforeach
                    @else
                        <p class="cpbn-empty-sm">No recent activity. Start exploring your career options!</p>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

@endsection