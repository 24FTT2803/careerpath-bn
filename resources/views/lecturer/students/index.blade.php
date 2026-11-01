@extends('admin.layouts.admin')

@section('title', 'My Students')

@section('content')
<div class="lecturer-student-list">
    <div class="page-header">
        <div>
            <h1>
                <i class="fas fa-user-graduate" style="color:#c9a84c;"></i>
                My Students
            </h1>
            <p class="subtitle">
                Students in the classes you teach
            </p>
        </div>
    </div>

    @if($assignedClasses->isEmpty())
        <div class="card">
            <div style="text-align:center;padding:48px 20px;">
                <i
                    class="fas fa-chalkboard-teacher"
                    style="font-size:48px;color:#d1d5db;"
                ></i>

                <h3 style="margin-top:16px;font-size:18px;color:#1a3a5c;">
                    You are not assigned to any classes yet
                </h3>

                <p style="color:#6b7280;margin-top:6px;">
                    Contact an administrator to be assigned to a class.
                </p>
            </div>
        </div>
    @else
        <div class="card">
            <h3 class="card-heading">
                Classes you teach
            </h3>

            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                @foreach($assignedClasses as $class)
                    <span
                        class="status-pill status-pill-blue"
                        style="padding:6px 12px;font-size:12px;"
                    >
                        <i class="fas fa-chalkboard"></i>
                        {{ $class->name }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="card">
            <h3 class="card-heading">
                Students ({{ $students->total() }})
            </h3>

            @if($students->isEmpty())
                <p class="empty-text">
                    No students in your classes yet.
                </p>
            @else
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Student ID</th>
                            <th>Programme</th>
                            <th>Class</th>
                            <th>Readiness</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td>
                                    <span class="cell-title">
                                        {{ $student->name }}
                                    </span>
                                    <span class="cell-sub">
                                        {{ $student->email }}
                                    </span>
                                </td>

                                <td class="cell-sub">
                                    <x-student-id-status :student="$student" />
                                </td>

                                <td>
                                    {{ $student->programme ?? 'Not set' }}
                                </td>

                                <td>
                                    @foreach($student->organisationGroups as $class)
                                        <span class="cell-title">{{ $class->name }}</span>
                                    @endforeach
                                </td>

                                <td>
                                    @php $r = $student->readiness_score ?? 0; @endphp

                                    <span
                                        class="status-pill
                                        {{ $r >= 70 ? 'status-pill-green' : ($r >= 40 ? 'status-pill-gold' : 'status-pill-muted') }}"
                                    >
                                        {{ $r }}%
                                    </span>
                                </td>

                                <td><div class="row-actions">
                                    <a
                                        href="{{ route('lecturer.students.show', $student) }}"
                                        class="link"
                                    >
                                        View
                                    </a>

                                    {{--
                                        Students pick their own class. One in
                                        the wrong class can be taken out here.
                                    --}}
                                    @foreach($student->organisationGroups as $class)
                                        @if($class->type?->name === \App\Services\Business\StudentClassService::CLASS_TYPE)
                                            <form
                                                method="POST"
                                                action="{{ route('lecturer.students.class.destroy', [$student, $class]) }}"
                                                data-confirm="{{ $student->name }} will be taken out of {{ $class->name }}, and you will no longer see them unless they are in another of your classes."
                                                data-confirm-title="Remove from {{ $class->name }}?"
                                                data-confirm-ok="Yes, remove"
                                                style="display:inline;"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="link link-danger" style="background:none;border:0;padding:0;cursor:pointer;font:inherit;">
                                                    Remove from {{ $class->name }}
                                                </button>
                                            </form>
                                        @endif
                                    @endforeach
                                </div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div style="margin-top:16px;">
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection