@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')

<style>
    .edit-profile {
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

    .edit-profile .cpbn-field .error {
        color: var(--rose);
        font-size: 12px;
        margin-top: 4px;
        font-family: var(--font-body);
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

    .edit-profile .cpbn-check-note {
        font-size: 11px;
        color: var(--ink-dim);
        margin-top: 12px;
        font-family: var(--font-body);
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
    .edit-profile .cpbn-remove-certification:hover {
        background: var(--rose);
        color: white;
    }

    .edit-profile .cpbn-remove-project svg,
    .edit-profile .cpbn-remove-certification svg {
        width: 14px;
        height: 14px;
    }

    /* Add Buttons */
    .edit-profile .cpbn-add-project,
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
    .edit-profile .cpbn-add-certification:hover {
        background: var(--gold);
        color: var(--ink);
        border-style: solid;
    }

    .edit-profile .cpbn-add-project svg,
    .edit-profile .cpbn-add-certification svg {
        width: 16px;
        height: 16px;
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
        font-size: 12px;
        color: var(--green);
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
        font-family: var(--font-body);
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
        width: 14px;
        height: 14px;
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

    .edit-profile .cpbn-btn-muted {
        background: var(--line);
        color: var(--ink);
    }

    .edit-profile .cpbn-btn-muted:hover {
        background: #d8d2c0;
        transform: translateY(-2px);
    }

    .edit-profile .submit-row {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 24px;
    }

    /* Error States */
    .edit-profile .cpbn-field .error-input {
        border-color: var(--rose) !important;
        box-shadow: 0 0 0 4px rgba(198, 91, 78, 0.15) !important;
    }

    @media (max-width: 720px) {
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
            flex-direction: column;
        }
        .edit-profile .submit-row .cpbn-btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .edit-profile .cpbn-checks {
            grid-template-columns: 1fr;
        }
        .edit-profile .head {
            flex-direction: column;
        }
    }
</style>

<div class="edit-profile">
    <div class="wrap">

        <div class="head">
            <div>
                <h1>Complete Your <span>Profile</span></h1>
                <p class="sub">Fill in all sections for better career recommendations</p>
            </div>
            <a href="{{ route('student.profile') }}" class="back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Back
            </a>
        </div>

        <!-- Progress -->
        <div class="progress-card">
            <div class="progress-top">
                <span>Profile Completion</span>
                <span>{{ $profileCompletion ?? 0 }}%</span>
            </div>
            <div class="bar">
                <div class="fill" style="width: {{ $profileCompletion ?? 0 }}%"></div>
            </div>
            <p class="progress-note">
                {{ $profileCompletion >= 70 ? '✅ Profile complete — ready for career matching!' : 'Complete all sections for better recommendations' }}
            </p>
        </div>

        <form method="POST" action="{{ route('student.profile.update') }}" enctype="multipart/form-data" id="profile-form">
            @csrf
            @method('PUT')

            <!-- SECTION 1: Personal Information -->
            <div class="section">
                <div class="title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
                    Personal Information
                </div>
                <div class="cpbn-fgrid">
                    <div class="cpbn-field">
                        <label>First Name <span class="req">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name', $user->first_name ?? '') }}" required>
                        @error('first_name')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="cpbn-field">
                        <label>Last Name <span class="req">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name', $user->last_name ?? '') }}" required>
                        @error('last_name')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="cpbn-field">
                        <label>Email <span class="req">*</span></label>
                        <input type="email" value="{{ $user->email }}" disabled>
                    </div>
                    <div class="cpbn-field">
                        <label>Student ID <span class="req">*</span></label>
                        <input type="text" name="student_id" value="{{ old('student_id', $user->student_id) }}" required>
                        @error('student_id')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="cpbn-field">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="+673 123 4567" pattern="[\+\d\s\-\(\)]{7,20}" title="Only digits, +, -, spaces, and parentheses allowed">
                        <span class="hint">Only digits, +, -, spaces, and parentheses allowed (7-20 characters)</span>
                        @error('phone')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="cpbn-field">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $user->profile->date_of_birth ?? '') }}">
                        @error('date_of_birth')<div class="error">{{ $message }}</div>@enderror
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
                        @error('nationality')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="cpbn-field full">
                        <label>Address</label>
                        <textarea name="address" rows="2">{{ old('address', $user->profile->address ?? '') }}</textarea>
                        @error('address')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="cpbn-field full">
                        <label>Bio / About You</label>
                        <textarea name="bio" rows="3">{{ old('bio', $user->profile->bio ?? '') }}</textarea>
                        @error('bio')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Academic Information -->
            <div class="section">
                <div class="title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 10v6"/>
                        <path d="M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                    Academic Information
                </div>
                <div class="cpbn-fgrid">
                    <div class="cpbn-field">
                        <label>Programme <span class="req">*</span></label>
                        <select name="programme" required>
                            <option value="">Select your programme</option>
                            <option value="Diploma in ICT (Application Development)" {{ old('programme', $user->programme) == 'Diploma in ICT (Application Development)' ? 'selected' : '' }}>DADT - Application Development</option>
                            <option value="Diploma in ICT (Data Analytics)" {{ old('programme', $user->programme) == 'Diploma in ICT (Data Analytics)' ? 'selected' : '' }}>DDAT - Data Analytics</option>
                            <option value="Diploma in ICT (Cloud Networking)" {{ old('programme', $user->programme) == 'Diploma in ICT (Cloud Networking)' ? 'selected' : '' }}>DCNG - Cloud Networking</option>
                            <option value="Diploma in Business Information Systems" {{ old('programme', $user->programme) == 'Diploma in Business Information Systems' ? 'selected' : '' }}>DBIS - Business Information Systems</option>
                            <option value="Others" {{ old('programme', $user->programme) == 'Others' ? 'selected' : '' }}>Others</option>
                        </select>
                        @error('programme')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="cpbn-field">
                        <label>CGPA <span class="req">*</span></label>
                        <input type="number" name="cgpa" step="0.01" min="0" max="4" value="{{ old('cgpa', $user->cgpa) }}" required>
                        @error('cgpa')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- SECTION 3: Skills -->
            <div class="section">
                <div class="title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    Skills &amp; Competencies
                </div>
                <p class="desc">Select your technical skills</p>
                @php
                    $skillOptions = ['Python', 'JavaScript', 'SQL', 'Java', 'PHP', 'HTML/CSS', 'React', 'Node.js', 'Git', 'Linux', 'Docker', 'AWS', 'C++', 'C#', 'Ruby'];
                    $savedSkills = $user->competencies->pluck('skill_name')->toArray();
                @endphp
                <div class="cpbn-checks">
                    @foreach($skillOptions as $skill)
                        <label class="cpbn-check">
                            <input type="checkbox" name="skills[]" value="{{ $skill }}" {{ in_array($skill, $savedSkills) ? 'checked' : '' }}>
                            <span>{{ $skill }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="cpbn-check-note">Select all that apply. More skills = better career matching.</p>
                @error('skills')<div class="error">{{ $message }}</div>@enderror
            </div>

            <!-- SECTION 4: Interests -->
<div class="section">
    <div class="title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.8 1-1a5.5 5.5 0 0 0 0-7.6z"/></svg>
        Interests &amp; Preferences
    </div>
    <p class="desc">What are you interested in?</p>
    @php
        $interestOptions = ['Problem Solving', 'Teamwork', 'Communication', 'Leadership', 'Creativity', 'Analytical Thinking', 'Research', 'Writing', 'Public Speaking', 'Programming', 'Data Analysis', 'Networking', 'Cybersecurity', 'Cloud Computing', 'Project Management'];
        $savedInterests = $user->interests->pluck('interest_name')->toArray();
        $savedInterestNames = $savedInterests;
        
        // Check if there are custom interests (not in the predefined list)
        $customInterests = [];
        $hasOtherInterest = false;
        foreach ($savedInterests as $interest) {
            if (!in_array($interest, $interestOptions)) {
                $customInterests[] = $interest;
                $hasOtherInterest = true;
            }
        }
        $otherInterestText = $hasOtherInterest ? implode(', ', $customInterests) : '';
    @endphp
    <div class="cpbn-checks">
        @foreach($interestOptions as $interest)
            <label class="cpbn-check">
                <input type="checkbox" name="interests[]" value="{{ $interest }}" {{ in_array($interest, $savedInterests) ? 'checked' : '' }}>
                <span>{{ $interest }}</span>
            </label>
        @endforeach
        <!-- Others Option -->
        <label class="cpbn-check">
            <input type="checkbox" name="interests[]" id="interest-others" value="others" {{ $hasOtherInterest ? 'checked' : '' }} onchange="toggleInterestOtherField()">
            <span>Others</span>
        </label>
    </div>
    <!-- Others Text Field -->
    <div id="interest-others-field" style="{{ $hasOtherInterest ? 'display:block' : 'display:none' }}; margin-top:12px;">
        <div class="cpbn-field">
            <label>Please specify your other interests <span class="req">*</span></label>
            <input type="text" name="interest_others_text" id="interest-others-text" 
                   value="{{ old('interest_others_text', $otherInterestText) }}" 
                   placeholder="e.g. AI, Machine Learning, Graphic Design (separate with commas)"
                   {{ $hasOtherInterest ? 'required' : '' }}>
            <span class="hint">Separate multiple interests with commas</span>
        </div>
    </div>
    @error('interests')<div class="error">{{ $message }}</div>@enderror
    @error('interest_others_text')<div class="error">{{ $message }}</div>@enderror
</div>

            <!-- SECTION 5: Projects -->
            <div class="section">
                <div class="title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    Projects &amp; Experience
                </div>
                <p class="desc">Add your projects with details like technologies used, your role, and achievements.</p>

                @php
                    $projectRows = old('projects');
                    if ($projectRows === null) {
                        $projectRows = $user->projects->map(function ($p) {
                            return [
                                'id' => $p->id,
                                'title' => $p->title,
                                'description' => $p->description,
                                'technologies_used' => $p->technologies_used ?? [],
                                'role' => $p->role,
                                'project_url' => $p->project_url,
                                'start_date' => $p->start_date?->format('Y-m-d'),
                                'end_date' => $p->end_date?->format('Y-m-d'),
                                'achievements' => $p->achievements,
                            ];
                        })->values()->all();
                    }
                    $nextIdx = !empty($projectRows) && is_array($projectRows) ? max(array_map('intval', array_keys($projectRows))) + 1 : 0;
                @endphp

                <div id="project-list" data-next-index="{{ $nextIdx }}">
                    @if(empty($projectRows) || !is_array($projectRows) || count($projectRows) === 0)
                        <div class="cpbn-project-card">
                            <span class="cpbn-project-number">Project #1</span>
                            <div class="cpbn-fgrid">
                                <div class="cpbn-field"><label>Project Title <span class="req">*</span></label><input type="text" name="projects[0][title]" placeholder="e.g. Hobbee Apps" required></div>
                                <div class="cpbn-field"><label>Your Role</label><input type="text" name="projects[0][role]" placeholder="e.g. Lead Developer"></div>
                                <div class="cpbn-field"><label>Project URL</label><input type="url" name="projects[0][project_url]" placeholder="https://github.com/your-project"></div>
                                <div class="cpbn-field full"><label>Description</label><textarea name="projects[0][description]" rows="2" placeholder="Brief description"></textarea></div>
                                <div class="cpbn-field full"><label>Technologies Used</label><input type="text" name="projects[0][technologies_used]" placeholder="e.g. Python, React, MySQL"><span class="cpbn-file-note">Separate with commas</span></div>
                                <div class="cpbn-field"><label>Start Date</label><input type="date" name="projects[0][start_date]"></div>
                                <div class="cpbn-field"><label>End Date</label><input type="date" name="projects[0][end_date]"></div>
                                <div class="cpbn-field full"><label>Achievements</label><textarea name="projects[0][achievements]" rows="2" placeholder="What did you accomplish?"></textarea></div>
                            </div>
                            <button type="button" class="cpbn-remove-project" onclick="removeProject(this)" style="display:none;"><i class="fas fa-trash-alt"></i> Remove</button>
                        </div>
                    @else
                        @foreach ($projectRows as $idx => $p)
                            @php $tech = is_array($p['technologies_used'] ?? []) ? implode(', ', $p['technologies_used']) : (string) ($p['technologies_used'] ?? ''); @endphp
                            <div class="cpbn-project-card">
                                @if (!empty($p['id'])) <input type="hidden" name="projects[{{ $idx }}][id]" value="{{ $p['id'] }}"> @endif
                                <span class="cpbn-project-number">Project #{{ $loop->iteration }}</span>
                                <div class="cpbn-fgrid">
                                    <div class="cpbn-field"><label>Project Title <span class="req">*</span></label><input type="text" name="projects[{{ $idx }}][title]" value="{{ $p['title'] ?? '' }}" required></div>
                                    <div class="cpbn-field"><label>Your Role</label><input type="text" name="projects[{{ $idx }}][role]" value="{{ $p['role'] ?? '' }}"></div>
                                    <div class="cpbn-field"><label>Project URL</label><input type="url" name="projects[{{ $idx }}][project_url]" value="{{ $p['project_url'] ?? '' }}"></div>
                                    <div class="cpbn-field full"><label>Description</label><textarea name="projects[{{ $idx }}][description]" rows="2">{{ $p['description'] ?? '' }}</textarea></div>
                                    <div class="cpbn-field full"><label>Technologies Used</label><input type="text" name="projects[{{ $idx }}][technologies_used]" value="{{ $tech }}"><span class="cpbn-file-note">Separate with commas</span></div>
                                    <div class="cpbn-field"><label>Start Date</label><input type="date" name="projects[{{ $idx }}][start_date]" value="{{ $p['start_date'] ?? '' }}"></div>
                                    <div class="cpbn-field"><label>End Date</label><input type="date" name="projects[{{ $idx }}][end_date]" value="{{ $p['end_date'] ?? '' }}"></div>
                                    <div class="cpbn-field full"><label>Achievements</label><textarea name="projects[{{ $idx }}][achievements]" rows="2">{{ $p['achievements'] ?? '' }}</textarea></div>
                                </div>
                                <button type="button" class="cpbn-remove-project" onclick="removeProject(this)" data-confirm-delete data-item-name="{{ $p['title'] ?? 'this project' }}">
                                    <i class="fas fa-trash-alt"></i> Remove
                                </button>
                            </div>
                        @endforeach
                    @endif
                </div>
                <button type="button" id="add-project" class="cpbn-add-project"><i class="fas fa-plus"></i> Add Project</button>
            </div>

            <!-- SECTION 6: Certifications -->
            <div class="section">
                <div class="title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="9" r="6"/>
                        <path d="M9 14.5 7 22l5-3 5 3-2-7.5"/>
                    </svg>
                    Certifications
                </div>
                <p class="desc">Add any academic, professional, training or course certifications you have completed.</p>

                @php
                    $certRows = old('certifications');
                    if ($certRows === null) {
                        $certRows = $user->certifications->map(function ($c) {
                            return [
                                'id' => $c->id,
                                'certification_name' => $c->certification_name,
                                'issuing_organization' => $c->issuing_organization,
                                'issue_date' => $c->issue_date?->format('Y-m-d'),
                            ];
                        })->values()->all();
                    }
                    $nextCertIdx = !empty($certRows) && is_array($certRows) ? max(array_map('intval', array_keys($certRows))) + 1 : 0;
                @endphp

                <div id="certification-list" data-next-index="{{ $nextCertIdx }}">
                    @if(empty($certRows) || !is_array($certRows) || count($certRows) === 0)
                        <div class="cpbn-certification-card">
                            <div class="cpbn-fgrid">
                                <div class="cpbn-field"><label>Certification Name <span class="req">*</span></label><input type="text" name="certifications[0][certification_name]" placeholder="e.g. AWS Cloud Practitioner" required></div>
                                <div class="cpbn-field"><label>Issuing Organisation</label><input type="text" name="certifications[0][issuing_organization]" placeholder="e.g. AWS, Cisco"></div>
                                <div class="cpbn-field"><label>Issue Date</label><input type="date" name="certifications[0][issue_date]" max="{{ now()->format('Y-m-d') }}"></div>
                                <div class="cpbn-field"><label>Certificate File</label><input type="file" name="certifications[0][certificate_file]" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"><span class="cpbn-file-note">PDF, JPG, JPEG or PNG · Max 5MB</span></div>
                            </div>
                            <button type="button" class="cpbn-remove-certification" onclick="removeCertification(this)" style="display:none;"><i class="fas fa-trash-alt"></i> Remove</button>
                        </div>
                    @else
                        @foreach ($certRows as $idx => $c)
                            @php $existing = !empty($c['id']) ? $user->certifications->firstWhere('id', (int) $c['id']) : null; @endphp
                            <div class="cpbn-certification-card">
                                @if (!empty($c['id'])) <input type="hidden" name="certifications[{{ $idx }}][id]" value="{{ $c['id'] }}"> @endif
                                <div class="cpbn-fgrid">
                                    <div class="cpbn-field"><label>Certification Name <span class="req">*</span></label><input type="text" name="certifications[{{ $idx }}][certification_name]" value="{{ $c['certification_name'] ?? '' }}" required></div>
                                    <div class="cpbn-field"><label>Issuing Organisation</label><input type="text" name="certifications[{{ $idx }}][issuing_organization]" value="{{ $c['issuing_organization'] ?? '' }}"></div>
                                    <div class="cpbn-field"><label>Issue Date</label><input type="date" name="certifications[{{ $idx }}][issue_date]" value="{{ $c['issue_date'] ?? '' }}" max="{{ now()->format('Y-m-d') }}"></div>
                                    <div class="cpbn-field"><label>Certificate File</label><input type="file" name="certifications[{{ $idx }}][certificate_file]" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"><span class="cpbn-file-note">PDF, JPG, JPEG or PNG · Max 5MB</span>@if($existing?->certificate_file_path)<span class="cpbn-file-existing"><i class="fas fa-check-circle"></i> Uploaded</span>@endif</div>
                                </div>
                                <button type="button" class="cpbn-remove-certification" onclick="removeCertification(this)" data-confirm-delete data-item-name="{{ $c['certification_name'] ?? 'this certification' }}">
                                    <i class="fas fa-trash-alt"></i> Remove
                                </button>
                            </div>
                        @endforeach
                    @endif
                </div>
                <button type="button" id="add-certification" class="cpbn-add-certification"><i class="fas fa-plus"></i> Add Certification</button>
            </div>

            <!-- SECTION 7: Aspirations -->
            <div class="section">
                <div class="title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2l2.9 6.6 7.1.7-5.4 4.7 1.7 7-6.3-3.8L5.7 21l1.7-7-5.4-4.7 7.1-.7z"/>
                    </svg>
                    Career Aspirations
                </div>
                <div class="cpbn-fgrid">
                    <div class="cpbn-field full"><label>Dream Career</label><input type="text" name="career_goals_text" value="{{ old('career_goals_text', $user->aspirations->career_goals[0] ?? '') }}" placeholder="e.g. Software Engineer">@error('career_goals_text')<div class="error">{{ $message }}</div>@enderror</div>
                    <div class="cpbn-field full"><label>Vision Statement</label><textarea name="vision_statement" rows="2">{{ old('vision_statement', $user->aspirations->vision_statement ?? '') }}</textarea>@error('vision_statement')<div class="error">{{ $message }}</div>@enderror</div>
                    <div class="cpbn-field full"><label>Long Term Goals</label><textarea name="long_term_goals" rows="2">{{ old('long_term_goals', $user->aspirations->long_term_goals ?? '') }}</textarea>@error('long_term_goals')<div class="error">{{ $message }}</div>@enderror</div>
                </div>
            </div>

            <!-- SUBMIT BUTTON WITH CONFIRMATION -->
            <div class="submit-row">
                <button type="submit" class="cpbn-btn cpbn-btn-primary" data-confirm-update data-item-name="your profile">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                    Save Profile
                </button>
            </div>

        </form>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ============================================
        // PROJECTS - Dynamic Add/Remove
        // ============================================

        const projectList = document.getElementById('project-list');
        let projectIndex = Number(projectList.dataset.nextIndex) || 0;

        document.getElementById('add-project').addEventListener('click', function() {
            const card = document.createElement('div');
            card.className = 'cpbn-project-card';
            const iteration = projectList.children.length + 1;
            card.innerHTML = `
                <span class="cpbn-project-number">Project #${iteration}</span>
                <div class="cpbn-fgrid">
                    <div class="cpbn-field"><label>Project Title <span class="req">*</span></label><input type="text" name="projects[${projectIndex}][title]" placeholder="e.g. Hobbee Apps" required></div>
                    <div class="cpbn-field"><label>Your Role</label><input type="text" name="projects[${projectIndex}][role]" placeholder="e.g. Lead Developer"></div>
                    <div class="cpbn-field"><label>Project URL</label><input type="url" name="projects[${projectIndex}][project_url]" placeholder="https://github.com/your-project"></div>
                    <div class="cpbn-field full"><label>Description</label><textarea name="projects[${projectIndex}][description]" rows="2" placeholder="Brief description"></textarea></div>
                    <div class="cpbn-field full"><label>Technologies Used</label><input type="text" name="projects[${projectIndex}][technologies_used]" placeholder="e.g. Python, React, MySQL"><span class="cpbn-file-note">Separate with commas</span></div>
                    <div class="cpbn-field"><label>Start Date</label><input type="date" name="projects[${projectIndex}][start_date]"></div>
                    <div class="cpbn-field"><label>End Date</label><input type="date" name="projects[${projectIndex}][end_date]"></div>
                    <div class="cpbn-field full"><label>Achievements</label><textarea name="projects[${projectIndex}][achievements]" rows="2" placeholder="What did you accomplish?"></textarea></div>
                </div>
                <button type="button" class="cpbn-remove-project" onclick="removeProject(this)" data-confirm-delete data-item-name="this new project">
                    <i class="fas fa-trash-alt"></i> Remove
                </button>
            `;
            projectList.appendChild(card);
            projectIndex++;
        });

        window.removeProject = function(button) {
            const card = button.closest('.cpbn-project-card');
            if (projectList.children.length <= 1) {
                const inputs = card.querySelectorAll('input, textarea');
                inputs.forEach(input => input.value = '');
                return;
            }
            card.remove();
            const cards = projectList.querySelectorAll('.cpbn-project-card');
            cards.forEach((c, i) => {
                const num = c.querySelector('.cpbn-project-number');
                if (num) num.textContent = `Project #${i + 1}`;
            });
        };

        // ============================================
        // CERTIFICATIONS - Dynamic Add/Remove
        // ============================================

        const certList = document.getElementById('certification-list');
        let certIndex = Number(certList.dataset.nextIndex) || 0;

        document.getElementById('add-certification').addEventListener('click', function() {
            const card = document.createElement('div');
            card.className = 'cpbn-certification-card';
            card.innerHTML = `
                <div class="cpbn-fgrid">
                    <div class="cpbn-field"><label>Certification Name <span class="req">*</span></label><input type="text" name="certifications[${certIndex}][certification_name]" placeholder="e.g. AWS Cloud Practitioner" required></div>
                    <div class="cpbn-field"><label>Issuing Organisation</label><input type="text" name="certifications[${certIndex}][issuing_organization]" placeholder="e.g. AWS, Cisco"></div>
                    <div class="cpbn-field"><label>Issue Date</label><input type="date" name="certifications[${certIndex}][issue_date]" max="{{ now()->format('Y-m-d') }}"></div>
                    <div class="cpbn-field"><label>Certificate File</label><input type="file" name="certifications[${certIndex}][certificate_file]" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"><span class="cpbn-file-note">PDF, JPG, JPEG or PNG · Max 5MB</span></div>
                </div>
                <button type="button" class="cpbn-remove-certification" onclick="removeCertification(this)" data-confirm-delete data-item-name="this new certification">
                    <i class="fas fa-trash-alt"></i> Remove
                </button>
            `;
            certList.appendChild(card);
            certIndex++;
        });

        window.removeCertification = function(button) {
            const card = button.closest('.cpbn-certification-card');
            if (certList.children.length <= 1) {
                const inputs = card.querySelectorAll('input, textarea');
                inputs.forEach(input => input.value = '');
                return;
            }
            card.remove();
        };
    });
</script>

@endsection