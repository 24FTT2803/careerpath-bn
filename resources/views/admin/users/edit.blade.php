@extends('admin.layouts.admin')

@section('title', 'Edit User')

@section('content')
<div>
    <div class="cpbn-head">
        <div>
            <h1>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                Edit User
            </h1>
            <p class="sub">Update user information</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="cpbn-btn cpbn-btn-muted">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back
        </a>
    </div>

    <div class="cpbn-card" style="max-width:640px">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" data-confirm-update data-item-name="{{ $user->name }}">
            @csrf
            @method('PUT')

            <div class="cpbn-field">
                <label>Full Name <span class="req">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name')<p class="err">{{ $message }}</p>@enderror
            </div>

            <div class="cpbn-field">
                <label>Email <span class="req">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
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
                <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="+673 123 4567">
                <p class="cpbn-hint">Only digits, +, -, spaces, and parentheses allowed (7-20 characters)</p>
                @error('phone')<p class="err">{{ $message }}</p>@enderror
            </div>

            <div class="cpbn-field">
                <label>New Password</label>
                <input type="password" name="password" minlength="8">
                <p class="cpbn-hint">Leave blank to keep current password</p>
                @error('password')<p class="err">{{ $message }}</p>@enderror
            </div>

            <div class="cpbn-field">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation">
            </div>

            <div class="cpbn-field">
                <label>Role <span class="req">*</span></label>
                <select name="role" id="role-select" required onchange="toggleStudentFields()">
                    <option value="student" {{ old('role', $user->role) == 'student' ? 'selected' : '' }}>Student</option>
                    <option value="lecturer" {{ old('role', $user->role) == 'lecturer' ? 'selected' : '' }}>Lecturer</option>
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')<p class="err">{{ $message }}</p>@enderror
            </div>

            <!-- Student-specific fields -->
            <div id="student-fields" style="{{ old('role', $user->role) == 'student' ? 'display:block' : 'display:none' }}">
                <div class="cpbn-field">
                    <label>Student ID <span class="req">*</span></label>
                    <input type="text" name="student_id" value="{{ old('student_id', $user->student_id) }}" 
                           {{ old('role', $user->role) == 'student' ? 'required' : '' }}>
                    @error('student_id')<p class="err">{{ $message }}</p>@enderror
                </div>

                <div class="cpbn-field" style="margin-bottom:0">
                    <label>Programme <span class="req">*</span></label>
                    <select name="programme" {{ old('role', $user->role) == 'student' ? 'required' : '' }}>
                        <option value="">Select programme</option>
                        <option value="Diploma in ICT (Application Development)" {{ old('programme', $user->programme) == 'Diploma in ICT (Application Development)' ? 'selected' : '' }}>
                            DADT - Application Development
                        </option>
                        <option value="Diploma in ICT (Data Analytics)" {{ old('programme', $user->programme) == 'Diploma in ICT (Data Analytics)' ? 'selected' : '' }}>
                            DDAT - Data Analytics
                        </option>
                        <option value="Diploma in ICT (Cloud Networking)" {{ old('programme', $user->programme) == 'Diploma in ICT (Cloud Networking)' ? 'selected' : '' }}>
                            DCNG - Cloud Networking
                        </option>
                        <option value="Diploma in Business Information Systems" {{ old('programme', $user->programme) == 'Diploma in Business Information Systems' ? 'selected' : '' }}>
                            DBIS - Business Information Systems
                        </option>
                    </select>
                </div>
            </div>

            <div class="cpbn-form-actions">
                <button type="submit" class="cpbn-btn cpbn-btn-primary" data-confirm-update data-item-name="{{ $user->name }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                    Update User
                </button>
            </div>
        </form>
    </div>
</div>

<style>
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
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleStudentFields();
    });
</script>
@endsection