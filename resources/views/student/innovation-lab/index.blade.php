@extends('layouts.app')

@section('title', 'Innovation Lab')

@section('content')

<style>
    .lab-page {
        max-width: 800px;
        margin: 0 auto;
        padding: 24px 0 48px;
    }

    .lab-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 24px;
    }

    .lab-heading {
        font-size: 22px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .lab-heading i {
        color: #c9a84c;
    }

    .lab-sub {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 0;
    }

    .lab-note {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px 24px;
        margin-bottom: 16px;
    }

    .lab-note h2 {
        font-size: 17px;
        font-weight: 600;
        color: #1a3a5c;
        margin: 0 0 6px;
    }

    .lab-meta {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 12px;
    }

    .lab-image-frame {
        display: flex;
        justify-content: center;
        background: #000;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 14px;
    }

    .lab-image {
        display: block;
        width: auto;
        height: auto;
        max-width: 100%;
        max-height: 560px;
    }

    .lab-body {
        font-size: 14px;
        line-height: 1.6;
        color: #1a1a2e;
        white-space: pre-line;
    }

    .lab-body a {
        color: #2a5a8c;
        text-decoration: underline;
        overflow-wrap: anywhere;
    }

    .lab-body a:hover {
        color: #c9a84c;
    }

    .lab-empty {
        text-align: center;
        color: #9ca3af;
        padding: 48px 0;
        font-size: 14px;
    }

    /* Posts added since the student's last visit */
    .lab-note.is-new {
        border-color: rgba(201, 168, 76, 0.55);
        box-shadow: 0 0 0 3px rgba(201, 168, 76, 0.12);
    }

    .lab-note .lab-new-pill {
        margin-left: 8px;
        vertical-align: middle;
    }
</style>

<div class="lab-page">
    <div class="lab-header">
        <div>
            <h1 class="lab-heading">
                <i class="fas fa-lightbulb"></i> Innovation Lab
            </h1>
            <p class="lab-sub">Notes from the developers about what's new and what we're building.</p>
        </div>
    </div>

    @forelse($notes as $note)
        @php
            $postedAt = $note->published_at ?? $note->created_at;
            $isNew = $note->published_at && (! $newSince || $note->published_at->gt($newSince));
        @endphp
        <article class="lab-note {{ $isNew ? 'is-new' : '' }}">
            <h2>
                {{ $note->title }}
                @if($isNew)
                    <span class="attn-pill lab-new-pill" title="Posted since your last visit">New</span>
                @endif
            </h2>
            <div class="lab-meta">
                {{ $postedAt->format('d M Y') }}
            </div>
            @if($note->imageUrl())
                <div class="lab-image-frame">
                    <img
                        src="{{ $note->imageUrl() }}"
                        alt="{{ $note->title }}"
                        class="lab-image"
                    >
                </div>
            @endif
            <div class="lab-body">{{ $note->bodyHtml() }}</div>
        </article>
    @empty
        <p class="lab-empty">No notes yet. Check back soon.</p>
    @endforelse
</div>

@endsection
