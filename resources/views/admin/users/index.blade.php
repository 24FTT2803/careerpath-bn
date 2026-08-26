@extends('admin.layouts.admin')

@section('title', 'Manage Users')

@section('content')
<div class="users-page">

    <!-- Page Header -->
    <div class="page-header">
        <div class="header-left">
            <h1>
                <i class="fas fa-users" style="color: #c9a84c;"></i>
                Manage Users
            </h1>
            <p class="subtitle">Manage all user accounts across the platform</p>
        </div>
        <div class="header-stats">
            <div class="header-stat">
                <span class="stat-number">{{ $users->total() }}</span>
                <span class="stat-label">Total Users</span>
            </div>
            <div class="header-stat">
                <span class="stat-number">{{ $users->where('role', 'student')->count() }}</span>
                <span class="stat-label">Students</span>
            </div>
            <div class="header-stat">
                <span class="stat-number">{{ $users->where('role', 'lecturer')->count() }}</span>
                <span class="stat-label">Lecturers</span>
            </div>
            <div class="header-stat">
                <span class="stat-number">{{ $users->where('role', 'admin')->count() }}</span>
                <span class="stat-label">Admins</span>
            </div>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Add User
            </a>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="filter-bar">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <div class="filter-field">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by name, email or student ID...">
                </div>
                <div class="filter-field">
                    <i class="fas fa-user-tag"></i>
                    <select name="role">
                        <option value="">All Roles</option>
                        <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Student</option>
                        <option value="lecturer" {{ request('role') == 'lecturer' ? 'selected' : '' }}>Lecturer</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline">
                    <i class="fas fa-undo"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Users Grid -->
    <div class="users-grid">
        @forelse($users as $user)
            @php
                $roleColors = [
                    'admin' => 'role-admin',
                    'lecturer' => 'role-lecturer',
                    'student' => 'role-student',
                ];
                $roleIcons = [
                    'admin' => 'fa-crown',
                    'lecturer' => 'fa-chalkboard-teacher',
                    'student' => 'fa-user-graduate',
                ];
                $roleLabels = [
                    'admin' => 'Administrator',
                    'lecturer' => 'Lecturer',
                    'student' => 'Student',
                ];
                $roleColor = $roleColors[$user->role] ?? 'role-student';
                $roleIcon = $roleIcons[$user->role] ?? 'fa-user';
                $roleLabel = $roleLabels[$user->role] ?? ucfirst($user->role);

                $profileCompletion = $user->profile_completion ?? 0;
                $isStudent = $user->role === 'student';
                $isLecturer = $user->role === 'lecturer';
                $isAdmin = $user->role === 'admin';
            @endphp

            <div class="user-card">
                <div class="user-card-header">
                    <div class="user-avatar" style="background: {{ $isAdmin ? 'linear-gradient(135deg, #c65b4e, #e08a7d)' : ($isLecturer ? 'linear-gradient(135deg, #c9a84c, #e8d4a0)' : 'linear-gradient(135deg, #1a3a5c, #2a5a8c)') }};">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div class="user-info">
                        <h4>
                            <a href="{{ route('admin.users.edit', $user) }}">
                                {{ $user->name }}
                            </a>
                        </h4>
                        <span class="user-email">
                            <i class="fas fa-envelope"></i> {{ $user->email }}
                        </span>
                        @if($isStudent && $user->student_id)
                            <span class="user-id">
                                <i class="fas fa-id-card"></i> {{ $user->student_id }}
                            </span>
                        @endif
                        @if($isLecturer || $isAdmin)
                            <span class="user-role-label">
                                <i class="fas fa-briefcase"></i> {{ $roleLabel }}
                            </span>
                        @endif
                    </div>
                    <div class="user-status">
                        <span class="role-badge {{ $roleColor }}">
                            <i class="fas {{ $roleIcon }}"></i>
                            {{ $roleLabel }}
                        </span>
                    </div>
                </div>

                <div class="user-card-body">
                    <!-- STUDENT METRICS -->
                    @if($isStudent)
                        <div class="user-metrics">
                            <div class="metric">
                                <span class="metric-label">Programme</span>
                                <span class="metric-value">{{ $user->programme ?? 'Not set' }}</span>
                            </div>
                            <div class="metric">
                                <span class="metric-label">Profile</span>
                                <span class="metric-value completion-badge {{ $profileCompletion >= 70 ? 'complete' : 'incomplete' }}">
                                    {{ $profileCompletion }}%
                                </span>
                            </div>
                            <div class="metric">
                                <span class="metric-label">CGPA</span>
                                <span class="metric-value">{{ $user->cgpa ?? '-' }}</span>
                            </div>
                            <div class="metric">
                                <span class="metric-label">Joined</span>
                                <span class="metric-value">{{ $user->created_at->format('d M Y') }}</span>
                            </div>
                        </div>

                        <div class="user-progress">
                            <div class="progress-item">
                                <span class="progress-label">Profile Completion</span>
                                <span class="progress-percent">{{ $profileCompletion }}%</span>
                                <div class="progress-bar">
                                    <div class="progress-fill gold" style="width: {{ $profileCompletion }}%"></div>
                                </div>
                            </div>
                            <div class="progress-item" style="margin-top: 8px;">
                                <span class="progress-label">Career Readiness</span>
                                <span class="progress-percent">{{ $user->readiness_score ?? 0 }}%</span>
                                <div class="progress-bar">
                                    <div class="progress-fill green" style="width: {{ $user->readiness_score ?? 0 }}%"></div>
                                </div>
                            </div>
                        </div>

                    <!-- LECTURER METRICS -->
                    @elseif($isLecturer)
                        <div class="user-metrics">
                            <div class="metric">
                                <span class="metric-label">Role</span>
                                <span class="metric-value">Lecturer</span>
                            </div>
                            <div class="metric">
                                <span class="metric-label">Email</span>
                                <span class="metric-value" style="font-size: 12px; word-break: break-all;">{{ $user->email }}</span>
                            </div>
                            <div class="metric">
                                <span class="metric-label">Joined</span>
                                <span class="metric-value">{{ $user->created_at->format('d M Y') }}</span>
                            </div>
                            <div class="metric">
                                <span class="metric-label">Account</span>
                                <span class="metric-value" style="color: #8a6420; font-size: 12px;">Active</span>
                            </div>
                        </div>
                        <div class="lecturer-info">
                            <p style="font-size: 13px; color: #6b7280; margin: 0; text-align: center;">
                                <i class="fas fa-info-circle"></i>
                                Lecturer accounts can view student progress and generate reports
                            </p>
                        </div>

                    <!-- ADMIN METRICS -->
                    @elseif($isAdmin)
                        <div class="user-metrics">
                            <div class="metric">
                                <span class="metric-label">Role</span>
                                <span class="metric-value">Administrator</span>
                            </div>
                            <div class="metric">
                                <span class="metric-label">Email</span>
                                <span class="metric-value" style="font-size: 12px; word-break: break-all;">{{ $user->email }}</span>
                            </div>
                            <div class="metric">
                                <span class="metric-label">Joined</span>
                                <span class="metric-value">{{ $user->created_at->format('d M Y') }}</span>
                            </div>
                            <div class="metric">
                                <span class="metric-label">Access</span>
                                <span class="metric-value" style="color: #c65b4e; font-size: 12px;">Full System</span>
                            </div>
                        </div>
                        <div class="admin-info">
                            <p style="font-size: 13px; color: #6b7280; margin: 0; text-align: center;">
                                <i class="fas fa-crown" style="color: #c9a84c;"></i>
                                Administrator with full system access
                            </p>
                        </div>
                    @endif
                </div>

                <div class="user-card-footer">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    @if($isStudent)
                        <a href="{{ route('admin.students.show', $user) }}" class="btn btn-outline btn-sm">
                            <i class="fas fa-eye"></i> View Profile
                        </a>
                    @endif
                    @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                              style="display:inline; margin-left: auto;"
                              data-confirm-delete data-item-name="{{ $user->name }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state-full">
                <div class="empty-illustration">
                    <i class="fas fa-users-slash"></i>
                </div>
                <h3>No Users Found</h3>
                <p>Try adjusting your search or filter criteria.</p>
                <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
                    <i class="fas fa-undo"></i> Reset Filters
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pagination-wrap">
        {{ $users->withQueryString()->links() }}
    </div>

