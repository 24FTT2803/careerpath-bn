@extends('layouts.app')

@section('title', 'My Profile')

@section('content')

<style>
    .profile-page {
        padding: 24px 0 40px;
    }

    .profile-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }

    .profile-header h1 {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: var(--primary);
    }

    .profile-header h1 span {
        color: var(--accent);
    }

    .profile-header .subtitle {
        color: var(--text-muted);
        font-size: 14px;
        margin-top: 2px;
    }

    .profile-header-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .profile-header-avatar {
        width: 72px;
        height: 72px;
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
        font-size: 26px;
        font-weight: 700;
    }

    .profile-header-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        font-family: inherit;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-light);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(26, 58, 92, 0.25);
    }

    .btn-outline {
        background: transparent;
        color: var(--primary);
        border: 2px solid var(--primary);
    }

    .btn-outline:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-2px);
    }

    .btn-sm {
        padding: 8px 16px;
        font-size: 12px;
    }

    /* Header buttons: same height and shape side by side. */
    .action-buttons .btn-primary {
        border: 2px solid var(--primary);
    }

    .action-buttons .btn-primary:hover {
        border-color: var(--primary-light);
    }

    .btn-success {
        background: #2d8f5c;
        color: white;
    }

    .btn-success:hover {
        background: #1e6b44;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(45, 143, 92, 0.3);
    }

    .profile-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .panel {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 24px;
    }

    .panel-full {
        grid-column: 1 / -1;
    }

    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .panel-header h3 {
        font-size: 16px;
        font-weight: 600;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .panel-header h3 i {
        color: var(--accent);
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid var(--border);
        font-size: 14px;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-row .label {
        color: var(--text-muted);
        font-weight: 500;
    }

    .info-row .value {
        font-weight: 500;
        color: var(--text);
        text-align: right;
    }

    .tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 500;
    }

    .tag-blue {
        background: rgba(26, 58, 92, 0.08);
        color: var(--primary);
    }

    .tag-gold {
        background: rgba(201, 168, 76, 0.12);
        color: var(--accent-dark);
    }

    .tag-green {
        background: rgba(45, 143, 92, 0.12);
        color: var(--success);
    }

    .tag-rose {
        background: rgba(192, 57, 43, 0.08);
        color: var(--danger);
    }

    .tag-additional-skill {
        background: rgba(201, 168, 76, 0.08);
        color: #7b5c23;
        border: 1px solid rgba(201, 168, 76, 0.45);
    }

    .tag-additional-interest {
        background: rgba(192, 57, 43, 0.04);
        color: #9a453c;
        border: 1px solid rgba(192, 57, 43, 0.25);
    }

    .tag small {
        opacity: 0.7;
        font-weight: 400;
    }

    .additional-group {
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid var(--border);
    }

    .additional-group-title {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 9px;
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .additional-group-title i {
        color: var(--accent);
        font-size: 10px;
    }

    .empty-text {
        color: var(--text-muted);
        font-size: 13px;
        padding: 8px 0;
    }

    .completion-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px 24px;
        margin-bottom: 24px;
    }

    .completion-card .top {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .completion-card .top .label {
        font-weight: 500;
        font-size: 14px;
    }

    .completion-card .top .percentage {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: var(--accent-dark);
    }

    .completion-card .bar {
        height: 6px;
        background: var(--bg);
        border-radius: 4px;
        overflow: hidden;
        margin-top: 10px;
    }

    .completion-card .bar .fill {
        height: 100%;
        border-radius: 4px;
        background: var(--accent);
        transition: width 0.6s ease;
    }

    .completion-card .note {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 8px;
    }

    .project-card {
        background: var(--bg);
        border-radius: 8px;
        padding: 16px;
        border: 1px solid var(--border);
    }

    .project-card h4 {
        font-weight: 600;
        font-size: 14px;
        color: var(--primary);
        margin-bottom: 4px;
    }

    .project-card .role {
        font-size: 12px;
        color: var(--text-muted);
    }

    .project-card .desc {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 6px;
    }

    .project-card .tech-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 8px;
    }

    .project-card .tech-tags span {
        background: rgba(26, 58, 92, 0.06);
        padding: 2px 10px;
        border-radius: 100px;
        font-size: 11px;
        color: var(--primary);
    }

    .project-card .achievement {
        font-size: 12px;
        color: var(--success);
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .cert-card {
        background: var(--bg);
        border-radius: 8px;
        padding: 14px 16px;
        border: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cert-card .cert-name {
        font-weight: 500;
        font-size: 14px;
        color: var(--primary);
    }

    .cert-card .cert-org {
        font-size: 12px;
        color: var(--text-muted);
    }

    .cert-card .cert-date {
        font-size: 12px;
        color: var(--text-muted);
    }

    .cert-card .cert-badge {
        font-size: 11px;
        font-weight: 500;
        color: var(--success);
        background: rgba(45, 143, 92, 0.1);
        padding: 2px 10px;
        border-radius: 100px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* ============================================
       TWO-COLUMN LAYOUT: details left, badge right
       ============================================ */
    .profile-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 330px;
        gap: 28px;
        align-items: start;
    }

    .profile-main {
        min-width: 0;
    }

    .profile-main .completion-card {
        margin-bottom: 20px;
    }

    .fact-tiles {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        gap: 12px;
    }

    .fact-tile {
        display: grid;
        grid-template-columns: auto 1fr;
        grid-template-rows: auto auto;
        column-gap: 12px;
        align-items: center;
        padding: 14px 16px;
        border-radius: 10px;
        background: var(--bg);
        border: 1px solid var(--border);
    }

    .fact-tile i {
        grid-row: 1 / 3;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(201, 168, 76, 0.14);
        color: var(--accent-dark);
        font-size: 15px;
    }

    .fact-label {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--text-muted);
    }

    .fact-value {
        font-size: 15px;
        font-weight: 600;
        color: var(--primary);
        overflow-wrap: anywhere;
    }

    .project-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 12px;
    }

    /* ============================================
       STUDENT BADGE
       ============================================ */
    .badge-column {
        position: sticky;
        top: var(--badge-top, 120px);
    }

    .badge-stage {
        position: relative;
        padding-top: 64px;
        perspective: 900px;
    }

    .badge-lanyard {
        position: absolute;
        top: 0;
        left: 50%;
        width: 44px;
        height: 70px;
        transform: translateX(-50%);
        pointer-events: none;
    }

    .badge-lanyard::before,
    .badge-lanyard::after {
        content: '';
        position: absolute;
        top: -10px;
        width: 14px;
        height: 64px;
        border-radius: 3px;
        background: repeating-linear-gradient(180deg, #1a3a5c 0 10px, #23497a 10px 20px);
        box-shadow: inset 0 0 0 1px rgba(201, 168, 76, 0.45);
    }

    .badge-lanyard::before {
        left: 4px;
        transform: rotate(9deg);
        transform-origin: bottom center;
    }

    .badge-lanyard::after {
        right: 4px;
        transform: rotate(-9deg);
        transform-origin: bottom center;
    }

    .badge-swing {
        position: relative;
        transform-origin: 50% -40px;
    }

    .badge-clip {
        position: absolute;
        top: -22px;
        left: 50%;
        z-index: 2;
        width: 34px;
        height: 30px;
        transform: translateX(-50%);
        border-radius: 7px 7px 5px 5px;
        background: linear-gradient(180deg, #e9edf2, #aab4c0);
        box-shadow: 0 2px 4px rgba(13, 31, 51, 0.25), inset 0 1px 0 #fff;
        pointer-events: none;
    }

    .badge-clip::after {
        content: '';
        position: absolute;
        left: 50%;
        bottom: 7px;
        width: 16px;
        height: 5px;
        transform: translateX(-50%);
        border-radius: 3px;
        background: #6b7787;
    }

    .badge-tilt {
        transition: transform 0.35s cubic-bezier(0.22, 0.61, 0.36, 1);
        transform-style: preserve-3d;
        will-change: transform;
    }

    .id-badge {
        position: relative;
        overflow: hidden;
        border-radius: 18px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 24px 48px -24px rgba(13, 31, 51, 0.45), 0 2px 6px rgba(13, 31, 51, 0.08);
        text-align: center;
    }

    .id-badge::after {
        content: '';
        position: absolute;
        inset: -40% -60%;
        background: linear-gradient(105deg, transparent 40%, rgba(255, 255, 255, 0.55) 50%, transparent 60%);
        transform: translateX(-60%);
        opacity: 0;
        pointer-events: none;
    }

    .id-badge-top {
        position: relative;
        padding: 26px 20px 64px;
        background:
            radial-gradient(circle at 85% 0%, rgba(201, 168, 76, 0.35), transparent 55%),
            linear-gradient(135deg, #0d1f33 0%, #1a3a5c 60%, #2a5a8c 100%);
        color: #fff;
    }

    .id-badge-slot {
        position: absolute;
        top: 10px;
        left: 50%;
        width: 46px;
        height: 8px;
        transform: translateX(-50%);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.25);
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.35);
    }

    .id-badge-brand {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 4px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .id-badge-brand img {
        width: 22px;
        height: 22px;
        border-radius: 50%;
    }

    .id-badge-brand span {
        color: #e8d4a0;
    }

    .id-badge-photo {
        position: relative;
        display: block;
        width: 128px;
        height: 128px;
        margin: -58px auto 0;
        border-radius: 50%;
        /* Its own transition keeps the site-wide card hover off the photo. */
        transition: box-shadow 0.25s ease;
    }

    .id-badge-photo::before {
        content: '';
        position: absolute;
        inset: -5px;
        border-radius: 50%;
        background: conic-gradient(from 0deg, #c9a84c, #f3e2b0, #c9a84c, #8a6d22, #c9a84c);
    }

    .id-badge-photo-inner {
        position: absolute;
        inset: 0;
        overflow: hidden;
        border-radius: 50%;
        border: 4px solid #fff;
        background: linear-gradient(135deg, #c9a84c, #e8d4a0);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0d1f33;
        font-size: 40px;
        font-weight: 700;
        font-family: 'Playfair Display', serif;
    }

    .id-badge-photo-inner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .id-badge-photo-hint {
        position: absolute;
        inset: 4px;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        background: rgba(13, 31, 51, 0.6);
        color: #fff;
        font-size: 11px;
        font-weight: 600;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .id-badge-photo-hint i {
        font-size: 18px;
    }

    .id-badge-photo:hover .id-badge-photo-hint,
    .id-badge-photo:focus-visible .id-badge-photo-hint {
        opacity: 1;
    }

    .id-badge-body {
        padding: 16px 22px 18px;
    }

    .id-badge-name {
        margin: 0;
        font-family: 'Playfair Display', serif;
        font-size: 22px;
        line-height: 1.25;
        color: #0d1f33;
        overflow-wrap: anywhere;
    }

    .id-badge-role {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 8px;
        padding: 4px 12px;
        border-radius: 999px;
        background: rgba(201, 168, 76, 0.16);
        border: 1px solid rgba(201, 168, 76, 0.45);
        color: #8a6d22;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .id-badge-details {
        display: grid;
        gap: 9px;
        margin: 16px 0 0;
        padding: 14px 0 0;
        border-top: 1px dashed #e5e7eb;
        text-align: left;
        font-size: 12.5px;
        color: #374151;
    }

    .id-badge-details > div {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        min-width: 0;
    }

    .id-badge-details > div > i {
        width: 14px;
        margin-top: 2px;
        flex-shrink: 0;
        text-align: center;
        color: #c9a84c;
    }

    .id-badge-details > div > span {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .id-badge-muted {
        color: #9ca3af;
    }

    .id-badge-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 22px;
        background: #f8fafc;
        border-top: 1px solid #eef0f3;
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #6b7280;
    }

    .id-badge-barcode {
        width: 92px;
        height: 22px;
        flex-shrink: 0;
        background: repeating-linear-gradient(
            90deg,
            #0d1f33 0 2px,
            transparent 2px 4px,
            #0d1f33 4px 5px,
            transparent 5px 8px,
            #0d1f33 8px 11px,
            transparent 11px 12px
        );
        opacity: 0.75;
    }

    @media (prefers-reduced-motion: no-preference) {
        .badge-swing {
            animation:
                badge-arrive 1.6s cubic-bezier(0.25, 0.8, 0.3, 1) both,
                badge-sway 7s ease-in-out 1.6s infinite;
        }

        .badge-stage:hover .badge-swing {
            animation-play-state: running, paused;
        }

        .id-badge-photo::before {
            animation: badge-ring 9s linear infinite;
        }

        .badge-stage:hover .id-badge::after {
            animation: badge-shine 1.1s ease forwards;
        }

        @keyframes badge-arrive {
            0% { opacity: 0; transform: translateY(-36px) rotate(-9deg); }
            30% { opacity: 1; transform: translateY(0) rotate(6deg); }
            52% { transform: rotate(-3.5deg); }
            72% { transform: rotate(1.8deg); }
            88% { transform: rotate(-0.6deg); }
            100% { opacity: 1; transform: rotate(0); }
        }

        @keyframes badge-sway {
            0%, 100% { transform: rotate(0); }
            25% { transform: rotate(1.1deg); }
            75% { transform: rotate(-1.1deg); }
        }

        @keyframes badge-ring {
            to { transform: rotate(1turn); }
        }

        @keyframes badge-shine {
            0% { opacity: 1; transform: translateX(-60%); }
            100% { opacity: 1; transform: translateX(60%); }
        }
    }

    @media (max-width: 1024px) {
        .profile-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        .badge-column {
            position: static;
            order: -1;
            width: 100%;
            max-width: 340px;
            margin: 0 auto;
        }
    }

    @media (max-width: 768px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }

        .panel-full {
            grid-column: 1;
        }

        .profile-header h1 {
            font-size: 24px;
        }

        .profile-header-left {
            align-items: flex-start;
        }

        .profile-header-avatar {
            width: 60px;
            height: 60px;
            font-size: 22px;
        }

        .completion-card .top .percentage {
            font-size: 22px;
        }

        .action-buttons {
            width: 100%;
        }

        .action-buttons .btn {
            flex: 1;
            justify-content: center;
        }
    }
</style>

@php
    $badgePicture = $user->profile?->profile_picture
        ? asset('storage/'.ltrim($user->profile->profile_picture, '/'))
        : null;

    $badgeInitials = collect(preg_split('/\s+/', trim($user->name ?? '')))
        ->filter()
        ->take(2)
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('') ?: 'S';

    $badgePhone = $user->profile?->phone;

    if ($badgePhone) {
        try {
            $badgePhone = (new \Propaganistas\LaravelPhone\PhoneNumber($badgePhone))->formatInternational();
        } catch (\Throwable $exception) {
            $badgePhone = $user->profile->phone;
        }
    }

    $skillOptions = $skillOptions ?? [];
    $interestOptions = $interestOptions ?? [];

    $predefinedSkills = $user->competencies
        ->filter(
            fn ($skill) => in_array(
                $skill->skill_name,
                $skillOptions,
                true
            )
        );

    $additionalSkills = $user->competencies
        ->filter(
            fn ($skill) => ! in_array(
                $skill->skill_name,
                $skillOptions,
                true
            )
        );

    $predefinedInterests = $user->interests
        ->filter(
            fn ($interest) => in_array(
                $interest->interest_name,
                $interestOptions,
                true
            )
        );

    $additionalInterests = $user->interests
        ->filter(
            fn ($interest) => ! in_array(
                $interest->interest_name,
                $interestOptions,
                true
            )
        );
@endphp

<div class="profile-page">
    <div class="container">

        <div class="profile-header">
            <div>
                <h1>My <span>Profile</span></h1>
                <p class="subtitle">
                    Your student badge, academic record, skills and career goals
                </p>
            </div>

            <div class="action-buttons">
                <a
                    href="{{ route('student.profile.export') }}"
                    class="btn btn-outline btn-sm"
                    target="_blank"
                >
                    <i class="fas fa-file-pdf"></i>
                    Download PDF
                </a>

                <a
                    href="{{ route('student.profile.edit') }}"
                    class="btn btn-primary btn-sm"
                >
                    <i class="fas fa-edit"></i>
                    Edit Profile
                </a>
            </div>
        </div>

        <div class="profile-layout">
            <div class="profile-main">

                <!-- Completion Card -->
                <div class="completion-card">
                    <div class="top">
                        <span class="label">
                            Profile Completion
                        </span>

                        <span class="percentage">
                            {{ $profileCompletion ?? 0 }}%
                        </span>
                    </div>

                    <div class="bar">
                        <div
                            class="fill"
                            style="width: {{ $profileCompletion ?? 0 }}%"
                        ></div>
                    </div>

                    <p class="note">
                        @if(($profileCompletion ?? 0) < 100)
                            Complete your profile to get better career recommendations
                        @else
                            <i class="fas fa-circle-check" style="color:var(--success);"></i> Your profile is complete!
                        @endif
                    </p>
                </div>

                <div class="profile-grid">

                    <!-- Academic Information -->
                    <div class="panel panel-full">
                        <div class="panel-header">
                            <h3>
                                <i class="fas fa-graduation-cap"></i>
                                Academic Information
                            </h3>
                        </div>

                        <div class="fact-tiles">
                            <div class="fact-tile">
                                <i class="fas fa-award"></i>
                                <span class="fact-label">CGPA</span>
                                <span class="fact-value">{{ $user->cgpa ?? 'Not set' }}</span>
                            </div>

                            @if($user->profile->date_of_birth ?? false)
                                <div class="fact-tile">
                                    <i class="fas fa-cake-candles"></i>
                                    <span class="fact-label">Date of Birth</span>
                                    <span class="fact-value">
                                        {{ $user->profile->date_of_birth->timezone(config('app.business_timezone'))->format('d M Y') }}
                                    </span>
                                </div>
                            @endif

                            @if($user->profile->nationality ?? false)
                                <div class="fact-tile">
                                    <i class="fas fa-flag"></i>
                                    <span class="fact-label">Nationality</span>
                                    <span class="fact-value">{{ $user->profile->nationality }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Skills -->
                    <div class="panel">
                        <div class="panel-header">
                            <h3>
                                <i class="fas fa-tools"></i>
                                Skills &amp; Competencies
                            </h3>
                        </div>

                        @if(
                            $predefinedSkills->isNotEmpty()
                            || $additionalSkills->isNotEmpty()
                        )
                            @if($predefinedSkills->isNotEmpty())
                                <div class="tags">
                                    @foreach($predefinedSkills as $skill)
                                        <span class="tag tag-blue">
                                            {{ $skill->skill_name }}

                                            <small>
                                                ({{ $skill->proficiency_level }})
                                            </small>
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            @if($additionalSkills->isNotEmpty())
                                <div class="additional-group">
                                    <div class="additional-group-title">
                                        <i class="fas fa-plus"></i>
                                        Additional Skills
                                    </div>

                                    <div class="tags">
                                        @foreach($additionalSkills as $skill)
                                            <span class="tag tag-additional-skill">
                                                {{ $skill->skill_name }}

                                                <small>
                                                    ({{ $skill->proficiency_level }})
                                                </small>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @else
                            <p class="empty-text">
                                No skills added yet
                            </p>
                        @endif
                    </div>

                    <!-- Interests -->
                    <div class="panel">
                        <div class="panel-header">
                            <h3>
                                <i class="fas fa-heart"></i>
                                Interests
                            </h3>
                        </div>

                        @if(
                            $predefinedInterests->isNotEmpty()
                            || $additionalInterests->isNotEmpty()
                        )
                            @if($predefinedInterests->isNotEmpty())
                                <div class="tags">
                                    @foreach($predefinedInterests as $interest)
                                        <span class="tag tag-rose">
                                            {{ $interest->interest_name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            @if($additionalInterests->isNotEmpty())
                                <div class="additional-group">
                                    <div class="additional-group-title">
                                        <i class="fas fa-plus"></i>
                                        Additional Interests
                                    </div>

                                    <div class="tags">
                                        @foreach($additionalInterests as $interest)
                                            <span class="tag tag-additional-interest">
                                                {{ $interest->interest_name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @else
                            <p class="empty-text">
                                No interests added yet
                            </p>
                        @endif
                    </div>

                    <!-- Projects -->
                    <div class="panel panel-full">
                        <div class="panel-header">
                            <h3>
                                <i class="fas fa-project-diagram"></i>
                                Projects &amp; Experience
                            </h3>
                        </div>

                        @if($user->projects && $user->projects->count() > 0)
                            <div class="project-grid">
                                @foreach($user->projects as $project)
                                    <div class="project-card">
                                        <h4>{{ $project->title }}</h4>

                                        @if($project->role)
                                            <span class="role">
                                                <i class="fas fa-user-tag"></i>
                                                {{ $project->role }}
                                            </span>
                                        @endif

                                        @if($project->description)
                                            <p class="desc">
                                                {{ Str::limit(
                                                    $project->description,
                                                    80
                                                ) }}
                                            </p>
                                        @endif

                                        @if(
                                            $project->technologies_used
                                            && count(
                                                $project->technologies_used
                                            ) > 0
                                        )
                                            <div class="tech-tags">
                                                @foreach(
                                                    $project->technologies_used
                                                    as $tech
                                                )
                                                    <span>{{ $tech }}</span>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if($project->achievements)
                                            <div class="achievement">
                                                <i class="fas fa-trophy"></i>
                                                {{ $project->achievements }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="empty-text">
                                No projects added yet
                            </p>
                        @endif
                    </div>

                    <!-- Certifications -->
                    <div class="panel panel-full">
                        <div class="panel-header">
                            <h3>
                                <i class="fas fa-certificate"></i>
                                Certifications
                            </h3>
                        </div>

                        @if(
                            $user->certifications
                            && $user->certifications->count() > 0
                        )
                            <div style="display:grid;gap:10px;">
                                @foreach($user->certifications as $cert)
                                    <div class="cert-card">
                                        <div>
                                            <div class="cert-name">
                                                {{ $cert->certification_name }}
                                            </div>

                                            <div class="cert-org">
                                                {{ $cert->issuing_organization ?? 'Unknown' }}
                                            </div>
                                        </div>

                                        <div style="text-align:right;">
                                            @if($cert->issue_date)
                                                <div class="cert-date">
                                                    Issued:
                                                    {{ $cert->issue_date->timezone(config('app.business_timezone'))->format('d M Y') }}
                                                </div>
                                            @endif

                                            @if($cert->certificate_file_path)
                                                <span class="cert-badge">
                                                    <i class="fas fa-paperclip"></i>
                                                    Evidence uploaded
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="empty-text">
                                No certifications added yet
                            </p>
                        @endif
                    </div>

                    <!-- Aspirations -->
                    <div class="panel panel-full">
                        <div class="panel-header">
                            <h3>
                                <i class="fas fa-star"></i>
                                Career Aspirations
                            </h3>
                        </div>

                        @if($user->aspirations)
                            <div style="display:grid;gap:10px;">
                                @if(
                                    $user->aspirations->career_goals
                                    && count(
                                        $user->aspirations->career_goals
                                    ) > 0
                                )
                                    <div class="info-row">
                                        <span class="label">
                                            Dream Career
                                        </span>

                                        <span class="value">
                                            {{ $user->aspirations->career_goals[0]
                                                ?? 'Not set' }}
                                        </span>
                                    </div>
                                @endif

                                @if($user->aspirations->vision_statement)
                                    <div class="info-row">
                                        <span class="label">
                                            Vision Statement
                                        </span>

                                        <span class="value">
                                            {{ $user->aspirations->vision_statement }}
                                        </span>
                                    </div>
                                @endif

                                @if($user->aspirations->long_term_goals)
                                    <div class="info-row">
                                        <span class="label">
                                            Long Term Goals
                                        </span>

                                        <span class="value">
                                            {{ $user->aspirations->long_term_goals }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="empty-text">
                                No career aspirations set yet
                            </p>
                        @endif
                    </div>

                </div>

            </div>

            <!-- Student badge: stays in view while the details scroll -->
            <aside class="badge-column" aria-label="Your student badge">
                <div class="badge-stage" id="badgeStage">
                    <div class="badge-lanyard" aria-hidden="true"></div>

                    <div class="badge-swing">
                        <div class="badge-clip" aria-hidden="true"></div>

                        <div class="badge-tilt" id="badgeTilt">
                            <div class="id-badge">
                                <div class="id-badge-top">
                                    <div class="id-badge-slot" aria-hidden="true"></div>
                                    <div class="id-badge-brand">
                                        @if(file_exists(public_path('images/careerpath-badge.png')))
                                            <img src="{{ asset('images/careerpath-badge.png') }}" alt="">
                                        @endif
                                        CareerPath <span>BN</span>
                                    </div>
                                </div>

                                <a
                                    href="{{ route('student.profile.edit') }}"
                                    class="id-badge-photo"
                                    title="Change your picture in Edit Profile"
                                >
                                    <span class="id-badge-photo-inner">
                                        @if($badgePicture)
                                            <img src="{{ $badgePicture }}" alt="{{ $user->name }} profile picture">
                                        @else
                                            {{ $badgeInitials }}
                                        @endif
                                    </span>
                                    <span class="id-badge-photo-hint" aria-hidden="true">
                                        <i class="fas fa-camera"></i>
                                        Change
                                    </span>
                                </a>

                                <div class="id-badge-body">
                                    <h2 class="id-badge-name">{{ $user->name ?? 'Not set' }}</h2>
                                    <span class="id-badge-role">
                                        <i class="fas fa-user-graduate"></i> Student
                                    </span>

                                    <div class="id-badge-details">
                                        <div>
                                            <i class="fas fa-envelope" title="Email"></i>
                                            <span>{{ $user->email ?? 'Not set' }}</span>
                                        </div>
                                        <div>
                                            <i class="fas fa-id-card" title="Student ID"></i>
                                            <span><x-student-id-status :student="$user" /></span>
                                        </div>
                                        <div>
                                            <i class="fas fa-book-open" title="Programme"></i>
                                            @if($user->programme)
                                                <span>{{ $user->programme }}</span>
                                            @else
                                                <span class="id-badge-muted">Programme not set</span>
                                            @endif
                                        </div>
                                        <div>
                                            <i class="fas fa-phone" title="Phone"></i>
                                            @if($badgePhone)
                                                <span>{{ $badgePhone }}</span>
                                            @else
                                                <span class="id-badge-muted">No phone added</span>
                                            @endif
                                        </div>
                                        <div>
                                            <i class="fas fa-calendar-check" title="Member since"></i>
                                            <span>Member since {{ $user->created_at?->format('F Y') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="id-badge-foot">
                                    <span>Politeknik Brunei</span>
                                    <span class="id-badge-barcode" aria-hidden="true"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <script>
                (function () {
                    /* Keep the badge just below the sticky top bar. */
                    var nav = document.querySelector('.site-nav');

                    var setOffset = function () {
                        document.documentElement.style.setProperty(
                            '--badge-top',
                            ((nav ? nav.offsetHeight : 0) + 20) + 'px'
                        );
                    };

                    setOffset();
                    window.addEventListener('resize', setOffset);

                    /* Gentle tilt that follows the pointer. */
                    var stage = document.getElementById('badgeStage');
                    var tilt = document.getElementById('badgeTilt');
                    var canTilt = window.matchMedia
                        && window.matchMedia('(hover: hover) and (pointer: fine)').matches
                        && ! window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                    if (! stage || ! tilt || ! canTilt) {
                        return;
                    }

                    var frame = null;

                    stage.addEventListener('pointermove', function (event) {
                        var box = tilt.getBoundingClientRect();
                        var x = (event.clientX - box.left) / box.width - 0.5;
                        var y = (event.clientY - box.top) / box.height - 0.5;

                        if (frame) {
                            cancelAnimationFrame(frame);
                        }

                        frame = requestAnimationFrame(function () {
                            tilt.style.transform =
                                'rotateY(' + (x * 10).toFixed(2) + 'deg) rotateX(' + (-y * 8).toFixed(2) + 'deg)';
                        });
                    });

                    stage.addEventListener('pointerleave', function () {
                        if (frame) {
                            cancelAnimationFrame(frame);
                        }

                        tilt.style.transform = '';
                    });
                })();
            </script>

        </div>

    </div>
</div>

@endsection