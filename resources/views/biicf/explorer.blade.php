@extends('layouts.app')

@section('title', 'BIICF Explorer')

@section('content')

<style>
    .biicf-page {
        padding: 24px 0 40px;
    }

    .biicf-header {
        margin-bottom: 28px;
    }

    .biicf-header h1 {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: var(--primary);
    }

    .biicf-header h1 span {
        color: var(--accent);
    }

    .biicf-header .subtitle {
        color: var(--text-muted);
        font-size: 14px;
        margin-top: 2px;
    }

    .biicf-header .badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(26, 58, 92, 0.06);
        padding: 4px 14px;
        border-radius: 100px;
        font-size: 12px;
        color: var(--primary);
        margin-top: 8px;
    }

    .biicf-grid {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 24px;
    }

    /* Sidebar Navigation */
    .biicf-sidebar {
        position: sticky;
        top: 90px;
        align-self: start;
    }

    .biicf-sidebar .nav-group {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 8px;
    }

    .biicf-sidebar .nav-item {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 10px 14px;
        border: none;
        border-radius: 8px;
        background: transparent;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        font-family: inherit;
        text-align: left;
    }

    .biicf-sidebar .nav-item:hover {
        background: var(--bg);
        color: var(--primary);
    }

    .biicf-sidebar .nav-item.active {
        background: rgba(26, 58, 92, 0.08);
        color: var(--primary);
        font-weight: 600;
    }

    .biicf-sidebar .nav-item i {
        width: 18px;
        font-size: 14px;
    }

    .biicf-sidebar .nav-item .count {
        margin-left: auto;
        font-size: 11px;
        background: var(--bg);
        padding: 1px 10px;
        border-radius: 100px;
        color: var(--text-muted);
    }

    .biicf-sidebar .nav-item.active .count {
        background: rgba(26, 58, 92, 0.08);
        color: var(--primary);
    }

    /* Main Content */
    .biicf-content {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 28px;
        min-height: 500px;
    }

    .biicf-content .section-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .biicf-content .section-title i {
        color: var(--accent);
    }

    /* Breadcrumb */
    .breadcrumb-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        background: rgba(26, 58, 92, 0.04);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 10px 16px;
        margin-bottom: 20px;
        font-size: 13px;
    }

    .breadcrumb-bar .crumb {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-muted);
    }

    .breadcrumb-bar .crumb i {
        color: var(--accent);
    }

    .breadcrumb-bar .crumb .sep {
        color: var(--border);
    }

    .breadcrumb-bar .crumb .current {
        color: var(--primary);
        font-weight: 600;
    }

    .breadcrumb-bar .change-role-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: none;
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 5px 12px;
        font-size: 12px;
        color: var(--text-muted);
        cursor: pointer;
        font-family: inherit;
        transition: var(--transition);
    }

    .breadcrumb-bar .change-role-btn:hover {
        border-color: var(--accent-light);
        color: var(--primary);
    }

    /* Role Overview */
    .overview-card {
        background: rgba(201, 168, 76, 0.06);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 20px;
    }

    .overview-card h4 {
        font-size: 14px;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .overview-card h4 i {
        color: var(--accent);
    }

    .overview-card p {
        font-size: 13px;
        color: var(--text-muted);
        line-height: 1.6;
        margin-bottom: 12px;
    }

    .overview-card h5 {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-muted);
        margin-bottom: 6px;
    }

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

    .overview-card li {
        color: var(--primary);
    }

    .overview-card li::marker,
    .level-statements li::marker,
    .req-list li::marker {
        color: var(--accent);
    }

    /* Search + Filters */
    .search-box {
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 9px 14px;
        margin-bottom: 16px;
        background: var(--card);
    }

    .search-box i {
        color: var(--text-muted);
        font-size: 13px;
    }

    .search-box input {
        border: none;
        outline: none;
        font-size: 13px;
        font-family: inherit;
        flex: 1;
        background: transparent;
        color: var(--primary);
    }

    .type-filter-pills {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
    }

    .filter-pill {
        border: 1px solid var(--border);
        background: var(--card);
        border-radius: 100px;
        padding: 5px 16px;
        font-size: 12px;
        color: var(--text-muted);
        cursor: pointer;
        font-family: inherit;
        transition: var(--transition);
    }

    .filter-pill.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .filter-pill:hover:not(.active) {
        border-color: var(--accent-light);
    }

    /* Loading Spinner */
    .spinner-wrap {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-muted);
    }

    .spinner {
        width: 30px;
        height: 30px;
        border: 3px solid var(--border);
        border-top-color: var(--accent);
        border-radius: 50%;
        margin: 0 auto 12px;
        animation: biicf-spin 0.7s linear infinite;
    }

    @keyframes biicf-spin {
        to { transform: rotate(360deg); }
    }

    /* Sub-sector Cards */
    .sub-sector-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .sub-sector-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--card);
        cursor: pointer;
        transition: var(--transition);
        font-family: inherit;
        width: 100%;
        text-align: left;
    }

    .sub-sector-card:hover {
        border-color: var(--accent-light);
        box-shadow: var(--shadow);
        transform: translateY(-2px);
    }

    .sub-sector-card .name {
        font-weight: 500;
        font-size: 14px;
        color: var(--primary);
    }

    .sub-sector-card .count {
        font-size: 12px;
        color: var(--text-muted);
    }

    /* Job Role List */
    .role-list {
        display: grid;
        gap: 8px;
    }

    .role-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--card);
        cursor: pointer;
        transition: var(--transition);
        font-family: inherit;
        width: 100%;
        text-align: left;
        gap: 12px;
    }

    .role-item:hover {
        border-color: var(--accent-light);
        box-shadow: var(--shadow);
    }

    .role-item .title {
        font-weight: 500;
        font-size: 14px;
        color: var(--primary);
    }

    .role-item .badges {
        display: flex;
        gap: 6px;
        flex-shrink: 0;
    }

    .role-item .level {
        font-size: 12px;
        color: var(--text-muted);
        background: var(--bg);
        padding: 2px 12px;
        border-radius: 100px;
        white-space: nowrap;
    }

    /* Career Path */
    .career-path {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        padding: 16px 0;
    }

    .career-path .node {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
    }

    .career-path .node.current {
        background: var(--primary);
        color: white;
    }

    .career-path .node.prev {
        background: var(--bg);
        color: var(--text-muted);
    }

    .career-path .node.next {
        background: rgba(201, 168, 76, 0.12);
        color: var(--accent-dark);
    }

    .career-path .arrow {
        color: var(--text-muted);
        font-size: 14px;
    }

    /* Competencies */
    .comp-group {
        margin-bottom: 20px;
    }

    .comp-group h4 {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 8px;
    }

    .comp-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: 6px;
        margin-bottom: 6px;
        font-size: 13px;
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

    .prof-level .level {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
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

    /* Where the student stands against a tier. */
    .match-banner {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 12px;
        border: 1px solid var(--border);
    }

    .match-banner.met {
        background: rgba(45, 143, 92, 0.1);
        border-color: rgba(45, 143, 92, 0.35);
        color: var(--success);
    }

    .match-banner.not_met {
        background: rgba(192, 57, 43, 0.07);
        border-color: rgba(192, 57, 43, 0.3);
        color: var(--danger);
    }

    .match-banner.undetermined {
        background: var(--bg);
        color: var(--text-muted);
    }

    .match-detail {
        font-weight: 400;
        color: var(--text-muted);
    }

    .req-text.met {
        border-left-color: var(--success);
    }

    .req-verdict {
        font-size: 10px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 100px;
    }

    .req-verdict.met {
        background: rgba(45, 143, 92, 0.12);
        color: var(--success);
    }

    .req-verdict.soon {
        background: rgba(201, 168, 76, 0.18);
        color: #8a6420;
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

    /* Entry Requirements */
    .req-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid var(--border);
        font-size: 13px;
    }

    .req-item:last-child {
        border-bottom: none;
    }

    .req-item .label {
        color: var(--text-muted);
    }

    .req-item .value {
        font-weight: 500;
    }

    /* Training */
    .training-item {
        padding: 12px 16px;
        border: 1px solid var(--border);
        border-radius: 8px;
        margin-bottom: 8px;
    }

    .training-item .name {
        font-weight: 500;
        font-size: 14px;
        color: var(--primary);
    }

    .training-item .provider {
        font-size: 12px;
        color: var(--text-muted);
    }

    /* Proficiency Levels */
    .prof-level {
        padding: 14px 18px;
        border: 1px solid var(--border);
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .prof-level .level {
        font-weight: 600;
        font-size: 15px;
        color: var(--primary);
    }

    .prof-level .desc {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 4px;
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

    /* Compare Button */
    .compare-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 8px;
        border: none;
        background: var(--accent);
        color: var(--primary-dark);
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: var(--transition);
        font-family: inherit;
    }

    .compare-btn:hover {
        background: var(--accent-light);
        transform: translateY(-2px);
    }

    .compare-btn:disabled {
        opacity: 0.7;
        cursor: default;
        transform: none;
    }

    .compare-result {
        margin-top: 16px;
        padding: 16px 20px;
        background: rgba(26, 58, 92, 0.04);
        border-radius: 8px;
        border: 1px solid var(--border);
    }

    .compare-result .summary {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .compare-result .summary .stat {
        text-align: center;
    }

    .compare-result .summary .stat .number {
        font-family: 'Playfair Display', serif;
        font-size: 24px;
        font-weight: 700;
        color: var(--primary);
    }

    .compare-result .summary .stat .label {
        font-size: 12px;
        color: var(--text-muted);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 40px;
        color: var(--border);
        margin-bottom: 12px;
    }

    .empty-state h4 {
        font-size: 16px;
        color: var(--primary);
        margin-bottom: 4px;
    }

    .empty-state p {
        font-size: 13px;
    }

    .selected-filter {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(26, 58, 92, 0.06);
        padding: 4px 12px;
        border-radius: 100px;
        font-size: 12px;
        color: var(--primary);
        margin-bottom: 12px;
    }

    .selected-filter .remove {
        cursor: pointer;
        color: var(--text-muted);
    }

    .selected-filter .remove:hover {
        color: var(--danger);
    }

    @media (max-width: 768px) {
        .biicf-grid {
            grid-template-columns: 1fr;
        }
        .biicf-sidebar {
            position: static;
        }
        .sub-sector-grid {
            grid-template-columns: 1fr;
        }
        .compare-result .summary {
            flex-direction: column;
            gap: 8px;
        }
        .career-path {
            flex-direction: column;
            align-items: stretch;
            gap: 4px;
        }
        .career-path .arrow {
            transform: rotate(90deg);
            text-align: center;
        }
        .breadcrumb-bar {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<div class="biicf-page" x-data="biicfExplorer()" x-init="init()">
    <div class="container">

        <div class="biicf-header">
            <h1>BIICF <span>Explorer</span></h1>
            <p class="subtitle">Browse the Brunei ICT Industry Competency Framework — sub-sectors, job roles, career paths, competencies and training.</p>
            <span class="badge"><i class="fas fa-certificate"></i> Framework aligned with AITI</span>
        </div>

        <div class="biicf-grid">

            <!-- Sidebar -->
            <aside class="biicf-sidebar">
                <div class="nav-group">
                    <button class="nav-item" :class="{ active: activeSection === 'sub-sectors' }" @click="activeSection = 'sub-sectors'">
                        <i class="fas fa-layer-group"></i> ICT Sub-sectors
                        <span class="count">{{ $subSectors->count() }}</span>
                    </button>
                    <button class="nav-item" :class="{ active: activeSection === 'job-roles' }" @click="activeSection = 'job-roles'">
                        <i class="fas fa-briefcase"></i> Job Roles
                        <span class="count">{{ $jobRoleCount ?? 0 }}</span>
                    </button>
                    <button class="nav-item" :class="{ active: activeSection === 'career-paths' }" @click="activeSection = 'career-paths'">
                        <i class="fas fa-route"></i> Career Paths
                    </button>
                    <button class="nav-item" :class="{ active: activeSection === 'competencies' }" @click="activeSection = 'competencies'">
                        <i class="fas fa-tools"></i> Competencies
                        <span class="count">{{ $competencyCount ?? 0 }}</span>
                    </button>
                    <button class="nav-item" :class="{ active: activeSection === 'proficiency-levels' }" @click="activeSection = 'proficiency-levels'">
                        <i class="fas fa-level-up-alt"></i> Proficiency Levels
                        <span class="count">{{ $proficiencyLevels->count() }}</span>
                    </button>
                    <button class="nav-item" :class="{ active: activeSection === 'entry-requirements' }" @click="activeSection = 'entry-requirements'">
                        <i class="fas fa-door-open"></i> Entry Requirements
                    </button>
                    <button class="nav-item" :class="{ active: activeSection === 'training' }" @click="activeSection = 'training'">
                        <i class="fas fa-graduation-cap"></i> Training & Certifications
                        <span class="count">{{ $trainingCount ?? 0 }}</span>
                    </button>
                </div>
            </aside>

            <!-- Content -->
            <main class="biicf-content">

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
                <section x-show="activeSection === 'sub-sectors'" x-cloak>
                    <div class="section-title"><i class="fas fa-layer-group"></i> ICT Sub-sectors</div>
                    <div class="sub-sector-grid">
                        @foreach ($subSectors as $sector)
                            <button class="sub-sector-card" @click="selectSubSector('{{ $sector->slug }}', '{{ $sector->name }}')">
                                <span class="name">{{ $sector->name }}</span>
                                <span class="count">{{ $sector->job_roles_count }} roles</span>
                            </button>
                        @endforeach
                    </div>
                </section>

                <!-- Job Roles -->
                <section x-show="activeSection === 'job-roles'" x-cloak>
                    <div class="section-title"><i class="fas fa-briefcase"></i> Job Roles</div>

                    <template x-if="selectedSubSectorName">
                        <div class="selected-filter">
                            <i class="fas fa-filter"></i>
                            <span x-text="selectedSubSectorName"></span>
                            <span class="remove" @click="clearSubSectorFilter()">×</span>
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

                    <div class="empty-state" x-show="!loadingRoles && roles.length === 0" style="padding:20px;">
                        <i class="fas fa-hand-point-left"></i>
                        <h4>No job roles loaded</h4>
                        <p>Try selecting a different sub-sector, or clear the filter to browse all {{ $jobRoleCount ?? 0 }} job roles.</p>
                    </div>

                    <div class="role-list" x-show="!loadingRoles && roles.length > 0">
                        <template x-for="role in filteredRoles" :key="role.id">
                            <button class="role-item" @click="selectJobRole(role.slug)">
                                <span class="title" x-text="role.title"></span>
                                <span class="badges">
                                    <span class="level" x-show="!selectedSubSectorSlug && role.sub_sector" x-text="role.sub_sector?.name"></span>
                                    <span class="level">Level <span x-text="role.career_path_level"></span></span>
                                </span>
                            </button>
                        </template>
                        <div x-show="filteredRoles.length === 0" class="empty-state" style="padding:20px;">
                            <i class="fas fa-search"></i>
                            <h4>No roles match "<span x-text="roleSearch"></span>"</h4>
                            <p>Try a different search term.</p>
                        </div>
                    </div>
                </section>

                <!-- Career Paths -->
                <section x-show="activeSection === 'career-paths'" x-cloak>
                    <div class="section-title"><i class="fas fa-route"></i> Career Path</div>

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

                            <div class="career-path">
                                <template x-for="prev in (selectedRole.progresses_from || [])" :key="prev.id">
                                    <span class="node prev" x-text="prev.title"></span>
                                </template>
                                <span class="arrow" x-show="(selectedRole.progresses_from || []).length">→</span>
                                <span class="node current" x-text="selectedRole.title"></span>
                                <span class="arrow" x-show="(selectedRole.progresses_to || []).length">→</span>
                                <template x-for="next in (selectedRole.progresses_to || [])" :key="next.id">
                                    <span class="node next" x-text="next.title"></span>
                                </template>
                            </div>

                            <div x-show="!(selectedRole.progresses_from || []).length && !(selectedRole.progresses_to || []).length" class="empty-state" style="padding:20px;">
                                <i class="fas fa-info-circle"></i>
                                <h4>No career path data</h4>
                                <p>This role doesn't have defined progression paths yet.</p>
                            </div>
                        </div>
                    </template>

                    <div x-show="!selectedRole && !loadingRoleDetail" class="empty-state" style="padding:40px;">
                        <i class="fas fa-hand-point-up"></i>
                        <h4>Select a job role first</h4>
                        <p>Go to "Job Roles" and pick one to see its career progression.</p>
                    </div>
                </section>

                <!-- Competencies -->
                <section x-show="activeSection === 'competencies'" x-cloak>
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
                        <div class="section-title" style="margin-bottom:0;"><i class="fas fa-tools"></i> Competencies</div>
                        <button class="compare-btn" x-show="selectedRole" :disabled="loadingComparison" @click="compareToMe(selectedRole.slug)">
                            <span x-show="!loadingComparison"><i class="fas fa-chart-bar"></i> Compare to my profile</span>
                            <span x-show="loadingComparison"><i class="fas fa-spinner fa-spin"></i> Comparing…</span>
                        </button>
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

                    <!-- Comparison Result -->
                    <div class="compare-result" x-show="comparison" x-cloak>
                        <div class="summary">
                            <div class="stat">
                                <div class="number" x-text="comparison?.summary?.met || 0"></div>
                                <div class="label">Met</div>
                            </div>
                            <div class="stat">
                                <div class="number" x-text="comparison?.summary?.total || 0"></div>
                                <div class="label">Required</div>
                            </div>
                            <div class="stat">
                                <div class="number" x-text="comparison?.summary?.missing || 0"></div>
                                <div class="label">Missing</div>
                            </div>
                        </div>
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
                                    {{--
                                        Opens the competency in
                                        Proficiency Levels, where
                                        the levels and what they
                                        mean already live, rather
                                        than unfolding a second
                                        copy of them here.
                                    --}}
                                    <template x-for="comp in filteredRoleCompetencies(type)" :key="comp.id">
                                        <div class="comp-item is-clickable" @click="openCompetency(comp.id, comp.level)">
                                            <span class="name" x-text="comp.name"></span>
                                            <div>
                                                <span class="status" :class="statusClass(comp.id)" x-text="statusLabel(comp.id)"></span>
                                                <span class="status core" x-show="comp.is_core">Core</span>
                                                <span class="status level" x-show="comp.level"
                                                      x-text="comp.level ? comp.level.number + ' · ' + comp.level.name : ''"></span>
                                                <i class="fas fa-angle-right comp-go"></i>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <div class="empty-state" x-show="compSearch && filteredRoleCompetencies('technical').length === 0 && filteredRoleCompetencies('soft_skill').length === 0" style="padding:20px;">
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
                                    <template x-for="comp in filteredGlossary(type)" :key="comp.id">
                                        <div class="comp-item is-clickable" @click="openCompetency(comp.id, null)">
                                            <span class="name" x-text="comp.name"></span>
                                            <i class="fas fa-angle-right comp-go"></i>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <div class="empty-state" x-show="filteredGlossary('technical').length === 0 && filteredGlossary('soft_skill').length === 0" style="padding:30px;">
                                <i class="fas fa-search"></i>
                                <h4>No competencies found</h4>
                                <p>Try a different search term or filter.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Proficiency Levels -->
                <section x-show="activeSection === 'proficiency-levels'" x-cloak>
                    <div class="section-title"><i class="fas fa-level-up-alt"></i> Proficiency Levels</div>

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
                                        <div class="prof-scale-heading">
                                            <h4 x-text="set.scope ? set.scope : 'Proficiency levels'"></h4>
                                            <span class="scale-note" x-text="set.levels.length + ' levels'"></span>
                                        </div>

                                        <template x-for="level in set.levels" :key="level.number">
                                            <div class="prof-level"
                                                 :class="{ required: requiredLevelNumber === level.number, absent: !level.described }">
                                                <div class="level">
                                                    <span x-text="level.number + '. ' + level.name"></span>
                                                    <span class="required-flag" x-show="requiredLevelNumber === level.number">
                                                        Required for <span x-text="selectedRole?.title"></span>
                                                    </span>
                                                </div>

                                                <div class="desc" x-text="level.summary"></div>

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
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div x-show="!competencyDetail && !loadingCompetency">

                    {{--
                        Two scales, not one. Technical skills are
                        rated over five levels and soft skills
                        over four, so Advanced on a soft skill is
                        not the same rung as level 3 on a
                        technical one.
                    --}}
                    @foreach ($proficiencyScales ?? [] as $scaleName => $levels)
                        @continue($levels->isEmpty())

                        <div class="prof-scale">
                            <div class="prof-scale-heading">
                                <h4>{{ $scaleName }}</h4>
                                <span class="scale-note">
                                    {{ $levels->count() }} levels
                                </span>
                            </div>

                            @foreach ($levels as $level)
                                <div class="prof-level">
                                    <div class="level">{{ $level->level_number }}. {{ $level->name }}</div>
                                    <div class="desc">{{ $level->description }}</div>
                                </div>
                            @endforeach
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
                <section x-show="activeSection === 'entry-requirements'" x-cloak>
                    <div class="section-title"><i class="fas fa-door-open"></i> Entry Requirements</div>

                    <div class="spinner-wrap" x-show="loadingRoleDetail" x-cloak>
                        <div class="spinner"></div>
                        <p>Loading role details…</p>
                    </div>

                    {{--
                        Shown as the book writes it. A requirement
                        often names two ways in, each with its own
                        qualification level and years of
                        experience, so reducing it to a single
                        level and field of study would have to
                        pick one and drop the other.
                    --}}
                    <template x-if="selectedRole && !loadingRoleDetail">
                        <div>
                            <template x-for="tier in roleTiers" :key="tier.id">
                                <div class="req-tier" x-show="tier.entry_requirements.length">
                                    <h4 x-show="roleTiers.length > 1" x-text="tier.label"></h4>

                                    {{--
                                        Where the student stands against
                                        this tier. Three answers, never
                                        two: the framework asks for
                                        things the app holds no record
                                        of, and a verdict on those would
                                        be invented.
                                    --}}
                                    <div class="match-banner"
                                         :class="tier.qualification_match.outcome"
                                         x-show="tier.qualification_match">
                                        <i class="fas"
                                           :class="{
                                               'fa-check-circle': tier.qualification_match.outcome === 'met',
                                               'fa-circle-half-stroke': tier.qualification_match.outcome === 'undetermined',
                                               'fa-circle-exclamation': tier.qualification_match.outcome === 'not_met'
                                           }"></i>

                                        <span x-show="tier.qualification_match.outcome === 'met'">
                                            Your qualifications meet this tier.
                                        </span>
                                        <span x-show="tier.qualification_match.outcome === 'not_met'">
                                            Your qualifications do not reach this tier yet.
                                        </span>
                                        <span x-show="tier.qualification_match.outcome === 'undetermined'">
                                            This cannot be checked against your profile yet.
                                        </span>

                                        <span class="match-detail" x-show="tier.qualification_match.held_level">
                                            You hold BDQF Level <span x-text="tier.qualification_match.held_level"></span>.
                                        </span>
                                        <span class="match-detail" x-show="!tier.qualification_match.held_level && tier.qualification_match.studying_level">
                                            You are studying towards BDQF Level
                                            <span x-text="tier.qualification_match.studying_level"></span>.
                                        </span>
                                        <span class="match-detail" x-show="!tier.qualification_match.held_level && !tier.qualification_match.studying_level">
                                            Add a qualification to your profile to see how you compare.
                                        </span>
                                    </div>

                                    {{--
                                        The book usually gives two ways
                                        in, and meeting either one is
                                        enough. Numbered only when there
                                        is a choice to make.
                                    --}}
                                    <template x-for="(req, index) in tier.entry_requirements" :key="req.alternative_number">
                                        <div>
                                            <div class="req-or" x-show="index > 0">or</div>

                                            @php
                                                /*
                                                 * The alternative's own verdict sits
                                                 * beside it, matched up by number.
                                                 */
                                            @endphp
                                            <div class="req-text"
                                                 :class="matchFor(tier, req)?.outcome">
                                                <div class="req-head" x-show="req.bdqf_level">
                                                    <span class="req-level" x-text="'BDQF Level ' + req.bdqf_level"></span>
                                                    <span class="req-field" x-text="req.field_of_study"></span>

                                                    <span class="req-verdict met"
                                                          x-show="matchFor(tier, req)?.outcome === 'met'">
                                                        You meet this
                                                    </span>
                                                    <span class="req-verdict soon"
                                                          x-show="matchFor(tier, req)?.met_on_finishing">
                                                        You will on finishing
                                                    </span>
                                                </div>

                                                <p x-text="req.text"></p>

                                                <div class="req-conditions" x-show="req.conditions.length">
                                                    <i class="fas fa-plus-circle"></i>
                                                    Also asks for
                                                    <span x-text="req.conditions.join(' and ')"></span>,
                                                    which the framework states but your profile does not record.
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <div x-show="!roleTiers.some(t => t.entry_requirements.length)"
                                 class="empty-state" style="padding:30px;">
                                <i class="fas fa-info-circle"></i>
                                <h4>No entry requirement published</h4>
                                <p>The framework does not state one for this role.</p>
                            </div>
                        </div>
                    </template>

                    <div x-show="!selectedRole && !loadingRoleDetail" class="empty-state" style="padding:40px;">
                        <i class="fas fa-hand-point-up"></i>
                        <h4>Select a job role first</h4>
                        <p>Go to "Job Roles" to see entry requirements for a specific role.</p>
                    </div>
                </section>

                <!-- Training -->
                <section x-show="activeSection === 'training'" x-cloak>
                    <div class="section-title"><i class="fas fa-graduation-cap"></i> Training & Certifications</div>

                    <div class="spinner-wrap" x-show="loadingRoleDetail" x-cloak>
                        <div class="spinner"></div>
                        <p>Loading role details…</p>
                    </div>

                    <template x-if="selectedRole && !loadingRoleDetail">
                        <div>
                            <template x-for="t in (selectedRole.trainings || [])" :key="t.id">
                                <div class="training-item">
                                    <div class="name" x-text="t.name"></div>
                                    <div class="provider" x-text="[t.provider, t.certification_body].filter(Boolean).join(' · ')"></div>
                                </div>
                            </template>

                            <div x-show="!(selectedRole.trainings || []).length" class="empty-state" style="padding:20px;">
                                <i class="fas fa-info-circle"></i>
                                <h4>No training data</h4>
                                <p>This role doesn't have recommended training yet.</p>
                            </div>
                        </div>
                    </template>

                    <div x-show="!selectedRole && !loadingRoleDetail" class="empty-state" style="padding:40px;">
                        <i class="fas fa-hand-point-up"></i>
                        <h4>Select a job role first</h4>
                        <p>Go to "Job Roles" to see recommended training and certifications for a specific role.</p>
                    </div>
                </section>

            </main>

        </div>

    </div>
</div>

<script>
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

        init() {
            // Auto-load data the first time a tab that needs it becomes active.
            this.$watch('activeSection', (section) => {
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

        /*
         * How the student stands against one way in, matched up
         * by the number the book's alternatives are kept in.
         */
        matchFor(tier, req) {
            return (tier.qualification_match?.alternatives || [])
                .find(a => a.alternative_number === req.alternative_number) || null;
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
            if (!entry.student_has_skill) return 'Not logged';
            return entry.meets_requirement ? '✓ Met' : `You: ${entry.student_level}`;
        },

        statusClass(competencyId) {
            const entry = this.statusEntry(competencyId);
            if (!entry) return '';
            if (!entry.student_has_skill) return 'not-logged';
            return entry.meets_requirement ? 'met' : 'missing';
        },
    };
}
</script>

@endsection