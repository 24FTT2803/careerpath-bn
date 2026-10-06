@extends('admin.layouts.admin')

@section('title', 'Settings')

@section('content')
<style>
    .settings-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 20px;
        max-width: 860px;
    }

    .settings-layout > .card {
        margin-bottom: 0;
    }

    .settings-avatar-row {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
        padding-bottom: 20px;
        margin-bottom: 20px;
        border-bottom: 1px solid #f3f4f6;
    }

    .settings-avatar {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        flex-shrink: 0;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #c9a84c, #e8d4a0);
        color: #0d1f33;
        font-size: 34px;
        font-weight: 700;
        box-shadow: 0 0 0 4px #faf7ee;
    }

    .settings-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .settings-avatar-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .settings-avatar-actions input[type="file"] {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .settings-remove {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: var(--text-muted);
        cursor: pointer;
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
    .settings-layout .iti {
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

    @media (max-width: 768px) {
        .settings-avatar-row {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="page-header">
    <div>
        <h1><i class="fas fa-gear"></i> Settings</h1>
        <p class="subtitle">Manage your profile, password and account</p>
    </div>
</div>

<div class="settings-layout">

    <!-- Profile -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-user"></i> Profile</h3>
            <p class="card-note">How you appear to admins and in the class lists you teach.</p>
        </div>

        <form
            method="POST"
            action="{{ route('lecturer.settings.profile') }}"
            enctype="multipart/form-data"
        >
            @csrf
            @method('PUT')

            <div class="settings-avatar-row">
                <div class="settings-avatar" id="avatarPreview">
                    @if($user->avatar)
                        <img src="{{ asset('storage/'.ltrim($user->avatar, '/')) }}" alt="{{ $user->name }} profile picture">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </div>

                <div class="settings-avatar-actions">
                    <div>
                        <label for="avatarInput" class="btn btn-outline btn-sm" style="cursor:pointer;">
                            <i class="fas fa-camera"></i>
                            {{ $user->avatar ? 'Change picture' : 'Upload picture' }}
                        </label>

                        <input
                            type="file"
                            id="avatarInput"
                            name="avatar"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        >
                    </div>

                    <span class="field-hint" style="margin-top:0;">JPG, PNG or WebP, up to 5 MB.</span>

                    @if($user->avatar)
                        <label class="settings-remove">
                            <input type="checkbox" name="remove_avatar" value="1">
                            Remove current picture
                        </label>
                    @endif

                    @error('avatar')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

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
                    <i class="fas fa-check"></i> Save profile
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

<script>
    (function () {
        /* Show the chosen picture straight away. */
        var input = document.getElementById('avatarInput');
        var preview = document.getElementById('avatarPreview');

        if (input && preview) {
            input.addEventListener('change', function () {
                var file = input.files && input.files[0];

                if (! file || ! file.type.match(/^image\//)) {
                    return;
                }

                var reader = new FileReader();

                reader.onload = function (event) {
                    preview.innerHTML = '';

                    var image = document.createElement('img');
                    image.src = event.target.result;
                    image.alt = 'New profile picture';
                    preview.appendChild(image);
                };

                reader.readAsDataURL(file);
            });
        }

        /* Ask once more before deleting the account. */
        var form = document.getElementById('deleteAccountForm');

        if (form) {
            form.addEventListener('submit', function (event) {
                if (form.dataset.approved === '1') {
                    return;
                }

                event.preventDefault();

                var approve = function () {
                    form.dataset.approved = '1';
                    form.requestSubmit();
                };

                var message = 'Delete your account permanently? You will be signed out and this cannot be undone.';

                if (typeof window.showConfirmModal !== 'function') {
                    if (window.confirm(message)) {
                        approve();
                    }

                    return;
                }

                window.showConfirmModal({
                    title: 'Delete Account',
                    message: message,
                    confirmText: 'Yes, Delete',
                    cancelText: 'Cancel',
                    type: 'danger',
                    onConfirm: approve,
                });
            });
        }
    })();
</script>
@endsection
