@extends('layouts.app')

@section('title', 'Career Recommendations')

@section('content')

<style>
    .recommendations-page {
        --rec-primary: #1a3a5c;
        --rec-primary-light: #2a5a8c;
        --rec-accent: #c9a84c;
        --rec-text: #1a1a2e;
        --rec-muted: #6b7280;
        --rec-border: #e5e7eb;
        --rec-bg: #f4f6f9;
        --rec-card: #ffffff;

        padding: 28px 0 48px;
    }

    .recommendations-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 22px;
    }

    .recommendations-header h1 {
        margin: 0;
        color: var(--rec-primary);
        font-family: 'Playfair Display', serif;
        font-size: 31px;
        line-height: 1.2;
    }

    .recommendations-header p {
        margin: 7px 0 0;
        max-width: 680px;
        color: var(--rec-muted);
        font-size: 14px;
        line-height: 1.6;
    }

    .generation-panel {
        display: grid;
        grid-template-columns:
            minmax(0, 1fr)
            auto;
        align-items: center;
        gap: 22px;
        padding: 20px;
        margin-bottom: 22px;
        border: 1px solid var(--rec-border);
        border-radius: 14px;
        background: var(--rec-card);
        box-shadow:
            0 4px 24px
            rgba(26, 58, 92, 0.07);
    }

    .generation-panel h2 {
        margin: 0 0 6px;
        color: var(--rec-primary);
        font-size: 16px;
    }

    .generation-panel p {
        margin: 0;
        color: var(--rec-muted);
        font-size: 12px;
        line-height: 1.55;
    }

    .quota-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 10px;
        padding: 6px 9px;
        border-radius: 7px;
        background: #f3f5f7;
        color: var(--rec-muted);
        font-size: 11px;
        font-weight: 600;
    }

    .quota-status.blocked {
        background: #fff7ed;
        color: #9a6700;
    }

    .generate-button {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 18px;
        border: none;
        border-radius: 9px;
        background: var(--rec-primary);
        color: white;
        font-family: inherit;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
    }

    .generate-button:hover {
        background: var(--rec-primary-light);
    }

    .generate-button:disabled {
        background: #d6dbe1;
        color: #7c8794;
        cursor: not-allowed;
    }

    .recommendations-section {
        margin-top: 22px;
    }

    .section-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 13px;
    }

    .section-heading h2 {
        margin: 0;
        color: var(--rec-primary);
        font-size: 18px;
    }

    .section-heading span {
        color: var(--rec-muted);
        font-size: 11px;
    }

    .recommendations-grid {
        display: grid;
        grid-template-columns:
            repeat(
                auto-fit,
                minmax(240px, 1fr)
            );
        gap: 14px;
    }

    .recommendation-card {
        padding: 18px;
        border: 1px solid var(--rec-border);
        border-radius: 12px;
        background: var(--rec-card);
    }

    .recommendation-rank {
        margin-bottom: 8px;
        color: var(--rec-accent);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .recommendation-card h3 {
        margin: 0;
        color: var(--rec-primary);
        font-size: 16px;
        line-height: 1.35;
    }

    .recommendation-subsector {
        margin: 5px 0 14px;
        color: var(--rec-muted);
        font-size: 11px;
    }

    .match-score {
        margin-bottom: 14px;
        color: var(--rec-text);
        font-size: 13px;
        font-weight: 700;
    }

    .analysis-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--rec-primary-light);
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
    }

    .analysis-link:hover {
        text-decoration: underline;
    }

    .empty-recommendations {
        padding: 42px 20px;
        border: 1px dashed var(--rec-border);
        border-radius: 12px;
        background: var(--rec-card);
        text-align: center;
        color: var(--rec-muted);
    }

    .empty-recommendations i {
        margin-bottom: 10px;
        color: var(--rec-accent);
        font-size: 28px;
    }

    .empty-recommendations h3 {
        margin: 0 0 6px;
        color: var(--rec-primary);
        font-size: 15px;
    }

    .empty-recommendations p {
        margin: 0;
        font-size: 12px;
        line-height: 1.55;
    }

    /*
     * Reserved advertising placement:
     * future ad component can sit between
     * generation-panel and recommendations-section.
     */

    @media (max-width: 700px) {
        .recommendations-page {
            padding-top: 20px;
        }

        .generation-panel {
            grid-template-columns: 1fr;
        }

        .generate-button {
            width: 100%;
        }
    }

    @media (max-width: 520px) {
        .recommendations-header h1 {
            font-size: 27px;
        }

        .recommendations-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@php
    $canGenerate =
        $generationAccess['allowed']
        && $generationQuota['allowed'];

    $hasRecommendations =
        $recommendations->isNotEmpty();

    $quotaText = null;

    if (! $generationAccess['allowed']) {
        $quotaText =
            $generationAccess['message'];

    } elseif(
        $generationQuota['mode']
        === 'unlimited'
    ) {
        $quotaText =
            'Unlimited generations';

    } elseif(
        $generationQuota['mode']
        === 'total'
    ) {
        $amount =
            $generationQuota['amount'];

        $remaining =
            $generationQuota['remaining'];

        $quotaText =
            $amount
            . ' '
            . Str::plural(
                'generation',
                $amount
            )
            . ' total';

        if ($remaining !== null) {
            $quotaText .=
                ' · '
                . $remaining
                . ' remaining';
        }

    } elseif(
        $generationQuota['mode']
        === 'recurring'
    ) {
        $amount =
            $generationQuota['amount'];

        $periodValue =
            $generationQuota[
                'period_value'
            ];

        $periodUnit =
            $generationQuota[
                'period_unit'
            ];

        if ($periodValue === 1) {
            $quotaText =
                $amount
                . ' '
                . Str::plural(
                    'generation',
                    $amount
                )
                . ' per '
                . $periodUnit;
        } else {
            $quotaText =
                $amount
                . ' '
                . Str::plural(
                    'generation',
                    $amount
                )
                . ' every '
                . $periodValue
                . ' '
                . Str::plural(
                    $periodUnit,
                    $periodValue
                );
        }

        if (
            $generationQuota['allowed']
            && $generationQuota[
                'remaining'
            ] !== null
            && $generationQuota[
                'remaining'
            ] < $amount
        ) {
            $quotaText .=
                ' · '
                . $generationQuota[
                    'remaining'
                ]
                . ' currently available';
        }

        if (
            ! $generationQuota['allowed']
            && $generationQuota[
                'reason'
            ] === 'quota_exceeded'
            && $generationQuota[
                'next_available_at'
            ]
        ) {
            $quotaText =
                'Limit reached · Next generation available '
                . $generationQuota[
                    'next_available_at'
                ]->format(
                    'd M, g:i A'
                );
        }
    }
@endphp

<div class="recommendations-page">
    <div class="container">

        <div class="recommendations-header">
            <div>
                <h1>Career Recommendations</h1>

                <p>
                    Explore career paths based on your
                    current CareerPath profile,
                    competencies, interests and
                    aspirations.
                </p>
            </div>
        </div>

        <section class="generation-panel">
            <div>
                <h2>
                    {{
                        $hasRecommendations
                            ? 'Refresh your recommendations'
                            : 'Generate your recommendations'
                    }}
                </h2>

                <p>
                    Recommendation generation is manual.
                    Update your profile whenever needed,
                    then generate a new set when you are
                    ready.
                </p>

                @if($quotaText)
                    <div
                        class="quota-status {{
                            ! $canGenerate
                                ? 'blocked'
                                : ''
                        }}"
                    >
                        <i
                            class="fas {{
                                $generationQuota['mode']
                                    === 'unlimited'
                                    ? 'fa-infinity'
                                    : 'fa-clock'
                            }}"
                        ></i>

                        {{ $quotaText }}
                    </div>
                @endif
            </div>

            @if($canGenerate)
                <form
                    method="POST"
                    action="{{
                        route(
                            'student.recommendations.generate'
                        )
                    }}"
                    style="margin:0;"
                >
                    @csrf

                    <button
                        type="submit"
                        class="generate-button"
                    >
                        <i
                            class="fas
                            fa-wand-magic-sparkles"
                        ></i>

                        {{
                            $hasRecommendations
                                ? 'Refresh Recommendations'
                                : 'Generate Recommendations'
                        }}
                    </button>
                </form>
            @else
                <button
                    type="button"
                    class="generate-button"
                    disabled
                >
                    <i
                        class="fas
                        fa-circle-exclamation"
                    ></i>

                    Generation Unavailable
                </button>
            @endif
        </section>

        {{--
            Future advertisement placement goes here.
            It keeps ads outside the Dashboard and
            close to recommendation-related content.
        --}}

        <section class="recommendations-section">

            <div class="section-heading">
                <h2>Your Recommendations</h2>

                <span>
                    {{
                        $recommendations->count()
                    }}
                    {{
                        Str::plural(
                            'recommendation',
                            $recommendations->count()
                        )
                    }}
                </span>
            </div>

            @if($hasRecommendations)

                <div class="recommendations-grid">

                    @foreach($recommendations as $recommendation)

                        <article class="recommendation-card">

                            <div class="recommendation-rank">
                                Rank
                                #{{ $recommendation->rank }}
                            </div>

                            <h3>
                                {{
                                    $recommendation
                                        ->jobRole?->title
                                    ?? $recommendation
                                        ->career?->job_title
                                    ?? 'Career'
                                }}
                            </h3>

                            <div
                                class="recommendation-subsector"
                            >
                                {{
                                    $recommendation
                                        ->jobRole
                                        ?->subSector
                                        ?->name
                                    ?? $recommendation
                                        ->career
                                        ?->subsector
                                    ?? 'Sub-sector unavailable'
                                }}
                            </div>

                            <div class="match-score">
                                {{
                                    round(
                                        $recommendation
                                            ->match_score
                                        ?? 0
                                    )
                                }}%
                                match
                            </div>

                            <a
                                href="{{
                                    route(
                                        'student.recommendations.analysis',
                                        $recommendation->id
                                    )
                                }}"
                                class="analysis-link"
                            >
                                View Career Analysis
                                <i
                                    class="fas fa-arrow-right"
                                ></i>
                            </a>

                        </article>

                    @endforeach

                </div>

            @else

                <div class="empty-recommendations">
                    <i class="fas fa-compass"></i>

                    <h3>
                        No recommendations yet
                    </h3>

                    <p>
                        When you are ready, generate your
                        first set of personalised career
                        recommendations above.
                    </p>
                </div>

            @endif

        </section>

    </div>
</div>

@endsection