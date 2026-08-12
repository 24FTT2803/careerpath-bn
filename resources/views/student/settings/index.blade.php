@extends('layouts.app')

@section('title', 'Settings')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
    .cpbn-dash{
        --ink:#0d1a2b; --ink-dim:#5b6675; --paper:#faf8f2; --card:#ffffff; --line:#e7e2d4;
        --gold:#cf9a3d; --gold-bright:#e9b95a; --gold-wash:#fbf1de;
        --rose:#c65b4e; --rose-wash:#fbeceb; --green:#4c8a68; --green-wash:#e9f3ee;
        --font-display:'Fraunces', Georgia, serif; --font-body:'IBM Plex Sans', ui-sans-serif, system-ui, sans-serif;
        --font-mono:'IBM Plex Mono', ui-monospace, monospace;
        background:var(--paper); color:var(--ink); font-family:var(--font-body);
        margin:-24px -16px 0; padding:32px 20px 56px;
    }
    .cpbn-dash *{box-sizing:border-box}
    .cpbn-dash a{text-decoration:none;color:inherit}
    .cpbn-wrap{max-width:1100px;margin-inline:auto}

    .cpbn-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:28px}
    .cpbn-head h1{font-family:var(--font-display);font-weight:600;font-size:24px;letter-spacing:-.01em}
    .cpbn-head p.sub{color:var(--ink-dim);margin-top:4px;font-size:14.5px}
    .cpbn-back{display:flex;align-items:center;gap:6px;font-size:13.5px;color:var(--ink-dim)}
    .cpbn-back:hover{color:var(--ink)}
    .cpbn-back svg{width:14px;height:14px}

    .cpbn-cols-settings{display:grid;grid-template-columns:1fr 2.2fr;gap:20px;align-items:start}
    .cpbn-card{background:var(--card);border:1px solid var(--line);border-radius:6px;padding:22px}
    .cpbn-card + .cpbn-card{margin-top:20px}

    /* sidebar */
    .cpbn-me{display:flex;align-items:center;gap:12px;padding-bottom:18px;margin-bottom:14px;border-bottom:1px solid var(--line)}
    .cpbn-avatar{
        width:44px;height:44px;border-radius:50%;background:var(--gold-wash);color:#8a6420;
        display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:600;font-size:17px;flex-shrink:0;
    }
    .cpbn-me .n{font-weight:600;font-size:14.5px}
    .cpbn-me .e{font-size:12.5px;color:var(--ink-dim)}

    .cpbn-navlink{display:flex;align-items:center;gap:11px;padding:10px 12px;border-radius:5px;font-size:14px;color:var(--ink-dim);margin-bottom:2px}
    .cpbn-navlink svg{width:16px;height:16px;flex-shrink:0}
    .cpbn-navlink:hover{background:#f4f1e7}
    .cpbn-navlink.active{background:var(--gold-wash);color:#8a6420;font-weight:500}
    .cpbn-navlink.danger{color:var(--rose);margin-top:12px;padding-top:14px;border-top:1px solid var(--line);border-radius:0 0 5px 5px}
    .cpbn-navlink.danger:hover{background:var(--rose-wash)}
    .cpbn-navform button{all:unset;display:flex;align-items:center;gap:11px;padding:10px 12px;font-size:14px;color:var(--rose);cursor:pointer;width:100%;margin-top:12px;border-top:1px solid var(--line);border-radius:0;padding-top:14px}
    .cpbn-navform button:hover{background:var(--rose-wash)}
    .cpbn-navform button svg{width:16px;height:16px}

    /* content */
    .cpbn-panel-title{font-family:var(--font-display);font-size:16.5px;font-weight:600;margin-bottom:18px;display:flex;align-items:center;gap:9px}
    .cpbn-panel-title svg{width:17px;height:17px;color:var(--gold)}

    .cpbn-info-row{margin-bottom:14px}
    .cpbn-info-row label{display:block;font-family:var(--font-mono);font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-dim);margin-bottom:3px}
    .cpbn-info-row p{font-weight:500;font-size:14.5px}
    .cpbn-edit-link{display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#8a6420;margin-top:6px}
    .cpbn-edit-link svg{width:13px;height:13px}

    .cpbn-alert{padding:13px 16px;border-radius:4px;font-size:13.5px;margin-bottom:16px;display:flex;align-items:flex-start;gap:9px}
    .cpbn-alert svg{width:15px;height:15px;flex-shrink:0;margin-top:1px}
    .cpbn-alert-success{background:var(--green-wash);color:#2e5c43;border:1px solid rgba(76,138,104,0.25)}
    .cpbn-alert-error{background:var(--rose-wash);color:#8f3a30;border:1px solid rgba(198,91,78,0.25)}
    .cpbn-alert ul{margin:0;padding-left:2px;list-style:none}
    .cpbn-alert li{margin-top:3px}
    .cpbn-alert li:first-child{margin-top:0}

    .cpbn-field{margin-bottom:16px}
    .cpbn-field label{display:block;font-size:13px;font-weight:500;color:var(--ink);margin-bottom:6px}
    .cpbn-field input{
        width:100%;padding:10px 13px;border-radius:4px;border:1px solid var(--line);background:#fff;
        font-family:var(--font-body);font-size:14px;color:var(--ink);
    }
    .cpbn-field input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(207,154,61,0.15)}
    .cpbn-hint{font-size:11.5px;color:var(--ink-dim);margin-top:5px}

    .cpbn-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:5px;font-size:14px;font-weight:500;border:none;cursor:pointer;transition:background .15s}
    .cpbn-btn svg{width:14px;height:14px}
    .cpbn-btn-primary{background:var(--gold);color:var(--ink)}
    .cpbn-btn-primary:hover{background:var(--gold-bright)}

    @media (max-width:820px){
        .cpbn-cols-settings{grid-template-columns:1fr}
    }
</style>

<div class="cpbn-dash">
    <div class="cpbn-wrap">

        <div class="cpbn-head">
            <div>
                <h1>Settings</h1>
                <p class="sub">Manage your account settings and preferences</p>
            </div>
            <a href="{{ route('student.dashboard') }}" class="cpbn-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Back to Dashboard
            </a>
        </div>

        <div class="cpbn-cols-settings">
            <!-- Sidebar -->
            <div class="cpbn-card">
                <div class="cpbn-me">
                    <div class="cpbn-avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
                    <div>
                        <p class="n">{{ Auth::user()->name }}</p>
                        <p class="e">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <a href="{{ route('student.profile') }}" class="cpbn-navlink">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
                    Profile
                </a>
                <a href="{{ route('student.settings') }}" class="cpbn-navlink active">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/></svg>
                    Settings
                </a>
                <a href="#" class="cpbn-navlink">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                    Notifications
                </a>
                <form method="POST" action="{{ route('logout') }}" class="cpbn-navform">
                    @csrf
                    <button type="submit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                        Logout
                    </button>
                </form>
            </div>

            <!-- Settings Content -->
            <div>
                <!-- Account Information -->
                <div class="cpbn-card">
                    <h3 class="cpbn-panel-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
                        Account Information
                    </h3>
                    <div class="cpbn-info-row">
                        <label>Name</label>
                        <p>{{ Auth::user()->name }}</p>
                    </div>
                    <div class="cpbn-info-row">
                        <label>Email</label>
                        <p>{{ Auth::user()->email }}</p>
                    </div>
                    <div class="cpbn-info-row">
                        <label>Student ID</label>
                        <p>{{ Auth::user()->student_id ?? 'Not set' }}</p>
                    </div>
                    <div class="cpbn-info-row">
                        <label>Programme</label>
                        <p>{{ Auth::user()->programme ?? 'Not set' }}</p>
                    </div>
                    <div class="cpbn-info-row" style="margin-bottom:0">
                        <label>Account Created</label>
                        <p>{{ Auth::user()->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                    <a href="{{ route('student.profile.edit') }}" class="cpbn-edit-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                        Edit Profile
                    </a>
                </div>

                <!-- Change Password -->
                <div class="cpbn-card">
                    <h3 class="cpbn-panel-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                        Change Password
                    </h3>

                    @if(session('success'))
                        <div class="cpbn-alert cpbn-alert-success">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                            {{ session('success') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="cpbn-alert cpbn-alert-error">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('student.settings.password') }}">
                        @csrf
                        @method('PUT')

                        <div class="cpbn-field">
                            <label>Current Password</label>
                            <input type="password" name="current_password" required>
                        </div>
                        <div class="cpbn-field">
                            <label>New Password</label>
                            <input type="password" name="password" required minlength="8">
                            <p class="cpbn-hint">Minimum 8 characters</p>
                        </div>
                        <div class="cpbn-field" style="margin-bottom:20px">
                            <label>Confirm New Password</label>
                            <input type="password" name="password_confirmation" required>
                        </div>

                        <button type="submit" class="cpbn-btn cpbn-btn-primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                            Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection