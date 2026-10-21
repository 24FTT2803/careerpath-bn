{{--
    Shared look for the student pages: the same banner and card
    styling as the BIICF Explorer and the terms/privacy pages, so
    the site reads as one product.

    Only presentation lives here. Each page keeps its own markup,
    scripts and data, and the site-wide motion (partials.motion)
    still reveals the content as before.

    Included at the end of the student layout, after each page's
    own styles. Selectors are tied to each page's own wrapper, so
    nothing else on the site is affected.
--}}
<style>
    /* ---------- Banner (matches the explorer's .bx-hero) ---------- */
    body .dashboard-header,
    body .recommendations-header,
    body .milestone-page .head,
    body .lab-header,
    body .adviser-header,
    body .settings-header,
    body .notif-header,
    body .edit-profile .head,
    body .profile-header,
    body .history-header {
        position: relative;
        overflow: hidden;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin: 0 0 24px;
        padding: 28px 32px;
        border: none;
        border-radius: 18px;
        color: #fff;
        background:
            radial-gradient(circle at 92% 0%, rgba(201, 168, 76, 0.28), transparent 45%),
            linear-gradient(135deg, #0d1f33 0%, #1a3a5c 55%, #2a5a8c 100%);
        box-shadow: 0 24px 50px -32px rgba(13, 31, 51, 0.7);
    }

    body .dashboard-header::after,
    body .recommendations-header::after,
    body .milestone-page .head::after,
    body .lab-header::after,
    body .adviser-header::after,
    body .settings-header::after,
    body .notif-header::after,
    body .edit-profile .head::after,
    body .profile-header::after,
    body .history-header::after {
        content: '';
        position: absolute;
        right: -60px;
        bottom: -90px;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        border: 40px solid rgba(255, 255, 255, 0.04);
        pointer-events: none;
    }

    body .dashboard-header > *,
    body .recommendations-header > *,
    body .milestone-page .head > *,
    body .lab-header > *,
    body .adviser-header > *,
    body .settings-header > *,
    body .notif-header > *,
    body .edit-profile .head > *,
    body .profile-header > *,
    body .history-header > * {
        position: relative;
        z-index: 1;
        min-width: 0;
    }

    /* Titles */
    body .dashboard-header h1,
    body .dashboard-greeting .greeting-line,
    body .recommendations-header h1,
    body .milestone-page .head h1,
    body .lab-heading,
    body .adviser-header h1,
    body .settings-header h1,
    body .notif-header h1,
    body .edit-profile .head h1,
    body .profile-header h1,
        body .history-heading {
        margin: 0 0 6px;
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        line-height: 1.2;
        color: #fff;
    }

    body .dashboard-header h1 span,
    body .dashboard-greeting .greeting-name,
    body .milestone-page .head h1 span,
    body .settings-header h1 span,
    body .notif-header h1 span,
    body .edit-profile .head h1 span,
    body .profile-header h1 span,
    body .lab-heading i {
        color: #e8d4a0;
    }

    /* Subtitles */
    body .dashboard-header .subtitle,
    body .recommendations-header p,
    body .milestone-page .head .sub,
    body .lab-sub,
    body .adviser-header p,
    body .settings-header .subtitle,
    body .notif-header .subtitle,
    body .edit-profile .head .sub,
    body .profile-header .subtitle,
    body .history-subheading {
        max-width: 560px;
        margin: 0;
        font-size: 14px;
        line-height: 1.6;
        color: rgba(255, 255, 255, 0.78);
    }

    /* Small chips inside the banner */
    body .adviser-eyebrow,
    body .dashboard-header .badge-chip,
    body .adviser-preview-badge {
        padding: 5px 12px;
        border-radius: 999px;
        background: rgba(201, 168, 76, 0.16);
        border: 1px solid rgba(232, 212, 160, 0.35);
        color: #e8d4a0;
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    body .adviser-eyebrow {
        margin-bottom: 10px;
    }

    body .dashboard-header .badge-chip.primary {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.22);
        color: #fff;
    }

    /* Links and buttons that sit in a banner */
    body .edit-profile .head .back {
        padding: 8px 14px;
        border: 1px solid rgba(255, 255, 255, 0.28);
        border-radius: 999px;
        color: #fff;
        background: rgba(255, 255, 255, 0.08);
        transition: background 0.2s ease;
    }

    body .edit-profile .head .back:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.18);
    }

    /* Profile page buttons inside the banner */
    body .profile-header .action-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    body .profile-header .btn-outline {
        border: 1px solid rgba(255, 255, 255, 0.35);
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
    }

    body .profile-header .btn-outline:hover {
        background: rgba(255, 255, 255, 0.18);
        color: #fff;
    }

    body .profile-header .btn-primary {
        background: #c9a84c;
        border-color: #c9a84c;
        color: #0d1f33;
    }

    /* ---------- Cards (matches the explorer's .bx-card) ---------- */
    body .dashboard .stat-card,
    body .dashboard .panel,
    body .profile-page .panel,
    body .profile-page .info-card,
    body .milestone-page .stat-card,
    body .milestone-page .milestone-list,
    body .settings-page .panel,
    body .generation-panel,
    body .recommendation-card,
    body .history-card,
    body .lab-note,
    body .edit-profile .progress-card,
    body .edit-profile .section,
    body .notif-page .notif-list {
        border: 1px solid #e6e8ec;
        border-radius: 16px;
        box-shadow: 0 10px 30px -26px rgba(13, 31, 51, 0.45);
    }

    body .dashboard .stat-card,
    body .milestone-page .stat-card,
    body .recommendation-card,
    body .history-card,
    body .lab-note {
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }

    body .dashboard .stat-card:hover,
    body .milestone-page .stat-card:hover,
    body .recommendation-card:hover,
    body .history-card:hover,
    body .lab-note:hover {
        transform: translateY(-2px);
        border-color: #e8d4a0;
        box-shadow: 0 18px 36px -24px rgba(13, 31, 51, 0.5);
    }

    /* Section headings get the explorer's gold marker */
    body .history-section-title,
    body .section-heading h2 {
        padding-left: 10px;
        border-left: 4px solid #c9a84c;
    }

    @media (max-width: 720px) {
        body .dashboard-header,
        body .recommendations-header,
        body .milestone-page .head,
        body .lab-header,
        body .adviser-header,
        body .settings-header,
        body .notif-header,
        body .edit-profile .head,
        body .profile-header,
        body .history-header {
            padding: 22px 18px;
            border-radius: 16px;
        }

        body .dashboard-header h1,
        body .dashboard-greeting .greeting-line,
        body .recommendations-header h1,
        body .milestone-page .head h1,
        body .lab-heading,
        body .adviser-header h1,
        body .settings-header h1,
        body .notif-header h1,
        body .edit-profile .head h1,
        body .profile-header h1,
        body .history-heading {
            font-size: 22px;
        }
    }
    /* ======================================================
       Buttons that sit inside a banner: gold on navy
       ====================================================== */
    body .milestone-page .head .btn-primary,
    body .notif-header .btn-primary {
        background: #c9a84c;
        border: 1px solid #c9a84c;
        color: #0d1f33;
        font-weight: 700;
    }

    body .milestone-page .head .btn-primary:hover,
    body .notif-header .btn-primary:hover {
        background: #e8d4a0;
        border-color: #e8d4a0;
        color: #0d1f33;
    }

    /* ======================================================
       Career Recommendations
       ====================================================== */
    body .generation-panel {
        position: relative;
        padding: 22px 24px 22px 28px;
        background: linear-gradient(180deg, #ffffff 0%, #faf6ea 140%);
    }

    body .generation-panel::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 5px;
        border-radius: 16px 0 0 16px;
        background: linear-gradient(180deg, #c9a84c, #e8d4a0);
    }

    body .generation-panel h2 {
        font-size: 19px;
        font-weight: 700;
        color: #1a3a5c;
    }

    body .generate-button {
        border-radius: 10px;
        background: linear-gradient(135deg, #1a3a5c 0%, #2a5a8c 100%);
        box-shadow: 0 10px 22px -14px rgba(13, 31, 51, 0.8);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    /* The tooltip lives inside the button, so it must restore normal wrapping */
    body .generate-button .stale-why {
        width: 260px;
        white-space: normal;
        overflow-wrap: break-word;
        text-align: left;
        font-weight: 500;
        letter-spacing: normal;
        text-transform: none;
    }

    body .generate-button:not(:disabled):hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 28px -16px rgba(13, 31, 51, 0.85);
    }

    body .recommendation-card {
        position: relative;
        overflow: hidden;
        padding: 20px 20px 18px;
    }

    body .recommendation-card::before {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        top: 0;
        height: 4px;
        background: linear-gradient(90deg, #c9a84c, #e8d4a0);
    }

    body .recommendation-rank {
        display: inline-block;
        padding: 4px 11px;
        border-radius: 999px;
        background: #faf6ea;
        border: 1px solid #e8d4a0;
        color: #8a6420;
    }

    body .recommendation-card h3 {
        font-weight: 700;
        color: #1a3a5c;
    }

    body .match-score {
        display: inline-flex;
        align-items: center;
        padding: 4px 11px;
        border-radius: 999px;
        background: #e8f5ee;
        color: #1f8a5b;
    }

    body .analysis-link {
        padding: 6px 12px;
        border-radius: 999px;
        background: #f6f7f9;
        transition: background 0.2s ease, color 0.2s ease;
    }

    body .analysis-link:hover {
        background: #faf6ea;
        color: #1a3a5c;
    }

    body .empty-recommendations {
        border: 1px dashed #e8d4a0;
        border-radius: 16px;
        background: linear-gradient(180deg, #ffffff, #faf6ea);
    }

    /* ======================================================
       Career Analysis
       ====================================================== */
    body .cpbn-analysis {
        --font-display: 'Inter', -apple-system, sans-serif;
        --font-body: 'Inter', -apple-system, sans-serif;
        background: var(--bg, #f4f6f9);
    }

    body .cpbn-hero {
        position: relative;
        overflow: hidden;
        padding: 30px 32px;
        border: none;
        border-radius: 18px;
        color: #fff;
        background:
            radial-gradient(circle at 92% 0%, rgba(201, 168, 76, 0.28), transparent 45%),
            linear-gradient(135deg, #0d1f33 0%, #1a3a5c 55%, #2a5a8c 100%);
        box-shadow: 0 24px 50px -32px rgba(13, 31, 51, 0.7);
    }

    body .cpbn-hero::after {
        content: '';
        position: absolute;
        right: -60px;
        bottom: -90px;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        border: 40px solid rgba(255, 255, 255, 0.04);
        pointer-events: none;
    }

    body .cpbn-hero > * {
        position: relative;
        z-index: 1;
    }

    body .cpbn-hero h1 {
        font-family: 'Playfair Display', serif;
        color: #fff;
        font-weight: 700;
    }

    body .cpbn-subsector {
        color: rgba(255, 255, 255, 0.78);
    }

    body .cpbn-metric {
        min-width: 130px;
        padding: 12px 16px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.14);
    }

    body .cpbn-metric strong {
        font-family: 'Playfair Display', serif;
        font-weight: 700;
        color: #fff;
    }

    body .cpbn-metric span {
        color: rgba(255, 255, 255, 0.72);
    }

    body .cpbn-section {
        padding: 26px;
        border: 1px solid #e6e8ec;
        border-radius: 16px;
        box-shadow: 0 10px 30px -26px rgba(13, 31, 51, 0.45);
    }

    body .cpbn-section h2 {
        padding-left: 10px;
        border-left: 4px solid #c9a84c;
        color: #1a3a5c;
        font-weight: 700;
    }

    body .cpbn-gap {
        border-radius: 12px;
        border-color: #e6e8ec;
    }

    body .cpbn-gap-value {
        border-radius: 8px;
    }

    body .cpbn-back {
        padding: 7px 14px;
        border: 1px solid #e6e8ec;
        border-radius: 999px;
        background: #fff;
        color: #1a3a5c;
        transition: border-color 0.2s ease, background 0.2s ease;
    }

    body .cpbn-back:hover {
        border-color: #e8d4a0;
        background: #faf6ea;
    }

    /* ======================================================
       Edit Profile
       ====================================================== */
    body .edit-profile .section,
    body .edit-profile .progress-card {
        padding: 26px;
        border-color: #e6e8ec;
        border-radius: 16px;
    }

    body .edit-profile .progress-card {
        padding: 20px 24px;
    }

    body .edit-profile .section .title {
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid #eef0f3;
        font-size: 18px;
        font-weight: 700;
        color: #1a3a5c;
    }

    body .edit-profile .section .title svg {
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        padding: 8px;
        border-radius: 10px;
        background: linear-gradient(135deg, #1a3a5c, #2a5a8c);
        color: #e8d4a0;
    }

    body .edit-profile .bar {
        height: 10px;
        background: #eef0f3;
    }

    body .edit-profile .bar .fill {
        background: linear-gradient(90deg, #c9a84c, #e8d4a0);
    }

    body .edit-profile .cpbn-competency-item,
    body .edit-profile .cpbn-certification-card {
        border-color: #e6e8ec;
        border-radius: 12px;
    }

    body .edit-profile .cpbn-competency-item.selected {
        border-color: #e8d4a0;
        background: #faf6ea;
    }

    body .edit-profile input:not([type="checkbox"]):not([type="radio"]):not([type="file"]):focus,
    body .edit-profile select:focus,
    body .edit-profile textarea:focus {
        border-color: #c9a84c;
        outline: none;
        box-shadow: 0 0 0 4px rgba(201, 168, 76, 0.16);
    }

    /* ======================================================
       Notifications
       ====================================================== */
    body .notif-list {
        border-color: #e6e8ec;
        border-radius: 16px;
        box-shadow: 0 10px 30px -26px rgba(13, 31, 51, 0.45);
    }

    body .notif-item {
        padding: 18px 22px;
    }

    body .notif-item:last-child {
        border-bottom: none;
    }

    body .notif-item:hover {
        background: #f6f7f9;
    }

    body .notif-item.unread {
        background: #faf6ea;
        border-left: 4px solid #c9a84c;
    }

    body .notif-item.unread:hover {
        background: #f7f0dc;
    }

    body .notif-banner {
        padding: 14px 20px;
        border: 1px solid #e8d4a0;
        border-radius: 14px;
        background: #faf6ea;
    }

    body .notif-banner i {
        color: #c9a84c;
    }

    /* ======================================================
       My Profile
       ====================================================== */
    body .profile-page .panel {
        padding: 26px;
    }

    body .profile-page .panel-header {
        padding-bottom: 14px;
        border-bottom: 1px solid #eef0f3;
    }

    body .profile-page .panel-header h3 {
        gap: 10px;
        font-size: 17px;
        font-weight: 700;
    }

    body .profile-page .panel-header h3 i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: #faf6ea;
        border: 1px solid #e8d4a0;
        color: #a88830;
        font-size: 14px;
    }

    body .profile-page .completion-card {
        border: 1px solid #e6e8ec;
        border-radius: 16px;
        box-shadow: 0 10px 30px -26px rgba(13, 31, 51, 0.45);
    }

    body .profile-page .completion-card .bar .fill {
        background: linear-gradient(90deg, #c9a84c, #e8d4a0);
    }

    body .profile-page .fact-tile,
    body .profile-page .project-card,
    body .profile-page .cert-card {
        border-color: #e6e8ec;
        border-radius: 14px;
        transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
    }

    body .profile-page .fact-tile:hover,
    body .profile-page .project-card:hover,
    body .profile-page .cert-card:hover {
        transform: translateY(-2px);
        border-color: #e8d4a0;
        box-shadow: 0 14px 28px -22px rgba(13, 31, 51, 0.5);
    }

    body .profile-page .info-row {
        border-bottom-color: #eef0f3;
    }

    @media (prefers-reduced-motion: reduce) {
        body .generate-button,
        body .recommendation-card,
        body .profile-page .fact-tile,
        body .profile-page .project-card,
        body .profile-page .cert-card {
            transition: none;
        }
    }
</style>
