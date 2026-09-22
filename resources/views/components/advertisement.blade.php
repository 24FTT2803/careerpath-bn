{{--
    Not named "slot": Blade reserves that for a component's own
    content, so binding it here is silently overridden and the
    settings arrive as a ComponentSlot instead.
--}}
@props([
    'advertisements' => null,
    'placement' => null,
])

@php
    $items = collect($advertisements ?? []);
@endphp

@if($items->isNotEmpty())
    @php
        $rotates = $items->count() > 1
            && $placement?->rotation_enabled;

        $dwell = max(2, (int) ($placement?->dwell_seconds ?? 8));
    @endphp

    <aside
        class="ad-slot"
        aria-label="Advertisement"
        @if($rotates)
            data-ad-rotate
            data-ad-dwell="{{ $dwell }}"
        @endif
    >
        <span class="ad-slot-label">
            Advertisement
        </span>

        @foreach($items as $index => $advertisement)
            <div
                class="ad-slot-item"
                @if($rotates && $index > 0) hidden @endif
            >
                <x-advertisement-item :advertisement="$advertisement" />
            </div>
        @endforeach
    </aside>
@endif
