@extends('admin.layouts.admin')

@section('title', 'Academic Groups')

@section('content')
<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🏫 Academic Groups</h1>
            <p class="text-gray-600">
                Schools, programmes, intakes, sessions and classes
            </p>
        </div>

        <a
            href="{{ route('admin.business.groups.create') }}"
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition"
        >
            <i class="fas fa-plus"></i> New Group
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-3 mb-6 rounded text-sm">
        <i class="fas fa-info-circle"></i>
        Sponsorship and advertising can be aimed at any of these,
        so a group covers everyone inside it.
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        @if($groups->isEmpty())
            <p class="text-gray-500 text-center py-6">
                No groups yet.
            </p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-200">
                        <th class="py-2">Name</th>
                        <th class="py-2">Type</th>
                        <th class="py-2">Sits inside</th>
                        <th class="py-2">Members</th>
                        <th class="py-2">Status</th>
                        <th class="py-2 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($groups as $group)
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="py-3">
                                <span class="font-medium">{{ $group->name }}</span>

                                @if($group->code)
                                    <span class="text-xs text-gray-500 block">
                                        {{ $group->code }}
                                    </span>
                                @endif
                            </td>

                            <td class="py-3">
                                {{ $group->type?->name ?? '—' }}
                            </td>

                            <td class="py-3 text-gray-600">
                                {{ $group->primaryParent()?->name ?? 'Top level' }}
                            </td>

                            <td class="py-3">
                                {{ $group->memberships_count }}
                            </td>

                            <td class="py-3">
                                @if($group->is_active)
                                    <span class="text-green-600">Active</span>
                                @else
                                    <span class="text-gray-500">Archived</span>
                                @endif
                            </td>

                            <td class="py-3 text-right">
                                <a
                                    href="{{ route('admin.business.groups.edit', $group) }}"
                                    class="text-blue-600 hover:underline mr-3"
                                >
                                    Edit
                                </a>

                                @if($group->is_active)
                                    <form
                                        method="POST"
                                        action="{{ route('admin.business.groups.archive', $group) }}"
                                        class="inline"
                                        onsubmit="return confirm('Archive this group?');"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <button
                                            type="submit"
                                            class="text-yellow-600 hover:underline bg-transparent border-0 cursor-pointer"
                                        >
                                            Archive
                                        </button>
                                    </form>
                                @else
                                    <form
                                        method="POST"
                                        action="{{ route('admin.business.groups.restore', $group) }}"
                                        class="inline"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <button
                                            type="submit"
                                            class="text-green-600 hover:underline bg-transparent border-0 cursor-pointer"
                                        >
                                            Restore
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
