@extends('layouts.app')

@section('title', 'BIICF Explorer')

@section('content')

<div x-data="biicfExplorer()" x-init="init()" class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">BIICF Explorer</h1>
        <p class="text-gray-600 mt-1">Browse the Brunei ICT Industry Competency Framework — sub-sectors, job roles, career paths, competencies and training.</p>
    </div>

    <div class="grid grid-cols-12 gap-6">

        {{-- Left nav: the 7 sections --}}
        <aside class="col-span-12 md:col-span-3">
            <nav class="space-y-1 sticky top-4">
                @php
                    $sections = [
                        'sub-sectors' => 'ICT Sub-sectors',
                        'job-roles' => 'Job Roles',
                        'career-paths' => 'Career Paths',
                        'competencies' => 'Competencies',
                        'proficiency-levels' => 'Proficiency Levels',
                        'entry-requirements' => 'Entry Requirements',
                        'training' => 'Training & Certifications',
                    ];
                @endphp
                @foreach ($sections as $key => $label)
                    <button
                        @click="activeSection = '{{ $key }}'"
                        :class="activeSection === '{{ $key }}' ? 'bg-purple-100 text-purple-800 font-semibold' : 'text-gray-600 hover:bg-gray-100'"
                        class="w-full text-left px-3 py-2 rounded-lg text-sm transition"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </aside>

        {{-- Right content --}}
        <main class="col-span-12 md:col-span-9">

            {{-- 1. ICT Sub-sectors --}}
            <section x-show="activeSection === 'sub-sectors'" x-cloak>
                <h2 class="text-lg font-semibold mb-4">ICT Sub-sectors</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    @foreach ($subSectors as $sector)
                        <button
                            @click="selectSubSector('{{ $sector->slug }}', '{{ $sector->name }}')"
                            class="text-left border rounded-xl p-4 hover:border-purple-400 hover:shadow-sm transition"
                        >
                            <div class="font-semibold text-gray-900">{{ $sector->name }}</div>
                            <div class="text-sm text-gray-500 mt-1">{{ $sector->job_roles_count }} job role{{ $sector->job_roles_count === 1 ? '' : 's' }}</div>
                        </button>
                    @endforeach
                </div>
            </section>

            {{-- 2. Job Roles --}}
            <section x-show="activeSection === 'job-roles'" x-cloak>
                <h2 class="text-lg font-semibold mb-4">Job Roles</h2>

                <template x-if="!selectedSubSectorSlug">
                    <p class="text-sm text-gray-500">Pick a sub-sector first, or browse all {{ $jobRoleCount }} job roles below.</p>
                </template>

                <div class="mb-4" x-show="selectedSubSectorName">
                    <span class="inline-flex items-center gap-2 text-sm bg-purple-50 text-purple-700 px-3 py-1 rounded-full">
                        <span x-text="selectedSubSectorName"></span>
                        <button @click="selectedSubSectorSlug = null; selectedSubSectorName = null; loadRoles()" class="text-purple-500 hover:text-purple-800">&times;</button>
                    </span>
                </div>

                <div class="space-y-2">
                    <template x-for="role in roles" :key="role.id">
                        <button
                            @click="selectJobRole(role.slug)"
                            class="w-full text-left border rounded-lg px-4 py-3 hover:border-purple-400 flex items-center justify-between"
                        >
                            <span class="font-medium text-gray-900" x-text="role.title"></span>
                            <span class="text-xs text-gray-400">Level <span x-text="role.career_path_level"></span></span>
                        </button>
                    </template>
                    <p x-show="roles.length === 0" class="text-sm text-gray-400">No job roles loaded yet — select a sub-sector.</p>
                </div>
            </section>

            {{-- 3. Career Paths --}}
            <section x-show="activeSection === 'career-paths'" x-cloak>
                <h2 class="text-lg font-semibold mb-4">Career Path</h2>
                <p class="text-sm text-gray-500 mb-4">Select a job role to see how it connects to other roles in its progression chain.</p>
                <div x-show="selectedRole" class="space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <template x-for="prev in (selectedRole?.progresses_from || [])" :key="prev.id">
                            <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-sm" x-text="prev.title"></span>
                        </template>
                        <span x-show="(selectedRole?.progresses_from || []).length" class="text-gray-400">&rarr;</span>
                        <span class="px-3 py-1 rounded-full bg-purple-600 text-white text-sm font-semibold" x-text="selectedRole?.title"></span>
                        <span x-show="(selectedRole?.progresses_to || []).length" class="text-gray-400">&rarr;</span>
                        <template x-for="next in (selectedRole?.progresses_to || [])" :key="next.id">
                            <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm" x-text="next.title"></span>
                        </template>
                    </div>
                </div>
                <p x-show="!selectedRole" class="text-sm text-gray-400">No role selected yet.</p>
            </section>

            {{-- 4. Competencies --}}
            <section x-show="activeSection === 'competencies'" x-cloak>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Competencies</h2>
                    <button
                        x-show="selectedRole"
                        @click="compareToMe(selectedRole.slug)"
                        class="text-sm bg-purple-600 text-white px-3 py-1.5 rounded-lg hover:bg-purple-700"
                    >
                        <i class="fas fa-chart-bar mr-1"></i> Compare to my profile
                    </button>
                </div>

                {{-- Comparison result banner --}}
                <div x-show="comparison" x-cloak class="mb-5 border rounded-lg p-4 bg-purple-50">
                    <div class="text-sm font-semibold text-purple-900">
                        You meet <span x-text="comparison?.summary?.met"></span> of <span x-text="comparison?.summary?.total"></span> required competencies for this role
                        <span x-show="comparison?.summary?.missing" class="text-purple-700 font-normal">
                            (<span x-text="comparison?.summary?.missing"></span> not yet logged in your profile)
                        </span>
                    </div>
                </div>

                <div x-show="selectedRole">
                    <template x-for="type in ['technical', 'soft_skill']" :key="type">
                        <div class="mb-5" x-show="(selectedRole?.competencies || []).filter(c => c.type === type).length">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2" x-text="type === 'technical' ? 'Technical Competencies' : 'Soft Skill Competencies'"></h3>
                            <ul class="space-y-2">
                                <template x-for="comp in (selectedRole?.competencies || []).filter(c => c.type === type)" :key="comp.id">
                                    <li class="border rounded-lg px-3 py-2 flex items-center justify-between text-sm" :class="statusClass(comp.id)">
                                        <span x-text="comp.name"></span>
                                        <span class="flex items-center gap-2">
                                            <span x-show="statusLabel(comp.id)" class="text-xs font-medium" x-text="statusLabel(comp.id)"></span>
                                            <span class="text-xs text-gray-400" x-text="comp.pivot?.is_core ? 'Core' : 'Supporting'"></span>
                                        </span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>
                </div>
                <p x-show="!selectedRole" class="text-sm text-gray-400 mb-4">Select a job role under "Job Roles" to see its required competencies, or browse the full glossary of {{ $competencyCount }} competencies.</p>
            </section>

            {{-- 5. Proficiency Levels --}}
            <section x-show="activeSection === 'proficiency-levels'" x-cloak>
                <h2 class="text-lg font-semibold mb-4">Proficiency Levels</h2>
                <div class="space-y-3">
                    @foreach ($proficiencyLevels as $level)
                        <div class="border rounded-lg px-4 py-3">
                            <div class="font-semibold text-gray-900">{{ $level->level_number }}. {{ $level->name }}</div>
                            <div class="text-sm text-gray-500 mt-1">{{ $level->description }}</div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- 6. Entry Requirements --}}
            <section x-show="activeSection === 'entry-requirements'" x-cloak>
                <h2 class="text-lg font-semibold mb-4">Entry Requirements</h2>
                <div x-show="selectedRole?.entry_requirement" class="border rounded-lg p-4 space-y-2 text-sm">
                    <div><span class="font-medium text-gray-700">Qualification level:</span> <span x-text="selectedRole?.entry_requirement?.bdqf_level"></span></div>
                    <div><span class="font-medium text-gray-700">Field of study:</span> <span x-text="selectedRole?.entry_requirement?.field_of_study"></span></div>
                    <div x-show="selectedRole?.entry_requirement?.years_experience"><span class="font-medium text-gray-700">Experience:</span> <span x-text="selectedRole?.entry_requirement?.years_experience"></span></div>
                    <div x-show="selectedRole?.entry_requirement?.alternative_pathway"><span class="font-medium text-gray-700">Alternative pathway:</span> <span x-text="selectedRole?.entry_requirement?.alternative_pathway"></span></div>
                </div>
                <p x-show="!selectedRole?.entry_requirement" class="text-sm text-gray-400">Select a job role to see its entry requirements.</p>
            </section>

            {{-- 7. Training & Certifications --}}
            <section x-show="activeSection === 'training'" x-cloak>
                <h2 class="text-lg font-semibold mb-4">Training & Certifications</h2>
                <ul class="space-y-2" x-show="(selectedRole?.trainings || []).length">
                    <template x-for="t in (selectedRole?.trainings || [])" :key="t.id">
                        <li class="border rounded-lg px-4 py-3">
                            <div class="font-medium text-gray-900" x-text="t.name"></div>
                            <div class="text-xs text-gray-500 mt-0.5" x-text="[t.provider, t.certification_body].filter(Boolean).join(' · ')"></div>
                        </li>
                    </template>
                </ul>
                <p x-show="!(selectedRole?.trainings || []).length" class="text-sm text-gray-400">Select a job role to see recommended training and certifications ({{ $trainingCount }} total in the catalogue).</p>
            </section>

        </main>
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
            // no-op; sections load lazily as the user drills in
        },

        selectSubSector(slug, name) {
            this.selectedSubSectorSlug = slug;
            this.selectedSubSectorName = name;
            this.activeSection = 'job-roles';
            this.loadRoles();
        },

        async loadRoles() {
            if (!this.selectedSubSectorSlug) { this.roles = []; return; }
            const res = await fetch(`/student/biicf-explorer/sub-sectors/${this.selectedSubSectorSlug}/roles`);
            this.roles = await res.json();
        },

        async selectJobRole(slug) {
            const res = await fetch(`/student/biicf-explorer/job-roles/${slug}`);
            const data = await res.json();
            this.selectedRole = data.job_role;
            this.comparison = null; // reset comparison when switching roles
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
            if (!entry.student_has_skill) return 'Not yet logged';
            return entry.meets_requirement ? 'Meets requirement' : `You: ${entry.student_level}`;
        },

        statusClass(competencyId) {
            const entry = this.statusEntry(competencyId);
            if (!entry) return '';
            if (!entry.student_has_skill) return 'border-gray-200';
            return entry.meets_requirement ? 'border-green-300 bg-green-50' : 'border-amber-300 bg-amber-50';
        },
    };
}
</script>

@endsection