@extends('admin.layouts.admin')

@section('title', 'BIICF Careers')

@section('content')
<div class="careers-page">

    <!-- Page Header -->
    <div class="page-header">
        <div class="header-left">
            <h1>
                <i class="fas fa-briefcase" style="color: #c9a84c;"></i>
                BIICF Careers
            </h1>
            <p class="subtitle">Browse the Brunei ICT Industry Competency Framework career pathways</p>
        </div>
        <div class="header-stats">
            <div class="header-stat">
                <span class="stat-number">{{ $careers->total() }}</span>
                <span class="stat-label">Total Careers</span>
            </div>
            <div class="header-stat">
                <span class="stat-number">
                    @php
                        $subsectors = $careers->pluck('subsector')->unique()->count();
                    @endphp
                    {{ $subsectors }}
                </span>
                <span class="stat-label">Sub-Sectors</span>
            </div>
            <div class="header-stat">
                <span class="stat-number">
                    @php
                        $highDemand = $careers->filter(function($c) {
                            return str_contains(strtolower($c->demand_level ?? ''), 'high') ||
                                   str_contains(strtolower($c->demand_level ?? ''), 'very');
                        })->count();
                    @endphp
                    {{ $highDemand }}
                </span>
                <span class="stat-label">High Demand</span>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="filter-bar">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <div class="filter-field">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search careers by title or subsector...">
                </div>
                <div class="filter-field">
                    <i class="fas fa-layer-group"></i>
                    <select name="subsector">
                        <option value="">All Sub-Sectors</option>
                        @php
                            $uniqueSubsectors = $careers->pluck('subsector')->unique()->filter();
                        @endphp
                        @foreach($uniqueSubsectors as $subsector)
                            <option value="{{ $subsector }}" {{ request('subsector') == $subsector ? 'selected' : '' }}>
                                {{ $subsector }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <i class="fas fa-chart-line"></i>
                    <select name="demand">
                        <option value="">All Demand Levels</option>
                        <option value="Very High" {{ request('demand') == 'Very High' ? 'selected' : '' }}>Very High</option>
                        <option value="High" {{ request('demand') == 'High' ? 'selected' : '' }}>High</option>
                        <option value="Medium" {{ request('demand') == 'Medium' ? 'selected' : '' }}>Medium</option>
                        <option value="Low" {{ request('demand') == 'Low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('admin.careers.index') }}" class="btn btn-outline">
                    <i class="fas fa-undo"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Careers Grid -->
    <div class="careers-grid">
        @forelse($careers as $career)
            @php
                $technicalSkills = is_array($career->technical_skills) ? $career->technical_skills : json_decode($career->technical_skills ?? '[]', true);
                $softSkills = is_array($career->soft_skills) ? $career->soft_skills : json_decode($career->soft_skills ?? '[]', true);
                $totalSkills = count($technicalSkills) + count($softSkills);

                $demandColor = match(strtolower($career->demand_level ?? 'medium')) {
                    'very high' => 'demand-very-high',
                    'high' => 'demand-high',
                    'medium' => 'demand-medium',
                    'low' => 'demand-low',
                    default => 'demand-medium',
                };

                $demandIcon = match(strtolower($career->demand_level ?? 'medium')) {
                    'very high' => 'fa-rocket',
                    'high' => 'fa-arrow-up',
                    'medium' => 'fa-minus',
                    'low' => 'fa-arrow-down',
                    default => 'fa-minus',
                };
            @endphp

            <div class="career-card">
                <div class="career-card-header">
                    <div class="career-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="career-info">
                        <h4>
                            <a href="{{ route('admin.careers.show', $career) }}">
                                {{ $career->job_title }}
                            </a>
                        </h4>
                        <span class="career-subsector">
                            <i class="fas fa-layer-group"></i> {{ $career->subsector ?? 'Uncategorized' }}
                        </span>
                    </div>
                    <div class="career-demand">
                        <span class="demand-badge {{ $demandColor }}">
                            <i class="fas {{ $demandIcon }}"></i>
                            {{ $career->demand_level ?? 'Medium' }}
                        </span>
                    </div>
                </div>

                <div class="career-card-body">
                    @if($career->job_description)
                        <p class="career-description">{{ Str::limit($career->job_description, 120) }}</p>
                    @endif

                    <div class="career-skills">
                        <div class="skill-group">
                            <span class="skill-label">Technical</span>
                            <div class="skill-tags">
                                @forelse(array_slice($technicalSkills, 0, 4) as $skill)
                                    <span class="tag tag-blue">{{ $skill }}</span>
                                @empty
                                    <span class="tag tag-muted">No skills listed</span>
                                @endforelse
                                @if(count($technicalSkills) > 4)
                                    <span class="tag tag-muted">+{{ count($technicalSkills) - 4 }} more</span>
                                @endif
                            </div>
                        </div>
                        <div class="skill-group" style="margin-top: 8px;">
                            <span class="skill-label">Soft</span>
                            <div class="skill-tags">
                                @forelse(array_slice($softSkills, 0, 3) as $skill)
                                    <span class="tag tag-green">{{ $skill }}</span>
                                @empty
                                    <span class="tag tag-muted">No skills listed</span>
                                @endforelse
                                @if(count($softSkills) > 3)
                                    <span class="tag tag-muted">+{{ count($softSkills) - 3 }} more</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="career-metrics">
                        <div class="metric">
                            <span class="metric-label">Total Skills</span>
                            <span class="metric-value">{{ $totalSkills }}</span>
                        </div>
                        @php
                            $certifications = is_array($career->certifications) ? $career->certifications : json_decode($career->certifications ?? '[]', true);
                            $training = is_array($career->recommended_training) ? $career->recommended_training : json_decode($career->recommended_training ?? '[]', true);
                        @endphp
                        <div class="metric">
                            <span class="metric-label">Certifications</span>
                            <span class="metric-value">{{ count($certifications) }}</span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">Training</span>
                            <span class="metric-value">{{ count($training) }}</span>
                        </div>
                    </div>
                </div>

                <div class="career-card-footer">
                    <a href="{{ route('admin.careers.show', $career) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                    <span class="career-id">ID: {{ $career->id }}</span>
                </div>
            </div>
        @empty
            <div class="empty-state-full">
                <div class="empty-illustration">
                    <i class="fas fa-briefcase-slash"></i>
                </div>
                <h3>No Careers Found</h3>
                <p>Try adjusting your search or filter criteria.</p>
                <a href="{{ route('admin.careers.index') }}" class="btn btn-primary">
                    <i class="fas fa-undo"></i> Reset Filters
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pagination-wrap">
        {{ $careers->withQueryString()->links() }}
    </div>

</div>

<style>
    .careers-page {
        padding: 0 4px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 24px;
    }

    .header-left h1 {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: #1a3a5c;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .header-left .subtitle {
        color: #6b7280;
        font-size: 14px;
        margin-top: 2px;
    }

    .header-stats {
        display: flex;
        gap: 24px;
        background: white;
        padding: 12px 24px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }

    .header-stat {
        text-align: center;
    }

    .header-stat .stat-number {
        display: block;
        font-family: 'Playfair Display', serif;
        font-size: 24px;
        font-weight: 700;
        color: #1a3a5c;
    }

    .header-stat .stat-label {
        font-size: 11px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .filter-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        background: white;
        padding: 16px 20px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        margin-bottom: 24px;
    }

    .filter-form {
        flex: 1;
    }

    .filter-group {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-field {
        position: relative;
        flex: 1;
        min-width: 180px;
    }

    .filter-field i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 14px;
    }

    .filter-field input,
    .filter-field select {
        width: 100%;
        padding: 10px 12px 10px 36px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        background: white;
        transition: all 0.3s ease;
    }

    .filter-field input:focus,
    .filter-field select:focus {
        outline: none;
        border-color: #c9a84c;
        box-shadow: 0 0 0 4px rgba(201, 168, 76, 0.15);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        font-family: inherit;
    }

    .btn-primary {
        background: #c9a84c;
        color: #0d1f33;
    }

    .btn-primary:hover {
        background: #e8d4a0;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(201, 168, 76, 0.25);
    }

    .btn-outline {
        background: transparent;
        color: #1a1a2e;
        border: 2px solid #e5e7eb;
    }

    .btn-outline:hover {
        border-color: #c9a84c;
        color: #c9a84c;
        transform: translateY(-2px);
    }

    .btn-sm {
        padding: 6px 14px;
        font-size: 12px;
    }

    .careers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .career-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .career-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 48px rgba(0, 0, 0, 0.08);
        border-color: #c9a84c;
    }

    .career-card-header {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 16px 20px;
        background: linear-gradient(135deg, #faf8f2, #f4f1e7);
        border-bottom: 1px solid #e5e7eb;
    }

    .career-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: #fbf1de;
        color: #c9a84c;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .career-info {
        flex: 1;
        min-width: 0;
    }

    .career-info h4 {
        font-size: 16px;
        font-weight: 600;
        color: #1a3a5c;
        margin: 0;
    }

    .career-info h4 a {
        color: #1a3a5c;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .career-info h4 a:hover {
        color: #c9a84c;
    }

    .career-subsector {
        font-size: 12px;
        color: #6b7280;
        display: block;
        margin-top: 2px;
    }

    .career-subsector i {
        margin-right: 4px;
    }

    .career-demand {
        flex-shrink: 0;
    }

    .demand-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 100px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .demand-very-high {
        background: #fbeceb;
        color: #c65b4e;
    }

    .demand-high {
        background: #fbf1de;
        color: #8a6420;
    }

    .demand-medium {
        background: #e8f0fe;
        color: #2a5a8c;
    }

    .demand-low {
        background: #e9f3ee;
        color: #2d8f5c;
    }

    .career-card-body {
        padding: 16px 20px;
        flex: 1;
    }

    .career-description {
        font-size: 13px;
        color: #6b7280;
        line-height: 1.6;
        margin: 0 0 14px 0;
    }

    .career-skills {
        margin-bottom: 14px;
    }

    .skill-group {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .skill-label {
        font-size: 10px;
        text-transform: uppercase;
        color: #6b7280;
        letter-spacing: 0.04em;
        font-weight: 600;
        min-width: 48px;
        padding-top: 3px;
    }

    .skill-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }

    .tag {
        font-size: 11px;
        padding: 3px 10px;
        border-radius: 100px;
        font-weight: 500;
    }

    .tag-blue {
        background: #e8f0fe;
        color: #2a5a8c;
    }

    .tag-green {
        background: #e9f3ee;
        color: #2d8f5c;
    }

    .tag-muted {
        background: #f3f4f6;
        color: #6b7280;
    }

    .career-metrics {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        padding-top: 12px;
        border-top: 1px solid #e5e7eb;
    }

    .metric {
        text-align: center;
        padding: 6px;
        background: #faf8f2;
        border-radius: 6px;
    }

    .metric-label {
        display: block;
        font-size: 9px;
        text-transform: uppercase;
        color: #6b7280;
        letter-spacing: 0.04em;
    }

    .metric-value {
        display: block;
        font-size: 16px;
        font-weight: 700;
        color: #1a3a5c;
        margin-top: 2px;
    }

    .career-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 20px;
        border-top: 1px solid #e5e7eb;
        background: #faf8f2;
    }

    .career-id {
        font-size: 11px;
        color: #9ca3af;
        font-family: 'IBM Plex Mono', monospace;
    }

    .empty-state-full {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        border: 2px dashed #e5e7eb;
    }

    .empty-state-full .empty-illustration {
        font-size: 64px;
        color: #e5e7eb;
        margin-bottom: 16px;
    }

    .empty-state-full h3 {
        font-size: 20px;
        font-weight: 600;
        color: #1a3a5c;
        margin: 0 0 4px;
    }

    .empty-state-full p {
        color: #6b7280;
        margin-bottom: 20px;
    }

    .pagination-wrap {
        display: flex;
        justify-content: center;
        padding: 12px 0;
    }

    .pagination-wrap nav {
        display: flex;
        gap: 4px;
    }

    .pagination-wrap a,
    .pagination-wrap span {
        padding: 8px 16px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        color: #1a1a2e;
        text-decoration: none;
        font-size: 13px;
        transition: all 0.3s ease;
    }

    .pagination-wrap a:hover {
        background: #c9a84c;
        color: white;
        border-color: #c9a84c;
    }

    .pagination-wrap .active span {
        background: #c9a84c;
        color: white;
        border-color: #c9a84c;
    }

    @media (max-width: 1024px) {
        .careers-grid {
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: stretch;
        }
        .header-stats {
            justify-content: space-around;
        }
        .filter-group {
            flex-direction: column;
        }
        .filter-field {
            min-width: 100%;
        }
        .careers-grid {
            grid-template-columns: 1fr;
        }
        .career-card-footer {
            flex-direction: column;
            gap: 8px;
            align-items: stretch;
        }
        .career-card-footer .btn {
            width: 100%;
            justify-content: center;
        }
        .career-metrics {
            grid-template-columns: repeat(3, 1fr);
        }
        .career-card-header {
            flex-wrap: wrap;
        }
        .career-demand {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .header-stats {
            flex-direction: column;
            gap: 8px;
        }
        .header-stat .stat-number {
            font-size: 20px;
        }
        .skill-group {
            flex-direction: column;
            gap: 4px;
        }
        .skill-label {
            min-width: auto;
        }
        .career-metrics {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>
@endsection