@extends('layouts.app')

@section('title', 'My Profile')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
    .cpbn-dash{
        --ink:#0d1a2b; --ink-dim:#5b6675; --paper:#faf8f2; --card:#ffffff; --line:#e7e2d4;
        --gold:#cf9a3d; --gold-bright:#e9b95a; --gold-wash:#fbf1de;
        --rose:#c65b4e; --rose-wash:#fbeceb; --green:#4c8a68; --green-wash:#e9f3ee;
        --purple:#7a5ea8; --purple-wash:#f1ecf7;
        --font-display:'Fraunces', Georgia, serif; --font-body:'IBM Plex Sans', ui-sans-serif, system-ui, sans-serif;
        --font-mono:'IBM Plex Mono', ui-monospace, monospace;
        background:var(--paper); color:var(--ink); font-family:var(--font-body);
        margin:-24px -16px 0; padding:32px 20px 56px;
    }
    .cpbn-dash *{box-sizing:border-box}
    .cpbn-dash a{text-decoration:none;color:inherit}
    .cpbn-wrap{max-width:1100px;margin-inline:auto}

    .cpbn-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:24px}
    .cpbn-head h1{font-family:var(--font-display);font-weight:600;font-size:25px;letter-spacing:-.01em}
    .cpbn-head p.sub{color:var(--ink-dim);margin-top:4px;font-size:14.5px}

    .cpbn-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:5px;font-size:14px;font-weight:500;border:none;cursor:pointer;transition:background .15s}
    .cpbn-btn svg{width:14px;height:14px}
    .cpbn-btn-primary{background:var(--gold);color:var(--ink)}
    .cpbn-btn-primary:hover{background:var(--gold-bright)}

    .cpbn-card{background:var(--card);border:1px solid var(--line);border-radius:6px;padding:22px;margin-bottom:20px}

    .cpbn-bar{width:100%;background:#eee9db;border-radius:100px;height:10px;margin-top:10px;overflow:hidden}
    .cpbn-bar-fill{height:100%;background:var(--gold);border-radius:100px}
    .cpbn-comp-top{display:flex;align-items:center;justify-content:space-between}
    .cpbn-comp-top span:first-child{font-weight:500;font-size:14.5px}
    .cpbn-comp-num{font-family:var(--font-mono);font-size:24px;color:#8a6420;font-weight:500}
    .cpbn-comp-note{font-size:12.5px;color:var(--ink-dim);margin-top:10px}

    .cpbn-grid2{display:grid;grid-template-columns:1fr 1fr;gap:20px}

    .cpbn-panel-title{font-family:var(--font-display);font-size:16px;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:9px}
    .cpbn-panel-title svg{width:16px;height:16px;color:var(--gold)}

    .cpbn-info-row{margin-bottom:12px}
    .cpbn-info-row:last-child{margin-bottom:0}
    .cpbn-info-row label{display:block;font-family:var(--font-mono);font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-dim);margin-bottom:3px}
    .cpbn-info-row p{font-weight:500;font-size:14.5px}

    .cpbn-tags{display:flex;flex-wrap:wrap;gap:8px}
    .cpbn-tag{font-family:var(--font-mono);font-size:12.5px;padding:6px 12px;border-radius:100px;display:inline-flex;align-items:center;gap:6px}
    .cpbn-tag-purple{background:var(--purple-wash);color:#5a4180}
    .cpbn-tag-rose{background:var(--rose-wash);color:#8f3a30}
    .cpbn-tag small{opacity:.75}
    .cpbn-empty-sm{color:var(--ink-dim);font-size:13.5px}

    @media (max-width:820px){
        .cpbn-grid2{grid-template-columns:1fr}
    }
</style>

<div class="cpbn-dash">
    <div class="cpbn-wrap">

        <div class="cpbn-head">
            <div>
                <h1>My Profile</h1>
                <p class="sub">View and manage your personal information</p>
            </div>
            <a href="{{ route('student.profile.edit') }}" class="cpbn-btn cpbn-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                Edit Profile
            </a>
        </div>

        <!-- Profile Completion -->
        <div class="cpbn-card">
            <div class="cpbn-comp-top">
                <span>Profile Completion</span>
                <span class="cpbn-comp-num">{{ $profileCompletion ?? 0 }}%</span>
            </div>
            <div class="cpbn-bar"><div class="cpbn-bar-fill" style="width: {{ $profileCompletion ?? 0 }}%"></div></div>
            <p class="cpbn-comp-note">
                @if(($profileCompletion ?? 0) < 100)
                    Complete your profile to get better career recommendations
                @else
                    Your profile is complete!
                @endif
            </p>
        </div>

        <!-- Profile Information -->
        <div class="cpbn-grid2">
            <!-- Personal Information -->
            <div class="cpbn-card">
                <h3 class="cpbn-panel-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
                    Personal Information
                </h3>
                <div class="cpbn-info-row">
                    <label>Full Name</label>
                    <p>{{ $user->name ?? 'Not set' }}</p>
                </div>
                <div class="cpbn-info-row">
                    <label>Email</label>
                    <p>{{ $user->email ?? 'Not set' }}</p>
                </div>
                <div class="cpbn-info-row">
                    <label>Student ID</label>
                    <p>{{ $user->student_id ?? 'Not set' }}</p>
                </div>
                <div class="cpbn-info-row">
                    <label>Programme</label>
                    <p>{{ $user->programme ?? 'Not set' }}</p>
                </div>
            </div>

            <!-- Academic Information -->
            <div class="cpbn-card">
                <h3 class="cpbn-panel-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c0 1.5 3 3 6 3s6-1.5 6-3v-5"/></svg>
                    Academic Information
                </h3>
                <div class="cpbn-info-row">
                    <label>CGPA</label>
                    <p>{{ $user->cgpa ?? 'Not set' }}</p>
                </div>
                <div class="cpbn-info-row">
                    <label>Academic Records</label>
                    <p>{{ $user->academicRecords->count() ?? 0 }} records</p>
                </div>
            </div>

            <!-- Skills & Competencies -->
            <div class="cpbn-card">
                <h3 class="cpbn-panel-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    Skills &amp; Competencies
                </h3>
                @if($user->competencies && $user->competencies->count() > 0)
                    <div class="cpbn-tags">
                        @foreach($user->competencies as $skill)
                            <span class="cpbn-tag cpbn-tag-purple">
                                {{ $skill->skill_name }}
                                <small>({{ $skill->proficiency_level }})</small>
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="cpbn-empty-sm">No skills added yet</p>
                @endif
            </div>

            <!-- Interests -->
            <div class="cpbn-card">
                <h3 class="cpbn-panel-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.8 1-1a5.5 5.5 0 0 0 0-7.6z"/></svg>
                    Interests
                </h3>
                @if($user->interests && $user->interests->count() > 0)
                    <div class="cpbn-tags">
                        @foreach($user->interests as $interest)
                            <span class="cpbn-tag cpbn-tag-rose">{{ $interest->interest_name }}</span>
                        @endforeach
                    </div>
                @else
                    <p class="cpbn-empty-sm">No interests added yet</p>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection