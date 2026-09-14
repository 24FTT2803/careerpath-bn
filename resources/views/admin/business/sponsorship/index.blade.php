@extends('admin.layouts.admin')

@section('title', 'Sponsorship')

@section('content')
<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🤝 Sponsorship</h1>
            <p class="text-gray-600">
                Organisations funding access for groups of students
            </p>
        </div>

        <a
            href="{{ route('admin.business.groups.index') }}"
            class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition"
        >
            <i class="fas fa-school"></i> Academic Groups
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

    <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-3 mb-6 rounded text-sm">
        <i class="fas fa-info-circle"></i>
        Sponsoring a group covers every student inside it, however
        deep. Sponsor a school and all of its classes are included.
    </div>

    <!-- Sponsors -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4">Sponsors</h3>

        @if($sponsors->isEmpty())
            <p class="text-gray-500 py-2">No sponsors yet.</p>
        @else
            <table class="w-full text-sm mb-4">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-200">
                        <th class="py-2">Name</th>
                        <th class="py-2">Code</th>
                        <th class="py-2">Funding</th>
                        <th class="py-2">Status</th>
                        <th class="py-2 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($sponsors as $sponsor)
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="py-3 font-medium">{{ $sponsor->name }}</td>

                            <td class="py-3 text-gray-500">
                                {{ $sponsor->code ?? '—' }}
                            </td>

                            <td class="py-3">
                                {{ $sponsor->sponsored_access_grants_count }}
                                {{ Str::plural('grant', $sponsor->sponsored_access_grants_count) }}
                            </td>

                            <td class="py-3">
                                @if($sponsor->is_active)
                                    <span class="text-green-600">Active</span>
                                @else
                                    <span class="text-gray-500">Suspended</span>
                                @endif
                            </td>

                            <td class="py-3 text-right">
                                <form
                                    method="POST"
                                    action="{{ route('admin.business.sponsorship.sponsors.toggle', $sponsor) }}"
                                    class="inline"
                                >
                                    @csrf
                                    @method('PUT')

                                    <button
                                        type="submit"
                                        class="text-blue-600 hover:underline bg-transparent border-0 cursor-pointer mr-3"
                                    >
                                        {{ $sponsor->is_active ? 'Suspend' : 'Reactivate' }}
                                    </button>
                                </form>

                                @if($sponsor->sponsored_access_grants_count === 0)
                                    <form
                                        method="POST"
                                        action="{{ route('admin.business.sponsorship.sponsors.destroy', $sponsor) }}"
                                        class="inline"
                                        onsubmit="return confirm('Delete {{ $sponsor->name }}?');"
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
                                @else
                                    <span
                                        class="text-gray-300"
                                        title="They still fund access"
                                    >Delete</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <form
            method="POST"
            action="{{ route('admin.business.sponsorship.sponsors.store') }}"
            class="flex gap-2 flex-wrap"
        >
            @csrf

            <input
                type="text"
                name="name"
                maxlength="120"
                required
                placeholder="Sponsor name"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
            >

            <input
                type="text"
                name="code"
                maxlength="40"
                placeholder="Code (optional)"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
            >

            <button
                type="submit"
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg text-sm transition"
            >
                Add sponsor
            </button>
        </form>
    </div>

    <!-- New sponsored access -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4">Fund access</h3>

        @if($activeSponsors->isEmpty())
            <p class="text-gray-500 py-2">
                Add an active sponsor first.
            </p>
        @else
            <form
                method="POST"
                action="{{ route('admin.business.sponsorship.grants.store') }}"
            >
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Sponsor
                        </label>

                        <select
                            name="business_sponsor_id"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2"
                        >
                            @foreach($activeSponsors as $sponsor)
                                <option
                                    value="{{ $sponsor->id }}"
                                    @selected((int) old('business_sponsor_id') === $sponsor->id)
                                >{{ $sponsor->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Plan
                        </label>

                        <select
                            name="plan_id"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2"
                        >
                            @foreach($plans as $plan)
                                <option
                                    value="{{ $plan->id }}"
                                    @selected((int) old('plan_id') === $plan->id)
                                >{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Covers
                        </label>

                        <select
                            name="organisation_group_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2"
                        >
                            <option value="">The whole institution</option>

                            @foreach($groupOptions as $group)
                                <option
                                    value="{{ $group->id }}"
                                    @selected((int) old('organisation_group_id') === $group->id)
                                >{{ $group->path }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Starts
                        </label>

                        <input
                            type="date"
                            name="starts_at"
                            value="{{ old('starts_at') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Ends
                        </label>

                        <input
                            type="date"
                            name="ends_at"
                            value="{{ old('ends_at') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Priority
                        </label>

                        <input
                            type="number"
                            name="priority"
                            min="0"
                            max="100"
                            value="{{ old('priority', 0) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2"
                        >

                        <p class="text-xs text-gray-500 mt-1">
                            Higher wins when two sponsorships cover
                            the same student.
                        </p>
                    </div>
                </div>

                <button
                    type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg transition"
                >
                    <i class="fas fa-plus"></i> Fund access
                </button>
            </form>
        @endif
    </div>

    <!-- Existing grants -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Sponsored access</h3>

        @if($grants->isEmpty())
            <p class="text-gray-500 text-center py-6">
                Nothing is sponsored yet.
            </p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-200">
                        <th class="py-2">Sponsor</th>
                        <th class="py-2">Plan</th>
                        <th class="py-2">Covers</th>
                        <th class="py-2">Runs</th>
                        <th class="py-2">Priority</th>
                        <th class="py-2">Status</th>
                        <th class="py-2 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($grants as $grant)
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="py-3 font-medium">
                                {{ $grant->sponsor?->name ?? '—' }}
                            </td>

                            <td class="py-3">{{ $grant->plan?->name ?? '—' }}</td>

                            <td class="py-3">
                                {{ $grant->organisationGroup?->name ?? 'Whole institution' }}
                            </td>

                            <td class="py-3 text-xs text-gray-600">
                                {{ $grant->starts_at?->format('j M Y') ?? 'Immediately' }}
                                &rarr;
                                {{ $grant->ends_at?->format('j M Y') ?? 'No end' }}
                            </td>

                            <td class="py-3">{{ $grant->priority }}</td>

                            <td class="py-3">
                                @if($grant->is_active)
                                    <span class="text-green-600">Active</span>
                                @else
                                    <span class="text-gray-500">Withdrawn</span>
                                @endif
                            </td>

                            <td class="py-3 text-right">
                                @if($grant->is_active)
                                    <form
                                        method="POST"
                                        action="{{ route('admin.business.sponsorship.grants.revoke', $grant) }}"
                                        class="inline"
                                        onsubmit="return confirm('Withdraw this sponsorship?');"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <button
                                            type="submit"
                                            class="text-red-600 hover:underline bg-transparent border-0 cursor-pointer"
                                        >
                                            Withdraw
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400">—</span>
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
