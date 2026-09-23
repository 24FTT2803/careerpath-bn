@extends('admin.layouts.admin')

@section('title', $note->exists ? 'Edit Note' : 'New Note')

@section('content')
<div>
    <div class="page-header">
        <div>
            <h1><i class="fas fa-lightbulb"></i> {{ $note->exists ? 'Edit Note' : 'New Note' }}</h1>
            <p class="subtitle">
                {{ $note->exists ? 'Update this Innovation Lab note.' : 'Post a developer note for students to see.' }}
            </p>
        </div>

        <a
            href="{{ route('admin.business.innovation-lab.index') }}"
            class="btn btn-outline"
        >
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card" style="max-width:720px;">
        <form
            method="POST"
            action="{{ $note->exists
                ? route('admin.business.innovation-lab.update', $note)
                : route('admin.business.innovation-lab.store') }}"
        >
            @csrf
            @if($note->exists)
                @method('PUT')
            @endif

            <div style="margin-bottom:16px;">
                <label class="field-label">Title</label>
                <input
                    type="text"
                    name="title"
                    class="field-input"
                    value="{{ old('title', $note->title) }}"
                    maxlength="150"
                    required
                >
                @error('title')
                    <div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom:16px;">
                <label class="field-label">Note</label>
                <textarea
                    name="body"
                    class="field-input"
                    rows="8"
                    required
                >{{ old('body', $note->body) }}</textarea>
                @error('body')
                    <div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text);">
                    <input
                        type="checkbox"
                        name="is_published"
                        value="1"
                        @checked(old('is_published', $note->is_published))
                    >
                    Published (visible to students)
                </label>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ $note->exists ? 'Save Changes' : 'Post Note' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
