@extends('admin.layouts.admin')

@section('title', 'My Profile')

@section('content')
@php
    $avatarUrl = $user->avatar ? asset('storage/'.ltrim($user->avatar, '/')) : null;
    $initials = collect(preg_split('/\s+/', trim($user->name)))
        ->filter()
        ->reject(fn ($word) => in_array(strtolower(rtrim($word, '.')), ['dr', 'sir', 'mr', 'mrs', 'ms', 'cik', 'awg', 'dyg', 'pg', 'hj', 'hjh']))
        ->take(2)
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('') ?: mb_strtoupper(mb_substr($user->name, 0, 1));

    $badgePhone = $user->phone;

    if ($badgePhone) {
        try {
            $badgePhone = (new \Propaganistas\LaravelPhone\PhoneNumber($user->phone))->formatInternational();
        } catch (\Throwable $exception) {
            $badgePhone = $user->phone;
        }
    }
@endphp

<style>
    /* ============================================
       PAGE LAYOUT: forms on the left, badge on the right
       ============================================ */
    .profile-page {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 28px;
        align-items: start;
        max-width: 1240px;
    }

    .profile-forms {
        display: grid;
        gap: 20px;
        min-width: 0;
    }

    .profile-forms > .card {
        margin-bottom: 0;
    }

    .settings-readonly {
        background: #f9fafb;
        color: var(--text-muted);
        cursor: not-allowed;
    }

    .settings-footer {
        display: flex;
        justify-content: flex-end;
        padding-top: 4px;
    }

    /* The country picker wraps the phone box; let it fill the column. */
    .profile-forms .iti {
        display: block;
        width: 100%;
    }

    .field-error {
        color: var(--danger);
        font-size: 12px;
        margin-top: 4px;
    }

    .danger-card {
        border-color: #f3d2ce;
    }

    .danger-card .card-header h3,
    .danger-card .card-header h3 i {
        color: var(--danger);
    }

    .danger-row {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .danger-row > div {
        flex: 1 1 260px;
    }

    .btn-danger {
        background: var(--danger);
        color: white;
        border: 0;
    }

    .btn-danger:hover {
        background: #a93226;
        transform: translateY(-2px);
    }

    /* ============================================
       LECTURER BADGE
       ============================================ */
    .badge-column {
        position: sticky;
        top: 24px;
        display: grid;
        gap: 16px;
    }

    .badge-stage {
        position: relative;
        padding-top: 64px;
        perspective: 900px;
    }

    /* Lanyard strap and clip */
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
        background: repeating-linear-gradient(
            180deg,
            #1a3a5c 0 10px,
            #23497a 10px 20px
        );
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
        box-shadow:
            0 24px 48px -24px rgba(13, 31, 51, 0.45),
            0 2px 6px rgba(13, 31, 51, 0.08);
        text-align: center;
    }

    /* Moving light across the card on hover */
    .id-badge::after {
        content: '';
        position: absolute;
        inset: -40% -60%;
        background: linear-gradient(
            105deg,
            transparent 40%,
            rgba(255, 255, 255, 0.55) 50%,
            transparent 60%
        );
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
        cursor: pointer;
    }

    /* Slowly turning gold ring */
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
        color: var(--primary-dark, #0d1f33);
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

    .id-badge-details i {
        width: 14px;
        margin-top: 2px;
        flex-shrink: 0;
        text-align: center;
        color: #c9a84c;
    }

    .id-badge-details span {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .id-badge-muted {
        color: #9ca3af;
    }

    .id-badge-classes {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .id-badge-classes b {
        padding: 2px 8px;
        border-radius: 6px;
        background: #eef2f7;
        color: #1a3a5c;
        font-size: 11px;
        font-weight: 600;
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

    /* Decorative barcode strip */
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

    /* ---------- Picture controls under the badge ---------- */
    .badge-controls {
        margin-bottom: 0;
        padding: 18px 20px;
    }

    .badge-controls h3 {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 4px;
        font-size: 14px;
        color: var(--primary);
    }

    .badge-controls h3 i {
        color: #c9a84c;
    }

    .badge-controls p {
        margin: 0 0 14px;
        font-size: 12px;
        color: var(--text-muted);
    }

    .badge-controls-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .badge-controls-row .btn {
        flex: 1 1 auto;
        justify-content: center;
    }

    .badge-controls input[type="file"] {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .badge-file-name {
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0 0 10px;
        font-size: 12px;
        color: var(--primary);
        overflow-wrap: anywhere;
    }

    .badge-controls [hidden] {
        display: none !important;
    }

    .btn-ghost-danger {
        background: transparent;
        color: var(--danger);
        border: 1px solid #f3d2ce;
    }

    .btn-ghost-danger:hover {
        background: #fdf1ef;
    }

    /* ============================================
       BADGE MOTION
       ============================================ */
    @media (prefers-reduced-motion: no-preference) {
        /* Swings in on arrival, then sways gently. */
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

    @media (max-width: 1100px) {
        .profile-page {
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
</style>

<div class="page-header">
    <div>
        <h1><i class="fas fa-id-badge"></i> My Profile</h1>
        <p class="subtitle">Your lecturer badge, contact details, password and account</p>
    </div>
</div>

<div class="profile-page">

    <div class="profile-forms">

        <!-- Details -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-user"></i> Details</h3>
                <p class="card-note">How you appear to admins and in the class lists you teach.</p>
            </div>

            <form method="POST" action="{{ route('lecturer.settings.profile') }}">
                @csrf
                @method('PUT')

                <div class="field-grid field-grid-2">
                    <div>
                        <label class="field-label" for="name">Full name</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            maxlength="255"
                            required
                            autocomplete="name"
                            class="field-input"
                        >
                        @error('name')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="cpbn-field">
                        <label class="field-label" for="phone">Phone (optional)</label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            value="{{ old('phone', $user->phone) }}"
                            maxlength="30"
                            autocomplete="tel"
                            class="field-input"
                        >
                        <input
                            type="hidden"
                            id="phone_country"
                            name="phone_country"
                            value="{{ old('phone_country', 'BN') }}"
                        >
                        <p class="field-hint">Choose the country, then enter the number. Brunei (+673) is selected by default.</p>
                        @error('phone')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                        @error('phone_country')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="field-grid">
                    <div>
                        <label class="field-label">Email</label>
                        <input type="email" value="{{ $user->email }}" class="field-input settings-readonly" disabled>
                        <p class="field-hint">Your sign-in email. Ask an administrator if it needs to change.</p>
                    </div>
                </div>

                <div class="settings-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Save details
                    </button>
                </div>
            </form>
        </div>

        <!-- Password -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-lock"></i> Password</h3>
                <p class="card-note">Use at least 8 characters.</p>
            </div>

            <form method="POST" action="{{ route('lecturer.settings.password') }}">
                @csrf
                @method('PUT')

                <div class="field-grid">
                    <div>
                        <label class="field-label" for="current_password">Current password</label>
                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            required
                            autocomplete="current-password"
                            class="field-input"
                        >
                        @error('current_password')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="field-grid field-grid-2">
                    <div>
                        <label class="field-label" for="password">New password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            class="field-input"
                        >
                        @error('password')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="field-label" for="password_confirmation">Confirm new password</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            class="field-input"
                        >
                    </div>
                </div>

                <div class="settings-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key"></i> Change password
                    </button>
                </div>
            </form>
        </div>

        <!-- Delete account -->
        <div class="card danger-card">
            <div class="card-header">
                <h3><i class="fas fa-triangle-exclamation"></i> Delete account</h3>
                <p class="card-note">
                    Permanently removes your account and your class assignments.
                    Your students and their records are not affected. This cannot be undone.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('lecturer.settings.destroy') }}"
                id="deleteAccountForm"
            >
                @csrf
                @method('DELETE')

                <div class="danger-row">
                    <div>
                        <label class="field-label" for="delete_password">Confirm with your password</label>
                        <input
                            type="password"
                            id="delete_password"
                            name="delete_password"
                            required
                            autocomplete="current-password"
                            class="field-input"
                        >
                        @error('delete_password', 'deleteAccount')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Delete my account
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lecturer badge -->
    <aside class="badge-column" aria-label="Your lecturer badge">
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

                        <label for="avatarInput" class="id-badge-photo" title="Change picture" tabindex="0">
                            <span class="id-badge-photo-inner" id="badgePhoto">
                                @if($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="{{ $user->name }} profile picture">
                                @else
                                    {{ $initials }}
                                @endif
                            </span>
                            <span class="id-badge-photo-hint" aria-hidden="true">
                                <i class="fas fa-camera"></i>
                                Change
                            </span>
                        </label>

                        <div class="id-badge-body">
                            <h2 class="id-badge-name">{{ $user->name }}</h2>
                            <span class="id-badge-role">
                                <i class="fas fa-chalkboard-user"></i> Lecturer
                            </span>

                            <div class="id-badge-details">
                                <div>
                                    <i class="fas fa-envelope"></i>
                                    <span>{{ $user->email }}</span>
                                </div>
                                <div>
                                    <i class="fas fa-phone"></i>
                                    @if($badgePhone)
                                        <span>{{ $badgePhone }}</span>
                                    @else
                                        <span class="id-badge-muted">No phone added</span>
                                    @endif
                                </div>
                                <div>
                                    <i class="fas fa-people-group"></i>
                                    @if($classes->isNotEmpty())
                                        <span class="id-badge-classes">
                                            @foreach($classes->take(6) as $class)
                                                <b>{{ $class->code ?: $class->name }}</b>
                                            @endforeach
                                            @if($classes->count() > 6)
                                                <b>+{{ $classes->count() - 6 }}</b>
                                            @endif
                                        </span>
                                    @else
                                        <span class="id-badge-muted">No classes assigned yet</span>
                                    @endif
                                </div>
                                <div>
                                    <i class="fas fa-calendar-check"></i>
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

        <!-- Picture controls (kept still, outside the swinging badge) -->
        <div class="card badge-controls">
            <h3><i class="fas fa-camera"></i> Profile picture</h3>
            <p>JPG, PNG or WebP, up to 5 MB. Click the photo or the button below.</p>

            <form
                method="POST"
                action="{{ route('lecturer.settings.avatar') }}"
                enctype="multipart/form-data"
                id="avatarForm"
            >
                @csrf
                @method('PUT')

                <input
                    type="file"
                    id="avatarInput"
                    name="avatar"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                >

                <p class="badge-file-name" id="avatarFileName" hidden>
                    <i class="fas fa-image"></i> <span></span>
                </p>

                <div class="badge-controls-row" id="avatarIdleActions">
                    <label for="avatarInput" class="btn btn-outline btn-sm" style="cursor:pointer;">
                        <i class="fas fa-upload"></i>
                        {{ $avatarUrl ? 'Change picture' : 'Upload picture' }}
                    </label>

                    @if($avatarUrl)
                        <button type="button" class="btn btn-ghost-danger btn-sm" id="removeAvatarButton">
                            <i class="fas fa-trash-alt"></i> Remove
                        </button>
                    @endif
                </div>

                <div class="badge-controls-row" id="avatarPendingActions" hidden>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-check"></i> Save picture
                    </button>
                    <button type="button" class="btn btn-outline btn-sm" id="avatarCancel">
                        Cancel
                    </button>
                </div>

                @error('avatar')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </form>

            @if($avatarUrl)
                <form
                    method="POST"
                    action="{{ route('lecturer.settings.avatar') }}"
                    id="removeAvatarForm"
                    hidden
                >
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="remove_avatar" value="1">
                </form>
            @endif
        </div>
    </aside>
</div>

<script>
    (function () {
        var confirmThen = function (options, action) {
            if (typeof window.showConfirmModal !== 'function') {
                if (window.confirm(options.message)) {
                    action();
                }

                return;
            }

            options.onConfirm = action;
            window.showConfirmModal(options);
        };

        /* ---------- Profile picture: preview, save or cancel ---------- */
        var input = document.getElementById('avatarInput');
        var photo = document.getElementById('badgePhoto');
        var fileName = document.getElementById('avatarFileName');
        var idleActions = document.getElementById('avatarIdleActions');
        var pendingActions = document.getElementById('avatarPendingActions');
        var cancel = document.getElementById('avatarCancel');
        var original = photo ? photo.innerHTML : '';

        var resetPicture = function () {
            input.value = '';
            photo.innerHTML = original;
            fileName.hidden = true;
            pendingActions.hidden = true;
            idleActions.hidden = false;
        };

        if (input && photo) {
            input.addEventListener('change', function () {
                var file = input.files && input.files[0];

                if (! file) {
                    resetPicture();
                    return;
                }

                fileName.querySelector('span').textContent = file.name;
                fileName.hidden = false;
                idleActions.hidden = true;
                pendingActions.hidden = false;

                if (! file.type.match(/^image\//)) {
                    return;
                }

                var reader = new FileReader();

                reader.onload = function (event) {
                    photo.innerHTML = '';

                    var image = document.createElement('img');
                    image.src = event.target.result;
                    image.alt = 'New profile picture';
                    photo.appendChild(image);
                };

                reader.readAsDataURL(file);
            });

            cancel.addEventListener('click', resetPicture);
        }

        /* Keyboard users can open the picker from the photo too. */
        var photoLabel = document.querySelector('.id-badge-photo');

        if (photoLabel && input) {
            photoLabel.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    input.click();
                }
            });
        }

        /* ---------- Remove picture ---------- */
        var removeButton = document.getElementById('removeAvatarButton');
        var removeForm = document.getElementById('removeAvatarForm');

        if (removeButton && removeForm) {
            removeButton.addEventListener('click', function () {
                confirmThen({
                    title: 'Remove Picture',
                    message: 'Remove your profile picture? Your initials will show instead.',
                    confirmText: 'Yes, Remove',
                    cancelText: 'Cancel',
                    type: 'danger',
                }, function () {
                    removeForm.submit();
                });
            });
        }

        /* ---------- Badge tilt that follows the pointer ---------- */
        var stage = document.getElementById('badgeStage');
        var tilt = document.getElementById('badgeTilt');
        var canTilt = window.matchMedia
            && window.matchMedia('(hover: hover) and (pointer: fine)').matches
            && ! window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (stage && tilt && canTilt) {
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
        }

        /* ---------- Ask once more before deleting the account ---------- */
        var form = document.getElementById('deleteAccountForm');

        if (form) {
            form.addEventListener('submit', function (event) {
                if (form.dataset.approved === '1') {
                    return;
                }

                event.preventDefault();

                confirmThen({
                    title: 'Delete Account',
                    message: 'Delete your account permanently? You will be signed out and this cannot be undone.',
                    confirmText: 'Yes, Delete',
                    cancelText: 'Cancel',
                    type: 'danger',
                }, function () {
                    form.dataset.approved = '1';
                    form.requestSubmit();
                });
            });
        }
    })();
</script>
@endsection
