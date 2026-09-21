@extends('layouts.app')

@section('title', 'Settings')

@section('content')

<style>
    .settings-page {
        padding: 24px 0 40px;
    }

    .settings-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }

    .settings-header h1 {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: var(--primary);
    }

    .settings-header h1 span {
        color: var(--accent);
    }

    .settings-header .subtitle {
        color: var(--text-muted);
        font-size: 14px;
        margin-top: 2px;
    }

    .settings-grid {
        display: grid;
        grid-template-columns: 240px 1fr;
        gap: 24px;
        align-items: start;
    }

    /* Sidebar */
    .settings-sidebar {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 8px;
        position: sticky;
        top: 90px;
    }

    .settings-sidebar .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        margin-bottom: 8px;
        border-bottom: 1px solid var(--border);
    }

    .settings-sidebar .user-info .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 16px;
        flex-shrink: 0;
        overflow: hidden;
    }

    .settings-sidebar .user-info .avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .settings-sidebar .user-info .name {
        font-weight: 600;
        font-size: 14px;
        color: var(--primary);
    }

    .settings-sidebar .user-info .email {
        font-size: 12px;
        color: var(--text-muted);
    }

    .settings-sidebar .nav-item {
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
        text-decoration: none;
    }

    .settings-sidebar .nav-item:hover {
        background: var(--bg);
        color: var(--primary);
    }

    .settings-sidebar .nav-item.active {
        background: rgba(26, 58, 92, 0.08);
        color: var(--primary);
        font-weight: 600;
    }

    .settings-sidebar .nav-item i {
        width: 18px;
        font-size: 14px;
    }

    .settings-sidebar .nav-item.danger {
        color: var(--danger);
        margin-top: 4px;
        border-top: 1px solid var(--border);
        padding-top: 14px;
        border-radius: 0;
    }

    .settings-sidebar .nav-item.danger:hover {
        background: rgba(192, 57, 43, 0.08);
    }

    .settings-sidebar .nav-item.danger i {
        color: var(--danger);
    }

    /* Main Content */
    .settings-content {
        display: grid;
        gap: 20px;
    }

    .panel {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 24px;
    }

    .panel .panel-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
    }

    .panel .panel-title i {
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
    }

    .edit-link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 13px;
        color: var(--accent-dark);
        text-decoration: none;
        margin-top: 12px;
        transition: var(--transition);
    }

    .edit-link:hover {
        color: var(--accent);
    }

    .field {
        margin-bottom: 16px;
    }

    .field label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        letter-spacing: 0.04em;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .field input {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid var(--border);
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        transition: var(--transition);
        background: white;
    }

    .field input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(26, 58, 92, 0.08);
    }

    .field .hint {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .field .error {
        color: var(--danger);
        font-size: 12px;
        margin-top: 4px;
    }

    /*
     * Toggle switch.
     *
     * Selectors carry the label element so they outrank the
     * panel's own `.field label` rule, which is block level and
     * uppercases its text.
     */
    .field label.toggle {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        user-select: none;
        margin-bottom: 0;
        font-size: 14px;
        font-weight: 400;
        letter-spacing: normal;
        text-transform: none;
        color: var(--text);
    }

    /*
     * The control is hidden rather than removed, so it still
     * takes keyboard focus and still submits its value.
     */
    .field label.toggle input {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: 0;
        border: 0;
        opacity: 0;
        overflow: hidden;
        clip: rect(0 0 0 0);
    }

    .field label.toggle .track {
        position: relative;
        display: block;
        flex: 0 0 auto;
        width: 46px;
        height: 26px;
        border-radius: 999px;
        background: var(--danger);
        transition: background 0.2s ease;
    }

    .field label.toggle .thumb {
        position: absolute;
        top: 3px;
        left: 3px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: white;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        transition: transform 0.2s ease;
    }

    .field label.toggle input:checked + .track {
        background: var(--success);
    }

    .field label.toggle input:checked + .track .thumb {
        transform: translateX(20px);
    }

    .field label.toggle input:disabled + .track {
        opacity: 0.55;
    }

    .field label.toggle:has(input:disabled) {
        cursor: not-allowed;
    }

    .field label.toggle input:focus-visible + .track {
        box-shadow: 0 0 0 4px rgba(26, 58, 92, 0.18);
    }

    .field label.toggle .toggle-state {
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-size: 11px;
        margin-left: 6px;
    }

    .field label.toggle .toggle-state.on {
        color: var(--success);
    }

    .field label.toggle .toggle-state.off {
        color: var(--danger);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
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

    .btn-danger {
        background: var(--danger);
        color: white;
    }

    .btn-danger:hover {
        background: #a93226;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(192, 57, 43, 0.3);
    }

    .btn-sm {
        padding: 8px 16px;
        font-size: 12px;
    }

    .danger-zone {
        border: 2px solid var(--danger);
        border-radius: var(--radius);
        padding: 24px;
        margin-top: 8px;
    }

    .danger-zone .panel-title {
        color: var(--danger);
    }

    .danger-zone .panel-title i {
        color: var(--danger);
    }

    .danger-zone .warning-text {
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 16px;
        line-height: 1.6;
    }

    .danger-zone .warning-text strong {
        color: var(--danger);
    }

    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 13px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert i {
        font-size: 16px;
    }

    @media (max-width: 768px) {
        .settings-grid {
            grid-template-columns: 1fr;
        }
        .settings-sidebar {
            position: static;
        }
        .settings-header h1 {
            font-size: 24px;
        }
        .info-row {
            flex-direction: column;
            gap: 4px;
        }
    }
</style>

<div class="settings-page">
    <div class="container">

        <div class="settings-header">
            <div>
                <h1>Settings <span>⚙️</span></h1>
                <p class="subtitle">Manage your account settings and preferences</p>
            </div>
            <a href="{{ route('student.dashboard') }}" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="settings-grid">

            <!-- Sidebar -->
            <aside class="settings-sidebar">
                <div class="user-info">
                    <div class="avatar">
                        @if(Auth::user()->profile?->profile_picture)
                            <img
                                src="{{ asset(
                                    'storage/' .
                                    ltrim(
                                        Auth::user()->profile->profile_picture,
                                        '/'
                                    )
                                ) }}"
                                alt="{{ Auth::user()->name }} profile picture"
                            >
                        @else
                            {{
                                strtoupper(
                                    substr(
                                        Auth::user()->first_name
                                            ?? Auth::user()->name,
                                        0,
                                        1
                                    )
                                )
                            }}
                        @endif
                    </div>
                    <div>
                        <div class="name">{{ Auth::user()->name }}</div>
                        <div class="email">{{ Auth::user()->email }}</div>
                    </div>
                </div>

                <a href="{{ route('student.profile') }}" class="nav-item">
                    <i class="fas fa-user"></i> Profile
                </a>
                <a href="{{ route('student.settings') }}" class="nav-item active">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <a href="{{ route('student.notifications') }}" class="nav-item">
                    <i class="fas fa-bell"></i> Notifications
                </a>

                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="nav-item danger">
                        <i class="fas fa-sign-out-alt"></i> Log Out
                    </button>
                </form>
            </aside>

            <!-- Content -->
            <div class="settings-content">

                <!-- Account Information -->
                <div class="panel">
                    <div class="panel-title"><i class="fas fa-id-card"></i> Account Information</div>
                    <div class="info-row">
                        <span class="label">Full Name</span>
                        <span class="value">{{ Auth::user()->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Email</span>
                        <span class="value">{{ Auth::user()->email }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Student ID</span>
                        <span class="value">{{ Auth::user()->student_id ?? 'Not set' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Programme</span>
                        <span class="value">{{ Auth::user()->programme ?? 'Not set' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Account Created</span>
                        <span class="value">{{ Auth::user()->created_at->timezone(config('app.business_timezone'))->format('d M Y, h:i A') }}</span>
                    </div>
                    <a href="{{ route('student.profile.edit') }}" class="edit-link">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                </div>

                <!-- Plan -->
                <div class="panel">
                    <div class="panel-title">
                        <i class="fas fa-star"></i> Your Plan
                    </div>

                    <div class="info-row">
                        <span class="label">Current plan</span>
                        <span class="value">{{ $plan?->name ?? 'Free' }}</span>
                    </div>

                    @if($adsAvailable)
                        <div class="info-row">
                            <span class="label">Advertising</span>
                            <span class="value">{{ $showsAds ? 'Shown' : 'Not shown' }}</span>
                        </div>
                    @endif

                    @if(($plan?->code ?? 'free') !== 'premium')
                        <p class="hint" style="margin:16px 0;">
                            Premium lets you switch advertising off
                            and lifts the limit on generating
                            recommendations.
                            <strong>No payment is taken.</strong>
                            This stands in for a subscription so the
                            platform can be demonstrated.
                        </p>

                        <form
                            method="POST"
                            action="{{ route('student.settings.upgrade') }}"
                            onsubmit="return confirm('Upgrade to Premium? No payment will be taken.');"
                        >
                            @csrf

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-arrow-up"></i> Upgrade to Premium
                            </button>
                        </form>
                    @else
                        <p class="hint" style="margin-top:16px;">
                            You are on Premium. No payment was taken —
                            this is a demonstration of the upgrade.
                        </p>
                    @endif
                </div>

                <!-- Advertising -->
                @if($adsAvailable)
                <div class="panel">
                    <div class="panel-title">
                        <i class="fas fa-bullhorn"></i> Advertising
                    </div>

                    @if($canChooseAds)
                        <p class="hint" style="margin-bottom:16px;">
                            Your plan is ad free. You may still choose
                            to see advertisements if you would like to
                            support the platform, and you can change
                            this whenever you like.
                        </p>
                    @else
                        <p class="hint" style="margin-bottom:16px;">
                            Advertising is part of the free plan and is
                            what pays for free access, so it cannot be
                            switched off. Upgrading to Premium removes
                            it.
                        </p>
                    @endif

                    <form
                        method="POST"
                        action="{{ route('student.settings.preferences') }}"
                    >
                        @csrf
                        @method('PUT')

                        {{--
                            An unchecked box sends nothing, so
                            this carries the off value explicitly
                            rather than relying on its absence.
                        --}}
                        <input type="hidden" name="show_ads" value="0">

                        <div class="field" style="margin-bottom:20px;">
                            <label class="toggle" for="show_ads">
                                <input
                                    id="show_ads"
                                    type="checkbox"
                                    name="show_ads"
                                    value="1"
                                    @checked($showsAds)
                                    @disabled(! $canChooseAds)
                                >

                                <span class="track">
                                    <span class="thumb"></span>
                                </span>

                                <span class="toggle-text">
                                    Show me advertisements

                                    <span
                                        class="toggle-state {{ $showsAds ? 'on' : 'off' }}"
                                    >
                                        {{ $showsAds ? 'On' : 'Off' }}
                                    </span>
                                </span>
                            </label>
                        </div>

                        @if($canChooseAds)
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Preference
                            </button>
                        @endif
                    </form>
                </div>
                @endif

                <!-- Change Password -->
                <div class="panel">
                    <div class="panel-title"><i class="fas fa-lock"></i> Change Password</div>


                    @if($errors->any())
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <ul style="margin:0;padding-left:16px;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('student.settings.password') }}" data-confirm-update data-item-name="your password">
                        @csrf
                        @method('PUT')

                        <div class="field">
                            <label for="current_password">Current Password</label>
                            <input id="current_password" type="password" name="current_password" required>
                        </div>
                        <div class="field">
                            <label for="password">New Password</label>
                            <input id="password" type="password" name="password" required minlength="8">
                            <div class="hint">Minimum 8 characters</div>
                        </div>
                        <div class="field" style="margin-bottom:20px;">
                            <label for="password_confirmation">Confirm New Password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" required>
                        </div>

                        <button type="submit" class="btn btn-primary">
    <i class="fas fa-save"></i> Update Password
</button>
                    </form>
                </div>

                <!-- Delete Account -->
                <div class="panel danger-zone">
                    <div class="panel-title"><i class="fas fa-trash-alt"></i> Delete Account</div>
                    <p class="warning-text">
                        <i class="fas fa-exclamation-triangle" style="color:var(--danger);"></i>
                        Once you delete your account, all your data will be permanently removed from the system.
                        This includes your profile, career recommendations, milestones, and all associated data.
                        <strong>This action cannot be undone.</strong>
                    </p>
                    <form method="POST" action="{{ route('student.profile.destroy') }}" data-confirm-delete data-item-name="your account">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash-alt"></i> Delete Account
                        </button>
                    </form>
                </div>

            </div>

        </div>

    </div>
</div>

@endsection