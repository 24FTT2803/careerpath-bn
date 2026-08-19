@extends('admin.layouts.admin')

@section('title', 'Careers')

@section('content')
<div>
    <div class="cpbn-head">
        <div>
            <h1>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 12h18"/></svg>
                BIICF Careers
            </h1>
            <p class="sub">View all ICT careers aligned with BIICF framework</p>
        </div>
        <div class="cpbn-note">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
            Read-Only View
        </div>
    </div>

    <div class="cpbn-table-wrap">
        <table class="cpbn-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Job Title</th>
                    <th>Subsector</th>
                    <th>Skills</th>
                    <th>Demand</th>
                    <th class="center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($careers as $index => $career)
                    <tr>
                        <td>{{ $careers->firstItem() + $index }}</td>
                        <td style="font-weight:500">{{ $career->job_title }}</td>
                        <td>{{ $career->subsector }}</td>
                        <td>
                            @php
                                $skills = is_array($career->technical_skills) ? $career->technical_skills : json_decode($career->technical_skills ?? '[]', true);
                            @endphp
                            <span class="cpbn-pill pill-gold">{{ count($skills) }} skills</span>
                        </td>
                        <td>
                            <span class="cpbn-pill {{ $career->demand_level == 'Very High' ? 'pill-rose' : ($career->demand_level == 'High' ? 'pill-gold' : 'pill-green') }}">
                                {{ $career->demand_level ?? 'Medium' }}
                            </span>
                        </td>
                        <td class="center">
                            <a href="{{ route('admin.careers.show', $career) }}" class="link">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;display:inline;vertical-align:-1px"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="cpbn-empty-row">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            No careers found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="cpbn-pagination">
        {{ $careers->links() }}
    </div>
</div>
@endsection