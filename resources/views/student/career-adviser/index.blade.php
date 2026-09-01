@extends('layouts.app')

@section('title', 'Career Adviser')

@section('content')

<style>
    .career-adviser-page {
        --adviser-primary: #1a3a5c;
        --adviser-primary-light: #2a5a8c;
        --adviser-accent: #c9a84c;
        --adviser-accent-light: #e8d4a0;
        --adviser-bg: #f4f6f9;
        --adviser-card: #ffffff;
        --adviser-text: #1a1a2e;
        --adviser-muted: #6b7280;
        --adviser-border: #e5e7eb;
        --adviser-success: #2d8f5c;
        --adviser-warning: #c58a24;
        --adviser-shadow: 0 4px 24px rgba(26, 58, 92, 0.08);
        --adviser-shadow-hover: 0 8px 36px rgba(26, 58, 92, 0.13);

        padding: 28px 0 48px;
    }

    .career-adviser-page * {
        box-sizing: border-box;
    }

    .adviser-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 22px;
    }

    .adviser-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: var(--adviser-accent);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 7px;
    }

    .adviser-header h1 {
        margin: 0;
        color: var(--adviser-primary);
        font-family: 'Playfair Display', serif;
        font-size: 31px;
        line-height: 1.2;
        font-weight: 700;
    }

    .adviser-header p {
        margin: 7px 0 0;
        color: var(--adviser-muted);
        font-size: 14px;
        line-height: 1.6;
        max-width: 670px;
    }

    .adviser-preview-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        flex-shrink: 0;
        padding: 7px 13px;
        border-radius: 100px;
        background: rgba(201, 168, 76, 0.12);
        border: 1px solid rgba(201, 168, 76, 0.28);
        color: #8b7028;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .preview-notice {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 13px 16px;
        margin-bottom: 18px;
        border: 1px solid #dbe4ee;
        border-radius: 10px;
        background: #f8fbfe;
        color: #486078;
        font-size: 13px;
        line-height: 1.55;
    }

    .preview-notice i {
        color: var(--adviser-primary-light);
        margin-top: 2px;
    }

    /*
    |--------------------------------------------------------------------------
    | Main Workspace
    |--------------------------------------------------------------------------
    */

    .adviser-workspace {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 300px;
        gap: 18px;
        align-items: stretch;
    }

    /*
    |--------------------------------------------------------------------------
    | Conversation
    |--------------------------------------------------------------------------
    */

    .adviser-shell {
        min-width: 0;
        background: var(--adviser-card);
        border: 1px solid var(--adviser-border);
        border-radius: 14px;
        box-shadow: var(--adviser-shadow);
        overflow: hidden;
    }

    .conversation-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--adviser-border);
        background: #fff;
    }

    .adviser-identity {
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .adviser-avatar {
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: linear-gradient(
            135deg,
            var(--adviser-primary),
            var(--adviser-primary-light)
        );
        color: var(--adviser-accent);
        font-size: 16px;
    }

    .adviser-identity h2 {
        margin: 0;
        color: var(--adviser-primary);
        font-size: 14px;
        font-weight: 700;
    }

    .adviser-identity p {
        margin: 2px 0 0;
        color: var(--adviser-muted);
        font-size: 11px;
    }

    .preview-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: var(--adviser-muted);
        font-size: 11px;
        white-space: nowrap;
    }

    .preview-status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--adviser-accent);
    }

    .conversation-body {
        min-height: 410px;
        padding: 28px;
        background:
            linear-gradient(
                180deg,
                rgba(244, 246, 249, 0.35),
                rgba(255, 255, 255, 0)
            );
    }

    .adviser-message {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        max-width: 760px;
    }

    .message-avatar {
        width: 32px;
        height: 32px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--adviser-primary);
        color: var(--adviser-accent);
        font-size: 12px;
    }

    .message-bubble {
        padding: 14px 16px;
        border: 1px solid var(--adviser-border);
        border-radius: 4px 12px 12px 12px;
        background: white;
        color: var(--adviser-text);
        font-size: 13px;
        line-height: 1.65;
        box-shadow: 0 2px 12px rgba(26, 58, 92, 0.05);
    }

    .message-name {
        margin: 0 0 5px;
        color: var(--adviser-primary);
        font-size: 11px;
        font-weight: 700;
    }

    .message-bubble p:last-child {
        margin-bottom: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Suggested Questions
    |--------------------------------------------------------------------------
    */

    .suggested-section {
        margin-top: 30px;
        padding-left: 43px;
    }

    .suggested-label {
        margin: 0 0 10px;
        color: var(--adviser-muted);
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .suggested-prompts {
        display: flex;
        flex-wrap: wrap;
        gap: 9px;
    }

    .suggested-prompt {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 13px;
        border: 1px solid var(--adviser-border);
        border-radius: 8px;
        background: white;
        color: var(--adviser-primary);
        font-family: inherit;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .suggested-prompt:hover {
        border-color: var(--adviser-accent-light);
        background: rgba(201, 168, 76, 0.07);
        transform: translateY(-1px);
        box-shadow: var(--adviser-shadow);
    }

    .suggested-prompt i {
        color: var(--adviser-accent);
        font-size: 11px;
    }

    /*
    |--------------------------------------------------------------------------
    | Composer
    |--------------------------------------------------------------------------
    */

    .composer {
        padding: 16px 20px 18px;
        border-top: 1px solid var(--adviser-border);
        background: white;
    }

    .composer-label {
        display: block;
        margin-bottom: 7px;
        color: var(--adviser-primary);
        font-size: 11px;
        font-weight: 700;
    }

    .composer-row {
        display: flex;
        align-items: flex-end;
        gap: 10px;
    }

    .composer-input {
        width: 100%;
        min-height: 46px;
        max-height: 130px;
        resize: none;
        padding: 12px 14px;
        border: 1px solid var(--adviser-border);
        border-radius: 9px;
        background: #fff;
        color: var(--adviser-text);
        font-family: inherit;
        font-size: 13px;
        line-height: 1.5;
        outline: none;
        transition: all 0.2s ease;
    }

    .composer-input:focus {
        border-color: var(--adviser-primary-light);
        box-shadow: 0 0 0 3px rgba(42, 90, 140, 0.08);
    }

    .composer-input::placeholder {
        color: #9ca3af;
    }

    .composer-send {
        height: 46px;
        min-width: 92px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 16px;
        border: none;
        border-radius: 9px;
        background: #d6dbe1;
        color: #7c8794;
        font-family: inherit;
        font-size: 12px;
        font-weight: 700;
        cursor: not-allowed;
    }

    .composer-note {
        margin: 7px 0 0;
        color: var(--adviser-muted);
        font-size: 10.5px;
    }

    /*
    |--------------------------------------------------------------------------
    | Career Context
    |--------------------------------------------------------------------------
    */

    .career-context {
        min-width: 0;
        padding: 20px;
        background: var(--adviser-card);
        border: 1px solid var(--adviser-border);
        border-radius: 14px;
        box-shadow: var(--adviser-shadow);
    }

    .context-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        padding-bottom: 15px;
        margin-bottom: 4px;
        border-bottom: 1px solid var(--adviser-border);
    }

    .context-heading-icon {
        width: 31px;
        height: 31px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 8px;
        background: rgba(26, 58, 92, 0.07);
        color: var(--adviser-primary);
        font-size: 12px;
    }

    .context-heading h2 {
        margin: 0;
        color: var(--adviser-primary);
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .context-heading p {
        margin: 2px 0 0;
        color: var(--adviser-muted);
        font-size: 10px;
    }

    .context-item {
        padding: 17px 0;
        border-bottom: 1px solid var(--adviser-border);
    }

    .context-item:last-of-type {
        border-bottom: none;
    }

    .context-label {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 8px;
        color: var(--adviser-muted);
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .context-label i {
        width: 13px;
        color: var(--adviser-accent);
        text-align: center;
    }

    .context-value {
        margin: 0;
        color: var(--adviser-primary);
        font-size: 20px;
        font-weight: 750;
        line-height: 1.25;
    }

    .context-value.career-name {
        font-size: 16px;
        line-height: 1.35;
    }

    .context-description {
        margin: 5px 0 0;
        color: var(--adviser-muted);
        font-size: 11px;
        line-height: 1.55;
    }

    /*
    |--------------------------------------------------------------------------
    | Profile Completion
    |--------------------------------------------------------------------------
    */

    .profile-progress-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 8px;
    }

    .profile-progress-row .context-value {
        font-size: 19px;
    }

    .profile-progress-track {
        width: 100%;
        height: 6px;
        overflow: hidden;
        border-radius: 100px;
        background: #edf0f3;
    }

    .profile-progress-fill {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(
            90deg,
            var(--adviser-primary),
            var(--adviser-primary-light)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Context Status
    |--------------------------------------------------------------------------
    */

    .context-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 7px;
        padding: 5px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
    }

    .context-status.success {
        background: rgba(45, 143, 92, 0.1);
        color: var(--adviser-success);
    }

    .context-status.warning {
        background: rgba(197, 138, 36, 0.1);
        color: var(--adviser-warning);
    }

    .context-status.neutral {
        background: #f1f3f5;
        color: var(--adviser-muted);
    }

    /*
    |--------------------------------------------------------------------------
    | BIICF Link
    |--------------------------------------------------------------------------
    */

    .context-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 10px;
        color: var(--adviser-primary-light);
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
    }

    .context-action:hover {
        color: var(--adviser-primary);
        text-decoration: underline;
    }

    .context-action i {
        font-size: 9px;
    }

    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1000px) {
        .adviser-workspace {
            grid-template-columns: minmax(0, 1fr) 270px;
        }

        .career-context {
            padding: 17px;
        }
    }

    @media (max-width: 850px) {
        .adviser-workspace {
            grid-template-columns: 1fr;
        }

        .career-context {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0 22px;
        }

        .context-heading {
            grid-column: 1 / -1;
        }

        .context-item:nth-of-type(2),
        .context-item:nth-of-type(4) {
            border-right: 1px solid var(--adviser-border);
            padding-right: 22px;
        }

        .context-item:nth-of-type(3),
        .context-item:nth-of-type(5) {
            padding-left: 0;
        }
    }

    @media (max-width: 768px) {
        .career-adviser-page {
            padding-top: 20px;
        }

        .adviser-header {
            flex-direction: column;
            gap: 12px;
        }

        .adviser-header h1 {
            font-size: 27px;
        }

        .conversation-body {
            min-height: 370px;
            padding: 20px 16px;
        }

        .suggested-section {
            padding-left: 0;
        }

        .suggested-prompts {
            flex-direction: column;
        }

        .suggested-prompt {
            width: 100%;
            justify-content: flex-start;
        }

        .composer {
            padding: 14px;
        }
    }

    @media (max-width: 600px) {
        .career-context {
            display: block;
        }

        .context-item {
            border-right: none !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
    }

    @media (max-width: 520px) {
        .conversation-header {
            align-items: flex-start;
        }

        .preview-status {
            display: none;
        }

        .composer-row {
            align-items: stretch;
        }

        .composer-send {
            min-width: 48px;
            width: 48px;
            padding: 0;
        }

        .composer-send span {
            display: none;
        }
    }
</style>

<div class="career-adviser-page">

    <div class="adviser-header">
        <div>
            <div class="adviser-eyebrow">
                <i class="fas fa-compass"></i>
                Personalised Career Guidance
            </div>

            <h1>Career Adviser</h1>

            <p>
                Ask questions about your career matches, competency gaps,
                BIICF roles and the next steps in your career development.
            </p>
        </div>

        <div class="adviser-preview-badge">
            <i class="fas fa-flask"></i>
            Preview Mode
        </div>
    </div>

    <div class="preview-notice">
        <i class="fas fa-circle-info"></i>

        <div>
            This is the Career Adviser interface preview.
            AI responses are not connected yet; the current page establishes
            the experience that will later connect to the Career Adviser service.
        </div>
    </div>

    <div class="adviser-workspace">

        {{-- ============================================================
             CONVERSATION
             ============================================================ --}}
        <section class="adviser-shell">

            <div class="conversation-header">

                <div class="adviser-identity">
                    <div class="adviser-avatar">
                        <i class="fas fa-compass"></i>
                    </div>

                    <div>
                        <h2>Career Adviser</h2>
                        <p>CareerPath BN guidance assistant</p>
                    </div>
                </div>

                <div class="preview-status">
                    <span class="preview-status-dot"></span>
                    Interface preview
                </div>

            </div>

            <div class="conversation-body">

                <div class="adviser-message">

                    <div class="message-avatar">
                        <i class="fas fa-compass"></i>
                    </div>

                    <div class="message-bubble">

                        <p class="message-name">
                            Career Adviser
                        </p>

                        <p>
                            Hi {{ $student->first_name ?? $student->name }}.
                            This space will help you understand your career
                            recommendations and turn them into practical next steps.
                        </p>

                        <p>
                            You will be able to ask about your career matches,
                            skills to improve, BIICF roles and career development.
                        </p>

                    </div>

                </div>

                <div class="suggested-section">

                    <p class="suggested-label">
                        Suggested questions
                    </p>

                    <div class="suggested-prompts">

                        <button
                            type="button"
                            class="suggested-prompt"
                            data-prompt="Explain my top career match"
                        >
                            <i class="fas fa-star"></i>
                            Explain my top career match
                        </button>

                        <button
                            type="button"
                            class="suggested-prompt"
                            data-prompt="What skills should I improve?"
                        >
                            <i class="fas fa-arrow-trend-up"></i>
                            What skills should I improve?
                        </button>

                        <button
                            type="button"
                            class="suggested-prompt"
                            data-prompt="Compare my career options"
                        >
                            <i class="fas fa-code-compare"></i>
                            Compare my career options
                        </button>

                        <button
                            type="button"
                            class="suggested-prompt"
                            data-prompt="What should I do next?"
                        >
                            <i class="fas fa-route"></i>
                            What should I do next?
                        </button>

                    </div>

                </div>

            </div>

            <div class="composer">

                <label
                    for="careerAdviserPrompt"
                    class="composer-label"
                >
                    Ask your Career Adviser
                </label>

                <div class="composer-row">

                    <textarea
                        id="careerAdviserPrompt"
                        class="composer-input"
                        rows="1"
                        maxlength="500"
                        placeholder="Ask about your career options, competencies or next steps..."
                    ></textarea>

                    <button
                        type="button"
                        class="composer-send"
                        disabled
                        title="AI integration is not connected yet"
                    >
                        <i class="fas fa-paper-plane"></i>
                        <span>Send</span>
                    </button>

                </div>

                <p class="composer-note">
                    Preview only — message processing will be enabled during
                    Career Adviser integration.
                </p>

            </div>

        </section>

        {{-- ============================================================
             CAREER CONTEXT
             ============================================================ --}}
        <aside class="career-context">

            <div class="context-heading">

                <div class="context-heading-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>

                <div>
                    <h2>Your Career Context</h2>
                    <p>Based on your current CareerPath data</p>
                </div>

            </div>

            {{-- Profile Completion --}}
            <div class="context-item">

                <div class="context-label">
                    <i class="fas fa-user-check"></i>
                    Profile Completion
                </div>

                <div class="profile-progress-row">
                    <p class="context-value">
                        {{ $profileCompletion }}%
                    </p>
                </div>

                <div
                    class="profile-progress-track"
                    role="progressbar"
                    aria-label="Profile completion"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-valuenow="{{ $profileCompletion }}"
                >
                    <div
                        class="profile-progress-fill"
                        style="width: {{ min(max($profileCompletion, 0), 100) }}%;"
                    ></div>
                </div>

                @if($profileCompletion >= 70)
                    <span class="context-status success">
                        <i class="fas fa-circle-check"></i>
                        Recommendation ready
                    </span>
                @else
                    <span class="context-status warning">
                        <i class="fas fa-circle-exclamation"></i>
                        More profile details needed
                    </span>
                @endif

            </div>

            {{-- Top Career Recommendation --}}
            <div class="context-item">

                <div class="context-label">
                    <i class="fas fa-star"></i>
                    Top Career Match
                </div>

                @if($topRecommendation && $topRecommendation->career)

                    <p class="context-value career-name">
                        {{ $topRecommendation->career->job_title }}
                    </p>

                    <p class="context-description">
                        {{ round($topRecommendation->match_score ?? 0) }}%
                        match based on your latest recommendation.
                    </p>

                    <span class="context-status success">
                        <i class="fas fa-ranking-star"></i>
                        Rank #{{ $topRecommendation->rank ?? 1 }}
                    </span>

                @else

                    <p class="context-value career-name">
                        Not available yet
                    </p>

                    <p class="context-description">
                        Career recommendations will appear here once they
                        are available for your profile.
                    </p>

                    <span class="context-status neutral">
                        <i class="fas fa-clock"></i>
                        No recommendation
                    </span>

                @endif

            </div>

            {{-- Skill Gaps --}}
            <div class="context-item">

                <div class="context-label">
                    <i class="fas fa-arrow-trend-up"></i>
                    Priority Skill Gaps
                </div>

                @if($topRecommendation)

                    <p class="context-value">
                        {{ $skillGapCount }}
                    </p>

                    @if($skillGapCount > 0)
                        <p class="context-description">
                            {{ Str::plural('gap', $skillGapCount) }}
                            identified in your top career recommendation.
                        </p>

                        <span class="context-status warning">
                            <i class="fas fa-screwdriver-wrench"></i>
                            Development areas
                        </span>
                    @else
                        <p class="context-description">
                            No skill gaps are currently listed for your
                            top recommendation.
                        </p>

                        <span class="context-status success">
                            <i class="fas fa-circle-check"></i>
                            No gaps listed
                        </span>
                    @endif

                @else

                    <p class="context-value">
                        —
                    </p>

                    <p class="context-description">
                        Skill-gap information will be available with your
                        career recommendations.
                    </p>

                @endif

            </div>

            {{-- BIICF Explorer --}}
            <div class="context-item">

                <div class="context-label">
                    <i class="fas fa-compass"></i>
                    BIICF Explorer
                </div>

                @if($biicfAvailable)

                    <p class="context-value">
                        {{ $biicfRoleCount }}
                    </p>

                    <p class="context-description">
                        Job {{ Str::plural('role', $biicfRoleCount) }}
                        across
                        {{ $biicfSubSectorCount }}
                        {{ Str::plural('sub-sector', $biicfSubSectorCount) }}.
                    </p>

                    <span class="context-status success">
                        <i class="fas fa-database"></i>
                        BIICF available
                    </span>

                @else

                    <p class="context-value">
                        —
                    </p>

                    <p class="context-description">
                        BIICF Explorer data is currently unavailable.
                    </p>

                    <span class="context-status neutral">
                        <i class="fas fa-database"></i>
                        No BIICF data
                    </span>

                @endif

                <br>

                <a
                    href="{{ route('student.biicf-explorer.index') }}"
                    class="context-action"
                >
                    Browse BIICF Explorer
                    <i class="fas fa-arrow-right"></i>
                </a>

            </div>

        </aside>

    </div>

</div>

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function () {
            const promptInput =
                document.getElementById(
                    'careerAdviserPrompt'
                );

            const promptButtons =
                document.querySelectorAll(
                    '.suggested-prompt'
                );

            promptButtons.forEach(
                function (button) {
                    button.addEventListener(
                        'click',
                        function () {
                            promptInput.value =
                                button.dataset.prompt || '';

                            promptInput.focus();
                        }
                    );
                }
            );
        }
    );
</script>

@endsection