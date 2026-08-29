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

    .adviser-shell {
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