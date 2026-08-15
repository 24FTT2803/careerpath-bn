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
                @error('email')<p class="err">{{ $message }}</p>@enderror
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
                <select name="role" required>
                    <option value="">Select a role</option>
                    <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                    <option value="lecturer" {{ old('role') == 'lecturer' ? 'selected' : '' }}>Lecturer</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')<p class="err">{{ $message }}</p>@enderror
            </div>

            <div class="cpbn-field">
                <label>Student ID</label>
                <input type="text" name="student_id" value="{{ old('student_id') }}">
                @error('student_id')<p class="err">{{ $message }}</p>@enderror
            </div>

            <div class="cpbn-field" style="margin-bottom:0">
                <label>Programme</label>
                <select name="programme">
                    <option value="">Select programme</option>
                    <option value="Diploma in ICT (Application Development)" {{ old('programme') == 'Diploma in ICT (Application Development)' ? 'selected' : '' }}>
                        DADT - Application Development
                    </option>
                    <option value="Diploma in ICT (Data Analytics)" {{ old('programme') == 'Diploma in ICT (Data Analytics)' ? 'selected' : '' }}>
                        DDAT - Data Analytics
                    </option>
                    <option value="Diploma in ICT (Cloud Networking)" {{ old('programme') == 'Diploma in ICT (Cloud Networking)' ? 'selected' : '' }}>
                        DCNG - Cloud Networking
                    </option>
                </select>
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
@endsection