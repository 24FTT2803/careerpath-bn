@extends('admin.layouts.admin')

@section('title', 'Student Profile')

@section('content')
<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">👤 Student Profile</h1>
            <p class="text-gray-600">View student details and career progress</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('student.profile.export.admin', $student->id) }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition" target="_blank">
                <i class="fas fa-file-pdf"></i> Download Profile
            </a>
            <a href="{{ route('admin.students.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Read-Only Notice -->
    <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-3 mb-6 rounded text-sm">
        <i class="fas fa-info-circle"></i> 
        @if($isAdmin)
            You have read-only access to student profiles.
        @else
            Lecturer access - View only. Student data cannot be modified.
        @endif
    </div>

    <!-- Database Status -->
    @if($topRecommendations->isEmpty())
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-6 rounded text-sm">
            <i class="fas fa-info-circle"></i> 
            <strong>Database Setup in Progress:</strong> 
            Career recommendations data is not yet available. This is expected while the database team is working on it.
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Student Info -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center mb-4">
                    <div class="w-24 h-24 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-3xl font-bold mx-auto">
                        {{ substr($student->name, 0, 1) }}
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 mt-3">{{ $student->name }}</h2>
                    <p class="text-gray-500 text-sm">{{ $student->programme ?? 'Programme not set' }}</p>
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <div class="flex justify-between py-2 text-sm">
                        <span class="text-gray-500">Student ID</span>
                        <span class="font-medium">{{ $student->student_id ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 text-sm border-t border-gray-100">
                        <span class="text-gray-500">Email</span>
                        <span class="font-medium">{{ $student->email }}</span>
                    </div>
                    <div class="flex justify-between py-2 text-sm border-t border-gray-100">
                        <span class="text-gray-500">CGPA</span>
                        <span class="font-medium">{{ $student->cgpa ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 text-sm border-t border-gray-100">
                        <span class="text-gray-500">Readiness Score</span>
                        <span class="font-medium text-green-600">{{ $readinessScore }}%</span>
                    </div>
                </div>
            </div>

            <!-- Skills -->
            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <h3 class="font-semibold text-gray-800 mb-3">🛠️ Skills & Competencies</h3>
                @if($student->competencies->count() > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach($student->competencies as $skill)
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                {{ $skill->skill_name }}
                                <span class="text-xs text-blue-500">({{ $skill->proficiency_level }})</span>
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No skills recorded.</p>
                @endif
            </div>
        </div>

        <!-- Recommendations & Gaps -->
        <div class="lg:col-span-2">
            <!-- Career Recommendations -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-gray-800 mb-3">🎯 Career Recommendations</h3>
                @if($topRecommendations->count() > 0)
                    @foreach($topRecommendations as $rec)
                        <div class="border border-gray-200 rounded-lg p-4 mb-3 hover:shadow transition">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 class="font-semibold">{{ $rec->career->job_title ?? 'N/A' }}</h4>
                                    <p class="text-sm text-gray-500">{{ $rec->career->subsector ?? '' }}</p>
                                </div>
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                                    {{ $rec->match_score }}% Match
                                </span>
                            </div>
                            <div class="mt-2 text-sm">
                                <span class="text-gray-500">Matched Skills:</span>
                                @php
                                    $matched = is_array($rec->matched_skills) ? $rec->matched_skills : [];
                                @endphp
                                @if(count($matched) > 0)
                                    @foreach($matched as $skill)
                                        <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs">{{ $skill }}</span>
                                    @endforeach
                                @else
                                    <span class="text-gray-400">None</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-gray-500 text-center py-4">
                        <i class="fas fa-info-circle"></i> 
                        No career recommendations yet. This will appear once the database team adds the data.
                    </p>
                @endif
            </div>

            <!-- Competency Gaps -->
            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <h3 class="font-semibold text-gray-800 mb-3">⚠️ Competency Gaps</h3>
                @if(count($skillGaps) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($skillGaps as $gap)
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                <span class="text-red-700 font-medium">
                                    {{ is_array($gap) ? ($gap['skill_name'] ?? json_encode($gap)) : $gap }}
                                </span>
                                <div class="text-xs text-red-500 mt-1">Needs development</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">
                        <i class="fas fa-info-circle"></i> 
                        No competency gaps identified yet.
                    </p>
                @endif
            </div>

            <!-- Milestones -->
            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <h3 class="font-semibold text-gray-800 mb-3">🏆 Milestones</h3>
                @if($student->milestones->count() > 0)
                    @foreach($student->milestones as $milestone)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                            <div>
                                <span class="font-medium">{{ $milestone->title }}</span>
                                <span class="text-xs text-gray-500 ml-2">{{ $milestone->category }}</span>
                            </div>
                            <div>
                                @if($milestone->is_completed)
                                    <span class="text-green-600 text-sm"><i class="fas fa-check-circle"></i> Completed</span>
                                @else
                                    <span class="text-yellow-600 text-sm"><i class="fas fa-clock"></i> In Progress</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-gray-500 text-center py-4">No milestones recorded.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection