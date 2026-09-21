@extends('admin.layouts.admin')

@section('title', 'Group Members')

@section('content')
<div>
    <div class="page-header">
        <div>
            <h1>
                👥 {{ $group->name }}
            </h1>
            <p class="subtitle">
                {{ $group->pathLabel() }}
            </p>
        </div>

        <a
            href="{{ route('admin.business.groups.index') }}"
            class="btn btn-outline"
        >
            <i class="fas fa-arrow-left"></i> Back to groups
        </a>
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

    @if($isProgramme)
        <div class="info-banner info-banner-gold">
            <i class="fas fa-triangle-exclamation"></i>
            Membership of a programme follows the student's own
            profile. Removing someone here lasts only until they
            next save their profile — change their programme
            instead.
        </div>
    @else
        <div class="info-banner info-banner-blue">
            <i class="fas fa-info-circle"></i>
            Members of this group are also covered by anything
            aimed at the groups above it.
        </div>
    @endif

    <!-- Current members -->
    <div class="card">
        <h3 class="card-heading">
            Members ({{ $members->count() }})
        </h3>

        @if($members->isEmpty())
            <p class="empty-text">
                Nobody belongs to this group yet.
            </p>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Student ID</th>
                        <th>Programme</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($members as $member)
                        <tr>
                            <td>
                                <span class="cell-title">{{ $member->name }}</span>

                                <span class="cell-sub">
                                    {{ $member->email }}
                                </span>
                            </td>

                            <td class="cell-sub">
                                {{ $member->student_id ?? '—' }}
                            </td>

                            <td>
                                {{ $member->programme ?? 'Not set' }}

                                @if(in_array($member->id, $mismatched, true))
                                    <span
                                        class="text-yellow-600 text-xs block"
                                        title="Their profile names a different programme than this group sits under"
                                    >
                                        <i class="fas fa-triangle-exclamation"></i>
                                        does not match this branch
                                    </span>
                                @endif
                            </td>

                            <td><div class="row-actions">
                                <a
                                    href="{{ route('admin.students.show', $member->id) }}"
                                    class="link"
                                >
                                    View
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('admin.business.groups.members.destroy', [$group, $member]) }}"
                                    class="inline"
                                    onsubmit="return confirm('Remove {{ $member->name }} from {{ $group->name }}?');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="link link-danger"
                                    >
                                        Remove
                                    </button>
                                </form>
                            </div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Add members -->
    <div class="card">
        <h3 class="card-heading">Add students</h3>

        <form
            method="GET"
            action="{{ route('admin.business.groups.members.index', $group) }}"
            class="flex gap-2 mb-4"
        >
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Search by name, email or student ID"
                class="field-input"
            >

            <button
                type="submit"
                class="btn btn-subtle"
            >
                Search
            </button>
        </form>

        @if($search === '')
            <p class="cell-sub">
                Search for a student to add them.
            </p>
        @elseif($candidates->isEmpty())
            <p class="cell-sub">
                No students found for "{{ $search }}".
            </p>
        @else
            <form
                method="POST"
                action="{{ route('admin.business.groups.members.store', $group) }}"
            >
                @csrf

                <div class="border border-gray-200 rounded-lg p-3 mb-4">
                    @foreach($candidates as $candidate)
                        <label class="flex items-center gap-2 py-1 cursor-pointer">
                            <input
                                type="checkbox"
                                name="user_ids[]"
                                value="{{ $candidate->id }}"
                            >

                            <span>
                                {{ $candidate->name }}

                                <span class="text-xs text-gray-400">
                                    {{ $candidate->student_id ?? $candidate->email }}
                                    &middot;
                                    {{ $candidate->programme ?? 'No programme' }}
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="fas fa-plus"></i> Add selected
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
