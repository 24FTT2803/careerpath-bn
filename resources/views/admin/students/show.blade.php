@extends('admin.layouts.admin')

@section('title', 'Student Profile')

@section('content')
<div class="student-profile-page">

    <!-- Page Header -->
    <div class="page-header">
        <div class="header-left">
            <h1>
                <i class="fas fa-user" style="color: #c9a84c;"></i>
                Student Profile
            </h1>
            <p class="subtitle">View student details and career progress</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('student.profile.export.admin', $student->id) }}" class="btn btn-primary" target="_blank">
                <i class="fas fa-file-pdf"></i> Download Profile
            </a>
            <a href="{{ route('admin.students.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Read-Only Notice -->
    <div class="info-banner info-banner-blue">
        <i class="fas fa-info-circle"></i>
        @if($isAdmin)
            You have read-only access to student profiles.
        @else
            Lecturer access - View only. Student data cannot be modified.
        @endif
    </div>

    <!-- Database Status -->
    @if($topRecommendations->isEmpty())
        <div class="info-banner info-banner-gold">
            <i class="fas fa-info-circle"></i>
            <strong>Database Setup in Progress:</strong>
            Career recommendations data is not yet available. This is expected while the database team is working on it.
        </div>
    @endif

    <div class="profile-grid">

        <!-- Left Column -->
        <div class="profile-col-side">

            <!-- Student Info -->
            <div class="card">
                <div class="student-hero">
                    <div class="student-avatar-xl">
                        @if($student->profile?->profile_picture)
                            <img
                                src="{{ asset(
                                    'storage/' .
                                    ltrim(
                                        $student->profile->profile_picture,
                                        '/'
                                    )
                                ) }}"
                                alt="{{ $student->name }} profile picture"
                            >
                        @else
                            {{ strtoupper(substr($student->name, 0, 1)) }}
                        @endif
                    </div>
                    <h2>{{ $student->name }}</h2>
                    <p class="student-programme">{{ $student->programme ?? 'Programme not set' }}</p>
                </div>

                <div class="info-list">
                    <div class="info-row">
                        <span class="info-label">Student ID</span>
                        <span class="info-value">{{ $student->student_id ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value">{{ $student->email }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">CGPA</span>
                        <span class="info-value">{{ $student->cgpa ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Readiness Score</span>
                        <span class="info-value readiness-value">{{ $readinessScore }}%</span>
                    </div>
                </div>
            </div>

            <!-- Academic Groups -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-school"></i> Academic Groups</h3>
                </div>

                @if($student->groupMemberships->isEmpty())
                    <p class="empty-text">Not in any group.</p>
                @else
                    <div class="list-rows">
                        @foreach($student->groupMemberships as $membership)
                            @php $group = $membership->organisationGroup; @endphp

                            @if($group)
                                <div class="list-row">
                                    <div>
                                        <span class="list-row-title">{{ $group->name }}</span>
                                        <span class="list-row-sub">{{ $group->type?->name }}</span>
                                    </div>
                                    <a href="{{ route('admin.business.groups.members.index', $group) }}" class="link">
                                        Manage
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Skills -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-tools"></i> Skills & Competencies</h3>
                </div>

                @if($student->competencies->count() > 0)
                    <div class="tag-cloud">
                        @foreach($student->competencies as $skill)
                            <span class="tag tag-blue">
                                {{ $skill->skill_name }}
                                <span class="tag-sub">{{ $skill->proficiency_level }}</span>
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="empty-text">No skills recorded.</p>
                @endif
            </div>

        </div>

        <!-- Right Column -->
        <div class="profile-col-main">

            <!-- Career Recommendations -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-bullseye"></i> Career Recommendations</h3>
                </div>

                @if($topRecommendations->count() > 0)
                    @foreach($topRecommendations as $rec)
                        <div class="recommendation-row">
                            <div class="recommendation-row-top">
                                <div>
                                    <h4>{{ $rec->jobRole?->title ?? $rec->career?->job_title ?? 'N/A' }}</h4>
                                    <p class="recommendation-subsector">{{ $rec->jobRole?->subSector?->name ?? $rec->career?->subsector ?? '' }}</p>
                                </div>
                                <span class="match-badge">{{ $rec->match_score }}% Match</span>
                            </div>
                            <div class="recommendation-skills">
                                <span class="info-label">Matched Skills:</span>
                                @php
                                    $matched = is_array($rec->matched_skills) ? $rec->matched_skills : [];
                                @endphp
                                @if(count($matched) > 0)
                                    @foreach($matched as $skill)
                                        <span class="tag tag-green">{{ $skill }}</span>
                                    @endforeach
                                @else
                                    <span class="empty-text-inline">None</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="empty-text-centered">
                        <i class="fas fa-info-circle"></i>
                        No career recommendations yet. This will appear once the database team adds the data.
                    </p>
                @endif
            </div>

            <!-- Competency Gaps -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-triangle-exclamation"></i> Competency Gaps</h3>
                </div>

                @if(count($skillGaps) > 0)
                    <div class="gap-grid">
                        @foreach($skillGaps as $gap)
                            <div class="gap-chip">
                                <span class="gap-chip-name">
                                    {{ is_array($gap) ? ($gap['skill_name'] ?? json_encode($gap)) : $gap }}
                                </span>
                                <span class="gap-chip-sub">Needs development</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="empty-text-centered">
                        <i class="fas fa-info-circle"></i>
                        No competency gaps identified yet.
                    </p>
                @endif
            </div>

            <!-- Milestones -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-flag"></i> Milestones</h3>
                </div>

                @if($student->milestones->count() > 0)
                    <div class="list-rows">
                        @foreach($student->milestones as $milestone)
                            <div class="list-row">
                                <div>
                                    <span class="list-row-title">{{ $milestone->title }}</span>
                                    <span class="list-row-sub">{{ $milestone->category }}</span>
                                </div>
                                @if($milestone->is_completed)
                                    <span class="status-pill status-pill-green">
                                        <i class="fas fa-check-circle"></i> Completed
                                    </span>
                                @else
                                    <span class="status-pill status-pill-gold">
                                        <i class="fas fa-clock"></i> In Progress
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="empty-text-centered">No milestones recorded.</p>
                @endif
            </div>

            <!-- AI History -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-clock-rotate-left"></i> Recommendation History</h3>
                </div>

                @if($generations->count() > 0)
                    <div class="list-rows">
                        @foreach($generations as $generation)
                            <div class="list-row">
                                <div>
                                    <span class="list-row-title">Generation #{{ $generation->generation_number }}</span>
                                    <span class="list-row-sub">
                                        {{ $generation->generated_at->format('j M Y') }}
                                        &middot;
                                        {{ $generation->recommendation_count }} {{ Str::plural('recommendation', $generation->recommendation_count) }}
                                    </span>
                                </div>
                                <div class="list-row-actions">
                                    @if($generation->isOutdated())
                                        <span class="status-pill status-pill-gold">Outdated</span>
                                    @elseif($generation->isCurrent())
                                        <span class="status-pill status-pill-green">Current</span>
                                    @else
                                        <span class="status-pill status-pill-muted">Previous</span>
                                    @endif
                                    <a href="{{ route('student.profile.export.admin', [$student->id, $generation->id]) }}" class="link">
                                        Report
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="empty-text-centered">No recommendations generated yet.</p>
                @endif
            </div>

            <!-- Career Adviser Activity -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-comments"></i> Career Adviser Activity</h3>
                </div>

                @if($adviserActivity && $adviserActivity->message_count > 0)
                    <div class="adviser-activity-row">
                        <span class="list-row-title">
                            {{ $adviserActivity->message_count }} {{ Str::plural('message', $adviserActivity->message_count) }}
                        </span>
                        @if($adviserActivity->last_message_at)
                            <span class="list-row-sub">
                                last active {{ $adviserActivity->last_message_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                    <p class="adviser-privacy-note">
                        Conversation contents are private to the student.
                    </p>
                @else
                    <p class="empty-text-centered">Has not used the Career Adviser.</p>
                @endif
            </div>

        </div>
    </div>
</div>

<style>
    .student-profile-page {
        padding: 0 4px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 20px;
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

    .header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
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

    /* Info Banners */
    .info-banner {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 18px;
        border-radius: 8px;
        font-size: 13px;
        margin-bottom: 16px;
        line-height: 1.5;
    }

    .info-banner-blue {
        background: #e8f0fe;
        border-left: 4px solid #2a5a8c;
        color: #1e4870;
    }

    .info-banner-gold {
        background: #fbf1de;
        border-left: 4px solid #c9a84c;
        color: #8a6420;
    }

    /* Grid */
    .profile-grid {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 20px;
        align-items: start;
    }

    @media (max-width: 1024px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
    }

    .profile-col-side,
    .profile-col-main {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Cards */
    .card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 24px;
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    }

    .card-header {
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

    /* Student Hero */
    .student-hero {
        text-align: center;
        margin-bottom: 20px;
    }

    .student-avatar-xl {
        width: 84px;
        height: 84px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1a3a5c, #2a5a8c);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 32px;
        margin: 0 auto 14px;
        overflow: hidden;
    }

    .student-avatar-xl img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .student-hero h2 {
        font-family: 'Playfair Display', serif;
        font-size: 20px;
        font-weight: 700;
        color: #1a3a5c;
        margin: 0;
    }

    .student-programme {
        color: #6b7280;
        font-size: 13px;
        margin-top: 2px;
    }

    /* Info List */
    .info-list {
        border-top: 1px solid #e5e7eb;
        padding-top: 14px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: 13px;
        border-top: 1px solid #f3f4f6;
    }

    .info-row:first-child {
        border-top: none;
    }

    .info-label {
        color: #6b7280;
    }

    .info-value {
        font-weight: 600;
        color: #1a1a2e;
    }

    .readiness-value {
        color: #2d8f5c;
    }

    /* List Rows */
    .list-rows {
        display: flex;
        flex-direction: column;
    }

    .list-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f3f4f6;
        gap: 10px;
    }

    .list-row:last-child {
        border-bottom: none;
    }

    .list-row-title {
        font-weight: 600;
        font-size: 13px;
        color: #1a1a2e;
        display: block;
    }

    .list-row-sub {
        font-size: 11px;
        color: #6b7280;
        display: block;
        margin-top: 1px;
    }

    .list-row-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }

    .link {
        color: #2a5a8c;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .link:hover {
        color: #c9a84c;
        text-decoration: underline;
    }

    /* Tags */
    .tag-cloud {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .tag {
        font-size: 12px;
        padding: 5px 12px;
        border-radius: 100px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .tag-blue {
        background: #e8f0fe;
        color: #2a5a8c;
    }

    .tag-green {
        background: #e9f3ee;
        color: #2d8f5c;
    }

    .tag-sub {
        font-size: 10px;
        opacity: 0.7;
    }

    /* Recommendation Rows */
    .recommendation-row {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 12px;
        transition: all 0.3s ease;
    }

    .recommendation-row:last-child {
        margin-bottom: 0;
    }

    .recommendation-row:hover {
        border-color: #c9a84c;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
    }

    .recommendation-row-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }

    .recommendation-row-top h4 {
        font-size: 15px;
        font-weight: 600;
        color: #1a3a5c;
        margin: 0;
    }

    .recommendation-subsector {
        font-size: 12px;
        color: #6b7280;
        margin-top: 2px;
    }

    .match-badge {
        background: #e9f3ee;
        color: #2d8f5c;
        padding: 5px 14px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .recommendation-skills {
        margin-top: 10px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
    }

    .empty-text-inline {
        color: #9ca3af;
        font-size: 12px;
    }

    /* Gap Grid */
    .gap-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px;
    }

    .gap-chip {
        background: #fbeceb;
        border: 1px solid #f3d9d6;
        border-radius: 8px;
        padding: 12px 14px;
    }

    .gap-chip-name {
        display: block;
        color: #c0392b;
        font-weight: 600;
        font-size: 13px;
    }

    .gap-chip-sub {
        display: block;
        color: #c65b4e;
        font-size: 11px;
        margin-top: 3px;
    }

    /* Status Pills */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 11px;
        border-radius: 100px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .status-pill-green {
        background: #e9f3ee;
        color: #2d8f5c;
    }

    .status-pill-gold {
        background: #fbf1de;
        color: #8a6420;
    }

    .status-pill-muted {
        background: #f3f4f6;
        color: #6b7280;
    }

    /* Adviser Activity */
    .adviser-activity-row {
        padding: 4px 0;
    }

    .adviser-privacy-note {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 8px;
    }

    /* Empty States */
    .empty-text {
        color: #9ca3af;
        font-size: 13px;
    }

    .empty-text-centered {
        color: #9ca3af;
        font-size: 13px;
        text-align: center;
        padding: 24px 0;
    }
</style>
@endsection