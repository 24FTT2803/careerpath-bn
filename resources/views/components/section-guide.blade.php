{{--
    The '?' beside a section heading, and the window it opens.

    Usage: <x-section-guide guide="profile.skills" />

    The guide's content waits in a <template>, so a video in it is
    not loaded until the student asks for the help. The window and
    its script are written once per page, however many icons the
    page has.
--}}
@props(['guide'])

@php
    $sectionGuide = \App\Models\SectionGuide::forKey($guide);
@endphp

@if($sectionGuide)
    <button
        type="button"
        class="cpbn-guide-btn"
        data-guide-open="{{ $guide }}"
        aria-haspopup="dialog"
        aria-label="Help: {{ $sectionGuide->title }}"
        title="What is this?"
    >?</button>

    <template id="cpbn-guide-{{ $guide }}">
        <h2 class="cpbn-guide-title" id="cpbnGuideTitle">{{ $sectionGuide->title }}</h2>

        @foreach($sectionGuide->paragraphs() as $paragraph)
            <p class="cpbn-guide-text">{!! nl2br(e($paragraph)) !!}</p>
        @endforeach

        @if($embed = $sectionGuide->youtubeEmbedUrl())
            <div class="cpbn-guide-video">
                <iframe
                    src="{{ $embed }}"
                    title="{{ $sectionGuide->title }} video"
                    allow="accelerometer; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    loading="lazy"
                ></iframe>
            </div>
        @elseif($file = $sectionGuide->videoFileUrl())
            <div class="cpbn-guide-video">
                <video src="{{ $file }}" controls preload="metadata"></video>
            </div>
        @elseif($sectionGuide->video_url)
            <p class="cpbn-guide-text">
                <a href="{{ $sectionGuide->video_url }}" target="_blank" rel="noopener">
                    <i class="fas fa-play-circle"></i> Watch the video
                </a>
            </p>
        @endif

        {{-- Where the CPBN Bot can fill this section in from a chat. --}}
        @if($fillSection = \App\Services\CpbnBot\CpbnBotProfileFill::sectionForGuide($guide))
            <div class="cpbn-guide-bot">
                <img
                    src="{{ asset('images/careerpath-badge.png') }}"
                    alt=""
                >
                <div>
                    <strong>Want a hand?</strong>
                    <span>The CPBN Bot can ask you a few questions and fill this section in for you to check.</span>
                </div>
                <button
                    type="button"
                    data-guide-bot="Help me fill in my {{ $sectionGuide->title }}"
                    data-guide-section="{{ $fillSection }}"
                >
                    Fill it in with the CPBN Bot
                </button>
            </div>
        @endif
    </template>

    @once
        <div
            class="cpbn-guide-overlay"
            id="cpbnGuideOverlay"
            hidden
        >
            <div
                class="cpbn-guide-dialog"
                role="dialog"
                aria-modal="true"
                aria-labelledby="cpbnGuideTitle"
            >
                <button
                    type="button"
                    class="cpbn-guide-close"
                    data-guide-close
                    aria-label="Close help"
                >&times;</button>

                <div class="cpbn-guide-body" id="cpbnGuideBody"></div>
            </div>
        </div>

        <style>
            .cpbn-guide-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 20px;
                height: 20px;
                margin-left: 8px;
                padding: 0;
                border: 1.5px solid var(--primary, #1a3a5c);
                border-radius: 50%;
                background: transparent;
                color: var(--primary, #1a3a5c);
                font-family: 'Inter', -apple-system, sans-serif;
                font-size: 12px;
                font-weight: 700;
                line-height: 1;
                vertical-align: middle;
                cursor: pointer;
                flex-shrink: 0;
                transition: background 0.15s ease, color 0.15s ease;
            }

            .cpbn-guide-btn:hover,
            .cpbn-guide-btn:focus-visible {
                background: var(--primary, #1a3a5c);
                color: #ffffff;
                outline: none;
            }

            .cpbn-guide-overlay {
                position: fixed;
                inset: 0;
                z-index: 10050;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 16px;
                background: rgba(13, 31, 51, 0.55);
            }

            .cpbn-guide-overlay[hidden] {
                display: none;
            }

            .cpbn-guide-dialog {
                position: relative;
                width: 100%;
                max-width: 560px;
                max-height: calc(100vh - 32px);
                overflow-y: auto;
                padding: 28px 28px 24px;
                border-radius: var(--radius, 12px);
                background: var(--card, #ffffff);
                box-shadow: 0 20px 60px rgba(13, 31, 51, 0.3);
                text-align: left;
            }

            .cpbn-guide-close {
                position: absolute;
                top: 10px;
                right: 12px;
                width: 32px;
                height: 32px;
                border: none;
                border-radius: 50%;
                background: transparent;
                color: var(--text-muted, #6b7280);
                font-size: 24px;
                line-height: 1;
                cursor: pointer;
            }

            .cpbn-guide-close:hover {
                background: var(--bg, #f4f6f9);
                color: var(--text, #1a1a2e);
            }

            .cpbn-guide-title {
                margin: 0 36px 14px 0;
                font-family: 'Inter', -apple-system, sans-serif;
                text-transform: none;
                letter-spacing: normal;
                font-size: 19px;
                font-weight: 700;
                color: var(--primary, #1a3a5c);
            }

            .cpbn-guide-text {
                margin: 0 0 12px;
                font-size: 14px;
                font-weight: 400;
                line-height: 1.65;
                color: var(--text, #1a1a2e);
            }

            .cpbn-guide-video {
                position: relative;
                margin-top: 16px;
                aspect-ratio: 16 / 9;
                border-radius: 8px;
                overflow: hidden;
                background: #000000;
            }

            .cpbn-guide-video iframe,
            .cpbn-guide-video video {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                border: 0;
            }

            /* Shown only where the CPBN Bot is on the page. */
            .cpbn-guide-bot {
                display: none;
                align-items: center;
                flex-wrap: wrap;
                gap: 12px;
                margin-top: 18px;
                padding: 14px;
                border: 1px solid var(--border, #e5e7eb);
                border-radius: 10px;
                background: var(--bg, #f4f6f9);
            }

            .has-cpbn-bot .cpbn-guide-bot {
                display: flex;
            }

            .cpbn-guide-bot img {
                width: 36px;
                height: 36px;
                flex-shrink: 0;
            }

            .cpbn-guide-bot div {
                flex: 1;
                min-width: 180px;
                display: flex;
                flex-direction: column;
                gap: 2px;
                font-size: 13px;
                line-height: 1.5;
                color: var(--text, #1a1a2e);
            }

            .cpbn-guide-bot button {
                padding: 9px 14px;
                border: none;
                border-radius: 8px;
                background: var(--primary, #1a3a5c);
                color: #ffffff;
                font-family: inherit;
                font-size: 13px;
                font-weight: 600;
                cursor: pointer;
            }

            @media (max-width: 480px) {
                .cpbn-guide-dialog {
                    padding: 24px 18px 18px;
                }
            }
        </style>

        <script>
            (function () {
                const overlay = document.getElementById('cpbnGuideOverlay');
                const body = document.getElementById('cpbnGuideBody');
                let opener = null;

                /*
                 * Written wherever the first icon is, which may be
                 * inside a heading or a form; moved to the body so
                 * neither styles nor contains it.
                 */
                document.body.appendChild(overlay);

                function open(key, button) {
                    const template = document.getElementById('cpbn-guide-' + key);

                    if (! template) {
                        return;
                    }

                    opener = button;
                    body.replaceChildren(template.content.cloneNode(true));
                    overlay.hidden = false;
                    document.body.style.overflow = 'hidden';
                    overlay.querySelector('[data-guide-close]').focus();
                }

                function close() {
                    if (overlay.hidden) {
                        return;
                    }

                    overlay.hidden = true;

                    /* Emptied, so a playing video stops. */
                    body.replaceChildren();
                    document.body.style.overflow = '';

                    if (opener) {
                        opener.focus();
                        opener = null;
                    }
                }

                document.addEventListener('click', function (event) {
                    const helper = event.target.closest('[data-guide-bot]');

                    if (helper && typeof window.cpbnBotAsk === 'function') {
                        event.preventDefault();
                        const question = helper.dataset.guideBot;
                        const section = helper.dataset.guideSection;
                        close();
                        window.cpbnBotAsk(question, section);

                        return;
                    }

                    const button = event.target.closest('[data-guide-open]');

                    if (button) {
                        event.preventDefault();
                        event.stopPropagation();
                        open(button.dataset.guideOpen, button);

                        return;
                    }

                    if (event.target.closest('[data-guide-close]') || event.target === overlay) {
                        close();
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        close();
                    }
                });
            })();
        </script>
    @endonce
@endif
