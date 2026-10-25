@extends('layouts.app')

@section('title', 'Innovation Lab')

@section('content')

{{--
    The lab as a feed.

    It read as a changelog: every post led with its own title and
    a bare date, so a page of them was a list of release notes
    nobody is in. These posts are written by people to the
    students using what they built, and the one change that says
    so is leading with who wrote it and how long ago.

    The card's border, corners, shadow and hover belong to
    partials.student-polish, which loads after this and keeps
    every student card the same. Only what is inside a post is
    decided here.
--}}
<style>
    .lab-page {
        max-width: 760px;
        margin: 0 auto;
        padding: 24px 0 48px;
    }

    .lab-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 24px;
    }

    .lab-heading {
        font-size: 22px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .lab-heading i {
        color: #c9a84c;
    }

    .lab-sub {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 0;
    }

    /*
     * No padding on the card itself: each band sets its own, so
     * a picture can run to the card's edges the way it does in
     * any feed. The shared partial rounds the corners, and
     * clipping here is what makes the picture follow them.
     */
    .lab-note {
        background: #fff;
        padding: 0;
        overflow: hidden;
        margin-bottom: 18px;
    }

    /* ---------- Who posted it, and when ---------- */

    .lab-byline {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 16px 22px 0;
    }

    .lab-avatar {
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        overflow: hidden;
        display: grid;
        place-items: center;
        background: #1a3a5c;
        color: #e8d4a0;
        font-size: 14px;
        font-weight: 600;
        letter-spacing: .02em;
    }

    .lab-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .lab-who {
        min-width: 0;
        flex: 1;
    }

    .lab-author {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #1a3a5c;
        overflow-wrap: anywhere;
    }

    .lab-when {
        font-size: 12.5px;
        color: #6b7280;
    }

    /*
     * One gold dot for a post the student has not seen. It used
     * to be a pill beside the title and a gold ring round the
     * whole card, which on a page where most posts are unread
     * outlined most of the page.
     */
    .lab-fresh {
        flex: 0 0 auto;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #c9a84c;
    }

    /* ---------- The post ---------- */

    .lab-title {
        font-family: 'Playfair Display', serif;
        font-size: 20px;
        font-weight: 700;
        line-height: 1.3;
        color: #1a3a5c;
        margin: 12px 0 0;
        padding: 0 22px;
        overflow-wrap: anywhere;
    }

    .lab-body {
        padding: 8px 22px 0;
        font-size: 14.5px;
        line-height: 1.7;
        color: #1a1a2e;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .lab-body a {
        color: #2a5a8c;
        text-decoration: underline;
    }

    .lab-body a:hover {
        color: #a88830;
    }

    /*
     * Folded only once JavaScript has measured it, so a post is
     * never half-readable if the script does not run.
     */
    .lab-body.is-folded {
        display: -webkit-box;
        -webkit-line-clamp: 6;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .lab-more {
        margin: 6px 0 0;
        padding: 6px 22px 0;
        font: inherit;
        font-size: 13px;
        font-weight: 600;
        color: #1a3a5c;
        background: none;
        border: 0;
        cursor: pointer;
    }

    .lab-more:hover {
        color: #a88830;
    }

    /* Edge to edge, and the last thing in the card. */
    .lab-media {
        display: block;
        width: 100%;
        margin-top: 12px;
        background: #eef0f4;
    }

    .lab-media img {
        display: block;
        width: 100%;
        height: auto;
        max-height: 620px;
        object-fit: cover;
    }

    /* The card's own bottom edge, since the bands set their own. */
    .lab-foot {
        height: 18px;
    }

    /* ---------- What a student can say back ---------- */

    .lab-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        padding: 14px 22px 0;
    }

    .lab-reactions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .lab-react-form,
    .lab-interest-form,
    .lab-palette form {
        display: inline;
        margin: 0;
    }

    .lab-react {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        font: inherit;
        font-size: 13px;
        background: #f4f6f9;
        border: 1px solid #e5e7eb;
        border-radius: 999px;
        cursor: pointer;
        transition: border-color .15s ease, background .15s ease;
    }

    .lab-react:hover {
        border-color: #c9a84c;
    }

    /* The ones this student put there. */
    .lab-react.is-mine {
        background: rgba(201, 168, 76, .14);
        border-color: #c9a84c;
    }

    .lab-react-emoji {
        font-size: 14px;
        line-height: 1;
    }

    .lab-react-count {
        font-size: 12px;
        font-weight: 600;
        color: #1a3a5c;
        font-variant-numeric: tabular-nums;
    }

    /*
     * The palette behind one key, so a post nobody has reacted
     * to yet shows one thing to press rather than six.
     */
    .lab-pick {
        position: relative;
    }

    .lab-pick > summary {
        width: 30px;
        height: 28px;
        display: grid;
        place-items: center;
        font-size: 13px;
        color: #6b7280;
        background: #f4f6f9;
        border: 1px solid #e5e7eb;
        border-radius: 999px;
        cursor: pointer;
        list-style: none;
    }

    .lab-pick > summary::-webkit-details-marker {
        display: none;
    }

    .lab-pick > summary:hover {
        border-color: #c9a84c;
        color: #1a3a5c;
    }

    /*
     * A panel rather than a strip, because the palette is six
     * rows now. Grouped, so it reads as rows with a name each
     * instead of thirty-six characters in a line.
     */
    .lab-palette {
        position: absolute;
        bottom: calc(100% + 8px);
        left: 0;
        z-index: 5;
        width: 268px;
        max-height: 264px;
        overflow-y: auto;
        padding: 10px 12px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 16px 40px -20px rgba(13, 31, 51, .55);
    }

    .lab-palette-group + .lab-palette-group {
        margin-top: 8px;
    }

    .lab-palette-name {
        display: block;
        margin-bottom: 2px;
        font-size: 11px;
        font-weight: 600;
        color: #6b7280;
    }

    .lab-palette-keys {
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
    }

    .lab-palette-key {
        width: 32px;
        height: 32px;
        font-size: 16px;
        line-height: 1;
        background: none;
        border: 0;
        border-radius: 50%;
        cursor: pointer;
        transition: background .15s ease, transform .15s ease;
    }

    .lab-palette-key:hover {
        background: #f4f6f9;
        transform: scale(1.15);
    }

    .lab-palette-key.is-mine {
        background: rgba(201, 168, 76, .18);
    }

    /* ---------- Putting your hand up ---------- */

    .lab-interest {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-left: auto;
        padding: 7px 14px;
        font: inherit;
        font-size: 13px;
        font-weight: 600;
        color: #1a3a5c;
        background: #fff;
        border: 1.5px solid #e5e7eb;
        border-radius: 999px;
        cursor: pointer;
        transition: border-color .15s ease, background .15s ease, color .15s ease;
    }

    .lab-interest:hover {
        border-color: #c9a84c;
    }

    .lab-interest.is-in {
        color: #0d1f33;
        background: #c9a84c;
        border-color: #c9a84c;
    }

    .lab-interest i {
        font-size: 12px;
    }

    .lab-interest-count {
        padding-left: 7px;
        margin-left: 2px;
        border-left: 1px solid rgba(26, 58, 92, .2);
        font-variant-numeric: tabular-nums;
    }

    .lab-said {
        flex: 1 1 100%;
        font-size: 12px;
        color: #c65b4e;
    }

    @media (max-width: 600px) {
        .lab-actions {
            padding-left: 16px;
            padding-right: 16px;
        }

        .lab-interest {
            margin-left: 0;
        }
    }

    .lab-empty {
        padding: 56px 24px;
        text-align: center;
        font-size: 14px;
        color: #6b7280;
        background: #fff;
        border-radius: 16px;
        border: 1px dashed #e5e7eb;
    }

    @media (max-width: 600px) {
        .lab-byline,
        .lab-title,
        .lab-body,
        .lab-more {
            padding-left: 16px;
            padding-right: 16px;
        }

        .lab-title {
            font-size: 18px;
        }
    }
</style>

@php
    /*
     * Who is reading, and which emoji they have already put on
     * each post — gathered once rather than asked per post.
     */
    $reader = auth()->user();

    $mine = $notes
        ->mapWithKeys(fn ($note) => [$note->id => $note->emojiFrom($reader)])
        ->all();

    $palette = \App\Models\InnovationLabNote::EMOJI_GROUPS;
@endphp

<div class="lab-page">
    <div class="lab-header">
        <div>
            <h1 class="lab-heading">
                <i class="fas fa-lightbulb"></i> Innovation Lab
            </h1>
            <p class="lab-sub">What the team is building, and what has just changed.</p>
        </div>
    </div>

    @forelse($notes as $note)
        @php
            $postedAt = $note->published_at ?? $note->created_at;
            $isNew = $note->published_at && (! $newSince || $note->published_at->gt($newSince));

            $author = $note->author;
            $picture = $author?->profile?->profile_picture;
        @endphp

        <article class="lab-note">
            <div class="lab-byline">
                <span class="lab-avatar" aria-hidden="true">
                    @if($picture)
                        <img src="{{ asset('storage/'.ltrim($picture, '/')) }}" alt="">
                    @else
                        {{ mb_strtoupper(mb_substr($author?->name ?? 'CareerPath BN', 0, 1)) }}
                    @endif
                </span>

                <span class="lab-who">
                    <span class="lab-author">{{ $author?->name ?? 'CareerPath BN' }}</span>

                    {{--
                        How long ago, with the date itself on hover.
                        A feed is read by how recent things are; the
                        exact day matters once, when you go looking
                        for it.
                    --}}
                    <span class="lab-when" title="{{ $postedAt->format('d M Y, g:ia') }}">
                        {{ $postedAt->diffForHumans() }}
                    </span>
                </span>

                @if($isNew)
                    <span class="lab-fresh" title="Posted since your last visit"></span>
                @endif
            </div>

            {{--
                The title says what you are looking at, then the
                picture, then the detail. A headline above the
                photograph is how anything with something to
                announce is laid out.
            --}}
            <h2 class="lab-title">{{ $note->title }}</h2>

            @if($note->imageUrl())
                <span class="lab-media">
                    <img src="{{ $note->imageUrl() }}" alt="{{ $note->title }}" loading="lazy">
                </span>
            @endif

            <div class="lab-body" data-lab-body>{{ $note->bodyHtml() }}</div>

            {{--
                What a student can say back.

                Forms rather than buttons wired to script, so a
                reaction still lands with no JavaScript — it
                reloads the feed, which is worse than not
                reloading it and better than not working. The
                script below posts the same forms and swaps the
                counts in place.
            --}}
            <div class="lab-actions" data-lab-actions data-note="{{ $note->id }}">
                <div class="lab-reactions">
                    @foreach($note->reactionTally() as $emoji => $count)
                        <form method="POST"
                              action="{{ route('student.innovation-lab.react', $note) }}"
                              class="lab-react-form">
                            @csrf
                            <input type="hidden" name="emoji" value="{{ $emoji }}">
                            <button type="submit"
                                    class="lab-react {{ in_array($emoji, $mine[$note->id] ?? [], true) ? 'is-mine' : '' }}"
                                    title="{{ in_array($emoji, $mine[$note->id] ?? [], true) ? 'Take yours back' : 'Add yours' }}">
                                <span class="lab-react-emoji">{{ $emoji }}</span>
                                <span class="lab-react-count">{{ $count }}</span>
                            </button>
                        </form>
                    @endforeach

                    {{--
                        The rest of the palette, behind one
                        button, so a post with no reactions yet
                        shows one thing to press rather than six.
                    --}}
                    <details class="lab-pick">
                        <summary title="React to this post">
                            <i class="fas fa-face-smile"></i>
                        </summary>

                        <div class="lab-palette">
                            @foreach($palette as $group => $emojis)
                                <div class="lab-palette-group">
                                    <span class="lab-palette-name">{{ $group }}</span>

                                    <div class="lab-palette-keys">
                                        @foreach($emojis as $emoji)
                                            <form method="POST"
                                                  action="{{ route('student.innovation-lab.react', $note) }}">
                                                @csrf
                                                <input type="hidden" name="emoji" value="{{ $emoji }}">
                                                <button type="submit"
                                                        class="lab-palette-key {{ in_array($emoji, $mine[$note->id] ?? [], true) ? 'is-mine' : '' }}"
                                                        title="{{ $emoji }}">
                                                    {{ $emoji }}
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </details>
                </div>

                @if($note->accepts_interest)
                    <form method="POST"
                          action="{{ route('student.innovation-lab.interest', $note) }}"
                          class="lab-interest-form">
                        @csrf
                        <button type="submit"
                                class="lab-interest {{ $note->isInterested($reader) ? 'is-in' : '' }}">
                            <i class="fas {{ $note->isInterested($reader) ? 'fa-check' : 'fa-hand' }}"></i>
                            <span class="lab-interest-label">
                                {{ $note->isInterested($reader) ? 'Interested' : 'I am interested' }}
                            </span>
                            @if($note->interestCount() > 0)
                                <span class="lab-interest-count">{{ $note->interestCount() }}</span>
                            @endif
                        </button>
                    </form>
                @endif
            </div>

            <div class="lab-foot"></div>
        </article>
    @empty
        <p class="lab-empty">
            Nothing posted yet. This is where the team writes about what they are
            building, so there will be something here soon.
        </p>
    @endforelse
</div>

<script>
/*
 * Folding a long post.
 *
 * Measured rather than guessed from the length of the text,
 * because six lines of one post is not six lines of another —
 * the body keeps its own line breaks. A post that fits is left
 * alone and never gets a button.
 */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-lab-body]').forEach(function (body) {
        body.classList.add('is-folded');

        if (body.scrollHeight <= body.clientHeight + 4) {
            body.classList.remove('is-folded');

            return;
        }

        const more = document.createElement('button');

        more.type = 'button';
        more.className = 'lab-more';
        more.textContent = 'Read more';

        more.addEventListener('click', function () {
            body.classList.remove('is-folded');
            more.remove();
        });

        body.insertAdjacentElement('afterend', more);
    });
});
/*
 * Reacting without losing your place.
 *
 * The forms work on their own — pressed with no JavaScript they
 * post and the feed comes back. Here they are posted in the
 * background instead and only the counts are redrawn, because a
 * feed that jumps to the top every time you react is a feed you
 * stop reacting to.
 */
