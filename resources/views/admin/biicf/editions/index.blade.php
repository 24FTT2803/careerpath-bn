@extends('admin.layouts.admin')

@section('title', 'BIICF Editions')

@section('content')
<div class="biicf-page">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-book"></i> Reference Book Editions</h1>
            <p class="text-muted">
                The framework changes when AITI publishes a new edition. Upload one here,
                decide what it changes, then apply it.
            </p>
        </div>
        <a href="{{ route('admin.biicf.job-roles') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to BIICF
        </a>
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

    <div class="card">
        <div class="card-header">Upload an edition</div>
        <div class="card-body">
            <p class="hint">{{ $describes }}</p>


            <form method="POST" action="{{ route('admin.biicf.editions.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-grid">
                    <div class="field">
                        <label for="name">Edition name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               placeholder="Third Edition" required>
                    </div>

                    <div class="field">
                        <label for="edition_number">Edition number</label>
                        <input type="number" id="edition_number" name="edition_number"
                               value="{{ old('edition_number') }}" min="1" required>
                    </div>

                    <div class="field">
                        <label for="published_on">Published on</label>
                        <input type="date" id="published_on" name="published_on" value="{{ old('published_on') }}">
                    </div>

                    <div class="field">
                        <label for="source">The book</label>
                        <input type="file" id="source" name="source"
                               accept="{{ collect($accepts)->map(fn ($e) => '.'.$e)->implode(',') }}" required>
                    </div>
                </div>

                {{--
                    How much of the edition to propose. The
                    framework is what every recommendation rests
                    on, so this says how much an administrator is
                    asked to look at, never how much is applied
                    without them.
                --}}
                <fieldset class="strategies">
                    <legend>What should it propose?</legend>

                    <label class="strategy">
                        <input type="radio" name="strategy" value="changed" checked>
                        <span>
                            <strong>Only what differs</strong>
                            Compare against what the framework holds now and raise the
                            differences. The usual choice for a new edition.
                        </span>
                    </label>

                    <label class="strategy">
                        <input type="radio" name="strategy" value="gaps">
                        <span>
                            <strong>Only what is missing</strong>
                            Fill in what was never recorded and leave everything already
                            there alone, including anything corrected by hand.
                        </span>
                    </label>

                    <label class="strategy">
                        <input type="radio" name="strategy" value="everything">
                        <span>
                            <strong>Everything</strong>
                            Propose the book's value for every field, agreeing or not.
                            For loading a framework from scratch.
                        </span>
                    </label>

                    <label class="strategy">
                        <input type="radio" name="strategy" value="keep">
                        <span>
                            <strong>Just keep the book</strong>
                            Record it as the source and compare nothing. The framework is
                            left exactly as it is, and the book can be opened and cited
                            from here — for a machine whose data already came from it.
                        </span>
                    </label>
                </fieldset>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Upload and compare
                </button>

                {{--
                    What is happening, while it happens. Sending the
                    file and reading it are shown apart because they
                    fail for different reasons and take different
                    lengths of time: the first is quick and has a
                    end you can measure, the second is a slow minute
                    with nothing to count.
                --}}
                <div class="progress" hidden>
                    <div class="progress-head">
                        <span class="progress-stage">Sending the file…</span>
                        <span class="progress-count"></span>
                    </div>

                    <div class="progress-track">
                        <div class="progress-fill"></div>
                    </div>

                    <p class="progress-note"></p>
                </div>

                <div class="flash flash-error upload-failed" hidden></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Editions</div>
        <div class="card-body">
            @forelse($editions as $edition)
                <div class="edition-row">
                    <div>
                        <span class="edition-name">
                            {{ $edition->name }}
                            @if($edition->is_current)
                                <span class="badge badge-core">Current</span>
                            @endif
                        </span>
                        <span class="edition-meta">
                            Edition {{ $edition->edition_number }}
                            @if($edition->published_on)
                                · published {{ $edition->published_on->format('d M Y') }}
                            @endif
                            @if($edition->applied_at)
                                · applied {{ $edition->applied_at->format('d M Y') }}
                            @endif
                        </span>
                    </div>

                    <div class="edition-state">
                        {{--
                            Only what is still waiting. An edition with
                            nothing left to decide says nothing: the badge
                            that announced it took up the same room as the
                            ones that mean something, on every row, for
                            ever.
                        --}}
                        @if($edition->pending_changes_count > 0)
                            <span class="badge badge-level">
                                {{ $edition->pending_changes_count }} of {{ $edition->changes_count }} undecided
                            </span>
                        @elseif($edition->changes_count === 0)
                            <span class="badge">Nothing to review</span>
                        @endif

                        {{--
                            The book opens here, on this screen. It used to
                            go to the review and ask it to open the panel,
                            which is a detour through a screen about
                            something else — and it never arrived, because
                            the opener is a module and had not loaded when
                            the review asked for it.
                        --}}
                        @if($edition->source_file_path)
                            <button type="button" class="btn btn-outline btn-small" data-book-at
                                    data-url="{{ route('admin.biicf.editions.source', $edition) }}"
                                    data-title="{{ $edition->source_original_name ?? $edition->name }}"
                                    data-page="1" data-spans="[]"
                                    data-label="the book itself">
                                <i class="fas fa-book-open"></i> The book
                            </button>
                        @endif

                        <a href="{{ route('admin.biicf.editions.show', $edition) }}" class="btn btn-outline btn-small">
                            Review
                        </a>

                        @if($edition->applied_at === null)
                            {{--
                                Only before it is applied. Afterwards the
                                edition is the record of what it changed
                                and why the framework reads as it does.
                            --}}
                            <form method="POST" action="{{ route('admin.biicf.editions.destroy', $edition) }}"
                                  data-confirm="{{ $edition->name }} and the {{ $edition->changes_count }} difference(s) staged with it will be thrown away, along with the uploaded file. The framework itself is not touched."
                                  data-confirm-title="Discard this edition?"
                                  data-confirm-ok="Yes, discard it"
                                  data-confirm-type="danger">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-small btn-danger">Discard</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p>No editions uploaded yet. The framework currently in the tables was loaded
                   from the second edition before this screen existed.</p>
            @endforelse
        </div>
    </div>
