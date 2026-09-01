@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<style>
    :root {
        --primary: #1a3a5c;
        --primary-light: #2a5a8c;
        --accent: #c9a84c;
        --accent-light: #e8d4a0;
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
    }

    .dashboard {
        padding: 24px 0 40px;
    }

    /* Welcome Header */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }

    .dashboard-header h1 {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: var(--primary);
    }

    .dashboard-header h1 span {
        color: var(--accent);
    }

    .dashboard-header .subtitle {
        color: var(--text-muted);
        font-size: 14px;
        margin-top: 2px;
    }

    .dashboard-header .badges {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .badge-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 500;
        background: var(--bg);
        color: var(--text-muted);
    }

    .badge-chip i {
        font-size: 14px;
    }

    .badge-chip.primary {
        background: rgba(26, 58, 92, 0.08);
        color: var(--primary);
    }

    .badge-chip.accent {
        background: rgba(201, 168, 76, 0.12);
        color: var(--accent-dark);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px 24px;
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
        border-color: var(--accent-light);
    }

    .stat-card .stat-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .stat-card .stat-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .stat-card .stat-number {
        font-family: 'Playfair Display', serif;
        font-size: 32px;
        font-weight: 700;
        color: var(--primary);
        margin-top: 4px;
    }

    .stat-card .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .stat-card .stat-icon.gold {
        background: rgba(201, 168, 76, 0.12);
        color: var(--accent-dark);
    }

    .stat-card .stat-icon.green {
        background: rgba(45, 143, 92, 0.12);
        color: var(--success);
    }

    .stat-card .stat-icon.blue {
        background: rgba(26, 58, 92, 0.08);
        color: var(--primary);
    }

    .stat-card .stat-icon.red {
        background: rgba(192, 57, 43, 0.08);
        color: var(--danger);
    }

    .stat-card .stat-progress {
        margin-top: 12px;
        height: 4px;
        background: var(--bg);
        border-radius: 4px;
        overflow: hidden;
    }

    .stat-card .stat-progress .fill {
        height: 100%;
        border-radius: 4px;
        background: var(--accent);
        transition: width 0.6s ease;
    }

    /* Quick Actions */
    .quick-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 24px;
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

    .btn-accent {
        background: var(--accent);
        color: var(--primary-dark);
    }

    .btn-accent:hover {
        background: var(--accent-light);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(201, 168, 76, 0.3);
    }

    .btn-sm {
        padding: 8px 16px;
        font-size: 12px;
    }

    /* Main Content Grid */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }

    .panel {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 24px;
    }

    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .panel-header h3 {
        font-size: 16px;
        font-weight: 600;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .panel-header h3 i {
        color: var(--accent);
    }

    .panel-header a {
        font-size: 12px;
        color: var(--text-muted);
        text-decoration: none;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .panel-header a:hover {
        color: var(--primary);
    }

    /* Recommendation Cards */
    .rec-card {
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 16px 18px;
        margin-bottom: 12px;
        transition: var(--transition);
        cursor: pointer;
    }

    .rec-card:last-child {
        margin-bottom: 0;
    }

    .rec-card:hover {
        border-color: var(--accent-light);
        box-shadow: var(--shadow);
    }

    .rec-card .rec-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .rec-card .rec-title {
        font-weight: 600;
        font-size: 15px;
        color: var(--primary);
    }

    .rec-card .rec-subsector {
        font-size: 12px;
        color: var(--text-muted);
    }

    .rec-card .rec-match {
        font-size: 13px;
        font-weight: 600;
        color: var(--accent-dark);
        background: rgba(201, 168, 76, 0.12);
        padding: 4px 12px;
        border-radius: 100px;
    }

    .rec-card .rec-link {
        margin-top: 10px;
        font-size: 12px;
        color: var(--primary);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .rec-card .rec-link:hover {
        color: var(--accent);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
    }

    .empty-state i {
        font-size: 48px;
        color: var(--border);
        margin-bottom: 16px;
    }

    .empty-state h4 {
        font-size: 16px;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 4px;
    }

    .empty-state p {
        font-size: 13px;
        color: var(--text-muted);
    }

    .empty-state .btn {
        margin-top: 16px;
    }

    /* Profile Status */
    .profile-status .status-row {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 6px;
    }

    .profile-status .status-row .value {
        font-weight: 600;
        color: var(--primary);
    }

    .profile-status .progress-bar {
        height: 6px;
        background: var(--bg);
        border-radius: 4px;
        overflow: hidden;
    }

    .profile-status .progress-bar .fill {
        height: 100%;
        border-radius: 4px;
        background: var(--accent);
        transition: width 0.6s ease;
    }

    .profile-status .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 500;
        padding: 4px 12px;
        border-radius: 100px;
        margin-top: 12px;
    }

    .status-badge.complete {
        background: rgba(45, 143, 92, 0.12);
        color: var(--success);
    }

    .status-badge.incomplete {
        background: rgba(230, 126, 34, 0.12);
        color: var(--warning);
    }

    /* Activity Feed */
    .activity-item {
        padding: 12px 0;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-item .activity-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--bg);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        font-size: 14px;
        flex-shrink: 0;
    }

    .activity-item .activity-content {
        flex: 1;
    }

    .activity-item .activity-content .message {
        font-size: 13px;
        color: var(--text);
    }

    .activity-item .activity-content .time {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .no-activity {
        text-align: center;
        padding: 20px;
        color: var(--text-muted);
        font-size: 13px;
    }

    .no-activity i {
        font-size: 24px;
        display: block;
        margin-bottom: 8px;
        color: var(--border);
    }

    @media (max-width: 1024px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-header h1 {
            font-size: 24px;
        }

        .stats-grid {
            grid-template-columns: 1fr 1fr;
        }

        .stat-card .stat-number {
            font-size: 24px;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-header .badges {
            width: 100%;
        }

        .quick-actions {
            flex-direction: column;
        }

        .quick-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="dashboard">
    <div class="container">

        <!-- Header -->
        <div class="dashboard-header">
            <div>
                <h1>Welcome back, <span>{{ Auth::user()->first_name ?? Auth::user()->name }}</span></h1>
                <p class="subtitle">{{ Auth::user()->programme ?? 'Complete your profile to get started' }}</p>
            </div>
            <div class="badges">
                <span class="badge-chip primary">
                    <i class="fas fa-graduation-cap"></i> Student
                </span>
                <span class="badge-chip accent">
                    <i class="fas fa-calendar-alt"></i> {{ now()->format('d M Y') }}
                </span>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-label">Profile Completion</div>
                        <div class="stat-number">{{ $profileCompletion ?? 0 }}%</div>
                    </div>
                    <div class="stat-icon gold"><i class="fas fa-user-check"></i></div>
                </div>
                <div class="stat-progress">
                    <div class="fill" style="width: {{ $profileCompletion ?? 0 }}%"></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-label">Career Recommendations</div>
                        <div class="stat-number">{{ $recommendationCount ?? 0 }}</div>
                    </div>
                    <div class="stat-icon green"><i class="fas fa-bullseye"></i></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-label">Career Readiness</div>
                        <div class="stat-number">{{ $readinessScore ?? 0 }}%</div>
                    </div>
                    <div class="stat-icon blue"><i class="fas fa-chart-line"></i></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-label">Milestones Completed</div>
                        <div class="stat-number">{{ $milestoneCount ?? 0 }}</div>
                    </div>
                    <div class="stat-icon red"><i class="fas fa-flag-checkered"></i></div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="{{ route('student.profile') }}" class="btn btn-primary">
                <i class="fas fa-user-edit"></i> Update Profile
            </a>

            <a href="{{ route('student.career-adviser') }}" class="btn btn-outline">
                <i class="fas fa-comments"></i> Career Adviser
            </a>

            <a href="{{ route('student.milestones') }}" class="btn btn-outline">
                <i class="fas fa-flag-checkered"></i> Track Milestones
            </a>

            <a href="{{ route('student.biicf-explorer.index') }}" class="btn btn-outline">
                <i class="fas fa-compass"></i> BIICF Explorer
            </a>
        </div>

        <!-- Main Content -->
        <div class="dashboard-grid">
            <!-- Left Column - Recommendations -->
            <div class="panel">
                <div class="panel-header">
                    <h3><i class="fas fa-star"></i> Top Career Matches</h3>
                </div>

                @if(isset($recommendations) && count($recommendations) > 0)
                    @foreach($recommendations as $rec)
                        <div class="rec-card">
                            <div class="rec-top">
                                <div>
                                    <div class="rec-title">{{ $rec->career->job_title ?? 'Career' }}</div>
                                    <div class="rec-subsector">{{ $rec->career->subsector ?? '' }}</div>
                                </div>
                                <span class="rec-match">{{ $rec->match_score ?? 0 }}% Match</span>
                            </div>
                            <a href="{{ route('student.recommendations.analysis', $rec->id) }}" class="rec-link">
                                View Career Analysis <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    @endforeach
                @else
                    <div class="empty-state">
                        <i class="fas fa-compass"></i>
                        <h4>No Recommendations Yet</h4>
                        <p>Complete your profile to receive personalised career recommendations.</p>
                        <a href="{{ route('student.profile') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-edit"></i> Complete Profile
                        </a>
                    </div>
                @endif
            </div>

            <!-- Right Column -->
            <div>
                <!-- Profile Status -->
                <div class="panel profile-status">
                    <div class="panel-header">
                        <h3><i class="fas fa-user-circle"></i> Profile Status</h3>
                    </div>

                    <div class="status-row">
                        <span>Completion</span>
                        <span class="value">{{ $profileCompletion ?? 0 }}%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="fill" style="width: {{ $profileCompletion ?? 0 }}%"></div>
                    </div>

                    <div>
                        @if(($profileCompletion ?? 0) >= 70)
                            <span class="status-badge complete">
                                <i class="fas fa-check-circle"></i> Profile Complete
                            </span>
                        @else
                            <span class="status-badge incomplete">
                                <i class="fas fa-exclamation-circle"></i> {{ 100 - ($profileCompletion ?? 0) }}% Remaining
                            </span>
                        @endif
                    </div>

                    <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);">
                        <div class="status-row">
                            <span>Readiness Score</span>
                            <span class="value">{{ $readinessScore ?? 0 }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="panel" style="margin-top:16px;">
                    <div class="panel-header">
                        <h3><i class="fas fa-clock"></i> Recent Activity</h3>
                    </div>

                    @if(isset($recentActivities) && count($recentActivities) > 0)
                        @foreach($recentActivities as $activity)
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-{{ $activity['icon'] ?? 'bell' }}"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="message">{{ $activity['message'] ?? 'Activity logged' }}</div>
                                    <div class="time">{{ $activity['time'] ?? 'Just now' }}</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="no-activity">
                            <i class="fas fa-inbox"></i>
                            No recent activity
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
