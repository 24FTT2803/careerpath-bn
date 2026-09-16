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
                Your institution's structure, however you choose to arrange it
            </p>
        </div>

        <a
            href="{{ route('admin.business.groups.create') }}"
            class="btn btn-primary"
        >
            <i class="fas fa-plus"></i> New top-level group
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

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

    <!-- Types -->
    <div class="card">
        <h3 class="card-heading">Group types</h3>

        <p class="text-gray-500 text-sm mb-3">
            Name the levels your institution actually uses. A type
            can only be removed while no group uses it.
        </p>

        <div class="flex flex-wrap gap-2 items-center mb-4">
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

        <form
            method="POST"
            action="{{ route('admin.business.groups.types.store') }}"
            class="flex gap-2"
        >
            @csrf

            <input
                type="text"
                name="name"
                maxlength="60"
                required
                placeholder="Intake Session"
                class="field-input"
            >

            <button
                type="submit"
                class="btn btn-subtle"
            >
                Add type
            </button>
        </form>
    </div>

    <!-- Tree -->
    <div class="card">
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
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.group-toggle').forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                var row = toggle.closest('.group-row');
                var branch = row.nextElementSibling;

                while (branch && !branch.classList.contains('group-branch')) {
                    branch = branch.nextElementSibling;
                }

                if (!branch) {
                    return;
                }

                var hidden = branch.style.display === 'none';
                branch.style.display = hidden ? '' : 'none';
                toggle.classList.toggle('collapsed', !hidden);
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
