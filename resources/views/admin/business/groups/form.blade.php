@extends('admin.layouts.admin')

@section('title', $group->exists ? 'Edit Group' : 'New Group')

@section('content')
@php
    $isEdit = $group->exists;

    $selectedPrimary = old('primary_parent_id', $primaryParentId);

    $selectedOthers = collect(
        old('parent_ids', $currentParentIds)
    )
        ->map(fn ($id) => (int) $id)
        ->reject(fn ($id) => $id === (int) $selectedPrimary)
        ->all();
@endphp

<div>
    <div class="page-header">
        <div>
            <h1>
                {{ $isEdit ? '✏️ Edit Group' : '🏫 New Group' }}
            </h1>
            <p class="subtitle">
                Where this group sits in the structure
            </p>
        </div>

        <a
            href="{{ route('admin.business.groups.index', array_filter([
                'organisation' => $lockedOrganisationId,
            ])) }}"
            class="btn btn-outline"
        >
            <i class="fas fa-arrow-left"></i> Back
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

    <form
        method="POST"
        action="{{
            $isEdit
                ? route('admin.business.groups.update', $group)
                : route('admin.business.groups.store')
        }}"
        class="card"
    >
        @csrf

        @if($isEdit)
            @method('PUT')
        @endif

        <div class="field-grid field-grid-2">
            <div>
                <label class="field-label">
                    Name
                </label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $group->name) }}"
                    maxlength="120"
                    required
                    class="field-input"
                >
            </div>

            <div>
                <label class="field-label">
                    Code
                </label>

                <input
                    type="text"
                    name="code"
                    value="{{ old('code', $group->code) }}"
                    maxlength="40"
                    class="field-input"
                >

                <p class="field-hint">
                    Optional short reference, such as DADT04.
                </p>
            </div>
        </div>

        @if($lockedOrganisationId !== null)
            <input
                type="hidden"
                name="organisation_id"
                value="{{ $lockedOrganisationId }}"
            >
        @elseif(! $isEdit && $primaryParentId === null)
            <div class="field-grid field-grid-2">
                <div>
                    <label class="field-label">
                        Organisation
                    </label>

                    <select
                        name="organisation_id"
                        id="organisationSelect"
                        required
                        class="field-input"
                    >
                        @foreach($organisations as $organisation)
                            <option
                                value="{{ $organisation->id }}"
                                @selected((int) old('organisation_id') === $organisation->id)
                            >{{ $organisation->name }}</option>
                        @endforeach
                    </select>

                    <p class="field-hint">
                        Which institution this top-level group
                        belongs to.
                    </p>
                </div>
            </div>
        @endif

        <div class="field-grid field-grid-2">
            <div>
                <label class="field-label">
                    Type
                </label>

                @php
                    $offeredTypes = $lockedOrganisationId === null
                        ? $types
                        : $types->where(
                            'organisation_id',
                            $lockedOrganisationId
                        );

                    $defaultTypeId = (int) old(
                        'group_type_id',
                        $group->group_type_id
                            ?? $offeredTypes->first()?->id
                    );
                @endphp

                <select
                    name="group_type_id"
                    id="groupTypeSelect"
                    required
                    class="field-input"
                >
                    @foreach($types as $type)
                        <option
                            value="{{ $type->id }}"
                            data-organisation="{{ $type->organisation_id }}"
                            @if($lockedOrganisationId !== null && $type->organisation_id !== $lockedOrganisationId)
                                hidden disabled
                            @endif
                            @selected($defaultTypeId === $type->id)
                        >{{ $type->name }}</option>
                    @endforeach
                </select>

                <p class="field-hint">
                    Only this organisation's own types are offered.
                </p>
            </div>

            <div>
                <label class="field-label">
                    Sits inside
                </label>

                <select
                    name="primary_parent_id"
                    class="field-input"
                >
                    <option value="">Top level</option>

                    @foreach($parents as $parent)
                        <option
                            value="{{ $parent->id }}"
                            @selected((int) $selectedPrimary === $parent->id)
                        >{{ $parent->path }}</option>
                    @endforeach
                </select>

                <p class="field-hint">
                    The main place this group lives. Used for
                    breadcrumbs and reports.
                </p>
            </div>
        </div>

        <div class="mb-6">
            <label class="field-label">
                Also sits inside
            </label>

            <p class="text-xs text-gray-500 mb-2">
                A class can belong to its programme and to its
                intake session at the same time. Tick any number,
                or none. Tick again to remove.
            </p>

            <input
                type="text"
                id="parentFilter"
                placeholder="Filter by name or path"
                class="field-input"
            >

            <div
                class="border border-gray-300 rounded-lg p-3"
                style="max-height:220px;overflow-y:auto"
            >
                @forelse($parents as $parent)
                    <label
                        class="parent-option flex items-center gap-2 py-1 cursor-pointer"
                        data-label="{{ strtolower($parent->path) }}"
                    >
                        <input
                            type="checkbox"
                            name="parent_ids[]"
                            value="{{ $parent->id }}"
                            @checked(in_array($parent->id, $selectedOthers, true))
                        >

                        <span>
                            {{ $parent->path }}

                            <span class="text-xs text-gray-400">
                                {{ $parent->type }}
                            </span>
                        </span>
                    </label>
                @empty
                    <p class="text-sm text-gray-500">
                        No other groups available.
                    </p>
                @endforelse
            </div>
        </div>

        @if($isEdit)
            <div class="mb-6">
                <label class="inline-flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        @checked(old('is_active', $group->is_active))
                    >

                    <span>Active</span>
                </label>
            </div>
        @endif

        <button
            type="submit"
            class="btn btn-primary"
        >
            {{ $isEdit ? 'Save changes' : 'Create group' }}
        </button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var filter = document.getElementById('parentFilter');

        if (!filter) {
            return;
        }

        filter.addEventListener('input', function () {
            var term = filter.value.trim().toLowerCase();

            document.querySelectorAll('.parent-option').forEach(function (option) {
                var match = term === ''
                    || (option.dataset.label || '').indexOf(term) !== -1;

                option.style.display = match ? '' : 'none';
            });
        });
    });
</script>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var organisation = document.getElementById('organisationSelect');
        var types = document.getElementById('groupTypeSelect');

        if (!organisation || !types) {
            return;
        }

        /*
         * Each organisation names its own levels, so offering
         * another institution's vocabulary would only invite
         * putting a group in the wrong place.
         */
        function narrow() {
            var chosen = organisation.value;
            var firstVisible = null;

            Array.prototype.forEach.call(types.options, function (option) {
                var belongs = option.dataset.organisation === chosen;

                option.hidden = !belongs;
                option.disabled = !belongs;

                if (belongs && firstVisible === null) {
                    firstVisible = option;
                }
            });

            if (types.selectedOptions.length === 0
                || types.selectedOptions[0].hidden) {
                types.value = firstVisible ? firstVisible.value : '';
            }
        }

        organisation.addEventListener('change', narrow);
        narrow();
    });
</script>