document.addEventListener('submit', function (event) {
    const form = event.target.closest('[data-lab-actions] form');

    if (!form) {
        return;
    }

    event.preventDefault();

    const actions = form.closest('[data-lab-actions]');

    fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('That did not save.');
            }

            return response.json();
        })
        .then(function (state) {
            redrawActions(actions, state);
        })
        .catch(function () {
            /*
             * Never a form.submit() here. It was one, and it
             * turned every failed request into a page reload —
             * which is exactly what a reaction is not supposed
             * to do, and looked from the outside like the
             * reaction itself refreshing the page. Say so
             * instead and leave the feed where it is.
             */
            say(actions, 'That did not save. Try again.');
        });
});

/*
 * A word when something failed, in the row that failed.
 */
function say(actions, words) {
    let note = actions.querySelector('.lab-said');

    if (!note) {
        note = document.createElement('span');
        note.className = 'lab-said';
        actions.appendChild(note);
    }

    note.textContent = words;

    window.setTimeout(function () {
        note.remove();
    }, 4000);
}

function redrawActions(actions, state) {
    const said = actions.querySelector('.lab-said');

    if (said) {
        said.remove();
    }

    const reactions = actions.querySelector('.lab-reactions');
    const pick = actions.querySelector('.lab-pick');
    const action = reactions.querySelector('form').action;
    const token = reactions.querySelector('input[name="_token"]').value;

    reactions.querySelectorAll('.lab-react-form').forEach(function (one) {
        one.remove();
    });

    Object.keys(state.reactions).forEach(function (emoji) {
        const mine = state.mine.includes(emoji);

        const form = document.createElement('form');

        form.method = 'POST';
        form.action = action;
        form.className = 'lab-react-form';
        form.innerHTML =
            '<input type="hidden" name="_token" value="' + token + '">'
            + '<input type="hidden" name="emoji" value="' + emoji + '">'
            + '<button type="submit" class="lab-react' + (mine ? ' is-mine' : '') + '">'
            + '<span class="lab-react-emoji">' + emoji + '</span>'
            + '<span class="lab-react-count">' + state.reactions[emoji] + '</span>'
            + '</button>';

        reactions.insertBefore(form, pick);
    });

    actions.querySelectorAll('.lab-palette-key').forEach(function (key) {
        key.classList.toggle(
            'is-mine',
            state.mine.includes(key.textContent.trim())
        );
    });

    /* The palette closes once something has been picked from it. */
    if (pick) {
        pick.open = false;
    }

    const interest = actions.querySelector('.lab-interest');

    if (!interest) {
        return;
    }

    interest.classList.toggle('is-in', state.interested);

    interest.querySelector('.lab-interest-label').textContent =
        state.interested ? 'Interested' : 'I am interested';

    interest.querySelector('i').className =
        'fas ' + (state.interested ? 'fa-check' : 'fa-hand');

    let count = interest.querySelector('.lab-interest-count');

    if (state.interest_count > 0) {
        if (!count) {
            count = document.createElement('span');
            count.className = 'lab-interest-count';
            interest.appendChild(count);
        }

        count.textContent = state.interest_count;
    } else if (count) {
        count.remove();
    }
}
</script>

@endsection