</div>

<style>
    .users-page {
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
        gap: 20px;
        background: white;
        padding: 12px 20px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        flex-wrap: wrap;
    }

    .header-stat {
        text-align: center;
        padding: 0 8px;
    }

    .header-stat .stat-number {
        display: block;
        font-family: 'Playfair Display', serif;
        font-size: 22px;
        font-weight: 700;
        color: #1a3a5c;
    }

    .header-stat .stat-label {
        font-size: 10px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
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

    .btn-danger {
        background: #fbeceb;
        color: #c65b4e;
        border: 1px solid transparent;
    }

    .btn-danger:hover {
        background: #c65b4e;
        color: white;
        transform: translateY(-2px);
    }

    .btn-sm {
        padding: 6px 14px;
        font-size: 12px;
    }

    .users-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .user-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .user-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 48px rgba(0, 0, 0, 0.08);
        border-color: #c9a84c;
    }

    .user-card-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 20px;
        background: linear-gradient(135deg, #faf8f2, #f4f1e7);
        border-bottom: 1px solid #e5e7eb;
    }

    .user-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 18px;
        flex-shrink: 0;
    }

    .user-info {
        flex: 1;
        min-width: 0;
    }

    .user-info h4 {
        font-size: 15px;
        font-weight: 600;
        color: #1a3a5c;
        margin: 0;
    }

    .user-info h4 a {
        color: #1a3a5c;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .user-info h4 a:hover {
        color: #c9a84c;
    }

    .user-email {
        font-size: 12px;
        color: #6b7280;
        display: block;
    }

    .user-email i {
        margin-right: 4px;
        width: 14px;
    }

    .user-id {
        font-size: 12px;
        color: #6b7280;
        display: block;
    }

    .user-id i {
        margin-right: 4px;
        width: 14px;
    }

    .user-role-label {
        font-size: 12px;
        color: #6b7280;
        display: block;
    }

    .user-role-label i {
        margin-right: 4px;
        width: 14px;
    }

    .user-status {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
        flex-shrink: 0;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 12px;
        border-radius: 100px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .role-admin {
        background: #fbeceb;
        color: #c65b4e;
    }

    .role-lecturer {
        background: #fbf1de;
        color: #8a6420;
    }

    .role-student {
        background: #e8f0fe;
        color: #2a5a8c;
    }

    .user-card-body {
        padding: 16px 20px;
        flex: 1;
    }

    .user-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
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
        font-size: 9px;
        text-transform: uppercase;
        color: #6b7280;
        letter-spacing: 0.04em;
    }

    .metric-value {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #1a3a5c;
        margin-top: 2px;
    }

    .completion-badge {
        padding: 2px 8px;
        border-radius: 100px;
        font-size: 12px;
        display: inline-block;
    }

    .completion-badge.complete {
        background: #e9f3ee;
        color: #2d8f5c;
    }

    .completion-badge.incomplete {
        background: #fbf1de;
        color: #8a6420;
    }

    .user-progress {
        padding-top: 12px;
        border-top: 1px solid #e5e7eb;
    }

    .progress-item {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    .progress-label {
        font-size: 11px;
        color: #6b7280;
        flex: 1;
        min-width: 80px;
    }

    .progress-percent {
        font-size: 11px;
        font-weight: 600;
        color: #1a3a5c;
        min-width: 36px;
        text-align: right;
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
    .progress-fill.green { background: #2d8f5c; }

    .lecturer-info, .admin-info {
        padding-top: 12px;
        border-top: 1px solid #e5e7eb;
    }

    .user-card-footer {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 12px 20px;
        border-top: 1px solid #e5e7eb;
        background: #faf8f2;
    }

    .user-card-footer .btn {
        flex: 1;
        justify-content: center;
        min-width: 70px;
    }

    .user-card-footer form {
        flex: 0 1 auto;
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
        .users-grid {
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        }
        .user-metrics {
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
        .header-actions {
            width: 100%;
        }
        .header-actions .btn {
            width: 100%;
            justify-content: center;
        }
        .filter-group {
            flex-direction: column;
        }
        .filter-field {
            min-width: 100%;
        }
        .users-grid {
            grid-template-columns: 1fr;
        }
        .user-card-header {
            flex-wrap: wrap;
        }
        .user-status {
            flex-direction: row;
            width: 100%;
            justify-content: flex-start;
            gap: 8px;
        }
        .user-card-footer {
            flex-direction: column;
        }
        .user-card-footer .btn {
            width: 100%;
        }
        .user-card-footer form {
            flex: 1;
        }
        .user-card-footer form .btn {
            width: 100%;
        }
        .user-metrics {
            grid-template-columns: 1fr 1fr;
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
        .user-card-header {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .user-info {
            text-align: center;
        }
        .user-status {
            justify-content: center;
        }
        .user-metrics {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection