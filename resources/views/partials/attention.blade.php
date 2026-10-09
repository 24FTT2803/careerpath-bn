{{--
    Notification badges and "new" dots, shared by every layout.
    Badges pop in once, then a soft ring pulses around them.
--}}
<style>
    /* Small dot on an icon, avatar or button corner */
    .attn-dot {
        position: absolute;
        top: -3px;
        right: -3px;
        z-index: 2;
        width: 11px;
        height: 11px;
        border-radius: 50%;
        background: #e0483e;
        box-shadow: 0 0 0 2px #fff;
        pointer-events: none;
    }

    .attn-dot.is-gold {
        background: #c9a84c;
    }

    /* Small label inside a menu item, e.g. "New" or "3" */
    .attn-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-left: auto;
        padding: 1px 8px;
        border-radius: 999px;
        background: #e0483e;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        line-height: 1.6;
        white-space: nowrap;
    }

    .attn-pill.is-gold {
        background: rgba(201, 168, 76, 0.16);
        color: #8a6d22;
        border: 1px solid rgba(201, 168, 76, 0.5);
    }

    /* Any number badge that should pop and pulse */
    .attn-count {
        position: relative;
        isolation: isolate;
    }

    /* The avatar dot sits on the avatar's corner */
    .nav-user {
        position: relative;
    }

    .nav-user > .attn-dot {
        top: 1px;
        left: 27px;
        right: auto;
    }

    /* Buttons that carry a dot need a corner to anchor it */
    .attn-anchor {
        position: relative;
    }

    /* Speech bubble that explains a dot, e.g. "Complete your profile…" */
    .attn-tip {
        position: absolute;
        bottom: calc(100% + 12px);
        right: -8px;
        z-index: 30;
        display: flex;
        align-items: flex-start;
        gap: 8px;
        width: max-content;
        max-width: min(260px, 80vw);
        padding: 10px 14px;
        border-radius: 10px;
        background: #0d1f33;
        color: #fff;
        font-size: 12.5px;
        font-weight: 500;
        line-height: 1.45;
        text-align: left;
        white-space: normal;
        box-shadow: 0 12px 28px -10px rgba(13, 31, 51, 0.55);
        opacity: 0;
        visibility: hidden;
        transform: translateY(6px) scale(0.96);
        transform-origin: calc(100% - 14px) 100%;
        transition: opacity 0.25s ease, transform 0.25s ease, visibility 0s linear 0.25s;
        pointer-events: none;
    }

    .attn-tip i {
        margin-top: 2px;
        color: #e8d4a0;
    }

    /* Little arrow pointing down at the dot */
    .attn-tip::after {
        content: '';
        position: absolute;
        top: 100%;
        right: 12px;
        border: 7px solid transparent;
        border-top-color: #0d1f33;
    }

    .attn-anchor:hover .attn-tip,
    .attn-anchor:focus-visible .attn-tip,
    .attn-tip.is-shown {
        opacity: 1;
        visibility: visible;
        transform: translateY(0) scale(1);
        transition: opacity 0.25s ease, transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), visibility 0s;
    }

    @media (prefers-reduced-motion: reduce) {
        .attn-tip {
            transform: none !important;
            transition: opacity 0.2s ease, visibility 0s linear 0.2s;
        }
    }

    @media (prefers-reduced-motion: no-preference) {
        .attn-dot,
        .attn-pill,
        .attn-count {
            animation: attn-pop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.35s both;
        }

        .attn-dot::after,
        .attn-count::after {
            content: '';
            position: absolute;
            inset: 0;
            z-index: -1;
            border-radius: inherit;
            background: inherit;
            animation: attn-ping 2.6s cubic-bezier(0, 0, 0.2, 1) 1s infinite;
            pointer-events: none;
        }

        /* Number badges are wider, so their ring grows less. */
        .attn-count::after {
            animation-name: attn-ping-soft;
        }

        /* The bell rings once when there is something unread. */
        .attn-ring {
            transform-origin: 50% 4px;
            animation: attn-ring 1s ease 0.6s 1;
        }

        @keyframes attn-pop {
            0% { transform: scale(0); }
            100% { transform: scale(1); }
        }

        @keyframes attn-ping {
            0% { transform: scale(1); opacity: 0.55; }
            70%, 100% { transform: scale(2.3); opacity: 0; }
        }

        @keyframes attn-ping-soft {
            0% { transform: scale(1); opacity: 0.5; }
            70%, 100% { transform: scale(1.6); opacity: 0; }
        }

        @keyframes attn-ring {
            0%, 100% { transform: rotate(0); }
            12% { transform: rotate(16deg); }
            24% { transform: rotate(-13deg); }
            36% { transform: rotate(9deg); }
            48% { transform: rotate(-6deg); }
            60% { transform: rotate(3deg); }
            72% { transform: rotate(0); }
        }
    }
</style>

<script>
    /*
     * Explain a new dot without waiting for a hover: the first
     * tip on the page slides in for a few seconds, once per visit.
     */
    document.addEventListener('DOMContentLoaded', function () {
        var tip = document.querySelector('.attn-tip[data-auto]');

        if (! tip) {
            return;
        }

        var key = 'cpbn-tip:' + (tip.dataset.auto || 'tip');

        try {
            if (sessionStorage.getItem(key)) {
                return;
            }

            sessionStorage.setItem(key, '1');
        } catch (error) {
            /* Storage blocked: still show it, just not remembered. */
        }

        setTimeout(function () {
            tip.classList.add('is-shown');
        }, 900);

        setTimeout(function () {
            tip.classList.remove('is-shown');
        }, 6900);
    });
</script>
