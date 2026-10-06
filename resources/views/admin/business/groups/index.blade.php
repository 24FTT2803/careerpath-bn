@extends('admin.layouts.admin')

@section('title', 'Academic Groups')

@section('content')
<style>
    /* ---------- Structure tree ---------- */
    .tree-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 14px;
    }

    .tree-legend {
        font-size: 11px;
        color: #9ca3af;
    }

    #groupTree {
        border: 1px solid #f0f1f3;
        border-radius: 10px;
        overflow: hidden;
    }

    .group-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        min-height: 48px;
        padding-top: 8px;
        padding-bottom: 8px;
        padding-right: 12px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 14px;
        background: white;
        transition: background-color 0.15s ease;
    }

    .group-row:hover,
    .group-row:focus-within {
        background: #fcfbf7;
    }

    .group-main {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        flex: 1;
    }

    .group-toggle,
    .group-toggle-space {
        flex-shrink: 0;
        width: 18px;
        height: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        font-size: 11px;
        color: #9ca3af;
        cursor: pointer;
        transition: transform 0.2s ease, background-color 0.15s ease, color 0.15s ease;
    }

    .group-toggle:hover {
        background: #f3f4f6;
        color: var(--primary);
    }

    .group-toggle.collapsed {
        transform: rotate(-90deg);
    }

    .group-name {
        font-weight: 500;
        color: #111827;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .group-row[data-depth="0"] .group-name {
        font-weight: 700;
        color: var(--primary);
    }

    .group-code {
        flex-shrink: 0;
        font-size: 10.5px;
        color: #6b7280;
        font-family: ui-monospace, monospace;
        background: #f3f4f6;
        padding: 1px 6px;
        border-radius: 4px;
    }

    .group-chip {
        flex-shrink: 0;
        background: #eef2f7;
        color: #3d5a7a;
        font-size: 11px;
        font-weight: 500;
        padding: 2px 9px;
        border-radius: 999px;
    }

    .group-chip.archived {
        background: #fff7ed;
        color: #9a6700;
    }

    .group-meta {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        color: #6b7280;
        padding: 2px 8px;
        border-radius: 999px;
        border: 1px solid transparent;
    }

    a.group-members {
        text-decoration: none;
        margin-left: auto;
        min-width: 120px;
        justify-content: flex-end;
    }

    a.group-members:hover {
        color: var(--primary);
        border-color: var(--border);
        background: white;
    }

    a.group-members i {
        font-size: 10px;
        color: #9ca3af;
    }

    /* Actions: always in the same place, quiet until the row is in use. */
    .group-actions {
        display: flex;
        align-items: center;
        gap: 4px;
        flex-shrink: 0;
        opacity: 0.35;
        transition: opacity 0.15s ease;
    }

    .group-row:hover .group-actions,
    .group-row:focus-within .group-actions {
        opacity: 1;
    }

    .group-actions form {
        display: inline;
    }

    .group-actions a,
    .group-actions button,
    .group-actions .group-blocked {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 9px;
        border: 1px solid var(--border);
        border-radius: 6px;
        background: white;
        color: var(--primary);
        font-size: 12px;
        font-weight: 600;
        font-family: inherit;
        line-height: 1.3;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .group-actions a i,
    .group-actions button i {
        font-size: 10px;
    }

    .group-actions a:hover,
    .group-actions button:hover {
        background: #faf7ee;
        border-color: var(--accent);
    }

    .group-actions .action-divider {
        width: 1px;
        height: 18px;
        margin: 0 4px;
        background: var(--border);
    }

    .group-actions button.danger {
        color: var(--danger);
    }

    .group-actions button.danger:hover {
        background: #fdf1f0;
        border-color: #f0b8b1;
    }

    .group-actions .group-blocked {
        color: #c3c7ce;
        background: #fafafa;
        cursor: not-allowed;
    }

    .group-move {
        padding-top: 10px;
        padding-bottom: 12px;
        padding-right: 12px;
        border-bottom: 1px solid #f3f4f6;
        background: #fcfbf7;
    }

    .group-move form {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .group-move select {
        max-width: 360px;
    }

    .group-move .link {
        padding: 8px 14px;
        border-radius: 8px;
        background: var(--accent);
        color: var(--primary-dark);
        text-decoration: none;
    }

    .group-also {
        font-size: 12px;
        color: #9ca3af;
        padding-top: 4px;
        padding-bottom: 8px;
        border-bottom: 1px solid #f3f4f6;
        background: white;
    }

    .group-also i {
        font-size: 10px;
        margin-right: 4px;
    }

    /* Guide line from a parent down through its children. */
    .group-branch {
        position: relative;
    }

    .group-branch::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: calc(12px + var(--depth, 0) * 22px + 8px);
        border-left: 1px dashed #e2dccb;
        pointer-events: none;
        z-index: 1;
    }

    @media (max-width: 900px) {
        .group-row {
            flex-wrap: wrap;
        }

        .group-actions {
            opacity: 1;
            width: 100%;
            flex-wrap: wrap;
        }
    }

    .type-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 12px;
        padding: 4px 10px;
        border-radius: 6px;
    }

    .type-chip form {
        display: inline;
    }

    .type-chip button {
        background: none;
        border: 0;
        color: #1d4ed8;
        cursor: pointer;
        padding: 0;
        font-size: 12px;
    }

    .type-chip.in-use button {
        color: #93c5fd;
        cursor: not-allowed;
    }
