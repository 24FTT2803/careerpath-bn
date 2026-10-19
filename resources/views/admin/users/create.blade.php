@extends('admin.layouts.admin')

@section('title', 'Create User')

@section('content')
<div>
    <div class="cpbn-head">
        <div>
            <h1>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                Create New User
            </h1>
            <p class="sub">Add a new user to the system</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="cpbn-btn cpbn-btn-muted">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back
        </a>
    </div>

    <div class="cpbn-card" style="max-width:640px">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <div class="cpbn-field">
                <label>Full Name <span class="req">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required>
                @error('name')<p class="err">{{ $message }}</p>@enderror
            </div>

            <div class="cpbn-field">
                <label>Email <span class="req">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required>
                <p class="cpbn-hint">
                    Allowed domains: 
                    <strong>gmail.com</strong>, 
                    <strong>pb.edu.bn</strong>, 
                    <strong>student.pb.edu.bn</strong>
                    <br>
                    <span style="color:var(--rose);font-size:11px;">
                        Students: gmail.com, student.pb.edu.bn, pb.edu.bn
                        <br>
                        Lecturers & Admins: gmail.com, pb.edu.bn
                    </span>
                </p>
                @error('email')<p class="err">{{ $message }}</p>@enderror
            </div>

            <div class="cpbn-field">
                <label>Phone Number</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" maxlength="30" autocomplete="tel">
                <input type="hidden" id="phone_country" name="phone_country" value="{{ old('phone_country', 'BN') }}">
                <p class="cpbn-hint">Choose the country, then enter the number. Brunei (+673) is selected by default.</p>
                @error('phone')<p class="err">{{ $message }}</p>@enderror
                @error('phone_country')<p class="err">{{ $message }}</p>@enderror
            </div>

            <div class="cpbn-field">
                <label>Password <span class="req">*</span></label>
                <input type="password" name="password" required minlength="8">
                @error('password')<p class="err">{{ $message }}</p>@enderror
            </div>

            <div class="cpbn-field">
                <label>Confirm Password <span class="req">*</span></label>
                <input type="password" name="password_confirmation" required>
            </div>

            <div class="cpbn-field">
                <label>Role <span class="req">*</span></label>
                <select name="role" id="role-select" required onchange="toggleStudentFields()">
                    <option value="">Select a role</option>
                    <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                    <option value="lecturer" {{ old('role') == 'lecturer' ? 'selected' : '' }}>Lecturer</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')<p class="err">{{ $message }}</p>@enderror
            </div>

            <!-- Student-specific fields (hidden by default) -->
            <div id="student-fields" style="{{ old('role') == 'student' ? 'display:block' : 'display:none' }}">
                <div class="cpbn-field">
                    <label>Student ID <span class="req">*</span></label>
                    <input type="text" name="student_id" value="{{ old('student_id') }}">
                    <p style="font-size:12px;color:#6b7280;margin-top:4px;">Format: 24FTT2803. IDs you enter here are marked verified.</p>
                    @error('student_id')<p class="err">{{ $message }}</p>@enderror
                </div>

                <div class="cpbn-field">
                    <label>Programme <span class="req">*</span></label>
                    <x-programme-select :selected="null" />
                </div>

                {{--
                    An account that cannot confirm its email.

                    Verification is asked of students created
                    since the requirement began, which is right
                    for students who sign themselves up and
                    leaves no way in for one typed in here: the
                    address may not exist, and nothing ever sends
                    it anything. Off by default, because a real
                    student should confirm.
                --}}
                <div class="cpbn-field" style="margin-bottom:0">
                    <label class="cpbn-waive">
                        <input type="checkbox" name="waive_email_confirmation" value="1"
                               @checked(old('waive_email_confirmation'))>
                        <span class="cpbn-waive-body">
                            <span class="cpbn-waive-title">Skip email confirmation</span>
                            <span class="cpbn-waive-note">
                                For demonstration accounts, and for a student whose address
                                does not exist. The address is not marked as confirmed — it
                                is recorded that you excused it, and you can withdraw that
                                later.
                            </span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="cpbn-form-actions">
                <button type="submit" class="cpbn-btn cpbn-btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>

<style>

:root {
    --ink: #0d1a2b;
    --ink-dim: #5b6675;
    --line: #e7e2d4;
    --gold: #cf9a3d;
    --gold-bright: #e9b95a;
    --font-body: 'IBM Plex Sans', ui-sans-serif, system-ui, sans-serif;
    --font-mono: 'IBM Plex Mono', ui-monospace, monospace;
    --card: #ffffff;
    --rose: #c65b4e;
}

    .cpbn-field input,
