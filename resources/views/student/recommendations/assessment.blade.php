@extends('layouts.app')

@section('title', 'Career Assessment')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
    .cpbn-assessment {
        --ink:#0d1a2b;
        --ink-dim:#5b6675;
        --paper:#faf8f2;
        --card:#ffffff;
        --line:#e7e2d4;
        --gold:#cf9a3d;
        --gold-bright:#e9b95a;
        --gold-wash:#fbf1de;
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

    .cpbn-assessment * {
        box-sizing:border-box;
    }

    .cpbn-assessment a {
        text-decoration:none;
        color:inherit;
    }

    .cpbn-assessment-wrap {
        max-width:1180px;
        margin-inline:auto;
    }

    .cpbn-assessment-head {
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:20px;
        margin-bottom:28px;
    }

    .cpbn-assessment-head h1 {
        font-family:var(--font-display);
        font-size:28px;
        font-weight:600;
        letter-spacing:-.01em;
        margin:0;
    }

    .cpbn-assessment-head p {
        color:var(--ink-dim);
        font-size:14.5px;
        margin-top:5px;
    }

    .cpbn-back {
        display:inline-flex;
        align-items:center;
        gap:7px;
        color:var(--ink-dim);
        font-size:13px;
        padding-top:7px;
    }

    .cpbn-back:hover {
        color:var(--ink);
    }

    .cpbn-back svg {
        width:14px;
        height:14px;
    }

    .cpbn-assessment-list {
        display:grid;
        gap:16px;
    }

    .cpbn-assessment-card {
        background:var(--card);
        border:1px solid var(--line);
        border-radius:6px;
        padding:22px 24px;
        transition:border-color .15s, box-shadow .15s;
    }

    .cpbn-assessment-card:hover {
        border-color:var(--gold);
        box-shadow:0 8px 24px -12px rgba(13,26,43,.12);
    }

    .cpbn-assessment-card-top {
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:20px;
    }

    .cpbn-rank {
        display:inline-block;
        font-family:var(--font-mono);
        font-size:12px;
        color:#8a6420;
        background:var(--gold-wash);
        padding:5px 10px;
        border-radius:100px;
        margin-bottom:11px;
    }

    .cpbn-career-title {
        font-family:var(--font-display);
        font-size:21px;
        font-weight:600;
        margin:0;
    }

    .cpbn-career-subsector {
        color:var(--ink-dim);
        font-size:13.5px;
        margin-top:5px;
    }

    .cpbn-match-score {
        text-align:right;
        flex-shrink:0;
    }

    .cpbn-match-number {
        font-family:var(--font-mono);
        font-size:23px;
        font-weight:500;
        color:var(--ink);
    }

    .cpbn-match-label {
        display:block;
        color:var(--ink-dim);
        font-size:12px;
        margin-top:2px;
    }

    .cpbn-card-footer {
        border-top:1px solid var(--line);
        margin-top:18px;
        padding-top:15px;
        display:flex;
        justify-content:flex-end;
    }

    .cpbn-analysis-btn {
        display:inline-flex;
        align-items:center;
        gap:7px;
        padding:9px 15px;
        border:1px solid var(--line);
        border-radius:5px;
        font-size:13px;
        font-weight:500;
        background:var(--card);
        transition:all .15s;
    }

    .cpbn-analysis-btn:hover {
        border-color:var(--gold);
        color:#8a6420;
    }

    .cpbn-analysis-btn svg {
        width:13px;
        height:13px;
    }

    .cpbn-empty {
        background:var(--card);
        border:1px solid var(--line);
        border-radius:6px;
        padding:50px 24px;
        text-align:center;
    }

    .cpbn-empty h2 {
        font-family:var(--font-display);
        font-size:19px;
        font-weight:600;
        margin-bottom:6px;
    }

    .cpbn-empty p {
        color:var(--ink-dim);
        font-size:13.5px;
        margin-bottom:18px;
    }

    .cpbn-profile-btn {
        display:inline-flex;
        align-items:center;
        padding:10px 17px;
        border-radius:5px;
        background:var(--gold);
        font-size:13.5px;
        font-weight:500;
    }

    .cpbn-profile-btn:hover {
        background:var(--gold-bright);
    }

    @media (max-width:640px) {
        .cpbn-assessment-head {
            flex-direction:column;
        }

        .cpbn-assessment-card-top {
            flex-direction:column;
        }

        .cpbn-match-score {
            text-align:left;
        }

        .cpbn-card-footer {
            justify-content:flex-start;
        }
    }
</style>

<div class="cpbn-assessment">
    <div class="cpbn-assessment-wrap">

        <div class="cpbn-assessment-head">
            <div>
                <h1>Career Assessment</h1>
                <p>Based on your profile, these are your top career matches.</p>
            </div>

            <a href="{{ route('student.dashboard') }}" class="cpbn-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5"/>
                    <path d="M12 19l-7-7 7-7"/>
                </svg>
                Back to Dashboard
            </a>
        </div>

        @if($recommendations->isNotEmpty())
            <div class="cpbn-assessment-list">
                @foreach($recommendations as $recommendation)
                    <div class="cpbn-assessment-card">
                        <div class="cpbn-assessment-card-top">
                            <div>
                                <span class="cpbn-rank">
                                    #{{ $recommendation->rank }}
                                </span>

                                <h2 class="cpbn-career-title">
                                    {{ $recommendation->career->job_title ?? 'Career' }}
                                </h2>

                                <p class="cpbn-career-subsector">
                                    {{ $recommendation->career->subsector ?? 'Sub-sector unavailable' }}
                                </p>
                            </div>

                            <div class="cpbn-match-score">
                                <span class="cpbn-match-number">
                                    {{ number_format($recommendation->match_score, 0) }}%
                                </span>
                                <span class="cpbn-match-label">
                                    Match Score
                                </span>
                            </div>
                        </div>

                        <div class="cpbn-card-footer">
                            <a
                                href="{{ route('student.recommendations.analysis', $recommendation->id) }}"
                                class="cpbn-analysis-btn"
                            >
                                View Career Analysis

                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 12h14"/>
                                    <path d="M13 6l6 6-6 6"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="cpbn-empty">
                <h2>No career assessment yet</h2>
                <p>
                    Complete your career profile to generate your career recommendations.
                </p>

                <a href="{{ route('student.profile') }}" class="cpbn-profile-btn">
                    Complete Profile
                </a>
            </div>
        @endif

    </div>
</div>

@endsection