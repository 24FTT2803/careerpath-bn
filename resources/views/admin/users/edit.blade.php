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
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
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
                @error('email')<p class="err">{{ $message }}</p>@enderror
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
                <select name="role" required>
                    <option value="student" {{ old('role', $user->role) == 'student' ? 'selected' : '' }}>Student</option>
                    <option value="lecturer" {{ old('role', $user->role) == 'lecturer' ? 'selected' : '' }}>Lecturer</option>
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')<p class="err">{{ $message }}</p>@enderror
            </div>

            <div class="cpbn-field">
                <label>Student ID</label>
                <input type="text" name="student_id" value="{{ old('student_id', $user->student_id) }}">
                @error('student_id')<p class="err">{{ $message }}</p>@enderror
            </div>

            <div class="cpbn-field" style="margin-bottom:0">
                <label>Programme</label>
                <select name="programme">
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
                </select>
            </div>

            <div class="cpbn-form-actions">
                <button type="submit" class="cpbn-btn cpbn-btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                    Update User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection