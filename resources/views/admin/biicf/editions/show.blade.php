@extends('admin.layouts.admin')

@section('title', 'Review ' . $edition->name)

@php
    /**
     * A value as it should read on the page.
     *
     * Lists are the common case — a description, a set of
     * courses — and reading them as JSON would make an
     * administrator parse brackets to see what changed.
     */
    $readable = function ($value) {
        if ($value === null) {
            return '—';
        }

        if (is_array($value)) {
            return collect($value)
                ->map(fn ($line) => is_scalar($line) ? (string) $line : json_encode($line))
                ->implode("\n");
        }

        return (string) $value;
    };
@endphp

@section('content')
<div class="biicf-page">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-book-open"></i> {{ $edition->name }}</h1>
            <p class="text-muted">
                <span data-pending>{{ $pending }}</span> undecided of {{ $pending + $decided }}
                @if($edition->source_original_name)
                    · read from
                    <a href="{{ route('admin.biicf.editions.source', $edition) }}" target="_blank" rel="noopener">
                        {{ $edition->source_original_name }}
                    </a>
                @endif
            </p>
        </div>

        <div class="btn-group">
            <a href="{{ route('admin.biicf.editions.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Editions
            </a>

            @if($edition->applied_at === null)
                <form method="POST" action="{{ route('admin.biicf.editions.apply', $edition) }}"
                      data-confirm="Every decision recorded here will be written to the live framework, and students will see the result. The edition currently in use is kept as a record."
                      data-confirm-title="Apply {{ $edition->name }}?"
                      data-confirm-ok="Yes, apply it"
                      data-confirm-type="warning">
                    @csrf
                    <button type="submit" class="btn btn-primary" data-apply @disabled($pending > 0)>
                        <i class="fas fa-check-double"></i> Apply this edition
                    </button>
                </form>
            @else
                <span class="badge badge-core">Applied {{ $edition->applied_at->format('d M Y') }}</span>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="flash flash-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if(session('problems'))
        {{--
            Fields the reader could not make sense of. They raise
            no difference, so the framework keeps what it has —
            but that is a decision someone should make knowingly
            rather than by not being told.
        --}}
        <div class="flash flash-warn">
            <strong>{{ count(session('problems')) }} thing(s) in that file could not be read, and were left out:</strong>
            @foreach(session('problems') as $problem)
                <div>{{ $problem }}</div>
            @endforeach
        </div>
    @endif

    @if($pending > 0 && $edition->applied_at === null)
        {{--
            Every difference is decided before any of it is
            applied, so this is here for the long tail an
            administrator is happy to take wholesale after
            reading the handful they are not.
        --}}
        <div class="bulk">
            <span><span data-pending>{{ $pending }}</span> still undecided.</span>

            <form method="POST" action="{{ route('admin.biicf.editions.decide-all', $edition) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="decision" value="accept">
                <button type="submit" class="btn btn-outline btn-small">Take the book's value for all</button>
            </form>

            <form method="POST" action="{{ route('admin.biicf.editions.decide-all', $edition) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="decision" value="keep">
                <button type="submit" class="btn btn-outline btn-small">Keep what we have for all</button>
            </form>

            <a href="{{ route('admin.biicf.editions.show', ['edition' => $edition, 'show' => 'pending']) }}"
               class="btn btn-outline btn-small">Show only undecided</a>
        </div>
    @endif

    @forelse($groups as $label => $changes)
        <div class="card">
            <div class="card-header">
                {{ $label }}
                <span class="subject-type">{{ str_replace('_', ' ', $changes->first()->subject_type) }}</span>
            </div>

            <div class="card-body">
                @foreach($changes as $change)
                    <div class="change change-{{ $change->kind }} @if($change->isDecided()) is-decided @endif"
                         data-change
                         data-decision="{{ $change->isDecided() ? $change->decision : '' }}">

                        {{--
                            A decided difference folds down to one line.
                            There are well over a hundred of them, and
                            the ones still wanting an answer are what
                            should fill the screen; the rest are a record,
                            and a record can be a line until it is asked
                            for.
                        --}}
                        <button type="button" class="summary" @unless($change->isDecided()) hidden @endunless>
                            <span class="field">{{ $change->field === '*' ? 'the whole record' : $change->field }}</span>
                            <span class="verdict"></span>
                            <i class="fas fa-chevron-down summary-arrow"></i>
                        </button>

                        <div class="change-body">
                        <div class="change-head">
                            <span class="field">{{ $change->field === '*' ? 'the whole record' : $change->field }}</span>
                            <span class="kind kind-{{ $change->kind }}">{{ $change->kind }}</span>

                            @if($change->applied_at)
                                <span class="badge badge-core">applied</span>
                            @endif
                        </div>

                        @php($panes = \App\Services\Biicf\ValueDiff::panes($change->current_value, $change->proposed_value))

                        <div class="values">
                            <div class="value">
                                <h5>Now</h5>
                                {{-- Marked by ValueDiff, which escapes everything it is given. --}}
                                <pre>{!! $panes['current'] !!}</pre>
                            </div>

                            <div class="value">
                                <h5>{{ $edition->name }} says</h5>
                                <pre>{!! $panes['proposed'] !!}</pre>
                            </div>
                        </div>

                        <div class="value edited" @if($change->decision !== 'edit' || $change->edited_value === null) hidden @endif>
                            <h5>You wrote</h5>
                            <pre>{{ $readable($change->edited_value) }}</pre>
                        </div>

                        @if($change->applied_at === null)
                            {{--
                                What was decided, said in words rather than
                                left to be inferred from which button looks
                                pressed. It is the first thing on the row
                                once there is an answer, and it can be
                                changed.
                            --}}
                            <div class="settled" @unless($change->isDecided()) hidden @endunless>
                                <span class="settled-said"></span>
                                <button type="button" class="btn btn-outline btn-small change-again">Change</button>
                            </div>

                            <form method="POST"
                                  action="{{ route('admin.biicf.editions.decide', ['edition' => $edition, 'change' => $change]) }}"
                                  class="decide" @if($change->isDecided()) hidden @endif>
                                @csrf
                                @method('PATCH')

                                <div class="decide-actions">
                                    <button type="submit" name="decision" value="accept" class="btn btn-primary btn-small">
                                        Take the book's
                                    </button>
                                    <button type="submit" name="decision" value="keep" class="btn btn-outline btn-small">
                                        Keep ours
                                    </button>
                                    <button type="button" class="btn btn-outline btn-small toggle-edit">
                                        Write my own
                                    </button>
                                </div>

                                {{--
                                    Open only while being written in, and
                                    saying plainly that nothing is recorded
                                    until it is saved — the three buttons
                                    above decide on the spot, and this one
                                    does not, which is not obvious from
                                    looking at them.
                                --}}
                                <div class="edit-box" hidden>
                                    <textarea name="edited_value" rows="4"
                                              placeholder="One line per item.">{{ $readable($change->edited_value ?? $change->proposed_value) }}</textarea>

                                    <div class="edit-actions">
                                        <button type="submit" name="decision" value="edit" class="btn btn-primary btn-small">
                                            Save mine
                                        </button>
                                        <button type="button" class="btn btn-outline btn-small cancel-edit">Cancel</button>
                                        <span class="edit-note">Not recorded until you save.</span>
                                    </div>
                                </div>
                            </form>
                        @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body">
                <p>Nothing to review. This edition says nothing the framework does not already say.</p>
            </div>
        </div>
    @endforelse
