@extends('admin.layouts.admin')

@section('title', 'Check against ' . $edition->name)

@section('content')
{{--
    The framework against the book, side by side.

    Not against the extraction. An administrator asking whether a
    role's duties are right does not want to be told what a
    reader made of them — they want the page the book prints,
    next to the value the framework holds, and to decide for
    themselves.

    Every value the book states is here, agreed or not. The
    review screen only ever showed the ones that disagreed, which
    is the opposite of what checking means: once an edition is
    applied there are no disagreements left, and so there was
    nothing to check.
--}}
<div class="biicf-page">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-book-open-reader"></i> Check against the book</h1>
            <p class="text-muted">
                {{ $edition->name }}
                @if($edition->source_original_name)
                    · {{ $edition->source_original_name }}
                @endif
                @if($recorded > 0)
                    · {{ number_format($recorded) }} values located
                @endif
            </p>
        </div>

        <div class="btn-group">
            <a href="{{ route('admin.biicf.editions.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Editions
            </a>

            <a href="{{ route('admin.biicf.editions.show', $edition) }}" class="btn btn-outline">
                <i class="fas fa-scale-balanced"></i> Review differences
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="flash flash-error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if($recorded === 0)
        {{--
            An edition staged before the places were being kept.
            The reader knows them — it reads the book by position
            and nothing else — so reading the same file again is
            all this needs.
        --}}
        <div class="card">
            <div class="card-body">
                <h3>Nothing to check against yet</h3>

                <p>
                    This edition was read before the book's places were kept, so
                    there is nothing here to point at. Nothing needs uploading
                    again: read the same file once more and every value it states
                    is located.
                </p>

                <form method="POST" action="{{ route('admin.biicf.editions.reread', $edition) }}">
                    @csrf
                    <input type="hidden" name="strategy" value="changed">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-rotate"></i> Read the book again
                    </button>
                </form>
            </div>
        </div>
    @elseif($title === null)
        <div class="card">
            <div class="card-body">
                <p>This edition names no job roles, so there is nothing to check.</p>
            </div>
        </div>
    @else
        <div class="check-head">
            <form method="GET" action="{{ route('admin.biicf.editions.book', $edition) }}">
                <label for="role">Role</label>

                <select name="role" id="role" onchange="this.form.submit()">
                    @foreach($subjects as $subject)
                        <option value="{{ $subject }}" @selected($subject === $title)>{{ $subject }}</option>
                    @endforeach
                </select>

                <noscript><button type="submit" class="btn btn-outline btn-small">Show</button></noscript>
            </form>

            @if($role === null)
                <span class="check-gone">
                    <i class="fas fa-triangle-exclamation"></i>
                    The framework no longer holds a role by this name.
                </span>
            @else
                <span class="text-muted">{{ $role->subSector?->name }}</span>
            @endif
        </div>

        <div class="check-grid">
            <div class="check-values">
                @foreach($rows as $index => $row)
                    <div class="check-row @if($index === 0) is-open @endif" data-check-row>
                        <div class="check-row-head">
                            <span class="field">{{ $row['label'] }}</span>

                            <button type="button" class="book-at" data-book-at
                                    data-into="#bookPane"
                                    data-url="{{ route('admin.biicf.editions.source', $edition) }}"
                                    data-title="{{ $edition->source_original_name ?? $edition->name }}"
                                    data-page="{{ $row['page'] }}"
                                    data-spans="{{ json_encode($row['spans']) }}"
                                    data-label="{{ $row['label'] }}">
                                <i class="fas fa-book-open"></i> p.{{ $row['page'] }}
                            </button>
                        </div>

                        @if($row['missing'])
                            {{--
                                The book states it and the framework does
                                not. Not necessarily wrong — an edition's
                                differences may have been reviewed and the
                                framework's silence chosen — but it is the
                                first thing worth looking at.
                            --}}
                            <p class="check-missing">
                                <i class="fas fa-circle-minus"></i>
                                The framework holds nothing here.
                            </p>
                        @else
                            <ul class="check-value">
                                @foreach($row['value'] as $line)
                                    <li>{{ $line }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>

            {{--
                The book, in a column of its own rather than over the
                top of the values: reading one against the other is
                what the screen is for, and a panel covering them
                makes that two glances instead of one.
            --}}
            <div class="check-book">
                <div class="book-source book-inline" id="bookPane">
                    @include('admin.biicf.partials.book-pane', ['closable' => false])
                </div>
            </div>
        </div>
    @endif
</div>

{{-- The panel the review uses, for the page-turning wiring it carries. --}}
@include('admin.biicf.partials.book-source')

<script>
/*
 * Which value is being checked.
 *
 * The first one opens by itself, because a screen for reading
 * one thing against another should not open on an empty half.
 */
document.addEventListener('DOMContentLoaded', function () {
    var rows = Array.prototype.slice.call(document.querySelectorAll('[data-check-row]'));

    document.addEventListener('click', function (event) {
        var at = event.target.closest('[data-book-at]');

        if (!at) {
            return;
        }

        var row = at.closest('[data-check-row]');

        rows.forEach(function (one) {
            one.classList.toggle('is-open', one === row);
        });
    });

    var first = rows[0] && rows[0].querySelector('[data-book-at]');

    if (first) {
        first.click();
    }
});
</script>

<style>
    .biicf-page { padding: 0 4px; }
    .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 16px; }
    .page-header h1 { font-family: 'Playfair Display', serif; font-size: 26px; font-weight: 700; color: #1a3a5c; margin: 0; }
    .page-header h1 i { color: #c9a84c; }
    .text-muted { color: #6b7280; font-size: 13px; margin-top: 4px; }
    .btn-group { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .btn-primary { background: #c9a84c; color: #0d1f33; border: none; padding: 9px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; }
    .btn-primary:hover { background: #e8d4a0; }
    .btn-outline { background: transparent; color: #1a1a2e; border: 2px solid #e5e7eb; padding: 9px 18px; border-radius: 8px; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-block; }
    .btn-outline:hover { border-color: #c9a84c; color: #c9a84c; }
    .btn-small { padding: 6px 14px; font-size: 13px; }
    .card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 0; overflow: hidden; margin-bottom: 18px; }
    .card-body { padding: 18px 20px; }
    .card-body h3 { margin: 0 0 8px; font-size: 16px; color: #1a3a5c; }
    .card-body p { margin: 0 0 14px; font-size: 13px; color: #4b5563; line-height: 1.6; }
    .flash { padding: 12px 16px; border-radius: 10px; background: #e9f3ee; color: #1f6b47; margin-bottom: 16px; font-size: 13px; }
    .flash-error { background: #fdecea; color: #a4342a; }
    .flash p { margin: 0; }

    .check-head { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; padding: 12px 16px; margin-bottom: 18px; background: white; border: 1px solid #e5e7eb; border-radius: 10px; }
    .check-head form { display: flex; align-items: center; gap: 10px; margin: 0; }
    .check-head label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; }
    .check-head select { padding: 7px 10px; font: inherit; font-size: 13px; border: 2px solid #e5e7eb; border-radius: 8px; max-width: 320px; }
    .check-head select:focus { outline: none; border-color: #c9a84c; }
    .check-gone { font-size: 12px; color: #8a6420; }

    /*
     * Two columns, and the book stays put while the values
     * scroll. Checking is reading one against the other, so the
     * one being read against cannot wander off the screen.
     */
    .check-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr); gap: 18px; align-items: start; }
    .check-values { display: flex; flex-direction: column; gap: 10px; }
    .check-book { position: sticky; top: 16px; }

    .check-row { background: white; border: 1px solid #e5e7eb; border-left: 3px solid transparent; border-radius: 10px; padding: 12px 14px; }
    .check-row.is-open { border-left-color: #c9a84c; background: #fbfbfa; }
    .check-row-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 8px; }
    .check-row .field { font-size: 13px; font-weight: 600; color: #1a3a5c; }

    .check-value { margin: 0; padding-left: 18px; font-size: 13px; line-height: 1.6; color: #374151; }
    .check-value li { margin-bottom: 3px; overflow-wrap: anywhere; }
    .check-missing { margin: 0; font-size: 12px; color: #8a6420; }

    /*
     * The book in a column rather than over the screen: it sits
     * in the page, keeps its own height, and never takes the
     * scroll with it.
     */
    .book-inline { position: static; inset: auto; z-index: auto; display: block; padding: 0; background: transparent; }
    .book-inline .book-panel { max-width: none; max-height: calc(100vh - 48px); box-shadow: none; border: 1px solid #e5e7eb; }
    .book-inline .book-stage { min-height: 320px; }

    @media (max-width: 1100px) {
        .check-grid { grid-template-columns: minmax(0, 1fr); }
        .check-book { position: static; }
    }
</style>
@endsection
