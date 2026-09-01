@extends('admin.layouts.admin')

@section('title', 'Edit Proficiency Level')

@section('content')
<div class="biicf-page">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-edit"></i> Edit Proficiency Level</h1>
            <p class="text-muted">Update "{{ $level->name }}"</p>
        </div>
        <a href="{{ route('admin.biicf.proficiency-levels') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card" style="max-width: 800px;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.biicf.proficiency-levels.update', $level) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Level Number <span class="text-danger">*</span></label>
                    <select name="level_number" class="form-control @error('level_number') is-invalid @enderror" required>
                        <option value="1" {{ old('level_number', $level->level_number) == 1 ? 'selected' : '' }}>Level 1 - Follow</option>
                        <option value="2" {{ old('level_number', $level->level_number) == 2 ? 'selected' : '' }}>Level 2 - Assist</option>
                        <option value="3" {{ old('level_number', $level->level_number) == 3 ? 'selected' : '' }}>Level 3 - Apply</option>
                        <option value="4" {{ old('level_number', $level->level_number) == 4 ? 'selected' : '' }}>Level 4 - Ensure</option>
                        <option value="5" {{ old('level_number', $level->level_number) == 5 ? 'selected' : '' }}>Level 5 - Strategise</option>
                    </select>
                    <small class="form-text text-muted">Each level number must be unique (1-5).</small>
                    @error('level_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $level->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                              rows="3">{{ old('description', $level->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Proficiency Level
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
    .form-text { font-size: 12px; color: #6b7280; margin-top: 4px; }
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
    .is-invalid { border-color: #c0392b; }
    .invalid-feedback { color: #c0392b; font-size: 12px; margin-top: 4px; }
    .form-actions { margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px; }
</style>
@endsection