</div>

<script>
/*
 * Deciding a difference without losing your place.
 *
 * There are a hundred and twenty of these on a new edition, and
 * every decision used to reload the page and drop you back at
 * the top. So each one is sent on its own and answered in
 * place, and the row says in words what was decided rather than
 * leaving it to be inferred from which button looks pressed.
 *
 * Without JavaScript every button still submits its form and
 * the page still works, which is why they are submit buttons
 * carrying their own value rather than anything cleverer.
 */
(function () {
    var said = {
        accept: "Taking the book's value.",
        keep: 'Keeping what we have.',
        edit: 'Using your own wording.',
    };

    function readable(value) {
        if (value === null || value === undefined) {
            return '—';
        }

        return Array.isArray(value) ? value.join('\n') : String(value);
    }

    function settle(row, decision, edited) {
        var settled = row.querySelector('.settled');
        var form = row.querySelector('.decide');
        var wrote = row.querySelector('.value.edited');

        row.dataset.decision = decision;
        row.classList.add('is-decided');

        settled.querySelector('.settled-said').textContent = said[decision] || decision;
        settled.hidden = false;
        form.hidden = true;

        if (decision === 'edit' && edited !== null && edited !== undefined) {
            wrote.querySelector('pre').textContent = readable(edited);
            wrote.hidden = false;
        } else {
            wrote.hidden = true;
        }

        label(row);
        fold(row, true);
    }

    /* The one line a folded difference shows. */
    function label(row) {
        var verdict = row.querySelector('.verdict');

        if (verdict) {
            verdict.textContent = said[row.dataset.decision] || row.dataset.decision;
        }
    }

    function fold(row, shut) {
        var summary = row.querySelector('.summary');

        row.classList.toggle('is-folded', shut);

        if (summary) {
            summary.hidden = ! row.dataset.decision;
        }
    }

    /* The count at the top, and whether the edition can be applied yet. */
    function retally(pending) {
        var counter = document.querySelector('[data-pending]');

        if (counter) {
            counter.textContent = pending;
        }

        document.querySelectorAll('[data-apply]').forEach(function (button) {
            button.disabled = pending > 0;
        });

        var bulk = document.querySelector('.bulk');

        if (bulk && pending === 0) {
            bulk.hidden = true;
        }
    }

    document.addEventListener('click', function (event) {
        var open = event.target.closest('.toggle-edit');

        if (open) {
            var box = open.closest('.decide').querySelector('.edit-box');
            box.hidden = false;
            open.closest('.decide-actions').hidden = true;
            box.querySelector('textarea').focus();

            return;
        }

        var cancel = event.target.closest('.cancel-edit');

        if (cancel) {
            var form = cancel.closest('.decide');
            form.querySelector('.edit-box').hidden = true;
            form.querySelector('.decide-actions').hidden = false;

            return;
        }

        var summary = event.target.closest('.summary');

        if (summary) {
            var shown = summary.closest('[data-change]');
            fold(shown, ! shown.classList.contains('is-folded'));

            return;
        }

        var again = event.target.closest('.change-again');

        if (again) {
            var row = again.closest('[data-change]');
            row.querySelector('.settled').hidden = true;
            var decide = row.querySelector('.decide');
            decide.hidden = false;
            decide.querySelector('.decide-actions').hidden = false;
            decide.querySelector('.edit-box').hidden = true;
        }
    });

    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (! form.matches('.decide')) {
            return;
        }

        event.preventDefault();

        var pressed = event.submitter;
        var decision = pressed ? pressed.value : 'accept';
        var row = form.closest('[data-change]');

        var body = new FormData(form);
        body.set('decision', decision);
        body.append('ajax', '1');

        form.querySelectorAll('button').forEach(function (b) { b.disabled = true; });

        fetch(form.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: body,
        }).then(function (response) {
            return response.json().then(function (data) {
                return { ok: response.ok, data: data };
            });
        }).then(function (result) {
            form.querySelectorAll('button').forEach(function (b) { b.disabled = false; });

            if (! result.ok) {
                var why = result.data.message
                    || (result.data.errors && Object.values(result.data.errors)[0])
                    || 'That decision was not recorded.';

                window.alert(why);

                return;
            }

            settle(row, result.data.decision, result.data.edited);
            retally(result.data.pending);
        }).catch(function () {
            form.querySelectorAll('button').forEach(function (b) { b.disabled = false; });
            window.alert('That decision could not be sent. Check your connection and try again.');
        });
    });

    /* Fold the rows already decided when the page was built. */
    document.querySelectorAll('[data-change]').forEach(function (row) {
        if (row.dataset.decision) {
            row.querySelector('.settled-said').textContent =
                said[row.dataset.decision] || row.dataset.decision;
            label(row);
            fold(row, true);
        }
    });
})();
</script>

