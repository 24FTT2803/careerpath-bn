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