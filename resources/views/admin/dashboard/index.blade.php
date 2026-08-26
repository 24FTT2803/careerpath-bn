@extends('admin.layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="admin-dashboard">

    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="welcome-content">
            <div class="welcome-text">
                <div class="greeting">
                    <span class="wave">👋</span>
                    <h1>Welcome back, {{ auth()->user()->name }}!</h1>
                </div>
                <p class="subtitle">{{ now()->format('l, F j, Y') }} · {{ now()->format('h:i A') }}</p>
                <div class="quick-stats">
                    <span class="stat-chip">
                        <i class="fas fa-users"></i> {{ $stats['total_students'] ?? 0 }} Students
                    </span>
                    <span class="stat-chip">
                        <i class="fas fa-chalkboard-teacher"></i> {{ $stats['total_lecturers'] ?? 0 }} Lecturers
                    </span>
                    <span class="stat-chip">
                        <i class="fas fa-briefcase"></i> {{ $stats['total_careers'] ?? 0 }} Careers
                    </span>
                </div>
            </div>
            <div class="welcome-actions">
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus"></i> Add User
                </a>
                <button onclick="window.location.reload()" class="btn btn-outline btn-lg">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
        <div class="welcome-decoration">
            <div class="floating-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-card-gold">
            <div class="stat-icon-wrapper">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Students</span>
                <span class="stat-value">{{ $stats['total_students'] ?? 0 }}</span>
                <span class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> Active
                </span>
            </div>
            <div class="stat-progress">
                <div class="progress-bar" style="width: {{ min(($stats['total_students'] / 100) * 100, 100) }}%"></div>
            </div>
        </div>

        <div class="stat-card stat-card-green">
            <div class="stat-icon-wrapper">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Active Lecturers</span>
                <span class="stat-value">{{ $stats['total_lecturers'] ?? 0 }}</span>
                <span class="stat-change neutral">
                    <i class="fas fa-minus"></i> Current
                </span>
            </div>
            <div class="stat-progress">
                <div class="progress-bar" style="width: {{ min(($stats['total_lecturers'] / 50) * 100, 100) }}%"></div>
            </div>
        </div>

        <div class="stat-card stat-card-purple">
            <div class="stat-icon-wrapper">
                <i class="fas fa-briefcase"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">BIICF Careers</span>
                <span class="stat-value">{{ $stats['total_careers'] ?? 0 }}</span>
                <span class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> Available
                </span>
            </div>
            <div class="stat-progress">
                <div class="progress-bar" style="width: {{ min(($stats['total_careers'] / 20) * 100, 100) }}%"></div>
            </div>
        </div>

        <div class="stat-card stat-card-rose">
            <div class="stat-icon-wrapper">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Avg Readiness</span>
                <span class="stat-value">{{ $stats['avg_readiness'] ?? 0 }}%</span>
                <span class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> Improving
                </span>
            </div>
            <div class="stat-progress">
                <div class="progress-bar" style="width: {{ $stats['avg_readiness'] ?? 0 }}%"></div>
            </div>
        </div>

        <div class="stat-card stat-card-blue">
            <div class="stat-icon-wrapper">
                <i class="fas fa-rocket"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Recommendations</span>
                <span class="stat-value">{{ $stats['total_recommendations'] ?? 0 }}</span>
                <span class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> Generated
                </span>
            </div>
            <div class="stat-progress">
                <div class="progress-bar" style="width: {{ min(($stats['total_recommendations'] / 50) * 100, 100) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Two Column: Activity Feed & Quick Actions -->
    <div class="dashboard-grid">
        <!-- Activity Feed -->
        <div class="card activity-feed">
            <div class="card-header">
                <h3><i class="fas fa-bolt"></i> Recent Activity</h3>
                <span class="badge">Live</span>
            </div>
            <div class="activity-list">
                @forelse($recentActivities as $activity)
                    <div class="activity-item">
                        <div class="activity-icon {{ $activity['type'] ?? 'system' }}">
                            <i class="fas fa-{{ $activity['icon'] ?? 'bell' }}"></i>
                        </div>
                        <div class="activity-content">
                            <p class="activity-message">{{ $activity['message'] }}</p>
                            <span class="activity-time">{{ $activity['time'] }} by {{ $activity['user_name'] }}</span>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-illustration">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <h4>No recent activity</h4>
                        <p>Activities will appear here as users interact with the platform.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card quick-actions">
            <div class="card-header">
                <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
            </div>
            <div class="actions-grid">
                <a href="{{ route('admin.users.create') }}" class="action-card">
                    <div class="action-icon" style="background: #e8f5e9; color: #2e7d32;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <span>Add User</span>
                </a>
                <a href="{{ route('admin.students.index') }}" class="action-card">
                    <div class="action-icon" style="background: #e3f2fd; color: #1565c0;">
                        <i class="fas fa-search"></i>
                    </div>
                    <span>View Students</span>
                </a>
                <a href="{{ route('admin.careers.index') }}" class="action-card">
                    <div class="action-icon" style="background: #fff3e0; color: #e65100;">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <span>Manage Careers</span>
                </a>
                <a href="#" class="action-card">
                    <div class="action-icon" style="background: #fce4ec; color: #c62828;">
                        <i class="fas fa-file-export"></i>
                    </div>
                    <span>Export Reports</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Students by Programme -->
    <div class="card programme-chart">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie"></i> Students by Programme</h3>
            <span class="badge">{{ $stats['total_students'] ?? 0 }} total</span>
        </div>
        <div class="programme-list">
            @forelse($studentsByProgramme ?? [] as $prog)
                <div class="programme-item">
                    <div class="programme-info">
                        <span class="programme-name">{{ $prog->programme ?? 'Not Set' }}</span>
                        <span class="programme-count">{{ $prog->count }} students</span>
                    </div>
                    <div class="programme-bar">
                        <div class="bar-fill" style="width: {{ ($prog->count / max($stats['total_students'], 1)) * 100 }}%; background: {{ ['#c9a84c', '#2d8f5c', '#7a5ea8', '#c65b4e', '#2a5a8c', '#e67e22', '#3498db', '#e74c3c'][$loop->index % 8] ?? '#c9a84c' }};"></div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-illustration">
                        <i class="fas fa-users-slash"></i>
                    </div>
                    <h4>No students registered</h4>
                    <p>Students will appear here once they create accounts.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Two Column: Top Careers & Skill Gaps -->
    <div class="dashboard-grid-two">
        <!-- Top Career Matches -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-star"></i> Top Career Matches</h3>
                <a href="{{ route('admin.careers.index') }}" class="link">View All →</a>
            </div>
            @if(isset($topCareers) && $topCareers->count() > 0)
                @foreach($topCareers as $career)
                    <div class="rank-item">
                        <div class="rank-badge">{{ $loop->iteration }}</div>
                        <div class="rank-info">
                            <span class="rank-name">{{ $career->career->job_title ?? 'N/A' }}</span>
                            <span class="rank-score">{{ number_format($career->avg_score, 1) }}%</span>
                        </div>
                        <div class="rank-bar">
                            <div class="bar-fill gold" style="width: {{ $career->avg_score }}%"></div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state">
                    <div class="empty-illustration">
                        <i class="fas fa-chart-simple"></i>
                    </div>
                    <h4>No recommendations yet</h4>
                    <p>Career recommendations will appear once students complete their profiles.</p>
                </div>
            @endif
        </div>

        <!-- Common Skill Gaps -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Common Skill Gaps</h3>
            </div>
            @if(isset($skillGaps) && count($skillGaps) > 0)
                @foreach(array_slice($skillGaps, 0, 5) as $skill => $count)
                    <div class="gap-item">
                        <div class="gap-info">
                            <span class="gap-name">{{ $skill }}</span>
                            <span class="gap-count">{{ $count }} students</span>
                        </div>
                        <div class="gap-bar">
                            <div class="bar-fill rose" style="width: {{ min(($count / max($stats['total_students'], 1)) * 100, 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state">
                    <div class="empty-illustration">
                        <i class="fas fa-check-circle" style="color: #2d8f5c;"></i>
                    </div>
                    <h4>No gaps identified</h4>
                    <p>Skill gaps will appear once students receive career recommendations.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Students Table -->
    <div class="card recent-students">
        <div class="card-header">
            <h3><i class="fas fa-user-graduate"></i> Recent Students</h3>
            <a href="{{ route('admin.students.index') }}" class="link">View All →</a>
        </div>
        <div class="table-responsive">
            <table class="cpbn-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>ID</th>
                        <th>Programme</th>
                        <th>CGPA</th>
                        <th>Skills</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentStudents ?? [] as $student)
                        <tr>
                            <td>
                                <div class="student-cell">
                                    <div class="student-avatar">
                                        {{ substr($student->name, 0, 1) }}
                                    </div>
                                    <a href="{{ route('admin.students.show', $student) }}" class="student-name">
                                        {{ $student->name }}
                                    </a>
                                </div>
                            </td>
                            <td>{{ $student->student_id ?? '-' }}</td>
                            <td>{{ $student->programme ?? '-' }}</td>
                            <td>{{ $student->cgpa ?? '-' }}</td>
                            <td>
                                <span class="skill-count">{{ $student->competencies->count() }} skills</span>
                            </td>
                            <td>
                                <span class="status-badge active">
                                    <span class="dot"></span> Active
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-row">
                                <i class="fas fa-users"></i>
                                <span>No students registered yet.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Styles -->