.cpbn-field select {
    width: 100%;
    padding: 10px 13px;
    border-radius: 4px;
    border: 2px solid var(--line, #e7e2d4);
    background: #ffffff;
    font-size: 14px;
    font-family: var(--font-body, 'IBM Plex Sans', sans-serif);
    color: var(--ink, #0d1a2b);
    transition: border-color 0.2s;
}

.cpbn-field input:focus,
.cpbn-field select:focus {
    outline: none;
    border-color: var(--gold, #cf9a3d);
    box-shadow: 0 0 0 3px rgba(207,154,61,0.15);
}

.cpbn-field input::placeholder {
    color: #9ca3af;
}

.cpbn-field input:disabled {
    background: #f2efe6;
    color: #6b7280;
}
    .cpbn-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:22px}
    .cpbn-head h1{font-family:var(--font-display);font-weight:600;font-size:24px;letter-spacing:-.01em;display:flex;align-items:center;gap:8px}
    .cpbn-head h1 svg{width:22px;height:22px;color:var(--gold)}
    .cpbn-head p.sub{color:var(--ink-dim);margin-top:4px;font-size:14.5px}
    .cpbn-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:5px;font-size:14px;font-weight:500;border:none;cursor:pointer;transition:background .15s;text-decoration:none;font-family:inherit}
    .cpbn-btn svg{width:14px;height:14px}
    .cpbn-btn-primary{background:var(--gold);color:var(--ink)}
    .cpbn-btn-primary:hover{background:var(--gold-bright)}
    .cpbn-btn-muted{background:#eee9db;color:var(--ink)}
    .cpbn-btn-muted:hover{background:#e4dfcd}
    .cpbn-card{background:var(--card);border:1px solid var(--line);border-radius:6px;padding:24px}
    .cpbn-field{margin-bottom:16px}
    .cpbn-field label{display:block;font-size:13px;font-weight:500;margin-bottom:6px}
    .cpbn-field label .req{color:var(--rose)}
    .cpbn-field input,.cpbn-field select{width:100%;padding:10px 13px;border-radius:4px;border:1px solid var(--line);background:#fff;font-size:14px;font-family:var(--font-body)}
    .cpbn-field input:focus,.cpbn-field select:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(207,154,61,0.15)}
    .cpbn-hint{font-size:11.5px;color:var(--ink-dim);margin-top:4px}
    .err{color:var(--rose);font-size:12px;margin-top:4px}
    /*
     * The one tick box on a form of full-width fields.
     *
     * It cannot be a bare checkbox here: .cpbn-field input gives
     * every input a 2px border and 10px of padding, which turns
     * a 13px box into a padded bordered lump. So it is a panel
     * you click, gold when it is on, and the box inside it is
     * reset back to being a box.
     */
    .cpbn-waive{display:flex;align-items:flex-start;gap:12px;padding:13px 15px;border:2px solid var(--line,#e7e2d4);border-radius:6px;background:#fff;cursor:pointer;transition:border-color .15s ease,background .15s ease}
    .cpbn-waive:hover{border-color:var(--gold,#cf9a3d)}
    .cpbn-waive:has(input:checked){border-color:var(--gold,#cf9a3d);background:rgba(207,154,61,.07)}
    /*
     * The box is drawn here rather than by the browser.
     *
     * .cpbn-field input above gives every input a background, and
     * a checkbox with a background is one the browser stops
     * drawing natively — it paints a plain square, fills it with
     * the accent colour when ticked, and draws no tick at all.
     * Which is what a black square with nothing in it was.
     *
     * So the native widget is turned off on purpose and the tick
     * is ours: a white check on gold, as a background image
     * rather than a pseudo-element, because a checkbox is a void
     * element and ::after on one is not something every browser
     * renders.
     */
    .cpbn-field .cpbn-waive input[type="checkbox"]{appearance:none;-webkit-appearance:none;width:20px;height:20px;min-width:20px;flex:0 0 auto;margin:0;padding:0;border:2px solid var(--line,#e7e2d4);border-radius:5px;background:#fff;box-shadow:none;cursor:pointer;transition:background-color .15s ease,border-color .15s ease}
    .cpbn-field .cpbn-waive input[type="checkbox"]:checked{background-color:var(--gold,#cf9a3d);border-color:var(--gold,#cf9a3d);background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='none' stroke='%23ffffff' stroke-width='2.6' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M3 8.6 L6.3 11.9 L13 5'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:center;background-size:14px 14px}
    .cpbn-field .cpbn-waive input[type="checkbox"]:focus-visible{outline:none;border-color:var(--gold,#cf9a3d);box-shadow:0 0 0 3px rgba(207,154,61,.25)}
    .cpbn-waive-body{display:flex;flex-direction:column;gap:3px}
    .cpbn-waive-title{font-size:13.5px;font-weight:600;color:var(--ink,#0d1a2b)}
    .cpbn-waive-note{font-size:11.5px;color:var(--ink-dim,#5b6675);line-height:1.55}
    .cpbn-form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:18px}
</style>

<script>
    function toggleStudentFields() {
        const roleSelect = document.getElementById('role-select');
        const studentFields = document.getElementById('student-fields');
        const studentIdInput = studentFields.querySelector('input[name="student_id"]');
        const programmeSelect = studentFields.querySelector('select[name="programme"]');
        
        if (roleSelect.value === 'student') {
            studentFields.style.display = 'block';
            studentIdInput.setAttribute('required', 'required');
            programmeSelect.setAttribute('required', 'required');
        } else {
            studentFields.style.display = 'none';
            studentIdInput.removeAttribute('required');
            programmeSelect.removeAttribute('required');
            studentIdInput.value = '';
            programmeSelect.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleStudentFields();
    });
</script>
@endsection