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

    .recommendation-status {
        display: flex;
        align-items: flex-start;
        gap: 7px;
        margin-top: 10px;
        padding: 8px 10px;
        border-radius: 7px;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.5;
        max-width: 460px;
    }

    .recommendation-status.current {
        background: #f0fdf4;
        color: #166534;
    }

    .recommendation-status.outdated {
        background: #fff7ed;
        color: #9a6700;
    }

    .recommendation-status i {
        margin-top: 2px;
    }

    /*
     * A set generated before the profile changed. One red dot,
     * on the button that fixes it, and the reason on hover for
     * whoever wants it — rather than a paragraph above, which
     * said the same thing at ten times the size and nowhere near
     * the thing to press.
     */
    .generate-button.is-stale {
        overflow: visible;
    }

    .stale-dot {
        position: absolute;
        top: -5px;
        right: -5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #dc2626;
        border: 2px solid white;
        box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.55);
        animation: stalePulse 2s ease-out infinite;
    }

    @keyframes stalePulse {
        70% { box-shadow: 0 0 0 9px rgba(220, 38, 38, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
    }

    .stale-why {
        position: absolute;
        bottom: calc(100% + 10px);
        right: 0;
        width: 260px;
        padding: 10px 12px;
        border-radius: 8px;
        background: #1a1a2e;
        color: #f4f6f9;
        font-size: 11px;
        font-weight: 500;
        line-height: 1.6;
        text-align: left;
        opacity: 0;
        visibility: hidden;
        transform: translateY(4px);
        transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s;
        pointer-events: none;
        z-index: 5;
    }

    .stale-why::after {
        content: '';
        position: absolute;
        top: 100%;
        right: 18px;
        border: 6px solid transparent;
        border-top-color: #1a1a2e;
    }

    .generate-button.is-stale:hover .stale-why,
    .generate-button.is-stale:focus-visible .stale-why {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .generate-button {
        position: relative;
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

    /*
     * Each card arrives in turn, and holds a gold ring for a
     * moment before settling. Enough to be impossible to miss on
     * a page that otherwise looks unchanged, and over soon enough
     * not to be in the way of reading it.
     */
    .recommendations-grid.is-fresh .recommendation-card {
        animation:
            cardArrive 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) backwards,
            cardNotice 2.4s ease-out 0.5s backwards;
        animation-delay: calc(var(--card-order) * 0.12s), calc(var(--card-order) * 0.12s + 0.5s);
    }

    @keyframes cardArrive {
        from {
            opacity: 0;
            transform: translateY(18px) scale(0.98);
        }
    }

    @keyframes cardNotice {
        0%, 55% {
            border-color: var(--rec-accent);
            box-shadow: 0 0 0 3px rgba(201, 168, 76, 0.25);
        }
        100% {
            border-color: var(--rec-border);
            box-shadow: 0 0 0 0 rgba(201, 168, 76, 0);
        }
    }

    .recommendations-fresh-note {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
        padding: 10px 14px;
        border-radius: 9px;
        background: rgba(201, 168, 76, 0.14);
        color: #8a6420;
        font-size: 12px;
        font-weight: 700;
    }

    /*
     * Movement is a preference, and a student who has turned it
     * off still needs to see that something arrived — so the ring
     * stays and the sliding goes.
     */
    @media (prefers-reduced-motion: reduce) {
        .recommendations-grid.is-fresh .recommendation-card {
            animation: cardNotice 2.4s ease-out backwards;
            animation-delay: 0s;
        }
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

                {{--
                    An out-of-date set used to say so in a
                    paragraph under the heading, which is a lot of
                    words for one fact and sat nowhere near the
                    button that answers it. The button carries a
                    dot now, and the sentence with it, for whoever
                    wants to know what the dot means.
                --}}
                @if($currentGeneration && ($currentGeneration->status ?? null) !== 'outdated')
                    <div class="recommendation-status current">
                        <i class="fas fa-circle-check"></i>

                        Generated
                        {{
                            $currentGeneration
                                ->generated_at
                                ->timezone(config('app.business_timezone'))->format('j M Y')
                        }}
                        and up to date with your profile.
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

                    {{--
                        Disabled while the pause runs, with the
                        remaining time on the button itself, so
                        a student can see why it will not go
                        rather than pressing it and being told.
                    --}}
                    <button
                        type="submit"
                        class="generate-button @if(($currentGeneration?->status ?? null) === 'outdated') is-stale @endif"
                        id="generateButton"
                        data-cooldown="{{ $cooldownSeconds ?? 0 }}"
                        @disabled(($cooldownSeconds ?? 0) > 0)
                    >
                        @if(($currentGeneration?->status ?? null) === 'outdated')
                            <span class="stale-dot" aria-hidden="true"></span>

                            <span class="stale-why" role="note">
                                Your profile has changed since these were generated on
                                {{
                                    $currentGeneration
                                        ->generated_at
                                        ->timezone(config('app.business_timezone'))->format('j M Y')
                                }}.
                                Generate again to reflect it.
                            </span>
                        @endif

                        <i
                            class="fas
                            fa-wand-magic-sparkles"
                        ></i>

                        <span id="generateButtonLabel">
                            @if(($cooldownSeconds ?? 0) > 0)
                                Please wait {{ $cooldownSeconds }}s
                            @else
                                {{
                                    $hasRecommendations
                                        ? 'Refresh Recommendations'
                                        : 'Generate Recommendations'
                                }}
                            @endif
                        </span>
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

        <x-guidance-disclaimer />

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

                {{--
                    Freshly generated, and saying so. The page
                    reloads looking exactly as it did, with the
                    new matches below the fold — which is how a
                    student presses generate and comes away
                    thinking nothing happened.
                --}}
                @if(session('just_generated'))
                    <p class="recommendations-fresh-note">
                        <i class="fas fa-wand-magic-sparkles"></i>
                        Just generated &mdash; here are your matches.
                    </p>
                @endif

                <div class="recommendations-grid @if(session('just_generated')) is-fresh @endif"
                     id="recommendationsGrid">

                    @foreach($recommendations as $index => $recommendation)

                        <article class="recommendation-card" style="--card-order: {{ $index }}">

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

@if(session('just_generated'))
    <script>
        /*
         * The matches sit below the generation panel, so pressing
         * the button lands the student back at the top with the
         * new set out of sight. The page comes to them.
         */
        document.addEventListener('DOMContentLoaded', function () {
            const grid = document.getElementById('recommendationsGrid');

            if (!grid) {
                return;
            }

            window.requestAnimationFrame(function () {
                grid.scrollIntoView({
                    behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches
                        ? 'auto'
                        : 'smooth',
                    block: 'start',
                });
            });
        });
    </script>
@endif

@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var button = document.getElementById('generateButton');
        var label = document.getElementById('generateButtonLabel');

        if (!button || !label) {
            return;
        }

        var remaining = parseInt(button.dataset.cooldown, 10) || 0;

        if (remaining < 1) {
            return;
        }

        /*
         * The server decides whether a generation is allowed.
         * This only counts the same pause down in front of the
         * student so the button does not sit dead with no
         * explanation of when it will work again.
         */
        var ready = @js(
            $hasRecommendations
                ? 'Refresh Recommendations'
                : 'Generate Recommendations'
        );

        var timer = window.setInterval(function () {
            remaining -= 1;

            if (remaining > 0) {
                label.textContent = 'Please wait ' + remaining + 's';

                return;
            }

            window.clearInterval(timer);

            label.textContent = ready;
            button.disabled = false;
        }, 1000);
    });
</script>
