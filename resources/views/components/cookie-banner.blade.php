@php
    /*
     * Rendering is guarded by the cookie itself rather than by
     * server-side session state, so the banner never flickers
     * on first paint and works for guests too.
     */
@endphp

<div
    id="cookieBanner"
    class="cookie-banner"
    hidden
    role="dialog"
    aria-live="polite"
    aria-label="Cookie notice"
>
    <div class="cookie-banner-inner">
        <div class="cookie-banner-text">
            <strong>Cookies</strong>

            <p>
                This site uses only the cookies needed to keep
                you logged in and to protect your forms. We do
                not track you or share your data.
                <a href="{{ route('privacy') }}">Read our Privacy Policy</a>.
            </p>
        </div>

        <button
            type="button"
            id="cookieBannerAccept"
            class="cookie-banner-accept"
        >
            Accept
        </button>
    </div>
</div>

<style>
    .cookie-banner {
        position: fixed;
        left: 24px;
        right: 24px;
        bottom: 24px;
        z-index: 950;
        max-width: 560px;
        margin-inline: auto;

        background: #ffffff;
        border: 1px solid var(--border, #e5e7eb);
        border-radius: 12px;
        box-shadow: 0 12px 40px rgba(26, 58, 92, 0.18);

        padding: 18px 20px;
        font-size: 13px;
        line-height: 1.55;
        color: var(--text, #1a1a2e);
    }

    .cookie-banner[hidden] {
        display: none;
    }

    .cookie-banner-inner {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 18px;
        flex-wrap: wrap;
    }

    .cookie-banner-text {
        flex: 1;
        min-width: 220px;
    }

    .cookie-banner-text strong {
        display: block;
        font-size: 14px;
        margin-bottom: 4px;
        color: var(--primary, #1a3a5c);
    }

    .cookie-banner-text p {
        margin: 0;
        color: var(--text-muted, #6b7280);
    }

    .cookie-banner-text a {
        color: var(--primary-light, #2a5a8c);
        text-decoration: underline;
        font-weight: 600;
    }

    .cookie-banner-text a:hover {
        color: var(--accent, #c9a84c);
    }

    .cookie-banner-accept {
        flex-shrink: 0;

        background: var(--primary, #1a3a5c);
        color: #ffffff;
        border: none;
        border-radius: 8px;

        padding: 10px 22px;
        font-size: 13px;
        font-weight: 600;
        font-family: inherit;

        cursor: pointer;
        transition: background 0.2s ease;
    }

    .cookie-banner-accept:hover {
        background: var(--primary-light, #2a5a8c);
    }

    @media (max-width: 520px) {
        .cookie-banner {
            left: 14px;
            right: 14px;
            bottom: 14px;
            padding: 16px;
        }

        .cookie-banner-inner {
            align-items: stretch;
        }

        .cookie-banner-accept {
            width: 100%;
        }
    }
</style>

<script>
(function () {
    /*
     * The cookie stores a single flag. Anything that needs to
     * know whether consent was given can read the same cookie.
     */
    const COOKIE_NAME = 'cookie_consent';
    const COOKIE_VALUE = 'accepted';
    const COOKIE_DAYS = 365;

    const banner = document.getElementById('cookieBanner');
    const accept = document.getElementById('cookieBannerAccept');

    if (!banner || !accept) {
        return;
    }

    const hasConsent = document.cookie
        .split('; ')
        .some((row) => row.startsWith(COOKIE_NAME + '='));

    if (hasConsent) {
        return;
    }

    /*
     * Shown only after the decision has been made, so a
     * returning visitor never sees a flash of the banner
     * before the script runs.
     */
    banner.hidden = false;

    accept.addEventListener('click', function () {
        const expires = new Date(
            Date.now() + COOKIE_DAYS * 864e5
        ).toUTCString();

        document.cookie =
            COOKIE_NAME
            + '='
            + COOKIE_VALUE
            + '; expires='
            + expires
            + '; path=/'
            + '; SameSite=Lax';

        banner.hidden = true;
    });
})();
</script>