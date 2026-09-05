<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <title>
        CareerPath BN - {{ $user->name }} - Student Career Profile
    </title>

    <style>
        @page {
            margin: 22mm 16mm 20mm 16mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.45;
            color: #263238;
        }

        .header {
            border-bottom: 3px solid #c9a84c;
            padding-bottom: 14px;
            margin-bottom: 22px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
        }

        .brand {
            font-size: 25px;
            font-weight: bold;
            color: #1a3a5c;
        }

        .brand-gold {
            color: #c9a84c;
        }

        .report-title {
            margin-top: 3px;
            font-size: 13px;
            color: #667085;
        }

        .generated {
            text-align: right;
            font-size: 9px;
            color: #7b8794;
            line-height: 1.6;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            margin: 0 0 10px 0;
            padding-bottom: 6px;
            border-bottom: 1px solid #d8dee6;
            font-size: 14px;
            font-weight: bold;
            color: #1a3a5c;
        }

        .subsection-title {
            margin: 10px 0 6px 0;
            font-size: 11px;
            font-weight: bold;
            color: #344054;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #edf0f3;
            vertical-align: top;
        }

        .info-label {
            width: 25%;
            font-weight: bold;
            color: #667085;
            background: #fafafa;
        }

        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 4px;
        }

        .summary-table td {
            width: 25%;
            padding: 12px 6px;
            text-align: center;
            border: 1px solid #dde3ea;
            background: #faf8f2;
        }

        .summary-number {
            display: block;
            margin-bottom: 2px;
            font-size: 18px;
            font-weight: bold;
            color: #1a3a5c;
        }

        .summary-label {
            display: block;
            font-size: 8px;
            text-transform: uppercase;
            color: #667085;
        }

        .item-card {
            margin-bottom: 8px;
            padding: 9px 10px;
            border: 1px solid #e1e6eb;
            background: #ffffff;
        }

        .item-title {
            font-size: 11px;
            font-weight: bold;
            color: #1a3a5c;
        }

        .item-meta {
            margin-top: 2px;
            font-size: 9px;
            color: #667085;
        }

        .item-description {
            margin: 5px 0 0 0;
            color: #475467;
        }

        .badge {
            display: inline-block;
            margin: 2px 3px 2px 0;
            padding: 3px 7px;
            border: 1px solid #d7dee7;
            background: #f5f7fa;
            font-size: 8px;
            color: #344054;
        }

        .badge-blue {
            border-color: #cbdcef;
            background: #eef4fb;
            color: #245681;
        }

        .badge-gold {
            border-color: #e8d8a8;
            background: #fbf6e8;
            color: #80611b;
        }

        .badge-green {
            border-color: #cce3d6;
            background: #eff8f3;
            color: #28724b;
        }

        .badge-purple {
            border-color: #dbd2ea;
            background: #f5f1fa;
            color: #684d8e;
        }

        .competency-table,
        .gap-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .competency-table th,
        .gap-table th {
            padding: 6px 7px;
            text-align: left;
            background: #f3f5f7;
            border: 1px solid #dde3e8;
            font-size: 9px;
            color: #475467;
        }

        .competency-table td,
        .gap-table td {
            padding: 6px 7px;
            border: 1px solid #e2e7ec;
            vertical-align: top;
        }

        .recommendation {
            margin-bottom: 15px;
            border: 1px solid #d9e0e7;
        }

        .recommendation-header {
            padding: 10px 12px;
            background: #f7f8fa;
            border-bottom: 1px solid #d9e0e7;
        }

        .recommendation-header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .recommendation-header-table td {
            vertical-align: middle;
        }

        .rank {
            margin-bottom: 2px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #c19b39;
        }

        .career-name {
            font-size: 14px;
            font-weight: bold;
            color: #1a3a5c;
        }

        .career-subsector {
            margin-top: 1px;
            font-size: 9px;
            color: #667085;
        }

        .score-area {
            width: 38%;
            text-align: right;
            white-space: nowrap;
        }

        .score-box {
            display: inline-block;
            min-width: 68px;
            margin-left: 5px;
            padding: 5px;
            text-align: center;
            border: 1px solid #dce2e8;
            background: #ffffff;
        }

        .score-number {
            display: block;
            font-size: 13px;
            font-weight: bold;
            color: #1a3a5c;
        }

        .score-label {
            display: block;
            font-size: 7px;
            text-transform: uppercase;
            color: #667085;
        }

        .recommendation-body {
            padding: 10px 12px 12px 12px;
        }

        .recommendation-block {
            margin-top: 9px;
        }

        .recommendation-block:first-child {
            margin-top: 0;
        }

        .recommendation-label {
            margin-bottom: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #344054;
        }

        .development-list {
            margin: 4px 0 0 17px;
            padding: 0;
        }

        .development-list li {
            margin-bottom: 4px;
            padding-left: 2px;
        }

        .milestone-status {
            font-weight: bold;
        }

        .status-completed {
            color: #28724b;
        }

        .status-progress {
            color: #80611b;
        }

        .empty-note {
            padding: 8px 10px;
            border: 1px dashed #d6dce3;
            color: #7b8794;
            font-style: italic;
        }

        .footer {
            position: fixed;
            bottom: -13mm;
            left: 0;
            right: 0;
            padding-top: 5px;
            border-top: 1px solid #e0e4e8;
            text-align: center;
            font-size: 7px;
            color: #8a94a1;
        }

        .avoid-break {
            page-break-inside: avoid;
        }

        p {
            margin: 0;
        }
    </style>
