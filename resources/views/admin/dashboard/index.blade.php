@extends('admin.layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-2">📊 Dashboard</h1>
    <p class="text-gray-600 mb-6">Welcome back, {{ auth()->user()->name }}!</p>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 admin-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Students</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['total_students'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 admin-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Lecturers</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['total_lecturers'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-chalkboard-teacher text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 admin-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Careers</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['total_careers'] }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-briefcase text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 admin-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Recommendations</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['total_recommendations'] }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-bullseye text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 admin-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Avg Readiness</p>
                    <p class="text-2xl font-bold text-teal-600">{{ $stats['avg_readiness'] }}%</p>
                </div>
                <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-line text-teal-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Students by Programme -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-800 mb-4">📊 Students by Programme</h3>
            @foreach($studentsByProgramme as $prog)
                <div class="mb-3">
                    <div class="flex justify-between text-sm">
                        <span>{{ $prog->programme ?? 'Not Set' }}</span>
                        <span>{{ $prog->count }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full progress-bar-animated" 
                             style="width: {{ ($prog->count / $stats['total_students']) * 100 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Top Career Matches -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-800 mb-4">🏆 Top Career Matches</h3>
            @foreach($topCareers as $career)
                <div class="mb-3">
                    <div class="flex justify-between text-sm">
                        <span>{{ $career->career->job_title ?? 'N/A' }}</span>
                        <span>{{ number_format($career->avg_score, 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full progress-bar-animated" 
                             style="width: {{ $career->avg_score }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Common Competency Gaps -->
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h3 class="font-semibold text-gray-800 mb-4">📚 Common Competency Gaps</h3>
        @forelse($skillGaps as $skill => $count)
            <div class="mb-3">
                <div class="flex justify-between text-sm">
                    <span>{{ $skill }}</span>
                    <span>{{ $count }} students</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-red-500 h-2 rounded-full progress-bar-animated" 
                         style="width: {{ min(($count / $stats['total_students']) * 100, 100) }}%"></div>
                </div>
            </div>
        @empty
            <p class="text-gray-500 text-center py-4">No competency gaps recorded yet.</p>
        @endforelse
    </div>

    <!-- Recent Students -->
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-semibold text-gray-800">👨‍🎓 Recent Students</h3>
            <a href="{{ route('admin.students.index') }}" class="text-sm text-blue-600 hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left py-2 px-3">Name</th>
                        <th class="text-left py-2 px-3">Student ID</th>
                        <th class="text-left py-2 px-3">Programme</th>
                        <th class="text-left py-2 px-3">CGPA</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentStudents as $student)
                        <tr class="border-t border-gray-100">
                            <td class="py-2 px-3">
                                <a href="{{ route('admin.students.show', $student) }}" class="text-blue-600 hover:underline">
                                    {{ $student->name }}
                                </a>
                            </td>
                            <td class="py-2 px-3">{{ $student->student_id ?? '-' }}</td>
                            <td class="py-2 px-3">{{ $student->programme ?? '-' }}</td>
                            <td class="py-2 px-3">{{ $student->cgpa ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-gray-500">No students registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection