@extends('admin.layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div>
    <div class="cpbn-head" style="margin-bottom:8px">
        <div>
            <h1>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 15l4-6 3 3 5-8"/></svg>
                Dashboard
            </h1>
            <p class="sub">Welcome back, {{ auth()->user()->name }}!</p>
        </div>
    </div>

    <!-- Data Status Message -->
    @if(!$stats['has_careers'] || !$stats['has_recommendations'])
        <div class="cpbn-alert cpbn-alert-info" style="margin-top:16px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
            <span>
                <strong>Database setup in progress:</strong>
                @if(!$stats['has_careers'])
                    BIICF careers data
                @endif
                @if(!$stats['has_careers'] && !$stats['has_recommendations'])
                    and
                @endif
                @if(!$stats['has_recommendations'])
                    career recommendations
                @endif
                are not yet available. This is expected while the database team is working on it.
            </span>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="cpbn-stats" style="margin-top:20px">
        <div class="cpbn-stat">
            <div class="cpbn-stat-top">
                <div>
                    <p class="cpbn-stat-label">Students</p>
                    <p class="cpbn-stat-num">{{ $stats['total_students'] }}</p>
                </div>
                <div class="cpbn-icon-badge ib-gold">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="3.5"/><path d="M2 21c0-3.9 3.1-7 7-7s7 3.1 7 7"/><path d="M16 4.2a3.5 3.5 0 0 1 0 6.8"/><path d="M22 21c0-3-2-5.5-4.5-6.6"/></svg>
                </div>
            </div>
        </div>
        <div class="cpbn-stat">
            <div class="cpbn-stat-top">
                <div>
                    <p class="cpbn-stat-label">Lecturers</p>
                    <p class="cpbn-stat-num">{{ $stats['total_lecturers'] }}</p>
                </div>
                <div class="cpbn-icon-badge ib-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 21h8M12 18v3"/></svg>
                </div>
            </div>
        </div>
        <div class="cpbn-stat">
            <div class="cpbn-stat-top">
                <div>
                    <p class="cpbn-stat-label">BIICF Careers</p>
                    <p class="cpbn-stat-num">{{ $stats['total_careers'] }}</p>
                </div>
                <div class="cpbn-icon-badge ib-purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </div>
            </div>
        </div>
        <div class="cpbn-stat">
            <div class="cpbn-stat-top">
                <div>
                    <p class="cpbn-stat-label">Recommendations</p>
                    <p class="cpbn-stat-num">{{ $stats['total_recommendations'] }}</p>
                </div>
                <div class="cpbn-icon-badge ib-rose">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4.5"/><circle cx="12" cy="12" r="1"/></svg>
                </div>
            </div>
        </div>
        <div class="cpbn-stat">
            <div class="cpbn-stat-top">
                <div>
                    <p class="cpbn-stat-label">Avg Readiness</p>
                    <p class="cpbn-stat-num">{{ $stats['avg_readiness'] }}%</p>
                </div>
                <div class="cpbn-icon-badge ib-ink">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Students by Programme -->
    <div class="cpbn-card">
        <h3 class="cpbn-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 15l4-6 3 3 5-8"/></svg>
            Students by Programme
        </h3>
        @if($studentsByProgramme->count() > 0)
            @foreach($studentsByProgramme as $prog)
                <div class="cpbn-prog-row">
                    <div class="cpbn-prog-top">
                        <span>{{ $prog->programme ?? 'Not Set' }}</span>
                        <span>{{ $prog->count }}</span>
                    </div>
                    <div class="cpbn-bar"><div class="cpbn-bar-fill fill-gold" style="width: {{ ($prog->count / $stats['total_students']) * 100 }}%"></div></div>
                </div>
            @endforeach
        @else
            <p class="cpbn-empty-note">No students registered yet.</p>
        @endif
    </div>

    <!-- Two Column Layout for Top Careers and Skill Gaps -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px">
        <!-- Top Career Matches -->
        <div class="cpbn-card">
            <h3 class="cpbn-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l2.9 6.6 7.1.7-5.4 4.7 1.7 7-6.3-3.8L5.7 21l1.7-7-5.4-4.7 7.1-.7z"/></svg>
                Top Career Matches
            </h3>
            @if($topCareers->count() > 0)
                @foreach($topCareers as $career)
                    <div class="cpbn-prog-row">
                        <div class="cpbn-prog-top">
                            <span>{{ $career->career->job_title ?? 'N/A' }}</span>
                            <span>{{ number_format($career->avg_score, 1) }}%</span>
                        </div>
                        <div class="cpbn-bar"><div class="cpbn-bar-fill fill-green" style="width: {{ $career->avg_score }}%"></div></div>
                    </div>
                @endforeach
            @else
                <p class="cpbn-empty-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
                    No career recommendations yet. This data will appear once students complete profiles.
                </p>
            @endif
        </div>

        <!-- Competency Gaps -->
        <div class="cpbn-card">
            <h3 class="cpbn-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h4l3 8 4-16 3 8h4"/></svg>
                Common Competency Gaps
            </h3>
            @if(count($skillGaps) > 0)
                @foreach($skillGaps as $skill => $count)
                    <div class="cpbn-prog-row">
                        <div class="cpbn-prog-top">
                            <span>{{ $skill }}</span>
                            <span>{{ $count }} students</span>
                        </div>
                        <div class="cpbn-bar"><div class="cpbn-bar-fill fill-rose" style="width: {{ min(($count / max($stats['total_students'], 1)) * 100, 100) }}%"></div></div>
                    </div>
                @endforeach
            @else
                <p class="cpbn-empty-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
                    No competency gaps identified yet.
                </p>
            @endif
        </div>
    </div>

    <!-- Recent Students -->
    <div class="cpbn-card" style="margin-top:20px">
        <div class="cpbn-head" style="margin-bottom:14px">
            <h3 class="cpbn-card-title" style="margin-bottom:0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="3.5"/><path d="M2 21c0-3.9 3.1-7 7-7s7 3.1 7 7"/></svg>
                Recent Students
            </h3>
            <a href="{{ route('admin.students.index') }}" style="font-size:12.5px;color:#8a6420">View All</a>
        </div>
        <div class="cpbn-table-wrap" style="border:none">
            <table class="cpbn-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Student ID</th>
                        <th>Programme</th>
                        <th>CGPA</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentStudents as $student)
                        <tr>
                            <td>
                                <a href="{{ route('admin.students.show', $student) }}" class="link">{{ $student->name }}</a>
                            </td>
                            <td>{{ $student->student_id ?? '-' }}</td>
                            <td>{{ $student->programme ?? '-' }}</td>
                            <td>{{ $student->cgpa ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="cpbn-empty-row">No students registered yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection