@extends('admin.layouts.admin')

@section('title', 'Access Grants')

@section('content')
<div>
    <div class="page-header">
        <div>
            <h1>🎟️ Access Grants</h1>
            <p class="subtitle">
                Give an individual account the benefits of a plan
            </p>
        </div>

        <a
            href="{{ route('admin.business.plans.index') }}"
            class="btn btn-outline"
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

    <div class="info-banner info-banner-blue">
        <i class="fas fa-info-circle"></i>
        Use this for special and test accounts. A grant overrides
        whatever plan the account would otherwise fall under, for
        as long as it runs.
    </div>

    <!-- New grant -->
    <div class="card">
        <h3 class="card-heading">Grant access</h3>

        @if($users->isEmpty())
            <p class="empty-text">
                Every student already has a live grant.
            </p>
        @else
            <form
                method="POST"
                action="{{ route('admin.business.grants.store') }}"
            >
                @csrf

                <div class="field-grid">
                    <div>
                        <label class="field-label">
                            Student
                        </label>

                        <select
                            name="user_id"
                            required
                            class="field-input"
                        >
                            <option value="">Choose a student</option>

                            @foreach($users as $user)
                                <option
                                    value="{{ $user->id }}"
                                    @selected((int) old('user_id') === $user->id)
                                >
                                    {{ $user->name }} — {{ $user->student_id ?? $user->email }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="field-grid field-grid-3">
                    <div>
                        <label class="field-label">
                            Reason
                        </label>

                        <select
                            name="source"
                            class="field-input"
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
                        <label class="field-label">
                            Starts
                        </label>

                        <input
                            type="date"
                            name="starts_at"
                            value="{{ old('starts_at') }}"
                            class="field-input"
                        >

                        <p class="field-hint">
                            Leave empty to start immediately.
                        </p>
                    </div>

                    <div>
                        <label class="field-label">
                            Ends
                        </label>

                        <input
                            type="date"
                            name="ends_at"
                            value="{{ old('ends_at') }}"
                            class="field-input"
                        >

                        <p class="field-hint">
                            Leave empty for no end date.
                        </p>
                    </div>
                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="fas fa-plus"></i> Grant access
                </button>
            </form>
        @endif
    </div>

    <!-- Active grants -->
    <div class="card">
        <h3 class="card-heading">In force ({{ $activeGrants->count() }})</h3>

        @if($activeGrants->isEmpty())
            <p class="empty-text">Nobody has been granted access.</p>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Reason</th>
                        <th>Runs</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($activeGrants as $grant)
                        <tr>
                            <td>
                                <span class="cell-title">
                                    {{ $grant->user?->name ?? 'Deleted account' }}
                                </span>
                                <span class="cell-sub">
                                    {{ $grant->user?->student_id ?? $grant->user?->email }}
                                </span>
                            </td>

                            <td class="capitalize">{{ $grant->source }}</td>

                            <td class="cell-sub">
                                {{ $grant->starts_at?->format('j M Y') ?? 'Immediately' }}
                                &rarr;
                                {{ $grant->ends_at?->format('j M Y') ?? 'No end' }}
                            </td>

                            <td><div class="row-actions">
                                <form
                                    method="POST"
                                    action="{{ route('admin.business.grants.revoke', $grant) }}"
                                    onsubmit="return confirm('Revoke this access?');"
                                >
                                    @csrf
                                    @method('PUT')

                                    <button type="submit" class="link link-danger">
                                        Revoke
                                    </button>
                                </form>
                            </div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Revoked grants -->
    @if($revokedGrants->isNotEmpty())
        <div class="card">
            <h3 class="card-heading">Previously granted ({{ $revokedGrants->count() }})</h3>

            <p class="field-hint" style="margin-bottom:12px;">
                Kept as a record of who was given access and when
                it was withdrawn.
            </p>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Reason</th>
                        <th>Ended</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($revokedGrants as $grant)
                        <tr>
                            <td>
                                <span class="cell-title">
                                    {{ $grant->user?->name ?? 'Deleted account' }}
                                </span>
                                <span class="cell-sub">
                                    {{ $grant->user?->student_id ?? $grant->user?->email }}
                                </span>
                            </td>

                            <td class="capitalize">{{ $grant->source }}</td>

                            <td class="cell-sub">
                                {{ $grant->ends_at?->format('j M Y') ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