</div>

<script>
/*
 * Watching an upload that takes a minute.
 *
 * Submitted normally, the page sits blank from the moment the
 * file leaves until the book has been read — long enough that
 * it looks stuck, and a reload halfway through throws away the
 * wait and leaves a half-made edition behind.
 *
 * So it is sent with XMLHttpRequest, which is the only thing
 * that reports how much of a file has gone. The two phases are
 * shown apart: sending can be counted and reading cannot, and
 * saying "40%" about a parse nobody can measure would be a
 * number made up to look reassuring.
 *
 * Without JavaScript the form still submits and still works.
 */
(function () {
    var form = document.querySelector('form[action="{{ route('admin.biicf.editions.store') }}"]');

    if (! form || ! window.XMLHttpRequest || ! window.FormData) {
        return;
    }

    var box = form.querySelector('.progress');
    var stage = form.querySelector('.progress-stage');
    var count = form.querySelector('.progress-count');
    var fill = form.querySelector('.progress-fill');
    var note = form.querySelector('.progress-note');
    var failed = form.querySelector('.upload-failed');
    var button = form.querySelector('button[type="submit"]');
    var running = false;

    function size(bytes) {
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    function clock(from) {
        var seconds = Math.round((Date.now() - from) / 1000);

        return seconds < 60
            ? seconds + 's'
            : Math.floor(seconds / 60) + 'm ' + (seconds % 60) + 's';
    }

    form.addEventListener('submit', function (event) {
        if (running || ! form.querySelector('input[name="source"]').files.length) {
            return;
        }

        event.preventDefault();
        running = true;

        var data = new FormData(form);
        data.append('ajax', '1');

        var request = new XMLHttpRequest();
        var started = Date.now();
        var ticking = null;

        failed.hidden = true;
        box.hidden = false;
        box.classList.remove('progress-reading');
        button.disabled = true;
        fill.style.width = '0%';
        note.textContent = '';

        request.upload.addEventListener('progress', function (e) {
            if (! e.lengthComputable) {
                return;
            }

            var done = Math.round((e.loaded / e.total) * 100);

            fill.style.width = done + '%';
            stage.textContent = 'Sending the file…';
            count.textContent = size(e.loaded) + ' of ' + size(e.total) + ' · ' + done + '%';
        });

        /*
         * Everything is sent; now the server is reading it, and
         * there is nothing left to count. The bar goes to a
         * moving stripe and the only honest number is how long
         * it has been going.
         */
        request.upload.addEventListener('load', function () {
            box.classList.add('progress-reading');
            fill.style.width = '100%';
            stage.textContent = 'Reading the book…';
            count.textContent = '';
            note.textContent = 'A few hundred pages takes about a minute. Leave this page open.';

            ticking = setInterval(function () {
                count.textContent = clock(started);
            }, 1000);
        });

        function stop() {
            running = false;
            button.disabled = false;

            if (ticking) {
                clearInterval(ticking);
            }
        }

        function fail(message) {
            stop();
            box.hidden = true;
            failed.hidden = false;
            failed.textContent = message;
        }

        request.addEventListener('load', function () {
            var body = {};

            try {
                body = JSON.parse(request.responseText);
            } catch (e) {
                fail(
                    'The server answered with something this page could not read. '
                    + 'It may still have worked — reload the list before trying again.'
                );

                return;
            }

            if (request.status >= 200 && request.status < 300 && body.redirect) {
                stage.textContent = 'Done.';
                window.location = body.redirect;

                return;
            }

            /* Laravel returns field errors under "errors". */
            if (body.errors) {
                var lines = [];

                Object.keys(body.errors).forEach(function (field) {
                    lines.push([].concat(body.errors[field]).join(' '));
                });

                fail(lines.join(' '));

                return;
            }

            fail(body.message || 'That upload did not work, and gave no reason.');
        });

        request.addEventListener('error', function () {
            fail('The connection dropped before the server answered. Nothing was saved.');
        });

        request.addEventListener('timeout', function () {
            fail('The server took too long to answer. Reading a very large book is '
                + 'better done with "php artisan biicf:read-book".');
        });

        request.addEventListener('abort', function () {
            fail('That upload was stopped.');
        });

        request.open('POST', form.action);
        request.setRequestHeader('Accept', 'application/json');
        request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        request.timeout = {{ \App\Http\Controllers\Admin\BiicfEditionController::READING_SECONDS * 1000 }};
        request.send(data);
    });

    /* A reload mid-read loses the work and leaves a part-made edition. */
    window.addEventListener('beforeunload', function (event) {
        if (running) {
            event.preventDefault();
            event.returnValue = '';
        }
    });
})();
</script>

<style>
    .biicf-page { padding: 0 4px; }
    .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
    .page-header h1 { font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 700; color: #1a3a5c; margin: 0; }
    .page-header h1 i { color: #c9a84c; }
    .text-muted { color: #6b7280; font-size: 14px; margin-top: 4px; max-width: 70ch; }
    .btn-primary { background: #c9a84c; color: #0d1f33; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; }
    .btn-primary:hover { background: #e8d4a0; }
    .btn-outline { background: transparent; color: #1a1a2e; border: 2px solid #e5e7eb; padding: 10px 20px; border-radius: 8px; font-weight: 500; text-decoration: none; display: inline-block; }
    .btn-outline:hover { border-color: #c9a84c; color: #c9a84c; }
    .btn-small { padding: 6px 14px; font-size: 13px; }
    .card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; margin-bottom: 20px; }
    .card-header { padding: 16px 20px; background: #faf8f2; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: 16px; color: #1a3a5c; }
    .card-body { padding: 20px; }
    .hint { color: #6b7280; font-size: 13px; margin: 0 0 16px; }
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px; }
    .field label { display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; margin-bottom: 6px; }
    .field input { width: 100%; padding: 10px 13px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; }
    .field input:focus { outline: none; border-color: #c9a84c; }
    .strategies { border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px; margin-bottom: 20px; }
    .strategies legend { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; padding: 0 6px; }
    .strategy { display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f3f4f6; cursor: pointer; }
    .strategy:last-child { border-bottom: none; }
    .strategy input { margin-top: 4px; }
    .strategy strong { display: block; font-size: 14px; color: #1a1a2e; }
    .strategy span { font-size: 13px; color: #6b7280; line-height: 1.5; }
    .edition-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 14px 0; border-bottom: 1px solid #f3f4f6; flex-wrap: wrap; }
    .edition-row:last-child { border-bottom: none; }
    .edition-name { display: block; font-weight: 600; font-size: 15px; }
    .edition-meta { display: block; font-size: 12px; color: #6b7280; margin-top: 2px; }
    .edition-state { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .edition-state form { display: inline; }
    .btn-danger { color: #c65b4e; }
    .btn-danger:hover { border-color: #c65b4e; color: #c65b4e; }
    .badge { display: inline-block; padding: 4px 12px; border-radius: 100px; font-size: 12px; font-weight: 500; background: #f3f4f6; color: #6b7280; }
    .badge-core { background: #e9f3ee; color: #2d8f5c; }
    .badge-level { background: #fbf1de; color: #8a6420; }
    .flash { padding: 12px 16px; border-radius: 8px; background: #e9f3ee; color: #2d8f5c; margin-bottom: 20px; font-size: 14px; }
    .flash-error { background: #fbeceb; color: #c65b4e; }

    .progress { margin-top: 18px; max-width: 560px; }
    .progress-head { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; margin-bottom: 8px; }
    .progress-stage { font-size: 14px; font-weight: 600; color: #1a3a5c; }
    .progress-count { font-size: 12px; color: #6b7280; font-variant-numeric: tabular-nums; }
    .progress-track { height: 8px; border-radius: 100px; background: #f3f4f6; overflow: hidden; }
    .progress-fill { height: 100%; width: 0; border-radius: 100px; background: #c9a84c; transition: width .2s ease; }
    .progress-note { margin: 8px 0 0; font-size: 12px; color: #6b7280; }

    /*
     * Reading cannot be measured, so the bar stops counting and
     * starts moving. A bar that kept creeping toward a hundred
     * would be inventing progress it has no way of knowing.
     */
    .progress-reading .progress-fill {
        background-image: linear-gradient(115deg, #c9a84c 25%, #e8d4a0 25%, #e8d4a0 50%, #c9a84c 50%, #c9a84c 75%, #e8d4a0 75%);
        background-size: 28px 28px;
        animation: progress-stripe 1s linear infinite;
    }

    @keyframes progress-stripe {
        from { background-position: 0 0; }
        to { background-position: 28px 0; }
    }

    @media (prefers-reduced-motion: reduce) {
        .progress-reading .progress-fill { animation: none; }
        .progress-fill { transition: none; }
    }

    .upload-failed { margin: 18px 0 0; max-width: 560px; }
</style>

{{--
    The book, opened on this screen. Shared with the review,
    which opens the same panel at the page a value was read
    from.
--}}
@include('admin.biicf.partials.book-source')
@endsection
