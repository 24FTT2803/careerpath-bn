@extends('layouts.app')

@section('title', 'BIICF Explorer')

@section('content')

{{--
    BIICF Explorer. Same sections, data and steps as before (pick a
    sub-sector, choose a job role, then look at its career path,
    competencies, entry requirements and training); this file only
    sets how they look.
--}}
<style>
    [x-cloak] { display: none !important; }

    .biicf-page {
        --bx-navy: #0d1f33;
        --bx-navy-2: #1a3a5c;
        --bx-navy-3: #2a5a8c;
        --bx-gold: #c9a84c;
        --bx-gold-soft: #e8d4a0;
        --bx-gold-wash: #faf6ea;
        --bx-line: #e6e8ec;
        --bx-soft: #f6f7f9;
        --bx-green: #1f8a5b;
        --bx-green-wash: #e8f5ee;
        --bx-amber: #a5670c;
        --bx-amber-wash: #fdf3e1;
        padding: 24px 0 48px;
    }

    /* ---------- Banner ---------- */
    .bx-hero {
        position: relative;
        overflow: hidden;
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
        gap: 28px;
        align-items: center;
        margin-bottom: 24px;
        padding: 30px 32px;
        border-radius: 18px;
        color: #fff;
        background:
            radial-gradient(circle at 92% 0%, rgba(201, 168, 76, 0.28), transparent 45%),
            linear-gradient(135deg, var(--bx-navy) 0%, var(--bx-navy-2) 55%, var(--bx-navy-3) 100%);
        box-shadow: 0 24px 50px -32px rgba(13, 31, 51, 0.7);
    }

    .bx-hero::after {
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

    .bx-hero h1 {
        margin: 10px 0 6px;
        font-family: 'Playfair Display', serif;
        font-size: 30px;
        font-weight: 700;
        line-height: 1.2;
        color: #fff;
    }

    .bx-hero h1 span { color: var(--bx-gold-soft); }

    .bx-hero p {
        max-width: 560px;
        color: rgba(255, 255, 255, 0.78);
        font-size: 14px;
        line-height: 1.6;
    }

    .bx-chip {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 5px 12px;
        border-radius: 999px;
        background: rgba(201, 168, 76, 0.16);
        border: 1px solid rgba(232, 212, 160, 0.35);
        color: var(--bx-gold-soft);
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .bx-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }

    .bx-stat {
        min-width: 104px;
        padding: 10px 14px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
    }

    .bx-stat strong {
        display: block;
        font-family: 'Playfair Display', serif;
        font-size: 22px;
        line-height: 1.1;
        color: #fff;
    }

    .bx-stat span {
        font-size: 11.5px;
        color: rgba(255, 255, 255, 0.7);
    }

    /* How it works */
    .bx-steps {
        position: relative;
        z-index: 1;
        display: grid;
        gap: 10px;
        padding: 16px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.12);
    }

    .bx-steps-title {
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.6);
    }

    .bx-step {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 13.5px;
        color: rgba(255, 255, 255, 0.82);
    }

    .bx-step-num {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, 0.3);
        font-size: 12.5px;
        font-weight: 700;
        transition: background 0.25s ease, color 0.25s ease, border-color 0.25s ease;
    }

    .bx-step.is-done .bx-step-num {
        background: var(--bx-gold);
        border-color: var(--bx-gold);
        color: var(--bx-navy);
    }

    .bx-step small {
        display: block;
        color: rgba(255, 255, 255, 0.55);
        font-size: 12px;
    }

    /* ---------- Layout ---------- */
    .biicf-grid {
        display: grid;
        grid-template-columns: 270px minmax(0, 1fr);
        gap: 24px;
        align-items: start;
    }

    .biicf-sidebar {
        position: sticky;
        top: 96px;
        display: grid;
        gap: 14px;
    }

    .bx-card {
        background: var(--card, #fff);
        border: 1px solid var(--bx-line);
        border-radius: 16px;
        box-shadow: 0 10px 30px -26px rgba(13, 31, 51, 0.45);
    }

    /* Selected role, above the menu */
    .bx-current {
        padding: 16px;
    }

    .bx-current-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-muted);
    }

    .bx-current-role {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 10px;
    }

    .bx-current-icon {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--bx-navy-2), var(--bx-navy-3));
        color: var(--bx-gold-soft);
    }

    .bx-current-role strong {
        display: block;
        color: var(--primary);
        font-size: 14px;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .bx-current-role span {
        font-size: 12px;
        color: var(--text-muted);
    }

    .bx-current-empty {
        margin-top: 8px;
        font-size: 12.5px;
        line-height: 1.5;
        color: var(--text-muted);
    }

    /* Menu */
    .biicf-sidebar .nav-group {
        padding: 10px;
    }

    .bx-nav-heading {
        padding: 10px 12px 6px;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #9aa3b2;
    }

    .biicf-sidebar .nav-item {
        position: relative;
        display: flex;
        align-items: center;
        gap: 11px;
        width: 100%;
        padding: 10px 12px;
        border: none;
        border-radius: 10px;
        background: transparent;
        color: #4b5563;
        font-family: inherit;
        font-size: 13.5px;
        font-weight: 500;
        text-align: left;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .biicf-sidebar .nav-item > i {
        width: 18px;
        text-align: center;
        color: #9aa3b2;
        transition: color 0.2s ease;
    }

    .biicf-sidebar .nav-item:hover {
        background: var(--bx-soft);
        color: var(--primary);
    }

    .biicf-sidebar .nav-item.active {
        background: var(--bx-gold-wash);
        color: var(--primary);
        font-weight: 600;
    }

    .biicf-sidebar .nav-item.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 9px;
        bottom: 9px;
        width: 3px;
        border-radius: 3px;
        background: var(--bx-gold);
    }

    .biicf-sidebar .nav-item.active > i { color: var(--bx-gold); }

    .biicf-sidebar .nav-item .count {
        margin-left: auto;
        padding: 1px 9px;
        border-radius: 999px;
        background: var(--bx-soft);
        color: var(--text-muted);
        font-size: 11.5px;
        font-weight: 600;
    }

    .biicf-sidebar .nav-item.active .count {
        background: #fff;
        color: var(--primary);
    }

    .biicf-sidebar .nav-item .needs-role {
        margin-left: auto;
        font-size: 10.5px;
        color: #b0b7c3;
        font-weight: 500;
    }

    /* Content */
    .biicf-content {
        min-width: 0;
        padding: 26px 28px 30px;
    }

    .breadcrumb-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 22px;
        padding: 12px 14px 12px 16px;
        border-radius: 12px;
        background: var(--bx-soft);
        border: 1px solid var(--bx-line);
    }

    .breadcrumb-bar .crumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        min-width: 0;
        font-size: 13.5px;
        color: var(--text-muted);
    }

    .breadcrumb-bar .crumb i { color: var(--bx-gold); }
    .breadcrumb-bar .sep { color: #c3c8d0; }

    .breadcrumb-bar .current {
        color: var(--primary);
        font-weight: 600;
    }

    .change-role-btn {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 14px;
        border-radius: 9px;
        border: 1px solid var(--bx-line);
        background: #fff;
        color: var(--primary);
        font-family: inherit;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        transition: border-color 0.2s ease, background 0.2s ease;
    }

    .change-role-btn:hover {
        border-color: var(--bx-gold);
        background: var(--bx-gold-wash);
    }

    /* Section heading */
    .bx-section-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 18px;
    }

    .bx-section-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .bx-section-icon {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: var(--bx-gold-wash);
        color: var(--bx-gold);
        font-size: 17px;
    }

    /*
     * Only the classes master's redesign does not style.
     *
     * The merge kept both stylesheets and put ours second, so
     * for every class both sides write the old rules won: the
     * sub-sector tiles, a role's level badge, the career path's
     * nodes. The redesign draws those now, and what stays here
     * is what it has never heard of.
     */


    /*
     * Every list in the explorer is marked the same way: a gold
     * disc at the top level and a hollow one for anything nested
     * under it. The role overview was the only section drawing
     * markers at all, which made every other list read as
     * though its lines had been run together.
     */
    .overview-card ul,
    .level-statements,
    .req-list {
        margin: 0;
        padding-left: 20px;
        list-style: disc;
    }

    .overview-card ul ul,
    .overview-card ul ul ul {
        margin: 4px 0 8px;
        list-style: circle;
    }

    .overview-card li,
    .level-statements li,
    .req-list li {
        font-size: 13px;
        margin-bottom: 4px;
        line-height: 1.5;
    }

    .overview-card li::marker,
    .level-statements li::marker,
    .req-list li::marker {
        color: var(--accent);
    }

    .filter-pill:hover:not(.active) {
        border-color: var(--accent-light);
    }

    @keyframes biicf-spin {
        to { transform: rotate(360deg); }
    }

    .role-item .level {
        font-size: 12px;
        color: var(--text-muted);
        background: var(--bg);
        padding: 2px 12px;
        border-radius: 100px;
        white-space: nowrap;
    }

    .career-path .node.prev {
        background: var(--bg);
        color: var(--text-muted);
    }

    /* Competencies */
    .comp-group {
        margin-bottom: 20px;
    }

    .comp-item .name {
        font-weight: 500;
    }

    .comp-item .status {
        font-size: 11px;
        font-weight: 500;
        padding: 2px 12px;
        border-radius: 100px;
    }

    .comp-item .status.met {
        background: rgba(45, 143, 92, 0.12);
        color: var(--success);
    }

    .comp-item .status.missing {
        background: rgba(192, 57, 43, 0.08);
        color: var(--danger);
    }

    .comp-item .status.not-logged {
        background: var(--bg);
        color: var(--text-muted);
    }

    .comp-item .status.core {
        background: rgba(26, 58, 92, 0.06);
        color: var(--primary);
    }

    .comp-item .status.level {
        background: rgba(122, 94, 168, 0.1);
        color: #6b4ea0;
        margin-left: 6px;
    }

    /* A competency opens in Proficiency Levels. */
    .comp-item.is-clickable {
        cursor: pointer;
        transition: border-color 0.15s ease, background 0.15s ease;
    }

    .comp-item.is-clickable:hover {
        border-color: var(--accent);
        background: var(--bg);
    }

    .comp-item .comp-go {
        font-size: 12px;
        color: var(--text-muted);
        margin-left: 10px;
    }

    .comp-item.is-clickable:hover .comp-go {
        color: var(--accent);
    }

    /* What the framework asks at one rung of one competency. */
    .prof-level .required-flag {
        font-size: 10px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 100px;
        background: var(--accent);
        color: #0d1f33;
        text-transform: none;
        letter-spacing: 0;
    }

    .prof-level.required {
        border-left: 3px solid var(--accent);
        background: rgba(201, 168, 76, 0.07);
    }

    .prof-level.absent {
        opacity: 0.65;
    }

    .level-statements {
        margin: 8px 0 0;
        padding-left: 18px;
        font-size: 13px;
        line-height: 1.55;
        color: var(--text);
    }

    .level-statements li {
        margin-bottom: 5px;
    }

    .comp-level-none {
        margin: 6px 0 0;
        font-size: 13px;
        color: var(--text-muted);
        font-style: italic;
    }

    .section-note {
        margin-top: 18px;
        padding: 12px 16px;
        border: 1px dashed var(--border);
        border-radius: 8px;
        font-size: 13px;
        color: var(--text-muted);
    }

    .section-note i {
        color: var(--accent);
        margin-right: 6px;
    }

    .req-tier {
        margin-bottom: 18px;
    }

    .req-tier h4 {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-muted);
        margin: 0 0 8px;
    }

    .req-text {
        margin: 0 0 8px;
        padding: 12px 16px;
        border: 1px solid var(--border);
        border-left: 3px solid var(--accent);
        border-radius: 6px;
        line-height: 1.6;
        font-size: 14px;
    }

    .req-text p {
        margin: 0;
    }

    .req-head {
        display: flex;
        align-items: baseline;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 6px;
    }

    .req-level {
        font-size: 11px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 100px;
        background: rgba(26, 58, 92, 0.08);
        color: var(--primary);
    }

    .req-field {
        font-size: 12px;
        color: var(--text-muted);
    }

    .req-conditions {
        margin-top: 8px;
        font-size: 12px;
        color: var(--text-muted);
    }

    .req-conditions i {
        color: var(--accent);
        margin-right: 4px;
    }

    .filter-pill .pill-level {
        margin-left: 6px;
        padding-left: 6px;
        border-left: 1px solid currentColor;
        opacity: .7;
        font-size: 11px;
    }

    /* Either way in will do, which the wording alone can hide. */
    .req-or {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--text-muted);
        margin: 2px 0 8px 4px;
    }

    /* Which tier of a role's table is being shown. */
    .tier-pills {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 16px;
    }

    .tier-pills-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-muted);
    }

    .req-item:last-child {
        border-bottom: none;
    }

    /* Programming languages */
    .language-note {
        margin: -6px 0 12px;
        font-size: 13px;
        color: var(--text-muted);
    }

    .language-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .language {
        padding: 6px 14px;
        border: 1px solid var(--border);
        border-radius: 999px;
        font-size: 13px;
        font-weight: 500;
        color: var(--primary);
    }

    .prof-scale {
        margin-bottom: 26px;
    }

    .prof-scale-heading {
        display: flex;
        align-items: baseline;
        gap: 8px;
        margin-bottom: 12px;
    }

    .prof-scale-heading h4 {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: var(--primary);
    }

    .prof-scale-heading .scale-note {
        font-size: 12px;
        color: var(--text-muted);
    }

    .compare-result .summary .stat {
        text-align: center;
    }

    .bx-section-title h2 {
        margin: 0;
        font-family: 'Playfair Display', serif;
        font-size: 22px;
        font-weight: 700;
        color: var(--primary);
    }

    .bx-section-title p {
        margin: 2px 0 0;
        font-size: 13px;
        color: var(--text-muted);
    }

    /* Search */
    .search-box {
        position: relative;
        margin-bottom: 16px;
    }

    .search-box i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #9aa3b2;
    }

    .search-box input {
        width: 100%;
        padding: 12px 16px 12px 44px;
        border: 1px solid var(--bx-line);
        border-radius: 12px;
        background: #fff;
        font-family: inherit;
        font-size: 14px;
        color: var(--text);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .search-box input:focus {
        outline: none;
        border-color: var(--bx-gold);
        box-shadow: 0 0 0 4px rgba(201, 168, 76, 0.14);
    }

    .selected-filter {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
        padding: 6px 8px 6px 14px;
        border-radius: 999px;
        background: var(--bx-gold-wash);
        border: 1px solid rgba(201, 168, 76, 0.4);
        font-size: 12.5px;
        font-weight: 600;
        color: var(--primary);
    }

    .selected-filter i { color: var(--bx-gold); }

    .selected-filter .remove {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #fff;
        color: var(--text-muted);
        cursor: pointer;
    }

    .selected-filter .remove:hover { color: var(--danger, #c0392b); }

    /* Sub-sectors */
    .sub-sector-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .sub-sector-card {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        width: 100%;
        padding: 18px;
        border: 1px solid var(--bx-line);
        border-radius: 14px;
        background: #fff;
        font-family: inherit;
        text-align: left;
        cursor: pointer;
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .sub-sector-card:hover {
        transform: translateY(-2px);
        border-color: rgba(201, 168, 76, 0.6);
        box-shadow: 0 14px 28px -20px rgba(13, 31, 51, 0.45);
    }

    .sub-sector-card .icon {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--bx-navy-2), var(--bx-navy-3));
        color: var(--bx-gold-soft);
        font-size: 17px;
    }

    .sub-sector-card .body {
        min-width: 0;
        flex: 1;
    }

    .sub-sector-card .name {
        display: block;
        color: var(--primary);
        font-size: 14.5px;
        font-weight: 600;
        line-height: 1.35;
    }

    .sub-sector-card .desc {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-top: 4px;
        font-size: 12.5px;
        line-height: 1.5;
        color: var(--text-muted);
    }

    .sub-sector-card .count {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 10px;
        font-size: 12px;
        font-weight: 600;
        color: var(--bx-gold);
    }

    .sub-sector-card .count i { transition: transform 0.2s ease; }
    .sub-sector-card:hover .count i { transform: translateX(3px); }

    /* Job roles */
    .role-list {
        display: grid;
        gap: 10px;
    }

    .role-item {
        display: flex;
        align-items: center;
        gap: 14px;
        width: 100%;
        padding: 14px 16px;
        border: 1px solid var(--bx-line);
        border-radius: 12px;
        background: #fff;
        font-family: inherit;
        text-align: left;
        cursor: pointer;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .role-item:hover {
        transform: translateX(2px);
        border-color: rgba(201, 168, 76, 0.6);
        box-shadow: 0 10px 22px -18px rgba(13, 31, 51, 0.5);
    }

    .role-item .lvl {
        flex: 0 0 auto;
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: var(--bx-soft);
        color: var(--primary);
        line-height: 1;
    }

    .role-item .lvl small {
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-muted);
    }

    .role-item .lvl b {
        margin-top: 3px;
        font-size: 17px;
    }

    .role-item .info {
        min-width: 0;
        flex: 1;
    }

    .role-item .title {
        display: block;
        color: var(--primary);
        font-size: 14.5px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .role-item .badges {
        display: block;
        margin-top: 2px;
        font-size: 12px;
        color: var(--text-muted);
    }

    .role-item .go {
        color: #c3c8d0;
        transition: color 0.2s ease, transform 0.2s ease;
    }

    .role-item:hover .go {
        color: var(--bx-gold);
        transform: translateX(3px);
    }

    /* Career path */
    .overview-card {
        margin-bottom: 22px;
        padding: 20px 22px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--bx-gold-wash), #fff);
        border: 1px solid rgba(201, 168, 76, 0.35);
    }

    .overview-card h4 {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 8px;
        font-size: 15px;
        color: var(--primary);
    }

    .overview-card h4 i { color: var(--bx-gold); }

    .overview-card p {
        margin: 0;
        font-size: 14px;
        line-height: 1.7;
        color: #374151;
    }

    .overview-card h5 {
        margin: 16px 0 8px;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-muted);
    }

    .overview-card ul {
        display: grid;
        gap: 6px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .overview-card li {
        position: relative;
        padding-left: 24px;
        font-size: 13.5px;
        line-height: 1.55;
        color: #374151;
    }

    .overview-card li::before {
        content: '\f00c';
        position: absolute;
        left: 0;
        top: 1px;
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        font-size: 11px;
        color: var(--bx-gold);
    }

    .bx-subhead {
        margin: 0 0 12px;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-muted);
    }

    .career-path {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr) auto minmax(0, 1fr);
        align-items: center;
        gap: 12px;
    }

    .path-col {
        display: grid;
        gap: 8px;
        min-width: 0;
    }

    .path-col-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-align: center;
    }

    .career-path .node {
        display: block;
        padding: 12px 14px;
        border-radius: 12px;
        border: 1px solid var(--bx-line);
        background: #fff;
        color: var(--primary);
        font-size: 13px;
        font-weight: 600;
        text-align: center;
        overflow-wrap: anywhere;
    }

    .career-path .node.current {
        padding: 16px 14px;
        border: 0;
        background: linear-gradient(135deg, var(--bx-navy-2), var(--bx-navy-3));
        color: #fff;
        box-shadow: 0 14px 28px -18px rgba(13, 31, 51, 0.7);
    }

    .career-path .node.next {
        background: var(--bx-gold-wash);
        border-color: rgba(201, 168, 76, 0.45);
    }

    .career-path .node.none {
        border-style: dashed;
        color: #9aa3b2;
        font-weight: 500;
    }

    .career-path .arrow {
        color: var(--bx-gold);
        font-size: 16px;
    }

    /* Competencies */
    .compare-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 18px;
        border: 0;
        border-radius: 11px;
        background: linear-gradient(135deg, var(--bx-gold), #d9b968);
        color: var(--bx-navy);
        font-family: inherit;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 10px 22px -14px rgba(201, 168, 76, 0.9);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .compare-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 26px -14px rgba(201, 168, 76, 1);
    }

    .compare-btn:disabled {
        opacity: 0.65;
        cursor: wait;
        transform: none;
    }

    .compare-hint {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
        padding: 14px 16px;
        border-radius: 12px;
        background: #eef3f9;
        border: 1px solid #d9e3ef;
        font-size: 13px;
        line-height: 1.5;
        color: #3b4a5e;
    }

    .compare-hint i { color: var(--bx-navy-3); }

    .compare-result {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 22px;
        align-items: center;
        margin-bottom: 22px;
        padding: 20px 22px;
        border-radius: 14px;
        background: var(--bx-soft);
        border: 1px solid var(--bx-line);
    }

    .bx-ring {
        position: relative;
        width: 104px;
        height: 104px;
    }

    .bx-ring svg {
        width: 100%;
        height: 100%;
        transform: rotate(-90deg);
    }

    .bx-ring circle {
        fill: none;
        stroke-width: 10;
    }

    .bx-ring .track { stroke: #e3e6eb; }

    .bx-ring .bar {
        stroke: var(--bx-green);
        stroke-linecap: round;
        transition: stroke-dashoffset 0.8s cubic-bezier(0.22, 0.61, 0.36, 1);
    }

    .bx-ring-text {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        line-height: 1.1;
    }

    .bx-ring-text strong {
        font-family: 'Playfair Display', serif;
        font-size: 24px;
        color: var(--primary);
    }

    .bx-ring-text span {
        font-size: 11px;
        color: var(--text-muted);
    }

    .compare-result h4 {
        margin: 0 0 4px;
        font-size: 15px;
        color: var(--primary);
    }

    .compare-result .lead {
        margin: 0 0 12px;
        font-size: 13px;
        color: var(--text-muted);
    }

    .compare-result .summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .compare-result .stat {
        padding: 10px 12px;
        border-radius: 10px;
        background: #fff;
        border: 1px solid var(--bx-line);
    }

    .compare-result .stat .number {
        font-size: 20px;
        font-weight: 700;
        line-height: 1.2;
    }

    .compare-result .stat .label {
        font-size: 12px;
        color: var(--text-muted);
    }

    .compare-result .stat.met .number { color: var(--bx-green); }
    .compare-result .stat.below .number { color: var(--bx-amber); }
    .compare-result .stat.missing .number { color: #6b7280; }

    .compare-result .update-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 12px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--bx-navy-3);
        text-decoration: none;
    }

    .compare-result .update-link:hover { text-decoration: underline; }

    .type-filter-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 16px;
    }

    .filter-pill {
        padding: 7px 16px;
        border-radius: 999px;
        border: 1px solid var(--bx-line);
        background: #fff;
        color: var(--text-muted);
        font-family: inherit;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
    }

    .filter-pill:hover { border-color: var(--bx-gold); }

    .filter-pill.active {
        background: var(--primary);
        border-color: var(--primary);
        color: #fff;
    }

    .comp-group + .comp-group { margin-top: 22px; }

    .comp-group h4 {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 10px;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-muted);
    }

    .comp-group h4::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--bx-line);
    }

    .comp-list {
        display: grid;
        gap: 8px;
    }

    .comp-item {
        border: 1px solid var(--bx-line);
        border-radius: 12px;
        background: #fff;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .comp-item:hover { border-color: #d5d9e0; }

    .comp-item.is-open {
        border-color: rgba(201, 168, 76, 0.55);
        box-shadow: 0 10px 24px -20px rgba(13, 31, 51, 0.55);
    }

    .comp-head {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        padding: 13px 16px;
        border: 0;
        background: transparent;
        font-family: inherit;
        text-align: left;
        cursor: pointer;
    }

    .comp-head .dot {
        flex: 0 0 auto;
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #d5d9e0;
    }

    .comp-head .dot.met { background: var(--bx-green); }
    .comp-head .dot.missing { background: #e0a43a; }
    .comp-head .dot.not-logged { background: #c3c8d0; }

    .comp-head .name {
        min-width: 0;
        flex: 1;
        color: var(--primary);
        font-size: 14px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .comp-tags {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 6px;
    }

    .status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 600;
        white-space: nowrap;
    }

    .status:empty { display: none; }

    .status.need { background: var(--bx-soft); color: #4b5563; }
    .status.core { background: rgba(26, 58, 92, 0.08); color: var(--primary); }
    .status.met { background: var(--bx-green-wash); color: var(--bx-green); }
    .status.missing { background: var(--bx-amber-wash); color: var(--bx-amber); }
    .status.not-logged { background: #f1f2f4; color: #6b7280; }

    .comp-head .chev {
        color: #b0b7c3;
        font-size: 12px;
        transition: transform 0.25s ease;
    }

    .comp-item.is-open .chev {
        transform: rotate(180deg);
        color: var(--bx-gold);
    }

    .comp-body {
        padding: 0 16px 16px 37px;
        font-size: 13.5px;
        line-height: 1.65;
        color: #4b5563;
    }

    .comp-body .muted { color: #9aa3b2; font-style: italic; }

    .comp-levels {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 12px;
    }

    .comp-level {
        padding: 10px 12px;
        border-radius: 10px;
        background: var(--bx-soft);
    }

    .comp-level small {
        display: block;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--text-muted);
    }

    .comp-level b {
        color: var(--primary);
        font-size: 13.5px;
    }

    .level-bar {
        display: flex;
        gap: 4px;
        margin-top: 8px;
    }

    .level-bar span {
        flex: 1;
        height: 5px;
        border-radius: 3px;
        background: #e3e6eb;
    }

    .level-bar span.on { background: var(--bx-navy-3); }
    .level-bar.you span.on { background: var(--bx-gold); }

    /* Proficiency levels */
    .prof-list {
        display: grid;
        gap: 10px;
    }

    .prof-level {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 16px;
        align-items: center;
        padding: 16px 18px;
        border: 1px solid var(--bx-line);
        border-radius: 14px;
        background: #fff;
    }

    .prof-level .num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--bx-navy-2), var(--bx-navy-3));
        color: var(--bx-gold-soft);
        font-family: 'Playfair Display', serif;
        font-size: 20px;
        font-weight: 700;
    }

    .prof-level .level {
        color: var(--primary);
        font-size: 15px;
        font-weight: 700;
    }

    .prof-level .desc {
        margin-top: 2px;
        font-size: 13px;
        line-height: 1.6;
        color: var(--text-muted);
    }

    .prof-level .level-bar { max-width: 220px; }

    /* Entry requirements */
    .req-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .req-item {
        display: flex;
        gap: 14px;
        padding: 16px 18px;
        border: 1px solid var(--bx-line);
        border-radius: 14px;
        background: #fff;
    }

    .req-item > i {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 11px;
        background: var(--bx-gold-wash);
        color: var(--bx-gold);
    }

    .req-item .label {
        display: block;
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--text-muted);
    }

    .req-item .value {
        display: block;
        margin-top: 2px;
        color: var(--primary);
        font-size: 14px;
        font-weight: 600;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    /* Training */
    .training-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .training-item {
        display: flex;
        gap: 14px;
        padding: 16px 18px;
        border: 1px solid var(--bx-line);
        border-radius: 14px;
        background: #fff;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .training-item:hover {
        border-color: rgba(201, 168, 76, 0.55);
        box-shadow: 0 12px 24px -20px rgba(13, 31, 51, 0.5);
    }

    .training-item > i {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 11px;
        background: linear-gradient(135deg, var(--bx-navy-2), var(--bx-navy-3));
        color: var(--bx-gold-soft);
    }

    .training-item .body { min-width: 0; }

    .training-item .name {
        color: var(--primary);
        font-size: 14px;
        font-weight: 600;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .training-item .provider {
        margin-top: 2px;
        font-size: 12.5px;
        color: var(--text-muted);
    }

    .training-item .tdesc {
        margin-top: 6px;
        font-size: 12.5px;
        line-height: 1.55;
        color: #4b5563;
    }

    .training-item a {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 8px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--bx-navy-3);
        text-decoration: none;
    }

    .training-item a:hover { text-decoration: underline; }

    /* Loading and empty */
    .spinner-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        padding: 48px 20px;
        color: var(--text-muted);
        font-size: 13px;
    }

    .spinner {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 3px solid var(--bx-line);
        border-top-color: var(--bx-gold);
        animation: bx-spin 0.8s linear infinite;
    }

    @keyframes bx-spin { to { transform: rotate(1turn); } }

    .empty-state {
        padding: 40px 20px;
        border: 1px dashed #d5d9e0;
        border-radius: 14px;
        text-align: center;
        color: var(--text-muted);
    }

    .empty-state > i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 56px;
        height: 56px;
        margin-bottom: 12px;
        border-radius: 50%;
        background: var(--bx-gold-wash);
        color: var(--bx-gold);
        font-size: 22px;
    }

    .empty-state h4 {
        margin: 0 0 4px;
        font-size: 16px;
        color: var(--primary);
    }

    .empty-state p {
        margin: 0;
        font-size: 13px;
    }

    /* Each tab fades in */
    @media (prefers-reduced-motion: no-preference) {
        .bx-panel { animation: bx-in 0.35s ease both; }

        @keyframes bx-in {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: none; }
        }
    }

    /* ---------- Smaller screens ---------- */
    @media (max-width: 1024px) {
        .bx-hero { grid-template-columns: minmax(0, 1fr); }
    }

    @media (max-width: 900px) {
        .biicf-grid { grid-template-columns: minmax(0, 1fr); }

        .biicf-sidebar { position: static; }

        /* The menu becomes a row of tabs you can swipe */
        .biicf-sidebar .nav-group {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            padding: 8px;
            scrollbar-width: none;
        }

        .biicf-sidebar .nav-group::-webkit-scrollbar { display: none; }

        .bx-nav-heading { display: none; }

        .biicf-sidebar .nav-item {
            flex: 0 0 auto;
            width: auto;
            white-space: nowrap;
        }

        .biicf-sidebar .nav-item .needs-role { display: none; }
        .biicf-sidebar .nav-item.active::before { display: none; }
    }

    @media (max-width: 640px) {
        .bx-hero { padding: 22px 18px; border-radius: 16px; }
        .bx-hero h1 { font-size: 25px; }
        .bx-stat { min-width: 0; flex: 1 1 30%; padding: 8px 10px; }
        .bx-stat strong { font-size: 19px; }
        .bx-stat span { font-size: 10.5px; white-space: nowrap; overflow-wrap: normal; }
        .bx-step small { display: none; }
        .bx-steps { gap: 8px; padding: 14px; }

        .biicf-content { padding: 18px 16px 22px; }

        .breadcrumb-bar { flex-direction: column; align-items: stretch; }
        .change-role-btn { justify-content: center; }

        .sub-sector-grid,
        .req-grid,
        .training-list,
        .comp-levels { grid-template-columns: minmax(0, 1fr); }

        .bx-section-head .compare-btn { width: 100%; justify-content: center; }

        .compare-result { grid-template-columns: minmax(0, 1fr); justify-items: center; text-align: center; }
        .compare-result .summary { width: 100%; }

        .career-path { grid-template-columns: minmax(0, 1fr); }
        .career-path .arrow { transform: rotate(90deg); justify-self: center; }

        .comp-head { flex-wrap: wrap; }
        .comp-tags { width: 100%; justify-content: flex-start; padding-left: 21px; }
        .comp-body { padding-left: 16px; }
    }
</style>

<div class="biicf-page" x-data="biicfExplorer()" x-init="init()">
    <div class="container">

        <!-- Banner -->
        <header class="bx-hero">
            <div>
                <span class="bx-chip"><i class="fas fa-certificate"></i> Framework aligned with AITI</span>
                <h1>BIICF <span>Explorer</span></h1>
                <p>Browse the Brunei ICT Industry Competency Framework — sub-sectors, job roles, career paths, competencies and training.</p>

                <div class="bx-stats">
                    <div class="bx-stat"><strong>{{ $subSectors->count() }}</strong><span>Sub-sectors</span></div>
                    <div class="bx-stat"><strong>{{ $jobRoleCount ?? 0 }}</strong><span>Job roles</span></div>
                    <div class="bx-stat"><strong>{{ $competencyCount ?? 0 }}</strong><span>Competencies</span></div>
                </div>
            </div>

            <div class="bx-steps" aria-label="How to use the explorer">
                <div class="bx-steps-title">How it works</div>
                <div class="bx-step" :class="{ 'is-done': selectedSubSectorName || selectedRole }">
                    <span class="bx-step-num">1</span>
                    <div>Pick a sub-sector <small>or browse every job role</small></div>
                </div>
                <div class="bx-step" :class="{ 'is-done': selectedRole }">
                    <span class="bx-step-num">2</span>
                    <div>Choose a job role <small>to see what it involves</small></div>
                </div>
                <div class="bx-step" :class="{ 'is-done': comparison }">
                    <span class="bx-step-num">3</span>
                    <div>Compare it to your profile <small>see what you have and what to build</small></div>
                </div>
            </div>
        </header>

        <div class="biicf-grid">

            <!-- Sidebar -->
            <aside class="biicf-sidebar">
                <div class="bx-card bx-current">
                    <div class="bx-current-label">Selected role</div>
                    <template x-if="selectedRole">
                        <div class="bx-current-role">
                            <span class="bx-current-icon"><i class="fas fa-user-tie"></i></span>
                            <div>
                                <strong x-text="selectedRole.title"></strong>
                                <span x-text="selectedRole.sub_sector?.name"></span>
                            </div>
                        </div>
                    </template>
                    <p class="bx-current-empty" x-show="!selectedRole">
                        None yet. Pick one under <b>Job Roles</b> to unlock its path, competencies and training.
                    </p>
                </div>

                <div class="bx-card nav-group">
                    <div class="bx-nav-heading">Explore</div>
                    <button class="nav-item" :class="{ active: activeSection === 'sub-sectors' }" @click="activeSection = 'sub-sectors'">
                        <i class="fas fa-layer-group"></i> ICT Sub-sectors
                        <span class="count">{{ $subSectors->count() }}</span>
                    </button>
                    <button class="nav-item" :class="{ active: activeSection === 'job-roles' }" @click="activeSection = 'job-roles'">
                        <i class="fas fa-briefcase"></i> Job Roles
                        <span class="count">{{ $jobRoleCount ?? 0 }}</span>
                    </button>

                    <div class="bx-nav-heading">About the role</div>
                    <button class="nav-item" :class="{ active: activeSection === 'career-paths' }" @click="activeSection = 'career-paths'">
                        <i class="fas fa-route"></i> Career Paths
                        <span class="needs-role" x-show="!selectedRole">Pick a role</span>
                    </button>
                    <button class="nav-item" :class="{ active: activeSection === 'competencies' }" @click="activeSection = 'competencies'">
                        <i class="fas fa-tools"></i> Competencies
                        <span class="count">{{ $competencyCount ?? 0 }}</span>
                    </button>
                    <button class="nav-item" :class="{ active: activeSection === 'entry-requirements' }" @click="activeSection = 'entry-requirements'">
                        <i class="fas fa-door-open"></i> Entry Requirements
                        <span class="needs-role" x-show="!selectedRole">Pick a role</span>
                    </button>
                    <button class="nav-item" :class="{ active: activeSection === 'training' }" @click="activeSection = 'training'">
                        <i class="fas fa-graduation-cap"></i> Training & Certifications
                        <span class="count">{{ $trainingCount ?? 0 }}</span>
                    </button>

                    <div class="bx-nav-heading">Reference</div>
                    <button class="nav-item" :class="{ active: activeSection === 'proficiency-levels' }" @click="activeSection = 'proficiency-levels'">
                        <i class="fas fa-level-up-alt"></i> Proficiency Levels
                        <span class="count">{{ $proficiencyLevels->count() }}</span>
                    </button>
                </div>
            </aside>

            <!-- Content -->
            <main class="biicf-content bx-card">

                <!-- Breadcrumb: shown on any role-dependent tab once a role is selected -->
                <div class="breadcrumb-bar" x-show="selectedRole && !['sub-sectors', 'job-roles'].includes(activeSection)" x-cloak>
                    <div class="crumb">
                        <i class="fas fa-layer-group"></i>
                        <span x-text="selectedRole?.sub_sector?.name"></span>
                        <span class="sep">/</span>
                        <span class="current" x-text="selectedRole?.title"></span>
                    </div>
                    <button class="change-role-btn" @click="clearRole()">
                        <i class="fas fa-exchange-alt"></i> Change role
                    </button>
                </div>

                <!-- Sub-sectors -->
                <section class="bx-panel" x-show="activeSection === 'sub-sectors'" x-cloak>
                    <div class="bx-section-head">
                        <div class="bx-section-title">
                            <span class="bx-section-icon"><i class="fas fa-layer-group"></i></span>
                            <div>
                                <h2>ICT Sub-sectors</h2>
                                <p>The six areas of Brunei's ICT industry. Pick one to see its job roles.</p>
                            </div>
                        </div>
                    </div>

                    @php
                        $sectorIcons = ['fa-server', 'fa-network-wired', 'fa-code', 'fa-shield-halved', 'fa-photo-film', 'fa-brain'];
                    @endphp
                    <div class="sub-sector-grid">
                        @foreach ($subSectors as $sector)
                            <button class="sub-sector-card" @click="selectSubSector('{{ $sector->slug }}', '{{ $sector->name }}')">
                                <span class="icon"><i class="fas {{ $sectorIcons[$loop->index % count($sectorIcons)] }}"></i></span>
                                <span class="body">
                                    <span class="name">{{ $sector->name }}</span>
                                    @if (filled($sector->description))
                                        <span class="desc">{{ $sector->description }}</span>
                                    @endif
                                    <span class="count">{{ $sector->job_roles_count }} roles <i class="fas fa-arrow-right"></i></span>
                                </span>
                            </button>
                        @endforeach
                    </div>
                </section>

                <!-- Job Roles -->
                <section class="bx-panel" x-show="activeSection === 'job-roles'" x-cloak>
                    <div class="bx-section-head">
                        <div class="bx-section-title">
                            <span class="bx-section-icon"><i class="fas fa-briefcase"></i></span>
                            <div>
                                <h2>Job Roles</h2>
                                <p>Roles are ordered by career level, from entry level upwards.</p>
                            </div>
                        </div>
                    </div>

                    <template x-if="selectedSubSectorName">
                        <div class="selected-filter">
                            <i class="fas fa-filter"></i>
                            <span x-text="selectedSubSectorName"></span>
                            <span class="remove" @click="clearSubSectorFilter()" title="Show all job roles">×</span>
                        </div>
                    </template>

                    <div class="search-box" x-show="!loadingRoles && roles.length > 0">
                        <i class="fas fa-search"></i>
                        <input type="text" x-model="roleSearch" placeholder="Search job roles by title...">
                    </div>

                    <div class="spinner-wrap" x-show="loadingRoles" x-cloak>
                        <div class="spinner"></div>
                        <p>Loading job roles…</p>
                    </div>

                    <div class="empty-state" x-show="!loadingRoles && roles.length === 0">
                        <i class="fas fa-hand-point-left"></i>
                        <h4>No job roles loaded</h4>
                        <p>Try selecting a different sub-sector, or clear the filter to browse all {{ $jobRoleCount ?? 0 }} job roles.</p>
                    </div>

                    <div class="role-list" x-show="!loadingRoles && roles.length > 0">
                        <template x-for="role in filteredRoles" :key="role.id">
                            <button class="role-item" @click="selectJobRole(role.slug)">
                                <span class="lvl"><small>Level</small><b x-text="role.career_path_level"></b></span>
                                <span class="info">
                                    <span class="title" x-text="role.title"></span>
                                    <span class="badges" x-show="!selectedSubSectorSlug && role.sub_sector" x-text="role.sub_sector?.name"></span>
                                </span>
                                <i class="fas fa-chevron-right go"></i>
                            </button>
                        </template>
                        <div x-show="filteredRoles.length === 0" class="empty-state">
                            <i class="fas fa-search"></i>
                            <h4>No roles match "<span x-text="roleSearch"></span>"</h4>
                            <p>Try a different search term.</p>
                        </div>
                    </div>
                </section>

                <!-- Career Paths -->
                <section class="bx-panel" x-show="activeSection === 'career-paths'" x-cloak>
                    <div class="bx-section-head">
                        <div class="bx-section-title">
                            <span class="bx-section-icon"><i class="fas fa-route"></i></span>
                            <div>
                                <h2>Career Path</h2>
                                <p>What the role involves, and where it can lead.</p>
                            </div>
                        </div>
                    </div>

                    <div class="spinner-wrap" x-show="loadingRoleDetail" x-cloak>
                        <div class="spinner"></div>
                        <p>Loading role details…</p>
                    </div>

                    <template x-if="selectedRole && !loadingRoleDetail">
                        <div>
                            <!-- Role Overview -->
                            {{--
                                The book writes a description as a
                                list, and splits duties into generic
                                ones and ones belonging to a job
                                family, nesting a second level under
                                some. All of that is now stored as
                                rows rather than lines of text.
                            --}}
                            <div
                                class="overview-card"
                                x-show="(selectedRole.description_points || []).length
                                    || (selectedRole.generic_work_functions || []).length
                                    || roleSpecificFunctions.length"
                            >
                                <h4><i class="fas fa-info-circle"></i> Role Overview</h4>

                                <template x-if="(selectedRole.description_points || []).length">
                                    <ul>
                                        <template x-for="point in selectedRole.description_points" :key="point.id">
                                            <li x-text="point.text"></li>
                                        </template>
                                    </ul>
                                </template>

                                <template x-if="(selectedRole.generic_work_functions || []).length">
                                    <div>
                                        {{--
                                            "Key Responsibilities" rather
                                            than "Generic Functions": only
                                            one role in the book draws that
                                            distinction, and naming it on
                                            every role would imply a
                                            counterpart that is not there.
                                        --}}
                                        <h5>Key Responsibilities</h5>
                                        <ul>
                                            <template x-for="fn in selectedRole.generic_work_functions" :key="fn.id">
                                                <li>
                                                    <span x-text="fn.text"></span>
                                                    {{--
                                                        The book groups duties under
                                                        headings of their own and nests
                                                        a third level under some of
                                                        them, which is as deep as it
                                                        goes.
                                                    --}}
                                                    <ul x-show="(fn.children || []).length">
                                                        <template x-for="child in (fn.children || [])" :key="child.id">
                                                            <li>
                                                                <span x-text="child.text"></span>
                                                                <ul x-show="(child.children || []).length">
                                                                    <template x-for="leaf in (child.children || [])" :key="leaf.id">
                                                                        <li x-text="leaf.text"></li>
                                                                    </template>
                                                                </ul>
                                                            </li>
                                                        </template>
                                                    </ul>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>

                                <template x-for="group in roleSpecificFunctions" :key="group.family">
                                    <div>
                                        <h5 x-text="group.family"></h5>
                                        <ul>
                                            <template x-for="fn in group.functions" :key="fn.id">
                                                <li>
                                                    <span x-text="fn.text"></span>
                                                    <ul x-show="(fn.children || []).length">
                                                        <template x-for="child in (fn.children || [])" :key="child.id">
                                                            <li x-text="child.text"></li>
                                                        </template>
                                                    </ul>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>
                            </div>

                            <div x-show="(selectedRole.progresses_from || []).length || (selectedRole.progresses_to || []).length">
                                <div class="bx-subhead">Career progression</div>
                                <div class="career-path">
                                    <div class="path-col">
                                        <span class="path-col-label">Comes from</span>
                                        <template x-for="prev in (selectedRole.progresses_from || [])" :key="prev.id">
                                            <span class="node prev" x-text="prev.title"></span>
                                        </template>
                                        <span class="node none" x-show="!(selectedRole.progresses_from || []).length">Entry point</span>
                                    </div>
                                    <i class="fas fa-arrow-right arrow"></i>
                                    <div class="path-col">
                                        <span class="path-col-label">This role</span>
                                        <span class="node current" x-text="selectedRole.title"></span>
                                    </div>
                                    <i class="fas fa-arrow-right arrow"></i>
                                    <div class="path-col">
                                        <span class="path-col-label">Leads to</span>
                                        <template x-for="next in (selectedRole.progresses_to || [])" :key="next.id">
                                            <span class="node next" x-text="next.title"></span>
                                        </template>
                                        <span class="node none" x-show="!(selectedRole.progresses_to || []).length">Top of this path</span>
                                    </div>
                                </div>
                            </div>

                            <div x-show="!(selectedRole.progresses_from || []).length && !(selectedRole.progresses_to || []).length" class="empty-state">
                                <i class="fas fa-info-circle"></i>
                                <h4>No career path data</h4>
                                <p>This role doesn't have defined progression paths yet.</p>
                            </div>
                        </div>
                    </template>

                    <div x-show="!selectedRole && !loadingRoleDetail" class="empty-state">
                        <i class="fas fa-hand-point-up"></i>
                        <h4>Select a job role first</h4>
                        <p>Go to "Job Roles" and pick one to see its career progression.</p>
                    </div>
                </section>

                <!-- Competencies -->
                <section class="bx-panel" x-show="activeSection === 'competencies'" x-cloak>
                    <div class="bx-section-head">
                        <div class="bx-section-title">
                            <span class="bx-section-icon"><i class="fas fa-tools"></i></span>
                            <div>
                                <h2>Competencies</h2>
                                <p x-show="selectedRole">What this role needs, and at which level. Tap one for details.</p>
                                <p x-show="!selectedRole">Every BIICF competency. Tap one to read what it means.</p>
                            </div>
                        </div>
                        <button class="compare-btn" x-show="selectedRole" :disabled="loadingComparison" @click="compareToMe(selectedRole.slug)">
                            <span x-show="!loadingComparison"><i class="fas fa-chart-bar"></i> Compare to my profile</span>
                            <span x-show="loadingComparison"><i class="fas fa-spinner fa-spin"></i> Comparing…</span>
                        </button>
                    </div>

                    <div class="compare-hint" x-show="selectedRole && !comparison" x-cloak>
                        <i class="fas fa-lightbulb"></i>
                        <span>Press <b>Compare to my profile</b> to check each competency against the levels you set on your profile.</span>
                    </div>

                    <!-- Comparison Result -->
                    <div class="compare-result" x-show="comparison" x-cloak>
                        <div class="bx-ring">
                            <svg viewBox="0 0 120 120" aria-hidden="true">
                                <circle class="track" cx="60" cy="60" r="50"></circle>
                                <circle class="bar" cx="60" cy="60" r="50"
                                    stroke-dasharray="314.16"
                                    :stroke-dashoffset="314.16 * (1 - matchRatio())"></circle>
                            </svg>
                            <div class="bx-ring-text">
                                <strong x-text="Math.round(matchRatio() * 100) + '%'"></strong>
                                <span>met</span>
                            </div>
                        </div>
                        <div>
                            <h4>You meet <span x-text="comparison?.summary?.met || 0"></span> of <span x-text="comparison?.summary?.total || 0"></span> competencies</h4>
                            <p class="lead">Based on the competencies and levels saved on your profile.</p>
                            <div class="summary">
                                <div class="stat met">
                                    <div class="number" x-text="comparison?.summary?.met || 0"></div>
                                    <div class="label">Met</div>
                                </div>
                                <div class="stat below">
                                    <div class="number" x-text="comparison?.summary?.below || 0"></div>
                                    <div class="label">Below the level</div>
                                </div>
                                <div class="stat missing">
                                    <div class="number" x-text="comparison?.summary?.missing || 0"></div>
                                    <div class="label">Not on your profile</div>
                                </div>
                            </div>
                            <a href="{{ route('student.profile.edit') }}" class="update-link">
                                <i class="fas fa-pen"></i> Update my competencies
                            </a>
                        </div>
                    </div>

                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" x-model="compSearch" placeholder="Search competencies by name...">
                    </div>

                    <div class="type-filter-pills" x-show="!selectedRole">
                        <button class="filter-pill" :class="{ active: compTypeFilter === '' }" @click="compTypeFilter = ''">All</button>
                        <button class="filter-pill" :class="{ active: compTypeFilter === 'technical' }" @click="compTypeFilter = 'technical'">Technical</button>
                        <button class="filter-pill" :class="{ active: compTypeFilter === 'soft_skill' }" @click="compTypeFilter = 'soft_skill'">Soft Skill</button>
                    </div>

                    <!-- Role-specific competencies -->
                    <template x-if="selectedRole">
                        <div>
                            {{--
                                The book gives a role one column
                                of proficiencies per tier, so the
                                same competency can be wanted at
                                a different level further up the
                                role. Only worth showing when the
                                role actually has more than one.
                            --}}
                            <div class="tier-pills" x-show="roleTiers.length > 1">
                                <span class="tier-pills-label">Required at</span>
                                <template x-for="tier in roleTiers" :key="tier.id">
                                    <button class="filter-pill"
                                            :class="{ active: activeTierId === tier.id }"
                                            @click="activeTierId = tier.id">
                                        <span x-text="tier.label"></span>
                                    </button>
                                </template>
                            </div>

                            <template x-for="type in ['technical', 'soft_skill']" :key="type">
                                <div class="comp-group" x-show="filteredRoleCompetencies(type).length">
                                    <h4 x-text="type === 'technical' ? 'Technical Competencies' : 'Soft Skill Competencies'"></h4>
                                    <div class="comp-list">
                                        <template x-for="comp in filteredRoleCompetencies(type)" :key="comp.id">
                                            <div class="comp-item" :class="{ 'is-open': openComp === comp.id }">
                                                <button type="button" class="comp-head" @click="toggleComp(comp.id)" :aria-expanded="openComp === comp.id">
                                                    <span class="dot" :class="statusClass(comp.id)"></span>
                                                    <span class="name" x-text="comp.name"></span>
                                                    <span class="comp-tags">
                                                        <span class="status need" x-show="comp.level" x-text="'Needs: ' + (comp.level?.name || '')"></span>
                                                        <span class="status" :class="statusClass(comp.id)" x-text="statusLabel(comp.id)"></span>
                                                        <span class="status core" x-show="comp.is_core">Core</span>
                                                    </span>
                                                    <i class="fas fa-chevron-down chev"></i>
                                                </button>
                                                <div class="comp-body" x-show="openComp === comp.id" x-cloak>
                                                    <p x-show="comp.description" x-text="comp.description"></p>
                                                    <p class="muted" x-show="!comp.description">No description has been added for this competency yet.</p>

                                                    <div class="comp-levels">
                                                        <div class="comp-level" x-show="comp.level">
                                                            <small>This role needs</small>
                                                            <b x-text="comp.level ? comp.level.number + ' · ' + comp.level.name : ''"></b>
                                                            {{--
                                                                As many steps as the competency's own
                                                                scale has. Technical skills are rated
                                                                over five and soft skills over four, so
                                                                a fixed five draws Expert as four of
                                                                five and reads as a shortfall.
                                                            --}}
                                                            <div class="level-bar">
                                                                <template x-for="n in (comp.level?.of || 5)" :key="n">
                                                                    <span :class="{ on: n <= (comp.level?.number || 0) }"></span>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <div class="comp-level">
                                                            <small>Your level</small>
                                                            <b x-text="yourLevelText(comp.id)"></b>
                                                            <div class="level-bar you" x-show="statusEntry(comp.id)?.student_level_number">
                                                                <template x-for="n in (comp.level?.of || 5)" :key="n">
                                                                    <span :class="{ on: n <= (statusEntry(comp.id)?.student_level_number || 0) }"></span>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{--
                                                        A rung's name says little on its own.
                                                        What the framework asks of someone at
                                                        each rung of this competency is written
                                                        down, and it lives under Proficiency
                                                        Levels rather than in a second copy
                                                        here.
                                                    --}}
                                                    <button type="button" class="update-link"
                                                            @click="openCompetency(comp.id, comp.level)">
                                                        What this asks of you at each level
                                                        <i class="fas fa-angle-right"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            <div class="empty-state" x-show="compSearch && filteredRoleCompetencies('technical').length === 0 && filteredRoleCompetencies('soft_skill').length === 0">
                                <i class="fas fa-search"></i>
                                <h4>No competencies match "<span x-text="compSearch"></span>"</h4>
                            </div>
                        </div>
                    </template>

                    <!-- Full glossary (when no role selected) -->
                    <div x-show="!selectedRole">
                        <div class="spinner-wrap" x-show="loadingGlossary" x-cloak>
                            <div class="spinner"></div>
                            <p>Loading competency glossary…</p>
                        </div>

                        <div x-show="!loadingGlossary">
                            <template x-for="type in ['technical', 'soft_skill']" :key="type">
                                <div class="comp-group" x-show="filteredGlossary(type).length">
                                    <h4 x-text="type === 'technical' ? 'Technical Competencies' : 'Soft Skill Competencies'"></h4>
                                    <div class="comp-list">
                                        <template x-for="comp in filteredGlossary(type)" :key="comp.id">
                                            <div class="comp-item" :class="{ 'is-open': openComp === comp.id }">
                                                <button type="button" class="comp-head" @click="toggleComp(comp.id)" :aria-expanded="openComp === comp.id">
                                                    <span class="dot"></span>
                                                    <span class="name" x-text="comp.name"></span>
                                                    <i class="fas fa-chevron-down chev"></i>
                                                </button>
                                                <div class="comp-body" x-show="openComp === comp.id" x-cloak>
                                                    <p x-show="comp.description" x-text="comp.description"></p>
                                                    <p class="muted" x-show="!comp.description">No description has been added for this competency yet.</p>

                                                    <button type="button" class="update-link" @click="openCompetency(comp.id, null)">
                                                        What this asks of you at each level
                                                        <i class="fas fa-angle-right"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            <div class="empty-state" x-show="filteredGlossary('technical').length === 0 && filteredGlossary('soft_skill').length === 0">
                                <i class="fas fa-search"></i>
                                <h4>No competencies found</h4>
                                <p>Try a different search term or filter.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Proficiency Levels -->
                <section class="bx-panel" x-show="activeSection === 'proficiency-levels'" x-cloak>
                    <div class="bx-section-head">
                        <div class="bx-section-title">
                            <span class="bx-section-icon"><i class="fas fa-level-up-alt"></i></span>
                            <div>
                                <h2>Proficiency Levels</h2>
                                <p>The rungs the framework rates a competency on, and what it asks of you at each. These are the levels you choose on your profile.</p>
                            </div>
                        </div>
                    </div>

                    {{--
                        A competency opened from the Competencies
                        tab is shown here: the scale means little
                        on its own, and what a student needs is
                        what the book asks of them at each rung of
                        the competency in front of them.
                    --}}
                    <div x-show="competencyDetail || loadingCompetency" x-cloak>
                        <div class="selected-filter">
                            <span>
                                <i class="fas fa-tools"></i>
                                Showing
                                <strong x-text="competencyDetail?.competency?.name || '…'"></strong>
                            </span>
                            <span class="remove" @click="clearCompetency()">
                                <i class="fas fa-times"></i> Show the scales instead
                            </span>
                        </div>

                        <div class="spinner-wrap" x-show="loadingCompetency">
                            <div class="spinner"></div>
                            <p>Loading competency…</p>
                        </div>

                        <template x-if="competencyDetail && !loadingCompetency">
                            <div>
                                <div class="overview-card">
                                    <h4><i class="fas fa-info-circle"></i> <span x-text="competencyDetail.competency.name"></span></h4>
                                    <p x-text="competencyDetail.competency.description"></p>
                                </div>

                                <template x-for="(set, index) in competencyDetail.sets" :key="index">
                                    <div class="prof-scale">
                                        <div class="bx-section-head">
                                            <div class="bx-section-title">
                                                <div>
                                                    <h2 x-text="set.scope ? set.scope : 'Proficiency levels'"></h2>
                                                    <p x-text="set.levels.length + ' levels on this scale'"></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="prof-list">
                                            <template x-for="level in set.levels" :key="level.number">
                                                <div class="prof-level"
                                                     :class="{ required: requiredLevelNumber === level.number, absent: !level.described }">
                                                    <span class="num" x-text="level.number"></span>

                                                    <div>
                                                        <div class="level">
                                                            <span x-text="level.name"></span>
                                                            <span class="required-flag" x-show="requiredLevelNumber === level.number">
                                                                Required for <span x-text="selectedRole?.title"></span>
                                                            </span>
                                                        </div>

                                                        {{--
                                                            The generic meaning of the rung —
                                                            what "Assist" means anywhere —
                                                            and only where the framework
                                                            actually takes this competency to
                                                            that rung. Shown at a level the
                                                            book marks N/A, it reads as a
                                                            description of this competency
                                                            that the book never wrote.
                                                        --}}
                                                        <div class="desc" x-show="level.described" x-text="level.summary"></div>

                                                        <ul class="level-statements" x-show="level.statements.length">
                                                            <template x-for="(statement, i) in level.statements" :key="i">
                                                                <li x-text="statement"></li>
                                                            </template>
                                                        </ul>

                                                        {{--
                                                            The book prints nothing at this
                                                            rung, which it means: the
                                                            competency does not reach it.
                                                        --}}
                                                        <p class="comp-level-none" x-show="!level.described">
                                                            The framework does not describe this competency at this level.
                                                        </p>

                                                        <div class="level-bar" x-show="level.described">
                                                            <template x-for="n in set.levels.length" :key="n">
                                                                <span :class="{ on: n <= level.number }"></span>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div x-show="!competencyDetail && !loadingCompetency">
                        {{--
                            Two scales, not one. Technical skills are
                            rated over five levels and soft skills
                            over four, so a single run of rungs would
                            restart its numbering halfway down with
                            nothing to say why — and Advanced on a
                            soft skill is not level 3 on a technical
                            one.
                        --}}
                        @foreach ($proficiencyScales ?? [] as $scaleName => $scaleLevels)
                            @continue($scaleLevels->isEmpty())

                            <div class="prof-scale">
                                <div class="bx-section-head">
                                    <div class="bx-section-title">
                                        <div>
                                            <h2>{{ $scaleName }}</h2>
                                            <p>{{ $scaleLevels->count() }} levels on this scale</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="prof-list">
                                    @foreach ($scaleLevels as $level)
                                        <div class="prof-level">
                                            <span class="num">{{ $level->level_number }}</span>
                                            <div>
                                                <div class="level">{{ $level->name }}</div>
                                                <div class="desc">{{ $level->description }}</div>
                                                <div class="level-bar">
                                                    @foreach ($scaleLevels as $step)
                                                        <span class="{{ $step->level_number <= $level->level_number ? 'on' : '' }}"></span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <p class="section-note">
                            <i class="fas fa-lightbulb"></i>
                            Pick a competency under <strong>Competencies</strong> to see what the
                            framework asks of you at each of its levels.
                        </p>
                    </div>
                </section>

                <!-- Entry Requirements -->
                <section class="bx-panel" x-show="activeSection === 'entry-requirements'" x-cloak>
                    <div class="bx-section-head">
                        <div class="bx-section-title">
                            <span class="bx-section-icon"><i class="fas fa-door-open"></i></span>
                            <div>
                                <h2>Entry Requirements</h2>
                                <p>The qualifications and experience usually expected for this role.</p>
                            </div>
                        </div>
                    </div>

                    <div class="spinner-wrap" x-show="loadingRoleDetail" x-cloak>
                        <div class="spinner"></div>
                        <p>Loading role details…</p>
                    </div>

                    {{--
                        Shown as the book writes it, in the tiles the
                        rest of this screen uses.

                        A requirement belongs to a tier and usually
                        names two ways in, each with its own
                        qualification level and years of experience, so
                        the single level-and-field pair this panel used
                        to show had to pick one of them and drop the
                        other.
                    --}}
                    <template x-if="selectedRole && !loadingRoleDetail">
                        <div>
                            {{--
                                The same pills the competencies use, and
                                for the same reason: a role entered at
                                three levels asks something different at
                                each, and stacking all three down the
                                page made the student find their own.
                            --}}
                            <div class="tier-pills" x-show="roleTiers.length > 1">
                                <span class="tier-pills-label">Entering at</span>
                                <template x-for="tier in roleTiers" :key="tier.id">
                                    <button class="filter-pill"
                                            :class="{ active: activeTierId === tier.id }"
                                            @click="activeTierId = tier.id">
                                        <span x-text="tier.label"></span>
                                        <span class="pill-level" x-show="tier.job_level && tier.job_level !== tier.label"
                                              x-text="tier.job_level"></span>
                                    </button>
                                </template>
                            </div>

                            <template x-for="tier in roleTiers" :key="tier.id">
                                <div class="req-tier"
                                     x-show="tier.entry_requirements.length
                                             && (roleTiers.length === 1 || activeTierId === tier.id)">

                                    {{--
                                        The book usually gives two ways
                                        in, and meeting either one is
                                        enough. Numbered only when there
                                        is a choice to make.
                                    --}}
                                    <template x-for="(req, index) in tier.entry_requirements" :key="req.alternative_number">
                                        <div>
                                            <div class="req-or" x-show="index > 0">or</div>

                                            <div class="req-grid">
                                                <div class="req-item">
                                                    <i class="fas fa-award"></i>
                                                    <div>
                                                        <span class="label">Qualification Level</span>
                                                        <span class="value"
                                                              x-text="req.bdqf_level ? 'BDQF Level ' + req.bdqf_level : 'Not stated'"></span>
                                                    </div>
                                                </div>

                                                <div class="req-item">
                                                    <i class="fas fa-book-open"></i>
                                                    <div>
                                                        <span class="label">Field of Study</span>
                                                        <span class="value" x-text="req.field_of_study || 'Not stated'"></span>
                                                    </div>
                                                </div>

                                                <div class="req-item" x-show="req.conditions.length">
                                                    <i class="fas fa-briefcase"></i>
                                                    <div>
                                                        <span class="label">Also asks for</span>
                                                        <span class="value" x-text="req.conditions.join(' and ')"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{--
                                                The book's own sentence, under the
                                                tiles. Reading it is how a student
                                                settles anything the tiles had to
                                                shorten.
                                            --}}
                                            <div class="req-text">
                                                <p x-text="req.text"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <div x-show="!roleTiers.some(t => t.entry_requirements.length)"
                                 class="empty-state">
                                <i class="fas fa-info-circle"></i>
                                <h4>No entry requirement published</h4>
                                <p>The framework does not state one for this role.</p>
                            </div>
                        </div>
                    </template>

                    {{--
                        No role picked yet. A role whose requirements
                        the framework leaves out says so from inside
                        the panel above, where the tiers are.
                    --}}
                    <div x-show="!selectedRole && !loadingRoleDetail" class="empty-state">
                        <i class="fas fa-hand-point-up"></i>
                        <h4>Select a job role first</h4>
                        <p>Go to "Job Roles" to see entry requirements for a specific role.</p>
                    </div>
                </section>

                <!-- Training -->
                <section class="bx-panel" x-show="activeSection === 'training'" x-cloak>
                    <div class="bx-section-head">
                        <div class="bx-section-title">
                            <span class="bx-section-icon"><i class="fas fa-graduation-cap"></i></span>
                            <div>
                                <h2>Training & Certifications</h2>
                                <p>Courses and certifications recommended for this role.</p>
                            </div>
                        </div>
                    </div>

                    <div class="spinner-wrap" x-show="loadingRoleDetail" x-cloak>
                        <div class="spinner"></div>
                        <p>Loading role details…</p>
                    </div>

                    <template x-if="selectedRole && !loadingRoleDetail">
                        <div>
                            <div class="training-list">
                                <template x-for="t in (selectedRole.trainings || [])" :key="t.id">
                                    <div class="training-item">
                                        <i class="fas fa-certificate"></i>
                                        <div class="body">
                                            <div class="name" x-text="t.name"></div>
                                            {{--
                                                No provider or certification body:
                                                the book names neither, and the two
                                                columns that held them were empty on
                                                every row.
                                            --}}
                                            <div class="tdesc" x-show="t.description" x-text="t.description"></div>
                                            <a x-show="t.url" :href="t.url" target="_blank" rel="noopener">Learn more <i class="fas fa-arrow-up-right-from-square"></i></a>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div x-show="!(selectedRole.trainings || []).length" class="empty-state">
                                <i class="fas fa-info-circle"></i>
                                <h4>No training data</h4>
                                <p>This role doesn't have recommended training yet.</p>
                            </div>

                            <!--
                                The framework gives these a heading of
                                their own rather than listing them as
                                courses: nothing trains or certifies a
                                language, the role is expected to know
                                it. Only shown for the roles the book
                                names them for.
                            -->
                            <div x-show="(selectedRole.programming_languages || []).length" style="margin-top:26px;">
                                {{--
                                    The redesign's own section head. The
                                    class this used to carry went with the
                                    rules that drew it.
                                --}}
                                <div class="bx-section-head">
                                    <div class="bx-section-title">
                                        <span class="bx-section-icon"><i class="fas fa-code"></i></span>
                                        <div>
                                            <h2>Programming Languages</h2>
                                            <p>Languages in demand for this role.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="language-list">
                                    <template x-for="l in (selectedRole.programming_languages || [])" :key="l.id">
                                        <span class="language" x-text="l.name"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div x-show="!selectedRole && !loadingRoleDetail" class="empty-state">
                        <i class="fas fa-hand-point-up"></i>
                        <h4>Select a job role first</h4>
                        <p>Go to "Job Roles" to see recommended training and certifications for a specific role.</p>
                    </div>
                </section>

            </main>

        </div>

    </div>
</div>

@php
    $levelsForScript = $proficiencyLevels
        ->map(fn ($level) => [
            'id' => $level->id,
            'level_number' => (int) $level->level_number,
            'name' => $level->name,
            'description' => $level->description,
        ])
        ->values();
@endphp

<script>
const BIICF_LEVELS = @json($levelsForScript);

function biicfExplorer() {
    return {
        activeSection: 'sub-sectors',
        selectedSubSectorSlug: null,
        selectedSubSectorName: null,
        roles: [],
        roleSearch: '',
        selectedRole: null,
        comparison: null,
        compSearch: '',
        compTypeFilter: '',
        glossary: [],
        loadingRoles: false,
        loadingRoleDetail: false,
        roleSpecificFunctions: [],
        roleCompetenciesByType: {},
        roleTiers: [],
        activeTierId: null,
        openCompetencyId: null,
        competencyDetail: null,
        requiredLevelNumber: null,
        loadingCompetency: false,
        loadingComparison: false,
        loadingGlossary: false,
        levels: BIICF_LEVELS,
        openComp: null,

        init() {
            // Auto-load data the first time a tab that needs it becomes active.
            this.$watch('activeSection', (section) => {
                this.openComp = null;
                if (section === 'job-roles' && !this.selectedSubSectorSlug && this.roles.length === 0 && !this.loadingRoles) {
                    this.loadAllRoles();
                }
                if (section === 'competencies' && !this.selectedRole && this.glossary.length === 0 && !this.loadingGlossary) {
                    this.loadGlossary();
                }
            });
        },

        get filteredRoles() {
            if (!this.roleSearch.trim()) return this.roles;
            const q = this.roleSearch.toLowerCase();
            return this.roles.filter(r =>
                r.title.toLowerCase().includes(q) ||
                (r.sub_sector?.name || '').toLowerCase().includes(q)
            );
        },

        async selectSubSector(slug, name) {
            this.selectedSubSectorSlug = slug;
            this.selectedSubSectorName = name;
            this.roleSearch = '';
            this.activeSection = 'job-roles';
            await this.loadRoles();
        },

        clearSubSectorFilter() {
            this.selectedSubSectorSlug = null;
            this.selectedSubSectorName = null;
            this.loadAllRoles();
        },

        async loadRoles() {
            if (!this.selectedSubSectorSlug) {
                await this.loadAllRoles();
                return;
            }
            this.loadingRoles = true;
            try {
                const res = await fetch(`/student/biicf-explorer/sub-sectors/${this.selectedSubSectorSlug}/roles`);
                this.roles = await res.json();
            } finally {
                this.loadingRoles = false;
            }
        },

        async loadAllRoles() {
            this.loadingRoles = true;
            try {
                const res = await fetch(`/student/biicf-explorer/job-roles`);
                this.roles = await res.json();
            } finally {
                this.loadingRoles = false;
            }
        },

        async selectJobRole(slug) {
            this.selectedRole = null;
            this.comparison = null;
            this.roleSpecificFunctions = [];
            this.roleCompetenciesByType = {};
            this.roleTiers = [];
            this.activeTierId = null;
            this.openCompetencyId = null;
            this.competencyDetail = null;
            this.requiredLevelNumber = null;
            this.openComp = null;
            this.loadingRoleDetail = true;
            this.activeSection = 'career-paths';
            try {
                const res = await fetch(`/student/biicf-explorer/job-roles/${slug}`);
                const data = await res.json();
                this.selectedRole = data.job_role;
                // Grouped by job family server-side, as the book prints them.
                this.roleSpecificFunctions = data.specific_work_functions_by_family || [];
                /*
                 * Required proficiencies belong to a tier, so the
                 * role itself no longer carries a competencies
                 * list — reading one would have returned every
                 * competency once per tier. The tiers come with
                 * their own columns and the lowest is shown
                 * first: what it takes to enter the role.
                 */
                this.roleTiers = data.tiers || [];
                this.activeTierId = this.roleTiers[0]?.id ?? null;
                this.roleCompetenciesByType = data.competencies_by_type || {};
            } finally {
                this.loadingRoleDetail = false;
            }
        },

        clearRole() {
            this.selectedRole = null;
            this.comparison = null;
            this.roleSpecificFunctions = [];
            this.roleCompetenciesByType = {};
            this.roleTiers = [];
            this.activeTierId = null;
            this.openCompetencyId = null;
            this.competencyDetail = null;
            this.requiredLevelNumber = null;
            this.compSearch = '';
            this.openComp = null;
            this.activeSection = 'job-roles';
        },

        async compareToMe(slug) {
            this.loadingComparison = true;
            try {
                const res = await fetch(`/student/biicf-explorer/job-roles/${slug}/compare`);
                this.comparison = await res.json();
            } finally {
                this.loadingComparison = false;
            }
        },

        async loadGlossary() {
            if (this.glossary.length) return;
            this.loadingGlossary = true;
            try {
                const res = await fetch(`/student/biicf-explorer/competencies`);
                this.glossary = await res.json();
            } finally {
                this.loadingGlossary = false;
            }
        },

        get activeTier() {
            return this.roleTiers.find(t => t.id === this.activeTierId) || null;
        },

        filteredRoleCompetencies(type) {
            /*
             * From the tier being viewed, falling back to what
             * the role came with so a role whose tiers have not
             * been loaded still lists something.
             */
            const list = this.activeTier
                ? (this.activeTier.competencies_by_type[type] || [])
                : (this.roleCompetenciesByType[type] || []);

            if (!this.compSearch.trim()) return list;
            const q = this.compSearch.toLowerCase();
            return list.filter(c => c.name.toLowerCase().includes(q));
        },

        /*
         * Opens a competency where the levels already live
         * rather than unfolding a second copy of them in the
         * list. The level the role requires is carried along so
         * that rung can be marked once it is shown.
         */
        async openCompetency(id, requiredLevel) {
            this.openCompetencyId = id;
            this.requiredLevelNumber = requiredLevel?.number ?? null;
            this.competencyDetail = null;
            this.loadingCompetency = true;
            this.activeSection = 'proficiency-levels';
            try {
                const res = await fetch(`/student/biicf-explorer/competencies/${id}`);
                this.competencyDetail = await res.json();
            } finally {
                this.loadingCompetency = false;
            }
        },

        clearCompetency() {
            this.openCompetencyId = null;
            this.competencyDetail = null;
            this.requiredLevelNumber = null;
        },

        filteredGlossary(type) {
            if (this.compTypeFilter && this.compTypeFilter !== type) return [];
            let list = this.glossary.filter(c => c.type === type);
            if (this.compSearch.trim()) {
                const q = this.compSearch.toLowerCase();
                list = list.filter(c => c.name.toLowerCase().includes(q));
            }
            return list;
        },

        statusEntry(competencyId) {
            return (this.comparison?.comparison || []).find(c => c.competency.id === competencyId);
        },

        statusLabel(competencyId) {
            const entry = this.statusEntry(competencyId);
            if (!entry) return '';
            if (!entry.student_has_skill) return 'Not on your profile';
            return entry.meets_requirement ? '✓ Met' : `You: ${entry.student_level}`;
        },

        statusClass(competencyId) {
            const entry = this.statusEntry(competencyId);
            if (!entry) return '';
            if (!entry.student_has_skill) return 'not-logged';
            return entry.meets_requirement ? 'met' : 'missing';
        },

        /* Display helpers (no effect on the data or the steps) */
        toggleComp(id) {
            this.openComp = this.openComp === id ? null : id;
        },

        requiredLevel(comp) {
            const id = comp?.pivot?.proficiency_level_id;
            return id ? this.levels.find(level => level.id === id) || null : null;
        },

        /*
         * How many rungs a level is one of.
         *
         * Never the length of this.levels, which holds both
         * scales end to end: a soft skill at Advanced would read
         * as level 3 of 9. The scale says its own length, and
         * five is the technical scale a level with nothing to
         * say sits on.
         */
        rungsOf(level) {
            return level?.of ?? level?.scale_of ?? (level?.scale === 'soft' ? 4 : 5);
        },

        levelLabel(level, of) {
            if (!level) return '';
            return `${level.name} (level ${level.level_number} of ${of || this.rungsOf(level)})`;
        },

        yourLevelText(competencyId) {
            if (!this.comparison) return 'Press "Compare to my profile" to see';
            const entry = this.statusEntry(competencyId);
            if (!entry || !entry.student_has_skill) return 'Not on your profile yet';
            return entry.student_level_number
                ? `${entry.student_level} (level ${entry.student_level_number} of ${this.rungsOf(entry)})`
                : entry.student_level;
        },

        matchRatio() {
            const total = this.comparison?.summary?.total || 0;
            return total ? (this.comparison.summary.met || 0) / total : 0;
        },
    };
}
</script>

@endsection
