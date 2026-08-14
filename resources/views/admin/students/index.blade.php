@extends('admin.layouts.admin')

@section('title', 'Students')

@section('content')
<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">👨‍🎓 Students</h1>
            <p class="text-gray-600">View and manage student profiles (Read-Only)</p>
        </div>
        <div class="text-sm text-gray-500">
            <i class="fas fa-info-circle"></i> 
            @if($isAdmin)
                Administrators can view all student details.
            @else
                Lecturers have read-only access.
            @endif
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search by name, email or student ID..."
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
            </div>
            <div>
                <select name="programme" class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                    <option value="">All Programmes</option>
                    @foreach($programmes as $prog)
                        <option value="{{ $prog }}" {{ request('programme') == $prog ? 'selected' : '' }}>
                            {{ $prog }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                <i class="fas fa-search"></i> Filter
            </button>
            <a href="{{ route('admin.students.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition">
                <i class="fas fa-undo"></i> Reset
            </a>
        </form>
    </div>

    <!-- Students Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">#</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Name</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Student ID</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Programme</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">CGPA</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Skills</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-600">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $index => $student)
                        <tr class="border-t border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4">{{ $students->firstItem() + $index }}</td>
                            <td class="py-3 px-4 font-medium">{{ $student->name }}</td>
                            <td class="py-3 px-4">{{ $student->student_id ?? '-' }}</td>
                            <td class="py-3 px-4">{{ $student->programme ?? '-' }}</td>
                            <td class="py-3 px-4">{{ $student->cgpa ?? '-' }}</td>
                            <td class="py-3 px-4">
                                @php
                                    $skills = $student->competencies->pluck('skill_name')->toArray();
                                @endphp
                                @if(count($skills) > 0)
                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                                        {{ count($skills) }} skills
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">No skills</span>
                                @endif
                            </td>
                            <td class="text-center py-3 px-4">
                                <a href="{{ route('admin.students.show', $student) }}" 
                                   class="text-blue-600 hover:text-blue-800 hover:underline text-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">
                                <i class="fas fa-users text-4xl text-gray-300 block mb-2"></i>
                                No students found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $students->withQueryString()->links() }}
    </div>
</div>
@endsection