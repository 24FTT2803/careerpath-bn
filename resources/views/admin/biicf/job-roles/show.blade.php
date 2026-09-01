@extends('admin.layouts.admin')

@section('title', 'Job Role Details')

@section('content')
<div class="biicf-page">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-briefcase"></i> Job Role Details</h1>
            <p class="text-muted">{{ $jobRole->title }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.biicf.job-roles.edit', $jobRole) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.biicf.job-roles') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card-grid">
        <div class="card">
            <div class="card-header">Basic Information</div>
            <div class="card-body">
                <div class="info-row">
                    <span class="label">Title</span>
                    <span class="value">{{ $jobRole->title }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Slug</span>
                    <span class="value"><code>{{ $jobRole->slug }}</code></span>
                </div>
                <div class="info-row">
                    <span class="label">Sub-Sector</span>
                    <span class="value">{{ $jobRole->subSector->name ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Functional Group</span>
                    <span class="value">{{ $jobRole->functional_group ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Career Path Level</span>
                    <span class="value"><span class="badge badge-primary">Level {{ $jobRole->career_path_level }}</span></span>
                </div>
                <div class="info-row">
                    <span class="label">Box Colour</span>
                    <span class="value"><span class="color-box" style="background:{{ $jobRole->box_colour }};color:white;padding:4px 12px;border-radius:4px;">{{ $jobRole->box_colour }}</span></span>
                </div>
                <div class="info-row">
                    <span class="label">Alternative Titles</span>
                    <span class="value">{{ $jobRole->alternative_titles ? implode(', ', $jobRole->alternative_titles) : '-' }}</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Job Description</div>
            <div class="card-body">
                <p>{{ $jobRole->job_description ?? 'No description available.' }}</p>
            </div>
        </div>

        <div class="card full-width">
            <div class="card-header">Critical Work Functions</div>
            <div class="card-body">
                @if($jobRole->critical_work_function)
                    @foreach(explode("\n", $jobRole->critical_work_function) as $function)
                        @if(trim($function))
                            <div class="function-item">
                                <i class="fas fa-check-circle"></i>
                                {{ trim($function) }}
                            </div>
                        @endif
                    @endforeach
                @else
                    <p>No critical work functions defined.</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">Competencies</div>
            <div class="card-body">
                @if($jobRole->competencies->count() > 0)
                    @foreach($jobRole->competencies->groupBy('type') as $type => $competencies)
                        <h4>{{ ucfirst(str_replace('_', ' ', $type)) }}</h4>
                        @foreach($competencies as $comp)
                            <div class="competency-item">
                                <span class="comp-name">{{ $comp->name }}</span>
                                <span class="badge {{ $comp->pivot->is_core ? 'badge-core' : 'badge-supporting' }}">
                                    {{ $comp->pivot->is_core ? 'Core' : 'Supporting' }}
                                </span>
                                <span class="badge badge-level">
                                    Level {{ $comp->pivot->proficiencyLevel->level_number ?? '?' }}
                                </span>
                            </div>
                        @endforeach
                    @endforeach
                @else
                    <p>No competencies assigned.</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">Trainings</div>
            <div class="card-body">
                @if($jobRole->trainings->count() > 0)
                    @foreach($jobRole->trainings as $training)
                        <div class="training-item">
                            <span class="training-name">{{ $training->name }}</span>
                            @if($training->provider)
                                <span class="training-provider">{{ $training->provider }}</span>
                            @endif
                            @if($training->certification_body)
                                <span class="badge badge-cert">Cert: {{ $training->certification_body }}</span>
                            @endif
                        </div>
                    @endforeach
                @else
                    <p>No trainings assigned.</p>
                @endif
            </div>
        </div>

        @if($jobRole->entryRequirement)
        <div class="card full-width">
            <div class="card-header">Entry Requirements</div>
            <div class="card-body">
                <div class="info-row">
                    <span class="label">BDQF Level</span>
                    <span class="value">{{ $jobRole->entryRequirement->bdqf_level ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Field of Study</span>
                    <span class="value">{{ $jobRole->entryRequirement->field_of_study ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Years Experience</span>
                    <span class="value">{{ $jobRole->entryRequirement->years_experience ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Alternative Pathway</span>
                    <span class="value">{{ $jobRole->entryRequirement->alternative_pathway ?? '-' }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    .biicf-page { padding: 0 4px; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
    .page-header h1 { font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 700; color: #1a3a5c; margin: 0; }
    .page-header h1 i { color: #c9a84c; }
    .text-muted { color: #6b7280; font-size: 14px; margin-top: 2px; }
    .btn-primary { background: #c9a84c; color: #0d1f33; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; }
    .btn-primary:hover { background: #e8d4a0; transform: translateY(-2px); }
    .btn-outline { background: transparent; color: #1a1a2e; border: 2px solid #e5e7eb; padding: 10px 20px; border-radius: 8px; font-weight: 500; }
    .btn-outline:hover { border-color: #c9a84c; color: #c9a84c; }
    .btn-group { display: flex; gap: 10px; flex-wrap: wrap; }
    .card-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; }
    .card.full-width { grid-column: 1 / -1; }
    .card-header { padding: 16px 20px; background: #faf8f2; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: 16px; color: #1a3a5c; }
    .card-body { padding: 20px; }
    .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f3f4f6; }
    .info-row:last-child { border-bottom: none; }
    .info-row .label { color: #6b7280; font-weight: 500; }
    .info-row .value { font-weight: 500; color: #1a1a2e; }
    .badge { display: inline-block; padding: 4px 12px; border-radius: 100px; font-size: 12px; font-weight: 500; }
    .badge-primary { background: #e8f0fe; color: #2a5a8c; }
    .badge-core { background: #e9f3ee; color: #2d8f5c; }
    .badge-supporting { background: #fbf1de; color: #8a6420; }
    .badge-level { background: #f1ecf7; color: #7a5ea8; }
    .badge-cert { background: #fbeceb; color: #c65b4e; }
    .function-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f3f4f6; }
    .function-item:last-child { border-bottom: none; }
    .function-item i { color: #c9a84c; }
    .competency-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f3f4f6; flex-wrap: wrap; }
    .competency-item:last-child { border-bottom: none; }
    .comp-name { font-weight: 500; flex: 1; }
    .training-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f3f4f6; flex-wrap: wrap; }
    .training-item:last-child { border-bottom: none; }
    .training-name { font-weight: 500; }
    .training-provider { color: #6b7280; font-size: 13px; }
    .color-box { display: inline-block; padding: 4px 12px; border-radius: 4px; text-transform: capitalize; }
    @media (max-width: 768px) {
        .card-grid { grid-template-columns: 1fr; }
        .card.full-width { grid-column: 1; }
    }
</style>
@endsection