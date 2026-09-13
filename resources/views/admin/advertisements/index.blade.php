@extends('admin.layouts.admin')

@section('title', 'Advertisements')

@section('content')
<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">📣 Advertisements</h1>
            <p class="text-gray-600">
                Manage what appears in the student ad placements
            </p>
        </div>

        <a
            href="{{ route('admin.business.advertisements.create') }}"
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition"
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

    <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-3 mb-6 rounded text-sm">
        <i class="fas fa-info-circle"></i>
        Whether these appear at all is controlled on
        <a
            href="{{ route('admin.business.plans.index') }}"
            class="underline"
        >Plans &amp; Features</a>,
        and students can switch advertising off in their own settings.
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        @if($advertisements->isEmpty())
            <p class="text-gray-500 text-center py-6">
                No advertisements yet.
            </p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-200">
                        <th class="py-2">Title</th>
                        <th class="py-2">Type</th>
                        <th class="py-2">Position</th>
                        <th class="py-2">Audience</th>
                        <th class="py-2">Status</th>
                        <th class="py-2 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($advertisements as $advertisement)
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="py-3">
                                <span class="font-medium">
                                    {{ $advertisement->title }}
                                </span>
                            </td>

                            <td class="py-3 capitalize">
                                {{ $advertisement->type }}
                            </td>

                            <td class="py-3 capitalize">
                                {{ $advertisement->position }}
                            </td>

                            <td class="py-3">
                                {{ $advertisement->organisationGroup?->name ?? 'Everyone' }}
                            </td>

                            <td class="py-3">
                                @if($advertisement->is_active)
                                    <span class="text-green-600">Active</span>
                                @else
                                    <span class="text-gray-500">Inactive</span>
                                @endif

                                @if($advertisement->ends_at)
                                    <span class="text-xs text-gray-500 block">
                                        until {{ $advertisement->ends_at->format('j M Y') }}
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 text-right">
                                <a
                                    href="{{ route('admin.business.advertisements.edit', $advertisement) }}"
                                    class="text-blue-600 hover:underline mr-3"
                                >
                                    Edit
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('admin.business.advertisements.destroy', $advertisement) }}"
                                    class="inline"
                                    onsubmit="return confirm('Delete this advertisement?');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="text-red-600 hover:underline bg-transparent border-0 cursor-pointer"
                                    >
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
