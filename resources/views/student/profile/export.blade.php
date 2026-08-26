<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Profile - {{ $user->name }}</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            color: #1a1a2e;
            padding: 40px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #c9a84c;
            margin-bottom: 30px;
        }
        .header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: #1a3a5c;
            margin: 0;
        }
        .header h1 span {
            color: #c9a84c;
        }
        .header .subtitle {
            color: #6b7280;
            font-size: 14px;
            margin-top: 4px;
        }
        .section {
            margin-bottom: 24px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a3a5c;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 12px;
        }
        .info-row {
            display: flex;
            padding: 6px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .info-row .label {
            font-weight: 500;
            color: #6b7280;
            width: 160px;
            flex-shrink: 0;
        }
        .info-row .value {
            flex: 1;
        }
        .badge {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 500;
            margin: 2px;
        }
        .badge-blue { background: #e8f0fe; color: #2a5a8c; }
        .badge-gold { background: #fbf1de; color: #8a6420; }
        .badge-green { background: #e9f3ee; color: #2d8f5c; }
        .badge-rose { background: #fbeceb; color: #c65b4e; }
        .badge-purple { background: #f1ecf7; color: #7a5ea8; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin: 20px 0;
        }
        .stat-card {
            text-align: center;
            padding: 16px;
            background: #faf8f2;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .stat-number {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 700;
            color: #1a3a5c;
        }
        .stat-label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            display: block;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        .recommendation-item {
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            margin-bottom: 8px;
        }
        .recommendation-item .title {
            font-weight: 600;
            color: #1a3a5c;
        }
        .recommendation-item .score {
            float: right;
            color: #c9a84c;
            font-weight: 600;
        }
        .skill-tag {
            display: inline-block;
            padding: 4px 12px;
            background: #e8f0fe;
            color: #2a5a8c;
            border-radius: 100px;
            font-size: 12px;
            margin: 2px 4px 2px 0;
        }
        @media print {
            body { padding: 20px; }
            .stat-card { break-inside: avoid; }
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>CareerPath <span>BN</span></h1>
        <p class="subtitle">Student Profile Report</p>
        <p style="color: #6b7280; font-size: 13px;">Generated: {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    <!-- Student Info -->
    <div class="section">
        <div class="section-title">Student Information</div>
        <div class="info-row">
            <span class="label">Full Name</span>
            <span class="value">{{ $user->name }}</span>
        </div>
        <div class="info-row">
            <span class="label">Email</span>
            <span class="value">{{ $user->email }}</span>
        </div>
        @if($user->student_id)
        <div class="info-row">
            <span class="label">Student ID</span>
            <span class="value">{{ $user->student_id }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="label">Programme</span>
            <span class="value">{{ $user->programme ?? 'Not set' }}</span>
        </div>
        @if($user->cgpa)
        <div class="info-row">
            <span class="label">CGPA</span>
            <span class="value">{{ $user->cgpa }}</span>
        </div>
        @endif
        @if($user->profile->phone ?? false)
        <div class="info-row">
            <span class="label">Phone</span>
            <span class="value">{{ $user->profile->phone }}</span>
        </div>
        @endif
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-number">{{ $profileCompletion }}%</span>
            <span class="stat-label">Profile Completion</span>
        </div>
        <div class="stat-card">
            <span class="stat-number">{{ $readinessScore }}%</span>
            <span class="stat-label">Career Readiness</span>
        </div>
        <div class="stat-card">
            <span class="stat-number">{{ $user->competencies->count() }}</span>
            <span class="stat-label">Skills</span>
        </div>
        <div class="stat-card">
            <span class="stat-number">{{ $user->milestones->where('is_completed', true)->count() }}</span>
            <span class="stat-label">Milestones Completed</span>
        </div>
    </div>

    <!-- Career Recommendations -->
    @if($user->careerRecommendations->count() > 0)
    <div class="section">
        <div class="section-title">Career Recommendations</div>
        @foreach($user->careerRecommendations as $rec)
            <div class="recommendation-item">
                <span class="title">{{ $rec->career->job_title ?? 'Career' }}</span>
                <span class="score">{{ number_format($rec->match_score, 0) }}% Match</span>
                <p style="font-size: 13px; color: #6b7280; margin: 4px 0 0 0;">
                    {{ $rec->career->subsector ?? '' }}
                </p>
            </div>
        @endforeach
    </div>
    @endif

    <!-- Skills -->
    @if($user->competencies->count() > 0)
    <div class="section">
        <div class="section-title">Skills & Competencies</div>
        @foreach($user->competencies as $skill)
            <span class="skill-tag">{{ $skill->skill_name }}</span>
        @endforeach
    </div>
    @endif

    <!-- Projects -->
    @if($user->projects->count() > 0)
    <div class="section">
        <div class="section-title">Projects</div>
        @foreach($user->projects as $project)
            <div style="padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                <strong>{{ $project->title }}</strong>
                @if($project->role)
                    <span style="color: #6b7280; font-size: 13px;"> - {{ $project->role }}</span>
                @endif
                @if($project->description)
                    <p style="font-size: 13px; color: #6b7280; margin: 4px 0 0 0;">{{ $project->description }}</p>
                @endif
                @if($project->technologies_used && count($project->technologies_used) > 0)
                    <div style="margin-top: 4px;">
                        @foreach($project->technologies_used as $tech)
                            <span class="badge badge-blue">{{ $tech }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
    @endif

    <!-- Certifications -->
    @if($user->certifications->count() > 0)
    <div class="section">
        <div class="section-title">Certifications</div>
        @foreach($user->certifications as $cert)
            <div style="padding: 4px 0;">
                <strong>{{ $cert->certification_name }}</strong>
                @if($cert->issuing_organization)
                    <span style="color: #6b7280; font-size: 13px;"> - {{ $cert->issuing_organization }}</span>
                @endif
                @if($cert->issue_date)
                    <span style="color: #6b7280; font-size: 12px; float: right;">
                        Issued: {{ $cert->issue_date->format('d M Y') }}
                    </span>
                @endif
            </div>
        @endforeach
    </div>
    @endif

    <!-- Milestones -->
    @if($user->milestones->count() > 0)
    <div class="section">
        <div class="section-title">Milestones</div>
        @foreach($user->milestones as $milestone)
            <div style="display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid #f3f4f6;">
                <span>
                    {{ $milestone->title }}
                    <span class="badge badge-gold" style="font-size: 10px;">{{ $milestone->category }}</span>
                </span>
                <span style="color: {{ $milestone->is_completed ? '#2d8f5c' : '#6b7280' }};">
                    {{ $milestone->is_completed ? '✅ Completed' : '⏳ In Progress' }}
                </span>
            </div>
        @endforeach
    </div>
    @endif

    <div class="footer">
        <p>Generated by CareerPath BN - Politeknik Brunei</p>
        <p style="font-size: 10px;">This is a computer-generated document. No signature required.</p>
    </div>

</body>
</html>