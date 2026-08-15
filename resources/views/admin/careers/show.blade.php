@extends('admin.layouts.admin')

@section('title', 'Career Details')

@section('content')
<div>
    <div class="cpbn-head">
        <div>
            <h1>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 12h18"/></svg>
                Career Details
            </h1>
            <p class="sub">{{ $career->job_title }}</p>
        </div>
        <a href="{{ route('admin.careers.index') }}" class="cpbn-btn cpbn-btn-muted">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back
        </a>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
        <!-- Career Info -->
        <div class="cpbn-card">
            <h3 class="cpbn-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                Job Information
            </h3>
            <div class="cpbn-profile-row"><span>Job Title</span><span>{{ $career->job_title }}</span></div>
            <div class="cpbn-profile-row"><span>Subsector</span><span>{{ $career->subsector }}</span></div>
            <div class="cpbn-profile-row"><span>Demand Level</span><span>{{ $career->demand_level ?? 'Medium' }}</span></div>
            <div style="padding-top:14px">
                <span style="color:var(--ink-dim);font-size:13px">Description</span>
                <p style="margin-top:6px;font-size:14px">{{ $career->job_description ?? 'No description available.' }}</p>
            </div>
        </div>

        <!-- Skills -->
        <div class="cpbn-card">
            <h3 class="cpbn-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                Required Skills
            </h3>

            @php
                $technicalSkills = is_array($career->technical_skills) ? $career->technical_skills : json_decode($career->technical_skills ?? '[]', true);
                $softSkills = is_array($career->soft_skills) ? $career->soft_skills : json_decode($career->soft_skills ?? '[]', true);
            @endphp

            <div style="margin-bottom:16px">
                <h4 style="font-size:12.5px;font-weight:600;color:var(--ink-dim);margin-bottom:8px">Technical Skills</h4>
                <div>
                    @forelse($technicalSkills as $skill)
                        <span class="cpbn-tag pill-gold">{{ $skill }}</span>
                    @empty
                        <span style="color:var(--ink-dim);font-size:13px">No technical skills listed</span>
                    @endforelse
                </div>
            </div>

            <div>
                <h4 style="font-size:12.5px;font-weight:600;color:var(--ink-dim);margin-bottom:8px">Soft Skills</h4>
                <div>
                    @forelse($softSkills as $skill)
                        <span class="cpbn-tag pill-green">{{ $skill }}</span>
                    @empty
                        <span style="color:var(--ink-dim);font-size:13px">No soft skills listed</span>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Training & Certifications -->
        <div class="cpbn-card" style="grid-column:1 / -1">
            <h3 class="cpbn-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="9" r="6"/><path d="M9 14.5 7 22l5-3 5 3-2-7.5"/></svg>
                Training &amp; Certifications
            </h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
                <div>
                    <h4 style="font-size:12.5px;font-weight:600;color:var(--ink-dim);margin-bottom:8px">Recommended Training</h4>
                    @php
                        $training = is_array($career->recommended_training) ? $career->recommended_training : json_decode($career->recommended_training ?? '[]', true);
                    @endphp
                    <ul style="list-style:disc;padding-left:18px;font-size:13.5px">
                        @forelse($training as $item)
                            <li style="margin-bottom:4px">{{ $item }}</li>
                        @empty
                            <li style="color:var(--ink-dim);list-style:none;margin-left:-18px">No training listed</li>
                        @endforelse
                    </ul>
                </div>
                <div>
                    <h4 style="font-size:12.5px;font-weight:600;color:var(--ink-dim);margin-bottom:8px">Recommended Certifications</h4>
                    @php
                        $certs = is_array($career->certifications) ? $career->certifications : json_decode($career->certifications ?? '[]', true);
                    @endphp
                    <ul style="list-style:disc;padding-left:18px;font-size:13.5px">
                        @forelse($certs as $cert)
                            <li style="margin-bottom:4px">{{ $cert }}</li>
                        @empty
                            <li style="color:var(--ink-dim);list-style:none;margin-left:-18px">No certifications listed</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection