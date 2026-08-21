@extends('layouts.app')

@section('title', 'Career Analysis')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
    .cpbn-analysis {
        --ink:#0d1a2b;
        --ink-dim:#5b6675;
        --paper:#faf8f2;
        --card:#ffffff;
        --line:#e7e2d4;
        --gold:#cf9a3d;
        --gold-wash:#fbf1de;
        --green:#4c8a68;
        --green-wash:#e9f3ee;
        --rose:#c65b4e;
        --rose-wash:#fbeceb;
        --blue:#496f95;
        --blue-wash:#edf3f8;
        --font-display:'Fraunces', Georgia, serif;
        --font-body:'IBM Plex Sans', ui-sans-serif, system-ui, sans-serif;
        --font-mono:'IBM Plex Mono', ui-monospace, monospace;

        background:var(--paper);
        color:var(--ink);
        font-family:var(--font-body);
        margin:-24px -16px 0;
        padding:32px 20px 56px;
        min-height:70vh;
    }

    .cpbn-analysis * {
        box-sizing:border-box;
    }

    .cpbn-analysis a {
        text-decoration:none;
        color:inherit;
    }

    .cpbn-analysis-wrap {
        max-width:1180px;
        margin-inline:auto;
    }

    .cpbn-analysis-nav {
        display:flex;
        justify-content:space-between;
        align-items:center;
        flex-wrap:wrap;
        gap:12px;
        margin-bottom:24px;
    }

    .cpbn-back {
        display:inline-flex;
        align-items:center;
        gap:7px;
        color:var(--ink-dim);
        font-size:13px;
    }

    .cpbn-back:hover {
        color:var(--ink);
    }

    .cpbn-back svg {
        width:14px;
        height:14px;
    }

    .cpbn-rank {
        font-family:var(--font-mono);
        font-size:12px;
        color:#8a6420;
        background:var(--gold-wash);
        padding:6px 11px;
        border-radius:100px;
    }

    .cpbn-hero {
        background:var(--card);
        border:1px solid var(--line);
        border-radius:6px;
        padding:26px;
        margin-bottom:20px;
    }

    .cpbn-hero-top {
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:24px;
    }

    .cpbn-hero h1 {
        font-family:var(--font-display);
        font-size:28px;
        font-weight:600;
        letter-spacing:-.01em;
        margin:0;
    }

    .cpbn-subsector {
        color:var(--ink-dim);
        font-size:14px;
        margin-top:6px;
    }

    .cpbn-metrics {
        display:flex;
        align-items:flex-start;
        gap:28px;
        flex-shrink:0;
    }

    .cpbn-metric {
        text-align:right;
    }

    .cpbn-metric strong {
        display:block;
        font-family:var(--font-mono);
        font-size:27px;
        font-weight:500;
    }

    .cpbn-metric span {
        display:block;
        color:var(--ink-dim);
        font-size:12px;
        margin-top:2px;
    }

    .cpbn-grid {
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:20px;
    }

    .cpbn-section {
        background:var(--card);
        border:1px solid var(--line);
        border-radius:6px;
        padding:24px;
    }

    .cpbn-section-full {
        grid-column:1 / -1;
    }

    .cpbn-section h2 {
        font-family:var(--font-display);
        font-size:18px;
        font-weight:600;
        margin:0 0 16px;
    }

    .cpbn-section h3 {
        font-size:13px;
        font-weight:600;
        margin:18px 0 8px;
    }

    .cpbn-section h3:first-of-type {
        margin-top:0;
    }

    .cpbn-copy {
        color:var(--ink-dim);
        font-size:14px;
        line-height:1.7;
    }

    .cpbn-list {
        margin:0;
        padding:0;
        list-style:none;
    }

    .cpbn-list li {
        position:relative;
        padding:9px 0 9px 18px;
        color:var(--ink-dim);
        font-size:13.5px;
        border-bottom:1px solid var(--line);
    }

    .cpbn-list li:last-child {
        border-bottom:none;
    }

    .cpbn-list li::before {
        content:"";
        position:absolute;
        left:0;
        top:15px;
        width:6px;
        height:6px;
        border-radius:50%;
        background:var(--gold);
    }

    .cpbn-skills {
        display:flex;
        flex-wrap:wrap;
        gap:9px;
    }

    .cpbn-skill {
        display:inline-flex;
        padding:7px 11px;
        border-radius:100px;
        background:var(--green-wash);
        color:var(--green);
        font-size:12.5px;
        font-weight:500;
    }

    .cpbn-gap-groups {
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:20px;
    }

    .cpbn-gap-group {
        min-width:0;
    }

    .cpbn-gap-group-title {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        margin-bottom:12px;
    }

    .cpbn-gap-group-title h3 {
        margin:0;
        font-size:13px;
        font-weight:600;
    }

    .cpbn-gap-count {
        font-family:var(--font-mono);
        font-size:11px;
        color:var(--ink-dim);
        background:#f5f3ed;
        border-radius:100px;
        padding:4px 8px;
    }

    .cpbn-gaps {
        display:grid;
        gap:12px;
    }

    .cpbn-gap {
        border:1px solid var(--line);
        border-radius:5px;
        padding:15px;
    }

    .cpbn-gap-name {
        font-weight:600;
        font-size:13.5px;
        margin-bottom:12px;
    }

    .cpbn-gap-values {
        display:grid;
        grid-template-columns:1fr 1fr 1fr;
        gap:9px;
    }

    .cpbn-gap-value {
        border-radius:5px;
        padding:10px;
        min-width:0;
    }

    .cpbn-gap-value-current {
        background:var(--rose-wash);
    }

    .cpbn-gap-value-required {
        background:var(--gold-wash);
    }

    .cpbn-gap-value-difference {
        background:var(--green-wash);
    }

    .cpbn-gap-label {
        display:block;
        color:var(--ink-dim);
        font-size:10.5px;
        margin-bottom:4px;
    }

    .cpbn-gap-main {
        display:block;
        font-family:var(--font-mono);
        font-size:12px;
        font-weight:500;
        line-height:1.4;
    }

    .cpbn-gap-sub {
        display:block;
        color:var(--ink-dim);
        font-size:11px;
        line-height:1.4;
        margin-top:3px;
    }

    .cpbn-gap-note {
        margin-top:16px;
        padding:11px 13px;
        background:var(--blue-wash);
        border-radius:5px;
        color:var(--blue);
        font-size:11.5px;
        line-height:1.6;
    }

    .cpbn-empty-text {
        color:var(--ink-dim);
        font-size:13.5px;
    }

    .cpbn-plan {
        counter-reset:plan;
        list-style:none;
        padding:0;
        margin:0;
    }

    .cpbn-plan li {
        counter-increment:plan;
        display:flex;
        gap:13px;
        padding:12px 0;
        border-bottom:1px solid var(--line);
        color:var(--ink-dim);
        font-size:13.5px;
        line-height:1.5;
    }

    .cpbn-plan li:last-child {
        border-bottom:none;
    }

    .cpbn-plan li::before {
        content:counter(plan);
        display:flex;
        align-items:center;
        justify-content:center;
        width:25px;
        height:25px;
        flex:0 0 25px;
        border-radius:50%;
        background:var(--gold-wash);
        color:#8a6420;
        font-family:var(--font-mono);
        font-size:11px;
        font-weight:500;
    }

    @media (max-width:900px) {
        .cpbn-gap-groups {
            grid-template-columns:1fr;
        }
    }

    @media (max-width:760px) {
        .cpbn-grid {
            grid-template-columns:1fr;
        }

        .cpbn-section-full {
            grid-column:auto;
        }

        .cpbn-hero-top {
            flex-direction:column;
        }

        .cpbn-metrics {
            width:100%;
        }

        .cpbn-metric {
            text-align:left;
        }
    }

    @media (max-width:520px) {
        .cpbn-metrics {
            flex-direction:column;
            gap:14px;
        }

        .cpbn-gap-values {
            grid-template-columns:1fr;
        }
    }
