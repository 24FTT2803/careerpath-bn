@extends('admin.layouts.admin')

@section('title', 'Lecturer Dashboard')

@section('content')
<style>
    .lect-dash { padding: 4px 0 40px; }

    .lect-header {
        margin-bottom: 24px;
    }

    .lect-header h1 {
        font-family: var(--font-display);
        font-size: 26px;
        font-weight: 700;
        color: var(--primary);
    }

    .lect-header p {
        color: var(--text-muted);
        font-size: 13px;
        margin-top: 4px;
    }

    .lect-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    @media (max-width: 900px) {
        .lect-stats { grid-template-columns: repeat(2, 1fr); }
    }

    .lect-stat-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 18px 20px;
        box-shadow: var(--shadow);
    }

    .lect-stat-card .label {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .lect-stat-card .value {
        font-family: var(--font-display);
        font-size: 28px;
        font-weight: 700;
        color: var(--primary);
        margin-top: 4px;
    }

    .lect-stat-card.warning .value { color: var(--danger); }

    .lect-grid {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    @media (max-width: 1100px) {
        .lect-grid { grid-template-columns: 1fr; }
    }

    .lect-panel {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px;
        box-shadow: var(--shadow);
    }

    .lect-panel h3 {
        font-size: 15px;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .lect-panel h3 i { color: var(--accent); }

    .lect-empty {
        color: var(--text-muted);
        font-size: 13px;
        padding: 20px 0;
        text-align: center;
    }

    /* Programme breakdown bars */
    .prog-row {
        display: grid;
        grid-template-columns: 140px 1fr 34px;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        font-size: 13px;
    }

    .prog-row .name {
        color: var(--text);
        font-weight: 500;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .prog-row .bar-track {
        background: var(--bg);
        border-radius: 100px;
        height: 8px;
        overflow: hidden;
    }

    .prog-row .bar-fill {
        background: linear-gradient(90deg, var(--primary), var(--accent));
        height: 100%;
        border-radius: 100px;
    }

    .prog-row .count {
        text-align: right;
        color: var(--text-muted);
        font-weight: 600;
    }

    /* Skill gap bars */
    .gap-row {
        display: grid;
        grid-template-columns: 1fr 1fr 30px;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        font-size: 13px;
    }

    .gap-row .name {
        color: var(--text);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .gap-row .bar-track {
        background: var(--bg);
        border-radius: 100px;
        height: 8px;
        overflow: hidden;
    }

    .gap-row .bar-fill {
        background: var(--warning);
        height: 100%;
        border-radius: 100px;
    }

    .gap-row .count {
        text-align: right;
        color: var(--text-muted);
        font-weight: 600;
    }

    /* At-risk students table */
    .lect-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .lect-table th {
        text-align: left;
        color: var(--text-muted);
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 8px 10px;
        border-bottom: 1px solid var(--border);
    }

    .lect-table td {
        padding: 10px;
        border-bottom: 1px solid var(--border);
        color: var(--text);
    }

    .lect-table tr:last-child td { border-bottom: none; }

    .lect-table a {
        color: var(--primary);
        font-weight: 600;
        text-decoration: none;
    }

    .lect-table a:hover { text-decoration: underline; }

    .badge-risk {
        background: rgba(192, 57, 43, 0.1);
        color: var(--danger);
        padding: 3px 10px;
        border-radius: 100px;
        font-weight: 600;
        font-size: 12px;
    }

    .badge-new {
        background: var(--gold-wash);
        color: var(--accent-dark);
        padding: 3px 10px;
        border-radius: 100px;
        font-weight: 600;
        font-size: 11px;
    }
</style>

<div class="lect-dash">

    <div class="lect-header">
        <h1>Welcome back, {{ auth()->user()->name }}</h1>
        <p>{{ now()->format('l, F j, Y') }} · Cohort overview across all students</p>
    </div>

    <!-- Stat cards -->
    <div class="lect-stats">
        <div class="lect-stat-card">
            <div class="label">Total Students</div>
            <div class="value">{{ $totalStudents }}</div>
        </div>
        <div class="lect-stat-card">
            <div class="label">Avg. Readiness</div>
            <div class="value">{{ $avgReadiness }}%</div>
        </div>
        <div class="lect-stat-card">
            <div class="label">Profile Completion</div>
            <div class="value">{{ $completionRate }}%</div>
        </div>
        <div class="lect-stat-card warning">
            <div class="label">At-Risk Students</div>
            <div class="value">{{ $atRiskStudents->count() }}</div>
        </div>
    </div>

    <div class="lect-grid">

        <!-- At-risk students -->
        <div class="lect-panel">
            <h3><i class="fas fa-exclamation-triangle"></i> Students Needing Attention</h3>

            @if($atRiskStudents->isEmpty())
                <div class="lect-empty">No students currently below the 40% readiness threshold.</div>
            @else
                <table class="lect-table">
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
                                <td><span class="badge-risk">{{ $student->readiness_score }}%</span></td>
                                <td><a href="{{ route('admin.students.show', $student->id) }}">View →</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Students by programme -->
        <div class="lect-panel">
            <h3><i class="fas fa-layer-group"></i> Students by Programme</h3>

            @if($studentsByProgramme->isEmpty())
                <div class="lect-empty">No programme data yet.</div>
            @else
                @php $maxCount = $studentsByProgramme->max('count') ?: 1; @endphp
                @foreach($studentsByProgramme as $row)
                    <div class="prog-row">
                        <span class="name">{{ $row->programme ?? 'Not set' }}</span>
                        <span class="bar-track"><span class="bar-fill" style="width: {{ round(($row->count / $maxCount) * 100) }}%"></span></span>
                        <span class="count">{{ $row->count }}</span>
                    </div>
                @endforeach
            @endif
        </div>

    </div>

    <div class="lect-grid">

        <!-- Common competency gaps -->
        <div class="lect-panel">
            <h3><i class="fas fa-chart-bar"></i> Common Competency Gaps (Cohort-wide)</h3>

            @if(empty($skillGaps))
                <div class="lect-empty">No competency gap data available yet — students need career recommendations generated first.</div>
            @else
                @php $maxGap = max($skillGaps) ?: 1; @endphp
                @foreach($skillGaps as $skill => $count)
                    <div class="gap-row">
                        <span class="name">{{ $skill }}</span>
                        <span class="bar-track"><span class="bar-fill" style="width: {{ round(($count / $maxGap) * 100) }}%"></span></span>
                        <span class="count">{{ $count }}</span>
                    </div>
                @endforeach
            @endif
        </div>

        <!-- Recent milestone proof submissions -->
        <div class="lect-panel">
            <h3><i class="fas fa-file-upload"></i> Recent Milestone Submissions</h3>

            @if($recentSubmissions->isEmpty())
                <div class="lect-empty">No milestone proof submissions yet.</div>
            @else
                <table class="lect-table">
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
                                    <a href="{{ route('admin.milestones.proof', [$milestone->user_id, $milestone->id]) }}" target="_blank">View →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

    </div>

</div>
@endsection
