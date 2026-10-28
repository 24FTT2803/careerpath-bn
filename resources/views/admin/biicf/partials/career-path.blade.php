{{--
    The role's own place in a career path.

    biicf_career_path_edges was written only by the seeders, so a
    role added or corrected by hand had no progression at all and
    no screen could give it one: the Explorer's Career Paths panel
    read "No career path data" for it permanently. Both directions
    are edited here because both are edges touching this role, and
    listing only one would mean a role could be reached from
    somewhere it does not lead back to without anyone being able
    to see it from this form.

    Expects:
      $candidateRoles — every other role, with its sub-sector
      $selectedFrom   — ids of roles that lead into this one
      $selectedTo     — ids of roles this one leads to
--}}

<div class="form-divider">Career Path</div>

<div class="form-group">
    <label>Progression</label>

    <small class="form-text text-muted cp-intro">
        What a student sees under Career Paths in the Explorer. A
        role with neither is shown as a standalone entry point,
        which is correct for some roles and an omission for most.
    </small>

    @if($candidateRoles->isEmpty())
        <p class="cp-empty">
            There is no other job role to connect this one to yet.
            Add a second role and the progression can be set from
            either of them.
        </p>
    @else
        {{--
            Tells the controller this form carried the progression
            lists at all. Unticking every box submits neither
            field, which is indistinguishable from a page that
            never showed them — and redisplaying a failed save
            would then restore the edges the admin had just
            cleared.
        --}}
        <input type="hidden" name="career_path_submitted" value="1">

        <input
            type="search"
            class="form-control cp-search"
            placeholder="Filter roles by title or sub-sector"
            data-cp-search
            autocomplete="off"
        >

        <div class="cp-columns">
            @foreach([
                ['key' => 'progresses_from', 'label' => 'Comes from', 'selected' => $selectedFrom, 'hint' => 'Roles a student typically holds before this one.'],
                ['key' => 'progresses_to', 'label' => 'Leads to', 'selected' => $selectedTo, 'hint' => 'Roles this one typically progresses into.'],
            ] as $column)
                <div class="cp-column">
                    <div class="cp-column-head">
                        {{ $column['label'] }}
                        <span class="cp-count" data-cp-count="{{ $column['key'] }}">
                            {{ count($column['selected']) }}
                        </span>
                    </div>

                    <small class="cp-hint">{{ $column['hint'] }}</small>

                    <div class="cp-list" data-cp-list>
                        @foreach($candidateRoles as $candidate)
                            <label
                                class="checkbox-label cp-option"
                                data-cp-option="{{ Str::lower($candidate->title.' '.($candidate->subSector->name ?? '')) }}"
                            >
                                <input type="checkbox" name="{{ $column['key'] }}[]" value="{{ $candidate->id }}" data-cp-box="{{ $column['key'] }}" {{ in_array($candidate->id, $column['selected']) ? 'checked' : '' }}>

                                <span class="cp-option-text">
                                    <span class="cp-option-title">{{ $candidate->title }}</span>
                                    <span class="cp-option-meta">
                                        {{ $candidate->subSector->name ?? 'No sub-sector' }}
                                        &middot; level {{ $candidate->career_path_level }}
                                    </span>
                                </span>
                            </label>
                        @endforeach

                        <p class="cp-no-match" data-cp-no-match hidden>
                            No role matches that.
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        @error('progresses_from')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror

        @error('progresses_to')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    @endif
</div>

<style>
    .cp-intro { display: block; margin-bottom: 12px; }
    .cp-empty { color: #6b7280; font-size: 14px; margin: 0; }
    .cp-search { margin-bottom: 12px; }
    .cp-columns { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .cp-column { border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px; background: #fcfcfa; }
    .cp-column-head {
        display: flex; align-items: center; justify-content: space-between;
        font-weight: 600; color: #1a3a5c; font-size: 14px;
    }
    .cp-count {
        background: #1a3a5c; color: #fff; border-radius: 999px;
        font-size: 12px; font-weight: 600; min-width: 22px;
        padding: 1px 7px; text-align: center;
    }
    .cp-count[data-empty="true"] { background: #e5e7eb; color: #6b7280; }
    .cp-hint { display: block; color: #6b7280; font-size: 12px; margin: 4px 0 10px; }
    .cp-list { max-height: 260px; overflow-y: auto; display: grid; gap: 8px; }
    .cp-option { align-items: flex-start; gap: 8px; }
    .cp-option-text { display: flex; flex-direction: column; line-height: 1.3; }
    .cp-option-title { font-weight: 500; }
    .cp-option-meta { color: #6b7280; font-size: 12px; }
    .cp-no-match { color: #6b7280; font-size: 13px; margin: 4px 0 0; }

    @media (max-width: 720px) {
        .cp-columns { grid-template-columns: 1fr; }
    }
</style>

<script>
    /*
     * Inside the section on purpose. Blade discards whatever
     * follows the section's closing directive, and a filter that
     * never runs leaves an admin scrolling a list of every role
     * in the framework.
     *
     * That directive is deliberately not named here. Blade reads
     * its own directives out of Blade files whatever they sit
     * inside, a JavaScript comment included, so writing it in
     * this sentence closed the section early and took every page
     * that includes this partial down with it.
     */
    document.addEventListener('DOMContentLoaded', function () {
        var search = document.querySelector('[data-cp-search]');
        var lists = document.querySelectorAll('[data-cp-list]');

        if (!lists.length) {
            return;
        }

        var retally = function (key) {
            var badge = document.querySelector('[data-cp-count="' + key + '"]');

            if (!badge) {
                return;
            }

            var checked = document.querySelectorAll(
                '[data-cp-box="' + key + '"]:checked'
            ).length;

            badge.textContent = checked;
            badge.dataset.empty = checked === 0 ? 'true' : 'false';
        };

        ['progresses_from', 'progresses_to'].forEach(function (key) {
            retally(key);

            document.querySelectorAll('[data-cp-box="' + key + '"]').forEach(
                function (box) {
                    box.addEventListener('change', function () {
                        retally(key);
                    });
                }
            );
        });

        if (!search) {
            return;
        }

        search.addEventListener('input', function () {
            var needle = search.value.trim().toLowerCase();

            lists.forEach(function (list) {
                var shown = 0;

                list.querySelectorAll('[data-cp-option]').forEach(function (option) {
                    /*
                     * A ticked role stays visible whatever the
                     * filter says, so a search cannot hide a
                     * choice that is about to be submitted.
                     */
                    var keep = needle === ''
                        || option.dataset.cpOption.indexOf(needle) !== -1
                        || option.querySelector('input').checked;

                    option.hidden = !keep;

                    if (keep) {
                        shown++;
                    }
                });

                var none = list.querySelector('[data-cp-no-match]');

                if (none) {
                    none.hidden = shown > 0;
                }
            });
        });
    });
</script>
