@extends('admin.layouts.admin')

@section('title', 'My Students')

@section('content')
<div>
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
                                    {{ $student->student_id ?? '—' }}
                                </td>

                                <td>
                                    {{ $student->programme ?? 'Not set' }}
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