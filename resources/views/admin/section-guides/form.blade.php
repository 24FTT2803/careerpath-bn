@extends('admin.layouts.admin')

@section('title', 'Edit Guide')

@section('content')
<div>
    <div class="page-header">
        <div>
            <h1><i class="fas fa-circle-question"></i> Edit Guide</h1>
            <p class="subtitle">
                {{ $guide->pageLabel() }} &middot; {{ $guide->title }}
            </p>
        </div>

        <a
            href="{{ route('admin.business.guides.index') }}"
            class="btn btn-outline"
        >
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card" style="max-width:720px;">
        <form
            method="POST"
            action="{{ route('admin.business.guides.update', $guide) }}"
        >
            @csrf
            @method('PUT')

            <div style="margin-bottom:16px;">
                <label class="field-label">Title</label>
                <input
                    type="text"
                    name="title"
                    class="field-input"
                    value="{{ old('title', $guide->title) }}"
                    maxlength="120"
                    required
                >
                @error('title')
                    <div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom:16px;">
                <label class="field-label">Help text</label>
                <textarea
                    name="body"
                    class="field-input"
                    rows="10"
                    maxlength="5000"
                    required
                >{{ old('body', $guide->body) }}</textarea>
                <div class="field-hint">Leave a blank line between paragraphs.</div>
                @error('body')
                    <div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom:20px;">
                <label class="field-label">Video link (optional)</label>
                <input
                    type="url"
                    name="video_url"
                    class="field-input"
                    value="{{ old('video_url', $guide->video_url) }}"
                    maxlength="500"
                    placeholder="https://www.youtube.com/watch?v=..."
                >
                <div class="field-hint">
                    A YouTube link plays inside the help window, as does a direct
                    link to an .mp4 or .webm file. Any other link is shown as
                    "Watch the video". Leave empty for text only.
                </div>
                @error('video_url')
                    <div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>

        @if($hasDefault)
            <form
                method="POST"
                action="{{ route('admin.business.guides.restore', $guide) }}"
                data-confirm="Replace this guide's title and text with the original? Your edits will be lost. A video link is kept."
                data-confirm-title="Restore Default Text"
                data-confirm-ok="Yes, Restore"
                data-confirm-type="warning"
                style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);"
            >
                @csrf

                <button type="submit" class="btn btn-outline">
                    <i class="fas fa-rotate-left"></i> Restore default text
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
