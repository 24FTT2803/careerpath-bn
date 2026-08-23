@extends('layouts.app')

@section('title', 'Notifications')

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
    .cpbn-wrap{max-width:900px;margin-inline:auto}

    .cpbn-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:22px}
    .cpbn-head h1{font-family:var(--font-display);font-weight:600;font-size:24px;letter-spacing:-.01em}
    .cpbn-head p.sub{color:var(--ink-dim);margin-top:4px;font-size:14.5px}
    .cpbn-head-actions{display:flex;align-items:center;gap:14px}
    .cpbn-back{display:flex;align-items:center;gap:6px;font-size:13.5px;color:var(--ink-dim)}
    .cpbn-back:hover{color:var(--ink)}
    .cpbn-back svg{width:14px;height:14px}

    .cpbn-btn{display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:5px;font-size:13.5px;font-weight:500;border:none;cursor:pointer;transition:background .15s}
    .cpbn-btn svg{width:13px;height:13px}
    .cpbn-btn-primary{background:var(--gold);color:var(--ink)}
    .cpbn-btn-primary:hover{background:var(--gold-bright)}

    .cpbn-banner{
        background:var(--gold-wash);border:1px solid rgba(207,154,61,0.3);color:#8a6420;
        padding:13px 16px;border-radius:5px;font-size:14px;margin-bottom:22px;display:flex;align-items:center;gap:9px;
    }
    .cpbn-banner svg{width:16px;height:16px;flex-shrink:0}
    .cpbn-banner strong{font-weight:700}

    .cpbn-list{background:var(--card);border:1px solid var(--line);border-radius:6px;overflow:hidden}
    .cpbn-notif{padding:16px 20px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;gap:14px;transition:background .15s}
    .cpbn-notif:last-child{border-bottom:none}
    .cpbn-notif.unread{background:var(--gold-wash)}
    .cpbn-notif-top{display:flex;align-items:center;gap:9px;flex-wrap:wrap}
    .cpbn-dot{width:7px;height:7px;border-radius:50%;background:var(--gold);flex-shrink:0}
    .cpbn-notif-title{font-size:14.5px;font-weight:600}
    .cpbn-type{font-family:var(--font-mono);font-size:10.5px;letter-spacing:.04em;text-transform:uppercase;padding:3px 9px;border-radius:100px}
    .type-recommendation{background:var(--green-wash);color:#2e5c43}
    .type-milestone{background:var(--purple-wash);color:#5a4180}
    .type-reminder{background:var(--gold-wash);color:#8a6420}
    .type-default{background:#eef1f5;color:var(--ink-dim)}
    .cpbn-notif-msg{font-size:13.5px;color:var(--ink-dim);margin-top:6px}
    .cpbn-notif-meta{display:flex;align-items:center;gap:16px;margin-top:9px}
    .cpbn-notif-meta span{font-family:var(--font-mono);font-size:11.5px;color:var(--ink-dim);display:flex;align-items:center;gap:5px}
    .cpbn-notif-meta span svg{width:12px;height:12px}
    .cpbn-notif-meta a{font-size:11.5px;color:#8a6420;display:flex;align-items:center;gap:5px}
    .cpbn-notif-meta a svg{width:11px;height:11px}
    .cpbn-mark{background:none;border:none;color:var(--gold);cursor:pointer;padding:6px;flex-shrink:0}
    .cpbn-mark svg{width:18px;height:18px}
    .cpbn-mark:hover{color:var(--gold-bright)}

    .cpbn-empty{padding:56px 20px;text-align:center}
    .cpbn-empty svg{width:34px;height:34px;color:var(--gold);margin-inline:auto;margin-bottom:14px}
    .cpbn-empty p.t{font-weight:500;font-size:14.5px}
    .cpbn-empty p.s{font-size:13px;color:var(--ink-dim);margin-top:4px}

    .cpbn-pagination{margin-top:18px;font-size:13.5px;color:var(--ink-dim)}
</style>

<div class="cpbn-dash">
    <div class="cpbn-wrap">

        <div class="cpbn-head">
            <div>
                <h1>Notifications</h1>
                <p class="sub">Stay updated with your career progress</p>
            </div>
            <div class="cpbn-head-actions">
                @if($unreadCount > 0)
                    <form action="{{ route('student.notifications.read-all') }}" method="POST" data-confirm-update data-item-name="all notifications">
                        @csrf
                        <button type="submit" class="cpbn-btn cpbn-btn-primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 7 17l-5-5"/><path d="M22 10 12.5 19.5 11 18"/></svg>
                            Mark All Read
                        </button>
                    </form>
                @endif
                <a href="{{ route('student.dashboard') }}" class="cpbn-back">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back
                </a>
            </div>
        </div>

        <!-- Unread Count -->
        <div class="cpbn-banner">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
            You have <strong>{{ $unreadCount }}</strong> unread notification(s)
        </div>

        <!-- Notification List -->
        <div class="cpbn-list">
            @forelse($notifications as $notification)
                <div class="cpbn-notif {{ !$notification->is_read ? 'unread' : '' }}">
                    <div style="flex:1">
                        <div class="cpbn-notif-top">
                            @if(!$notification->is_read)
                                <span class="cpbn-dot"></span>
                            @endif
                            <span class="cpbn-notif-title">{{ $notification->title }}</span>
                            <span class="cpbn-type
                                {{ $notification->type == 'recommendation' ? 'type-recommendation' :
                                   ($notification->type == 'milestone' ? 'type-milestone' :
                                   ($notification->type == 'reminder' ? 'type-reminder' :
                                   'type-default')) }}">
                                {{ ucfirst($notification->type) }}
                            </span>
                        </div>
                        <p class="cpbn-notif-msg">{{ $notification->message }}</p>
                        <div class="cpbn-notif-meta">
                            <span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                            @if($notification->link)
                                <a href="{{ $notification->link }}">
                                    View Details
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                    @if(!$notification->is_read)
                        <form action="{{ route('student.notifications.read', $notification) }}" method="POST">
                            @csrf
                            <button type="submit" class="cpbn-mark" title="Mark as read">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="cpbn-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/><path d="M3 3l18 18"/></svg>
                    <p class="t">No notifications yet</p>
                    <p class="s">We'll notify you when there are updates</p>
                </div>
            @endforelse
        </div>

        <div class="cpbn-pagination">
            {{ $notifications->links() }}
        </div>

    </div>
</div>

@endsection