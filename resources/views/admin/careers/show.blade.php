@extends('admin.layouts.admin')

@section('title', 'Career Details')

@section('content')
<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">📋 Career Details</h1>
            <p class="text-gray-600">{{ $career->job_title }}</p>
        </div>
        <a href="{{ route('admin.careers.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Career Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-800 mb-3">📋 Job Information</h3>
            <div class="space-y-3">
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-500">Job Title</span>
                    <span class="font-medium">{{ $career->job_title }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-500">Subsector</span>
                    <span class="font-medium">{{ $career->subsector }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-500">Demand Level</span>
                    <span class="font-medium">{{ $career->demand_level ?? 'Medium' }}</span>
                </div>
                <div class="py-2">
                    <span class="text-gray-500">Description</span>
                    <p class="mt-1 text-gray-700">{{ $career->job_description ?? 'No description available.' }}</p>
                </div>
            </div>
        </div>

        <!-- Skills -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-800 mb-3">🛠️ Required Skills</h3>
            
            @php
                $technicalSkills = is_array($career->technical_skills) ? $career->technical_skills : json_decode($career->technical_skills ?? '[]', true);
                $softSkills = is_array($career->soft_skills) ? $career->soft_skills : json_decode($career->soft_skills ?? '[]', true);
            @endphp

            <div class="mb-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Technical Skills</h4>
                <div class="flex flex-wrap gap-2">
                    @forelse($technicalSkills as $skill)
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">{{ $skill }}</span>
                    @empty
                        <span class="text-gray-400 text-sm">No technical skills listed</span>
                    @endforelse
                </div>
            </div>

            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-2">Soft Skills</h4>
                <div class="flex flex-wrap gap-2">
                    @forelse($softSkills as $skill)
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">{{ $skill }}</span>
                    @empty
                        <span class="text-gray-400 text-sm">No soft skills listed</span>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Training & Certifications -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-800 mb-3">📚 Training & Certifications</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Recommended Training</h4>
                    @php
                        $training = is_array($career->recommended_training) ? $career->recommended_training : json_decode($career->recommended_training ?? '[]', true);
                    @endphp
                    <ul class="list-disc list-inside text-sm text-gray-700">
                        @forelse($training as $item)
                            <li>{{ $item }}</li>
                        @empty
                            <li class="text-gray-400">No training listed</li>
                        @endforelse
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Recommended Certifications</h4>
                    @php
                        $certs = is_array($career->certifications) ? $career->certifications : json_decode($career->certifications ?? '[]', true);
                    @endphp
                    <ul class="list-disc list-inside text-sm text-gray-700">
                        @forelse($certs as $cert)
                            <li>{{ $cert }}</li>
                        @empty
                            <li class="text-gray-400">No certifications listed</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection