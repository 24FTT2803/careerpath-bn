@extends('admin.layouts.admin')

@section('title', 'Students')

@section('content')
<div class="students-page">

    <!-- Page Header -->
    <div class="page-header">
        <div class="header-left">
            <h1>
                <i class="fas fa-user-graduate" style="color: #c9a84c;"></i>
                Students
            </h1>
            <p class="subtitle">Manage and monitor student progress</p>
        </div>
        <div class="header-stats">
            <div class="header-stat">
                <span class="stat-number">{{ $students->total() }}</span>
                <span class="stat-label">Total Students</span>
            </div>
            <div class="header-stat">
                <span class="stat-number">{{ $completionRate ?? 0 }}%</span>
                <span class="stat-label">Profile Completion</span>
            </div>
            <div class="header-stat">
                <span class="stat-number">{{ $atRiskStudents ?? 0 }}</span>
                <span class="stat-label">At Risk</span>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <div class="filter-field">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by name, email or student ID...">
                </div>
                <div class="filter-field">
                    <i class="fas fa-graduation-cap"></i>
                    <select name="programme">
                        <option value="">All Programmes</option>
                        @foreach($programmes as $prog)
                            <option value="{{ $prog }}" {{ request('programme') == $prog ? 'selected' : '' }}>
                                {{ $prog }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('admin.students.index') }}" class="btn btn-outline">
                    <i class="fas fa-undo"></i> Reset
                </a>
            </div>
        </form>
        <div class="filter-actions">
            <button class="btn btn-outline btn-sm" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
            <button class="btn btn-outline btn-sm">
                <i class="fas fa-file-export"></i> Export
            </button>
        </div>
    </div>

    <!-- Students Grid -->
    <div class="students-grid">
        @forelse($students as $student)
            <div class="student-card">
                <div class="student-card-header">
                    <div class="student-avatar-lg">
                        {{ substr($student->name, 0, 1) }}
                    </div>
                    <div class="student-card-info">
                        <h4>
                            <a href="{{ route('admin.students.show', $student) }}">
                                {{ $student->name }}
                            </a>
                        </h4>
                        <span class="student-id">{{ $student->student_id ?? 'No ID' }}</span>
                        <span class="student-programme">{{ $student->programme ?? 'No Programme' }}</span>
                    </div>
                    <div class="student-card-status">
                        <span class="status-badge active">
                            <span class="dot"></span> Active
                        </span>
                    </div>
                </div>

                <div class="student-card-body">
                    <div class="student-metrics">
                        <div class="metric">
                            <span class="metric-label">CGPA</span>
                            <span class="metric-value">{{ $student->cgpa ?? '-' }}</span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">Skills</span>
                            <span class="metric-value">{{ $student->competencies->count() }}</span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">Readiness</span>
                            <span class="metric-value readiness-score {{ ($student->readiness_score ?? 0) >= 70 ? 'high' : (($student->readiness_score ?? 0) >= 40 ? 'medium' : 'low') }}">
                                {{ $student->readiness_score ?? 0 }}%
                            </span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">Milestones</span>
                            <span class="metric-value">{{ $student->milestones->where('is_completed', true)->count() }}/{{ $student->milestones->count() }}</span>
                        </div>
                    </div>

                    <div class="student-progress">
                        <div class="progress-item">
                            <span class="progress-label">Profile Completion</span>
                            <span class="progress-percent">{{ $student->profile_completion ?? 0 }}%</span>
                            <div class="progress-bar">
                                <div class="progress-fill gold" style="width: {{ $student->profile_completion ?? 0 }}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="student-tags">
                        @foreach($student->competencies->take(3) as $skill)
                            <span class="tag tag-blue">{{ $skill->skill_name }}</span>
                        @endforeach
                        @if($student->competencies->count() > 3)
                            <span class="tag tag-muted">+{{ $student->competencies->count() - 3 }} more</span>
                        @endif
                    </div>
                </div>

                <div class="student-card-footer">
                    <a href="{{ route('admin.students.show', $student) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-eye"></i> View Profile
                    </a>
                    <button class="btn btn-outline btn-sm">
                        <i class="fas fa-envelope"></i> Message
                    </button>
                </div>
            </div>
        @empty
            <div class="empty-state-full">
                <div class="empty-illustration">
                    <i class="fas fa-users-slash"></i>
                </div>
                <h3>No Students Found</h3>
                <p>Try adjusting your search or filter criteria.</p>
                <a href="{{ route('admin.students.index') }}" class="btn btn-primary">
                    <i class="fas fa-undo"></i> Reset Filters
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pagination-wrap">
        {{ $students->withQueryString()->links() }}
    </div>

</div>

<style>
    .students-page {
        padding: 0 4px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 24px;
    }

    .header-left h1 {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: #1a3a5c;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .header-left .subtitle {
        color: #6b7280;
        font-size: 14px;
        margin-top: 2px;
    }

    .header-stats {
        display: flex;
        gap: 24px;
        background: white;
        padding: 12px 24px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }

    .header-stat {
        text-align: center;
    }

    .header-stat .stat-number {
        display: block;
        font-family: 'Playfair Display', serif;
        font-size: 24px;
        font-weight: 700;
        color: #1a3a5c;
    }

    .header-stat .stat-label {
        font-size: 11px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .filter-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        background: white;
        padding: 16px 20px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        margin-bottom: 24px;
    }

    .filter-form {
        flex: 1;
    }

    .filter-group {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-field {
        position: relative;
        flex: 1;
        min-width: 180px;
    }

    .filter-field i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 14px;
    }

    .filter-field input,
    .filter-field select {
        width: 100%;
        padding: 10px 12px 10px 36px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        background: white;
        transition: all 0.3s ease;
    }

    .filter-field input:focus,
    .filter-field select:focus {
        outline: none;
        border-color: #c9a84c;
        box-shadow: 0 0 0 4px rgba(201, 168, 76, 0.15);
    }

    .filter-actions {
        display: flex;
        gap: 8px;
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
        box-shadow: 0 8px 24px rgba(201, 168, 76, 0.25);
    }

    .btn-outline {
        background: transparent;
        color: #1a1a2e;
        border: 2px solid #e5e7eb;
    }

    .btn-outline:hover {
        border-color: #c9a84c;
        color: #c9a84c;
        transform: translateY(-2px);
    }

    .btn-sm {
        padding: 6px 14px;
        font-size: 12px;
    }

    .students-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .student-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .student-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 48px rgba(0, 0, 0, 0.08);
        border-color: #c9a84c;
    }

    .student-card-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 20px;
        background: linear-gradient(135deg, #faf8f2, #f4f1e7);
        border-bottom: 1px solid #e5e7eb;
    }

    .student-avatar-lg {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1a3a5c, #2a5a8c);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 20px;
        flex-shrink: 0;
    }

    .student-card-info {
        flex: 1;
        min-width: 0;
    }

    .student-card-info h4 {
        font-size: 16px;
        font-weight: 600;
        color: #1a3a5c;
        margin: 0;
    }

    .student-card-info h4 a {
        color: #1a3a5c;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .student-card-info h4 a:hover {
        color: #c9a84c;
    }

    .student-id {
        font-size: 12px;
        color: #6b7280;
        display: block;
    }

    .student-programme {
        font-size: 12px;
        color: #6b7280;
        display: block;
    }

    .student-card-status {
        flex-shrink: 0;
    }

    .student-card-body {
        padding: 16px 20px;
        flex: 1;
    }

    .student-metrics {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
        margin-bottom: 14px;
    }

    .metric {
        text-align: center;
        padding: 8px;
        background: #faf8f2;
        border-radius: 6px;
    }

    .metric-label {
        display: block;
        font-size: 10px;
        text-transform: uppercase;
        color: #6b7280;
        letter-spacing: 0.04em;
    }

    .metric-value {
        display: block;
        font-size: 18px;
        font-weight: 700;
        color: #1a3a5c;
        margin-top: 2px;
    }

    .readiness-score.high { color: #2d8f5c; }
    .readiness-score.medium { color: #c9a84c; }
    .readiness-score.low { color: #c65b4e; }

    .student-progress {
        margin-bottom: 12px;
    }

    .progress-item {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    .progress-label {
        font-size: 12px;
        color: #6b7280;
        flex: 1;
    }

    .progress-percent {
        font-size: 12px;
        font-weight: 600;
        color: #1a3a5c;
    }

    .progress-bar {
        width: 100%;
        height: 4px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.6s ease;
    }

    .progress-fill.gold { background: #c9a84c; }

    .student-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .tag {
        font-size: 11px;
        padding: 4px 12px;
        border-radius: 100px;
        font-weight: 500;
    }

    .tag-blue {
        background: #e8f0fe;
        color: #2a5a8c;
    }

    .tag-muted {
        background: #f3f4f6;
        color: #6b7280;
    }

    .student-card-footer {
        display: flex;
        gap: 8px;
        padding: 12px 20px;
        border-top: 1px solid #e5e7eb;
        background: #faf8f2;
        flex-wrap: wrap;
    }

    .student-card-footer .btn {
        flex: 1;
        justify-content: center;
        min-width: 80px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 500;
        color: #2d8f5c;
        padding: 4px 12px;
        border-radius: 100px;
        background: #e9f3ee;
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

    .empty-state-full {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        border: 2px dashed #e5e7eb;
    }

    .empty-state-full .empty-illustration {
        font-size: 64px;
        color: #e5e7eb;
        margin-bottom: 16px;
    }

    .empty-state-full h3 {
        font-size: 20px;
        font-weight: 600;
        color: #1a3a5c;
        margin: 0 0 4px;
    }

    .empty-state-full p {
        color: #6b7280;
        margin-bottom: 20px;
    }

    .pagination-wrap {
        display: flex;
        justify-content: center;
        padding: 12px 0;
    }

    .pagination-wrap nav {
        display: flex;
        gap: 4px;
    }

    .pagination-wrap a,
    .pagination-wrap span {
        padding: 8px 16px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        color: #1a1a2e;
        text-decoration: none;
        font-size: 13px;
        transition: all 0.3s ease;
    }

    .pagination-wrap a:hover {
        background: #c9a84c;
        color: white;
        border-color: #c9a84c;
    }

    .pagination-wrap .active span {
        background: #c9a84c;
        color: white;
        border-color: #c9a84c;
    }

    @media (max-width: 1024px) {
        .students-grid {
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        }
        .student-metrics {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: stretch;
        }
        .header-stats {
            justify-content: space-around;
        }
        .filter-group {
            flex-direction: column;
        }
        .filter-field {
            min-width: 100%;
        }
        .filter-actions {
            width: 100%;
        }
        .filter-actions .btn {
            flex: 1;
            justify-content: center;
        }
        .students-grid {
            grid-template-columns: 1fr;
        }
        .student-card-footer {
            flex-direction: column;
        }
        .student-card-footer .btn {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .header-stats {
            flex-direction: column;
            gap: 8px;
        }
        .header-stat .stat-number {
            font-size: 20px;
        }
        .student-metrics {
            grid-template-columns: 1fr 1fr;
        }
        .student-card-header {
            flex-wrap: wrap;
        }
    }
</style>
@endsection