@extends('layouts.app')

@section('title', 'My Profile')

@section('content')

<style>
    .profile-page {
        padding: 24px 0 40px;
    }

    .profile-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }

    .profile-header h1 {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: var(--primary);
    }

    .profile-header h1 span {
        color: var(--accent);
    }

    .profile-header .subtitle {
        color: var(--text-muted);
        font-size: 14px;
        margin-top: 2px;
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

    .btn-sm {
        padding: 8px 16px;
        font-size: 12px;
    }

    .btn-success {
        background: #2d8f5c;
        color: white;
    }

    .btn-success:hover {
        background: #1e6b44;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(45, 143, 92, 0.3);
    }

    .profile-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .panel {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 24px;
    }

    .panel-full {
        grid-column: 1 / -1;
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

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid var(--border);
        font-size: 14px;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-row .label {
        color: var(--text-muted);
        font-weight: 500;
    }

    .info-row .value {
        font-weight: 500;
        color: var(--text);
    }

    .tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 500;
    }

    .tag-blue {
        background: rgba(26, 58, 92, 0.08);
        color: var(--primary);
    }

    .tag-gold {
        background: rgba(201, 168, 76, 0.12);
        color: var(--accent-dark);
    }

    .tag-green {
        background: rgba(45, 143, 92, 0.12);
        color: var(--success);
    }

    .tag-rose {
        background: rgba(192, 57, 43, 0.08);
        color: var(--danger);
    }

    .tag small {
        opacity: 0.7;
        font-weight: 400;
    }

    .empty-text {
        color: var(--text-muted);
        font-size: 13px;
        padding: 8px 0;
    }

    .completion-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px 24px;
        margin-bottom: 24px;
    }

    .completion-card .top {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .completion-card .top .label {
        font-weight: 500;
        font-size: 14px;
    }

    .completion-card .top .percentage {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: var(--accent-dark);
    }

    .completion-card .bar {
        height: 6px;
        background: var(--bg);
        border-radius: 4px;
        overflow: hidden;
        margin-top: 10px;
    }

    .completion-card .bar .fill {
        height: 100%;
        border-radius: 4px;
        background: var(--accent);
        transition: width 0.6s ease;
    }

    .completion-card .note {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 8px;
    }

    .project-card {
        background: var(--bg);
        border-radius: 8px;
        padding: 16px;
        border: 1px solid var(--border);
    }

    .project-card h4 {
        font-weight: 600;
        font-size: 14px;
        color: var(--primary);
        margin-bottom: 4px;
    }

    .project-card .role {
        font-size: 12px;
        color: var(--text-muted);
    }

    .project-card .desc {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 6px;
    }

    .project-card .tech-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 8px;
    }

    .project-card .tech-tags span {
        background: rgba(26, 58, 92, 0.06);
        padding: 2px 10px;
        border-radius: 100px;
        font-size: 11px;
        color: var(--primary);
    }

    .project-card .achievement {
        font-size: 12px;
        color: var(--success);
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .cert-card {
        background: var(--bg);
        border-radius: 8px;
        padding: 14px 16px;
        border: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cert-card .cert-name {
        font-weight: 500;
        font-size: 14px;
        color: var(--primary);
    }

    .cert-card .cert-org {
        font-size: 12px;
        color: var(--text-muted);
    }

    .cert-card .cert-date {
        font-size: 12px;
        color: var(--text-muted);
    }

    .cert-card .cert-badge {
        font-size: 11px;
        font-weight: 500;
        color: var(--success);
        background: rgba(45, 143, 92, 0.1);
        padding: 2px 10px;
        border-radius: 100px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    @media (max-width: 768px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
        .panel-full {
            grid-column: 1;
        }
        .profile-header h1 {
            font-size: 24px;
        }
        .completion-card .top .percentage {
            font-size: 22px;
        }
        .action-buttons {
            width: 100%;
        }
        .action-buttons .btn {
            flex: 1;
            justify-content: center;
        }
    }
</style>

<div class="profile-page">
    <div class="container">

        <div class="profile-header">
            <div>
                <h1>My <span>Profile</span></h1>
                <p class="subtitle">View and manage your personal information</p>
            </div>
            <div class="action-buttons">
                <a href="{{ route('student.profile.export') }}" class="btn btn-success btn-sm" target="_blank">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
                <a href="{{ route('student.profile.edit') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>
        </div>

        <!-- Completion Card -->
        <div class="completion-card">
            <div class="top">
                <span class="label">Profile Completion</span>
                <span class="percentage">{{ $profileCompletion ?? 0 }}%</span>
            </div>
            <div class="bar">
                <div class="fill" style="width: {{ $profileCompletion ?? 0 }}%"></div>
            </div>
            <p class="note">
                @if(($profileCompletion ?? 0) < 100)
                    Complete your profile to get better career recommendations
                @else
                    🎉 Your profile is complete!
                @endif
            </p>
        </div>

        <div class="profile-grid">

            <!-- Personal Information -->
            <div class="panel">
                <div class="panel-header">
                    <h3><i class="fas fa-user-circle"></i> Personal Information</h3>
                </div>
                <div class="info-row">
                    <span class="label">Full Name</span>
                    <span class="value">{{ $user->name ?? 'Not set' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Email</span>
                    <span class="value">{{ $user->email ?? 'Not set' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Student ID</span>
                    <span class="value">{{ $user->student_id ?? 'Not set' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Programme</span>
                    <span class="value">{{ $user->programme ?? 'Not set' }}</span>
                </div>
                @if($user->profile->phone ?? false)
                    <div class="info-row">
                        <span class="label">Phone</span>
                        <span class="value">{{ $user->profile->phone }}</span>
                    </div>
                @endif
            </div>

            <!-- Academic Information -->
            <div class="panel">
                <div class="panel-header">
                    <h3><i class="fas fa-graduation-cap"></i> Academic Information</h3>
                </div>
                <div class="info-row">
                    <span class="label">CGPA</span>
                    <span class="value">{{ $user->cgpa ?? 'Not set' }}</span>
                </div>
                @if($user->profile->date_of_birth ?? false)
                    <div class="info-row">
                        <span class="label">Date of Birth</span>
                        <span class="value">{{ $user->profile->date_of_birth->format('d M Y') }}</span>
                    </div>
                @endif
                @if($user->profile->nationality ?? false)
                    <div class="info-row">
                        <span class="label">Nationality</span>
                        <span class="value">{{ $user->profile->nationality }}</span>
                    </div>
                @endif
            </div>

            <!-- Skills -->
            <div class="panel">
                <div class="panel-header">
                    <h3><i class="fas fa-tools"></i> Skills & Competencies</h3>
                </div>
                @if($user->competencies && $user->competencies->count() > 0)
                    <div class="tags">
                        @foreach($user->competencies as $skill)
                            <span class="tag tag-blue">
                                {{ $skill->skill_name }}
                                <small>({{ $skill->proficiency_level }})</small>
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="empty-text">No skills added yet</p>
                @endif
            </div>

            <!-- Interests -->
            <div class="panel">
                <div class="panel-header">
                    <h3><i class="fas fa-heart"></i> Interests</h3>
                </div>
                @if($user->interests && $user->interests->count() > 0)
                    <div class="tags">
                        @foreach($user->interests as $interest)
                            <span class="tag tag-rose">{{ $interest->interest_name }}</span>
                        @endforeach
                    </div>
                @else
                    <p class="empty-text">No interests added yet</p>
                @endif
            </div>

            <!-- Projects -->
            <div class="panel panel-full">
                <div class="panel-header">
                    <h3><i class="fas fa-project-diagram"></i> Projects & Experience</h3>
                </div>
                @if($user->projects && $user->projects->count() > 0)
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        @foreach($user->projects as $project)
                            <div class="project-card">
                                <h4>{{ $project->title }}</h4>
                                @if($project->role)
                                    <span class="role"><i class="fas fa-user-tag"></i> {{ $project->role }}</span>
                                @endif
                                @if($project->description)
                                    <p class="desc">{{ Str::limit($project->description, 80) }}</p>
                                @endif
                                @if($project->technologies_used && count($project->technologies_used) > 0)
                                    <div class="tech-tags">
                                        @foreach($project->technologies_used as $tech)
                                            <span>{{ $tech }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if($project->achievements)
                                    <div class="achievement">
                                        <i class="fas fa-trophy"></i> {{ $project->achievements }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="empty-text">No projects added yet</p>
                @endif
            </div>

            <!-- Certifications -->
            <div class="panel panel-full">
                <div class="panel-header">
                    <h3><i class="fas fa-certificate"></i> Certifications</h3>
                </div>
                @if($user->certifications && $user->certifications->count() > 0)
                    <div style="display:grid;gap:10px;">
                        @foreach($user->certifications as $cert)
                            <div class="cert-card">
                                <div>
                                    <div class="cert-name">{{ $cert->certification_name }}</div>
                                    <div class="cert-org">{{ $cert->issuing_organization ?? 'Unknown' }}</div>
                                </div>
                                <div style="text-align:right;">
                                    @if($cert->issue_date)
                                    <div class="cert-date">Issued: {{ $cert->issue_date->format('d M Y') }}</div>
                                @endif
                            @if($cert->certificate_file_path)
                        <span class="cert-badge" style="background:rgba(45,143,92,0.1);color:#2d8f5c;padding:2px 10px;border-radius:100px;font-size:11px;display:inline-flex;align-items:center;gap:4px;">
                         <i class="fas fa-file-alt"></i> File Attached
                        </span>
                            @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="empty-text">No certifications added yet</p>
                @endif
            </div>

            <!-- Aspirations -->
            <div class="panel panel-full">
                <div class="panel-header">
                    <h3><i class="fas fa-star"></i> Career Aspirations</h3>
                </div>
                @if($user->aspirations)
                    <div style="display:grid;gap:10px;">
                        @if($user->aspirations->career_goals && count($user->aspirations->career_goals) > 0)
                            <div class="info-row">
                                <span class="label">Dream Career</span>
                                <span class="value">{{ $user->aspirations->career_goals[0] ?? 'Not set' }}</span>
                            </div>
                        @endif
                        @if($user->aspirations->vision_statement)
                            <div class="info-row">
                                <span class="label">Vision Statement</span>
                                <span class="value">{{ $user->aspirations->vision_statement }}</span>
                            </div>
                        @endif
                        @if($user->aspirations->long_term_goals)
                            <div class="info-row">
                                <span class="label">Long Term Goals</span>
                                <span class="value">{{ $user->aspirations->long_term_goals }}</span>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="empty-text">No career aspirations set yet</p>
                @endif
            </div>

        </div>

    </div>
</div>

@endsection