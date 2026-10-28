@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')

<style>
    .edit-profile {

    --paper: var(--bg);
    --ink: var(--text);
    --ink-dim: var(--text-muted);
    --line: var(--border);
    --gold: var(--accent);
    --rose: var(--danger);
    --green: var(--success);

        padding: 20px 0 40px;
        font-family: 'Inter', -apple-system, sans-serif;
        background: var(--paper);
        color: var(--ink);
    }

    .edit-profile .wrap {
        max-width: 960px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .edit-profile .head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
    }

    .edit-profile .head h1 {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: var(--primary);
        margin: 0;
    }

    .edit-profile .head h1 span {
        color: var(--accent);
    }

    .edit-profile .head .sub {
        color: var(--text-muted);
        font-size: 14px;
        margin-top: 4px;
    }

    .edit-profile .back {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13.5px;
        color: var(--text-muted);
        text-decoration: none;
        font-family: var(--font-body);
    }

    .edit-profile .back:hover {
        color: var(--ink);
    }

    .edit-profile .back svg {
        width: 14px;
        height: 14px;
    }

    /* Progress Card */
    .edit-profile .progress-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 8px;
        padding: 18px 20px;
        margin-bottom: 24px;
    }

    .edit-profile .progress-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13.5px;
        font-family: var(--font-body);
    }

    .edit-profile .progress-top span:first-child {
        font-weight: 500;
    }

    .edit-profile .progress-top span:last-child {
        font-family: var(--font-mono);
        color: var(--gold);
        font-weight: 600;
    }

    .edit-profile .bar {
        width: 100%;
        background: var(--line);
        border-radius: 100px;
        height: 8px;
        margin-top: 9px;
        overflow: hidden;
    }

    .edit-profile .bar .fill {
        height: 100%;
        background: var(--gold);
        border-radius: 100px;
        transition: width 0.6s ease;
    }

    .edit-profile .progress-note {
        font-size: 12px;
        color: var(--ink-dim);
        margin-top: 8px;
        font-family: var(--font-body);
    }

    /* Sections */
    .edit-profile .section {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 18px;
    }

    .edit-profile .section .title {
        font-family: 'Playfair Display', serif;
        font-size: 17px;
        font-weight: 600;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--ink);
    }

    .edit-profile .section .title svg {
        width: 17px;
        height: 17px;
        color: var(--gold);
    }

    .edit-profile .section .desc {
        font-size: 13px;
        color: var(--ink-dim);
        margin-top: -10px;
        margin-bottom: 16px;
        font-family: var(--font-body);
    }

    /* Grid */
    .edit-profile .cpbn-fgrid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .edit-profile .cpbn-fgrid .full {
        grid-column: 1 / -1;
    }

    /* Form Fields */
    .edit-profile .cpbn-field {
        margin-bottom: 0;
    }

    .edit-profile .cpbn-field label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: var(--ink-dim);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 4px;
        font-family: var(--font-body);
    }

    .edit-profile .cpbn-field label .req {
        color: var(--rose);
    }

    .edit-profile .cpbn-field input,
    .edit-profile .cpbn-field select,
    .edit-profile .cpbn-field textarea {
        width: 100%;
        padding: 10px 13px;
        border: 2px solid var(--line);
        border-radius: 8px;
        font-size: 14px;
        font-family: var(--font-body);
        background: #fff;
        color: var(--ink);
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .edit-profile .cpbn-field input:focus,
    .edit-profile .cpbn-field select:focus,
    .edit-profile .cpbn-field textarea:focus {
        outline: none;
        border-color: var(--gold);
        box-shadow: 0 0 0 4px rgba(207, 154, 61, 0.15);
    }

    .edit-profile .cpbn-field input:disabled {
        background: var(--line);
        color: var(--ink-dim);
        cursor: not-allowed;
    }

    .edit-profile .cpbn-field textarea {
        resize: vertical;
        min-height: 60px;
    }

    .edit-profile .cpbn-field .hint {
        font-size: 11px;
        color: var(--ink-dim);
        margin-top: 4px;
        font-family: var(--font-body);
    }

    .edit-profile .cpbn-field .error,
    .edit-profile .cpbn-error {
        color: var(--rose);
        font-size: 12px;
        margin-top: 6px;
        font-family: var(--font-body);
    }

    /* International Phone Input */
    .edit-profile .cpbn-field .iti {
        width: 100%;
    }

    .edit-profile .cpbn-field .iti input[type="tel"] {
        width: 100%;
    }

    /*
    * The country search still works, but we hide the
    * magnifying-glass icon because it clashes with the
    * existing form styling.
    */
    .edit-profile .iti__search-icon,
    .edit-profile .iti__search-icon-svg {
        display: none !important;
    }

    .edit-profile .iti__search-input {
        padding-left: 12px !important;
    }

    /* Checkbox Grid */
    .edit-profile .cpbn-checks {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }

    .edit-profile .cpbn-check {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--ink);
        cursor: pointer;
        font-family: var(--font-body);
    }

    .edit-profile .cpbn-check input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: var(--gold);
        cursor: pointer;
    }

    .edit-profile .cpbn-check:hover {
        color: var(--gold);
    }

    /*
     * A checkbox inside a field inherits the full-width text
     * input styling above — 100% wide, 10px of padding, a 2px
     * border — which squeezes the native tick out of sight, so
     * there was no telling whether the box was ticked.
     */
    .edit-profile .cpbn-field label.cpbn-check {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 24px 0 0;
        font-size: 13px;
        font-weight: 500;
        text-transform: none;
        letter-spacing: 0;
        color: var(--ink);
    }

    .edit-profile .cpbn-field .cpbn-check input[type="checkbox"] {
        appearance: auto;
        -webkit-appearance: checkbox;
        width: 18px;
        height: 18px;
        min-width: 18px;
        padding: 0;
        border: none;
        background: none;
        accent-color: var(--gold);
    }

    /*
     * And the state in words beside it, since a tick says what
     * the box is but not what it means for the qualification.
     */
    .edit-profile .cpbn-check-state {
        font-size: 11px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 100px;
        background: rgba(45, 143, 92, 0.12);
        color: var(--green);
        white-space: nowrap;
    }

    .edit-profile .cpbn-check-state::after {
        content: 'Completed';
    }

    .edit-profile .cpbn-check input[type="checkbox"]:checked ~ .cpbn-check-state {
        background: var(--gold-wash);
        color: #8a6420;
    }

    .edit-profile .cpbn-check input[type="checkbox"]:checked ~ .cpbn-check-state::after {
        content: 'Still studying';
    }

    .edit-profile .cpbn-check-note {
        font-size: 11px;
        color: var(--ink-dim);
        margin-top: 12px;
        font-family: var(--font-body);
    }

    .edit-profile .cpbn-competency-group {
        margin-top: 20px;
    }

    .edit-profile .cpbn-competency-heading {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
    }

    .edit-profile .cpbn-competency-heading > div {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .edit-profile .cpbn-competency-heading strong {
        font-size: 14px;
        color: var(--ink);
        font-family: var(--font-body);
    }

    .edit-profile .cpbn-competency-heading span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 24px;
        padding: 0 7px;
        border-radius: 100px;
        background: var(--gold-wash);
        color: #7b5c23;
        font-size: 11px;
        font-weight: 700;
    }

    .edit-profile .cpbn-competency-heading small {
        color: var(--ink-dim);
        font-size: 11px;
    }

    .edit-profile .bdqf-help {
        margin: 0 0 16px;
        border: 1px solid var(--line, #e7e2d4);
        border-radius: 6px;
        background: #fcfbf7;
    }

    .edit-profile .bdqf-help > summary {
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 600;
        color: var(--ink, #0d1a2b);
        cursor: pointer;
    }

    .edit-profile .bdqf-help[open] > summary {
        border-bottom: 1px solid var(--line, #e7e2d4);
    }

    .edit-profile .bdqf-help-intro {
        margin: 0;
        padding: 12px 14px 0;
        font-size: 12.5px;
        color: var(--ink-dim, #5b6675);
        line-height: 1.6;
    }

    .edit-profile .bdqf-ladder {
        display: flex;
        flex-direction: column;
        gap: 2px;
        padding: 12px 14px 14px;
    }

    .edit-profile .bdqf-rung {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 8px 0;
        border-top: 1px solid #efece1;
    }

    .edit-profile .bdqf-rung:first-child {
        border-top: 0;
    }

    .edit-profile .bdqf-rung-level {
        flex: 0 0 64px;
        font-size: 12px;
        font-weight: 700;
        color: var(--gold, #cf9a3d);
        white-space: nowrap;
    }

    .edit-profile .bdqf-rung-body {
        flex: 1;
        min-width: 0;
    }

    .edit-profile .bdqf-sector {
        font-size: 12.5px;
        color: var(--ink, #0d1a2b);
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .edit-profile .bdqf-sector-name {
        display: inline-block;
        min-width: 150px;
        color: var(--ink-dim, #5b6675);
    }

    .edit-profile .bdqf-note {
        margin: 4px 0 0;
        font-size: 11.5px;
        color: var(--ink-dim, #5b6675);
        line-height: 1.55;
    }

    .edit-profile .cpbn-sector-filter {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 12px;
    }

    /*
     * Two rows of chips need saying which is which. One row of
     * sub-sectors and one of levels, unlabelled and stacked,
     * reads as one long row that wrapped.
     */
    .edit-profile .cpbn-filter-row {
        display: grid;
        grid-template-columns: 76px minmax(0, 1fr);
        align-items: start;
        gap: 10px;
    }

    .edit-profile .cpbn-filter-label {
        padding-top: 6px;
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: .02em;
        color: var(--ink-dim, #5b6675);
    }

    /*
     * The programme row. Marked as the one row that is not the
     * student's to add or remove, so it does not read as an
     * ordinary entry somebody has filled in for them.
     */
    .edit-profile .cpbn-certification-card.is-programme-row {
        border-color: var(--gold, #cf9a3d);
        background: rgba(207, 154, 61, .04);
    }

    .edit-profile .cpbn-row-badge {
        margin: 0 0 12px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--gold-ink, #8a6519);
    }

    .edit-profile .cpbn-fixed-value {
        margin: 0;
        padding: 9px 0;
        font-weight: 600;
        color: var(--ink, #0d1a2b);
    }

    .edit-profile .cpbn-filter-head {
        display: flex;
        align-items: baseline;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 10px;
    }

    .edit-profile .cpbn-filter-toggle {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0;
        font: inherit;
        font-size: 12px;
        font-weight: 600;
        color: var(--ink-dim, #5b6675);
        background: none;
        border: 0;
        cursor: pointer;
    }

    .edit-profile .cpbn-filter-toggle:hover {
        color: var(--ink, #0d1a2b);
    }

    .edit-profile .cpbn-filter-toggle-caret {
        display: inline-block;
        font-size: 10px;
        transition: transform .15s ease;
    }

    .edit-profile .cpbn-filter-toggle[aria-expanded="false"] .cpbn-filter-toggle-caret {
        transform: rotate(-90deg);
    }

    /* What is filtered, when the chips themselves are put away. */
    .edit-profile .cpbn-filter-active {
        font-size: 11.5px;
        color: var(--gold-ink, #8a6519);
    }

    .edit-profile .cpbn-filters[hidden] {
        display: none;
    }

    .edit-profile .cpbn-filter-summary {
        margin: 0 0 12px;
        font-size: 12px;
        line-height: 1.55;
        color: var(--ink-dim, #5b6675);
    }

    /*
     * A competency on screen because the student ticked it, not
     * because the filter asked for it. Without the distinction
     * the two look like the filter leaking.
     */
    .edit-profile .cpbn-competency-item[data-kept-by-tick="true"] {
        border-left: 3px solid var(--gold, #cf9a3d);
    }

    /*
     * Thirty-eight roles is longer than a row. Capped and
     * scrolled so the three filters together do not push the
     * competencies themselves off the screen.
     */
    .edit-profile .cpbn-role-filter {
        max-height: 92px;
        overflow-y: auto;
        align-content: flex-start;
        padding-right: 2px;
    }

    .edit-profile .cpbn-filter-empty {
        margin: 0 0 12px;
        font-size: 12px;
        line-height: 1.55;
        color: var(--ink-dim, #5b6675);
    }

    .edit-profile .cpbn-filter-reset {
        padding: 0;
        font: inherit;
        color: var(--gold-ink, #8a6519);
        background: none;
        border: 0;
        text-decoration: underline;
        cursor: pointer;
    }

    @media (max-width: 560px) {
        .edit-profile .cpbn-filter-row {
            grid-template-columns: minmax(0, 1fr);
            gap: 2px;
        }

        .edit-profile .cpbn-filter-label {
            padding-top: 0;
        }
    }

    .edit-profile .cpbn-sector-chip {
        padding: 5px 11px;
        font: inherit;
        font-size: 12px;
        font-weight: 500;
        color: var(--ink-dim, #5b6675);
        background: #fff;
        border: 1px solid var(--line, #e7e2d4);
        border-radius: 999px;
        cursor: pointer;
        transition: border-color .15s ease, color .15s ease, background .15s ease;
    }

    .edit-profile .cpbn-sector-chip:hover {
        border-color: var(--gold, #cf9a3d);
        color: var(--ink, #0d1a2b);
    }

    .edit-profile .cpbn-sector-chip.is-on {
        color: var(--ink, #0d1a2b);
        background: rgba(207, 154, 61, .12);
        border-color: var(--gold, #cf9a3d);
        font-weight: 600;
    }

    .edit-profile .cpbn-competency-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .edit-profile .cpbn-competency-item[hidden] {
        display: none;
    }

    .edit-profile .cpbn-competency-controls {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .edit-profile .cpbn-competency-page-button,
    .edit-profile .cpbn-competency-toggle {
        border: 1px solid var(--gold);
        border-radius: 7px;
        padding: 8px 14px;
        background: var(--gold-wash);
        color: #7b5c23;
        font-size: 12px;
        font-weight: 600;
        font-family: var(--font-body);
        cursor: pointer;
        transition: 0.15s ease;
    }

    .edit-profile .cpbn-competency-page-button:hover:not(:disabled),
    .edit-profile .cpbn-competency-toggle:hover {
        background: var(--gold);
        color: var(--ink);
    }

    .edit-profile .cpbn-competency-page-button:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .edit-profile .cpbn-competency-page-status {
        min-width: 90px;
        text-align: center;
        color: var(--ink-dim);
        font-size: 12px;
        font-family: var(--font-body);
    }

    .edit-profile .cpbn-competency-controls [hidden] {
        display: none;
    }

    .edit-profile .cpbn-competency-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 12px;
        border: 1px solid var(--line);
        border-radius: 9px;
        background: #fff;
        transition:
            border-color 0.15s ease,
            background 0.15s ease;
    }

    .edit-profile .cpbn-competency-item.selected {
        border-color: var(--gold);
        background: var(--gold-wash);
    }

    .edit-profile .cpbn-competency-choice {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
        min-width: 0;
        cursor: pointer;
        font-size: 12px;
        font-weight: 500;
        color: var(--ink);
    }

    .edit-profile .cpbn-competency-choice input {
        flex: 0 0 auto;
    }

    .edit-profile .cpbn-competency-choice span {
        line-height: 1.35;
    }

    .edit-profile .biicf-proficiency-select,
    .edit-profile .cpbn-custom-level-select {
        min-width: 132px;
        padding: 7px 9px;
        border: 1px solid var(--line);
        border-radius: 7px;
        background: #fff;
        color: var(--ink);
        font-size: 11px;
        font-family: var(--font-body);
    }

    .edit-profile .biicf-proficiency-select:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .edit-profile .cpbn-additional-toggle {
        margin-top: 20px;
    }

    .edit-profile .cpbn-custom-chip {
        border-radius: 10px;
    }

    .edit-profile .cpbn-custom-chip select {
        margin-left: 3px;
    }

    @media (max-width: 850px) {
        .edit-profile .cpbn-competency-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 560px) {
        .edit-profile .cpbn-competency-item {
            align-items: flex-start;
            flex-direction: column;
        }

        .edit-profile .biicf-proficiency-select,
        .edit-profile .cpbn-custom-level-select {
            width: 100%;
        }

        .edit-profile .cpbn-competency-heading {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    /* Additional Skills / Interests */
    .edit-profile .cpbn-additional {
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px solid var(--line);
    }

    .edit-profile .cpbn-additional[hidden] {
        display: none;
    }

    .edit-profile .cpbn-additional-heading {
        margin-bottom: 10px;
        font-size: 12px;
        font-weight: 600;
        color: var(--ink-dim);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-family: var(--font-body);
    }

    .edit-profile .cpbn-custom-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 12px;
    }

    .edit-profile .cpbn-custom-chip {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 10px 7px 12px;
        border: 1px solid var(--gold);
        border-radius: 100px;
        background: var(--gold-wash);
        color: #7b5c23;
        font-size: 12px;
        font-weight: 500;
        font-family: var(--font-body);
    }

    .edit-profile .cpbn-custom-chip button {
        width: 20px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 50%;
        background: transparent;
        color: inherit;
        cursor: pointer;
        padding: 0;
        font-size: 14px;
        line-height: 1;
    }

    .edit-profile .cpbn-custom-chip button:hover {
        background: rgba(123, 92, 35, 0.12);
    }

    .edit-profile .cpbn-custom-entry {
        display: flex;
        align-items: stretch;
        gap: 8px;
    }

    .edit-profile .cpbn-custom-entry input {
        flex: 1;
        min-width: 0;
        padding: 10px 13px;
        border: 2px solid var(--line);
        border-radius: 8px;
        font-size: 14px;
        font-family: var(--font-body);
        background: #fff;
        color: var(--ink);
    }

    .edit-profile .cpbn-custom-entry input:focus {
        outline: none;
        border-color: var(--gold);
        box-shadow: 0 0 0 4px rgba(207, 154, 61, 0.15);
    }

    .edit-profile .cpbn-custom-add {
        flex: 0 0 auto;
        border: 0;
        border-radius: 8px;
        padding: 0 16px;
        background: var(--gold);
        color: var(--ink);
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        font-family: var(--font-body);
    }

    .edit-profile .cpbn-custom-add:hover {
        background: var(--gold-bright);
    }

    .edit-profile .cpbn-custom-example {
        margin-top: 7px;
        color: var(--ink-dim);
        font-size: 11px;
        font-family: var(--font-body);
    }

    .edit-profile .cpbn-custom-feedback {
        margin-top: 10px;
        padding: 10px 12px;
        border-radius: 7px;
        font-size: 12px;
        line-height: 1.5;
        font-family: var(--font-body);
    }

    .edit-profile .cpbn-custom-feedback[hidden] {
        display: none;
    }

    .edit-profile .cpbn-custom-feedback.warning {
        background: var(--gold-wash);
        color: #7b5c23;
        border: 1px solid rgba(207, 154, 61, 0.35);
    }

    .edit-profile .cpbn-custom-feedback.error {
        background: var(--rose-wash);
        color: #9a453c;
        border: 1px solid rgba(198, 91, 78, 0.25);
    }

    .edit-profile .cpbn-custom-warning-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 9px;
    }

    .edit-profile .cpbn-custom-warning-actions[hidden] {
        display: none;
    }

    .edit-profile .cpbn-custom-warning-actions button {
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        font-family: var(--font-body);
    }

    .edit-profile .cpbn-custom-edit {
        border: 1px solid var(--line);
        background: #fff;
        color: var(--ink-dim);
    }

    .edit-profile .cpbn-custom-add-anyway {
        border: 1px solid var(--gold);
        background: var(--gold);
        color: var(--ink);
    }

    /* Project & Certification Cards */
    .edit-profile .cpbn-project-card,
    .edit-profile .cpbn-certification-card {
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 16px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .edit-profile .cpbn-project-card:hover,
    .edit-profile .cpbn-certification-card:hover {
        border-color: var(--gold);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .edit-profile .cpbn-project-number {
        font-family: var(--font-mono);
        font-size: 11px;
        color: var(--ink-dim);
        background: #eef1f5;
        padding: 2px 12px;
        border-radius: 100px;
        display: inline-block;
        margin-bottom: 12px;
    }

    /* Remove Buttons */
    .edit-profile .cpbn-remove-project,
    .edit-profile .cpbn-remove-qualification,
    .edit-profile .cpbn-remove-certification {
        background: var(--rose-wash);
        color: var(--rose);
        border: 1px solid transparent;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 12px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-family: var(--font-body);
    }

    .edit-profile .cpbn-remove-project:hover,
    .edit-profile .cpbn-remove-qualification:hover,
    .edit-profile .cpbn-remove-certification:hover {
        background: var(--rose);
        color: white;
    }

    /* Add Buttons */
    .edit-profile .cpbn-add-project,
    .edit-profile .cpbn-add-btn,
    .edit-profile .cpbn-add-certification {
        background: var(--gold-wash);
        color: #8a6420;
        border: 2px dashed var(--gold);
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        justify-content: center;
        font-family: var(--font-body);
    }

    .edit-profile .cpbn-add-project:hover,
    .edit-profile .cpbn-add-btn:hover,
    .edit-profile .cpbn-add-certification:hover {
        background: var(--gold);
        color: var(--ink);
        border-style: solid;
    }

    /*
     * Projects and certifications are optional, and their Add
     * button should ask for about as much attention as that
     * deserves. A full-width dashed panel asks for a great deal,
     * and only looked deliberate once the pointer was on it. This
     * one is small, solid, and sits at the end of the section
     * where the next thing to do belongs.
     */
    .edit-profile .cpbn-section-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 14px;
    }

    .edit-profile .cpbn-optional {
        margin: 0;
        font-size: 12px;
        color: var(--ink-dim);
        font-family: var(--font-body);
    }

    .edit-profile .cpbn-section-foot .cpbn-add-project,
    .edit-profile .cpbn-section-foot .cpbn-add-certification {
        width: auto;
        padding: 8px 16px;
        font-size: 12px;
        border: 1px solid var(--gold);
        border-radius: 999px;
        margin-left: auto;
    }

    /* File Upload Helpers */
    .edit-profile .cpbn-file-note {
        display: block;
        font-size: 11px;
        color: var(--ink-dim);
        margin-top: 4px;
        font-family: var(--font-body);
    }

    .edit-profile .cpbn-file-existing {
        display: block;
        margin-top: 8px;
        padding: 8px 10px;
        background: var(--green-wash);
        color: var(--green);
        border-radius: 6px;
        font-size: 12px;
        font-family: var(--font-body);
    }

    .edit-profile .cpbn-file-existing-name {
        display: block;
        margin-top: 4px;
        font-weight: 400;
        overflow-wrap: anywhere;
    }

    .edit-profile .cpbn-file-view {
        display: inline-block;
        margin-top: 7px;
        color: #386747;
        font-size: 12px;
        font-weight: 600;
        text-decoration: underline;
    }

    .edit-profile .cpbn-file-missing {
        display: block;
        margin-top: 8px;
        padding: 8px 10px;
        background: var(--rose-wash);
        color: #9a453c;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
    }

    .edit-profile .cpbn-file-selected {
        display: block;
        margin-top: 8px;
        padding: 8px 10px;
        background: var(--gold-wash);
        color: #7b5c23;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
    }

    .edit-profile .cpbn-clear-file {
        display: inline-block;
        margin-top: 7px;
        border: 0;
        background: transparent;
        color: #8a6420;
        cursor: pointer;
        font-size: 12px;
        padding: 0;
    }

    .edit-profile .cpbn-clear-file:hover {
        text-decoration: underline;
    }

    
    .edit-profile .cpbn-file-selected[hidden],
    .edit-profile .cpbn-clear-file[hidden] {
        display: none;
    }

    /* Submit Buttons */
    .edit-profile .cpbn-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 22px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        font-family: var(--font-body);
    }

    .edit-profile .cpbn-btn svg {
        width: 18px;
        height: 18px;
        flex: 0 0 18px;
    }

    .edit-profile .cpbn-btn-primary {
        background: var(--gold);
        color: var(--ink);
    }

    .edit-profile .cpbn-btn-primary:hover {
        background: var(--gold-bright);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(207, 154, 61, 0.25);
    }

    .edit-profile .submit-row {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 24px;
    }

    /* Profile Picture */
    .edit-profile .cpbn-profile-picture {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 18px;
        margin-bottom: 20px;
        border: 1px solid var(--line);
        border-radius: 12px;
        background: var(--paper);
    }

    .edit-profile .cpbn-profile-picture-preview {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(
            135deg,
            var(--primary),
            var(--primary-light)
        );
        color: white;
        font-size: 34px;
        font-weight: 700;
    }

    .edit-profile .cpbn-profile-picture-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .edit-profile .cpbn-profile-picture-details {
        flex: 1;
        min-width: 0;
    }

    .edit-profile .cpbn-profile-picture-details h4 {
        margin: 0 0 4px;
        font-size: 14px;
        color: var(--ink);
    }

    .edit-profile .cpbn-profile-picture-details p {
        margin: 0 0 12px;
        font-size: 12px;
        line-height: 1.5;
        color: var(--ink-dim);
    }

    .edit-profile .cpbn-profile-picture-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .edit-profile .cpbn-profile-picture-input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        overflow: hidden;
        pointer-events: none;
    }

    .edit-profile .cpbn-profile-picture-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 8px;
        border: 1px solid var(--line);
        background: white;
        color: var(--ink);
        font-family: var(--font-body);
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .edit-profile .cpbn-profile-picture-button:hover {
        border-color: var(--gold);
        color: var(--gold);
    }

    .edit-profile .cpbn-profile-picture-button.remove {
        color: var(--rose);
    }

    .edit-profile .cpbn-profile-picture-button.remove:hover {
        border-color: var(--rose);
    }

    .edit-profile .cpbn-profile-picture-file-name {
        margin-top: 8px;
        font-size: 11px;
        color: var(--ink-dim);
        word-break: break-word;
    }

    @media (max-width: 720px) {
        .edit-profile .cpbn-profile-picture {
            align-items: flex-start;
        }

        .edit-profile .cpbn-profile-picture-preview {
            width: 82px;
            height: 82px;
        }
        .edit-profile .cpbn-fgrid {
            grid-template-columns: 1fr;
        }

        .edit-profile .cpbn-fgrid .full {
            grid-column: auto;
        }

        .edit-profile .cpbn-checks {
            grid-template-columns: repeat(2, 1fr);
        }

        .edit-profile .head h1 {
            font-size: 20px;
        }

        .edit-profile .submit-row {
            position: sticky;
            bottom: 0;
            z-index: 20;
            flex-direction: column;
            margin: 24px -4px 0;
            padding: 10px 4px calc(10px + env(safe-area-inset-bottom, 0px));
            background: linear-gradient(to top, var(--paper, #fff) 70%, rgba(255, 255, 255, 0));
        }

        .edit-profile .submit-row .cpbn-btn {
            width: 100%;
            min-height: 44px;
            padding: 10px 18px;
            font-size: 14px;
            line-height: 1.2;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .edit-profile .cpbn-profile-picture {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .edit-profile .cpbn-profile-picture-actions {
            justify-content: center;
        }
        .edit-profile .cpbn-checks {
            grid-template-columns: 1fr;
        }

        .edit-profile .head {
            flex-direction: column;
        }

        .edit-profile .cpbn-custom-entry {
            flex-direction: column;
        }

        .edit-profile .cpbn-custom-add {
            min-height: 42px;
        }
    }
</style>

<style>
    /* ==========================================================
       Edit Profile redesign
       Layout, theme and spacing only. Every field, name, id and
       script above is unchanged. Selectors are prefixed so they
       win over the page's earlier rules and the shared polish.
       ========================================================== */
    body .edit-profile .wrap {
        max-width: 1180px;
    }

    body .edit-profile .ep-layout {
        display: grid;
        grid-template-columns: 276px minmax(0, 1fr);
        gap: 28px;
        align-items: start;
    }

    body .edit-profile .ep-main {
        min-width: 0;
    }

    /* ---------- Side column ---------- */
    body .edit-profile .ep-aside {
        position: sticky;
        top: 88px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        max-height: calc(100vh - 110px);
        overflow-y: auto;
        scrollbar-width: thin;
    }

    body .edit-profile .ep-layout .progress-card {
        margin: 0;
        padding: 20px;
        background: linear-gradient(160deg, #0d1f33 0%, #1a3a5c 70%, #2a5a8c 100%);
        border: none;
        border-radius: 16px;
        color: #fff;
        box-shadow: 0 18px 36px -26px rgba(13, 31, 51, 0.8);
    }

    body .edit-profile .ep-layout .progress-top span {
        color: rgba(255, 255, 255, 0.82);
        font-size: 13px;
        font-weight: 600;
    }

    body .edit-profile .ep-layout .progress-top span:last-child {
        font-family: 'Playfair Display', serif;
        font-size: 26px;
        font-weight: 700;
        color: #e8d4a0;
    }

    body .edit-profile .ep-layout .progress-card .bar {
        height: 8px;
        background: rgba(255, 255, 255, 0.16);
    }

    body .edit-profile .ep-layout .progress-note {
        margin: 12px 0 0;
        font-size: 12.5px;
        line-height: 1.5;
        color: rgba(255, 255, 255, 0.72);
    }

    body .edit-profile .ep-nav {
        padding: 12px;
        background: #fff;
        border: 1px solid #e6e8ec;
        border-radius: 16px;
        box-shadow: 0 10px 30px -26px rgba(13, 31, 51, 0.45);
    }

    body .edit-profile .ep-nav-title {
        margin: 4px 10px 8px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #8a94a1;
    }

    body .edit-profile .ep-nav-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 9px 10px;
        border-radius: 10px;
        color: #4b5868;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: background 0.2s ease, color 0.2s ease;
    }

    body .edit-profile .ep-nav-link:hover {
        background: #f6f7f9;
        color: #1a3a5c;
    }

    body .edit-profile .ep-nav-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 26px;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #eef0f3;
        color: #1a3a5c;
        font-size: 12px;
        font-weight: 700;
        transition: background 0.2s ease, color 0.2s ease;
    }

    body .edit-profile .ep-nav-link.is-active {
        background: #faf6ea;
        color: #1a3a5c;
        font-weight: 600;
    }

    body .edit-profile .ep-nav-link.is-active .ep-nav-num {
        background: #1a3a5c;
        color: #e8d4a0;
    }

    /* ---------- Section cards ---------- */
    body .edit-profile .ep-layout .section {
        scroll-margin-top: 96px;
        margin-bottom: 22px;
        padding: 28px;
        background: #fff;
        border: 1px solid #e6e8ec;
        border-radius: 18px;
        box-shadow: 0 10px 30px -26px rgba(13, 31, 51, 0.45);
    }

    body .edit-profile .ep-layout .section .title {
        gap: 12px;
        margin-bottom: 22px;
        padding-bottom: 16px;
        border-bottom: 1px solid #eef0f3;
        font-family: 'Playfair Display', serif;
        font-size: 19px;
        font-weight: 700;
        color: #1a3a5c;
    }

    body .edit-profile .ep-layout .section .desc {
        margin-top: -8px;
        margin-bottom: 20px;
        color: #6b7785;
        line-height: 1.6;
    }

    /* ---------- Fields ---------- */
    body .edit-profile .ep-layout .cpbn-fgrid {
        gap: 18px 20px;
    }

    body .edit-profile .ep-layout .cpbn-field label {
        margin-bottom: 7px;
        font-size: 13px;
        font-weight: 600;
        color: #1a3a5c;
    }

    body .edit-profile .ep-layout .cpbn-field input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
    body .edit-profile .ep-layout .cpbn-field select,
    body .edit-profile .ep-layout .cpbn-field textarea {
        min-height: 46px;
        padding: 10px 14px;
        border: 1px solid #dfe3e8;
        border-radius: 12px;
        background: #fcfcfd;
        font-size: 14px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    body .edit-profile .ep-layout .cpbn-field textarea {
        min-height: 110px;
        line-height: 1.6;
    }

    body .edit-profile .ep-layout .cpbn-field input:not([type="checkbox"]):not([type="radio"]):not([type="file"]):hover,
    body .edit-profile .ep-layout .cpbn-field select:hover,
    body .edit-profile .ep-layout .cpbn-field textarea:hover {
        border-color: #cdd3da;
    }

    body .edit-profile .ep-layout .cpbn-field input:not([type="checkbox"]):not([type="radio"]):not([type="file"]):focus,
    body .edit-profile .ep-layout .cpbn-field select:focus,
    body .edit-profile .ep-layout .cpbn-field textarea:focus {
        background: #fff;
        border-color: #c9a84c;
        box-shadow: 0 0 0 4px rgba(201, 168, 76, 0.16);
    }

    body .edit-profile .ep-layout .cpbn-field input:disabled {
        background: #f3f4f6;
        color: #6b7785;
    }

    body .edit-profile .ep-layout .cpbn-field .hint {
        margin-top: 6px;
        font-size: 12px;
        line-height: 1.5;
        color: #8a94a1;
    }

    body .edit-profile .ep-layout .cpbn-check {
        border-radius: 12px;
    }

    /* ---------- Inner cards ---------- */
    body .edit-profile .ep-layout .cpbn-profile-picture {
        padding: 20px;
        border: 1px solid #e8d4a0;
        border-radius: 16px;
        background: linear-gradient(135deg, #faf6ea 0%, #ffffff 100%);
    }

    body .edit-profile .ep-layout .cpbn-project-card,
    body .edit-profile .ep-layout .cpbn-certification-card,
    body .edit-profile .ep-layout .cpbn-competency-item {
        border: 1px solid #e6e8ec;
        border-radius: 14px;
        background: #fbfbfd;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    body .edit-profile .ep-layout .cpbn-project-card:hover,
    body .edit-profile .ep-layout .cpbn-certification-card:hover,
    body .edit-profile .ep-layout .cpbn-competency-item:hover {
        border-color: #e8d4a0;
        box-shadow: 0 12px 26px -22px rgba(13, 31, 51, 0.5);
    }

    body .edit-profile .ep-layout .cpbn-competency-item.selected {
        border-color: #e8d4a0;
        background: #faf6ea;
    }

    body .edit-profile .ep-layout .cpbn-project-number {
        display: inline-flex;
        align-items: center;
        padding: 3px 11px;
        border-radius: 999px;
        background: #1a3a5c;
        color: #e8d4a0;
        font-size: 12px;
        font-weight: 700;
    }

    body .edit-profile .ep-layout .cpbn-competency-heading {
        padding-left: 12px;
        border-left: 4px solid #c9a84c;
    }

    body .edit-profile .ep-layout .cpbn-custom-chip {
        border-radius: 999px;
        background: #eef3f9;
        border-color: #d3deea;
        color: #1a3a5c;
    }

    body .edit-profile .ep-layout .cpbn-add-btn,
    body .edit-profile .ep-layout .cpbn-add-project,
    body .edit-profile .ep-layout .cpbn-add-certification {
        border: 1px dashed #c9a84c;
        border-radius: 12px;
        background: #faf6ea;
        color: #1a3a5c;
        font-weight: 600;
    }

    body .edit-profile .ep-layout .cpbn-add-btn:hover,
    body .edit-profile .ep-layout .cpbn-add-project:hover,
    body .edit-profile .ep-layout .cpbn-add-certification:hover {
        background: #f4ecd2;
        border-style: solid;
    }

    body .edit-profile .ep-layout .cpbn-competency-page-button,
    body .edit-profile .ep-layout .cpbn-competency-toggle {
        border-radius: 999px;
    }

    /* ---------- Floating save bar ---------- */
    body .edit-profile .ep-layout .submit-row {
        position: sticky;
        bottom: 16px;
        z-index: 30;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin: 8px 0 0;
        padding: 12px 14px 12px 22px;
        background: rgba(255, 255, 255, 0.94);
        -webkit-backdrop-filter: blur(10px);
        backdrop-filter: blur(10px);
        border: 1px solid #e6e8ec;
        border-radius: 16px;
        box-shadow: 0 18px 40px -22px rgba(13, 31, 51, 0.55);
    }

    body .edit-profile .ep-save-note {
        margin: 0;
        font-size: 13px;
        color: #6b7785;
    }

    body .edit-profile .ep-layout .submit-row .cpbn-btn {
        min-height: 46px;
        padding: 10px 26px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #1a3a5c 0%, #2a5a8c 100%);
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        box-shadow: 0 10px 22px -14px rgba(13, 31, 51, 0.8);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    body .edit-profile .ep-layout .submit-row .cpbn-btn svg {
        color: #e8d4a0;
    }

    body .edit-profile .ep-layout .submit-row .cpbn-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 28px -16px rgba(13, 31, 51, 0.85);
    }

    /* ---------- Tablet and phone ---------- */
    @media (max-width: 1000px) {
        body .edit-profile .ep-layout {
            grid-template-columns: minmax(0, 1fr);
            gap: 16px;
        }

        body .edit-profile .ep-aside {
            position: static;
            max-height: none;
            overflow: visible;
        }

        body .edit-profile .ep-nav {
            display: flex;
            gap: 6px;
            padding: 8px;
            overflow-x: auto;
            scrollbar-width: none;
        }

        body .edit-profile .ep-nav::-webkit-scrollbar {
            display: none;
        }

        body .edit-profile .ep-nav-title {
            display: none;
        }

        body .edit-profile .ep-nav-link {
            flex: 0 0 auto;
            white-space: nowrap;
            padding: 8px 12px;
        }
    }

    @media (max-width: 720px) {
        body .edit-profile .ep-layout .section {
            padding: 20px 16px;
            border-radius: 16px;
        }

        body .edit-profile .ep-layout .section .title {
            font-size: 17px;
        }

        body .edit-profile .ep-layout .submit-row {
            bottom: 0;
            margin: 8px -4px 0;
            padding: 10px 12px calc(10px + env(safe-area-inset-bottom, 0px));
            border-radius: 16px 16px 0 0;
            background: rgba(255, 255, 255, 0.96);
        }

        body .edit-profile .ep-save-note {
            display: none;
        }

        body .edit-profile .ep-layout .submit-row .cpbn-btn {
            width: 100%;
            min-height: 44px;
            justify-content: center;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        body .edit-profile .ep-layout .submit-row .cpbn-btn,
        body .edit-profile .ep-nav-link,
        body .edit-profile .ep-layout .cpbn-project-card,
        body .edit-profile .ep-layout .cpbn-certification-card,
        body .edit-profile .ep-layout .cpbn-competency-item {
            transition: none;
        }
    }
</style>

<div class="edit-profile">
    <div class="wrap">

        <div class="head">
            <div>
                <h1>Complete Your <span>Profile</span></h1>
                <p class="sub">Fill in your profile for better career recommendations</p>
            </div>

            <a href="{{ route('student.profile') }}" class="back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
        </div>

        <div class="ep-layout">
            <aside class="ep-aside">
            <!-- Progress -->
        <div class="progress-card">
            <div class="progress-top">
                <span>Profile Completion</span>
                <span>{{ $profileCompletion ?? 0 }}%</span>
            </div>

            <div class="bar">
                <div
                    class="fill"
                    style="width: {{ $profileCompletion ?? 0 }}%"
                ></div>
            </div>

            <p class="progress-note">
                                @if($profileCompletion >= 70)
                    <i class="fas fa-circle-check" style="color:var(--green);"></i>
                    Profile complete — ready for career matching!
                @else
                    Your progress can be saved now and completed later.
                @endif
            </p>
        </div>

                <nav class="ep-nav" aria-label="Profile sections">
                    <p class="ep-nav-title">Sections</p>
                    <a href="#ep-personal" class="ep-nav-link" data-ep-target="ep-personal">
                        <span class="ep-nav-num">1</span>
                        <span class="ep-nav-label">Personal Information</span>
                    </a>
                    <a href="#ep-qualifications" class="ep-nav-link" data-ep-target="ep-qualifications">
                        <span class="ep-nav-num">2</span>
                        <span class="ep-nav-label">Qualifications</span>
                    </a>
                    <a href="#ep-skills" class="ep-nav-link" data-ep-target="ep-skills">
                        <span class="ep-nav-num">3</span>
                        <span class="ep-nav-label">Skills &amp; Competencies</span>
                    </a>
                    <a href="#ep-interests" class="ep-nav-link" data-ep-target="ep-interests">
                        <span class="ep-nav-num">4</span>
                        <span class="ep-nav-label">Interests &amp; Preferences</span>
                    </a>
                    <a href="#ep-projects" class="ep-nav-link" data-ep-target="ep-projects">
                        <span class="ep-nav-num">5</span>
                        <span class="ep-nav-label">Projects &amp; Experience</span>
                    </a>
                    <a href="#ep-certifications" class="ep-nav-link" data-ep-target="ep-certifications">
                        <span class="ep-nav-num">6</span>
                        <span class="ep-nav-label">Certifications</span>
                    </a>
                    <a href="#ep-aspirations" class="ep-nav-link" data-ep-target="ep-aspirations">
                        <span class="ep-nav-num">7</span>
                        <span class="ep-nav-label">Career Aspirations</span>
                    </a>
                </nav>
            </aside>

            <div class="ep-main">

        <form
            method="POST"
            action="{{ route('student.profile.update') }}"
            enctype="multipart/form-data"
            id="profile-form"
            data-max-file-bytes="{{ App\Http\Controllers\Admin\AdvertisementController::uploadLimitKilobytes() * 1024 }}"
            data-max-request-bytes="{{ App\Http\Controllers\Admin\AdvertisementController::requestLimitKilobytes() * 1024 }}"
            data-confirm-update
            data-item-name="your profile"
        >
            @csrf
            @method('PUT')

            <!-- SECTION 1: Personal Information -->
            <div class="section" id="ep-personal">
                <div class="title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/>
                    </svg>
                    Personal Information
                </div>

                @php
                    $profilePicturePath =
                        $user->profile?->profile_picture;

                    $profilePictureUrl =
                        $profilePicturePath
                            ? asset(
                                'storage/' .
                                ltrim(
                                    $profilePicturePath,
                                    '/'
                                )
                            )
                            : null;

                    $profileInitial =
                        strtoupper(
                            substr(
                                    $user->name
                                    ?? 'S',
                                0,
                                1
                            )
                        );
                @endphp

                <div class="cpbn-profile-picture">
                    <div
                        class="cpbn-profile-picture-preview"
                        id="profile-picture-preview"
                        data-existing-url="{{ $profilePictureUrl ?? '' }}"
                    >
                        <img
                            id="profile-picture-preview-image"
                            src="{{ $profilePictureUrl ?? '' }}"
                            alt="Profile picture"
                            {{ $profilePictureUrl ? '' : 'hidden' }}
                        >

                        <span
                            id="profile-picture-preview-fallback"
                            {{ $profilePictureUrl ? 'hidden' : '' }}
                        >
                            {{ $profileInitial }}
                        </span>
                    </div>

                    <div class="cpbn-profile-picture-details">
                        <h4>Profile Picture</h4>

                        <p>
                            Upload a JPG, JPEG, PNG or WebP image.
                            Maximum file size: 5 MB.
                        </p>

                        <div class="cpbn-profile-picture-actions">
                            <input
                                type="file"
                                name="profile_picture"
                                id="profile-picture-input"
                                class="cpbn-profile-picture-input"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                data-max-bytes="{{ 5 * 1024 * 1024 }}"
                                data-file-label="Profile pictures"
                            >

                            <label
                                for="profile-picture-input"
                                class="cpbn-profile-picture-button"
                            >
                                <i class="fas fa-camera"></i>
                                Choose Photo
                            </label>

                            @if($profilePictureUrl)
                                <input
                                    type="checkbox"
                                    name="remove_profile_picture"
                                    value="1"
                                    id="remove-profile-picture"
                                    class="cpbn-profile-picture-input"
                                    {{ old('remove_profile_picture') ? 'checked' : '' }}
                                >

                                <label
                                    for="remove-profile-picture"
                                    class="cpbn-profile-picture-button remove"
                                    id="remove-profile-picture-button"
                                >
                                    <i class="fas fa-trash-alt"></i>

                                    <span id="remove-profile-picture-label">
                                        Remove Photo
                                    </span>
                                </label>
                            @endif
                        </div>

                        <div
                            class="cpbn-profile-picture-file-name"
                            id="profile-picture-file-name"
                        ></div>

                        @error('profile_picture')
                            <div class="error">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="cpbn-fgrid">
                        <div class="cpbn-field full">
                        <label>Full Name (as on IC) <span class="req">*</span></label>

                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            maxlength="255"
                            autocomplete="name"
                            placeholder="Nur Aisyah binti Hassan"
                            required
                        >

                        @error('name')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="cpbn-field">
                        <label>Email <span class="req">*</span></label>

                        <input
                            type="email"
                            value="{{ $user->email }}"
                            disabled
                        >
                    </div>

                                        <div class="cpbn-field">
                        <label>Student ID <span class="req">*</span></label>

                        @if($user->hasVerifiedStudentId())
                            <input
                                type="text"
                                value="{{ $user->student_id }}"
                                disabled
                            >

                            <span class="hint">
                                <x-student-id-status :student="$user" :show-id="false" />
                                Locked. Contact an admin or your lecturer if this is wrong.
                            </span>
                        @else
                            <input
                                type="text"
                                name="student_id"
                                value="{{ old('student_id', $user->student_id) }}"
                                maxlength="9"
                                required
                                placeholder="24FTT2803"
                                pattern="[0-9]{2}[A-Za-z]{3}[0-9]{4}"
                                title="2 digits, 3 letters, 4 digits (e.g. 24FTT2803)"
                                style="text-transform:uppercase;"
                            >

                            <span class="hint">
                                @if($user->student_id)
                                    <x-student-id-status :student="$user" :show-id="false" />
                                    An admin or your lecturer will confirm this ID.
                                @else
                                    Your Politeknik Brunei ID, e.g. 24FTT2803.
                                @endif
                            </span>
                        @endif

                        @error('student_id')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="cpbn-field">
                        <label for="phone">Phone Number</label>

                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            value="{{ old('phone', $user->phone ?? '') }}"
                            maxlength="30"
                            autocomplete="tel"
                        >

                        <input
                            type="hidden"
                            id="phone_country"
                            name="phone_country"
                            value="{{ old('phone_country', 'BN') }}"
                        >

                        <span class="hint">
                            Select your country and enter a valid phone number.
                            Brunei (+673) is selected by default.
                        </span>

                        @error('phone')
                            <div class="error">{{ $message }}</div>
                        @enderror

                        @error('phone_country')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="cpbn-field">
                        <label>Date of Birth</label>

                        <input
                            type="date"
                            name="date_of_birth"
                            value="{{ old('date_of_birth', $user->profile->date_of_birth ?? '') }}"
                        >

                        @error('date_of_birth')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="cpbn-field">
                        <label>Nationality</label>

                        <select name="nationality">
                            <option value="">
                                Select your nationality
                            </option>

                            @php
                                $chosenNationality = old(
                                    'nationality',
                                    $user->profile->nationality ?? ''
                                );
                            @endphp

                            {{--
                                Southeast Asia first, because that is
                                where almost every student is from.
                                The rest of the world follows so
                                nobody has to leave this blank.
                            --}}
                            <optgroup label="Southeast Asia">
                                @foreach(config('nationalities.regional') as $nationality)
                                    <option
                                        value="{{ $nationality }}"
                                        @selected($chosenNationality === $nationality)
                                    >{{ $nationality }}</option>
                                @endforeach
                            </optgroup>

                            <optgroup label="Elsewhere">
                                @foreach(config('nationalities.other') as $nationality)
                                    <option
                                        value="{{ $nationality }}"
                                        @selected($chosenNationality === $nationality)
                                    >{{ $nationality }}</option>
                                @endforeach
                            </optgroup>
                        </select>

                        @error('nationality')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Qualifications -->
            <div class="section" id="ep-qualifications">
                <div class="title">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>

                    Qualifications
                </div>

                <p class="desc">
                    Every qualification you hold or are working towards, starting with
                    your Politeknik Brunei programme. Job roles state what they need as a
                    BDQF level, so saying which level an award sits on is what lets the
                    explorer tell you how you compare.
                </p>

                {{--
                    The ladder itself, for a student who has never
                    heard of it.

                    Entry requirements are written in these levels
                    and nowhere else explained them, so "BDQF Level
                    4 in IT or related field" was a sentence a
                    student had to take on trust — and the picker
                    below asks them to choose one. Shut by default:
                    anybody who knows their level does not need it
                    open.

                    Annex 1 of the framework, in the Annex's own
                    three columns.
                --}}
                @if(($bdqfLevels ?? collect())->isNotEmpty())
                    <details class="bdqf-help">
                        <summary>
                            What are BDQF levels?
                        </summary>

                        <p class="bdqf-help-intro">
                            The Brunei Darussalam Qualifications Framework puts every
                            award on one of eight levels, so qualifications from
                            schools, vocational training and universities can be
                            compared. Find your award below and use its level.
                        </p>

                        <div class="bdqf-ladder">
                            @foreach($bdqfLevels as $level)
                                <div class="bdqf-rung">
                                    <span class="bdqf-rung-level">
                                        Level {{ $level->level }}
                                    </span>

                                    <div class="bdqf-rung-body">
                                        @foreach([
                                            'Schools' => $level->schools_sector,
                                            'Technical and vocational' => $level->tvet_sector,
                                            'Higher education' => $level->higher_education_sector,
                                        ] as $sectorName => $awards)
                                            @continue(empty($awards))

                                            <div class="bdqf-sector">
                                                <span class="bdqf-sector-name">{{ $sectorName }}</span>
                                                <span>{{ implode(', ', $awards) }}</span>
                                            </div>
                                        @endforeach

                                        @if(empty($level->schools_sector)
                                            && empty($level->tvet_sector)
                                            && empty($level->higher_education_sector))
                                            <div class="bdqf-sector">
                                                <span>The framework lists no awards at this level.</span>
                                            </div>
                                        @endif

                                        @if($level->notes)
                                            <p class="bdqf-note">{{ $level->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endif

                @php
                    $qualificationRows = old('qualifications');

                    if ($qualificationRows === null) {
                        $qualificationRows = $user->academicRecords
                            ->map(fn ($record) => [
                                'id' => $record->id,
                                'institution_name' => $record->institution_name,
                                'programme_name' => $record->programme_name,
                                'level' => $record->level,
                                'bdqf_level' => $record->bdqf_level,
                                'field_of_study' => $record->field_of_study,
                                'start_date' => $record->start_date?->format('Y-m-d'),
                                'end_date' => $record->end_date?->format('Y-m-d'),
                                'is_current' => $record->is_current,
                                'is_programme' => $record->is_programme_record,
                            ])
                            ->values()
                            ->all();
                    }

                    /*
                     * The student's Politeknik programme is one of
                     * their qualifications, shown first and not
                     * removable. It used to be a section of its
                     * own asking for "current studies", which
                     * says nothing to a post-graduate who has
                     * none.
                     *
                     * Found by its flag rather than by looking
                     * for the institution's name, so a student
                     * holding two Politeknik awards — or who has
                     * finished the one they were on — keeps the
                     * same row as their programme.
                     */
                    $programmeRowKey = null;

                    foreach ($qualificationRows as $rowKey => $row) {
                        if (! empty($row['is_programme'])) {
                            $programmeRowKey = $rowKey;

                            break;
                        }
                    }

                    $programmeRow = $programmeRowKey === null
                        ? [
                            'id' => null,
                            'institution_name' => \App\Http\Controllers\Student\ProfileController::PROGRAMME_INSTITUTION,
                            'programme_name' => $user->programme,
                            'level' => \App\Http\Controllers\Student\ProfileController::PROGRAMME_AWARD,
                            'bdqf_level' => \App\Http\Controllers\Student\ProfileController::PROGRAMME_BDQF_LEVEL,
                            'field_of_study' => null,
                            'start_date' => null,
                            'end_date' => null,
                            'is_current' => true,
                            'is_programme' => true,
                        ]
                        : $qualificationRows[$programmeRowKey];

                    $otherQualificationRows = $programmeRowKey === null
                        ? $qualificationRows
                        : array_diff_key($qualificationRows, [$programmeRowKey => true]);

                    /*
                     * The programme row keeps whichever index it
                     * arrived with so a failed save puts the
                     * student's own entries back where they were.
                     */
                    $programmeRowIndex = $programmeRowKey ?? 'programme';

                    $usedIndexes = array_merge(
                        array_keys($qualificationRows),
                        [$programmeRowIndex]
                    );

                    $nextQualificationIndex = empty($usedIndexes)
                        ? 0
                        : max(array_map('intval', $usedIndexes)) + 1;

                    $bdqfOptions = ($bdqfLevels ?? collect())
                        ->map(fn ($level) => [
                            'level' => $level->level,
                            'label' => 'Level '.$level->level
                                .' — '.implode(', ', array_slice($level->qualifications(), 0, 3)),
                        ])
                        ->all();
                @endphp

                <div id="removed-qualification-fields">
                    @foreach(old('removed_qualification_ids', []) as $removedQualificationId)
                        <input
                            type="hidden"
                            name="removed_qualification_ids[]"
                            value="{{ $removedQualificationId }}"
                        >
                    @endforeach
                </div>

                <div
                    id="qualification-list"
                    data-next-index="{{ $nextQualificationIndex }}"
                    data-bdqf-options="{{ json_encode($bdqfOptions) }}"
                >
                    {{--
                        The Politeknik programme. Pre-filled as far
                        as it can be and not removable: every
                        student on this system is on one, which is
                        what makes the institution, the award and
                        the BDQF level answerable without asking.

                        The programme select is the same field it
                        always was — it still writes
                        users.programme, which the rest of the
                        application reads — so this row mirrors it
                        rather than holding a second copy.
                    --}}
                    <div class="cpbn-certification-card is-programme-row">
                        @if(! empty($programmeRow['id']))
                            <input
                                type="hidden"
                                name="qualifications[{{ $programmeRowIndex }}][id]"
                                value="{{ $programmeRow['id'] }}"
                            >
                        @endif

                        <input
                            type="hidden"
                            name="qualifications[{{ $programmeRowIndex }}][is_programme]"
                            value="1"
                        >

                        <p class="cpbn-row-badge">Your programme</p>

                        <div class="cpbn-fgrid">
                            <div class="cpbn-field">
                                <label>Institution</label>
                                <p class="cpbn-fixed-value">
                                    {{ \App\Http\Controllers\Student\ProfileController::PROGRAMME_INSTITUTION }}
                                </p>
                            </div>

                            <div class="cpbn-field">
                                <label>Programme <span class="req">*</span></label>

                                <x-programme-select
                                    :selected="$user->programme"
                                    :selected-group-id="$user->programme_group_id"
                                />

                                @error('programme')
                                    <div class="error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="cpbn-field">
                                <label>Award <span class="req">*</span></label>
                                <input
                                    type="text"
                                    name="qualifications[{{ $programmeRowIndex }}][level]"
                                    value="{{ $programmeRow['level'] ?? '' }}"
                                    maxlength="100"
                                    required
                                >
                            </div>

                            <div class="cpbn-field">
                                <label>BDQF Level</label>
                                <select name="qualifications[{{ $programmeRowIndex }}][bdqf_level]">
                                    <option value="">Not sure</option>
                                    @foreach($bdqfOptions as $option)
                                        <option
                                            value="{{ $option['level'] }}"
                                            @selected((string) ($programmeRow['bdqf_level'] ?? '') === (string) $option['level'])
                                        >{{ $option['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="cpbn-field">
                                <label>Latest CGPA (out of 4.00)</label>
                                <input
                                    type="number"
                                    name="cgpa"
                                    step="0.01"
                                    min="0"
                                    max="4"
                                    placeholder="e.g. 3.25"
                                    value="{{ old('cgpa', $user->cgpa) }}"
                                >

                                @error('cgpa')
                                    <div class="error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="cpbn-field">
                                <label>Field of Study</label>
                                <input
                                    type="text"
                                    name="qualifications[{{ $programmeRowIndex }}][field_of_study]"
                                    value="{{ $programmeRow['field_of_study'] ?? '' }}"
                                    maxlength="150"
                                    placeholder="Information Technology"
                                >
                            </div>

                            <div class="cpbn-field">
                                <label>Started</label>
                                <input
                                    type="date"
                                    name="qualifications[{{ $programmeRowIndex }}][start_date]"
                                    value="{{ $programmeRow['start_date'] ?? '' }}"
                                    max="{{ now()->format('Y-m-d') }}"
                                >
                            </div>

                            <div class="cpbn-field" data-finished-field>
                                <label>Finished</label>
                                <input
                                    type="date"
                                    name="qualifications[{{ $programmeRowIndex }}][end_date]"
                                    value="{{ $programmeRow['end_date'] ?? '' }}"
                                >
                            </div>

                            <div class="cpbn-field">
                                <label class="cpbn-check">
                                    <input
                                        type="hidden"
                                        name="qualifications[{{ $programmeRowIndex }}][is_current]"
                                        value="0"
                                    >
                                    <input
                                        type="checkbox"
                                        name="qualifications[{{ $programmeRowIndex }}][is_current]"
                                        value="1"
                                        data-still-studying
                                        @checked($programmeRow['is_current'] ?? true)
                                    >
                                    <span>Still studying this</span>
                                    <span class="cpbn-check-state"></span>
                                </label>
                                <small class="cpbn-file-note">
                                    Untick it once you have graduated — the award then counts
                                    towards what you hold rather than what you will.
                                </small>
                            </div>
                        </div>
                    </div>

                    @foreach($otherQualificationRows as $index => $qualification)
                        <div class="cpbn-certification-card">
                            @if(! empty($qualification['id']))
                                <input
                                    type="hidden"
                                    name="qualifications[{{ $index }}][id]"
                                    value="{{ $qualification['id'] }}"
                                >
                            @endif

                            <div class="cpbn-fgrid">
                                <div class="cpbn-field">
                                    <label>Institution <span class="req">*</span></label>
                                    <input
                                        type="text"
                                        name="qualifications[{{ $index }}][institution_name]"
                                        value="{{ $qualification['institution_name'] ?? '' }}"
                                        maxlength="150"
                                        placeholder="Politeknik Brunei"
                                        required
                                    >
                                </div>

                                <div class="cpbn-field">
                                    <label>Programme <span class="req">*</span></label>
                                    <input
                                        type="text"
                                        name="qualifications[{{ $index }}][programme_name]"
                                        value="{{ $qualification['programme_name'] ?? '' }}"
                                        maxlength="150"
                                        placeholder="Diploma in Information Technology"
                                        required
                                    >
                                </div>

                                <div class="cpbn-field">
                                    <label>Award <span class="req">*</span></label>
                                    <input
                                        type="text"
                                        name="qualifications[{{ $index }}][level]"
                                        value="{{ $qualification['level'] ?? '' }}"
                                        maxlength="100"
                                        placeholder="Diploma"
                                        required
                                    >
                                </div>

                                <div class="cpbn-field">
                                    <label>BDQF Level</label>
                                    <select name="qualifications[{{ $index }}][bdqf_level]">
                                        <option value="">Not sure</option>
                                        @foreach($bdqfOptions as $option)
                                            <option
                                                value="{{ $option['level'] }}"
                                                @selected((string) ($qualification['bdqf_level'] ?? '') === (string) $option['level'])
                                            >{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                    <small class="cpbn-file-note">
                                        Leave as "Not sure" rather than guessing — the explorer
                                        will say it cannot tell instead of comparing you wrongly.
                                    </small>
                                </div>

                                <div class="cpbn-field">
                                    <label>Field of Study</label>
                                    <input
                                        type="text"
                                        name="qualifications[{{ $index }}][field_of_study]"
                                        value="{{ $qualification['field_of_study'] ?? '' }}"
                                        maxlength="150"
                                        placeholder="Information Technology"
                                    >
                                </div>

                                <div class="cpbn-field">
                                    <label>Started <span class="req">*</span></label>
                                    <input
                                        type="date"
                                        name="qualifications[{{ $index }}][start_date]"
                                        value="{{ $qualification['start_date'] ?? '' }}"
                                        max="{{ now()->format('Y-m-d') }}"
                                        required
                                    >
                                </div>

                                {{--
                                    Put away while the programme
                                    is still being studied: a
                                    date it finished is a
                                    contradiction, and an empty
                                    box beside a ticked "still
                                    studying" invites one anyway.
                                --}}
                                <div class="cpbn-field" data-finished-field>
                                    <label>Finished</label>
                                    <input
                                        type="date"
                                        name="qualifications[{{ $index }}][end_date]"
                                        value="{{ $qualification['end_date'] ?? '' }}"
                                    >
                                </div>

                                <div class="cpbn-field">
                                    <label class="cpbn-check">
                                        <input
                                            type="hidden"
                                            name="qualifications[{{ $index }}][is_current]"
                                            value="0"
                                        >
                                        <input
                                            type="checkbox"
                                            name="qualifications[{{ $index }}][is_current]"
                                            value="1"
                                            data-still-studying
                                            @checked($qualification['is_current'] ?? false)
                                        >
                                        <span>Still studying this</span>
                                        <span class="cpbn-check-state"></span>
                                    </label>
                                    <small class="cpbn-file-note">
                                        A programme you are still on counts towards what you
                                        will hold, not what you hold now.
                                    </small>
                                </div>
                            </div>

                            <button type="button" class="cpbn-remove-qualification">
                                Remove
                            </button>
                        </div>
                    @endforeach
                </div>

                <button type="button" id="add-qualification" class="cpbn-add-btn">
                    + Add qualification
                </button>
            </div>

            <!-- SECTION 3: Skills -->
            <div class="section" id="ep-skills">
                <div class="title">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"
                        />
                    </svg>

                    Skills &amp; Competencies
                </div>

                <p class="desc">
                    Select your current BIICF competencies and the proficiency
                    level that best represents your ability.
                </p>

                <input
                    type="hidden"
                    name="biicf_mode"
                    value="1"
                >

                @php
                    $biicfCompetencies =
                        $biicfCompetencies ?? collect();

                    $proficiencyLevels =
                        $proficiencyLevels ?? collect();

                    /*
                     * Options for skills a student types in
                     * themselves, which carry no BIICF type and
                     * so no scale of their own. The framework's
                     * soft skills are a closed list of eleven
                     * named ones, so anything written in here is
                     * treated as technical.
                     */
                    $proficiencyLevelOptions =
                        $proficiencyLevels
                            ->where(
                                'scale',
                                \App\Models\BiicfProficiencyLevel::SCALE_TECHNICAL
                            )
                            ->map(
                                fn ($level) => [
                                    'id' => $level->id,
                                    'level_number' =>
                                        $level->level_number,
                                    'name' => $level->name,
                                ]
                            )
                            ->values()
                            ->all();

                    $officialSkillNames = $biicfCompetencies
                        ->pluck('name')
                        ->map(
                            fn ($name) =>
                                mb_strtolower(
                                    trim((string) $name)
                                )
                        )
                        ->all();

                    $storedSkillsByName = $user
                        ->competencies
                        ->keyBy(
                            fn ($skill) =>
                                mb_strtolower(
                                    trim(
                                        (string) $skill->skill_name
                                    )
                                )
                        );

                    $levelsByName = $proficiencyLevels
                        ->keyBy(
                            fn ($level) =>
                                mb_strtolower(
                                    trim((string) $level->name)
                                )
                        );

                    /*
                    * Existing student records may still use the
                    * old proficiency scale.
                    */
                    $legacyLevelNames = [
                        'beginner' => 'follow',
                        'intermediate' => 'assist',
                        'advanced' => 'apply',
                        'expert' => 'ensure',
                    ];

                    $oldBiicfSelections =
                        old('biicf_competencies');

                    $technicalCompetencies =
                        $biicfCompetencies
                            ->where(
                                'type',
                                'technical'
                            );

                    $softCompetencies =
                        $biicfCompetencies
                            ->where(
                                'type',
                                'soft_skill'
                            );

                    /*
                    * Anything that does not match an official BIICF
                    * competency remains an additional skill.
                    */
                    $storedAdditionalSkills = $user
                        ->competencies
                        ->filter(
                            fn ($skill) =>
                                ! in_array(
                                    mb_strtolower(
                                        trim(
                                            (string)
                                            $skill->skill_name
                                        )
                                    ),
                                    $officialSkillNames,
                                    true
                                )
                        )
                        ->values();

                    $oldCustomSkills =
                        old('custom_skills');

                    $oldCustomLevels =
                        old(
                            'custom_skill_levels',
                            []
                        );

                    if (is_array($oldCustomSkills)) {
                        $savedAdditionalSkills = [];

                        foreach (
                            $oldCustomSkills
                            as $index => $skill
                        ) {
                            if (
                                trim(
                                    (string) $skill
                                ) === ''
                            ) {
                                continue;
                            }

                            $savedAdditionalSkills[] = [
                                'name' => $skill,
                                'level_id' =>
                                    $oldCustomLevels[
                                        $index
                                    ] ?? null,
                            ];
                        }
                    } else {
                        $savedAdditionalSkills =
                            $storedAdditionalSkills
                                ->map(
                                    function ($skill)
                                    use (
                                        $levelsByName,
                                        $legacyLevelNames
                                    ) {
                                        $storedLevel =
                                            mb_strtolower(
                                                trim(
                                                    (string)
                                                    $skill
                                                        ->proficiency_level
                                                )
                                            );

                                        $levelName =
                                            $legacyLevelNames[
                                                $storedLevel
                                            ] ?? $storedLevel;

                                        $level =
                                            $levelsByName
                                                ->get(
                                                    $levelName
                                                );

                                        return [
                                            'name' =>
                                                $skill
                                                    ->skill_name,

                                            'level_id' =>
                                                $level?->id,
                                        ];
                                    }
                                )
                                ->values()
                                ->all();
                    }

                    $hasAdditionalSkills =
                        ! empty(
                            $savedAdditionalSkills
                        );
                @endphp

                <div class="cpbn-competency-group">
                    <div class="cpbn-competency-heading">
                        <div>
                            <strong>
                                Technical Competencies
                            </strong>

                            <span>
                                {{ $technicalCompetencies->count() }}
                            </span>
                        </div>

                        <small>
                            BIICF technical competencies
                        </small>
                    </div>

                    {{--
                        Three rows of chips is a lot of furniture
                        above a list, and plenty of people never
                        want any of it. Shut, the whole block is
                        one line that says what is filtered.

                        Open to begin with, because a filter
                        nobody finds is no better than one that is
                        not there — the point is being able to put
                        it away, not to hide it.
                    --}}
                    <div class="cpbn-filter-head">
                        <button
                            type="button"
                            class="cpbn-filter-toggle"
                            id="technical-filter-toggle"
                            aria-expanded="true"
                            aria-controls="technical-filters"
                        >
                            <span class="cpbn-filter-toggle-caret" aria-hidden="true">&#9662;</span>
                            <span id="technical-filter-toggle-label">Hide filters</span>
                        </button>

                        <span class="cpbn-filter-active" id="technical-filter-active"></span>
                    </div>

                    <div class="cpbn-filters" id="technical-filters">

                        {{--
                            Narrowing a hundred and twenty-seven of
                            them to the forty or so a sub-sector asks
                            for. A competency is wanted across several
                            sub-sectors, so these overlap on purpose.

                            Anything already ticked stays on screen
                            whichever filter is on. Hiding a choice a
                            student has made looks exactly like losing
                            it.
                        --}}
                        @if(! empty($subSectorOptions))
                            <div class="cpbn-filter-row">
                                <span class="cpbn-filter-label">Sub-sector</span>

                                <div class="cpbn-sector-filter" id="technical-sector-filter">
                                    <button type="button" class="cpbn-sector-chip is-on" data-sector="">
                                        All
                                    </button>

                                    @foreach($subSectorOptions as $sector)
                                        <button type="button" class="cpbn-sector-chip"
                                                data-sector="{{ $sector }}">
                                            {{ $sector }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{--
                            And the level crossed with it. A sub-sector
                            alone still leaves forty to sixty, which is
                            a list to read rather than a choice to
                            make; with a level it is the handful a
                            student entering that sector is expected
                            to have.

                            Only the levels something is filed under
                            are offered. The two narrow together, and
                            a competency the framework files under no
                            level at all stays on screen whichever is
                            picked — it can be reached from nowhere
                            else.
                        --}}
                        @if(! empty($jobLevelOptions))
                            <div class="cpbn-filter-row">
                                <span class="cpbn-filter-label">Job level</span>

                                <div class="cpbn-sector-filter" id="technical-level-filter">
                                    <button type="button" class="cpbn-sector-chip is-on" data-level="">
                                        All
                                    </button>

                                    @foreach($jobLevelOptions as $level)
                                        <button type="button" class="cpbn-sector-chip"
                                                data-level="{{ $level }}">
                                            {{ $level }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{--
                            And the job itself, which is the one that
                            actually cuts it down. A sub-sector at a
                            level runs to thirty-nine in the
                            combinations students pick most; one role
                            is eight to seventeen, always.

                            The rows above narrow which roles are
                            offered rather than being made redundant
                            by this one, so a student can still arrive
                            at a role by sector and seniority. A role
                            left showing after its sector or level
                            stops offering it would be a filter nobody
                            could see, so it is cleared when that
                            happens.
                        --}}
                        @if(! empty($jobRoleOptions))
                            <div class="cpbn-filter-row">
                                <span class="cpbn-filter-label">Job role</span>

                                <div class="cpbn-sector-filter cpbn-role-filter" id="technical-role-filter">
                                    <button type="button" class="cpbn-sector-chip is-on" data-role="">
                                        All
                                    </button>

                                    @foreach($jobRoleOptions as $role)
                                        <button type="button" class="cpbn-sector-chip"
                                                data-role="{{ $role['id'] }}"
                                                data-role-sector="{{ $role['sector'] }}"
                                                data-role-levels="{{ implode('|', $role['levels']) }}">
                                            {{ $role['title'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <p class="cpbn-filter-empty" id="technical-filter-empty" hidden>
                            Nothing is asked for by that combination.
                            Try another, or
                            <button type="button" class="cpbn-filter-reset" data-filter-reset>
                                clear the filters</button>.
                        </p>

                    </div>

                    {{--
                        Why the list can hold more than the filter
                        asked for. A competency already ticked
                        stays on screen whatever is selected, and
                        with every page open at once that reads as
                        the filter having failed. So it is said
                        plainly, and those rows are marked.
                    --}}
                    <p class="cpbn-filter-summary" id="technical-filter-summary" hidden></p>

                    <div
                        class="cpbn-competency-grid"
                        id="technical-competency-grid"
                    >
                        @foreach(
                            $technicalCompetencies
                            as $competency
                        )
                            @php
                                $storedSkill =
                                    $storedSkillsByName
                                        ->get(
                                            mb_strtolower(
                                                trim(
                                                    $competency
                                                        ->name
                                                )
                                            )
                                        );

                                if (
                                    is_array(
                                        $oldBiicfSelections
                                    )
                                ) {
                                    $oldSelection =
                                        $oldBiicfSelections[
                                            $competency->id
                                        ] ?? [];

                                    $isSelected =
                                        ! empty(
                                            $oldSelection[
                                                'selected'
                                            ] ?? null
                                        );

                                    $selectedLevelId =
                                        $oldSelection[
                                            'proficiency_level_id'
                                        ] ?? null;
                                } else {
                                    $isSelected =
                                        (bool) $storedSkill;

                                    $storedLevel =
                                        $storedSkill
                                            ? mb_strtolower(
                                                trim(
                                                    (string)
                                                    $storedSkill
                                                        ->proficiency_level
                                                )
                                            )
                                            : null;

                                    $levelName =
                                        $storedLevel
                                            ? (
                                                $legacyLevelNames[
                                                    $storedLevel
                                                ] ?? $storedLevel
                                            )
                                            : null;

                                    $selectedLevelId =
                                        $levelName
                                            ? $levelsByName
                                                ->get(
                                                    $levelName
                                                )?->id
                                            : null;
                                }
                            @endphp

                            <div
                                class="cpbn-competency-item {{ $isSelected ? 'selected' : '' }}"
                                data-biicf-competency
                                data-technical-competency
                                data-sectors="{{ implode('|', $competencySubSectors[$competency->id] ?? []) }}"
                                data-levels="{{ implode('|', $competencyJobLevels[$competency->id] ?? []) }}"
                                data-roles="{{ implode('|', $competencyJobRoles[$competency->id] ?? []) }}"
                            >
                                <label class="cpbn-competency-choice">
                                    <input
                                        type="checkbox"
                                        class="biicf-competency-checkbox"
                                        name="biicf_competencies[{{ $competency->id }}][selected]"
                                        value="1"
                                        {{ $isSelected ? 'checked' : '' }}
                                    >

                                    <span>
                                        {{ $competency->name }}
                                    </span>
                                </label>

                                <select
                                    class="biicf-proficiency-select"
                                    name="biicf_competencies[{{ $competency->id }}][proficiency_level_id]"
                                    {{ $isSelected ? 'required' : 'disabled' }}
                                >
                                    <option value="">
                                        Choose level
                                    </option>

                                    @foreach(
                                        $proficiencyLevels->where('scale', $competency->scale())
                                            as $level
                                    )
                                        <option
                                            value="{{ $level->id }}"
                                            {{
                                                (string)
                                                $selectedLevelId
                                                ===
                                                (string)
                                                $level->id
                                                    ? 'selected'
                                                    : ''
                                            }}
                                        >
                                            Level
                                            {{ $level->level_number }}
                                            —
                                            {{ $level->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error(
                                    "biicf_competencies.{$competency->id}.proficiency_level_id"
                                )
                                    <div class="cpbn-error">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    <div
                        class="cpbn-competency-controls"
                        id="technical-competency-controls"
                    >
                        <button
                            type="button"
                            class="cpbn-competency-page-button"
                            id="technical-competency-previous"
                        >
                            Previous
                        </button>

                        <span
                            class="cpbn-competency-page-status"
                            id="technical-competency-page-status"
                        >
                            Page 1
                        </span>

                        <button
                            type="button"
                            class="cpbn-competency-page-button"
                            id="technical-competency-next"
                        >
                            Next
                        </button>

                        <button
                            type="button"
                            class="cpbn-competency-toggle"
                            id="technical-competency-toggle"
                            aria-expanded="false"
                        >
                            Show All ({{ $technicalCompetencies->count() }})
                        </button>
                    </div>
                </div>

                <div class="cpbn-competency-group">
                    <div class="cpbn-competency-heading">
                        <div>
                            <strong>
                                Soft Skill Competencies
                            </strong>

                            <span>
                                {{ $softCompetencies->count() }}
                            </span>
                        </div>

                        <small>
                            BIICF behavioural and interpersonal competencies
                        </small>
                    </div>

                    <div
                        class="cpbn-competency-grid"
                        id="soft-competency-grid"
                    >
                        @foreach(
                            $softCompetencies
                            as $competency
                        )
                            @php
                                $storedSkill =
                                    $storedSkillsByName
                                        ->get(
                                            mb_strtolower(
                                                trim(
                                                    $competency
                                                        ->name
                                                )
                                            )
                                        );

                                if (
                                    is_array(
                                        $oldBiicfSelections
                                    )
                                ) {
                                    $oldSelection =
                                        $oldBiicfSelections[
                                            $competency->id
                                        ] ?? [];

                                    $isSelected =
                                        ! empty(
                                            $oldSelection[
                                                'selected'
                                            ] ?? null
                                        );

                                    $selectedLevelId =
                                        $oldSelection[
                                            'proficiency_level_id'
                                        ] ?? null;
                                } else {
                                    $isSelected =
                                        (bool) $storedSkill;

                                    $storedLevel =
                                        $storedSkill
                                            ? mb_strtolower(
                                                trim(
                                                    (string)
                                                    $storedSkill
                                                        ->proficiency_level
                                                )
                                            )
                                            : null;

                                    /*
                                     * Soft skills are saved under the
                                     * soft scale's own names (Basic,
                                     * Intermediate, Advanced, Expert).
                                     * The legacy map below only exists
                                     * for the old technical scale, so
                                     * it must not touch these names or
                                     * they stop matching their options.
                                     */
                                    $selectedLevelId =
                                        $storedLevel
                                            ? $proficiencyLevels
                                                ->where(
                                                    'scale',
                                                    $competency->scale()
                                                )
                                                ->first(
                                                    fn ($level) =>
                                                        mb_strtolower(
                                                            trim(
                                                                (string)
                                                                $level->name
                                                            )
                                                        ) === $storedLevel
                                                )?->id
                                            : null;
                                }
                            @endphp

                            <div
                                class="cpbn-competency-item {{ $isSelected ? 'selected' : '' }}"
                                data-biicf-competency
                                data-soft-competency
                            >
                                <label class="cpbn-competency-choice">
                                    <input
                                        type="checkbox"
                                        class="biicf-competency-checkbox"
                                        name="biicf_competencies[{{ $competency->id }}][selected]"
                                        value="1"
                                        {{ $isSelected ? 'checked' : '' }}
                                    >

                                    <span>
                                        {{ $competency->name }}
                                    </span>
                                </label>

                                <select
                                    class="biicf-proficiency-select"
                                    name="biicf_competencies[{{ $competency->id }}][proficiency_level_id]"
                                    {{ $isSelected ? 'required' : 'disabled' }}
                                >
                                    <option value="">
                                        Choose level
                                    </option>

                                    @foreach(
                                        $proficiencyLevels->where('scale', $competency->scale())
                                            as $level
                                    )
                                        <option
                                            value="{{ $level->id }}"
                                            {{
                                                (string)
                                                $selectedLevelId
                                                ===
                                                (string)
                                                $level->id
                                                    ? 'selected'
                                                    : ''
                                            }}
                                        >
                                            Level
                                            {{ $level->level_number }}
                                            —
                                            {{ $level->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error(
                                    "biicf_competencies.{$competency->id}.proficiency_level_id"
                                )
                                    <div class="cpbn-error">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    <div
                        class="cpbn-competency-controls"
                        id="soft-competency-controls"
                    >
                        <button
                            type="button"
                            class="cpbn-competency-page-button"
                            id="soft-competency-previous"
                        >
                            Previous
                        </button>

                        <span
                            class="cpbn-competency-page-status"
                            id="soft-competency-page-status"
                        >
                            Page 1
                        </span>

                        <button
                            type="button"
                            class="cpbn-competency-page-button"
                            id="soft-competency-next"
                        >
                            Next
                        </button>

                        <button
                            type="button"
                            class="cpbn-competency-toggle"
                            id="soft-competency-toggle"
                            aria-expanded="false"
                        >
                            Show All ({{ $softCompetencies->count() }})
                        </button>
                    </div>
                </div>

                <p class="cpbn-check-note">
                    BIICF proficiency levels:
                    Follow, Assist, Apply, Ensure and Strategise.
                </p>

                <div class="cpbn-additional-toggle">
                    <label class="cpbn-check">
                        <input
                            type="checkbox"
                            id="additional-skills-toggle"
                            {{ $hasAdditionalSkills ? 'checked' : '' }}
                        >

                        <span>
                            Add skills outside the BIICF list
                        </span>
                    </label>
                </div>

                <div
                    id="additional-skills-section"
                    class="cpbn-additional"
                    {{ $hasAdditionalSkills ? '' : 'hidden' }}
                >
                    <div class="cpbn-additional-heading">
                        Additional Skills
                    </div>

                    <p class="cpbn-check-note">
                        Use this for technologies, tools or other
                        skills that are not listed as BIICF competencies.
                    </p>

                    <div
                        id="additional-skill-chips"
                        class="cpbn-custom-chips"
                    >
                        @foreach(
                            $savedAdditionalSkills
                            as $skill
                        )
                            <span
                                class="cpbn-custom-chip"
                                data-value="{{ $skill['name'] }}"
                            >
                                <span>
                                    {{ $skill['name'] }}
                                </span>

                                <select
                                    name="custom_skill_levels[]"
                                    class="cpbn-custom-level-select"
                                    required
                                >
                                    <option value="">
                                        Choose level
                                    </option>

                                    @foreach(
                                        $proficiencyLevels->where('scale', \App\Models\BiicfProficiencyLevel::SCALE_TECHNICAL)
                                            as $level
                                    )
                                        <option
                                            value="{{ $level->id }}"
                                            {{
                                                (string)
                                                ($skill[
                                                    'level_id'
                                                ] ?? '')
                                                ===
                                                (string)
                                                $level->id
                                                    ? 'selected'
                                                    : ''
                                            }}
                                        >
                                            Level
                                            {{ $level->level_number }}
                                            —
                                            {{ $level->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <button
                                    type="button"
                                    class="cpbn-remove-custom-chip"
                                    aria-label="Remove {{ $skill['name'] }}"
                                    title="Remove"
                                >
                                    &times;
                                </button>

                                <input
                                    type="hidden"
                                    name="custom_skills[]"
                                    value="{{ $skill['name'] }}"
                                >
                            </span>
                        @endforeach
                    </div>

                    <div class="cpbn-custom-entry">
                        <input
                            type="text"
                            id="additional-skill-input"
                            maxlength="40"
                            autocomplete="off"
                            placeholder="Type another skill..."
                            aria-describedby="additional-skill-example additional-skill-feedback"
                        >

                        <button
                            type="button"
                            id="add-additional-skill"
                            class="cpbn-custom-add"
                        >
                            Add
                        </button>
                    </div>

                    <p
                        id="additional-skill-example"
                        class="cpbn-custom-example"
                    >
                        Examples: Laravel, Flutter, Power BI
                    </p>

                    <div
                        id="additional-skill-feedback"
                        class="cpbn-custom-feedback"
                        hidden
                    >
                        <span
                            id="additional-skill-feedback-text"
                        ></span>

                        <div
                            id="additional-skill-warning-actions"
                            class="cpbn-custom-warning-actions"
                            hidden
                        >
                            <button
                                type="button"
                                id="edit-additional-skill"
                                class="cpbn-custom-edit"
                            >
                                Edit
                            </button>

                            <button
                                type="button"
                                id="add-additional-skill-anyway"
                                class="cpbn-custom-add-anyway"
                            >
                                Add Anyway
                            </button>
                        </div>
                    </div>

                    @error('custom_skills')
                        <div class="cpbn-error">
                            {{ $message }}
                        </div>
                    @enderror

                    @error('custom_skills.*')
                        <div class="cpbn-error">
                            {{ $message }}
                        </div>
                    @enderror

                    @error('custom_skill_levels.*')
                        <div class="cpbn-error">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                @error('biicf_competencies')
                    <div class="cpbn-error">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- SECTION 4: Interests -->
            <div class="section" id="ep-interests">
                <div class="title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.8 1-1a5.5 5.5 0 0 0 0-7.6z"/>
                    </svg>
                    Interests &amp; Preferences
                </div>

                <p class="desc">What are you interested in?</p>

                @php
                    $interestOptions = $interestOptions ?? [];

                    $storedInterestNames = $user
                        ->interests
                        ->pluck('interest_name')
                        ->toArray();

                    $storedPredefinedInterests = array_values(
                        array_filter(
                            $storedInterestNames,
                            fn ($interest) => in_array(
                                $interest,
                                $interestOptions,
                                true
                            )
                        )
                    );

                    $storedAdditionalInterests = array_values(
                        array_filter(
                            $storedInterestNames,
                            fn ($interest) => ! in_array(
                                $interest,
                                $interestOptions,
                                true
                            )
                        )
                    );

                    $savedInterests = old(
                        'interests',
                        $storedPredefinedInterests
                    );

                    $savedAdditionalInterests = old(
                        'custom_interests',
                        $storedAdditionalInterests
                    );

                    $savedAdditionalInterests = is_array($savedAdditionalInterests)
                        ? array_values(
                            array_filter(
                                $savedAdditionalInterests,
                                fn ($interest) => trim((string) $interest) !== ''
                            )
                        )
                        : [];

                    $hasAdditionalInterests = ! empty(
                        $savedAdditionalInterests
                    );
                @endphp

                <div class="cpbn-checks">
                    @foreach($interestOptions as $interest)
                        <label class="cpbn-check">
                            <input
                                type="checkbox"
                                name="interests[]"
                                value="{{ $interest }}"
                                {{ in_array($interest, $savedInterests ?? [], true) ? 'checked' : '' }}
                            >
                            <span>{{ $interest }}</span>
                        </label>
                    @endforeach

                    <label class="cpbn-check">
                        <input
                            type="checkbox"
                            id="additional-interests-toggle"
                            {{ $hasAdditionalInterests ? 'checked' : '' }}
                        >
                        <span>Others</span>
                    </label>
                </div>

                <div
                    id="additional-interests-section"
                    class="cpbn-additional"
                    {{ $hasAdditionalInterests ? '' : 'hidden' }}
                >
                    <div class="cpbn-additional-heading">
                        Additional Interests
                    </div>

                    <div
                        id="additional-interest-chips"
                        class="cpbn-custom-chips"
                    >
                        @foreach($savedAdditionalInterests as $additionalInterest)
                            <span
                                class="cpbn-custom-chip"
                                data-value="{{ $additionalInterest }}"
                            >
                                <span>{{ $additionalInterest }}</span>

                                <button
                                    type="button"
                                    class="cpbn-remove-custom-chip"
                                    aria-label="Remove {{ $additionalInterest }}"
                                    title="Remove"
                                >
                                    &times;
                                </button>

                                <input
                                    type="hidden"
                                    name="custom_interests[]"
                                    value="{{ $additionalInterest }}"
                                >
                            </span>
                        @endforeach
                    </div>

                    <div class="cpbn-custom-entry">
                        <input
                            type="text"
                            id="additional-interest-input"
                            maxlength="40"
                            autocomplete="off"
                            placeholder="Type another interest..."
                            aria-describedby="additional-interest-example additional-interest-feedback"
                        >

                        <button
                            type="button"
                            id="add-additional-interest"
                            class="cpbn-custom-add"
                        >
                            Add
                        </button>
                    </div>

                    <p
                        id="additional-interest-example"
                        class="cpbn-custom-example"
                    >
                        Examples: Artificial Intelligence, UI/UX, Game Development
                    </p>

                    <div
                        id="additional-interest-feedback"
                        class="cpbn-custom-feedback"
                        hidden
                    >
                        <span id="additional-interest-feedback-text"></span>

                        <div
                            id="additional-interest-warning-actions"
                            class="cpbn-custom-warning-actions"
                            hidden
                        >
                            <button
                                type="button"
                                id="edit-additional-interest"
                                class="cpbn-custom-edit"
                            >
                                Edit
                            </button>

                            <button
                                type="button"
                                id="add-additional-interest-anyway"
                                class="cpbn-custom-add-anyway"
                            >
                                Add Anyway
                            </button>
                        </div>
                    </div>

                    @error('custom_interests')
                        <div class="cpbn-error">{{ $message }}</div>
                    @enderror

                    @error('custom_interests.*')
                        <div class="cpbn-error">{{ $message }}</div>
                    @enderror
                </div>

                @error('interests')
                    <div class="cpbn-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- SECTION 5: Projects -->
            <div class="section" id="ep-projects">
                <div class="title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    Projects &amp; Experience
                </div>

                <p class="desc">
                    Add your projects with details like technologies used, your role, and achievements.
                </p>

                @php
                    $projectRows = old('projects');

                    if ($projectRows === null) {
                        $projectRows = $user->projects
                            ->map(function ($project) {
                                return [
                                    'id' => $project->id,
                                    'title' => $project->title,
                                    'description' => $project->description,
                                    'technologies_used' => $project->technologies_used ?? [],
                                    'role' => $project->role,
                                    'project_url' => $project->project_url,
                                    'start_date' => $project->start_date?->timezone(config('app.business_timezone'))?->timezone(config('app.business_timezone'))->format('Y-m-d'),
                                    'end_date' => $project->end_date?->timezone(config('app.business_timezone'))?->timezone(config('app.business_timezone'))->format('Y-m-d'),
                                    'achievements' => $project->achievements,
                                ];
                            })
                            ->values()
                            ->all();
                    }

                    $nextProjectIndex = empty($projectRows)
                        ? 0
                        : max(
                            array_map(
                                'intval',
                                array_keys($projectRows)
                            )
                        ) + 1;
                @endphp

                <div
                    id="project-list"
                    data-next-index="{{ $nextProjectIndex }}"
                >
                    @foreach($projectRows as $index => $project)
                        @php
                            $technologiesUsed =
                                $project['technologies_used']
                                ?? [];

                            $technologies = is_array(
                                $technologiesUsed
                            )
                                ? implode(
                                    ', ',
                                    $technologiesUsed
                                )
                                : (string) $technologiesUsed;
                        @endphp

                        <div class="cpbn-project-card">
                            @if(! empty($project['id']))
                                <input
                                    type="hidden"
                                    name="projects[{{ $index }}][id]"
                                    value="{{ $project['id'] }}"
                                >
                            @endif

                            <span class="cpbn-project-number">
                                Project #{{ $loop->iteration }}
                            </span>

                            <div class="cpbn-fgrid">
                                <div class="cpbn-field">
                                    <label>
                                        Project Title
                                        <span class="req">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="projects[{{ $index }}][title]"
                                        value="{{ $project['title'] ?? '' }}"
                                        maxlength="150"
                                        placeholder="Hobbee Apps"
                                        required
                                    >
                                </div>

                                <div class="cpbn-field">
                                    <label>Your Role</label>
                                    <input
                                        type="text"
                                        name="projects[{{ $index }}][role]"
                                        value="{{ $project['role'] ?? '' }}"
                                        maxlength="100"
                                        placeholder="Laravel Developer, UI Designer, Team Member"
                                    >
                                </div>

                                <div class="cpbn-field">
                                    <label>Project URL</label>
                                    <input
                                        type="url"
                                        name="projects[{{ $index }}][project_url]"
                                        value="{{ $project['project_url'] ?? '' }}"
                                        maxlength="255"
                                        placeholder="GitHub repository or live project link"
                                    >
                                </div>

                                <div class="cpbn-field full">
                                    <label>Description</label>
                                    <textarea
                                        name="projects[{{ $index }}][description]"
                                        rows="2"
                                        maxlength="1000"
                                        placeholder="Briefly describe what the project does and your contribution."
                                    >{{ $project['description'] ?? '' }}</textarea>
                                </div>

                                <div class="cpbn-field full">
                                    <label>Technologies Used</label>
                                    <input
                                        type="text"
                                        name="projects[{{ $index }}][technologies_used]"
                                        value="{{ $technologies }}"
                                        maxlength="500"
                                        placeholder="Laravel, MySQL, JavaScript"
                                    >

                                    <span class="cpbn-file-note">
                                        Separate technologies with commas
                                    </span>
                                </div>

                                <div class="cpbn-field">
                                    <label>Start Date</label>
                                    <input
                                        type="date"
                                        name="projects[{{ $index }}][start_date]"
                                        value="{{ $project['start_date'] ?? '' }}"
                                    >
                                </div>

                                <div class="cpbn-field">
                                    <label>End Date</label>
                                    <input
                                        type="date"
                                        name="projects[{{ $index }}][end_date]"
                                        value="{{ $project['end_date'] ?? '' }}"
                                    >
                                </div>

                                <div class="cpbn-field full">
                                    <label>Achievements</label>
                                    <textarea
                                        name="projects[{{ $index }}][achievements]"
                                        rows="2"
                                        maxlength="500"
                                        placeholder="Completed the authentication module and integrated the recommendation API."
                                    >{{ $project['achievements'] ?? '' }}</textarea>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="cpbn-remove-project"
                                onclick="removeProject(this)"
                            >
                                <i class="fas fa-trash-alt"></i>
                                Remove
                            </button>
                        </div>
                    @endforeach
                </div>

                <div class="cpbn-section-foot">
                    <p class="cpbn-optional">Optional. Leave it empty if you have none &mdash; your profile still counts as complete.</p>

                    <button
                        type="button"
                        id="add-project"
                        class="cpbn-add-project"
                    >
                        <i class="fas fa-plus"></i>
                        Add Project
                    </button>
                </div>

            </div>

            <!-- SECTION 6: Certifications -->
            <div class="section" id="ep-certifications">
                <div class="title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="9" r="6"/>
                        <path d="M9 14.5 7 22l5-3 5 3-2-7.5"/>
                    </svg>
                    Certifications
                </div>

                <p class="desc">
                    Add any academic, professional, training or course certifications you have completed.
                </p>

                @php
                    $certificationRows = old('certifications');

                    if ($certificationRows === null) {
                        $certificationRows = $user->certifications
                            ->map(function ($certification) {
                                return [
                                    'id' => $certification->id,
                                    'certification_name' => $certification->certification_name,
                                    'issuing_organization' => $certification->issuing_organization,
                                    'issue_date' => $certification->issue_date?->timezone(config('app.business_timezone'))?->timezone(config('app.business_timezone'))->format('Y-m-d'),
                                ];
                            })
                            ->values()
                            ->all();
                    }

                    $nextCertificationIndex = empty($certificationRows)
                        ? 0
                        : max(
                            array_map(
                                'intval',
                                array_keys($certificationRows)
                            )
                        ) + 1;
                @endphp

                <div id="removed-certification-fields">
                    @foreach(old('removed_certification_ids', []) as $removedCertificationId)
                        <input
                            type="hidden"
                            name="removed_certification_ids[]"
                            value="{{ $removedCertificationId }}"
                        >
                    @endforeach
                </div>

                <div
                    id="certification-list"
                    data-next-index="{{ $nextCertificationIndex }}"
                >
                    @foreach($certificationRows as $index => $certification)
                        @php
                            $existingCertification = ! empty(
                                $certification['id']
                            )
                                ? $user->certifications->firstWhere(
                                    'id',
                                    (int) $certification['id']
                                )
                                : null;

                            $existingEvidenceAvailable =
                                $existingCertification?->certificate_file_path
                                && \Illuminate\Support\Facades\Storage::disk(
                                    'local'
                                )->exists(
                                    $existingCertification->certificate_file_path
                                );
                        @endphp

                        <div
                            class="cpbn-certification-card"
                            data-has-existing-evidence="{{ $existingEvidenceAvailable ? '1' : '0' }}"
                        >
                            @if(! empty($certification['id']))
                                <input
                                    type="hidden"
                                    name="certifications[{{ $index }}][id]"
                                    value="{{ $certification['id'] }}"
                                >
                            @endif

                            <div class="cpbn-fgrid">
                                <div class="cpbn-field">
                                    <label>
                                        Certification Name
                                        <span class="req">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        name="certifications[{{ $index }}][certification_name]"
                                        value="{{ $certification['certification_name'] ?? '' }}"
                                        maxlength="150"
                                        placeholder="AWS Certified Cloud Practitioner"
                                        required
                                    >
                                </div>

                                <div class="cpbn-field">
                                    <label>Issuing Organisation</label>

                                    <input
                                        type="text"
                                        name="certifications[{{ $index }}][issuing_organization]"
                                        value="{{ $certification['issuing_organization'] ?? '' }}"
                                        maxlength="150"
                                        placeholder="Amazon Web Services, Cisco, Politeknik Brunei"
                                    >
                                </div>

                                <div class="cpbn-field">
                                    <label>Issue Date</label>

                                    <input
                                        type="date"
                                        name="certifications[{{ $index }}][issue_date]"
                                        value="{{ $certification['issue_date'] ?? '' }}"
                                        max="{{ now()->timezone(config('app.business_timezone'))->format('Y-m-d') }}"
                                    >
                                </div>

                                <div class="cpbn-field">
                                    <label>Certificate File</label>

                                    <input
                                        type="file"
                                        class="cpbn-certificate-file"
                                        name="certifications[{{ $index }}][certificate_file]"
                                        accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                    >

                                    <span class="cpbn-file-note">
                                        PDF, JPG, JPEG or PNG · Maximum 5 MB
                                    </span>

                                    @if($existingEvidenceAvailable)
                                        <div class="cpbn-file-existing">
                                            Existing evidence uploaded

                                            @if($existingCertification->certificate_original_name)
                                                <span class="cpbn-file-existing-name">
                                                    Current file:
                                                    {{ $existingCertification->certificate_original_name }}
                                                </span>
                                            @else
                                                <span class="cpbn-file-existing-name">
                                                    Original filename unavailable for this older upload.
                                                </span>
                                            @endif

                                            <a
                                                href="{{ route(
                                                    'student.certifications.evidence',
                                                    $existingCertification->id
                                                ) }}"
                                                target="_blank"
                                                rel="noopener"
                                                class="cpbn-file-view"
                                            >
                                                View Evidence
                                            </a>
                                        </div>

                                        <span class="cpbn-file-note">
                                            Current evidence will be kept unless a replacement is successfully saved.
                                        </span>
                                    @elseif($existingCertification?->certificate_file_path)
                                        <div class="cpbn-file-missing">
                                            <i class="fas fa-triangle-exclamation"></i> An evidence record exists, but the stored file could not be found.
                                        </div>
                                    @endif

                                    <div
                                        class="cpbn-file-selected"
                                        hidden
                                    ></div>

                                    <button
                                        type="button"
                                        class="cpbn-clear-file"
                                        onclick="clearSelectedCertificateFile(this)"
                                        hidden
                                    >
                                        Clear selected file
                                    </button>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="cpbn-remove-certification"
                                onclick="removeCertification(this)"
                            >
                                <i class="fas fa-trash-alt"></i>
                                Remove
                            </button>
                        </div>
                    @endforeach
                </div>

                <div class="cpbn-section-foot">
                    <p class="cpbn-optional">Optional. Leave it empty if you have none &mdash; your profile still counts as complete.</p>

                    <button
                        type="button"
                        id="add-certification"
                        class="cpbn-add-certification"
                    >
                        <i class="fas fa-plus"></i>
                        Add Certification
                    </button>
                </div>

            </div>

            <!-- SECTION 7: Aspirations -->
            <div class="section" id="ep-aspirations">
                <div class="title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2l2.9 6.6 7.1.7-5.4 4.7 1.7 7-6.3-3.8L5.7 21l1.7-7-5.4-4.7 7.1-.7z"/>
                    </svg>
                    Career Aspirations
                </div>

                <div class="cpbn-fgrid">
                    <div class="cpbn-field full">
                        <label>Dream Career</label>
                        <input
                            type="text"
                            name="career_goals_text"
                            value="{{ old('career_goals_text', $user->aspirations->career_goals[0] ?? '') }}"
                            maxlength="100"
                            placeholder="Software Engineer"
                        >
                    </div>

                    <div class="cpbn-field full">
                        <label>Vision Statement</label>

                        <textarea
                            name="vision_statement"
                            rows="2"
                            maxlength="500"
                            placeholder="I want to use technology to build useful solutions that improve people's work and daily lives."
                        >{{ old('vision_statement', $user->aspirations->vision_statement ?? '') }}</textarea>

                        <span class="hint">
                            Describe the kind of impact or future you hope to create through your career.
                        </span>
                    </div>

                    <div class="cpbn-field full">
                        <label>Long Term Goals</label>
                        <textarea
                            name="long_term_goals"
                            rows="2"
                            maxlength="500"
                            placeholder="Become a senior developer and eventually lead software projects."
                        >{{ old('long_term_goals', $user->aspirations->long_term_goals ?? '') }}</textarea>

                        <span class="hint">
                            Describe what you would like to achieve in your career over the coming years.
                        </span>
                    </div>
                </div>
            </div>

            <div class="submit-row">
                <p class="ep-save-note">
                    Your progress can be saved now and completed later.
                </p>

                <button
                    type="submit"
                    class="cpbn-btn cpbn-btn-primary"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <path d="M17 21v-8H7v8M7 3v5h8"/>
                    </svg>
                    Save Profile
                </button>
            </div>
        </form>

            </div>
        </div>

    </div>
</div>

<script>
    /*
     * Side navigator for the profile sections. Highlights the
     * section being read and scrolls to one when it is clicked.
     * It only reads the page and never touches the form's data.
     */
    document.addEventListener('DOMContentLoaded', function () {
        const links = Array.from(
            document.querySelectorAll('.ep-nav-link[data-ep-target]')
        );

        if (links.length === 0) {
            return;
        }

        const reduceMotion = window.matchMedia(
            '(prefers-reduced-motion: reduce)'
        ).matches;

        const setActive = function (id) {
            links.forEach(function (link) {
                const active = link.dataset.epTarget === id;

                link.classList.toggle('is-active', active);

                if (active) {
                    link.setAttribute('aria-current', 'true');

                    /*
                     * On narrow screens the tabs scroll sideways. Move
                     * only that strip. scrollIntoView() would drag the
                     * whole page back up to the tabs while the student
                     * is scrolling down.
                     */
                    const strip = link.parentElement;

                    if (
                        window.innerWidth <= 1000
                        && strip
                        && strip.scrollWidth > strip.clientWidth
                    ) {
                        strip.scrollTo({
                            left: link.offsetLeft
                                - (strip.clientWidth - link.offsetWidth) / 2,
                            behavior: reduceMotion ? 'auto' : 'smooth',
                        });
                    }
                } else {
                    link.removeAttribute('aria-current');
                }
            });
        };

        links.forEach(function (link) {
            link.addEventListener('click', function (event) {
                const target = document.getElementById(
                    link.dataset.epTarget
                );

                if (! target) {
                    return;
                }

                event.preventDefault();

                target.scrollIntoView({
                    behavior: reduceMotion ? 'auto' : 'smooth',
                    block: 'start',
                });

                setActive(link.dataset.epTarget);
            });
        });

        if (! ('IntersectionObserver' in window)) {
            setActive(links[0].dataset.epTarget);

            return;
        }

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    setActive(entry.target.id);
                }
            });
        }, { rootMargin: '-25% 0px -65% 0px' });

        links.forEach(function (link) {
            const section = document.getElementById(link.dataset.epTarget);

            if (section) {
                observer.observe(section);
            }
        });

        setActive(links[0].dataset.epTarget);
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const markChanged = function () {
        if (typeof window.setFormChanged === 'function') {
            window.setFormChanged(true);
        }
    };

    const obviousKeyboardSmashes = new Set([
        'qwerty',
        'qwertyui',
        'qwertyuiop',
        'asdfgh',
        'asdfghjkl',
        'zxcvbn',
        'zxcvbnm'
    ]);

    const initialiseAdditionalField = function (config) {
        const toggle = document.getElementById(config.toggleId);
        const section = document.getElementById(config.sectionId);
        const input = document.getElementById(config.inputId);
        const addButton = document.getElementById(config.addButtonId);
        const chips = document.getElementById(config.chipsId);
        const feedback = document.getElementById(config.feedbackId);
        const feedbackText = document.getElementById(config.feedbackTextId);
        const warningActions = document.getElementById(
            config.warningActionsId
        );
        const editButton = document.getElementById(config.editButtonId);
        const addAnywayButton = document.getElementById(
            config.addAnywayButtonId
        );

        if (
            ! toggle
            || ! section
            || ! input
            || ! addButton
            || ! chips
            || ! feedback
            || ! feedbackText
            || ! warningActions
            || ! editButton
            || ! addAnywayButton
        ) {
            return;
        }

        const predefined = Array.isArray(config.predefined)
            ? config.predefined
            : [];

        const aliasGroups = Array.isArray(config.aliasGroups)
            ? config.aliasGroups
            : [];

        let pendingValue = null;

        const cleanValue = function (value) {
            return String(value)
                .trim()
                .replace(/\s+/gu, ' ');
        };

        const normaliseKey = function (value) {
            return cleanValue(value)
                .toLocaleLowerCase()
                .replace(/[\s\/_-]+/gu, '');
        };

        const canonicalKey = function (value) {
            const key = normaliseKey(value);

            for (const group of aliasGroups) {
                if (
                    ! Array.isArray(group)
                    || group.length === 0
                ) {
                    continue;
                }

                const matches = group.some(
                    alias =>
                        normaliseKey(String(alias))
                        === key
                );

                if (matches) {
                    return normaliseKey(
                        String(group[0])
                    );
                }
            }

            return key;
        };

        const currentCustomValues = function () {
            return Array.from(
                chips.querySelectorAll(
                    '.cpbn-custom-chip'
                )
            ).map(
                chip => chip.dataset.value || ''
            );
        };

        const allKnownTerms = function () {
            return [
                ...predefined,
                ...currentCustomValues(),
                ...aliasGroups.flat()
            ].filter(Boolean);
        };

        const findDuplicate = function (value) {
            const key = canonicalKey(value);

            const predefinedMatch = predefined.find(
                item =>
                    canonicalKey(item)
                    === key
            );

            if (predefinedMatch) {
                return {
                    type: 'predefined',
                    value: predefinedMatch
                };
            }

            const customMatch = currentCustomValues().find(
                item =>
                    canonicalKey(item)
                    === key
            );

            if (customMatch) {
                return {
                    type: 'custom',
                    value: customMatch
                };
            }

            return null;
        };

        const validateClearlyInvalid = function (value) {
            if (! value) {
                return `Enter a ${config.singular} before adding it.`;
            }

            if (value.length > 40) {
                return `Each additional ${config.singular} must be 40 characters or fewer.`;
            }

            const lettersAndNumbers = value.replace(
                /[^\p{L}\p{N}]+/gu,
                ''
            );

            if (! lettersAndNumbers) {
                return config.invalidMessage;
            }

            if (/^\d+$/u.test(lettersAndNumbers)) {
                return `A ${config.singular} cannot contain only numbers. ${config.invalidMessage}`;
            }

            const lower =
                lettersAndNumbers.toLocaleLowerCase();

            if (
                lower.length >= 4
                && /^(.)\1{3,}$/u.test(lower)
            ) {
                return `This looks like repeated characters rather than a ${config.singular}. Check the value and try again.`;
            }

            if (
                obviousKeyboardSmashes.has(lower)
            ) {
                return `This looks like a keyboard-smash rather than a ${config.singular}. ${config.invalidMessage}`;
            }

            return null;
        };

        const findSimilarTerm = function (value) {
            const distance =
                window.cpbnLevenshteinDistance;

            if (typeof distance !== 'function') {
                return null;
            }

            const comparable = cleanValue(value)
                .toLocaleLowerCase();

            if (comparable.length < 5) {
                return null;
            }

            let best = null;

            for (const candidate of allKnownTerms()) {
                const candidateComparable =
                    cleanValue(candidate)
                        .toLocaleLowerCase();

                if (
                    ! candidateComparable
                    || canonicalKey(candidateComparable)
                        === canonicalKey(comparable)
                ) {
                    continue;
                }

                const difference = distance(
                    comparable,
                    candidateComparable
                );

                const maxLength = Math.max(
                    comparable.length,
                    candidateComparable.length
                );

                const similarity = 1 - (
                    difference / maxLength
                );

                if (
                    difference <= 2
                    && similarity >= 0.75
                    && (
                        ! best
                        || similarity > best.similarity
                    )
                ) {
                    best = {
                        value: candidate,
                        similarity
                    };
                }
            }

            return best;
        };

        const looksUnusual = function (value) {
            const lettersOnly = value.replace(
                /[^A-Za-z]/g,
                ''
            );

            return (
                lettersOnly.length >= 6
                && ! /[aeiouy]/i.test(
                    lettersOnly
                )
            );
        };

        const hideFeedback = function () {
            feedback.hidden = true;
            feedback.classList.remove(
                'warning',
                'error'
            );
            feedbackText.textContent = '';
            warningActions.hidden = true;
            pendingValue = null;
        };

        const showError = function (message) {
            feedback.hidden = false;
            feedback.classList.remove('warning');
            feedback.classList.add('error');
            feedbackText.textContent = message;
            warningActions.hidden = true;
            pendingValue = null;
        };

        const showWarning = function (
            message,
            value
        ) {
            feedback.hidden = false;
            feedback.classList.remove('error');
            feedback.classList.add('warning');
            feedbackText.textContent = message;
            warningActions.hidden = false;
            pendingValue = value;
        };

        const createChip = function (value) {
            const chip =
                document.createElement('span');

            chip.className =
                'cpbn-custom-chip';

            chip.dataset.value = value;

            const label =
                document.createElement('span');

            label.textContent = value;

            const removeButton =
                document.createElement('button');

            removeButton.type = 'button';

            removeButton.className =
                'cpbn-remove-custom-chip';

            removeButton.setAttribute(
                'aria-label',
                `Remove ${value}`
            );

            removeButton.title = 'Remove';
            removeButton.innerHTML = '&times;';

            const hidden =
                document.createElement('input');

            hidden.type = 'hidden';
            hidden.name = config.hiddenName;
            hidden.value = value;

            chip.appendChild(label);

            if (
                Array.isArray(
                    config.proficiencyLevels
                )
                && config.levelName
            ) {
                const levelSelect =
                    document.createElement(
                        'select'
                    );

                levelSelect.name =
                    config.levelName;

                levelSelect.className =
                    'cpbn-custom-level-select';

                levelSelect.required = true;

                const placeholder =
                    document.createElement(
                        'option'
                    );

                placeholder.value = '';
                placeholder.textContent =
                    'Choose level';

                levelSelect.appendChild(
                    placeholder
                );

                config.proficiencyLevels
                    .forEach(level => {
                        const option =
                            document.createElement(
                                'option'
                            );

                        option.value =
                            level.id;

                        option.textContent =
                            `Level ${level.level_number} — ${level.name}`;

                        levelSelect.appendChild(
                            option
                        );
                    });

                levelSelect.addEventListener(
                    'change',
                    markChanged
                );

                chip.appendChild(
                    levelSelect
                );
            }

            chip.append(
                removeButton,
                hidden
            );

            chips.appendChild(chip);

            input.value = '';
            hideFeedback();
            markChanged();
            input.focus();
        };

        const attemptAdd = function () {
            const value = cleanValue(
                input.value
            );

            const invalidMessage =
                validateClearlyInvalid(value);

            if (invalidMessage) {
                showError(invalidMessage);
                return;
            }

            const duplicate =
                findDuplicate(value);

            if (duplicate) {
                if (
                    duplicate.type
                    === 'predefined'
                ) {
                    showError(
                        `"${value}" matches the predefined ${config.singular} "${duplicate.value}". Select that option instead.`
                    );
                } else {
                    showError(
                        `"${value}" is already in your additional ${config.plural}.`
                    );
                }

                return;
            }

            const similar =
                findSimilarTerm(value);

            if (similar) {
                showWarning(
                    `This looks similar to "${similar.value}". Check the spelling, or add it anyway if this is a different term.`,
                    value
                );

                return;
            }

            if (looksUnusual(value)) {
                showWarning(
                    `This ${config.singular} looks unusual. It may be a new term, acronym, or spelling variation. Check it before adding.`,
                    value
                );

                return;
            }

            createChip(value);
        };

        const setEnabled = function (
            enabled,
            shouldFocus = false
        ) {
            section.hidden = ! enabled;

            section
                .querySelectorAll(
                    `input[name="${config.hiddenName}"]`
                )
                .forEach(hidden => {
                    hidden.disabled = ! enabled;
                });

            if (config.levelName) {
                section
                    .querySelectorAll(
                        `select[name="${config.levelName}"]`
                    )
                    .forEach(select => {
                        select.disabled = ! enabled;
                    });
            }

            if (enabled && shouldFocus) {
                input.focus();
            }

            if (! enabled) {
                hideFeedback();
            }
        };

        setEnabled(
            toggle.checked,
            false
        );

        toggle.addEventListener(
            'change',
            function () {
                setEnabled(
                    this.checked,
                    this.checked
                );

                markChanged();
            }
        );

        addButton.addEventListener(
            'click',
            attemptAdd
        );

        input.addEventListener(
            'keydown',
            function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    attemptAdd();
                }
            }
        );

        input.addEventListener(
            'input',
            hideFeedback
        );

        chips.addEventListener(
            'click',
            function (event) {
                const removeButton =
                    event.target.closest(
                        '.cpbn-remove-custom-chip'
                    );

                if (! removeButton) {
                    return;
                }

                const chip =
                    removeButton.closest(
                        '.cpbn-custom-chip'
                    );

                if (chip) {
                    chip.remove();
                    hideFeedback();
                    markChanged();
                }
            }
        );

        editButton.addEventListener(
            'click',
            function () {
                hideFeedback();
                input.focus();
                input.select();
            }
        );

        addAnywayButton.addEventListener(
            'click',
            function () {
                if (! pendingValue) {
                    return;
                }

                const value = pendingValue;
                createChip(value);
            }
        );
    };

    const initialiseCompetencyPagination =
        function(config) {
            const grid =
                document.getElementById(
                    config.gridId
                );

            const toggle =
                document.getElementById(
                    config.toggleId
                );

            const previous =
                document.getElementById(
                    config.previousId
                );

            const next =
                document.getElementById(
                    config.nextId
                );

            const pageStatus =
                document.getElementById(
                    config.statusId
                );

            if (
                ! grid
                || ! toggle
                || ! previous
                || ! next
                || ! pageStatus
            ) {
                return;
            }

            const pageSize =
                config.pageSize || 10;

            const all =
                Array.from(
                    grid.querySelectorAll(
                        config.itemSelector
                    )
                );

            /*
             * What the filters have left in. Paging a list
             * captured once would count the hidden ones and
             * leave empty pages behind a filter.
             */
            const items =
                function() {
                    return all.filter(
                        function(item) {
                            return item.dataset
                                .filteredOut
                                !== 'true';
                        }
                    );
                };

            const totalPages =
                function() {
                    return Math.max(
                        1,
                        Math.ceil(
                            items().length
                            / pageSize
                        )
                    );
                };

            let currentPage = 0;
            let showingAll = false;

            const update =
                function() {
                    if (showingAll) {
                        all.forEach(
                            function(item) {
                                item.hidden =
                                    item.dataset
                                        .filteredOut
                                    === 'true';
                            }
                        );

                        previous.hidden = true;
                        next.hidden = true;

                        pageStatus.textContent =
                            `Showing all ${items().length}`;

                        toggle.textContent =
                            'Show Less';

                        toggle.setAttribute(
                            'aria-expanded',
                            'true'
                        );

                        return;
                    }

                    const start =
                        currentPage
                        * pageSize;

                    const end =
                        start
                        + pageSize;

                    all.forEach(
                        function(item) {
                            item.hidden = true;
                        }
                    );

                    items().forEach(
                        function(item, index) {
                            item.hidden =
                                index < start
                                || index >= end;
                        }
                    );

                    previous.hidden = false;
                    next.hidden = false;

                    previous.disabled =
                        currentPage === 0;

                    next.disabled =
                        currentPage
                        >= totalPages() - 1;

                    pageStatus.textContent =
                        `Page ${currentPage + 1} of ${totalPages()}`;

                    toggle.textContent =
                        `Show All (${items().length})`;

                    toggle.setAttribute(
                        'aria-expanded',
                        'false'
                    );
                };

            previous.addEventListener(
                'click',
                function() {
                    if (currentPage <= 0) {
                        return;
                    }

                    currentPage--;

                    update();
                }
            );

            next.addEventListener(
                'click',
                function() {
                    if (
                        currentPage
                        >= totalPages() - 1
                    ) {
                        return;
                    }

                    currentPage++;

                    update();
                }
            );

            toggle.addEventListener(
                'click',
                function() {
                    showingAll =
                        ! showingAll;

                    update();
                }
            );

            update();

            /*
             * How the filter asks for the pages to be counted
             * again. Back to the first page, since page four of
             * a longer list is nowhere in a shorter one.
             */
            return function() {
                currentPage = 0;

                update();
            };
        };

    const repageTechnical =
        initialiseCompetencyPagination({
            gridId:
                'technical-competency-grid',

            itemSelector:
                '[data-technical-competency]',

            previousId:
                'technical-competency-previous',

            nextId:
                'technical-competency-next',

            statusId:
                'technical-competency-page-status',

            toggleId:
                'technical-competency-toggle',

            pageSize:
                10
        });

    /*
     * The sub-sector, job level and job role filters.
     *
     * Client-side, because every competency is already on the
     * page and asking the server to send a shorter list would be
     * a round trip to hide things we are holding.
     *
     * All three narrow together: a competency has to answer every
     * one of them to stay on screen. The role is the one that
     * does the work — a sub-sector at a level still runs to
     * thirty-nine in the combinations students pick most, where
     * one role is eight to seventeen — and the two rows above it
     * narrow which roles are offered rather than being made
     * redundant by it.
     *
     * Two kinds of competency are never hidden. One already
     * ticked, because a student who filters to ICT Security and
     * watches four of their own choices vanish has every reason
     * to think the page threw them away. And one the framework
     * files under no level at all, because no level chip would
     * ever show it and it would stop being pickable at all.
     */
    const sectorFilter =
        document.getElementById(
            'technical-sector-filter'
        );

    const levelFilter =
        document.getElementById(
            'technical-level-filter'
        );

    const roleFilter =
        document.getElementById(
            'technical-role-filter'
        );

    if ((sectorFilter || levelFilter || roleFilter) && repageTechnical) {
        const emptyNote =
            document.getElementById(
                'technical-filter-empty'
            );

        let wantedSector = '';
        let wantedLevel = '';
        let wantedRole = '';

        const matches =
            function(item, attribute, wanted, showWhenBlank) {
                if (wanted === '') {
                    return true;
                }

                const values =
                    (item.dataset[attribute] || '')
                        .split('|')
                        .filter(Boolean);

                if (values.length === 0) {
                    return showWhenBlank;
                }

                return values.includes(wanted);
            };

        /*
         * Saying what the list is showing, and why it may hold
         * more than was asked for.
         *
         * This is the question the three rows raise and never
         * answered: a student who filters to one role and then
         * opens every page sees their own earlier ticks among
         * that role's competencies, with nothing to say which is
         * which.
         */
        const filtering =
            function() {
                return wantedSector !== ''
                    || wantedLevel !== ''
                    || wantedRole !== '';
            };

        const summary =
            document.getElementById(
                'technical-filter-summary'
            );

        const activeNote =
            document.getElementById(
                'technical-filter-active'
            );

        const describeFilters =
            function(asked, keptByTick) {
                const chosen = [];

                if (roleFilter) {
                    const role = roleFilter.querySelector(
                        '.is-on[data-role]:not([data-role=""])'
                    );

                    if (role) {
                        chosen.push(role.textContent.trim());
                    }
                }

                if (wantedLevel !== '') {
                    chosen.push(wantedLevel);
                }

                if (wantedSector !== '') {
                    chosen.push(wantedSector);
                }

                if (activeNote) {
                    activeNote.textContent =
                        chosen.length > 0
                            ? chosen.join(' · ')
                            : '';
                }

                if (!summary) {
                    return;
                }

                if (!filtering()) {
                    summary.hidden = true;
                    summary.textContent = '';

                    return;
                }

                summary.hidden = false;

                const subject =
                    chosen.length > 0
                        ? chosen.join(' · ')
                        : 'these filters';

                summary.textContent =
                    keptByTick > 0
                        ? `${asked} for ${subject}, plus `
                            + `${keptByTick} you have already `
                            + `ticked — those are marked.`
                        : `${asked} for ${subject}.`;
            };

        const apply =
            function() {
                const items =
                    document.querySelectorAll(
                        '[data-technical-competency]'
                    );

                let showing = 0;
                let asked = 0;
                let keptByTick = 0;

                items.forEach(
                    function(item) {
                        const ticked =
                            item.querySelector(
                                'input[type="checkbox"]'
                            );

                        const wanted = (
                                /*
                                 * A competency no role asks for
                                 * is in no sub-sector and under
                                 * no role, and both chips have
                                 * always hidden it. Levels are
                                 * the other way round: a tier
                                 * that is a numbered step inside
                                 * a level names none, so a blank
                                 * list there means unrecorded
                                 * rather than inapplicable.
                                 */
                                matches(item, 'sectors', wantedSector, false)
                                && matches(item, 'levels', wantedLevel, true)
                                && matches(item, 'roles', wantedRole, false)
                            );

                        const ownIt = !!(ticked && ticked.checked);

                        const keep = wanted || ownIt;

                        item.dataset.filteredOut =
                            keep ? 'false' : 'true';

                        /*
                         * On screen for the student's own sake
                         * rather than the filter's. Marked, so a
                         * row the filter did not ask for does not
                         * read as the filter having leaked.
                         */
                        item.dataset.keptByTick =
                            (ownIt && !wanted) ? 'true' : 'false';

                        if (ownIt && !wanted) {
                            item.title =
                                'You have ticked this. It stays on '
                                + 'screen whatever the filters say.';
                        } else {
                            item.removeAttribute('title');
                        }

                        if (wanted) {
                            asked++;
                        }

                        if (ownIt && !wanted) {
                            keptByTick++;
                        }

                        if (keep) {
                            showing++;
                        }
                    }
                );

                if (emptyNote) {
                    emptyNote.hidden = showing > 0;
                }

                describeFilters(asked, keptByTick);

                repageTechnical();
            };


        /*
         * Which roles the rows above leave on offer.
         *
         * A role whose sub-sector or level has just been filtered
         * away stops being offered, and if it was the one
         * selected the selection goes with it. Left standing it
         * would be a filter with no chip on screen to say so: the
         * grid would hold one role's competencies and nothing
         * would explain why.
         *
         * "All" is never taken away, which is also how the
         * selection gets somewhere to land.
         */
        const roleChips =
            roleFilter
                ? Array.from(
                    roleFilter.querySelectorAll('[data-role]')
                )
                : [];

        const refreshRoleChips =
            function() {
                let selectionGone = false;

                roleChips.forEach(
                    function(chip) {
                        if (chip.dataset.role === '') {
                            return;
                        }

                        const fits =
                            matches(chip, 'roleSector', wantedSector, false)
                            && matches(chip, 'roleLevels', wantedLevel, true);

                        chip.hidden = !fits;

                        if (
                            !fits
                            && chip.classList.contains('is-on')
                        ) {
                            selectionGone = true;
                        }
                    }
                );

                if (!selectionGone) {
                    return;
                }

                roleChips.forEach(
                    function(chip) {
                        chip.classList.toggle(
                            'is-on',
                            chip.dataset.role === ''
                        );
                    }
                );

                wantedRole = '';
            };

        /*
         * One row of chips, one of which is on at a time. All
         * three rows behave the same way, so they are wired the
         * same way rather than three times.
         */
        const wireRow =
            function(row, attribute, onPick) {
                if (!row) {
                    return function() {};
                }

                const chips =
                    Array.from(
                        row.querySelectorAll('[data-' + attribute + ']')
                    );

                const select =
                    function(chip) {
                        chips.forEach(
                            function(other) {
                                other.classList.toggle(
                                    'is-on',
                                    other === chip
                                );
                            }
                        );

                        onPick(chip.dataset[attribute]);
                    };

                chips.forEach(
                    function(chip) {
                        chip.addEventListener(
                            'click',
                            function() {
                                select(chip);

                                /*
                                 * Before applying, so a role the
                                 * new selection no longer offers
                                 * is cleared rather than left
                                 * filtering invisibly.
                                 */
                                refreshRoleChips();

                                apply();
                            }
                        );
                    }
                );

                /* Back to "All", without a click. */
                return function() {
                    if (chips.length > 0) {
                        select(chips[0]);
                    }
                };
            };

        const resetSector =
            wireRow(
                sectorFilter,
                'sector',
                function(value) {
                    wantedSector = value;
                }
            );

        const resetLevel =
            wireRow(
                levelFilter,
                'level',
                function(value) {
                    wantedLevel = value;
                }
            );

        const resetRole =
            wireRow(
                roleFilter,
                'role',
                function(value) {
                    wantedRole = value;
                }
            );

        document.querySelectorAll('[data-filter-reset]').forEach(
            function(button) {
                button.addEventListener(
                    'click',
                    function() {
                        resetSector();
                        resetLevel();
                        resetRole();

                        refreshRoleChips();

                        apply();
                    }
                );
            }
        );

        /*
         * Putting the chips away.
         *
         * Open to begin with: a filter nobody finds is no better
         * than one that is not there. What this is for is the
         * student who has chosen their role and wants the three
         * rows out of the way of the list — so what is filtered
         * stays on the line that remains, or shutting the block
         * would hide a list narrowed to thirteen with nothing on
         * screen to say why.
         */
        const filterBlock =
            document.getElementById(
                'technical-filters'
            );

        const filterToggle =
            document.getElementById(
                'technical-filter-toggle'
            );

        const filterToggleLabel =
            document.getElementById(
                'technical-filter-toggle-label'
            );

        if (filterBlock && filterToggle) {
            filterToggle.addEventListener(
                'click',
                function() {
                    const open = filterBlock.hidden;

                    filterBlock.hidden = !open;

                    filterToggle.setAttribute(
                        'aria-expanded',
                        open ? 'true' : 'false'
                    );

                    if (filterToggleLabel) {
                        filterToggleLabel.textContent =
                            open
                                ? 'Hide filters'
                                : 'Filters';
                    }
                }
            );
        }
    }

    initialiseCompetencyPagination({
        gridId:
            'soft-competency-grid',

        itemSelector:
            '[data-soft-competency]',

        previousId:
            'soft-competency-previous',

        nextId:
            'soft-competency-next',

        statusId:
            'soft-competency-page-status',

        toggleId:
            'soft-competency-toggle',

        pageSize:
            10
    });

    /*
    * If native form validation finds a required
    * proficiency level on a hidden competency page,
    * reveal that competency list before the browser
    * tries to focus the invalid field.
    */
    const profileForm =
        document.getElementById(
            'profile-form'
        );

    if (profileForm) {
        profileForm.addEventListener(
            'invalid',
            function(event) {
                const field =
                    event.target;

                if (
                    ! field.classList.contains(
                        'biicf-proficiency-select'
                    )
                ) {
                    return;
                }

                const competencyItem =
                    field.closest(
                        '.cpbn-competency-item'
                    );

                if (
                    ! competencyItem
                    || ! competencyItem.hidden
                ) {
                    return;
                }

                const grid =
                    competencyItem.closest(
                        '.cpbn-competency-grid'
                    );

                if (! grid) {
                    return;
                }

                let toggle = null;

                if (
                    grid.id
                    === 'technical-competency-grid'
                ) {
                    toggle =
                        document.getElementById(
                            'technical-competency-toggle'
                        );
                }

                if (
                    grid.id
                    === 'soft-competency-grid'
                ) {
                    toggle =
                        document.getElementById(
                            'soft-competency-toggle'
                        );
                }

                if (
                    toggle
                    && toggle.getAttribute(
                        'aria-expanded'
                    ) === 'false'
                ) {
                    toggle.click();
                }
            },
            true
        );
    }

    /*
     * BIICF competency controls.
     *
     * A proficiency level is only enabled when
     * its competency is selected.
     */
    document
        .querySelectorAll(
            '[data-biicf-competency]'
        )
        .forEach(item => {
            const checkbox =
                item.querySelector(
                    '.biicf-competency-checkbox'
                );

            const select =
                item.querySelector(
                    '.biicf-proficiency-select'
                );

            if (! checkbox || ! select) {
                return;
            }

            const syncState = function () {
                select.disabled =
                    ! checkbox.checked;

                select.required =
                    checkbox.checked;

                item.classList.toggle(
                    'selected',
                    checkbox.checked
                );

                if (! checkbox.checked) {
                    select.value = '';
                }
            };

            checkbox.addEventListener(
                'change',
                function () {
                    syncState();
                    markChanged();
                }
            );

            select.addEventListener(
                'change',
                markChanged
            );

            syncState();
        });

    initialiseAdditionalField({
        toggleId: 'additional-skills-toggle',
        sectionId: 'additional-skills-section',
        inputId: 'additional-skill-input',
        addButtonId: 'add-additional-skill',
        chipsId: 'additional-skill-chips',
        feedbackId: 'additional-skill-feedback',
        feedbackTextId: 'additional-skill-feedback-text',
        warningActionsId: 'additional-skill-warning-actions',
        editButtonId: 'edit-additional-skill',
        addAnywayButtonId: 'add-additional-skill-anyway',

        hiddenName: 'custom_skills[]',
        levelName: 'custom_skill_levels[]',
        proficiencyLevels: @json($proficiencyLevelOptions),

        singular: 'skill',
        plural: 'skills',
        predefined: @json($skillOptions ?? []),
        aliasGroups: @json($skillAliasGroups ?? []),
        invalidMessage: 'Please enter a skill, technology, tool, method, or competency you have.'
    });

    initialiseAdditionalField({
        toggleId: 'additional-interests-toggle',
        sectionId: 'additional-interests-section',
        inputId: 'additional-interest-input',
        addButtonId: 'add-additional-interest',
        chipsId: 'additional-interest-chips',
        feedbackId: 'additional-interest-feedback',
        feedbackTextId: 'additional-interest-feedback-text',
        warningActionsId: 'additional-interest-warning-actions',
        editButtonId: 'edit-additional-interest',
        addAnywayButtonId: 'add-additional-interest-anyway',
        hiddenName: 'custom_interests[]',
        singular: 'interest',
        plural: 'interests',
        predefined: @json($interestOptions ?? []),
        aliasGroups: @json($interestAliasGroups ?? []),
        invalidMessage: 'Please enter a topic, field, technology, or activity you are interested in.'
    });
});
</script>

@endsection