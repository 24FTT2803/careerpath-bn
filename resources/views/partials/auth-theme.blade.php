{{--
    Shared look for the sign-in pages (log in, sign up, forgot and
    reset password, verify email). Design only: every form, field
    and message on those pages is left exactly as it was.

    - The page background reuses the admin dashboard banner: the
      navy gradient, gently shifting, with soft floating circles.
    - The form sits in a white card, beside a navy picture panel
      (hidden on phones, where the form needs the room).
--}}
<style>
    /* ---------- Moving background (from the admin welcome banner) ---------- */
    body {
        position: relative;
        background: linear-gradient(135deg, #1a3a5c 0%, #2a5a8c 50%, #1a3a5c 100%);
        background-size: 220% 220%;
        background-attachment: fixed;
    }

    .auth-backdrop {
        position: fixed;
        inset: 0;
        z-index: 0;
        overflow: hidden;
        pointer-events: none;
    }

    .auth-backdrop span {
        position: absolute;
        border-radius: 50%;
        background: rgba(201, 168, 76, 0.08);
    }

    .auth-backdrop span:nth-child(1) { width: 420px; height: 420px; top: 8%; right: -6%; }
    .auth-backdrop span:nth-child(2) { width: 240px; height: 240px; bottom: 6%; left: 4%; background: rgba(255, 255, 255, 0.05); }
    .auth-backdrop span:nth-child(3) { width: 140px; height: 140px; top: 18%; left: 16%; background: rgba(201, 168, 76, 0.12); }
    .auth-backdrop span:nth-child(4) { width: 300px; height: 300px; bottom: -10%; right: 22%; background: rgba(255, 255, 255, 0.04); }
    .auth-backdrop span:nth-child(5) { width: 70px; height: 70px; top: 62%; left: 46%; background: rgba(201, 168, 76, 0.16); }

    /* The header stays as it is, above the moving background */
    .auth-header {
        position: relative;
        z-index: 2;
    }

    /* ---------- Split card ---------- */
    .auth-main {
        position: relative;
        z-index: 1;
        padding: 48px 24px;
    }

    .auth-shell {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
        width: 100%;
        max-width: 1000px;
        margin: 0 auto;
        border-radius: 20px;
        background: #fff;
        box-shadow:
            0 40px 80px -30px rgba(5, 14, 26, 0.65),
            0 0 0 1px rgba(255, 255, 255, 0.08);
    }

    .auth-shell > .auth-card {
        border: 0;
        border-radius: 20px 0 0 20px;
        box-shadow: none;
        padding: 44px 52px;
        max-width: none;
        width: auto;
        margin: 0;
    }

    /* "Back to Home" on its own line, the page label beneath it */
    .auth-shell .auth-card .back-link {
        display: flex;
        width: max-content;
    }

    /* Gold edge on the input being typed in, like the reference */
    .auth-shell .field .input-wrapper input:focus,
    .auth-shell .field input:focus,
    .auth-shell .field select:focus {
        border-color: #c9a84c;
        box-shadow: 0 0 0 4px rgba(201, 168, 76, 0.14);
    }

    /* ---------- Picture panel ---------- */
    .auth-visual {
        position: relative;
        min-height: 560px;
        border-radius: 0 20px 20px 0;
        color: #fff;
    }

    .auth-visual-media {
        position: absolute;
        inset: 0;
        overflow: hidden;
        border-radius: inherit;
    }

    .auth-visual-photo {
        position: absolute;
        inset: -6%;
        /* Each page has its own illustration (see auth-visual). */
        background:
            linear-gradient(160deg, rgba(13, 31, 51, 0.35) 0%, rgba(26, 58, 92, 0.15) 50%, rgba(13, 31, 51, 0.35) 100%),
            var(--auth-scene, linear-gradient(135deg, #1a3a5c, #2a5a8c)) center / cover no-repeat;
        transition: transform 0.6s cubic-bezier(0.22, 0.61, 0.36, 1);
    }

    /* The bright photos (mountain, padlock) get a deeper navy wash so
       the white badge tile and caption stay easy to read. */
    .auth-visual--register .auth-visual-photo,
    .auth-visual--reset .auth-visual-photo,
    .auth-visual--verify .auth-visual-photo {
        background:
            linear-gradient(160deg, rgba(13, 31, 51, 0.62) 0%, rgba(26, 58, 92, 0.42) 50%, rgba(13, 31, 51, 0.72) 100%),
            var(--auth-scene, linear-gradient(135deg, #1a3a5c, #2a5a8c)) center / cover no-repeat;
    }

    .auth-visual-glow {
        position: absolute;
        width: 420px;
        height: 420px;
        top: -120px;
        right: -120px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(201, 168, 76, 0.35), transparent 65%);
    }

    .auth-visual-grid {
        position: absolute;
        inset: 0;
        background-image: radial-gradient(rgba(255, 255, 255, 0.14) 1px, transparent 1px);
        background-size: 22px 22px;
        mask-image: linear-gradient(180deg, transparent, #000 30%, #000 70%, transparent);
        -webkit-mask-image: linear-gradient(180deg, transparent, #000 30%, #000 70%, transparent);
    }

    .auth-visual-inner {
        position: relative;
        height: 100%;
        min-height: inherit;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 26px;
        padding: 48px 40px;
    }

    /* Frosted tile holding the CareerPath badge */
    .auth-tile-wrap {
        perspective: 900px;
    }

    .auth-tile {
        position: relative;
        width: 230px;
        height: 230px;
        border-radius: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.06));
        border: 1px solid rgba(255, 255, 255, 0.28);
        box-shadow: 0 30px 60px -24px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        overflow: hidden;
        transition: transform 0.45s cubic-bezier(0.22, 0.61, 0.36, 1), box-shadow 0.45s ease;
        transform-style: preserve-3d;
    }

    .auth-tile::before {
        content: '';
        position: absolute;
        top: 16px;
        left: 16px;
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.85);
    }

    /* Three short bars, bottom right, like the reference tile */
    .auth-tile::after {
        content: '';
        position: absolute;
        right: 18px;
        bottom: 18px;
        width: 18px;
        height: 12px;
        background:
            linear-gradient(#e8d4a0, #e8d4a0) 0 0 / 18px 2px no-repeat,
            linear-gradient(#e8d4a0, #e8d4a0) 4px 5px / 14px 2px no-repeat,
            linear-gradient(#e8d4a0, #e8d4a0) 8px 10px / 10px 2px no-repeat;
        opacity: 0.85;
    }

    .auth-tile-badge {
        width: 128px;
        height: 128px;
        border-radius: 50%;
        transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .auth-tile-badge img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        box-shadow: 0 0 0 6px rgba(201, 168, 76, 0.25), 0 18px 34px -12px rgba(0, 0, 0, 0.6);
    }

    .auth-tile-shine {
        position: absolute;
        inset: -40% -60%;
        background: linear-gradient(105deg, transparent 40%, rgba(255, 255, 255, 0.35) 50%, transparent 60%);
        transform: translateX(-70%);
        pointer-events: none;
    }

    .auth-visual:hover .auth-tile {
        box-shadow: 0 40px 70px -24px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(201, 168, 76, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.3);
    }

    .auth-visual:hover .auth-tile-badge {
        transform: rotate(-12deg) scale(1.06);
    }

    .auth-visual-caption {
        max-width: 300px;
        margin: 0;
        text-align: center;
        font-size: 13px;
        line-height: 1.6;
        color: rgba(255, 255, 255, 0.78);
    }

    .auth-visual-caption strong {
        display: block;
        margin-bottom: 4px;
        font-family: 'Playfair Display', serif;
        font-size: 20px;
        font-weight: 700;
        color: #fff;
    }

    .auth-visual-caption strong span {
        color: #e8d4a0;
    }

    /* Gold circle sitting on the seam between the two halves */
    .auth-seam {
        position: absolute;
        top: 50%;
        left: 0;
        z-index: 3;
        width: 54px;
        height: 54px;
        margin: -27px 0 0 -27px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #c9a84c, #e8d4a0);
        color: #0d1f33;
        font-size: 18px;
        box-shadow: 0 10px 24px -8px rgba(13, 31, 51, 0.55), 0 0 0 6px #fff;
    }

    /* ---------- Motion ---------- */
    @media (prefers-reduced-motion: no-preference) {
        body {
            animation: auth-bg-shift 18s ease-in-out infinite;
        }

        .auth-backdrop span {
            animation: auth-float 6s ease-in-out infinite;
        }

        .auth-backdrop span:nth-child(2) { animation-delay: 2s; }
        .auth-backdrop span:nth-child(3) { animation-delay: 4s; }
        .auth-backdrop span:nth-child(4) { animation-delay: 1s; animation-duration: 8s; }
        .auth-backdrop span:nth-child(5) { animation-delay: 3s; animation-duration: 5s; }

        .auth-visual-photo {
            animation: auth-zoom 24s ease-in-out infinite alternate;
        }

        .auth-tile-wrap {
            animation: auth-bob 5s ease-in-out infinite;
        }

        .auth-visual:hover .auth-tile-shine {
            animation: auth-shine 1.1s ease forwards;
        }

        .auth-seam {
            animation: auth-pulse 2.8s ease-in-out infinite;
        }

        .auth-seam i {
            animation: auth-spin 12s linear infinite;
        }

        @keyframes auth-bg-shift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        @keyframes auth-float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(20px, -30px) scale(1.1); }
        }

        @keyframes auth-zoom {
            from { transform: scale(1); }
            to { transform: scale(1.08); }
        }

        @keyframes auth-bob {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes auth-shine {
            from { transform: translateX(-70%); }
            to { transform: translateX(70%); }
        }

        @keyframes auth-pulse {
            0%, 100% { box-shadow: 0 10px 24px -8px rgba(13, 31, 51, 0.55), 0 0 0 6px #fff, 0 0 0 6px rgba(201, 168, 76, 0.5); }
            50% { box-shadow: 0 10px 24px -8px rgba(13, 31, 51, 0.55), 0 0 0 6px #fff, 0 0 0 16px rgba(201, 168, 76, 0); }
        }

        @keyframes auth-spin {
            to { transform: rotate(1turn); }
        }
    }

    /* ---------- Smaller screens ---------- */
    @media (max-width: 900px) {
        .auth-shell {
            grid-template-columns: minmax(0, 1fr);
            max-width: 520px;
        }

        .auth-shell > .auth-card {
            border-radius: 20px;
            padding: 36px 32px;
        }

        /* The form needs the room on a phone; the picture is decoration. */
        .auth-visual {
            display: none;
        }
    }

    /* Same header size on every sign-in page (forgot/reset lacked it). */
    @media (max-width: 768px) {
        .auth-header { padding: 14px 0; }
        .auth-logo,
        .auth-header img[src*="careerpath-logo"] { height: 34px !important; }
        .auth-main { padding: 28px 18px; }
    }

    @media (max-width: 480px) {
        .auth-header { padding: 10px 0; }
        .auth-logo,
        .auth-header img[src*="careerpath-logo"] { height: 28px !important; }
        .auth-main { padding: 18px 12px; }
        .auth-shell { border-radius: 16px; }
        .auth-shell > .auth-card { border-radius: 16px; padding: 28px 20px; }
    }
</style>
