@extends('admin.layouts.admin')

@section('title', 'Innovation Lab')

@section('content')
<div>
    <div class="page-header">
        <div>
            <h1><i class="fas fa-lightbulb"></i> Innovation Lab</h1>
            <p class="subtitle">
                Developer notes shown to students from the Innovation Lab button on their dashboard
            </p>
        </div>

        <a
            href="{{ route('admin.business.innovation-lab.create') }}"
            class="btn btn-primary"
        >
            <i class="fas fa-plus"></i> New Note
        </a>
    </div>

    <div class="card">
        @if($notes->isEmpty())
            <p class="empty-text">No notes yet. Create one to let students know what you're building.</p>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Note</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Posted</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notes as $note)
                        <tr>
                            <td>
                                <span class="cell-title">{{ $note->title }}</span>
                                <span class="cell-sub">{{ \Illuminate\Support\Str::limit($note->body, 90) }}</span>
                            </td>
                            <td>{{ $note->author->name ?? 'Unknown' }}</td>
                            <td>
                                @if($note->is_published)
                                    <span class="status-pill status-pill-green">Published</span>
                                @else
                                    <span class="status-pill status-pill-muted">Draft</span>
                                @endif
                            </td>
                            <td>{{ $note->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="row-actions">
                                    <a
                                        href="{{ route('admin.business.innovation-lab.edit', $note) }}"
                                        class="link"
                                    >Edit</a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.business.innovation-lab.destroy', $note) }}"
                                        onsubmit="return confirm('Delete {{ $note->title }}?');"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="link link-danger">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
