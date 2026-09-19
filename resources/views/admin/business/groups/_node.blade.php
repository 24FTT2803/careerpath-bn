@php
    $children = $childrenByParent[$group->id] ?? [];

    /*
     * A filtered-out group still renders its branch, so a kept
     * child is never orphaned from the path it sits in.
     */
    $hidden = isset($visibleIds)
        && $visibleIds !== null
        && ! in_array($group->id, $visibleIds, true);
    $blocker = $blockers[$group->id] ?? null;
    $otherParents = $group->parents->where('id', '!=', $parentId);
@endphp

<div
    class="group-row"
    data-group-id="{{ $group->id }}"
    data-name="{{ strtolower($group->name.' '.$group->code) }}"
    style="padding-left: {{ 12 + $depth * 22 }}px{{ $hidden ? ';display:none' : '' }}"
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
        <a href="{{ route('admin.business.groups.create', [
            'parent' => $group->id,
            'organisation' => $group->organisation_id,
        ]) }}">
            <i class="fas fa-plus"></i> add inside
        </a>

        <a href="{{ route('admin.business.groups.edit', $group) }}">edit</a>

        <button
            type="button"
            class="group-move-toggle"
            data-move-target="move-{{ $group->id }}-{{ $parentId ?? 'root' }}"
        >move</button>

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

<div
    class="group-move"
    id="move-{{ $group->id }}-{{ $parentId ?? 'root' }}"
    style="display:none;padding-left: {{ 34 + $depth * 22 }}px"
>
    <form
        method="POST"
        action="{{ route('admin.business.groups.move', $group) }}"
    >
        @csrf
        @method('PUT')

        <input
            type="hidden"
            name="from_parent_id"
            value="{{ $parentId }}"
        >

        <span class="cell-sub">Move into</span>

        <select name="to_parent_id" class="field-input">
            <option value="">Top level</option>

            @foreach($moveOptions as $option)
                @continue($option->id === $group->id)

                <option
                    value="{{ $option->id }}"
                    @selected($option->id === $parentId)
                >{{ $option->path }}</option>
            @endforeach
        </select>

        <button type="submit" class="link">Move</button>

        @if(count($children) > 0)
            <span class="cell-sub">
                {{ count($children) }}
                {{ Str::plural('group', count($children)) }} move with it
            </span>
        @endif
    </form>
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
