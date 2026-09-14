@extends('admin.layouts.admin')

@section('title', 'Access Grants')

@section('content')
<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🎟️ Access Grants</h1>
            <p class="text-gray-600">
                Give an individual account the benefits of a plan
            </p>
        </div>

        <a
            href="{{ route('admin.business.plans.index') }}"
            class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition"
        >
            <i class="fas fa-sliders-h"></i> Plans &amp; Features
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
        Use this for special and test accounts. A grant overrides
        whatever plan the account would otherwise fall under, for
        as long as it runs.
    </div>

    <!-- New grant -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4">Grant access</h3>

        @if($users->isEmpty())
            <p class="text-gray-500 py-2">
                Every account already has a live grant.
            </p>
        @else
            <form
                method="POST"
                action="{{ route('admin.business.grants.store') }}"
            >
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Account
                        </label>

                        <select
                            name="user_id"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2"
                        >
                            <option value="">Choose an account</option>

                            @foreach($users as $user)
                                <option
                                    value="{{ $user->id }}"
                                    @selected((int) old('user_id') === $user->id)
                                >
                                    {{ $user->name }} — {{ $user->email }} ({{ $user->role }})
                                </option>
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
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Reason
                        </label>

                        <select
                            name="source"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2"
                        >
                            @foreach([
                                'admin' => 'Administrator decision',
                                'trial' => 'Trial',
                                'sponsorship' => 'Sponsorship',
                            ] as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(old('source', 'admin') === $value)
                                >{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

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

                        <p class="text-xs text-gray-500 mt-1">
                            Leave empty to start immediately.
                        </p>
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

                        <p class="text-xs text-gray-500 mt-1">
                            Leave empty for no end date.
                        </p>
                    </div>
                </div>

                <button
                    type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg transition"
                >
                    <i class="fas fa-plus"></i> Grant access
                </button>
            </form>
        @endif
    </div>

    <!-- Existing grants -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Existing grants</h3>

        @if($grants->isEmpty())
            <p class="text-gray-500 text-center py-6">
                No access has been granted yet.
            </p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-200">
                        <th class="py-2">Account</th>
                        <th class="py-2">Plan</th>
                        <th class="py-2">Reason</th>
                        <th class="py-2">Runs</th>
                        <th class="py-2">Status</th>
                        <th class="py-2 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($grants as $grant)
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="py-3">
                                <span class="font-medium">
                                    {{ $grant->user?->name ?? 'Deleted account' }}
                                </span>

                                <span class="text-xs text-gray-500 block">
                                    {{ $grant->user?->email }}
                                </span>
                            </td>

                            <td class="py-3">
                                {{ $grant->plan?->name ?? '—' }}
                            </td>

                            <td class="py-3 capitalize">
                                {{ $grant->source }}
                            </td>

                            <td class="py-3 text-xs text-gray-600">
                                {{ $grant->starts_at?->format('j M Y') ?? 'Immediately' }}
                                &rarr;
                                {{ $grant->ends_at?->format('j M Y') ?? 'No end' }}
                            </td>

                            <td class="py-3">
                                @if($grant->is_active)
                                    <span class="text-green-600">Active</span>
                                @else
                                    <span class="text-gray-500">Revoked</span>
                                @endif
                            </td>

                            <td class="py-3 text-right">
                                @if($grant->is_active)
                                    <form
                                        method="POST"
                                        action="{{ route('admin.business.grants.revoke', $grant) }}"
                                        class="inline"
                                        onsubmit="return confirm('Revoke this access?');"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <button
                                            type="submit"
                                            class="text-red-600 hover:underline bg-transparent border-0 cursor-pointer"
                                        >
                                            Revoke
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
