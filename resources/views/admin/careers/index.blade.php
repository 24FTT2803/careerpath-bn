@extends('admin.layouts.admin')

@section('title', 'Careers')

@section('content')
<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">📋 BIICF Careers</h1>
            <p class="text-gray-600">View all ICT careers aligned with BIICF framework</p>
        </div>
        <div class="text-sm text-gray-500">
            <i class="fas fa-info-circle"></i> Read-Only View
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">#</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Job Title</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Subsector</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Skills</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Demand</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-600">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($careers as $index => $career)
                        <tr class="border-t border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4">{{ $careers->firstItem() + $index }}</td>
                            <td class="py-3 px-4 font-medium">{{ $career->job_title }}</td>
                            <td class="py-3 px-4">{{ $career->subsector }}</td>
                            <td class="py-3 px-4">
                                @php
                                    $skills = is_array($career->technical_skills) ? $career->technical_skills : json_decode($career->technical_skills ?? '[]', true);
                                @endphp
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                                    {{ count($skills) }} skills
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 rounded-full text-xs 
                                    {{ $career->demand_level == 'Very High' ? 'bg-red-100 text-red-800' : 
                                       ($career->demand_level == 'High' ? 'bg-orange-100 text-orange-800' : 
                                       'bg-green-100 text-green-800') }}">
                                    {{ $career->demand_level ?? 'Medium' }}
                                </span>
                            </td>
                            <td class="text-center py-3 px-4">
                                <a href="{{ route('admin.careers.show', $career) }}" 
                                   class="text-blue-600 hover:text-blue-800 hover:underline text-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">
                                <i class="fas fa-briefcase text-4xl text-gray-300 block mb-2"></i>
                                No careers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $careers->links() }}
    </div>
</div>
@endsection