@extends('admin.layouts.admin')

@section('title', 'Student Profile')

@section('content')
<div>
    <div class="cpbn-head">
        <div>
            <h1>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
                Student Profile
            </h1>
            <p class="sub">View student details and career progress</p>
        </div>
        <a href="{{ route('admin.students.index') }}" class="cpbn-btn cpbn-btn-muted">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back
        </a>
    </div>

    <!-- Read-Only Notice -->
    <div class="cpbn-alert cpbn-alert-info">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
        @if($isAdmin)
            You have read-only access to student profiles.
        @else
            Lecturer access - View only. Student data cannot be modified.
        @endif
    </div>

    <!-- Database Status -->
    @if($topRecommendations->isEmpty())
        <div class="cpbn-alert cpbn-alert-info">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
            <span><strong>Database Setup in Progress:</strong> Career recommendations data is not yet available. This is expected while the database team is working on it.</span>
        </div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px">
        <!-- Student Info -->
        <div>
            <div class="cpbn-card">
                <div style="text-align:center;margin-bottom:16px">
                    <div class="cpbn-avatar-lg">{{ substr($student->name, 0, 1) }}</div>
                    <h2 style="font-family:var(--font-display);font-weight:600;font-size:19px;margin-top:12px">{{ $student->name }}</h2>
                    <p style="color:var(--ink-dim);font-size:13.5px">{{ $student->programme ?? 'Programme not set' }}</p>
                </div>
                <div>
                    <div class="cpbn-profile-row"><span>Student ID</span><span>{{ $student->student_id ?? '-' }}</span></div>
                    <div class="cpbn-profile-row"><span>Email</span><span>{{ $student->email }}</span></div>
                    <div class="cpbn-profile-row"><span>CGPA</span><span>{{ $student->cgpa ?? '-' }}</span></div>
                    <div class="cpbn-profile-row"><span>Readiness Score</span><span style="color:var(--green)">{{ $readinessScore }}%</span></div>
                </div>
            </div>

            <!-- Skills -->
            <div class="cpbn-card">
                <h3 class="cpbn-card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    Skills &amp; Competencies
                </h3>
                @if($student->competencies->count() > 0)
                    <div>
                        @foreach($student->competencies as $skill)
                            <span class="cpbn-tag pill-gold">
                                {{ $skill->skill_name }}
                                <small style="opacity:.75">({{ $skill->proficiency_level }})</small>
                            </span>
                        @endforeach
                    </div>
                @else
                    <p style="color:var(--ink-dim);font-size:13.5px">No skills recorded.</p>
                @endif
            </div>
        </div>

        <!-- Recommendations & Gaps -->
        <div>
            <!-- Career Recommendations -->
            <div class="cpbn-card">
                <h3 class="cpbn-card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l2.9 6.6 7.1.7-5.4 4.7 1.7 7-6.3-3.8L5.7 21l1.7-7-5.4-4.7 7.1-.7z"/></svg>
                    Career Recommendations
                </h3>
                @if($topRecommendations->count() > 0)
                    @foreach($topRecommendations as $rec)
                        <div style="border:1px solid var(--line);border-radius:6px;padding:16px;margin-bottom:12px">
                            <div style="display:flex;justify-content:space-between;align-items:center">
                                <div>
                                    <h4 style="font-weight:600;font-size:15px">{{ $rec->career->job_title ?? 'N/A' }}</h4>
                                    <p style="font-size:13px;color:var(--ink-dim)">{{ $rec->career->subsector ?? '' }}</p>
                                </div>
                                <span class="cpbn-pill pill-green">{{ $rec->match_score }}% Match</span>
                            </div>
                            <div style="margin-top:10px;font-size:13px">
                                <span style="color:var(--ink-dim)">Matched Skills:</span>
                                @php
                                    $matched = is_array($rec->matched_skills) ? $rec->matched_skills : [];
                                @endphp
                                @if(count($matched) > 0)
                                    @foreach($matched as $skill)
                                        <span class="cpbn-pill pill-green" style="margin-left:4px">{{ $skill }}</span>
                                    @endforeach
                                @else
                                    <span style="color:var(--ink-dim)">None</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="cpbn-empty-note">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
                        No career recommendations yet. This will appear once the database team adds the data.
                    </p>
                @endif
            </div>

            <!-- Competency Gaps -->
            <div class="cpbn-card">
                <h3 class="cpbn-card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01"/><circle cx="12" cy="12" r="9"/></svg>
                    Competency Gaps
                </h3>
                @if(count($skillGaps) > 0)
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                        @foreach($skillGaps as $gap)
                            <div style="background:var(--rose-wash);border:1px solid rgba(198,91,78,0.25);border-radius:6px;padding:11px 14px">
                                <span style="color:#8f3a30;font-weight:500;font-size:13.5px">{{ $gap }}</span>
                                <div style="font-size:11.5px;color:#a85a4f;margin-top:2px">Needs development</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="cpbn-empty-note">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
                        No competency gaps identified yet.
                    </p>
                @endif
            </div>

            <!-- Milestones -->
            <div class="cpbn-card">
                <h3 class="cpbn-card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22V4"/><path d="M4 4h14l-3 4 3 4H4"/></svg>
                    Milestones
                </h3>
                @if($student->milestones->count() > 0)
                    @foreach($student->milestones as $milestone)
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-top:1px solid var(--line)">
                            <div>
                                <span style="font-weight:500;font-size:13.5px">{{ $milestone->title }}</span>
                                <span class="cpbn-pill pill-neutral" style="margin-left:8px">{{ $milestone->category }}</span>
                            </div>
                            <div>
                                @if($milestone->is_completed)
                                    <span style="color:var(--green);font-size:13px;display:flex;align-items:center;gap:5px">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path d="M20 6 9 17l-5-5"/></svg>
                                        Completed
                                    </span>
                                @else
                                    <span style="color:#8a6420;font-size:13px;display:flex;align-items:center;gap:5px">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                                        In Progress
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="cpbn-empty-note">No milestones recorded.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection