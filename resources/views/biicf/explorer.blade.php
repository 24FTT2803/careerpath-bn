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

    .role-item .level {
        font-size: 12px;
        color: var(--text-muted);
        background: var(--bg);
        padding: 2px 12px;
        border-radius: 100px;
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
                            <span class="remove" @click="selectedSubSectorSlug = null; selectedSubSectorName = null; loadRoles()">×</span>
                        </div>
                    </template>

                    <div class="empty-state" x-show="!selectedSubSectorSlug && roles.length === 0" style="padding:20px;">
                        <i class="fas fa-hand-point-left"></i>
                        <h4>Select a sub-sector first</h4>
                        <p>Or browse all {{ $jobRoleCount ?? 0 }} job roles below.</p>
                    </div>

                    <div class="role-list">
                        <template x-for="role in roles" :key="role.id">
                            <button class="role-item" @click="selectJobRole(role.slug)">
                                <span class="title" x-text="role.title"></span>
                                <span class="level">Level <span x-text="role.career_path_level"></span></span>
                            </button>
                        </template>
                        <div x-show="selectedSubSectorSlug && roles.length === 0" class="empty-state" style="padding:20px;">
                            <i class="fas fa-search"></i>
                            <h4>No roles found</h4>
                            <p>Try selecting a different sub-sector.</p>
                        </div>
                    </div>
                </section>

                <!-- Career Paths -->
                <section x-show="activeSection === 'career-paths'" x-cloak>
                    <div class="section-title"><i class="fas fa-route"></i> Career Path</div>

                    <template x-if="selectedRole">
                        <div>
                            <div class="selected-filter">
                                <i class="fas fa-user-tie"></i>
                                <span x-text="selectedRole.title"></span>
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

                    <div x-show="!selectedRole" class="empty-state" style="padding:40px;">
                        <i class="fas fa-hand-point-up"></i>
                        <h4>Select a job role first</h4>
                        <p>Go to "Job Roles" and pick one to see its career progression.</p>
                    </div>
                </section>

                <!-- Competencies -->
                <section x-show="activeSection === 'competencies'" x-cloak>
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
                        <div class="section-title" style="margin-bottom:0;"><i class="fas fa-tools"></i> Competencies</div>
                        <button class="compare-btn" x-show="selectedRole" @click="compareToMe(selectedRole.slug)">
                            <i class="fas fa-chart-bar"></i> Compare to my profile
                        </button>
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

                    <template x-if="selectedRole">
                        <div>
                            <template x-for="type in ['technical', 'soft_skill']" :key="type">
                                <div class="comp-group" x-show="(selectedRole.competencies || []).filter(c => c.type === type).length">
                                    <h4 x-text="type === 'technical' ? 'Technical Competencies' : 'Soft Skill Competencies'"></h4>
                                    <template x-for="comp in (selectedRole.competencies || []).filter(c => c.type === type)" :key="comp.id">
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
                        </div>
                    </template>

                    <div x-show="!selectedRole" class="empty-state" style="padding:40px;">
                        <i class="fas fa-hand-point-up"></i>
                        <h4>Select a job role first</h4>
                        <p>Go to "Job Roles" to see required competencies, or browse the full glossary of {{ $competencyCount ?? 0 }} competencies.</p>
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

                    <template x-if="selectedRole?.entry_requirement">
                        <div>
                            <div class="selected-filter">
                                <i class="fas fa-user-tie"></i>
                                <span x-text="selectedRole.title"></span>
                            </div>
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

                    <div x-show="!selectedRole?.entry_requirement" class="empty-state" style="padding:40px;">
                        <i class="fas fa-hand-point-up"></i>
                        <h4>Select a job role first</h4>
                        <p>Go to "Job Roles" to see entry requirements for a specific role.</p>
                    </div>
                </section>

                <!-- Training -->
                <section x-show="activeSection === 'training'" x-cloak>
                    <div class="section-title"><i class="fas fa-graduation-cap"></i> Training & Certifications</div>

                    <template x-if="selectedRole">
                        <div>
                            <div class="selected-filter">
                                <i class="fas fa-user-tie"></i>
                                <span x-text="selectedRole.title"></span>
                            </div>

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

                    <div x-show="!selectedRole" class="empty-state" style="padding:40px;">
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
        selectedRole: null,
        comparison: null,

        init() {
            // no-op - sections load lazily
        },

        selectSubSector(slug, name) {
            this.selectedSubSectorSlug = slug;
            this.selectedSubSectorName = name;
            this.activeSection = 'job-roles';
            this.loadRoles();
        },

        async loadRoles() {
            if (!this.selectedSubSectorSlug) {
                this.roles = [];
                return;
            }
            const res = await fetch(`/student/biicf-explorer/sub-sectors/${this.selectedSubSectorSlug}/roles`);
            this.roles = await res.json();
        },

        async selectJobRole(slug) {
            const res = await fetch(`/student/biicf-explorer/job-roles/${slug}`);
            const data = await res.json();
            this.selectedRole = data.job_role;
            this.comparison = null;
            this.activeSection = 'career-paths';
        },

        async compareToMe(slug) {
            const res = await fetch(`/student/biicf-explorer/job-roles/${slug}/compare`);
            this.comparison = await res.json();
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