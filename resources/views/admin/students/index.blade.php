@extends('admin.layouts.admin')

@section('title', 'Students')

@section('content')
<div>
    <div class="cpbn-head">
        <div>
            <h1>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="3.5"/><path d="M2 21c0-3.9 3.1-7 7-7s7 3.1 7 7"/><path d="M16 4.2a3.5 3.5 0 0 1 0 6.8"/><path d="M22 21c0-3-2-5.5-4.5-6.6"/></svg>
                Students
            </h1>
            <p class="sub">View and manage student profiles (Read-Only)</p>
        </div>
        <div class="cpbn-note">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
            @if($isAdmin)
                Administrators can view all student details.
            @else
                Lecturers have read-only access.
            @endif
        </div>
    </div>

    <!-- Search and Filter -->
    <form method="GET" class="cpbn-filterbar">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email or student ID...">
        <select name="programme">
            <option value="">All Programmes</option>
            @foreach($programmes as $prog)
                <option value="{{ $prog }}" {{ request('programme') == $prog ? 'selected' : '' }}>{{ $prog }}</option>
            @endforeach
        </select>
        <button type="submit" class="cpbn-btn cpbn-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/></svg>
            Filter
        </button>
        <a href="{{ route('admin.students.index') }}" class="cpbn-btn cpbn-btn-muted">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 1 3 6.7"/><path d="M3 4v6h6"/></svg>
            Reset
        </a>
    </form>

    <!-- Students Table -->
    <div class="cpbn-table-wrap">
        <table class="cpbn-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Student ID</th>
                    <th>Programme</th>
                    <th>CGPA</th>
                    <th>Skills</th>
                    <th class="center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $index => $student)
                    <tr>
                        <td>{{ $students->firstItem() + $index }}</td>
                        <td style="font-weight:500">{{ $student->name }}</td>
                        <td>{{ $student->student_id ?? '-' }}</td>
                        <td>{{ $student->programme ?? '-' }}</td>
                        <td>{{ $student->cgpa ?? '-' }}</td>
                        <td>
                            @php
                                $skills = $student->competencies->pluck('skill_name')->toArray();
                            @endphp
                            @if(count($skills) > 0)
                                <span class="cpbn-pill pill-gold">{{ count($skills) }} skills</span>
                            @else
                                <span class="cpbn-pill pill-neutral">No skills</span>
                            @endif
                        </td>
                        <td class="center">
                            <a href="{{ route('admin.students.show', $student) }}" class="link">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;display:inline;vertical-align:-1px"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="cpbn-empty-row">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="8" r="3.5"/><path d="M2 21c0-3.9 3.1-7 7-7s7 3.1 7 7"/></svg>
                            No students found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="cpbn-pagination">
        {{ $students->withQueryString()->links() }}
    </div>
</div>
@endsection