</style>

<div>
    <div class="page-header">
        <div>
            <h1><i class="fas fa-school"></i> Academic Groups</h1>

            <p class="subtitle">
                @if($organisation)
                    {{ $organisation->name }}
                @else
                    Choose an institution to work in
                @endif
            </p>
        </div>

        @if($organisation)
            <div class="header-actions">
                <a
                    href="{{ route('admin.business.groups.index') }}"
                    class="btn btn-outline"
                >
                    <i class="fas fa-arrow-left"></i> All organisations
                </a>

                <a
                    href="{{ route('admin.business.groups.create', ['organisation' => $organisation->id]) }}"
                    class="btn btn-primary"
                >
                    <i class="fas fa-plus"></i> New top-level group
                </a>
            </div>
        @endif
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

@if($organisation === null)

    <!-- Choose an organisation -->
    <div class="card">
        <h3 class="card-heading">
            Organisations ({{ $organisationCounts['all'] }})
        </h3>

        <p class="field-hint" style="margin-bottom:12px;">
            Each owns its own group types and its own structure.
            A new one starts with a single group named after it.
        </p>

        <div class="filter-pills">
            @foreach([
                '' => 'All',
                'active' => 'Active',
                'archived' => 'Archived',
            ] as $value => $label)
                @php
                    $key = $value === '' ? 'all' : $value;
                    $current = $organisationFilter === $value;
                @endphp

                <a
                    href="{{ route('admin.business.groups.index', $value === '' ? [] : ['org_status' => $value]) }}"
                    class="btn btn-sm {{ $current ? 'btn-primary' : 'btn-subtle' }}"
                >
                    {{ $label }} ({{ $organisationCounts[$key] }})
                </a>
            @endforeach
        </div>

        @if($allOrganisations->isEmpty())
            <p class="empty-text">No organisations yet.</p>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Organisation</th>
                        <th>Structure</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($allOrganisations as $item)
                        <tr>
                            <td>
                                <form
                                    method="POST"
                                    action="{{ route('admin.business.organisations.update', $item) }}"
                                    class="inline-form"
                                >
                                    @csrf
                                    @method('PUT')

                                    <input
                                        type="text"
                                        name="name"
                                        value="{{ $item->name }}"
                                        maxlength="150"
                                        required
                                        class="field-input"
                                        aria-label="Organisation name"
                                    >

                                    <input
                                        type="text"
                                        name="code"
                                        value="{{ $item->code }}"
                                        maxlength="40"
                                        placeholder="Code"
                                        class="field-input is-short"
                                        aria-label="Code"
                                    >

                                    <button type="submit" class="btn btn-sm btn-subtle">
                                        <i class="fas fa-check"></i> Save
                                    </button>
                                </form>
                            </td>

                            <td class="cell-sub">
                                {{ $item->groups_count }}
                                {{ Str::plural('group', $item->groups_count) }}
                                &middot;
                                {{ $item->group_types_count }}
                                {{ Str::plural('type', $item->group_types_count) }}
                            </td>

                            <td>
                                @if($item->is_active)
                                    <span class="status-pill status-pill-green">Active</span>
                                @else
                                    <span class="status-pill status-pill-muted">Archived</span>
                                @endif
                            </td>

                            <td><div class="row-actions">
                                <a
                                    href="{{ route('admin.business.groups.index', ['organisation' => $item->id]) }}"
                                    class="link"
                                >
                                    <i class="fas fa-folder-open"></i> Open
                                </a>

                                @if($item->is_active)
                                    <form
                                        method="POST"
                                        action="{{ route('admin.business.organisations.archive', $item) }}"
                                    >
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="link"><i class="fas fa-box-archive"></i> Archive</button>
                                    </form>
                                @else
                                    <form
                                        method="POST"
                                        action="{{ route('admin.business.organisations.restore', $item) }}"
                                    >
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="link"><i class="fas fa-rotate-left"></i> Restore</button>
                                    </form>
                                @endif

                                <form
                                    method="POST"
                                    action="{{ route('admin.business.organisations.destroy', $item) }}"
                                    onsubmit="return confirm('Delete {{ $item->name }} and everything in it?');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link link-danger"><i class="fas fa-trash-alt"></i> Delete</button>
                                </form>
                            </div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="add-row">
            <span class="add-row-label">Add an institution</span>

            <form
                method="POST"
                action="{{ route('admin.business.organisations.store') }}"
                class="inline-form"
            >
                @csrf

                <input
                    type="text"
                    name="name"
                    maxlength="150"
                    required
                    placeholder="Institution name"
                    class="field-input"
                >

                <input
                    type="text"
                    name="code"
                    maxlength="40"
                    placeholder="Code"
                    class="field-input is-short"
                >

                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add
                </button>
            </form>
        </div>
    </div>

@else

    <!-- Types -->
    <div class="card">
        <h3 class="card-heading">Group types</h3>

        <p class="field-hint" style="margin-bottom:12px;">
            The levels {{ $organisation->name }} uses. Only these
            are offered when creating a group here.
        </p>

        @if($types->isEmpty())
            <p class="field-hint">No types yet.</p>
        @else
            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                @foreach($types as $type)
                    @php $usage = $typeUsage[$type->id] ?? 0; @endphp

                    <span class="type-chip {{ $usage > 0 ? 'in-use' : '' }}">
                        {{ $type->name }}

                        @if($usage > 0)
                            <button
                                type="button"
                                title="Used by {{ $usage }} {{ Str::plural('group', $usage) }}"
                            >&times;</button>
                        @else
                            <form
                                method="POST"
                                action="{{ route('admin.business.groups.types.destroy', $type) }}"
                                onsubmit="return confirm('Remove the {{ $type->name }} type?');"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit">&times;</button>
                            </form>
                        @endif
                    </span>
                @endforeach
            </div>
        @endif

        <div class="add-row">
            <span class="add-row-label">Add a type</span>

            <form
                method="POST"
                action="{{ route('admin.business.groups.types.store') }}"
                class="inline-form"
            >
                @csrf

                <input
                    type="hidden"
                    name="organisation_id"
                    value="{{ $organisation->id }}"
                >

                <input
                    type="text"
                    name="name"
                    maxlength="60"
                    required
                    placeholder="New type for {{ $organisation->name }}"
                    class="field-input"
                    style="max-width:360px;"
                >

                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add type
                </button>
            </form>
        </div>
    </div>

    <!-- Tree -->
    <div class="card">
        <div class="tree-card-head">
            <h3 class="card-heading" style="margin:0;">Structure</h3>

            <div class="tree-legend">
                <span><i class="fas fa-users"></i> members: direct &middot; total including groups inside</span>
            </div>
        </div>

        <div class="filter-pills">
            @foreach([
                '' => 'All',
                'active' => 'Active',
                'archived' => 'Archived',
            ] as $value => $label)
                @php
                    $key = $value === '' ? 'all' : $value;
                    $current = $filter === $value;
                @endphp

                <a
                    href="{{ route('admin.business.groups.index', array_filter(['organisation' => $organisation->id, 'status' => $value])) }}"
                    class="btn btn-sm {{ $current ? 'btn-primary' : 'btn-subtle' }}"
                >
                    {{ $label }} ({{ $counts[$key] }})
                </a>
            @endforeach
        </div>

        <div class="search-field">
            <i class="fas fa-search"></i>

            <input
                type="text"
                id="groupSearch"
                placeholder="Search groups by name or code"
                class="field-input"
            >
        </div>

        @if($roots->isEmpty())
            <p class="empty-text">
                No groups yet. Start with a top-level group such as
                your institution.
            </p>
        @else
            <div id="groupTree">
                @foreach($roots as $root)
                    @include('admin.business.groups._node', [
                        'group' => $root,
                        'parentId' => null,
                        'depth' => 0,
                    ])
                @endforeach
            </div>
        @endif
    </div>
@endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        /*
         * Collapsed branches are remembered between visits.
         * Reopening every branch on each load loses the place
         * an administrator was working in.
         */
        var STORE_KEY = 'careerpath.groups.collapsed';

        function readCollapsed() {
            try {
                return JSON.parse(
                    window.localStorage.getItem(STORE_KEY) || '[]'
                );
            } catch (error) {
                return [];
            }
        }

        function writeCollapsed(ids) {
            try {
                window.localStorage.setItem(
                    STORE_KEY,
                    JSON.stringify(ids)
                );
            } catch (error) {
                // Storage unavailable; the tree simply will not
                // remember, which is not worth failing over.
            }
        }

        function branchFor(row) {
            var branch = row.nextElementSibling;

            while (branch && !branch.classList.contains('group-branch')) {
                branch = branch.nextElementSibling;
            }

            return branch;
        }

        function setCollapsed(toggle, row, collapsed) {
            var branch = branchFor(row);

            if (!branch) {
                return;
            }

            branch.style.display = collapsed ? 'none' : '';
            toggle.classList.toggle('collapsed', collapsed);
        }

        var collapsed = readCollapsed();

        document.querySelectorAll('.group-toggle').forEach(function (toggle) {
            var row = toggle.closest('.group-row');
            var id = row.dataset.groupId;

            if (id && collapsed.indexOf(id) !== -1) {
                setCollapsed(toggle, row, true);
            }

            toggle.addEventListener('click', function () {
                var branch = branchFor(row);

                if (!branch) {
                    return;
                }

                var nowCollapsed = branch.style.display !== 'none';

                setCollapsed(toggle, row, nowCollapsed);

                var stored = readCollapsed();
                var position = stored.indexOf(id);

                if (nowCollapsed && position === -1) {
                    stored.push(id);
                } else if (!nowCollapsed && position !== -1) {
                    stored.splice(position, 1);
                }

                writeCollapsed(stored);
            });
        });

        /*
         * The move form stays hidden until asked for, so a tree
         * of any size is still readable.
         */
        document.querySelectorAll('.group-move-toggle').forEach(function (button) {
            button.addEventListener('click', function () {
                var panel = document.getElementById(
                    button.dataset.moveTarget
                );

                if (!panel) {
                    return;
                }

                var hidden = panel.style.display === 'none';

                document.querySelectorAll('.group-move').forEach(function (other) {
                    other.style.display = 'none';
                });

                panel.style.display = hidden ? '' : 'none';
            });
        });

        var search = document.getElementById('groupSearch');

        if (search) {
            search.addEventListener('input', function () {
                var term = search.value.trim().toLowerCase();
                var rows = document.querySelectorAll('.group-row');

                document.querySelectorAll('.group-branch').forEach(function (branch) {
                    branch.style.display = '';
                });

                if (term === '') {
                    rows.forEach(function (row) {
                        row.style.display = '';
                    });

                    return;
                }

                rows.forEach(function (row) {
                    row.style.display = 'none';
                });

                rows.forEach(function (row) {
                    if ((row.dataset.name || '').indexOf(term) === -1) {
                        return;
                    }

                    row.style.display = '';

                    var branch = row.closest('.group-branch');

                    while (branch) {
                        var ancestor = branch.previousElementSibling;

                        while (ancestor && !ancestor.classList.contains('group-row')) {
                            ancestor = ancestor.previousElementSibling;
                        }

                        if (!ancestor) {
                            break;
                        }

                        ancestor.style.display = '';
                        branch = ancestor.closest('.group-branch');
                    }
                });
            });
        }
    });
</script>
@endsection
