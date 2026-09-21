@extends('admin.layouts.admin')

@section('title', 'Academic Groups')

@section('content')
<style>
    .group-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-top: 9px;
        padding-bottom: 9px;
        padding-right: 8px;
        border-bottom: 1px solid #f1f1f1;
        font-size: 14px;
    }

    .group-row:hover {
        background: #fafafa;
    }

    .group-main {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .group-toggle,
    .group-toggle-space {
        width: 14px;
        font-size: 12px;
        color: #9ca3af;
        cursor: pointer;
    }

    .group-toggle.collapsed {
        transform: rotate(-90deg);
    }

    .group-name {
        font-weight: 500;
        color: #111827;
    }

    .group-code {
        font-size: 11px;
        color: #6b7280;
        font-family: ui-monospace, monospace;
    }

    .group-chip {
        background: #f3f4f6;
        color: #4b5563;
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 6px;
    }

    .group-chip.archived {
        background: #fff7ed;
        color: #9a6700;
    }

    .group-meta {
        font-size: 12px;
        color: #9ca3af;
    }

    a.group-members {
        text-decoration: none;
    }

    a.group-members:hover {
        color: #2563eb;
        text-decoration: underline;
    }

    .group-actions {
        display: none;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
        font-size: 12px;
    }

    .group-row:hover .group-actions {
        display: flex;
    }

    .group-actions a,
    .group-actions button {
        color: #2563eb;
        background: none;
        border: 0;
        padding: 0;
        font-size: 12px;
        cursor: pointer;
        text-decoration: none;
        font-family: inherit;
    }

    .group-actions button.danger {
        color: #dc2626;
    }

    .group-actions form {
        display: inline;
    }

    .group-blocked {
        color: #d1d5db;
        cursor: not-allowed;
    }

    .group-move {
        padding-top: 8px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f1f1f1;
    }

    .group-move form {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .group-move select {
        max-width: 320px;
    }

    .group-actions .group-move-toggle {
        color: #2563eb;
        background: none;
        border: 0;
        padding: 0;
        font-size: 12px;
        cursor: pointer;
        font-family: inherit;
    }

    .group-also {
        font-size: 12px;
        color: #9ca3af;
        padding-bottom: 8px;
        border-bottom: 1px solid #f1f1f1;
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
            <h1>🏫 Academic Groups</h1>

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

        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;">
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
                                    style="display:flex;gap:8px;align-items:center;"
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
                                        style="max-width:240px;"
                                    >

                                    <input
                                        type="text"
                                        name="code"
                                        value="{{ $item->code }}"
                                        maxlength="40"
                                        placeholder="Code"
                                        class="field-input"
                                        style="max-width:100px;"
                                    >

                                    <button type="submit" class="link">Save</button>
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
                                    Open
                                </a>

                                @if($item->is_active)
                                    <form
                                        method="POST"
                                        action="{{ route('admin.business.organisations.archive', $item) }}"
                                    >
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="link">Archive</button>
                                    </form>
                                @else
                                    <form
                                        method="POST"
                                        action="{{ route('admin.business.organisations.restore', $item) }}"
                                    >
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="link">Restore</button>
                                    </form>
                                @endif

                                <form
                                    method="POST"
                                    action="{{ route('admin.business.organisations.destroy', $item) }}"
                                    onsubmit="return confirm('Delete {{ $item->name }} and everything in it?');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link link-danger">Delete</button>
                                </form>
                            </div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <form
            method="POST"
            action="{{ route('admin.business.organisations.store') }}"
            style="display:flex;gap:8px;flex-wrap:wrap;margin-top:14px;"
        >
            @csrf

            <input
                type="text"
                name="name"
                maxlength="150"
                required
                placeholder="Institution name"
                class="field-input"
                style="max-width:240px;"
            >

            <input
                type="text"
                name="code"
                maxlength="40"
                placeholder="Code"
                class="field-input"
                style="max-width:100px;"
            >

            <button type="submit" class="btn btn-subtle">Add</button>
        </form>
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
            <div style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;">
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

        <form
            method="POST"
            action="{{ route('admin.business.groups.types.store') }}"
            style="display:flex;gap:8px;margin-top:12px;"
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
                style="max-width:280px;"
            >

            <button type="submit" class="btn btn-subtle">Add type</button>
        </form>
    </div>

    <!-- Tree -->
    <div class="card">
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;">
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

        <input
            type="text"
            id="groupSearch"
            placeholder="Search groups"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-4 text-sm"
        >

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