<style>
    .admin-dashboard {
        padding: 0 4px;
    }

    /* Welcome Banner */
    .welcome-banner {
        background: linear-gradient(135deg, #1a3a5c 0%, #2a5a8c 50%, #1a3a5c 100%);
        border-radius: 16px;
        padding: 32px 40px;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
    }

    .welcome-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        position: relative;
        z-index: 2;
    }

    .greeting {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .greeting .wave {
        font-size: 32px;
    }

    .greeting h1 {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: white;
        margin: 0;
    }

    .subtitle {
        color: rgba(255, 255, 255, 0.7);
        font-size: 14px;
        margin-top: 4px;
        margin-left: 48px;
    }

    .quick-stats {
        display: flex;
        gap: 16px;
        margin-top: 12px;
        margin-left: 48px;
        flex-wrap: wrap;
    }

    .stat-chip {
        background: rgba(255, 255, 255, 0.12);
        color: rgba(255, 255, 255, 0.9);
        padding: 4px 16px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        backdrop-filter: blur(4px);
    }

    .stat-chip i {
        color: #c9a84c;
    }

    .welcome-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        font-family: inherit;
    }

    .btn-primary {
        background: #c9a84c;
        color: #0d1f33;
    }

    .btn-primary:hover {
        background: #e8d4a0;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(201, 168, 76, 0.3);
    }

    .btn-outline {
        background: transparent;
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .btn-outline:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateY(-2px);
    }

    .btn-lg {
        padding: 12px 32px;
        font-size: 15px;
    }

    .welcome-decoration {
        position: absolute;
        top: -50%;
        right: -10%;
        width: 60%;
        height: 200%;
        z-index: 1;
    }

    .floating-shapes {
        position: relative;
        width: 100%;
        height: 100%;
    }

    .shape {
        position: absolute;
        border-radius: 50%;
        background: rgba(201, 168, 76, 0.08);
        animation: float 6s ease-in-out infinite;
    }

    .shape-1 {
        width: 200px;
        height: 200px;
        top: 20%;
        right: 20%;
        animation-delay: 0s;
    }

    .shape-2 {
        width: 120px;
        height: 120px;
        top: 60%;
        right: 50%;
        animation-delay: 2s;
        background: rgba(255, 255, 255, 0.05);
    }

    .shape-3 {
        width: 80px;
        height: 80px;
        top: 10%;
        right: 60%;
        animation-delay: 4s;
        background: rgba(201, 168, 76, 0.12);
    }

    @keyframes float {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50% { transform: translate(20px, -30px) scale(1.1); }
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px 24px;
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 40px rgba(0, 0, 0, 0.08);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    }

    .stat-card-gold::before { background: #c9a84c; }
    .stat-card-green::before { background: #2d8f5c; }
    .stat-card-purple::before { background: #7a5ea8; }
    .stat-card-rose::before { background: #c65b4e; }
    .stat-card-blue::before { background: #2a5a8c; }

    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 12px;
    }

    .stat-card-gold .stat-icon-wrapper { background: #fbf1de; color: #c9a84c; }
    .stat-card-green .stat-icon-wrapper { background: #e9f3ee; color: #2d8f5c; }
    .stat-card-purple .stat-icon-wrapper { background: #f1ecf7; color: #7a5ea8; }
    .stat-card-rose .stat-icon-wrapper { background: #fbeceb; color: #c65b4e; }
    .stat-card-blue .stat-icon-wrapper { background: #e8f0fe; color: #2a5a8c; }

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-label {
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .stat-value {
        font-family: 'Playfair Display', serif;
        font-size: 32px;
        font-weight: 700;
        color: #1a3a5c;
        margin: 4px 0;
    }

    .stat-change {
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .stat-change.positive { color: #2d8f5c; }
    .stat-change.neutral { color: #6b7280; }
    .stat-change.negative { color: #c65b4e; }

    .stat-progress {
        margin-top: 12px;
        height: 4px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
    }

    .stat-progress .progress-bar {
        height: 100%;
        border-radius: 4px;
        background: #c9a84c;
        transition: width 0.6s ease;
    }

    /* Cards */
    .card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 24px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .card-header h3 {
        font-size: 16px;
        font-weight: 600;
        color: #1a3a5c;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .card-header h3 i {
        color: #c9a84c;
    }

    .card-header .badge {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 4px 12px;
        border-radius: 100px;
        background: #e9f3ee;
        color: #2d8f5c;
    }

    .link {
        color: #6b7280;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .link:hover {
        color: #c9a84c;
    }

    /* Dashboard Grid */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .dashboard-grid-two {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    /* Activity Feed */
    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .activity-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 12px;
        border-radius: 8px;
        background: #faf8f2;
        transition: all 0.3s ease;
    }

    .activity-item:hover {
        background: #f4f1e7;
    }

    .activity-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    .activity-icon.system { background: #e8f0fe; color: #2a5a8c; }
    .activity-icon.user { background: #fbf1de; color: #c9a84c; }
    .activity-icon.career { background: #f1ecf7; color: #7a5ea8; }
    .activity-icon.milestone { background: #e9f3ee; color: #2d8f5c; }

    .activity-content {
        flex: 1;
    }

    .activity-message {
        font-size: 13px;
        color: #1a1a2e;
        margin: 0;
    }

    .activity-time {
        font-size: 11px;
        color: #6b7280;
    }

    /* Quick Actions */
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .action-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 20px 12px;
        border-radius: 10px;
        background: #faf8f2;
        border: 1px solid transparent;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .action-card:hover {
        border-color: #c9a84c;
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    }

    .action-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .action-card span {
        font-size: 12px;
        font-weight: 500;
        color: #1a1a2e;
    }

    /* Programme Chart */
    .programme-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .programme-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .programme-info {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
    }

    .programme-name {
        font-weight: 500;
        color: #1a3a5c;
    }

    .programme-count {
        color: #6b7280;
    }

    .programme-bar {
        height: 6px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
    }

    .programme-bar .bar-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.8s ease;
    }

    /* Rank Items */
    .rank-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .rank-item:last-child {
        border-bottom: none;
    }

    .rank-badge {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #fbf1de;
        color: #8a6420;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 600;
        flex-shrink: 0;
    }

    .rank-info {
        flex: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .rank-name {
        font-weight: 500;
        font-size: 13px;
        color: #1a1a2e;
    }

    .rank-score {
        font-size: 12px;
        font-weight: 600;
        color: #c9a84c;
    }

    .rank-bar {
        width: 100%;
        height: 4px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 4px;
    }

    .rank-bar .bar-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.6s ease;
    }

    .rank-bar .bar-fill.gold { background: #c9a84c; }

    /* Gap Items */
    .gap-item {
        padding: 8px 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .gap-item:last-child {
        border-bottom: none;
    }

    .gap-info {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
    }

    .gap-name {
        font-weight: 500;
        color: #1a1a2e;
    }

    .gap-count {
        color: #6b7280;
        font-size: 12px;
    }

    .gap-bar {
        height: 4px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 4px;
    }

    .gap-bar .bar-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.6s ease;
    }

    .gap-bar .bar-fill.rose { background: #c65b4e; }

    /* Table */
    .table-responsive {
        overflow-x: auto;
    }

    .cpbn-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .cpbn-table thead {
        background: #faf8f2;
    }

    .cpbn-table th {
        text-align: left;
        padding: 12px 16px;
        font-weight: 600;
        color: #6b7280;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .cpbn-table td {
        padding: 12px 16px;
        border-top: 1px solid #e5e7eb;
    }

    .student-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .student-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1a3a5c, #2a5a8c);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 13px;
        flex-shrink: 0;
    }

    .student-name {
        color: #1a3a5c;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .student-name:hover {
        color: #c9a84c;
    }

    .skill-count {
        padding: 2px 12px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 500;
        background: #e8f0fe;
        color: #2a5a8c;
        display: inline-block;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 500;
        color: #2d8f5c;
    }

    .status-badge .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #2d8f5c;
        display: inline-block;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    .empty-row {
        text-align: center;
        padding: 40px 20px;
        color: #6b7280;
    }

    .empty-row i {
        font-size: 32px;
        color: #e5e7eb;
        display: block;
        margin-bottom: 8px;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 32px 20px;
    }

    .empty-illustration {
        font-size: 48px;
        color: #e5e7eb;
        margin-bottom: 12px;
    }

    .empty-state h4 {
        font-size: 16px;
        font-weight: 600;
        color: #1a3a5c;
        margin: 0 0 4px;
    }

    .empty-state p {
        font-size: 13px;
        color: #6b7280;
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .dashboard-grid,
        .dashboard-grid-two {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .welcome-banner {
            padding: 24px;
        }
        .greeting h1 {
            font-size: 22px;
        }
        .quick-stats {
            margin-left: 0;
        }
        .subtitle {
            margin-left: 0;
        }
        .stats-grid {
            grid-template-columns: 1fr 1fr;
        }
        .welcome-actions {
            width: 100%;
        }
        .welcome-actions .btn {
            flex: 1;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        .actions-grid {
            grid-template-columns: 1fr;
        }
        .greeting {
            flex-wrap: wrap;
        }
        .greeting .wave {
            font-size: 24px;
        }
        .greeting h1 {
            font-size: 18px;
        }
    }
</style>
@endsection