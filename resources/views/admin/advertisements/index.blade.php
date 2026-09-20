@extends('admin.layouts.admin')

@section('title', 'Advertisements')

@section('content')
<style>
    /*
     * Shown at the shape a student sees, so a badly cropped
     * banner is obvious from the list rather than only once it
     * is live.
     */
    .ad-thumb {
        display: block;
        width: 160px;
        aspect-ratio: 6 / 1;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #e5e7eb;
        background: #f4f6f9;
    }
</style>

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

    @foreach($slots as $slot)
        @php
            $items = ($byPosition[$slot->position] ?? collect());
        @endphp

        <div class="card">
            <h3 class="card-heading">
                {{ $slot->name }}
            </h3>

            <!-- How this placement behaves -->
            <form
                method="POST"
                action="{{ route('admin.business.advertisements.slots.update', $slot) }}"
                style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;padding-bottom:14px;border-bottom:1px solid #f3f4f6;"
            >
                @csrf
                @method('PUT')

                <div>
                    <label class="field-label">Showing</label>

                    <select name="is_active" class="field-input" style="max-width:120px;">
                        <option value="1" @selected($slot->is_active)>On</option>
                        <option value="0" @selected(! $slot->is_active)>Off</option>
                    </select>
                </div>

                <div>
                    <label class="field-label">Rotation</label>

                    <select name="rotation_enabled" class="field-input" style="max-width:120px;">
                        <option value="1" @selected($slot->rotation_enabled)>On</option>
                        <option value="0" @selected(! $slot->rotation_enabled)>Off</option>
                    </select>
                </div>

                <div>
                    <label class="field-label">How many</label>

                    <input
                        type="number"
                        name="rotation_size"
                        value="{{ $slot->rotation_size }}"
                        min="1"
                        max="{{ App\Models\AdvertisementSlot::MAX_ROTATION_SIZE }}"
                        class="field-input"
                        style="max-width:90px;"
                    >
                </div>

                <div>
                    <label class="field-label">Seconds each</label>

                    <input
                        type="number"
                        name="dwell_seconds"
                        value="{{ $slot->dwell_seconds }}"
                        min="2"
                        max="120"
                        class="field-input"
                        style="max-width:90px;"
                    >
                </div>

                <button type="submit" class="btn btn-subtle">Save</button>

                <p class="field-hint" style="flex-basis:100%;">
                    The first
                    {{ $slot->rotation_enabled ? $slot->rotation_size : 1 }}
                    {{ Str::plural('advertisement', $slot->resolveCount()) }}
                    below that a student is eligible for will be
                    shown, in this order. A video plays to its end
                    before the next one appears.
                </p>
            </form>

            <!-- What appears here -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin:14px 0 8px;">
                <span class="cell-title">
                    {{ $items->count() }}
                    {{ Str::plural('advertisement', $items->count()) }}
                </span>

                <a
                    href="{{ route('admin.business.advertisements.create', ['position' => $slot->position]) }}"
                    class="btn btn-primary btn-sm"
                >
                    <i class="fas fa-plus"></i> Add to this placement
                </a>
            </div>

            @if($items->isEmpty())
                <p class="empty-text">Nothing here yet.</p>
            @else
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Preview</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Audience</th>
                            <th>Reach</th>
                            <th>Runs</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($items as $index => $advertisement)
                            <tr>
                                <td><div class="row-actions" style="justify-content:flex-start;">
                                    <form
                                        method="POST"
                                        action="{{ route('admin.business.advertisements.reorder', $advertisement) }}"
                                    >
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="direction" value="up">

                                        <button
                                            type="submit"
                                            class="link {{ $index === 0 ? 'link-disabled' : '' }}"
                                            @disabled($index === 0)
                                        >↑</button>
                                    </form>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.business.advertisements.reorder', $advertisement) }}"
                                    >
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="direction" value="down">

                                        <button
                                            type="submit"
                                            class="link {{ $index === $items->count() - 1 ? 'link-disabled' : '' }}"
                                            @disabled($index === $items->count() - 1)
                                        >↓</button>
                                    </form>
                                </div></td>

                                <td>
                                    @php
                                        $mediaUrl = $advertisement->mediaUrl();
                                    @endphp

                                    @if($advertisement->isNetworkEmbed())
                                        <span class="cell-sub">Network embed</span>
                                    @elseif($advertisement->isVideo() && $mediaUrl)
                                        <video
                                            src="{{ $mediaUrl }}"
                                            class="ad-thumb"
                                            muted
                                            playsinline
                                            preload="metadata"
                                        ></video>
                                    @elseif($mediaUrl)
                                        <img
                                            src="{{ $mediaUrl }}"
                                            alt=""
                                            class="ad-thumb"
                                            loading="lazy"
                                        >
                                    @else
                                        <span class="cell-sub">Text only</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="cell-title">{{ $advertisement->title }}</span>

                                    @if($advertisement->click_url)
                                        <span class="cell-sub">
                                            links to {{ parse_url($advertisement->click_url, PHP_URL_HOST) }}
                                        </span>
                                    @endif
                                </td>

                                <td class="capitalize">{{ $advertisement->type }}</td>

                                <td>
                                    {{ $advertisement->organisationGroup?->name ?? 'All students' }}
                                </td>

                                <td class="cell-sub">
                                    {{ $reach[$advertisement->id] ?? 0 }}
                                    {{ Str::plural('student', $reach[$advertisement->id] ?? 0) }}
                                </td>

                                <td class="cell-sub">
                                    {{ $advertisement->starts_at?->timezone(config('app.business_timezone'))->format('j M Y') ?? 'Immediately' }}
                                    &rarr;
                                    {{ $advertisement->ends_at?->timezone(config('app.business_timezone'))->format('j M Y') ?? 'No end' }}
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
                                </td>

                                <td><div class="row-actions">
                                    <a
                                        href="{{ route('admin.business.advertisements.edit', $advertisement) }}"
                                        class="link"
                                    >Edit</a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.business.advertisements.destroy', $advertisement) }}"
                                        onsubmit="return confirm('Delete {{ $advertisement->title }}?');"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="link link-danger">
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
    @endforeach
</div>
@endsection
