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

    .cpbn-match {
        text-align:right;
        flex-shrink:0;
    }

    .cpbn-match strong {
        display:block;
        font-family:var(--font-mono);
        font-size:27px;
        font-weight:500;
    }

    .cpbn-match span {
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
        margin-bottom:9px;
    }

    .cpbn-gap-levels {
        display:flex;
        gap:10px;
        flex-wrap:wrap;
        font-size:12px;
    }

    .cpbn-current {
        background:var(--rose-wash);
        color:var(--rose);
        border-radius:100px;
        padding:5px 9px;
    }

    .cpbn-required {
        background:var(--gold-wash);
        color:#8a6420;
        border-radius:100px;
        padding:5px 9px;
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

        .cpbn-match {
            text-align:left;
        }
    }
</style>

<div class="cpbn-analysis">
    <div class="cpbn-analysis-wrap">

        <div class="cpbn-analysis-nav">
            <a
                href="{{ route('student.recommendations.assessment') }}"
                class="cpbn-back"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                        {{ $careerRecommendation->career->subsector ?? 'Sub-sector unavailable' }}
                    </p>
                </div>

                <div class="cpbn-match">
                    <strong>
                        {{ number_format($careerRecommendation->match_score, 0) }}%
                    </strong>

                    <span>Match Score</span>
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
                        @foreach($careerDetails['entry_requirements'] as $requirement)
                            <li>{{ $requirement }}</li>
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
                        @foreach($careerRecommendation->matched_skills as $skill)
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
                <h2>Skill Gaps</h2>

                @if(! empty($careerRecommendation->skill_gaps))
                    <div class="cpbn-gaps">
                        @foreach($careerRecommendation->skill_gaps as $gap)
                            <div class="cpbn-gap">
                                <div class="cpbn-gap-name">
                                    {{ $gap['skill_name'] ?? 'Competency' }}
                                </div>

                                <div class="cpbn-gap-levels">
                                    <span class="cpbn-current">
                                        Current:
                                        {{ ucfirst($gap['current_level'] ?? 'Not specified') }}
                                    </span>

                                    <span class="cpbn-required">
                                        Recommended:
                                        {{ ucfirst($gap['recommended_level'] ?? 'Not specified') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="cpbn-empty-text">
                        No skill gaps were identified.
                    </p>
                @endif
            </section>

            <section class="cpbn-section">
                <h2>Recommended Training</h2>

                @if(count($careerDetails['recommended_training']) > 0)
                    <ul class="cpbn-list">
                        @foreach($careerDetails['recommended_training'] as $training)
                            <li>{{ $training }}</li>
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

                @if(count($careerDetails['certifications']) > 0)
                    <ul class="cpbn-list">
                        @foreach($careerDetails['certifications'] as $certification)
                            <li>{{ $certification }}</li>
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
                        @foreach($careerRecommendation->development_plan as $step)
                            <li>{{ $step }}</li>
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