</head>

<body>

@php
    $technicalCompetencies = $user->competencies->filter(function ($competency) {
        $category = strtolower((string) $competency->category);

        return ! str_contains($category, 'soft');
    });

    $softCompetencies = $user->competencies->filter(function ($competency) {
        $category = strtolower((string) $competency->category);

        return str_contains($category, 'soft');
    });

    $recommendations = $user->careerRecommendations->sortBy('rank');

    $aspiration = $user->aspirations;

    $careerGoals = $aspiration && is_array($aspiration->career_goals)
        ? $aspiration->career_goals
        : [];

    $preferredIndustries = $aspiration && is_array($aspiration->preferred_industries)
        ? $aspiration->preferred_industries
        : [];

    $preferredWorkActivities = $aspiration && is_array($aspiration->preferred_work_activities)
        ? $aspiration->preferred_work_activities
        : [];
@endphp

<div class="footer">
    CareerPath BN — Politeknik Brunei |
    Student Career Profile Report |
    Generated {{ now()->format('d M Y') }}
</div>

<!-- Header -->
<div class="header">
    <table class="header-table">
        <tr>
            <td>
                <div class="brand">
                    CareerPath <span class="brand-gold">BN</span>
                </div>

                <div class="report-title">
                    Student Career Profile Report
                </div>
            </td>

            <td class="generated">
                <strong>{{ $user->name }}</strong><br>
                Generated {{ now()->format('d M Y') }}<br>
                {{ now()->format('h:i A') }}
            </td>
        </tr>
    </table>
</div>

<!-- Student Information -->
<div class="section">
    <div class="section-title">1. Student Information</div>

    <table class="info-table">
        <tr>
            <td class="info-label">Full Name</td>
            <td>{{ $user->name }}</td>
        </tr>

        @if($user->student_id)
            <tr>
                <td class="info-label">Student ID</td>
                <td>{{ $user->student_id }}</td>
            </tr>
        @endif

        <tr>
            <td class="info-label">Email</td>
            <td>{{ $user->email }}</td>
        </tr>

        <tr>
            <td class="info-label">Programme</td>
            <td>{{ $user->programme ?? 'Not provided' }}</td>
        </tr>

        @if($user->cgpa !== null)
            <tr>
                <td class="info-label">CGPA</td>
                <td>{{ $user->cgpa }}</td>
            </tr>
        @endif

        @if($user->profile?->phone)
            <tr>
                <td class="info-label">Phone</td>
                <td>{{ $user->profile->phone }}</td>
            </tr>
        @elseif($user->phone)
            <tr>
                <td class="info-label">Phone</td>
                <td>{{ $user->phone }}</td>
            </tr>
        @endif
    </table>
