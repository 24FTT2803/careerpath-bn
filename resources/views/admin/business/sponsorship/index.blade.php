@extends('admin.layouts.admin')

@section('title', 'Sponsorship')

@section('content')
<style>
    /* The plan is fixed to Premium: shown like a field, but not editable. */
    .plan-fixed {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #faf7ee;
        border-color: rgba(201, 168, 76, 0.45);
        color: #8a6d22;
        font-weight: 600;
        cursor: default;
    }

    .plan-fixed .plan-fixed-lock {
        margin-left: auto;
        font-size: 12px;
        color: #b8a26a;
    }
</style>

<div>
    <div class="page-header">
        <div>
            <h1><i class="fas fa-handshake"></i> Sponsorship</h1>
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
            <div class="table-scroll">
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
                                <td colspan="2">
                                    <form
                                        method="POST"
                                        action="{{ route('admin.business.sponsorship.sponsors.update', $sponsor) }}"
                                        style="display:flex;gap:8px;align-items:center;"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <input
                                            type="text"
                                            name="name"
                                            value="{{ $sponsor->name }}"
                                            maxlength="120"
                                            required
                                            class="field-input"
                                            style="max-width:220px;"
                                        >

                                        <input
                                            type="text"
                                            name="code"
                                            value="{{ $sponsor->code }}"
                                            maxlength="40"
                                            placeholder="Code"
                                            class="field-input"
                                            style="max-width:110px;"
                                        >

                                        <button type="submit" class="link">Save</button>
                                    </form>
                                </td>

                                <td>
                                    <span class="cell-title">
                                        {{ $sponsor->active_grants_count }} active
                                    </span>

                                    @if($sponsor->sponsored_access_grants_count > $sponsor->active_grants_count)
                                        <span class="cell-sub">
                                            {{ $sponsor->sponsored_access_grants_count }} in total
                                        </span>
                                    @endif
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
            </div>
        @endif

        <div class="add-row">
            <span class="add-row-label">Add a sponsor</span>

            <form
                method="POST"
                action="{{ route('admin.business.sponsorship.sponsors.store') }}"
                class="inline-form"
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
                    class="field-input is-short"
                    style="flex-basis:160px;"
                >

                <button
                    type="submit"
                    class="btn btn-primary btn-sm"
                >
                    <i class="fas fa-plus"></i> Add sponsor
                </button>
            </form>
        </div>
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
                        <span class="field-label">
                            Plan
                        </span>

                        {{-- Sponsored access is always Premium, so there is nothing to choose. --}}
                        <div class="field-input plan-fixed" aria-label="Plan: Premium">
                            <i class="fas fa-crown"></i>
                            Premium
                            <i class="fas fa-lock plan-fixed-lock" title="Set automatically"></i>
                        </div>

                        <p class="field-hint">
                            Sponsored students always get Premium.
                        </p>
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

    <!-- Active sponsorship -->
    <div class="card">
        <h3 class="card-heading">In force ({{ $activeGrants->count() }})</h3>

        @if($activeGrants->isEmpty())
            <p class="empty-text">Nothing is sponsored at the moment.</p>
        @else
            <div class="table-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Sponsor</th>
                            <th>Plan</th>
                            <th>Covers</th>
                            <th>Runs</th>
                            <th>Priority</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($activeGrants as $grant)
                            <tr>
                                <td class="cell-title">{{ $grant->sponsor?->name ?? '—' }}</td>
                                <td>{{ $grant->plan?->name ?? '—' }}</td>
                                <td>{{ $grant->organisationGroup?->name ?? 'Whole institution' }}</td>

                                <td class="cell-sub">
                                    {{ $grant->starts_at?->timezone(config('app.business_timezone'))?->timezone(config('app.business_timezone'))->format('j M Y') ?? 'Immediately' }}
                                    &rarr;
                                    {{ $grant->ends_at?->timezone(config('app.business_timezone'))?->timezone(config('app.business_timezone'))->format('j M Y') ?? 'No end' }}
                                </td>

                                <td>{{ $grant->priority }}</td>

                                <td><div class="row-actions">
                                    <form
                                        method="POST"
                                        action="{{ route('admin.business.sponsorship.grants.revoke', $grant) }}"
                                        onsubmit="return confirm('Withdraw this sponsorship?');"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <button type="submit" class="link link-danger">
                                            Withdraw
                                        </button>
                                    </form>
                                </div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Withdrawn sponsorship -->
    @if($withdrawnGrants->isNotEmpty())
        <div class="card">
            <h3 class="card-heading">Previously sponsored ({{ $withdrawnGrants->count() }})</h3>

            <p class="field-hint" style="margin-bottom:12px;">
                Kept as a record of who funded what, and until when.
            </p>

            <div class="table-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Sponsor</th>
                            <th>Plan</th>
                            <th>Covered</th>
                            <th>Ended</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($withdrawnGrants as $grant)
                            <tr>
                                <td class="cell-title">{{ $grant->sponsor?->name ?? '—' }}</td>
                                <td>{{ $grant->plan?->name ?? '—' }}</td>
                                <td>{{ $grant->organisationGroup?->name ?? 'Whole institution' }}</td>

                                <td class="cell-sub">
                                    {{ $grant->ends_at?->timezone(config('app.business_timezone'))?->timezone(config('app.business_timezone'))->format('j M Y') ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
