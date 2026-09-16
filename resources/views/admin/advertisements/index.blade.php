@extends('admin.layouts.admin')

@section('title', 'Advertisements')

@section('content')
<div>
    <div class="page-header">
        <div>
            <h1>📣 Advertisements</h1>
            <p class="subtitle">
                Manage what appears in the student ad placements
            </p>
        </div>

        <a
            href="{{ route('admin.business.advertisements.create') }}"
            class="btn btn-primary"
        >
            <i class="fas fa-plus"></i> New Advertisement
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="info-banner info-banner-blue">
        <i class="fas fa-info-circle"></i>
        Whether these appear at all is controlled on
        <a
            href="{{ route('admin.business.plans.index') }}"
            class="underline"
        >Plans &amp; Features</a>,
        and students can switch advertising off in their own settings.
    </div>

    <div class="card">
        <div class="flex flex-wrap gap-2 mb-4">
            @foreach([
                '' => 'All',
                'live' => 'Live',
                'scheduled' => 'Scheduled',
                'ended' => 'Ended',
                'paused' => 'Paused',
            ] as $value => $label)
                @php
                    $key = $value === '' ? 'all' : $value;
                    $current = $filter === $value;
                @endphp

                <a
                    href="{{ route('admin.business.advertisements.index', $value === '' ? [] : ['status' => $value]) }}"
                    class="px-3 py-1 rounded-lg text-sm {{ $current ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                >
                    {{ $label }} ({{ $counts[$key] }})
                </a>
            @endforeach
        </div>

        @if($advertisements->isEmpty())
            <p class="empty-text">
                No advertisements yet.
            </p>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Position</th>
                        <th>Audience</th>
                        <th>Reach</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($advertisements as $advertisement)
                        <tr>
                            <td>
                                <span class="cell-title">
                                    {{ $advertisement->title }}
                                </span>
                            </td>

                            <td class="capitalize">
                                {{ $advertisement->type }}
                            </td>

                            <td class="capitalize">
                                {{ $advertisement->position }}
                            </td>

                            <td>
                                {{ $advertisement->organisationGroup?->name ?? 'All students' }}
                            </td>

                            <td class="cell-sub">
                                {{ $reach[$advertisement->id] ?? 0 }}
                                {{ Str::plural('student', $reach[$advertisement->id] ?? 0) }}
                            </td>

                            <td>
                                @php $status = $advertisement->status(); @endphp

                                @if($status === 'live')
                                    <span class="status-pill status-pill-green">Live</span>
                                @elseif($status === 'scheduled')
                                    <span class="status-pill status-pill-blue">Scheduled</span>
                                @elseif($status === 'ended')
                                    <span class="status-pill status-pill-muted">Ended</span>
                                @else
                                    <span class="status-pill status-pill-gold">Paused</span>
                                @endif

                                <span class="cell-sub">
                                    @if($status === 'scheduled')
                                        from {{ $advertisement->starts_at->format('j M Y') }}
                                    @elseif($advertisement->ends_at)
                                        until {{ $advertisement->ends_at->format('j M Y') }}
                                    @endif
                                </span>
                            </td>

                            <td><div class="row-actions">
                                <a
                                    href="{{ route('admin.business.advertisements.edit', $advertisement) }}"
                                    class="link"
                                >
                                    Edit
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('admin.business.advertisements.destroy', $advertisement) }}"
                                    class="inline"
                                    onsubmit="return confirm('Delete this advertisement?');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="link link-danger"
                                    >
                                        Delete
                                    </button>
                                </form>
                            </div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
