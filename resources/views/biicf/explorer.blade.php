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

    .overview-card ul {
        margin: 0;
        padding-left: 18px;
    }

    .overview-card li {
        font-size: 13px;
        color: var(--primary);
        margin-bottom: 4px;
        line-height: 1.5;
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
                            <div class="overview-card" x-show="selectedRole.job_description || selectedRole.critical_work_function">
                                <h4><i class="fas fa-info-circle"></i> Role Overview</h4>
                                <p x-show="selectedRole.job_description" x-text="selectedRole.job_description"></p>
                                <template x-if="selectedRole.critical_work_function">
                                    <div>
                                        <h5>Key Responsibilities</h5>
                                        <ul>
                                            <template x-for="(line, idx) in (selectedRole.critical_work_function || '').split('\n').filter(Boolean)" :key="idx">
                                                <li x-text="line"></li>
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
                            <template x-for="type in ['technical', 'soft_skill']" :key="type">
                                <div class="comp-group" x-show="filteredRoleCompetencies(type).length">
                                    <h4 x-text="type === 'technical' ? 'Technical Competencies' : 'Soft Skill Competencies'"></h4>
                                    <template x-for="comp in filteredRoleCompetencies(type)" :key="comp.id">
                                        <div class="comp-item">
                                            <span class="name" x-text="comp.name"></span>
                                            <div>
                                                <span class="status" :class="statusClass(comp.id)" x-text="statusLabel(comp.id)"></span>
                                                <span class="status core" x-show="comp.pivot?.is_core">Core</span>
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
                                        <div class="comp-item">
                                            <span class="name" x-text="comp.name"></span>
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
                    @foreach ($proficiencyLevels as $level)
                        <div class="prof-level">
                            <div class="level">{{ $level->level_number }}. {{ $level->name }}</div>
                            <div class="desc">{{ $level->description }}</div>
                        </div>
                    @endforeach
                </section>

                <!-- Entry Requirements -->
                <section x-show="activeSection === 'entry-requirements'" x-cloak>
                    <div class="section-title"><i class="fas fa-door-open"></i> Entry Requirements</div>

                    <div class="spinner-wrap" x-show="loadingRoleDetail" x-cloak>
                        <div class="spinner"></div>
                        <p>Loading role details…</p>
                    </div>

                    <template x-if="selectedRole?.entry_requirement && !loadingRoleDetail">
                        <div>
                            <div class="req-item">
                                <span class="label">Qualification Level</span>
                                <span class="value" x-text="selectedRole.entry_requirement.bdqf_level || 'Not specified'"></span>
                            </div>
                            <div class="req-item">
                                <span class="label">Field of Study</span>
                                <span class="value" x-text="selectedRole.entry_requirement.field_of_study || 'Not specified'"></span>
                            </div>
                            <div class="req-item" x-show="selectedRole.entry_requirement.years_experience">
                                <span class="label">Years of Experience</span>
                                <span class="value" x-text="selectedRole.entry_requirement.years_experience"></span>
                            </div>
                            <div class="req-item" x-show="selectedRole.entry_requirement.alternative_pathway">
                                <span class="label">Alternative Pathway</span>
                                <span class="value" x-text="selectedRole.entry_requirement.alternative_pathway"></span>
                            </div>
                        </div>
                    </template>

                    <div x-show="!selectedRole?.entry_requirement && !loadingRoleDetail" class="empty-state" style="padding:40px;">
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
            this.loadingRoleDetail = true;
            this.activeSection = 'career-paths';
            try {
                const res = await fetch(`/student/biicf-explorer/job-roles/${slug}`);
                const data = await res.json();
                this.selectedRole = data.job_role;
            } finally {
                this.loadingRoleDetail = false;
            }
        },

        clearRole() {
            this.selectedRole = null;
            this.comparison = null;
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

        filteredRoleCompetencies(type) {
            const list = (this.selectedRole?.competencies || []).filter(c => c.type === type);
            if (!this.compSearch.trim()) return list;
            const q = this.compSearch.toLowerCase();
            return list.filter(c => c.name.toLowerCase().includes(q));
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
            return entry.meets_requirement ? '✅ Met' : `You: ${entry.student_level}`;
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