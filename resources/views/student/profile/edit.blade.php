@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
    .cpbn-dash{
        --ink:#0d1a2b; --ink-dim:#5b6675; --paper:#faf8f2; --card:#ffffff; --line:#e7e2d4;
        --gold:#cf9a3d; --gold-bright:#e9b95a; --gold-wash:#fbf1de;
        --rose:#c65b4e; --rose-wash:#fbeceb; --green:#4c8a68; --green-wash:#e9f3ee;
        --font-display:'Fraunces', Georgia, serif; --font-body:'IBM Plex Sans', ui-sans-serif, system-ui, sans-serif;
        --font-mono:'IBM Plex Mono', ui-monospace, monospace;
        background:var(--paper); color:var(--ink); font-family:var(--font-body);
        margin:-24px -16px 0; padding:32px 20px 64px;
    }
    .cpbn-dash *{box-sizing:border-box}
    .cpbn-dash a{text-decoration:none;color:inherit}
    .cpbn-wrap{max-width:960px;margin-inline:auto}

    .cpbn-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:20px}
    .cpbn-head h1{font-family:var(--font-display);font-weight:600;font-size:24px;letter-spacing:-.01em}
    .cpbn-head p.sub{color:var(--ink-dim);margin-top:4px;font-size:14.5px}
    .cpbn-back{display:flex;align-items:center;gap:6px;font-size:13.5px;color:var(--ink-dim)}
    .cpbn-back:hover{color:var(--ink)}
    .cpbn-back svg{width:14px;height:14px}

    .cpbn-progress-card{background:var(--card);border:1px solid var(--line);border-radius:6px;padding:18px 20px;margin-bottom:24px}
    .cpbn-progress-top{display:flex;justify-content:space-between;align-items:center;font-size:13.5px}
    .cpbn-progress-top span:first-child{font-weight:500}
    .cpbn-progress-top span:last-child{font-family:var(--font-mono);color:#8a6420;font-weight:600}
    .cpbn-bar{width:100%;background:#eee9db;border-radius:100px;height:8px;margin-top:9px;overflow:hidden}
    .cpbn-bar-fill{height:100%;background:var(--gold);border-radius:100px}
    .cpbn-progress-note{font-size:12px;color:var(--ink-dim);margin-top:8px}

    .cpbn-section{background:var(--card);border:1px solid var(--line);border-radius:6px;padding:24px;margin-bottom:18px}
    .cpbn-section h3{
        font-family:var(--font-display);font-size:17px;font-weight:600;margin-bottom:18px;
        display:flex;align-items:center;gap:10px;
    }
    .cpbn-section h3 svg{width:17px;height:17px;color:var(--gold)}
    .cpbn-section p.desc{font-size:13px;color:var(--ink-dim);margin-top:-10px;margin-bottom:16px}

    .cpbn-fgrid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .cpbn-field{margin-bottom:0}
    .cpbn-field.full{grid-column:1 / -1}
    .cpbn-field label{display:block;font-size:13px;font-weight:500;color:var(--ink);margin-bottom:6px}
    .cpbn-field label .req{color:var(--rose)}
    .cpbn-field input[type="text"],
    .cpbn-field input[type="email"],
    .cpbn-field input[type="number"],
    .cpbn-field input[type="date"],
    .cpbn-field input[type="url"],
    .cpbn-field input[type="tel"],
    .cpbn-field select,
    .cpbn-field textarea{
        width:100%;padding:10px 13px;border-radius:4px;border:1px solid var(--line);background:#fff;
        font-family:var(--font-body);font-size:14px;color:var(--ink);
    }
    .cpbn-field input:focus,
    .cpbn-field select:focus,
    .cpbn-field textarea:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(207,154,61,0.15)}
    .cpbn-field input:disabled{background:#f2efe6;color:var(--ink-dim)}
    .cpbn-field textarea{resize:vertical;font-family:var(--font-body)}

    .cpbn-checks{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
    .cpbn-check{display:flex;align-items:center;gap:8px;font-size:13.5px}
    .cpbn-check input{accent-color:var(--gold);width:15px;height:15px;flex-shrink:0}
    .cpbn-check-note{font-size:11.5px;color:var(--ink-dim);margin-top:12px}

    .cpbn-submit-row{display:flex;justify-content:flex-end;margin-top:24px}
    .cpbn-btn{display:inline-flex;align-items:center;gap:8px;padding:11px 22px;border-radius:5px;font-size:14.5px;font-weight:500;border:none;cursor:pointer;transition:background .15s}
    .cpbn-btn svg{width:14px;height:14px}
    .cpbn-btn-primary{background:var(--gold);color:var(--ink)}
    .cpbn-btn-primary:hover{background:var(--gold-bright)}
    .cpbn-btn-muted{background:#eee9db;color:var(--ink)}
    .cpbn-btn-muted:hover{background:#e4dfcd}

    /* ============================================
       DYNAMIC FORM STYLES - Projects & Certifications
       ============================================ */
    .cpbn-project-card,
    .cpbn-certification-card {
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 6px;
        padding: 20px;
        margin-bottom: 16px;
        position: relative;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .cpbn-project-card:hover,
    .cpbn-certification-card:hover {
        border-color: var(--gold);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .cpbn-project-card .cpbn-fgrid,
    .cpbn-certification-card .cpbn-fgrid {
        gap: 12px;
    }

    .cpbn-remove-project,
    .cpbn-remove-certification {
        background: var(--rose-wash);
        color: var(--rose);
        border: 1px solid transparent;
        padding: 6px 14px;
        border-radius: 4px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 12px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-family: var(--font-body);
    }

    .cpbn-remove-project:hover,
    .cpbn-remove-certification:hover {
        background: var(--rose);
        color: white;
    }

    .cpbn-add-project,
    .cpbn-add-certification {
        background: var(--gold-wash);
        color: #8a6420;
        border: 1px dashed var(--gold);
        padding: 10px 18px;
        border-radius: 6px;
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

    .cpbn-add-project:hover,
    .cpbn-add-certification:hover {
        background: var(--gold);
        color: var(--ink);
        border-style: solid;
    }

    .cpbn-file-note {
        display: block;
        font-size: 11px;
        color: var(--ink-dim);
        margin-top: 4px;
    }

    .cpbn-file-existing {
        font-size: 12px;
        color: var(--green);
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .cpbn-project-number {
        font-family: var(--font-mono);
        font-size: 11px;
        color: var(--ink-dim);
        background: #eef1f5;
        padding: 2px 10px;
        border-radius: 100px;
        display: inline-block;
        margin-bottom: 12px;
    }

    /* Error messages */
    .err {
        color: var(--rose);
        font-size: 12px;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .err svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }

    @media (max-width:720px){
        .cpbn-fgrid{grid-template-columns:1fr}
        .cpbn-field.full{grid-column:auto}
        .cpbn-checks{grid-template-columns:repeat(2,1fr)}
    }
</style>

<div class="cpbn-dash">
    <div class="cpbn-wrap">

        <div class="cpbn-head">
            <div>
                <h1>Complete Your Profile</h1>
                <p class="sub">Fill in all sections for better career recommendations</p>
            </div>
            <a href="{{ route('student.profile') }}" class="cpbn-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Back
            </a>
        </div>

        <!-- Profile Completion Progress -->
        <div class="cpbn-progress-card">
            <div class="cpbn-progress-top">
                <span>Profile Completion</span>
                <span>{{ $profileCompletion ?? 0 }}%</span>
            </div>
            <div class="cpbn-bar"><div class="cpbn-bar-fill" style="width: {{ $profileCompletion ?? 0 }}%"></div></div>
            <p class="cpbn-progress-note">
                {{ $profileCompletion >= 70 ? '✅ Profile complete — ready for career matching!' : 'Complete all sections for better recommendations' }}
            </p>
        </div>

        <form method="POST" action="{{ route('student.profile.update') }}" enctype="multipart/form-data" id="profile-form">
            @csrf
            @method('PUT')

            <!-- SECTION 1: Personal Information -->
            <div class="cpbn-section">
                <h3>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
                    Personal Information
                </h3>

                <div class="cpbn-fgrid">
                    <div class="cpbn-field">
                        <label>First Name <span class="req">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name', $user->first_name ?? '') }}" required>
                        @error('first_name')<p class="err">{{ $message }}</p>@enderror
                    </div>
                    <div class="cpbn-field">
                        <label>Last Name <span class="req">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name', $user->last_name ?? '') }}" required>
                        @error('last_name')<p class="err">{{ $message }}</p>@enderror
                    </div>
                    <div class="cpbn-field">
                        <label>Email <span class="req">*</span></label>
                        <input type="email" value="{{ $user->email }}" disabled>
                    </div>
                    <div class="cpbn-field">
                        <label>Student ID <span class="req">*</span></label>
                        <input type="text" name="student_id" value="{{ old('student_id', $user->student_id) }}" required>
                        @error('student_id')<p class="err">{{ $message }}</p>@enderror
                    </div>
                    <div class="cpbn-field">
                        <label>Phone Number</label>
                        <input type="tel" 
                               name="phone" 
                               value="{{ old('phone', $user->phone ?? '') }}" 
                               placeholder="+673 123 4567"
                               pattern="[\+\d\s\-\(\)]{7,20}"
                               title="Only digits, +, -, spaces, and parentheses allowed (7-20 characters)"
                               maxlength="20">
                        <small class="cpbn-file-note">Only digits, +, -, spaces, and parentheses allowed (7-20 characters)</small>
                        @error('phone')<p class="err">{{ $message }}</p>@enderror
                    </div>
                    <div class="cpbn-field">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $user->profile->date_of_birth ?? '') }}">
                        @error('date_of_birth')<p class="err">{{ $message }}</p>@enderror
                    </div>
                    <div class="cpbn-field">
                        <label>Nationality</label>
                        <select name="nationality">
                            <option value="">Select your nationality</option>
                            <option value="Brunei Darussalam" {{ old('nationality', $user->profile->nationality ?? '') == 'Brunei Darussalam' ? 'selected' : '' }}>🇧🇳 Brunei Darussalam</option>
                            <option value="Cambodia" {{ old('nationality', $user->profile->nationality ?? '') == 'Cambodia' ? 'selected' : '' }}>🇰🇭 Cambodia</option>
                            <option value="Indonesia" {{ old('nationality', $user->profile->nationality ?? '') == 'Indonesia' ? 'selected' : '' }}>🇮🇩 Indonesia</option>
                            <option value="Laos" {{ old('nationality', $user->profile->nationality ?? '') == 'Laos' ? 'selected' : '' }}>🇱🇦 Laos</option>
                            <option value="Malaysia" {{ old('nationality', $user->profile->nationality ?? '') == 'Malaysia' ? 'selected' : '' }}>🇲🇾 Malaysia</option>
                            <option value="Myanmar" {{ old('nationality', $user->profile->nationality ?? '') == 'Myanmar' ? 'selected' : '' }}>🇲🇲 Myanmar</option>
                            <option value="Philippines" {{ old('nationality', $user->profile->nationality ?? '') == 'Philippines' ? 'selected' : '' }}>🇵🇭 Philippines</option>
                            <option value="Singapore" {{ old('nationality', $user->profile->nationality ?? '') == 'Singapore' ? 'selected' : '' }}>🇸🇬 Singapore</option>
                            <option value="Thailand" {{ old('nationality', $user->profile->nationality ?? '') == 'Thailand' ? 'selected' : '' }}>🇹🇭 Thailand</option>
                            <option value="Timor-Leste" {{ old('nationality', $user->profile->nationality ?? '') == 'Timor-Leste' ? 'selected' : '' }}>🇹🇱 Timor-Leste</option>
                            <option value="Vietnam" {{ old('nationality', $user->profile->nationality ?? '') == 'Vietnam' ? 'selected' : '' }}>🇻🇳 Vietnam</option>
                        </select>
                        @error('nationality')<p class="err">{{ $message }}</p>@enderror
                    </div>
                    <div class="cpbn-field full">
                        <label>Address</label>
                        <textarea name="address" rows="2">{{ old('address', $user->profile->address ?? '') }}</textarea>
                        @error('address')<p class="err">{{ $message }}</p>@enderror
                    </div>
                    <div class="cpbn-field full">
                        <label>Bio / About You</label>
                        <textarea name="bio" rows="3">{{ old('bio', $user->profile->bio ?? '') }}</textarea>
                        @error('bio')<p class="err">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Academic Information -->
            <div class="cpbn-section">
                <h3>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 10v6"/>
                        <path d="M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                    Academic Information
                </h3>
                <div class="cpbn-fgrid">
                    <div class="cpbn-field">
                        <label>Programme <span class="req">*</span></label>
                        <select name="programme" required>
                            <option value="">Select your programme</option>
                            <option value="Diploma in ICT (Application Development)" {{ old('programme', $user->programme) == 'Diploma in ICT (Application Development)' ? 'selected' : '' }}>
                                Diploma in ICT (Application Development) - DADT
                            </option>
                            <option value="Diploma in ICT (Data Analytics)" {{ old('programme', $user->programme) == 'Diploma in ICT (Data Analytics)' ? 'selected' : '' }}>
                                Diploma in ICT (Data Analytics) - DDAT
                            </option>
                            <option value="Diploma in ICT (Cloud Networking)" {{ old('programme', $user->programme) == 'Diploma in ICT (Cloud Networking)' ? 'selected' : '' }}>
                                Diploma in ICT (Cloud Networking) - DCNG
                            </option>
                            <option value="Diploma in Business Information Systems" {{ old('programme', $user->programme) == 'Diploma in Business Information Systems' ? 'selected' : '' }}>
                                Diploma in Business Information Systems - DBIS
                            </option>
                            <option value="Others" {{ old('programme', $user->programme) == 'Others' ? 'selected' : '' }}>
                                Others
                            </option>
                        </select>
                        @error('programme')<p class="err">{{ $message }}</p>@enderror
                    </div>
                    <div class="cpbn-field">
                        <label>CGPA <span class="req">*</span></label>
                        <input type="number" name="cgpa" step="0.01" min="0" max="4"
                               value="{{ old('cgpa', $user->cgpa) }}" required>
                        @error('cgpa')<p class="err">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- SECTION 3: Skills & Competencies -->
            <div class="cpbn-section">
                <h3>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    Skills &amp; Competencies
                </h3>
                <p class="desc">Select your technical skills</p>
                @php
                    $skillOptions = [
                        'Python', 'JavaScript', 'SQL', 'Java', 'PHP',
                        'HTML/CSS', 'React', 'Node.js', 'Git', 'Linux',
                        'Docker', 'AWS', 'C++', 'C#', 'Ruby'
                    ];
                    $savedSkills = $user->competencies->pluck('skill_name')->toArray();
                @endphp
                <div class="cpbn-checks">
                    @foreach($skillOptions as $skill)
                        <label class="cpbn-check">
                            <input type="checkbox" name="skills[]" value="{{ $skill }}"
                                   {{ in_array($skill, $savedSkills) ? 'checked' : '' }}>
                            <span>{{ $skill }}</span>
                        </label>
                    @endforeach
                </div>
                @error('skills')<p class="err">{{ $message }}</p>@enderror
                <p class="cpbn-check-note">Select all that apply. More skills = better career matching.</p>
            </div>

            <!-- SECTION 4: Interests & Preferences -->
            <div class="cpbn-section">
                <h3>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.8 1-1a5.5 5.5 0 0 0 0-7.6z"/></svg>
                    Interests &amp; Preferences
                </h3>
                <p class="desc">What are you interested in?</p>
                @php
                    $interestOptions = [
                        'Problem Solving', 'Teamwork', 'Communication', 'Leadership',
                        'Creativity', 'Analytical Thinking', 'Research', 'Writing',
                        'Public Speaking', 'Programming', 'Data Analysis', 'Networking',
                        'Cybersecurity', 'Cloud Computing', 'Project Management'
                    ];
                    $savedInterests = $user->interests->pluck('interest_name')->toArray();
                @endphp
                <div class="cpbn-checks">
                    @foreach($interestOptions as $interest)
                        <label class="cpbn-check">
                            <input type="checkbox" name="interests[]" value="{{ $interest }}"
                                   {{ in_array($interest, $savedInterests) ? 'checked' : '' }}>
                            <span>{{ $interest }}</span>
                        </label>
                    @endforeach
                </div>
                @error('interests')<p class="err">{{ $message }}</p>@enderror
            </div>

            <!-- ============================================ -->
            <!-- SECTION 5: Projects & Experience - DYNAMIC    -->
            <!-- ============================================ -->
            <div class="cpbn-section">
                <h3>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    Projects &amp; Experience
                </h3>

                <p class="desc">Add your projects and experience</p>

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
                                    'start_date' => $project->start_date?->format('Y-m-d'),
                                    'end_date' => $project->end_date?->format('Y-m-d'),
                                    'achievements' => $project->achievements,
                                ];
                            })
                            ->values()
                            ->all();
                    }

                    // FIX: Check if $projectRows is null or empty before using array_keys
                    $nextProjectIndex = 0;
                    if (!empty($projectRows) && is_array($projectRows)) {
                        $nextProjectIndex = max(array_map('intval', array_keys($projectRows))) + 1;
                    }
                @endphp

                <div id="project-list" data-next-index="{{ $nextProjectIndex }}">
                    @if(empty($projectRows) || !is_array($projectRows) || count($projectRows) === 0)
                        <!-- Show one empty project card if no projects exist -->
                        <div class="cpbn-project-card">
                            <span class="cpbn-project-number">Project #1</span>
                            <div class="cpbn-fgrid">
                                <div class="cpbn-field">
                                    <label>Project Title <span class="req">*</span></label>
                                    <input type="text" name="projects[0][title]" placeholder="e.g. Hobbee Apps" required>
                                </div>
                                <div class="cpbn-field">
                                    <label>Your Role</label>
                                    <input type="text" name="projects[0][role]" placeholder="e.g. Lead Developer">
                                </div>
                                <div class="cpbn-field">
                                    <label>Project URL (Optional)</label>
                                    <input type="url" name="projects[0][project_url]" placeholder="https://github.com/your-project-name">
                                </div>
                                <div class="cpbn-field full">
                                    <label>Description</label>
                                    <textarea name="projects[0][description]" rows="2" placeholder="Brief description of the project"></textarea>
                                </div>
                                <div class="cpbn-field full">
                                    <label>Technologies Used</label>
                                    <input type="text" name="projects[0][technologies_used]" placeholder="e.g. Python, React, MySQL">
                                    <small class="cpbn-file-note">Separate technologies with commas</small>
                                </div>
                                <div class="cpbn-field">
                                    <label>Start Date</label>
                                    <input type="date" name="projects[0][start_date]">
                                </div>
                                <div class="cpbn-field">
                                    <label>End Date</label>
                                    <input type="date" name="projects[0][end_date]">
                                </div>
                                <div class="cpbn-field full">
                                    <label>Achievements</label>
                                    <textarea name="projects[0][achievements]" rows="2" placeholder="What did you accomplish?"></textarea>
                                </div>
                            </div>
                            <button type="button" class="cpbn-remove-project" onclick="removeProject(this)" style="display:none;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;">
                                    <path d="M18 6L6 18M6 6l12 12"/>
                                </svg>
                                Remove Project
                            </button>
                        </div>
                    @else
                        @foreach ($projectRows as $index => $project)
                            @php
                                // FIX: Handle null technologies_used
                                $techArray = $project['technologies_used'] ?? [];
                                if (!is_array($techArray)) {
                                    $techArray = [];
                                }
                                $technologies = implode(', ', $techArray);
                            @endphp

                            <div class="cpbn-project-card">
                                @if (! empty($project['id']))
                                    <input type="hidden" name="projects[{{ $index }}][id]" value="{{ $project['id'] }}">
                                @endif

                                <span class="cpbn-project-number">Project #{{ $loop->iteration }}</span>

                                <div class="cpbn-fgrid">
                                    <div class="cpbn-field">
                                        <label>Project Title <span class="req">*</span></label>
                                        <input type="text" name="projects[{{ $index }}][title]"
                                               value="{{ $project['title'] ?? '' }}"
                                               placeholder="e.g. Hobbee Apps" required>
                                    </div>
                                    <div class="cpbn-field">
                                        <label>Your Role</label>
                                        <input type="text" name="projects[{{ $index }}][role]"
                                               value="{{ $project['role'] ?? '' }}"
                                               placeholder="e.g. Lead Developer">
                                    </div>
                                    <div class="cpbn-field">
                                        <label>Project URL (Optional)</label>
                                        <input type="url" name="projects[{{ $index }}][project_url]"
                                               value="{{ $project['project_url'] ?? '' }}"
                                               placeholder="https://github.com/your-project-name">
                                    </div>
                                    <div class="cpbn-field full">
                                        <label>Description</label>
                                        <textarea name="projects[{{ $index }}][description]" rows="2"
                                                  placeholder="Brief description of the project">{{ $project['description'] ?? '' }}</textarea>
                                    </div>
                                    <div class="cpbn-field full">
                                        <label>Technologies Used</label>
                                        <input type="text" name="projects[{{ $index }}][technologies_used]"
                                               value="{{ $technologies }}"
                                               placeholder="e.g. Python, React, MySQL">
                                        <small class="cpbn-file-note">Separate technologies with commas</small>
                                    </div>
                                    <div class="cpbn-field">
                                        <label>Start Date</label>
                                        <input type="date" name="projects[{{ $index }}][start_date]"
                                               value="{{ $project['start_date'] ?? '' }}">
                                    </div>
                                    <div class="cpbn-field">
                                        <label>End Date</label>
                                        <input type="date" name="projects[{{ $index }}][end_date]"
                                               value="{{ $project['end_date'] ?? '' }}">
                                    </div>
                                    <div class="cpbn-field full">
                                        <label>Achievements</label>
                                        <textarea name="projects[{{ $index }}][achievements]" rows="2"
                                                  placeholder="What did you accomplish?">{{ $project['achievements'] ?? '' }}</textarea>
                                    </div>
                                </div>

                                <button type="button" class="cpbn-remove-project" onclick="removeProject(this)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;">
                                        <path d="M18 6L6 18M6 6l12 12"/>
                                    </svg>
                                    Remove Project
                                </button>
                            </div>
                        @endforeach
                    @endif
                </div>

                <button type="button" id="add-project" class="cpbn-add-project">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Add Another Project
                </button>
            </div>

            <!-- SECTION 6: Certifications -->
            <div class="cpbn-section">
                <h3>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="9" r="6"/>
                        <path d="M9 14.5 7 22l5-3 5 3-2-7.5"/>
                    </svg>
                    Certifications
                </h3>

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
                                    'issue_date' => $certification->issue_date?->format('Y-m-d'),
                                ];
                            })
                            ->values()
                            ->all();
                    }

                    $nextCertificationIndex = empty($certificationRows)
                        ? 0
                        : max(array_map('intval', array_keys($certificationRows))) + 1;
                @endphp

                <div id="certification-list" data-next-index="{{ $nextCertificationIndex }}">
                    @if(empty($certificationRows) || !is_array($certificationRows) || count($certificationRows) === 0)
                        <!-- Show one empty certification card -->
                        <div class="cpbn-certification-card">
                            <div class="cpbn-fgrid">
                                <div class="cpbn-field">
                                    <label>Certification Name <span class="req">*</span></label>
                                    <input type="text" name="certifications[0][certification_name]" 
                                           placeholder="e.g. AWS Cloud Practitioner" required>
                                </div>
                                <div class="cpbn-field">
                                    <label>Issuing Organisation</label>
                                    <input type="text" name="certifications[0][issuing_organization]" 
                                           placeholder="e.g. AWS, Cisco, Politeknik Brunei">
                                </div>
                                <div class="cpbn-field">
                                    <label>Issue Date</label>
                                    <input type="date" name="certifications[0][issue_date]" 
                                           max="{{ now()->format('Y-m-d') }}">
                                </div>
                                <div class="cpbn-field">
                                    <label>Certificate File</label>
                                    <input type="file" name="certifications[0][certificate_file]"
                                           accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png">
                                    <small class="cpbn-file-note">PDF, JPG, JPEG or PNG · Maximum 5 MB</small>
                                </div>
                            </div>
                            <button type="button" class="cpbn-remove-certification" onclick="removeCertification(this)" style="display:none;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;">
                                    <path d="M18 6L6 18M6 6l12 12"/>
                                </svg>
                                Remove Certification
                            </button>
                        </div>
                    @else
                        @foreach ($certificationRows as $index => $certification)
                            @php
                                $existingCertification = ! empty($certification['id'])
                                    ? $user->certifications->firstWhere('id', (int) $certification['id'])
                                    : null;
                            @endphp

                            <div class="cpbn-certification-card">
                                @if (! empty($certification['id']))
                                    <input type="hidden" name="certifications[{{ $index }}][id]" value="{{ $certification['id'] }}">
                                @endif

                                <div class="cpbn-fgrid">
                                    <div class="cpbn-field">
                                        <label>Certification Name <span class="req">*</span></label>
                                        <input type="text" name="certifications[{{ $index }}][certification_name]"
                                               value="{{ $certification['certification_name'] ?? '' }}"
                                               placeholder="e.g. AWS Cloud Practitioner" required>
                                    </div>
                                    <div class="cpbn-field">
                                        <label>Issuing Organisation</label>
                                        <input type="text" name="certifications[{{ $index }}][issuing_organization]"
                                               value="{{ $certification['issuing_organization'] ?? '' }}"
                                               placeholder="e.g. AWS, Cisco, Politeknik Brunei">
                                    </div>
                                    <div class="cpbn-field">
                                        <label>Issue Date</label>
                                        <input type="date" name="certifications[{{ $index }}][issue_date]"
                                               value="{{ $certification['issue_date'] ?? '' }}"
                                               max="{{ now()->format('Y-m-d') }}">
                                    </div>
                                    <div class="cpbn-field">
                                        <label>Certificate File</label>
                                        <input type="file" name="certifications[{{ $index }}][certificate_file]"
                                               accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png">
                                        <small class="cpbn-file-note">PDF, JPG, JPEG or PNG · Maximum 5 MB</small>

                                        @if ($existingCertification?->certificate_file_path)
                                            <div class="cpbn-file-existing">✓ Evidence uploaded</div>
                                        @endif
                                    </div>
                                </div>

                                <button type="button" class="cpbn-remove-certification" onclick="removeCertification(this)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;">
                                        <path d="M18 6L6 18M6 6l12 12"/>
                                    </svg>
                                    Remove Certification
                                </button>
                            </div>
                        @endforeach
                    @endif
                </div>

                <button type="button" id="add-certification" class="cpbn-add-certification">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Add Certification
                </button>
            </div>

            <!-- SECTION 7: Career Aspirations -->
            <div class="cpbn-section">
                <h3>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2l2.9 6.6 7.1.7-5.4 4.7 1.7 7-6.3-3.8L5.7 21l1.7-7-5.4-4.7 7.1-.7z"/>
                    </svg>
                    Career Aspirations
                </h3>
                <div class="cpbn-field full" style="margin-bottom:16px">
                    <label>What is your dream career?</label>
                    <input type="text" name="career_goals_text"
                           value="{{ old('career_goals_text', $user->aspirations->career_goals[0] ?? '') }}"
                           placeholder="e.g. Software Engineer, Data Scientist">
                    @error('career_goals_text')<p class="err">{{ $message }}</p>@enderror
                </div>
                <div class="cpbn-field full" style="margin-bottom:16px">
                    <label>Your Vision Statement</label>
                    <textarea name="vision_statement" rows="2">{{ old('vision_statement', $user->aspirations->vision_statement ?? '') }}</textarea>
                    @error('vision_statement')<p class="err">{{ $message }}</p>@enderror
                </div>
                <div class="cpbn-field full">
                    <label>Long Term Goals</label>
                    <textarea name="long_term_goals" rows="2">{{ old('long_term_goals', $user->aspirations->long_term_goals ?? '') }}</textarea>
                    @error('long_term_goals')<p class="err">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="cpbn-submit-row">
                <button type="submit" class="cpbn-btn cpbn-btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                    Save Profile
                </button>
            </div>

        </form>

    </div>
</div>

<!-- 
    ============================================
    JAVASCRIPT IS IN app.js
    ============================================
    All dynamic functionality (projects, certifications, 
    phone validation, modals) is handled in resources/js/app.js
-->

@endsection