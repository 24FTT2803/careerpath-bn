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
        <div class="filter-pills">
            @foreach([
                '' => 'All',
                'live' => 'Live',
                'expired' => 'Expired',
                'draft' => 'Drafts',
            ] as $value => $label)
                <a
                    href="{{ route('admin.business.innovation-lab.index', $value === '' ? [] : ['status' => $value]) }}"
                    class="btn btn-sm {{ $filter === $value ? 'btn-primary' : 'btn-subtle' }}"
                >
                    {{ $label }} ({{ $counts[$value === '' ? 'all' : $value] }})
                </a>
            @endforeach
        </div>

        @if($notes->isEmpty())
            <p class="empty-text">
                {{ $counts['all'] === 0
                    ? "No notes yet. Create one to let students know what you're building."
                    : 'No notes match this filter.' }}
            </p>
        @else
            <div class="table-scroll">
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
                                    @if($note->imageUrl())
                                        <img
                                            src="{{ $note->imageUrl() }}"
                                            alt=""
                                            style="width:56px;height:56px;object-fit:cover;border-radius:6px;border:1px solid var(--border);float:left;margin-right:12px;"
                                        >
                                    @endif
                                    <span class="cell-title">{{ $note->title }}</span>
                                    <span
                                        class="cell-sub"
                                        style="display:-webkit-box;-webkit-line-clamp:2;line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;max-width:420px;"
                                    >{{ $note->body }}</span>
                                </td>
                                <td>{{ $note->author->name ?? 'Unknown' }}</td>
                                <td>
                                    @php $daysLeft = $note->daysLeft(); @endphp

                                    @switch($note->status())
                                        @case('draft')
                                            <span class="status-pill status-pill-muted">Draft</span>
                                            @break
                                        @case('expired')
                                            <span class="status-pill status-pill-muted" title="Hidden from students">
                                                <i class="fas fa-eye-slash"></i> Expired
                                            </span>
                                            @break
                                        @default
                                            <span class="status-pill status-pill-green">Live</span>
                                    @endswitch

                                    <span class="cell-sub" style="margin-top:4px;">
                                        @if($daysLeft === null)
                                            No expiry
                                        @elseif($daysLeft < 0)
                                            Ended {{ $note->show_until->format('d M Y') }}
                                        @elseif($daysLeft === 0)
                                            Last day today
                                        @else
                                            Until {{ $note->show_until->format('d M Y') }}
                                            ({{ $daysLeft }} {{ \Illuminate\Support\Str::plural('day', $daysLeft) }} left)
                                        @endif
                                    </span>
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
                                            data-confirm="Delete {{ $note->title }}? This cannot be undone."
                                            data-confirm-title="Delete Note"
                                            data-confirm-ok="Yes, Delete"
                                            data-confirm-type="danger"
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
            </div>
        @endif
    </div>
</div>
@endsection
