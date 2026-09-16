@php
    $children = $childrenByParent[$group->id] ?? [];
    $blocker = $blockers[$group->id] ?? null;
    $otherParents = $group->parents->where('id', '!=', $parentId);
@endphp

<div
    class="group-row"
    data-group-id="{{ $group->id }}"
    data-name="{{ strtolower($group->name.' '.$group->code) }}"
    style="padding-left: {{ 12 + $depth * 22 }}px"
>
    <span class="group-main">
        @if(count($children) > 0)
            <i class="fas fa-chevron-down group-toggle"></i>
        @else
            <span class="group-toggle-space"></span>
        @endif

        <span class="group-name">{{ $group->name }}</span>

        @if($group->code)
            <span class="group-code">{{ $group->code }}</span>
        @endif

        <span class="group-chip">{{ $group->type?->name ?? 'No type' }}</span>

        <a
            href="{{ route('admin.business.groups.members.index', $group) }}"
            class="group-meta group-members"
            title="Manage who belongs to this group"
        >
            {{ $group->memberships_count }} direct

            @if(($reach[$group->id] ?? 0) !== $group->memberships_count)
                &middot; {{ $reach[$group->id] ?? 0 }} total
            @endif
        </a>

        @unless($group->is_active)
            <span class="group-chip archived">Archived</span>
        @endunless
    </span>

    <span class="group-actions">
        <a href="{{ route('admin.business.groups.create', ['parent' => $group->id]) }}">
            <i class="fas fa-plus"></i> add inside
        </a>

        <a href="{{ route('admin.business.groups.edit', $group) }}">edit</a>

        @if($group->is_active)
            <form
                method="POST"
                action="{{ route('admin.business.groups.archive', $group) }}"
                onsubmit="return confirm('Archive {{ $group->name }}?');"
            >
                @csrf
                @method('PUT')
                <button type="submit">archive</button>
            </form>
        @else
            <form
                method="POST"
                action="{{ route('admin.business.groups.restore', $group) }}"
            >
                @csrf
                @method('PUT')
                <button type="submit">restore</button>
            </form>
        @endif

        @if($blocker)
            <span class="group-blocked" title="{{ $blocker }}">delete</span>
        @else
            <form
                method="POST"
                action="{{ route('admin.business.groups.destroy', $group) }}"
                onsubmit="return confirm('Delete {{ $group->name }} permanently?');"
            >
                @csrf
                @method('DELETE')
                <button type="submit" class="danger">delete</button>
            </form>
        @endif
    </span>
</div>

@if($otherParents->isNotEmpty())
    <div class="group-also" style="padding-left: {{ 34 + $depth * 22 }}px">
        <i class="fas fa-arrow-right"></i>
        also under
        {{ $otherParents->pluck('name')->join(', ') }}
    </div>
@endif

@if(count($children) > 0 && $depth < 6)
    <div class="group-branch">
        @foreach($children as $child)
            @include('admin.business.groups._node', [
                'group' => $child,
                'parentId' => $group->id,
                'depth' => $depth + 1,
            ])
        @endforeach
    </div>
@endif
