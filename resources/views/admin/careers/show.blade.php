@extends('admin.layouts.admin')

@section('title', 'Career Details')

@section('content')
<style>
    .career-detail-page {
        padding: 0 4px;
    }
    .career-detail-page .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #6b7280;
        text-decoration: none;
        font-size: 14px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }
    .career-detail-page .back-link:hover {
        color: #1a3a5c;
    }
    .career-detail-page .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }
    .career-detail-page .header h1 {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: #1a3a5c;
        margin: 0;
    }
    .career-detail-page .header .subsector {
        color: #6b7280;
        font-size: 15px;
        margin-top: 2px;
    }
    .career-detail-page .header .demand-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 16px;
        border-radius: 100px;
        font-size: 13px;
        font-weight: 600;
    }
    .career-detail-page .demand-high { background: #fbf1de; color: #8a6420; }
    .career-detail-page .demand-very-high { background: #fbeceb; color: #c65b4e; }
    .career-detail-page .demand-medium { background: #e8f0fe; color: #2a5a8c; }
    .career-detail-page .demand-low { background: #e9f3ee; color: #2d8f5c; }
    .career-detail-page .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .career-detail-page .card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
    }
    .career-detail-page .card-title {
        font-size: 16px;
        font-weight: 600;
        color: #1a3a5c;
        margin: 0 0 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .career-detail-page .card-title i {
        color: #c9a84c;
    }
    .career-detail-page .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 14px;
    }
    .career-detail-page .info-row:last-child {
        border-bottom: none;
    }
    .career-detail-page .info-row .label {
        color: #6b7280;
        font-weight: 500;
    }
    .career-detail-page .info-row .value {
        font-weight: 500;
        color: #1a1a2e;
    }
    .career-detail-page .description-text {
        font-size: 14px;
        color: #374151;
        line-height: 1.7;
        margin-top: 8px;
    }
    .career-detail-page .skill-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .career-detail-page .tag {
        padding: 4px 14px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 500;
    }
    .career-detail-page .tag-tech {
        background: #e8f0fe;
        color: #2a5a8c;
    }
    .career-detail-page .tag-soft {
        background: #e9f3ee;
        color: #2d8f5c;
    }
    .career-detail-page .tag-muted {
        background: #f3f4f6;
        color: #6b7280;
    }
    .career-detail-page .list-items {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .career-detail-page .list-items li {
        padding: 8px 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 14px;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .career-detail-page .list-items li:last-child {
        border-bottom: none;
    }
    .career-detail-page .list-items li::before {
        content: "•";
        color: #c9a84c;
        font-weight: 700;
    }
    .career-detail-page .list-items .empty {
        color: #9ca3af;
        font-style: italic;
        padding: 8px 0;
    }
    .career-detail-page .list-items .empty::before {
        content: none;
    }
    .career-detail-page .full-width {
        grid-column: 1 / -1;
    }
    @media (max-width: 768px) {
        .career-detail-page .grid-2 {
            grid-template-columns: 1fr;
        }
        .career-detail-page .full-width {
            grid-column: 1;
        }
        .career-detail-page .header h1 {
            font-size: 22px;
        }
    }
</style>

<div class="career-detail-page">
    <a href="{{ route('admin.careers.index') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Careers
    </a>

    <div class="header">
        <div>
            <h1>{{ $career->job_title }}</h1>
            <div class="subsector">
                <i class="fas fa-layer-group"></i> {{ $career->subsector ?? 'Uncategorized' }}
            </div>
        </div>
        @php
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
        <span class="demand-badge {{ $demandColor }}">
            <i class="fas {{ $demandIcon }}"></i>
            {{ $career->demand_level ?? 'Medium' }} Demand
        </span>
    </div>

    <div class="grid-2">
        <!-- Job Information -->
        <div class="card">
            <div class="card-title">
                <i class="fas fa-briefcase"></i> Job Information
            </div>
            <div class="info-row">
                <span class="label">Job Title</span>
                <span class="value">{{ $career->job_title }}</span>
            </div>
            <div class="info-row">
                <span class="label">Subsector</span>
                <span class="value">{{ $career->subsector ?? 'Not specified' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Demand Level</span>
                <span class="value">{{ $career->demand_level ?? 'Medium' }}</span>
            </div>
            <div style="padding-top: 14px; margin-top: 14px; border-top: 1px solid #e5e7eb;">
                <span style="color: #6b7280; font-size: 13px; font-weight: 500;">Description</span>
                <p class="description-text">
                    {{ $career->job_description ?? 'No description available for this career.' }}
                </p>
            </div>
        </div>

        <!-- Skills -->
        <div class="card">
            <div class="card-title">
                <i class="fas fa-tools"></i> Required Skills
            </div>

            @php
                $technicalSkills = is_array($career->technical_skills) ? $career->technical_skills : json_decode($career->technical_skills ?? '[]', true);
                $softSkills = is_array($career->soft_skills) ? $career->soft_skills : json_decode($career->soft_skills ?? '[]', true);
            @endphp

            <div style="margin-bottom: 16px;">
                <h4 style="font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 8px;">
                    Technical Skills
                </h4>
                <div class="skill-tags">
                    @forelse($technicalSkills as $skill)
                        <span class="tag tag-tech">{{ $skill }}</span>
                    @empty
                        <span style="color: #9ca3af; font-size: 13px;">No technical skills listed</span>
                    @endforelse
                </div>
            </div>

            <div>
                <h4 style="font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 8px;">
                    Soft Skills
                </h4>
                <div class="skill-tags">
                    @forelse($softSkills as $skill)
                        <span class="tag tag-soft">{{ $skill }}</span>
                    @empty
                        <span style="color: #9ca3af; font-size: 13px;">No soft skills listed</span>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Training -->
        <div class="card">
            <div class="card-title">
                <i class="fas fa-graduation-cap"></i> Recommended Training
            </div>
            @php
                $training = is_array($career->recommended_training) ? $career->recommended_training : json_decode($career->recommended_training ?? '[]', true);
            @endphp
            <ul class="list-items">
                @forelse($training as $item)
                    <li>{{ $item }}</li>
                @empty
                    <li class="empty">No training recommendations available</li>
                @endforelse
            </ul>
        </div>

        <!-- Certifications -->
        <div class="card">
            <div class="card-title">
                <i class="fas fa-certificate"></i> Certifications
            </div>
            @php
                $certs = is_array($career->certifications) ? $career->certifications : json_decode($career->certifications ?? '[]', true);
            @endphp
            <ul class="list-items">
                @forelse($certs as $cert)
                    <li>{{ $cert }}</li>
                @empty
                    <li class="empty">No certifications listed</li>
                @endforelse
            </ul>
        </div>

        <!-- Entry Requirements -->
        <div class="card full-width">
            <div class="card-title">
                <i class="fas fa-door-open"></i> Entry Requirements
            </div>
            @php
                $requirements = is_array($career->entry_requirements) ? $career->entry_requirements : json_decode($career->entry_requirements ?? '[]', true);
            @endphp
            <ul class="list-items" style="display:grid;grid-template-columns:1fr 1fr;gap:4px 20px;">
                @forelse($requirements as $req)
                    <li>{{ $req }}</li>
                @empty
                    <li class="empty" style="grid-column:1/-1;">No entry requirements listed</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection