@extends('layouts.app')

@section('title', 'History')

@section('content')

<style>
    .history-page {
        max-width: 900px;
        margin: 0 auto;
        padding: 24px 0 48px;
    }

    .history-heading {
        font-size: 22px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }

    .history-subheading {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 28px;
    }

    .history-section {
        margin-bottom: 36px;
    }

    .history-section-title {
        font-size: 15px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5e7eb;
    }

    .history-card {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 14px 16px;
        margin-bottom: 10px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
    }

    .history-card-main {
        min-width: 0;
    }

    .history-card-title {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
    }

    .history-card-meta {
        font-size: 12px;
        color: #6b7280;
        margin-top: 3px;
    }

    .history-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .history-badge.current {
        background: #f0fdf4;
        color: #166534;
    }

    .history-badge.previous {
        background: #f3f4f6;
        color: #374151;
    }

    .history-badge.outdated {
        background: #fff7ed;
        color: #9a6700;
    }

    .history-actions {
        display: flex;
        flex-direction: column;
        gap: 6px;
        flex-shrink: 0;
    }

    .history-action {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 7px;
        border: 1px solid #d1d5db;
        background: #fff;
        color: #111827;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        text-align: center;
        white-space: nowrap;
    }

    .history-action:hover {
        background: #f9fafb;
    }

    .history-empty {
        padding: 20px;
        border: 1px dashed #d1d5db;
        border-radius: 10px;
        font-size: 13px;
        color: #6b7280;
        background: #fafafa;
    }

    @media (max-width: 640px) {
        .history-card {
            flex-direction: column;
        }

        .history-actions {
            flex-direction: row;
            width: 100%;
        }

        .history-action {
            flex: 1;
        }
    }
</style>

<div class="history-page">

    <h1 class="history-heading">History</h1>

    <p class="history-subheading">
        Everything CareerPath BN has generated for you, kept so
        you can look back at earlier results.
    </p>

    {{-- Recommendation generations --}}
    <div class="history-section">

        <div class="history-section-title">
            Career Recommendations
        </div>

        @if(! $recommendationHistoryAccess['allowed'])

            <div class="history-empty">
                {{
                    $recommendationHistoryAccess['message']
                        ?? 'Recommendation history is not available on your current plan.'
                }}
            </div>

        @elseif($generations->isEmpty())

            <div class="history-empty">
                You have not generated any career recommendations
                yet. Once you do, every set is kept here.
            </div>

        @else

            @foreach($generations as $generation)

                <div class="history-card">

                    <div class="history-card-main">

                        <div class="history-card-title">
                            Generation #{{ $generation->generation_number }}

                            <span
                                class="history-badge {{ $generation->status }}"
                            >
                                {{ $generation->status }}
                            </span>
                        </div>

                        <div class="history-card-meta">
                            {{ $generation->generated_at->timezone(config('app.business_timezone'))->format('j M Y') }}

                            &middot;

                            {{ $generation->recommendation_count }} {{ Str::plural('recommendation', $generation->recommendation_count) }}

                            @if($generation->isOutdated())
                                &middot;
                                your profile has changed since this was generated
                            @endif
                        </div>

                    </div>

                    <div class="history-actions">

                        @if($generation->isCurrent())
                            <a
                                href="{{ route('student.recommendations.index') }}"
                                class="history-action"
                            >
                                View
                            </a>
                        @endif

                        @if($reportAccess['allowed'])
                            <a
                                href="{{ route('student.recommendations.report', $generation) }}"
                                class="history-action"
                            >
                                Report
                            </a>
                        @endif

                    </div>

                </div>

            @endforeach

        @endif

    </div>

    {{-- Career Adviser --}}
    <div class="history-section">

        <div class="history-section-title">
            Career Adviser
        </div>

        @if(! $adviserHistoryAccess['allowed'])

            <div class="history-empty">
                {{
                    $adviserHistoryAccess['message']
                        ?? 'Career Adviser history is not available on your current plan.'
                }}
            </div>

        @elseif(! $conversation || $conversation->message_count === 0)

            <div class="history-empty">
                You have not asked the Career Adviser anything
                yet. Your conversation is kept here once you do.
            </div>

        @else

            <div class="history-card">

                <div class="history-card-main">

                    <div class="history-card-title">
                        Your conversation
                    </div>

                    <div class="history-card-meta">
                        {{ $conversation->message_count }} {{ Str::plural('message', $conversation->message_count) }}

                        @if($conversation->last_message_at)
                            &middot;
                            last active
                            {{ $conversation->last_message_at->diffForHumans() }}
                        @endif
                    </div>

                </div>

                <div class="history-actions">
                    <a
                        href="{{ route('student.career-adviser') }}"
                        class="history-action"
                    >
                        Open
                    </a>
                </div>

            </div>

        @endif

    </div>

</div>
@endsection
