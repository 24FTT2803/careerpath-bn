@extends('admin.layouts.admin')

@section('title', 'Edit Competency')

@section('content')
<div class="biicf-page">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-edit"></i> Edit Competency</h1>
            <p class="text-muted">Update "{{ $competency->name }}"</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.biicf.competencies.show', $competency) }}" class="btn btn-outline">
                <i class="fas fa-eye"></i> View
            </a>
            <a href="{{ route('admin.biicf.competencies') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card" style="max-width: 900px;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.biicf.competencies.update', $competency) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $competency->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                        <option value="technical" {{ old('type', $competency->type) == 'technical' ? 'selected' : '' }}>Technical</option>
                        <option value="soft_skill" {{ old('type', $competency->type) == 'soft_skill' ? 'selected' : '' }}>Soft Skill</option>
                    </select>
                    <small class="form-text">
                        The type decides the scale: technical competencies are rated over
                        five levels, soft skills over four. Changing it changes which levels
                        can be described below.
                    </small>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Definition</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                              rows="8">{{ old('description', $competency->description) }}</textarea>
                    <small class="form-text">The framework's own definition, as it is printed.</small>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{--
                    The part a student actually reads. "Apply" on its
                    own says nothing about what applying this
                    competency looks like; these statements are what
                    the book puts under each rung.

                    A rung left empty has no row at all, which is how
                    the framework's N/A is recorded — so clearing one
                    here says "this competency does not reach that
                    level", not "it reaches it and we have nothing to
                    say".
                --}}
                <h3 class="section-heading">What it means at each level</h3>

                @foreach($sets as $index => $set)
                    <div class="descriptor-set">
                        <div class="set-head">
                            <h4>{{ $set['scope'] !== '' ? $set['scope'] : 'All roles' }}</h4>
                            @if($set['job_roles']->isNotEmpty())
                                <span class="set-note">
                                    {{ $set['job_roles']->pluck('title')->join(', ') }}
                                </span>
                            @endif
                        </div>

                        {{--
                            The scope travels with the form but is not
                            editable: the roles a scoped set covers are
                            matched from this text, and renaming it here
                            would quietly detach them.
                        --}}
                        <input type="hidden" name="descriptors[{{ $index }}][scope]" value="{{ $set['scope'] }}">

                        @foreach($set['levels'] as $row)
                            <div class="form-group level-group">
                                <label>
                                    {{ $row['level']->level_number }}. {{ $row['level']->name }}
                                    @if($row['descriptor'] === null)
                                        <span class="badge-na">not described</span>
                                    @endif
                                </label>
                                <textarea name="descriptors[{{ $index }}][levels][{{ $row['level']->id }}]"
                                          class="form-control" rows="4"
                                          placeholder="One statement per line. Leave empty for N/A.">{{ old(
                                              "descriptors.$index.levels.".$row['level']->id,
                                              collect($row['descriptor']->statements ?? [])->join("\n")
                                          ) }}</textarea>
                            </div>
                        @endforeach
                    </div>
                @endforeach

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Competency
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
    .header-actions { display: flex; gap: 10px; flex-wrap: wrap; }
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
    textarea.form-control { line-height: 1.7; resize: vertical; }
    .is-invalid { border-color: #c0392b; }
    .invalid-feedback { color: #c0392b; font-size: 12px; margin-top: 4px; }
    .form-text { display: block; font-size: 12px; color: #6b7280; margin-top: 4px; }
    .form-actions { margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px; }
    .section-heading { margin: 28px 0 12px; font-family: 'Playfair Display', serif; font-size: 20px; color: #1a3a5c; }
    .descriptor-set { border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px; margin-bottom: 16px; background: #fdfcf9; }
    .set-head { display: flex; flex-wrap: wrap; align-items: baseline; gap: 10px; margin-bottom: 14px; }
    .set-head h4 { margin: 0; font-size: 15px; font-weight: 700; color: #1a3a5c; }
    .set-note { font-size: 12px; color: #6b7280; }
    .level-group { margin-bottom: 14px; }
    .level-group:last-child { margin-bottom: 0; }
    .badge-na { margin-left: 8px; padding: 2px 8px; border-radius: 999px; background: #f3f4f6; color: #6b7280; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
</style>
@endsection