</div>

<!-- Career Summary -->
<div class="section avoid-break">
    <div class="section-title">2. Career Profile Summary</div>

    <table class="summary-table">
        <tr>
            <td>
                <span class="summary-number">
                    {{ number_format($profileCompletion, 0) }}%
                </span>
                <span class="summary-label">
                    Profile Completion
                </span>
            </td>

            <td>
                <span class="summary-number">
                    {{ number_format($readinessScore, 0) }}%
                </span>
                <span class="summary-label">
                    Career Readiness
                </span>
            </td>

            <td>
                <span class="summary-number">
                    {{ $user->competencies->count() }}
                </span>
                <span class="summary-label">
                    Competencies
                </span>
            </td>

            <td>
                <span class="summary-number">
                    {{ $recommendations->count() }}
                </span>
                <span class="summary-label">
                    Recommendations
                </span>
            </td>
        </tr>
    </table>
</div>

<!-- Academic Background -->
@if($user->academicRecords->isNotEmpty())
    <div class="section">
        <div class="section-title">3. Academic Background</div>

        @foreach($user->academicRecords as $record)
            <div class="item-card avoid-break">
                <div class="item-title">
                    {{ $record->programme_name ?: 'Academic Record' }}
                </div>

                <div class="item-meta">
                    @if($record->institution_name)
                        {{ $record->institution_name }}
                    @endif

                    @if($record->level)
                        @if($record->institution_name)
                            |
                        @endif

                        {{ $record->level }}
                    @endif

                    @if($record->is_current)
                        |
                        Current
                    @endif
                </div>

                @if($record->start_date || $record->end_date)
                    <div class="item-meta">
                        Period:
                        {{ $record->start_date ?: 'Not specified' }}
                        -
                        {{ $record->end_date ?: ($record->is_current ? 'Present' : 'Not specified') }}
                    </div>
                @endif

                @if($record->cgpa !== null)
                    <div class="item-meta">
                        CGPA: {{ $record->cgpa }}
                    </div>
                @endif

                @if($record->achievements)
                    <p class="item-description">
                        <strong>Achievements:</strong>
                        {{ $record->achievements }}
                    </p>
                @endif
            </div>
        @endforeach
    </div>
@endif

