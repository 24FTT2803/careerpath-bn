@extends('admin.layouts.admin')

@section('title', 'Lecturer Dashboard')

@section('content')
<div class="admin-dashboard">

    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="welcome-content">
            <div class="welcome-text">
                <div class="greeting">
                    <span class="wave">👋</span>
                    <h1>Welcome back, {{ auth()->user()->name }}</h1>
                </div>
                <p class="subtitle">{{ now()->format('l, F j, Y') }} · Cohort overview across all students</p>
                <div class="quick-stats">
                    <span class="stat-chip">
                        <i class="fas fa-users"></i> {{ $totalStudents }} Students
                    </span>
                    <span class="stat-chip">
                        <i class="fas fa-exclamation-triangle"></i> {{ $atRiskStudents->count() }} At Risk
                    </span>
                    <span class="stat-chip">
                        <i class="fas fa-chart-line"></i> {{ $avgReadiness }}% Avg Readiness
                    </span>
                </div>
            </div>
            <div class="welcome-actions">
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
                <span class="stat-value">{{ $totalStudents }}</span>
                <span class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> Enrolled
                </span>
            </div>
            <div class="stat-progress">
                <div class="progress-bar" style="width: {{ min(($totalStudents / 50) * 100, 100) }}%"></div>
            </div>
        </div>

        <div class="stat-card stat-card-green">
            <div class="stat-icon-wrapper">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Profile Completion</span>
                <span class="stat-value">{{ $completionRate }}%</span>
                <span class="stat-change neutral">
                    <i class="fas fa-minus"></i> Cohort average
                </span>
            </div>
            <div class="stat-progress">
                <div class="progress-bar" style="width: {{ $completionRate }}%"></div>
            </div>
        </div>

        <div class="stat-card stat-card-blue">
            <div class="stat-icon-wrapper">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Avg Readiness</span>
                <span class="stat-value">{{ $avgReadiness }}%</span>
                <span class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> Career readiness
                </span>
            </div>
            <div class="stat-progress">
                <div class="progress-bar" style="width: {{ $avgReadiness }}%"></div>
            </div>
        </div>

        <div class="stat-card stat-card-rose" title="Students with a career readiness score below 40%">
            <div class="stat-icon-wrapper">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">At-Risk Students</span>
                <span class="stat-value">{{ $atRiskStudents->count() }}</span>
                <span class="stat-change negative">
                    <i class="fas fa-circle-info"></i> Below 40% readiness
                </span>
            </div>
            <div class="stat-progress">
                <div class="progress-bar" style="width: {{ $totalStudents > 0 ? min(($atRiskStudents->count() / $totalStudents) * 100, 100) : 0 }}%"></div>
            </div>
        </div>
    </div>

    <!-- Two Column: Students Needing Attention & Students by Programme -->
    <div class="dashboard-grid">
        <!-- Students Needing Attention -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Students Needing Attention</h3>
                <a href="{{ route('admin.students.index') }}" class="link">View All →</a>
            </div>
            @if($atRiskStudents->isEmpty())
                <div class="empty-state">
                    <div class="empty-illustration">
                        <i class="fas fa-check-circle" style="color: #2d8f5c;"></i>
                    </div>
                    <h4>No students at risk</h4>
                    <p>No students are currently below the 40% readiness threshold.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="cpbn-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Programme</th>
                                <th>Readiness</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($atRiskStudents as $student)
                                <tr>
                                    <td>{{ $student->name }}</td>
                                    <td>{{ $student->programme ?? '—' }}</td>
                                    <td>
                                        <span class="skill-count" style="background: #fbeceb; color: #c65b4e;">{{ $student->readiness_score }}%</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.students.show', $student->id) }}" class="link">View →</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Students by Programme -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-pie"></i> Students by Programme</h3>
                <span class="badge">{{ $totalStudents }} total</span>
            </div>
            <div class="programme-list">
                @forelse($studentsByProgramme as $prog)
                    <div class="programme-item">
                        <div class="programme-info">
                            <span class="programme-name">{{ $prog->programme ?? 'Not Set' }}</span>
                            <span class="programme-count">{{ $prog->count }} students</span>
                        </div>
                        <div class="programme-bar">
                            <div class="bar-fill" style="width: {{ ($prog->count / max($totalStudents, 1)) * 100 }}%; background: {{ ['#c9a84c', '#2d8f5c', '#7a5ea8', '#c65b4e', '#2a5a8c', '#e67e22', '#3498db', '#e74c3c'][$loop->index % 8] ?? '#c9a84c' }};"></div>
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
    </div>

    <!-- Two Column: Common Competency Gaps & Recent Milestone Submissions -->
    <div class="dashboard-grid-two">
        <!-- Common Competency Gaps -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-bar"></i> Common Competency Gaps</h3>
                <span class="badge">Cohort-wide</span>
            </div>
            @if(empty($skillGaps))
                <div class="empty-state">
                    <div class="empty-illustration">
                        <i class="fas fa-chart-simple"></i>
                    </div>
                    <h4>No gap data yet</h4>
                    <p>Skill gaps will appear once students receive career recommendations.</p>
                </div>
            @else
                @php $maxGap = max($skillGaps) ?: 1; @endphp
                @foreach($skillGaps as $skill => $count)
                    <div class="gap-item">
                        <div class="gap-info">
                            <span class="gap-name">{{ $skill }}</span>
                            <span class="gap-count">{{ $count }} students</span>
                        </div>
                        <div class="gap-bar">
                            <div class="bar-fill rose" style="width: {{ min(($count / $maxGap) * 100, 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <!-- Recent Milestone Submissions -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-file-upload"></i> Recent Milestone Submissions</h3>
            </div>
            @if($recentSubmissions->isEmpty())
                <div class="empty-state">
                    <div class="empty-illustration">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h4>No submissions yet</h4>
                    <p>Milestone proof submissions will appear here for review.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="cpbn-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Milestone</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSubmissions as $milestone)
                                <tr>
                                    <td>{{ $milestone->user->name ?? '—' }}</td>
                                    <td>{{ $milestone->title }}</td>
                                    <td>
                                        <a href="{{ route('admin.milestones.proof', [$milestone->user_id, $milestone->id]) }}" class="link" target="_blank">View →</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>

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
    .stat-card-blue::before { background: #2a5a8c; }
    .stat-card-rose::before { background: #c65b4e; }

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
    .stat-card-blue .stat-icon-wrapper { background: #e8f0fe; color: #2a5a8c; }
    .stat-card-rose .stat-icon-wrapper { background: #fbeceb; color: #c65b4e; }

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
        color: #1a1a2e;
    }

    .skill-count {
        padding: 2px 12px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
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