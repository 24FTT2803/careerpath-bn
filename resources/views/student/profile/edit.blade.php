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

    .edit-profile .cpbn-check-note {
        font-size: 11px;
        color: var(--ink-dim);
        margin-top: 12px;
        font-family: var(--font-body);
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
            flex-direction: column;
        }

        .edit-profile .submit-row .cpbn-btn {
            width: 100%;
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
                {{ $profileCompletion >= 70
                    ? '✅ Profile complete — ready for career matching!'
                    : 'Your progress can be saved now and completed later.' }}
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('student.profile.update') }}"
            enctype="multipart/form-data"
            id="profile-form"
            data-confirm-update
            data-item-name="your profile"
        >
            @csrf
            @method('PUT')

            <!-- SECTION 1: Personal Information -->
            <div class="section">
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
                                $user->first_name
                                    ?? $user->name
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
                    <div class="cpbn-field">
                        <label>First Name <span class="req">*</span></label>

                        <input
                            type="text"
                            name="first_name"
                            value="{{ old('first_name', $user->first_name ?? '') }}"
                            required
                        >

                        @error('first_name')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="cpbn-field">
                        <label>Last Name <span class="req">*</span></label>

                        <input
                            type="text"
                            name="last_name"
                            value="{{ old('last_name', $user->last_name ?? '') }}"
                            required
                        >

                        @error('last_name')
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

                        <input
                            type="text"
                            name="student_id"
                            value="{{ old('student_id', $user->student_id) }}"
                            required
                        >

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

                            @foreach([
                                'Brunei Darussalam',
                                'Cambodia',
                                'Indonesia',
                                'Laos',
                                'Malaysia',
                                'Myanmar',
                                'Philippines',
                                'Singapore',
                                'Thailand',
                                'Timor-Leste',
                                'Vietnam'
                            ] as $nationality)
                                <option
                                    value="{{ $nationality }}"
                                    {{ old('nationality', $user->profile->nationality ?? '') === $nationality ? 'selected' : '' }}
                                >
                                    {{ $nationality }}
                                </option>
                            @endforeach
                        </select>

                        @error('nationality')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="cpbn-field full">
                        <label>Address</label>

                        <textarea
                            name="address"
                            rows="2"
                        >{{ old('address', $user->profile->address ?? '') }}</textarea>

                        @error('address')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="cpbn-field full">
                        <label>Bio / About You</label>

                        <textarea
                            name="bio"
                            rows="3"
                        >{{ old('bio', $user->profile->bio ?? '') }}</textarea>

                        @error('bio')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Academic Information -->
            <div class="section">
                <div class="title">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M22 10v6"/>
                        <path d="M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>

                    Academic Information
                </div>

                <div class="cpbn-fgrid">
                    <div class="cpbn-field">
                        <label>Programme</label>

                        <select name="programme">
                            <option value="">
                                Select your programme
                            </option>

                            <option
                                value="Diploma in ICT (Application Development)"
                                {{ old('programme', $user->programme) === 'Diploma in ICT (Application Development)' ? 'selected' : '' }}
                            >
                                DADT - Application Development
                            </option>

                            <option
                                value="Diploma in ICT (Data Analytics)"
                                {{ old('programme', $user->programme) === 'Diploma in ICT (Data Analytics)' ? 'selected' : '' }}
                            >
                                DDAT - Data Analytics
                            </option>

                            <option
                                value="Diploma in ICT (Cloud Networking)"
                                {{ old('programme', $user->programme) === 'Diploma in ICT (Cloud Networking)' ? 'selected' : '' }}
                            >
                                DCNG - Cloud Networking
                            </option>

                            <option
                                value="Diploma in Business Information Systems"
                                {{ old('programme', $user->programme) === 'Diploma in Business Information Systems' ? 'selected' : '' }}
                            >
                                DBIS - Business Information Systems
                            </option>
                        </select>

                        @error('programme')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="cpbn-field">
                        <label>CGPA</label>

                        <input
                            type="number"
                            name="cgpa"
                            step="0.01"
                            min="0"
                            max="4"
                            value="{{ old('cgpa', $user->cgpa) }}"
                        >

                        @error('cgpa')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- SECTION 3: Skills -->
            <div class="section">
                <div class="title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                    </svg>
                    Skills &amp; Competencies
                </div>

                <p class="desc">Select your technical skills</p>

                @php
                    $skillOptions = $skillOptions ?? [];

                    $storedSkillNames = $user
                        ->competencies
                        ->pluck('skill_name')
                        ->toArray();

                    $storedPredefinedSkills = array_values(
                        array_filter(
                            $storedSkillNames,
                            fn ($skill) => in_array(
                                $skill,
                                $skillOptions,
                                true
                            )
                        )
                    );

                    $storedAdditionalSkills = array_values(
                        array_filter(
                            $storedSkillNames,
                            fn ($skill) => ! in_array(
                                $skill,
                                $skillOptions,
                                true
                            )
                        )
                    );

                    $savedSkills = old(
                        'skills',
                        $storedPredefinedSkills
                    );

                    $savedAdditionalSkills = old(
                        'custom_skills',
                        $storedAdditionalSkills
                    );

                    $savedAdditionalSkills = is_array($savedAdditionalSkills)
                        ? array_values(
                            array_filter(
                                $savedAdditionalSkills,
                                fn ($skill) => trim((string) $skill) !== ''
                            )
                        )
                        : [];

                    $hasAdditionalSkills = ! empty(
                        $savedAdditionalSkills
                    );
                @endphp

                <div class="cpbn-checks">
                    @foreach($skillOptions as $skill)
                        <label class="cpbn-check">
                            <input
                                type="checkbox"
                                name="skills[]"
                                value="{{ $skill }}"
                                {{ in_array($skill, $savedSkills ?? [], true) ? 'checked' : '' }}
                            >
                            <span>{{ $skill }}</span>
                        </label>
                    @endforeach

                    <label class="cpbn-check">
                        <input
                            type="checkbox"
                            id="additional-skills-toggle"
                            {{ $hasAdditionalSkills ? 'checked' : '' }}
                        >
                        <span>Others</span>
                    </label>
                </div>

                <p class="cpbn-check-note">
                    Select all that apply. More skills = better career matching.
                </p>

                <div
                    id="additional-skills-section"
                    class="cpbn-additional"
                    {{ $hasAdditionalSkills ? '' : 'hidden' }}
                >
                    <div class="cpbn-additional-heading">
                        Additional Skills
                    </div>

                    <div
                        id="additional-skill-chips"
                        class="cpbn-custom-chips"
                    >
                        @foreach($savedAdditionalSkills as $skill)
                            <span
                                class="cpbn-custom-chip"
                                data-value="{{ $skill }}"
                            >
                                <span>{{ $skill }}</span>

                                <button
                                    type="button"
                                    class="cpbn-remove-custom-chip"
                                    aria-label="Remove {{ $skill }}"
                                    title="Remove"
                                >
                                    &times;
                                </button>

                                <input
                                    type="hidden"
                                    name="custom_skills[]"
                                    value="{{ $skill }}"
                                >
                            </span>
                        @endforeach
                    </div>

                    <div class="cpbn-custom-entry">
                        <input
                            type="text"
                            id="additional-skill-input"
                            maxlength="60"
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
                        <span id="additional-skill-feedback-text"></span>

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
                        <div class="cpbn-error">{{ $message }}</div>
                    @enderror

                    @error('custom_skills.*')
                        <div class="cpbn-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- SECTION 4: Interests -->
            <div class="section">
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
                            maxlength="60"
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
            <div class="section">
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
                                    'start_date' => $project->start_date?->format('Y-m-d'),
                                    'end_date' => $project->end_date?->format('Y-m-d'),
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
                            $technologies = is_array(
                                $project['technologies_used'] ?? []
                            )
                                ? implode(
                                    ', ',
                                    $project['technologies_used']
                                )
                                : (string) (
                                    $project['technologies_used']
                                    ?? ''
                                );
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
                                        required
                                    >
                                </div>

                                <div class="cpbn-field">
                                    <label>Your Role</label>
                                    <input
                                        type="text"
                                        name="projects[{{ $index }}][role]"
                                        value="{{ $project['role'] ?? '' }}"
                                    >
                                </div>

                                <div class="cpbn-field">
                                    <label>Project URL</label>
                                    <input
                                        type="url"
                                        name="projects[{{ $index }}][project_url]"
                                        value="{{ $project['project_url'] ?? '' }}"
                                    >
                                </div>

                                <div class="cpbn-field full">
                                    <label>Description</label>
                                    <textarea
                                        name="projects[{{ $index }}][description]"
                                        rows="2"
                                    >{{ $project['description'] ?? '' }}</textarea>
                                </div>

                                <div class="cpbn-field full">
                                    <label>Technologies Used</label>
                                    <input
                                        type="text"
                                        name="projects[{{ $index }}][technologies_used]"
                                        value="{{ $technologies }}"
                                    >
                                    <span class="cpbn-file-note">
                                        Separate with commas
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

                <button
                    type="button"
                    id="add-project"
                    class="cpbn-add-project"
                >
                    <i class="fas fa-plus"></i>
                    Add Project
                </button>
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
                                        required
                                    >
                                </div>

                                <div class="cpbn-field">
                                    <label>Issuing Organisation</label>

                                    <input
                                        type="text"
                                        name="certifications[{{ $index }}][issuing_organization]"
                                        value="{{ $certification['issuing_organization'] ?? '' }}"
                                    >
                                </div>

                                <div class="cpbn-field">
                                    <label>Issue Date</label>

                                    <input
                                        type="date"
                                        name="certifications[{{ $index }}][issue_date]"
                                        value="{{ $certification['issue_date'] ?? '' }}"
                                        max="{{ now()->format('Y-m-d') }}"
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
                                            ⚠ An evidence record exists, but the stored file could not be found.
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

                <button
                    type="button"
                    id="add-certification"
                    class="cpbn-add-certification"
                >
                    <i class="fas fa-plus"></i>
                    Add Certification
                </button>
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
                    <div class="cpbn-field full">
                        <label>Dream Career</label>
                        <input
                            type="text"
                            name="career_goals_text"
                            value="{{ old('career_goals_text', $user->aspirations->career_goals[0] ?? '') }}"
                            placeholder="e.g. Software Engineer"
                        >
                    </div>

                    <div class="cpbn-field full">
                        <label>Vision Statement</label>
                        <textarea
                            name="vision_statement"
                            rows="2"
                        >{{ old('vision_statement', $user->aspirations->vision_statement ?? '') }}</textarea>
                    </div>

                    <div class="cpbn-field full">
                        <label>Long Term Goals</label>
                        <textarea
                            name="long_term_goals"
                            rows="2"
                        >{{ old('long_term_goals', $user->aspirations->long_term_goals ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="submit-row">
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

            if (value.length > 60) {
                return `Each additional ${config.singular} must be 60 characters or fewer.`;
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

            chip.append(
                label,
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

        const setEnabled = function (enabled) {
            section.hidden = ! enabled;

            section
                .querySelectorAll(
                    `input[name="${config.hiddenName}"]`
                )
                .forEach(hidden => {
                    hidden.disabled = ! enabled;
                });

            if (enabled) {
                input.focus();
            } else {
                /*
                 * Important:
                 * Chips are NOT deleted here.
                 *
                 * Unticking Others only prevents them from
                 * being submitted. If the user changes their
                 * mind before saving, checking Others again
                 * restores the same chips.
                 *
                 * If the page is refreshed without saving,
                 * the database remains unchanged.
                 */
                hideFeedback();
            }
        };

        setEnabled(toggle.checked);

        toggle.addEventListener(
            'change',
            function () {
                setEnabled(this.checked);
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