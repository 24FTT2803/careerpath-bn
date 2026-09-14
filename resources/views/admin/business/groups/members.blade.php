@extends('admin.layouts.admin')

@section('title', 'Group Members')

@section('content')
<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                👥 {{ $group->name }}
            </h1>
            <p class="text-gray-600">
                {{ $group->pathLabel() }}
            </p>
        </div>

        <a
            href="{{ route('admin.business.groups.index') }}"
            class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition"
        >
            <i class="fas fa-arrow-left"></i> Back to groups
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

    @if($isProgramme)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 p-3 mb-6 rounded text-sm">
            <i class="fas fa-triangle-exclamation"></i>
            Membership of a programme follows the student's own
            profile. Removing someone here lasts only until they
            next save their profile — change their programme
            instead.
        </div>
    @else
        <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-3 mb-6 rounded text-sm">
            <i class="fas fa-info-circle"></i>
            Members of this group are also covered by anything
            aimed at the groups above it.
        </div>
    @endif

    <!-- Current members -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4">
            Members ({{ $members->count() }})
        </h3>

        @if($members->isEmpty())
            <p class="text-gray-500 text-center py-6">
                Nobody belongs to this group yet.
            </p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-200">
                        <th class="py-2">Name</th>
                        <th class="py-2">Student ID</th>
                        <th class="py-2">Programme</th>
                        <th class="py-2 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($members as $member)
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="py-3">
                                <span class="font-medium">{{ $member->name }}</span>

                                <span class="text-xs text-gray-500 block">
                                    {{ $member->email }}
                                </span>
                            </td>

                            <td class="py-3 text-gray-600">
                                {{ $member->student_id ?? '—' }}
                            </td>

                            <td class="py-3">
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

                            <td class="py-3 text-right">
                                <a
                                    href="{{ route('admin.students.show', $member->id) }}"
                                    class="text-blue-600 hover:underline mr-3"
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
                                        class="text-red-600 hover:underline bg-transparent border-0 cursor-pointer"
                                    >
                                        Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Add members -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Add students</h3>

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
                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm"
            >

            <button
                type="submit"
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg text-sm transition"
            >
                Search
            </button>
        </form>

        @if($search === '')
            <p class="text-gray-500 text-sm">
                Search for a student to add them.
            </p>
        @elseif($candidates->isEmpty())
            <p class="text-gray-500 text-sm">
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

                            <span class="text-sm text-gray-700">
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
                    class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg transition"
                >
                    <i class="fas fa-plus"></i> Add selected
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
