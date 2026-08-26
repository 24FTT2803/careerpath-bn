@extends('layouts.app')

@section('title', 'Notifications')

@section('content')

<style>
    .notif-page {
        padding: 24px 0 40px;
    }

    .notif-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }

    .notif-header h1 {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: var(--primary);
    }

    .notif-header h1 span {
        color: var(--accent);
    }

    .notif-header .subtitle {
        color: var(--text-muted);
        font-size: 14px;
        margin-top: 2px;
    }

    .notif-header .actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        font-family: inherit;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-light);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(26, 58, 92, 0.25);
    }

    .btn-outline {
        background: transparent;
        color: var(--primary);
        border: 2px solid var(--primary);
    }

    .btn-outline:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-2px);
    }

    .btn-sm {
        padding: 6px 14px;
        font-size: 12px;
    }

    .notif-banner {
        background: rgba(26, 58, 92, 0.04);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 12px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        color: var(--text);
    }

    .notif-banner i {
        color: var(--accent);
        font-size: 18px;
    }

    .notif-banner strong {
        color: var(--primary);
    }

    .notif-list {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
    }

    .notif-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        transition: var(--transition);
    }

    .notif-item:last-child {
        border-bottom: none;
    }

    .notif-item:hover {
        background: var(--bg);
    }

    .notif-item.unread {
        background: rgba(201, 168, 76, 0.04);
        border-left: 3px solid var(--accent);
    }

    .notif-item .content {
        flex: 1;
    }

    .notif-item .top {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .notif-item .top .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--accent);
        flex-shrink: 0;
    }

    .notif-item .top .title {
        font-weight: 600;
        font-size: 14px;
        color: var(--primary);
    }

    .notif-item .top .type {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 2px 10px;
        border-radius: 100px;
    }

    .notif-item .top .type.recommendation {
        background: rgba(45, 143, 92, 0.12);
        color: var(--success);
    }

    .notif-item .top .type.milestone {
        background: rgba(201, 168, 76, 0.12);
        color: var(--accent-dark);
    }

    .notif-item .top .type.reminder {
        background: rgba(230, 126, 34, 0.12);
        color: var(--warning);
    }

    .notif-item .top .type.system {
        background: rgba(26, 58, 92, 0.06);
        color: var(--primary);
    }

    .notif-item .message {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 4px;
        line-height: 1.6;
    }

    .notif-item .meta {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-top: 8px;
    }

    .notif-item .meta .time {
        font-size: 12px;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .notif-item .meta .link {
        font-size: 12px;
        color: var(--accent-dark);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 4px;
        transition: var(--transition);
    }

    .notif-item .meta .link:hover {
        color: var(--accent);
    }

    .notif-item .actions {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .notif-item .actions .mark-read {
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        transition: var(--transition);
        font-size: 14px;
    }

    .notif-item .actions .mark-read:hover {
        color: var(--accent);
        background: var(--bg);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 48px;
        color: var(--border);
        margin-bottom: 16px;
    }

    .empty-state h4 {
        font-size: 18px;
        color: var(--primary);
        margin-bottom: 4px;
    }

    .empty-state p {
        font-size: 14px;
    }

    .pagination-wrap {
        margin-top: 16px;
        display: flex;
        justify-content: center;
    }

    @media (max-width: 480px) {
        .notif-item {
            flex-direction: column;
        }
        .notif-item .actions {
            align-self: flex-end;
        }
        .notif-header .actions {
            flex-direction: column;
            width: 100%;
        }
        .notif-header .actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="notif-page">
    <div class="container">

        <div class="notif-header">
            <div>
                <h1>Notifications <span>📬</span></h1>
                <p class="subtitle">Stay updated with your career progress</p>
            </div>
            <div class="actions">
                @if($unreadCount > 0)
                    <form action="{{ route('student.notifications.read-all') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-check-double"></i> Mark All Read
                        </button>
                    </form>
                @endif
                <a href="{{ route('student.dashboard') }}" class="btn btn-outline btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        @if($unreadCount > 0)
            <div class="notif-banner">
                <i class="fas fa-bell"></i>
                You have <strong>{{ $unreadCount }}</strong> unread notification(s)
            </div>
        @endif

        <div class="notif-list">
            @forelse($notifications as $notification)
                <div class="notif-item {{ !$notification->is_read ? 'unread' : '' }}">
                    <div class="content">
                        <div class="top">
                            @if(!$notification->is_read)
                                <span class="dot"></span>
                            @endif
                            <span class="title">{{ $notification->title }}</span>
                            <span class="type {{ $notification->type }}">
                                {{ ucfirst($notification->type) }}
                            </span>
                        </div>
                        <p class="message">{{ $notification->message }}</p>
                        <div class="meta">
                            <span class="time">
                                <i class="fas fa-clock"></i>
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                            @if($notification->link)
                                <a href="{{ $notification->link }}" class="link">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                    @if(!$notification->is_read)
                        <div class="actions">
                            <form action="{{ route('student.notifications.read', $notification) }}" method="POST">
                                @csrf
                                <button type="submit" class="mark-read" title="Mark as read">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <h4>No notifications yet</h4>
                    <p>We'll notify you when there are updates about your career journey.</p>
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="pagination-wrap">
                {{ $notifications->links() }}
            </div>
        @endif

    </div>
</div>

@endsection