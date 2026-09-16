@extends('admin.layouts.admin')

@section('title', 'Sponsorship')

@section('content')
<div>
    <div class="page-header">
        <div>
            <h1>🤝 Sponsorship</h1>
            <p class="subtitle">
                Organisations funding access for groups of students
            </p>
        </div>

        <a
            href="{{ route('admin.business.groups.index') }}"
            class="btn btn-outline"
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

    <div class="info-banner info-banner-blue">
        <i class="fas fa-info-circle"></i>
        Sponsoring a group covers every student inside it, however
        deep. Sponsor a school and all of its classes are included.
    </div>

    <!-- Sponsors -->
    <div class="card">
        <h3 class="card-heading">Sponsors</h3>

        @if($sponsors->isEmpty())
            <p class="empty-text">No sponsors yet.</p>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Funding</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($sponsors as $sponsor)
                        <tr>
                            <td class="cell-title">{{ $sponsor->name }}</td>

                            <td class="cell-sub">
                                {{ $sponsor->code ?? '—' }}
                            </td>

                            <td>
                                {{ $sponsor->sponsored_access_grants_count }}
                                {{ Str::plural('grant', $sponsor->sponsored_access_grants_count) }}
                            </td>

                            <td>
                                @if($sponsor->is_active)
                                    <span class="status-pill status-pill-green">Active</span>
                                @else
                                    <span class="status-pill status-pill-muted">Suspended</span>
                                @endif
                            </td>

                            <td><div class="row-actions">
                                <form
                                    method="POST"
                                    action="{{ route('admin.business.sponsorship.sponsors.toggle', $sponsor) }}"
                                    class="inline"
                                >
                                    @csrf
                                    @method('PUT')

                                    <button
                                        type="submit"
                                        class="link"
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
                                            class="link link-danger"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                @else
                                    <span
                                        class="link link-disabled"
                                        title="They still fund access"
                                    >Delete</span>
                                @endif
                            </div></td>
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
                class="field-input"
            >

            <input
                type="text"
                name="code"
                maxlength="40"
                placeholder="Code (optional)"
                class="field-input"
            >

            <button
                type="submit"
                class="btn btn-subtle"
            >
                Add sponsor
            </button>
        </form>
    </div>

    <!-- New sponsored access -->
    <div class="card">
        <h3 class="card-heading">Fund access</h3>

        @if($activeSponsors->isEmpty())
            <p class="empty-text">
                Add an active sponsor first.
            </p>
        @else
            <form
                method="POST"
                action="{{ route('admin.business.sponsorship.grants.store') }}"
            >
                @csrf

                <div class="field-grid field-grid-3">
                    <div>
                        <label class="field-label">
                            Sponsor
                        </label>

                        <select
                            name="business_sponsor_id"
                            required
                            class="field-input"
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

                    <div>
                        <label class="field-label">
                            Covers
                        </label>

                        <select
                            name="organisation_group_id"
                            class="field-input"
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

                <div class="field-grid field-grid-3">
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
                    </div>

                    <div>
                        <label class="field-label">
                            Priority
                        </label>

                        <input
                            type="number"
                            name="priority"
                            min="0"
                            max="100"
                            value="{{ old('priority', 0) }}"
                            class="field-input"
                        >

                        <p class="field-hint">
                            Higher wins when two sponsorships cover
                            the same student.
                        </p>
                    </div>
                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="fas fa-plus"></i> Fund access
                </button>
            </form>
        @endif
    </div>

    <!-- Existing grants -->
    <div class="card">
        <h3 class="card-heading">Sponsored access</h3>

        @if($grants->isEmpty())
            <p class="empty-text">
                Nothing is sponsored yet.
            </p>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Sponsor</th>
                        <th>Plan</th>
                        <th>Covers</th>
                        <th>Runs</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($grants as $grant)
                        <tr>
                            <td class="cell-title">
                                {{ $grant->sponsor?->name ?? '—' }}
                            </td>

                            <td>{{ $grant->plan?->name ?? '—' }}</td>

                            <td>
                                {{ $grant->organisationGroup?->name ?? 'Whole institution' }}
                            </td>

                            <td class="cell-sub">
                                {{ $grant->starts_at?->format('j M Y') ?? 'Immediately' }}
                                &rarr;
                                {{ $grant->ends_at?->format('j M Y') ?? 'No end' }}
                            </td>

                            <td>{{ $grant->priority }}</td>

                            <td>
                                @if($grant->is_active)
                                    <span class="status-pill status-pill-green">Active</span>
                                @else
                                    <span class="status-pill status-pill-muted">Withdrawn</span>
                                @endif
                            </td>

                            <td><div class="row-actions">
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
                                            class="link link-danger"
                                        >
                                            Withdraw
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