</style>

@php
    $skillGaps = collect(
        $careerRecommendation->skill_gaps ?? []
    );

    $technicalGaps = $skillGaps
        ->filter(
            fn ($gap) =>
                ($gap['skill_type'] ?? null) === 'technical'
        )
        ->values();

    $softGaps = $skillGaps
        ->filter(
            fn ($gap) =>
                ($gap['skill_type'] ?? null) === 'soft'
        )
        ->values();

    $uncategorisedGaps = $skillGaps
        ->filter(
            fn ($gap) =>
                ! in_array(
                    $gap['skill_type'] ?? null,
                    ['technical', 'soft'],
                    true
                )
        )
        ->values();
@endphp

<div class="cpbn-analysis">
    <div class="cpbn-analysis-wrap">

        <div class="cpbn-analysis-nav">
            <a
                href="{{ route('student.recommendations.assessment') }}"
                class="cpbn-back"
            >
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M19 12H5"/>
                    <path d="M12 19l-7-7 7-7"/>
                </svg>

                Back to Career Assessment
            </a>

            <span class="cpbn-rank">
                #{{ $careerRecommendation->rank }} Career Match
            </span>
        </div>

        <div class="cpbn-hero">
            <div class="cpbn-hero-top">
                <div>
                    <h1>
                        {{ $careerRecommendation->career->job_title ?? 'Career' }}
                    </h1>

                    <p class="cpbn-subsector">
                        {{ $careerRecommendation->career->subsector
                            ?? 'Sub-sector unavailable' }}
                    </p>
                </div>

                <div class="cpbn-metrics">
                    <div class="cpbn-metric">
                        <strong>
                            {{ number_format(
                                $careerRecommendation->match_score,
                                0
                            ) }}%
                        </strong>

                        <span>Match Score</span>
                    </div>

                    <div class="cpbn-metric">
                        <strong>
                            {{ number_format(
                                $careerRecommendation->career_readiness_score,
                                0
                            ) }}%
                        </strong>

                        <span>Career Readiness</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="cpbn-grid">

            <section class="cpbn-section">
                <h2>Career Overview</h2>

                <h3>Job Description</h3>

                <p class="cpbn-copy">
                    {{ $careerRecommendation->career->job_description
                        ?? 'Job description is currently unavailable.' }}
                </p>

                <h3>Entry Requirements</h3>

                @if(count($careerDetails['entry_requirements']) > 0)
                    <ul class="cpbn-list">
                        @foreach(
                            $careerDetails['entry_requirements']
                            as $requirement
                        )
                            <li>
                                {{ $requirement }}
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="cpbn-empty-text">
                        Entry requirements are currently unavailable.
                    </p>
                @endif
            </section>

            <section class="cpbn-section">
                <h2>Why This Career Matches You</h2>

                <p class="cpbn-copy">
                    {{ $careerRecommendation->explanation
                        ?? 'A detailed match explanation is currently unavailable.' }}
                </p>
            </section>

            <section class="cpbn-section">
                <h2>Matching Competencies</h2>

                @if(! empty($careerRecommendation->matched_skills))
                    <div class="cpbn-skills">
                        @foreach(
                            $careerRecommendation->matched_skills
                            as $skill
                        )
                            <span class="cpbn-skill">
                                {{ $skill }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="cpbn-empty-text">
                        No matching competencies were provided.
                    </p>
                @endif
            </section>

            <section class="cpbn-section">
                <h2>Career Readiness</h2>

                <p class="cpbn-copy">
                    Your career readiness score estimates how closely your
                    current competencies align with the requirements of this
                    career.
                </p>

                <p
                    class="cpbn-copy"
                    style="margin-bottom:0;"
                >
                    Current readiness:
                    <strong>
                        {{ number_format(
                            $careerRecommendation->career_readiness_score,
                            0
                        ) }}%
                    </strong>
                </p>
            </section>

            <section class="cpbn-section cpbn-section-full">
                <h2>Skill Gaps</h2>

                @if($skillGaps->isNotEmpty())

                    <div class="cpbn-gap-groups">

                        <div class="cpbn-gap-group">
                            <div class="cpbn-gap-group-title">
                                <h3>
                                    Technical Competencies
                                </h3>

                                <span class="cpbn-gap-count">
                                    {{ $technicalGaps->count() }}
                                </span>
                            </div>

                            @if($technicalGaps->isNotEmpty())
                                <div class="cpbn-gaps">
                                    @foreach($technicalGaps as $gap)
                                        <div class="cpbn-gap">
                                            <div class="cpbn-gap-name">
                                                {{ $gap['skill_name']
                                                    ?? 'Competency' }}
                                            </div>

                                            <div class="cpbn-gap-values">

                                                <div
                                                    class="cpbn-gap-value
                                                    cpbn-gap-value-current"
                                                >
                                                    <span class="cpbn-gap-label">
                                                        Current
                                                    </span>

                                                    <span class="cpbn-gap-main">
                                                        @if(
                                                            isset(
                                                                $gap[
                                                                    'current_level_value'
                                                                ]
                                                            )
                                                        )
                                                            Level
                                                            {{
                                                                $gap[
                                                                    'current_level_value'
                                                                ]
                                                            }}
                                                        @else
                                                            Current Level
                                                        @endif
                                                    </span>

                                                    <span class="cpbn-gap-sub">
                                                        {{
                                                            $gap[
                                                                'current_level'
                                                            ]
                                                            ?? 'Not specified'
                                                        }}
                                                    </span>
                                                </div>

                                                <div
                                                    class="cpbn-gap-value
                                                    cpbn-gap-value-required"
                                                >
                                                    <span class="cpbn-gap-label">
                                                        Required
                                                    </span>

                                                    <span class="cpbn-gap-main">
                                                        @if(
                                                            isset(
                                                                $gap[
                                                                    'required_level'
                                                                ]
                                                            )
                                                        )
                                                            Level
                                                            {{
                                                                $gap[
                                                                    'required_level'
                                                                ]
                                                            }}
                                                        @else
                                                            Required Level
                                                        @endif
                                                    </span>

                                                    <span class="cpbn-gap-sub">
                                                        {{
                                                            $gap[
                                                                'required_label'
                                                            ]
                                                            ?? $gap[
                                                                'recommended_level'
                                                            ]
                                                            ?? 'Not specified'
                                                        }}
                                                    </span>
                                                </div>

                                                <div
                                                    class="cpbn-gap-value
                                                    cpbn-gap-value-difference"
                                                >
                                                    <span class="cpbn-gap-label">
                                                        Gap
                                                    </span>

                                                    <span class="cpbn-gap-main">
                                                        @if(
                                                            isset(
                                                                $gap['gap']
                                                            )
                                                        )
                                                            {{ $gap['gap'] }}
                                                            {{
                                                                $gap['gap'] === 1
                                                                    ? 'level'
                                                                    : 'levels'
                                                            }}
                                                        @else
                                                            Not calculated
                                                        @endif
                                                    </span>

                                                    <span class="cpbn-gap-sub">
                                                        To improve
                                                    </span>
                                                </div>

                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="cpbn-empty-text">
                                    No technical competency gaps were identified.
                                </p>
                            @endif
                        </div>

                        <div class="cpbn-gap-group">
                            <div class="cpbn-gap-group-title">
                                <h3>
                                    Soft Competencies
                                </h3>

                                <span class="cpbn-gap-count">
                                    {{ $softGaps->count() }}
                                </span>
                            </div>

                            @if($softGaps->isNotEmpty())
                                <div class="cpbn-gaps">
                                    @foreach($softGaps as $gap)
                                        <div class="cpbn-gap">
                                            <div class="cpbn-gap-name">
                                                {{ $gap['skill_name']
                                                    ?? 'Competency' }}
                                            </div>

                                            <div class="cpbn-gap-values">

                                                <div
                                                    class="cpbn-gap-value
                                                    cpbn-gap-value-current"
                                                >
                                                    <span class="cpbn-gap-label">
                                                        Current
                                                    </span>

                                                    <span class="cpbn-gap-main">
                                                        @if(
                                                            isset(
                                                                $gap[
                                                                    'current_level_value'
                                                                ]
                                                            )
                                                        )
                                                            Level
                                                            {{
                                                                $gap[
                                                                    'current_level_value'
                                                                ]
                                                            }}
                                                        @else
                                                            Current Level
                                                        @endif
                                                    </span>

                                                    <span class="cpbn-gap-sub">
                                                        {{
                                                            $gap[
                                                                'current_level'
                                                            ]
                                                            ?? 'Not specified'
                                                        }}
                                                    </span>
                                                </div>

                                                <div
                                                    class="cpbn-gap-value
                                                    cpbn-gap-value-required"
                                                >
                                                    <span class="cpbn-gap-label">
                                                        Required
                                                    </span>

                                                    <span class="cpbn-gap-main">
                                                        @if(
                                                            isset(
                                                                $gap[
                                                                    'required_level'
                                                                ]
                                                            )
                                                        )
                                                            Level
                                                            {{
                                                                $gap[
                                                                    'required_level'
                                                                ]
                                                            }}
                                                        @else
                                                            Required Level
                                                        @endif
                                                    </span>

                                                    <span class="cpbn-gap-sub">
                                                        {{
                                                            $gap[
                                                                'required_label'
                                                            ]
                                                            ?? $gap[
                                                                'recommended_level'
                                                            ]
                                                            ?? 'Not specified'
                                                        }}
                                                    </span>
                                                </div>

                                                <div
                                                    class="cpbn-gap-value
                                                    cpbn-gap-value-difference"
                                                >
                                                    <span class="cpbn-gap-label">
                                                        Gap
                                                    </span>

                                                    <span class="cpbn-gap-main">
                                                        @if(
                                                            isset(
                                                                $gap['gap']
                                                            )
                                                        )
                                                            {{ $gap['gap'] }}
                                                            {{
                                                                $gap['gap'] === 1
                                                                    ? 'level'
                                                                    : 'levels'
                                                            }}
                                                        @else
                                                            Not calculated
                                                        @endif
                                                    </span>

                                                    <span class="cpbn-gap-sub">
                                                        To improve
                                                    </span>
                                                </div>

                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="cpbn-empty-text">
                                    No soft competency gaps were identified.
                                </p>
                            @endif
                        </div>

                    </div>

                    @if($uncategorisedGaps->isNotEmpty())
                        <div style="margin-top:20px;">
                            <div class="cpbn-gap-group-title">
                                <h3>
                                    Other Competency Gaps
                                </h3>

                                <span class="cpbn-gap-count">
                                    {{ $uncategorisedGaps->count() }}
                                </span>
                            </div>

                            <div class="cpbn-gaps">
                                @foreach($uncategorisedGaps as $gap)
                                    <div class="cpbn-gap">
                                        <div class="cpbn-gap-name">
                                            {{ $gap['skill_name']
                                                ?? 'Competency' }}
                                        </div>

                                        <div class="cpbn-gap-values">

                                            <div
                                                class="cpbn-gap-value
                                                cpbn-gap-value-current"
                                            >
                                                <span class="cpbn-gap-label">
                                                    Current
                                                </span>

                                                <span class="cpbn-gap-main">
                                                    {{
                                                        $gap[
                                                            'current_level'
                                                        ]
                                                        ?? 'Not specified'
                                                    }}
                                                </span>
                                            </div>

                                            <div
                                                class="cpbn-gap-value
                                                cpbn-gap-value-required"
                                            >
                                                <span class="cpbn-gap-label">
                                                    Required
                                                </span>

                                                <span class="cpbn-gap-main">
                                                    {{
                                                        $gap[
                                                            'required_label'
                                                        ]
                                                        ?? $gap[
                                                            'recommended_level'
                                                        ]
                                                        ?? 'Not specified'
                                                    }}
                                                </span>
                                            </div>

                                            <div
                                                class="cpbn-gap-value
                                                cpbn-gap-value-difference"
                                            >
                                                <span class="cpbn-gap-label">
                                                    Gap
                                                </span>

                                                <span class="cpbn-gap-main">
                                                    @if(
                                                        isset(
                                                            $gap['gap']
                                                        )
                                                    )
                                                        {{ $gap['gap'] }}
                                                        {{
                                                            $gap['gap'] === 1
                                                                ? 'level'
                                                                : 'levels'
                                                        }}
                                                    @else
                                                        Not calculated
                                                    @endif
                                                </span>
                                            </div>

                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="cpbn-gap-note">
                        Competency levels currently use temporary mock values
                        while the Career AI and authoritative career
                        requirement data are still being integrated.
                    </div>

                @else
                    <p class="cpbn-empty-text">
                        No skill gaps were identified.
                    </p>
                @endif
            </section>

            <section class="cpbn-section">
                <h2>Recommended Training</h2>

                @if(
                    count(
                        $careerDetails['recommended_training']
                    ) > 0
                )
                    <ul class="cpbn-list">
                        @foreach(
                            $careerDetails['recommended_training']
                            as $training
                        )
                            <li>
                                {{ $training }}
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="cpbn-empty-text">
                        No recommended training is currently available.
                    </p>
                @endif
            </section>

            <section class="cpbn-section">
                <h2>Recommended Certifications</h2>

                @if(
                    count(
                        $careerDetails['certifications']
                    ) > 0
                )
                    <ul class="cpbn-list">
                        @foreach(
                            $careerDetails['certifications']
                            as $certification
                        )
                            <li>
                                {{ $certification }}
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="cpbn-empty-text">
                        No certification recommendations are currently available.
                    </p>
                @endif
            </section>

            <section class="cpbn-section cpbn-section-full">
                <h2>Development Plan</h2>

                @if(! empty($careerRecommendation->development_plan))
                    <ol class="cpbn-plan">
                        @foreach(
                            $careerRecommendation->development_plan
                            as $step
                        )
                            <li>
                                {{ $step }}
                            </li>
                        @endforeach
                    </ol>
                @else
                    <p class="cpbn-empty-text">
                        A development plan is currently unavailable.
                    </p>
                @endif
            </section>

        </div>
    </div>
</div>

@endsection