@extends('layouts.app')

@section('title', 'Innovation Lab')

@section('content')

<style>
    .lab-page {
        max-width: 800px;
        margin: 0 auto;
        padding: 24px 0 48px;
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
        margin-bottom: 24px;
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

    .lab-image {
        display: block;
        max-width: 100%;
        max-height: 360px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 14px;
    }

    .lab-body {
        font-size: 14px;
        line-height: 1.6;
        color: #1a1a2e;
        white-space: pre-line;
    }

    .lab-empty {
        text-align: center;
        color: #9ca3af;
        padding: 48px 0;
        font-size: 14px;
    }
</style>

<div class="lab-page">
    <h1 class="lab-heading">
        <i class="fas fa-lightbulb"></i> Innovation Lab
    </h1>
    <p class="lab-sub">Notes from the developers about what's new and what we're building.</p>

    @forelse($notes as $note)
        <article class="lab-note">
            <h2>{{ $note->title }}</h2>
            <div class="lab-meta">
                {{ $note->created_at->format('d M Y') }}
                @if($note->author)
                    &middot; {{ $note->author->name }}
                @endif
            </div>
            @if($note->imageUrl())
                <img
                    src="{{ $note->imageUrl() }}"
                    alt="{{ $note->title }}"
                    class="lab-image"
                >
            @endif
            <div class="lab-body">{{ $note->body }}</div>
        </article>
    @empty
        <p class="lab-empty">No notes yet. Check back soon.</p>
    @endforelse
</div>

@endsection
