@extends('admin.layouts.admin')

@section('title', 'Edit Job Role')

@section('content')
<div class="biicf-page">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-edit"></i> Edit Job Role</h1>
            <p class="text-muted">Update "{{ $jobRole->title }}"</p>
        </div>
        <a href="{{ route('admin.biicf.job-roles') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card" style="max-width: 1000px;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.biicf.job-roles.update', $jobRole) }}">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="form-group half">
                        <label>Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title', $jobRole->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group half">
                        <label>Sub-Sector <span class="text-danger">*</span></label>
                        <select name="sub_sector_id" class="form-control @error('sub_sector_id') is-invalid @enderror" required>
                            <option value="">Select sub-sector...</option>
                            @foreach($subSectors as $sector)
                                <option value="{{ $sector->id }}" {{ old('sub_sector_id', $jobRole->sub_sector_id) == $sector->id ? 'selected' : '' }}>
                                    {{ $sector->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('sub_sector_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Functional Group</label>
                    <input type="text" name="functional_group" class="form-control @error('functional_group') is-invalid @enderror" 
                           value="{{ old('functional_group', $jobRole->functional_group) }}" placeholder="e.g. Software and Systems">
                    @error('functional_group')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Job Description</label>
                    <textarea name="job_description" class="form-control @error('job_description') is-invalid @enderror" 
                              rows="3">{{ old('job_description', $jobRole->job_description) }}</textarea>
                    @error('job_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Critical Work Function</label>
                    <textarea name="critical_work_function" class="form-control @error('critical_work_function') is-invalid @enderror" 
                              rows="3">{{ old('critical_work_function', $jobRole->critical_work_function) }}</textarea>
                    @error('critical_work_function')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label>Career Path Level</label>
                        <input type="number" name="career_path_level" class="form-control @error('career_path_level') is-invalid @enderror" 
                               value="{{ old('career_path_level', $jobRole->career_path_level) }}" min="0">
                        @error('career_path_level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group half">
                        <label>Box Colour</label>
                        <select name="box_colour" class="form-control @error('box_colour') is-invalid @enderror">
                            <option value="primary" {{ old('box_colour', $jobRole->box_colour) == 'primary' ? 'selected' : '' }}>Primary (Blue)</option>
                            <option value="secondary" {{ old('box_colour', $jobRole->box_colour) == 'secondary' ? 'selected' : '' }}>Secondary (Grey)</option>
                            <option value="success" {{ old('box_colour', $jobRole->box_colour) == 'success' ? 'selected' : '' }}>Success (Green)</option>
                            <option value="danger" {{ old('box_colour', $jobRole->box_colour) == 'danger' ? 'selected' : '' }}>Danger (Red)</option>
                            <option value="warning" {{ old('box_colour', $jobRole->box_colour) == 'warning' ? 'selected' : '' }}>Warning (Yellow)</option>
                            <option value="info" {{ old('box_colour', $jobRole->box_colour) == 'info' ? 'selected' : '' }}>Info (Light Blue)</option>
                            <option value="light-blue" {{ old('box_colour', $jobRole->box_colour) == 'light-blue' ? 'selected' : '' }}>Light Blue</option>
                        </select>
                        @error('box_colour')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Alternative Titles (comma separated)</label>
                    <input type="text" name="alternative_titles_text" class="form-control" 
                           value="{{ old('alternative_titles_text', implode(', ', $jobRole->alternative_titles ?? [])) }}" 
                           placeholder="e.g. IT Generalist, Network Administrator">
                    <small class="form-text text-muted">Separate multiple titles with commas.</small>
                </div>

                <div class="form-divider">Competencies</div>

                <div class="form-group">
                    <label>Select Competencies</label>
                    <div class="competency-grid">
                        @foreach($competencies as $comp)
                            @php
                                $selected = $jobRole->competencies->contains($comp->id);
                                $pivot = $selected ? $jobRole->competencies->find($comp->id)->pivot : null;
                            @endphp
                            <div class="competency-item">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="competencies[{{ $comp->id }}][id]" 
                                           value="{{ $comp->id }}" 
                                           {{ $selected ? 'checked' : '' }}
                                           onchange="toggleCompetency(this, {{ $comp->id }})">
                                    <span class="comp-name">{{ $comp->name }}</span>
                                    <span class="comp-type {{ $comp->type }}">{{ $comp->type == 'technical' ? 'Tech' : 'Soft' }}</span>
                                </label>
                                <div class="comp-details" id="comp-details-{{ $comp->id }}" style="{{ $selected ? 'display:block' : 'display:none' }}">
                                    <div class="comp-row">
                                        <label>Proficiency Level</label>
                                        <select name="competencies[{{ $comp->id }}][proficiency_level_id]" class="form-control-sm">
                                            @foreach($proficiencyLevels as $level)
                                                <option value="{{ $level->id }}" {{ $selected && $pivot && $pivot->proficiency_level_id == $level->id ? 'selected' : '' }}>
                                                    Level {{ $level->level_number }} - {{ $level->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="comp-row">
                                        <label>
                                            <input type="checkbox" name="competencies[{{ $comp->id }}][is_core]" value="1" 
                                                   {{ $selected && $pivot && $pivot->is_core ? 'checked' : '' }}>
                                            Is Core Competency
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('competencies')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-divider">Trainings</div>

                <div class="form-group">
                    <label>Select Trainings</label>
                    <div class="training-grid">
                        @foreach($trainings as $training)
                            <label class="checkbox-label">
                                <input type="checkbox" name="trainings[]" value="{{ $training->id }}" 
                                       {{ $jobRole->trainings->contains($training->id) ? 'checked' : '' }}>
                                <span class="training-name">{{ $training->name }}</span>
                                @if($training->provider)
                                    <span class="training-provider">{{ $training->provider }}</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                    @error('trainings')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Job Role
                    </button>
                </div>
            </form>
        </div>
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
    .card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 24px; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
    .text-danger { color: #c0392b; }
    .form-control { width: 100%; padding: 10px 14px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; }
    .form-control:focus { border-color: #c9a84c; outline: none; box-shadow: 0 0 0 4px rgba(201, 168, 76, 0.15); }
    .form-control-sm { padding: 6px 10px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 13px; }
    .is-invalid { border-color: #c0392b; }
    .invalid-feedback { color: #c0392b; font-size: 12px; margin-top: 4px; }
    .form-actions { margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px; }
    .form-row { display: flex; gap: 16px; }
    .form-row .half { flex: 1; }
    .form-divider { font-size: 16px; font-weight: 600; color: #1a3a5c; margin: 24px 0 16px; padding-bottom: 8px; border-bottom: 2px solid #e5e7eb; }
    .competency-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .competency-item { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; }
    .competency-item:hover { border-color: #c9a84c; }
    .checkbox-label { display: flex; align-items: center; gap: 10px; cursor: pointer; flex-wrap: wrap; }
    .comp-name { font-weight: 500; flex: 1; }
    .comp-type { font-size: 10px; padding: 2px 8px; border-radius: 100px; font-weight: 600; }
    .comp-type.technical { background: #e8f0fe; color: #2a5a8c; }
    .comp-type.soft_skill { background: #e9f3ee; color: #2d8f5c; }
    .comp-details { margin-top: 10px; padding-top: 10px; border-top: 1px solid #e5e7eb; display: none; }
    .comp-details .comp-row { display: flex; align-items: center; gap: 12px; margin-top: 6px; }
    .comp-details .comp-row label { font-weight: 500; font-size: 13px; margin: 0; min-width: 120px; }
    .training-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .training-name { font-weight: 500; }
    .training-provider { font-size: 11px; color: #6b7280; }
    .form-text { font-size: 12px; color: #6b7280; margin-top: 4px; }
    @media (max-width: 768px) {
        .form-row { flex-direction: column; }
        .competency-grid { grid-template-columns: 1fr; }
        .training-grid { grid-template-columns: 1fr; }
    }
</style>

<script>
function toggleCompetency(checkbox, id) {
    const details = document.getElementById('comp-details-' + id);
    if (checkbox.checked) {
        details.style.display = 'block';
    } else {
        details.style.display = 'none';
    }
}
</script>
@endsection