<!-- Competencies -->
@if($user->competencies->isNotEmpty())
    <div class="section">
        <div class="section-title">4. Skills & Competencies</div>

        @if($technicalCompetencies->isNotEmpty())
            <div class="subsection-title">Technical Competencies</div>

            <table class="competency-table">
                <thead>
                    <tr>
                        <th style="width: 55%;">Competency</th>
                        <th style="width: 20%;">Type</th>
                        <th style="width: 25%;">Proficiency</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($technicalCompetencies as $competency)
                        <tr>
                            <td>
                                {{ $competency->skill_name }}
                            </td>

                            <td>
                                Technical
                            </td>

                            <td>
                                {{ $competency->proficiency_level ?: 'Not specified' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if($softCompetencies->isNotEmpty())
            <div class="subsection-title">Soft Skills</div>

            <table class="competency-table">
                <thead>
                    <tr>
                        <th style="width: 55%;">Competency</th>
                        <th style="width: 20%;">Type</th>
                        <th style="width: 25%;">Proficiency</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($softCompetencies as $competency)
                        <tr>
                            <td>
                                {{ $competency->skill_name }}
                            </td>

                            <td>
                                Soft Skill
                            </td>

                            <td>
                                {{ $competency->proficiency_level ?: 'Not specified' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endif

<!-- Interests -->
@if($user->interests->isNotEmpty())
    <div class="section">
        <div class="section-title">5. Interests</div>

        @foreach($user->interests as $interest)
            <div class="item-card avoid-break">
                <div class="item-title">
                    {{ $interest->interest_name }}
                </div>

                @if($interest->category || $interest->priority)
                    <div class="item-meta">
                        @if($interest->category)
                            Category: {{ $interest->category }}
                        @endif

                        @if($interest->priority)
                            @if($interest->category)
                                |
                            @endif

                            Priority: {{ $interest->priority }}
                        @endif
                    </div>
                @endif

                @if($interest->description)
                    <p class="item-description">
                        {{ $interest->description }}
                    </p>
                @endif
            </div>
        @endforeach
    </div>
@endif

<!-- Projects -->
@if($user->projects->isNotEmpty())
    <div class="section">
        <div class="section-title">6. Projects & Experience</div>

        @foreach($user->projects as $project)
            <div class="item-card avoid-break">
                <div class="item-title">
                    {{ $project->title }}
                </div>

                @if($project->role)
                    <div class="item-meta">
                        Role: {{ $project->role }}
                    </div>
                @endif

                @if($project->description)
                    <p class="item-description">
                        {{ $project->description }}
                    </p>
                @endif

                @if(
                    is_array($project->technologies_used)
                    && count($project->technologies_used) > 0
                )
                    <div style="margin-top: 6px;">
                        @foreach($project->technologies_used as $technology)
                            <span class="badge badge-blue">
                                {{ $technology }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif

<!-- Certifications -->
@if($user->certifications->isNotEmpty())
    <div class="section">
        <div class="section-title">7. Certifications</div>

        @foreach($user->certifications as $certification)
            <div class="item-card avoid-break">
                <div class="item-title">
                    {{ $certification->certification_name }}
                </div>

                @if($certification->issuing_organization)
                    <div class="item-meta">
                        Issued by {{ $certification->issuing_organization }}
                    </div>
                @endif

                @if($certification->issue_date)
                    <div class="item-meta">
                        Issued:
                        {{ $certification->issue_date->format('d M Y') }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif

<!-- Career Aspirations -->
@if(
    $aspiration
    && (
        count($careerGoals) > 0
        || count($preferredIndustries) > 0
        || count($preferredWorkActivities) > 0
        || $aspiration->vision_statement
        || $aspiration->mission_statement
        || $aspiration->long_term_goals
    )
)
    <div class="section">
        <div class="section-title">8. Career Aspirations</div>

        @if(count($careerGoals) > 0)
            <div class="subsection-title">Career Goals</div>

            @foreach($careerGoals as $goal)
                <span class="badge badge-gold">
                    {{ $goal }}
                </span>
            @endforeach
        @endif

        @if(count($preferredIndustries) > 0)
            <div class="subsection-title">Preferred Industries</div>

            @foreach($preferredIndustries as $industry)
                <span class="badge badge-purple">
                    {{ $industry }}
                </span>
            @endforeach
        @endif

        @if(count($preferredWorkActivities) > 0)
            <div class="subsection-title">Preferred Work Activities</div>

            @foreach($preferredWorkActivities as $activity)
                <span class="badge badge-blue">
                    {{ $activity }}
                </span>
            @endforeach
        @endif

        @if($aspiration->vision_statement)
            <div class="subsection-title">Vision</div>

            <div class="item-card">
                {{ $aspiration->vision_statement }}
            </div>
        @endif

        @if($aspiration->mission_statement)
            <div class="subsection-title">Mission</div>

            <div class="item-card">
                {{ $aspiration->mission_statement }}
            </div>
        @endif

        @if($aspiration->long_term_goals)
            <div class="subsection-title">Long-Term Goals</div>

            <div class="item-card">
                {{ $aspiration->long_term_goals }}
            </div>
        @endif
    </div>
@endif

<!-- Career Recommendations -->
@if($recommendations->isNotEmpty())
    <div class="section">
        <div class="section-title">9. Career Recommendations</div>

        @foreach($recommendations as $recommendation)
            @php
                $matchedSkills = is_array($recommendation->matched_skills)
                    ? $recommendation->matched_skills
                    : [];

                $skillGaps = is_array($recommendation->skill_gaps)
                    ? $recommendation->skill_gaps
                    : [];

                $developmentPlan = is_array($recommendation->development_plan)
                    ? $recommendation->development_plan
                    : [];
            @endphp

            <div class="recommendation">
                <div class="recommendation-header">
                    <table class="recommendation-header-table">
                        <tr>
                            <td>
                                <div class="rank">
                                    Recommendation #{{ $recommendation->rank }}
                                </div>

                                <div class="career-name">
                                    {{ $recommendation->career?->job_title ?? 'Career Recommendation' }}
                                </div>

                                @if($recommendation->career?->subsector)
                                    <div class="career-subsector">
                                        {{ $recommendation->career->subsector }}
                                    </div>
                                @endif
                            </td>

                            <td class="score-area">
                                <span class="score-box">
                                    <span class="score-number">
                                        {{ number_format($recommendation->match_score, 0) }}%
                                    </span>
                                    <span class="score-label">
                                        Match
                                    </span>
                                </span>

                                <span class="score-box">
                                    <span class="score-number">
                                        {{ number_format($recommendation->career_readiness_score, 0) }}%
                                    </span>
                                    <span class="score-label">
                                        Readiness
                                    </span>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="recommendation-body">
                    @if($recommendation->explanation)
                        <div class="recommendation-block">
                            <div class="recommendation-label">
                                Why This Career Matches
                            </div>

                            <p>
                                {{ $recommendation->explanation }}
                            </p>
                        </div>
                    @endif

                    @if(count($matchedSkills) > 0)
                        <div class="recommendation-block">
                            <div class="recommendation-label">
                                Matching Strengths
                            </div>

                            @foreach($matchedSkills as $skill)
                                <span class="badge badge-green">
                                    {{ $skill }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    @if(count($skillGaps) > 0)
                        <div class="recommendation-block">
                            <div class="recommendation-label">
                                Priority Skill Gaps
                            </div>

                            <table class="gap-table">
                                <thead>
                                    <tr>
                                        <th style="width: 45%;">
                                            Competency
                                        </th>

                                        <th style="width: 27.5%;">
                                            Current Level
                                        </th>

                                        <th style="width: 27.5%;">
                                            Recommended Level
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($skillGaps as $gap)
                                        <tr>
                                            <td>
                                                {{ $gap['skill_name'] ?? 'Skill' }}
                                            </td>

                                            <td>
                                                {{ ucfirst($gap['current_level'] ?? 'Not specified') }}
                                            </td>

                                            <td>
                                                {{ ucfirst($gap['recommended_level'] ?? 'Not specified') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if(count($developmentPlan) > 0)
                        <div class="recommendation-block">
                            <div class="recommendation-label">
                                Development Plan
                            </div>

                            <ol class="development-list">
                                @foreach($developmentPlan as $step)
                                    <li>
                                        {{ $step }}
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

<!-- Milestones -->
@if($user->milestones->isNotEmpty())
    <div class="section">
        <div class="section-title">10. Development Milestones</div>

        @foreach($user->milestones as $milestone)
            <div class="item-card avoid-break">
                <div class="item-title">
                    {{ $milestone->title }}
                </div>

                <div class="item-meta">
                    @if($milestone->category)
                        {{ $milestone->category }}
                    @endif

                    @if($milestone->priority)
                        @if($milestone->category)
                            |
                        @endif

                        Priority: {{ ucfirst($milestone->priority) }}
                    @endif

                    @if($milestone->target_date)
                        |
                        Target:
                        {{ $milestone->target_date->format('d M Y') }}
                    @endif
                </div>

                @if($milestone->description)
                    <p class="item-description">
                        {{ $milestone->description }}
                    </p>
                @endif

                <div style="margin-top: 5px;">
                    @if($milestone->is_completed)
                        <span class="milestone-status status-completed">
                            Completed
                        </span>

                        @if($milestone->completed_date)
                            —
                            {{ $milestone->completed_date->format('d M Y') }}
                        @endif
                    @else
                        <span class="milestone-status status-progress">
                            In Progress
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

<!-- No Recommendation Notice -->
@if($recommendations->isEmpty())
    <div class="section">
        <div class="section-title">Career Recommendations</div>

        <div class="empty-note">
            No career recommendations have been generated for this student yet.
        </div>
    </div>
@endif

</body>
</html>