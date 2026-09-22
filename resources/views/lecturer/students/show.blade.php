@extends('admin.layouts.admin')

@section('title', $student->name)

@section('content')
<div>
    <div class="page-header">
        <div>
            <h1>👤 {{ $student->name }}</h1>
            <p class="subtitle">
                {{ $student->programme ?? 'Programme not set' }}
            </p>
        </div>

        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a
                href="{{ route('student.profile.export.admin', $student->id) }}"
                class="btn btn-outline"
                target="_blank"
            >
                <i class="fas fa-file-pdf"></i> Download PDF
            </a>

            <a
                href="{{ route('lecturer.students.index') }}"
                class="btn btn-outline"
            >
                <i class="fas fa-arrow-left"></i> Back to My Students
            </a>
        </div>
    </div>

    <!-- Profile -->
    <div class="card">
        <h3 class="card-heading">Profile</h3>

        <table class="admin-table">
            <tbody>
                <tr>
                    <td class="cell-sub" style="width:30%;">Student ID</td>
                    <td>{{ $student->student_id ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="cell-sub">Email</td>
                    <td>{{ $student->email }}</td>
                </tr>
                <tr>
                    <td class="cell-sub">Phone</td>
                    <td>{{ $student->phone ?? $student->profile?->phone ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="cell-sub">Date of birth</td>
                    <td>
                        {{ $student->profile?->date_of_birth?->format('j M Y') ?? '—' }}
                    </td>
                </tr>
                <tr>
                    <td class="cell-sub">Nationality</td>
                    <td>{{ $student->profile?->nationality ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="cell-sub">CGPA</td>
                    <td>{{ $student->cgpa ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="cell-sub">Profile completion</td>
                    <td>{{ $student->profile_completion ?? 0 }}%</td>
                </tr>
                <tr>
                    <td class="cell-sub">Career readiness</td>
                    <td>{{ $student->readiness_score ?? 0 }}%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Academic Records -->
    @if($student->academicRecords->isNotEmpty())
        <div class="card">
            <h3 class="card-heading">
                Academic Background ({{ $student->academicRecords->count() }})
            </h3>

            @foreach($student->academicRecords as $record)
                <div style="padding:12px 0;border-bottom:1px solid #e5e7eb;">
                    <div style="font-weight:600;color:#1a3a5c;font-size:14px;">
                        {{ $record->programme_name ?: 'Academic record' }}
                    </div>

                    <div class="cell-sub" style="margin-top:2px;">
                        {{ $record->institution_name }}

                        @if($record->level)
                            · {{ $record->level }}
                        @endif

                        @if($record->is_current)
                            · Current
                        @endif
                    </div>

                    @if($record->start_date || $record->end_date)
                        <div class="cell-sub">
                            {{ $record->start_date ?? '—' }}
                            –
                            {{ $record->end_date ?? ($record->is_current ? 'Present' : '—') }}
                        </div>
                    @endif

                    @if($record->cgpa !== null)
                        <div class="cell-sub">CGPA: {{ $record->cgpa }}</div>
                    @endif

                    @if($record->achievements)
                        <p style="margin:6px 0 0;font-size:13px;color:#6b7280;">
                            {{ $record->achievements }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <!-- Skills & Competencies -->
    <div class="card">
        <h3 class="card-heading">
            Skills &amp; Competencies ({{ $student->competencies->count() }})
        </h3>

        @if($student->competencies->isEmpty())
            <p class="empty-text">No competencies recorded.</p>
        @else
            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                @foreach($student->competencies as $skill)
                    <span
                        class="status-pill status-pill-blue"
                        style="font-size:12px;"
                    >
                        {{ $skill->skill_name }}
                        <span style="opacity:0.65;">
                            · {{ $skill->proficiency_level }}
                        </span>
                    </span>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Interests -->
    @if($student->interests->isNotEmpty())
        <div class="card">
            <h3 class="card-heading">
                Interests ({{ $student->interests->count() }})
            </h3>

            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                @foreach($student->interests as $interest)
                    <span
                        class="status-pill status-pill-gold"
                        style="font-size:12px;"
                    >
                        {{ $interest->interest_name }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Projects -->
    @if($student->projects->isNotEmpty())
        <div class="card">
            <h3 class="card-heading">
                Projects &amp; Experience ({{ $student->projects->count() }})
            </h3>

            @foreach($student->projects as $project)
                <div style="padding:14px 0;border-bottom:1px solid #e5e7eb;">
                    <div style="font-weight:600;color:#1a3a5c;font-size:14px;">
                        {{ $project->title }}
                    </div>

                    @if($project->role)
                        <div class="cell-sub" style="margin-top:2px;">
                            <i class="fas fa-user-tag"></i> {{ $project->role }}
                        </div>
                    @endif

                    @if($project->description)
                        <p style="margin:8px 0 0;font-size:13px;color:#374151;line-height:1.6;">
                            {{ $project->description }}
                        </p>
                    @endif

                    @if(is_array($project->technologies_used) && count($project->technologies_used) > 0)
                        <div style="display:flex;flex-wrap:wrap;gap:5px;margin-top:8px;">
                            @foreach($project->technologies_used as $tech)
                                <span
                                    class="status-pill status-pill-blue"
                                    style="font-size:11px;"
                                >
                                    {{ $tech }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    @if($project->achievements)
                        <p style="margin:8px 0 0;font-size:12.5px;color:#2d8f5c;">
                            <i class="fas fa-trophy"></i> {{ $project->achievements }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <!-- Certifications -->
    @if($student->certifications->isNotEmpty())
        <div class="card">
            <h3 class="card-heading">
                Certifications ({{ $student->certifications->count() }})
            </h3>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Certification</th>
                        <th>Issuer</th>
                        <th>Issued</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($student->certifications as $cert)
                        <tr>
                            <td>{{ $cert->certification_name }}</td>
                            <td class="cell-sub">
                                {{ $cert->issuing_organization ?? '—' }}
                            </td>
                            <td class="cell-sub">
                                {{ $cert->issue_date?->format('j M Y') ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Aspirations -->
    @php
        $aspiration = $student->aspirations;
        $careerGoals = $aspiration && is_array($aspiration->career_goals)
            ? $aspiration->career_goals
            : [];
    @endphp

    @if(
        $aspiration
        && (
            count($careerGoals) > 0
            || $aspiration->vision_statement
            || $aspiration->long_term_goals
        )
    )
        <div class="card">
            <h3 class="card-heading">Career Aspirations</h3>

            @if(count($careerGoals) > 0)
                <div style="margin-bottom:12px;">
                    <div class="cell-sub" style="margin-bottom:6px;">Dream Career</div>
                    <p style="margin:0;font-size:13.5px;color:#374151;">
                        {{ $careerGoals[0] }}
                    </p>
                </div>
            @endif

            @if($aspiration->vision_statement)
                <div style="margin-bottom:12px;">
                    <div class="cell-sub" style="margin-bottom:6px;">Vision</div>
                    <p style="margin:0;font-size:13.5px;color:#374151;line-height:1.65;">
                        {{ $aspiration->vision_statement }}
                    </p>
                </div>
            @endif

            @if($aspiration->long_term_goals)
                <div>
                    <div class="cell-sub" style="margin-bottom:6px;">Long-term Goals</div>
                    <p style="margin:0;font-size:13.5px;color:#374151;line-height:1.65;">
                        {{ $aspiration->long_term_goals }}
                    </p>
                </div>
            @endif
        </div>
    @endif

    <!-- Milestones -->
    <div class="card">
        <h3 class="card-heading">
            Milestones ({{ $student->milestones->count() }})
        </h3>

        @if($student->milestones->isEmpty())
            <p class="empty-text">No milestones recorded.</p>
        @else
            <table class="admin-table">
                <tbody>
                    @foreach($student->milestones as $milestone)
                        <tr>
                            <td>
                                @if($milestone->is_completed)
                                    <i class="fas fa-check-circle" style="color:#2d8f5c;"></i>
                                @else
                                    <i class="far fa-circle" style="color:#d1d5db;"></i>
                                @endif

                                {{ $milestone->title }}
                            </td>
                            <td class="cell-sub" style="text-align:right;">
                                {{ ucfirst($milestone->category) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Career Recommendations -->
    <div class="card">
        <h3 class="card-heading">
            Career Recommendations
            ({{ $topRecommendations->count() }})
        </h3>

        @if($topRecommendations->isEmpty())
            <p class="empty-text">
                No recommendations generated yet.
            </p>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Career</th>
                        <th>Match</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topRecommendations as $rec)
                        <tr>
                            <td>{{ $rec->rank }}</td>
                            <td>
                                {{
                                    $rec->jobRole?->title
                                    ?? $rec->career?->job_title
                                    ?? '—'
                                }}
                            </td>
                            <td>{{ round($rec->match_score ?? 0) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection