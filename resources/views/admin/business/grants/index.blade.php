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
                Every account already has a live grant.
            </p>
        @else
            <form
                method="POST"
                action="{{ route('admin.business.grants.store') }}"
            >
                @csrf

                <div class="field-grid field-grid-2">
                    <div>
                        <label class="field-label">
                            Account
                        </label>

                        <select
                            name="user_id"
                            required
                            class="field-input"
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
                        <label class="field-label">
                            Plan
                        </label>

                        <select
                            name="plan_id"
                            required
                            class="field-input"
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

    <!-- Existing grants -->
    <div class="card">
        <h3 class="card-heading">Existing grants</h3>

        @if($grants->isEmpty())
            <p class="empty-text">
                No access has been granted yet.
            </p>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Account</th>
                        <th>Plan</th>
                        <th>Reason</th>
                        <th>Runs</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($grants as $grant)
                        <tr>
                            <td>
                                <span class="cell-title">
                                    {{ $grant->user?->name ?? 'Deleted account' }}
                                </span>

                                <span class="cell-sub">
                                    {{ $grant->user?->email }}
                                </span>
                            </td>

                            <td>
                                {{ $grant->plan?->name ?? '—' }}
                            </td>

                            <td class="capitalize">
                                {{ $grant->source }}
                            </td>

                            <td class="cell-sub">
                                {{ $grant->starts_at?->format('j M Y') ?? 'Immediately' }}
                                &rarr;
                                {{ $grant->ends_at?->format('j M Y') ?? 'No end' }}
                            </td>

                            <td>
                                @if($grant->is_active)
                                    <span class="status-pill status-pill-green">Active</span>
                                @else
                                    <span class="status-pill status-pill-muted">Revoked</span>
                                @endif
                            </td>

                            <td><div class="row-actions">
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
                                            class="link link-danger"
                                        >
                                            Revoke
                                        </button>
                                    </form>
                                @else
                                    <span class="cell-sub">—</span>
                                @endif
                            </div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