<style>
    .biicf-page { padding: 0 4px; }
    .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 16px; }
    .page-header h1 { font-family: 'Playfair Display', serif; font-size: 26px; font-weight: 700; color: #1a3a5c; margin: 0; }
    .page-header h1 i { color: #c9a84c; }
    .text-muted { color: #6b7280; font-size: 13px; margin-top: 4px; }
    .btn-group { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .btn-primary { background: #c9a84c; color: #0d1f33; border: none; padding: 9px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; }
    .btn-primary:hover:not(:disabled) { background: #e8d4a0; }
    .btn-primary:disabled { opacity: .5; cursor: not-allowed; }
    .btn-outline { background: transparent; color: #1a1a2e; border: 2px solid #e5e7eb; padding: 9px 18px; border-radius: 8px; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-block; }
    .btn-outline:hover { border-color: #c9a84c; color: #c9a84c; }
    .btn-small { padding: 6px 14px; font-size: 13px; }
    .bulk { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 12px 16px; margin-bottom: 20px; border: 1px dashed #e5e7eb; border-radius: 10px; font-size: 13px; color: #6b7280; }
    .bulk form { display: inline; }
    .card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; margin-bottom: 18px; }
    .card-header { padding: 14px 20px; background: #faf8f2; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: 15px; color: #1a3a5c; display: flex; justify-content: space-between; align-items: center; gap: 10px; }
    .subject-type { font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; }
    .card-body { padding: 8px 20px 16px; }
    .change { padding: 16px 0; border-bottom: 1px solid #f3f4f6; }
    .change:last-child { border-bottom: none; }
    .change-head { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 10px; }
    .field { font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 12px; color: #1a3a5c; background: #f3f4f6; padding: 2px 8px; border-radius: 4px; }
    .kind { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
    .kind-added { color: #2d8f5c; }
    .kind-changed { color: #8a6420; }
    .kind-removed { color: #c65b4e; }
    .values { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .value h5 { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; margin: 0 0 6px; }
    .value pre { margin: 0; padding: 10px 12px; background: #fafafa; border: 1px solid #f3f4f6; border-radius: 6px; font-family: inherit; font-size: 13px; line-height: 1.5; white-space: pre-wrap; word-break: break-word; max-height: 240px; overflow: auto; }
    .value.edited { margin-top: 12px; }
    .value.edited pre { background: #fffbeb; border-color: #f0e3c0; }
    .decide { margin-top: 12px; }

    /*
     * A decided difference folds to one line.
     *
     * Dimming it was not enough: a hundred and twenty faded
     * rows still fill the page, and what should fill it is the
     * ones still wanting an answer. Folded, a decision is a
     * line saying which it was, and opens again when asked.
     *
     * No tick anywhere. A tick means approved, and half of
     * these decisions are a refusal of what the book says.
     */
    .change.is-folded > .change-body { display: none; }
    .change.is-folded { padding: 0; border-bottom: 1px solid #f3f4f6; }

    .summary { display: flex; align-items: center; gap: 12px; width: 100%; padding: 10px 12px; margin: 4px 0; background: #fbfbfa; border: 1px solid #eceae4; border-left: 3px solid #c9a84c; border-radius: 8px; cursor: pointer; text-align: left; font: inherit; color: inherit; }
    .summary:hover { background: #f6f5f1; }
    .summary .field { flex: none; }
    .verdict { font-size: 13px; color: #4b5563; }
    .summary-arrow { margin-left: auto; font-size: 11px; color: #9ca3af; transition: transform .15s ease; }
    .change:not(.is-folded) .summary-arrow { transform: rotate(180deg); }

    .change[data-decision="keep"] .summary { border-left-color: #9ca3af; }
    .change[data-decision="edit"] .summary { border-left-color: #8a6420; }

    .settled { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 12px; padding: 8px 12px; border-radius: 8px; background: #faf8f2; border: 1px solid #f0e3c0; font-size: 13px; }
    .settled-said { font-weight: 600; color: #1a3a5c; }
    .settled .btn { margin-left: auto; }
    .change[data-decision="keep"] .settled { background: #fafafa; border-color: #ececec; }
    .change[data-decision="edit"] .settled { background: #fffbeb; border-color: #f0e3c0; }

    /*
     * What differs, marked. Gone from the left, arrived on the
     * right — the two are never the same colour, because a
     * reader scanning a hundred of these should be able to tell
     * at a glance which side they are looking at.
     */
    mark.diff-gone { background: #fbe4e2; color: #8c3b31; border-radius: 3px; padding: 0 2px; }
    mark.diff-came { background: #dff0e5; color: #1f6b43; border-radius: 3px; padding: 0 2px; }
    mark.diff-space { border-radius: 2px; }

    @media (prefers-contrast: more) {
        mark.diff-gone { outline: 1px solid #8c3b31; }
        mark.diff-came { outline: 1px solid #1f6b43; }
    }

    .edit-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .edit-note { font-size: 12px; color: #8a6420; }
    .decide-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .edit-box { margin-top: 10px; display: flex; flex-direction: column; gap: 8px; align-items: flex-start; }
    .edit-box textarea { width: 100%; padding: 10px 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-family: inherit; font-size: 13px; line-height: 1.5; }
    .edit-box textarea:focus { outline: none; border-color: #c9a84c; }
    .badge { display: inline-block; padding: 3px 10px; border-radius: 100px; font-size: 11px; font-weight: 500; background: #f3f4f6; color: #6b7280; }
    .badge-core { background: #e9f3ee; color: #2d8f5c; }
    .badge-level { background: #fbf1de; color: #8a6420; }
    .flash { padding: 12px 16px; border-radius: 8px; background: #e9f3ee; color: #2d8f5c; margin-bottom: 18px; font-size: 14px; }
    .flash-error { background: #fbeceb; color: #c65b4e; }
    .flash-warn { background: #fbf1de; color: #8a6420; }
    .flash-warn strong { display: block; margin-bottom: 6px; }
    .flash-warn div { font-size: 13px; line-height: 1.6; }
    @media (max-width: 768px) {
        .values { grid-template-columns: 1fr; }
    }
</style>
@endsection
