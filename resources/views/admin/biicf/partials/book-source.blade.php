{{--
    The reference book, open at a page, over whatever screen asked
    for it.

    One panel per screen rather than one per thing that can open
    it: a new edition stages a hundred and twenty differences and
    the editions list holds every edition there has ever been.
    Rendering a page of a three-hundred-page book into each of
    them is not a page anybody would wait for.

    Nothing here names an edition. Which book, which page and
    what to box are all given when it is opened, so one panel
    serves a screen that lists several books.
--}}
<div class="book-source" id="bookSource" hidden>
    <div class="book-panel" role="dialog" aria-modal="true" aria-labelledby="bookSourceTitle">
        <div class="book-bar">
            <div>
                <h3 id="bookSourceTitle" data-book-title></h3>
                <p>
                    <span data-book-field></span>
                    <span class="book-dot">·</span>
                    <span data-book-page></span>
                </p>
            </div>

            <div class="book-bar-actions">
                {{--
                    Turning the page, because reading one page of a
                    three-hundred-page book is not reading the book. A
                    value's own page is where it opens; from there it
                    goes wherever the reader wants.
                --}}
                <div class="book-turn">
                    <button type="button" class="book-step" data-book-prev aria-label="Previous page">
                        <i class="fas fa-chevron-left"></i>
                    </button>

                    <input type="number" class="book-jump" data-book-jump min="1" value="1"
                           aria-label="Go to page">

                    <button type="button" class="book-step" data-book-next aria-label="Next page">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

                <a class="btn btn-outline btn-small" data-book-full href="#" target="_blank" rel="noopener">
                    <i class="fas fa-up-right-from-square"></i> Whole book
                </a>

                <button type="button" class="book-close" data-book-close aria-label="Close">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>
        </div>

        <p class="book-note" data-book-state="loading" hidden>Opening the book…</p>

        <p class="book-note" data-book-state="drawn" hidden>
            Boxed in gold is what the reader read this value from.
        </p>

        <p class="book-note" data-book-state="plain" hidden>
            Scroll the page, or open the whole book to read on.
        </p>

        <p class="book-note book-note-warn" data-book-state="unmarked" hidden>
            The page is here, but the words could not be found on it to
            draw a box around. The value was read from this page.
        </p>

        <p class="book-note book-note-warn" data-book-state="failed" hidden>
            <span data-book-why></span>
        </p>

        <div class="book-stage" data-book-stage>
            <div class="book-sheet">
                <canvas data-book-canvas></canvas>
                <div class="book-highlights" data-book-highlights></div>
            </div>
        </div>
    </div>
</div>

<script>
/*
 * Opening it.
 *
 * Every wiring here waits for DOMContentLoaded, because the
 * thing it calls does not exist yet otherwise: app.js is a
 * module, modules are deferred, and this script runs while the
 * page is still parsing. A panel asked to open at parse time
 * found no opener and did nothing at all, which is what "I press
 * the book and there is no book" was.
 */
document.addEventListener('DOMContentLoaded', function () {
    if (!window.cpbnBookSource) {
        return;
    }

    function spansOn(button) {
        try {
            return JSON.parse(button.dataset.spans || '[]');
        } catch (error) {
            return [];
        }
    }

    document.addEventListener('click', function (event) {
        var at = event.target.closest('[data-book-at]');

        if (at) {
            event.preventDefault();

            window.cpbnBookSource.open({
                url: at.dataset.url,
                page: parseInt(at.dataset.page, 10) || 1,
                spans: spansOn(at),
                label: at.dataset.label || '',
                title: at.dataset.title || 'The book',
            });

            return;
        }

        var panel = document.getElementById('bookSource');

        if (!panel || panel.hidden) {
            return;
        }

        /* The close button, or the dark outside the panel. */
        if (event.target.closest('[data-book-close]') || event.target === panel) {
            window.cpbnBookSource.close();
        }
    });

    document.addEventListener('click', function (event) {
        if (event.target.closest('[data-book-prev]')) {
            window.cpbnBookSource.turn(-1);
        } else if (event.target.closest('[data-book-next]')) {
            window.cpbnBookSource.turn(1);
        }
    });

    document.addEventListener('change', function (event) {
        var jump = event.target.closest('[data-book-jump]');

        if (jump) {
            window.cpbnBookSource.goTo(jump.value);
        }
    });

    document.addEventListener('keydown', function (event) {
        var panel = document.getElementById('bookSource');

        if (!panel || panel.hidden) {
            return;
        }

        if (event.key === 'Escape') {
            window.cpbnBookSource.close();

            return;
        }

        /* Not while a page number is being typed. */
        if (event.target.closest('[data-book-jump]')) {
            return;
        }

        if (event.key === 'ArrowLeft') {
            window.cpbnBookSource.turn(-1);
        } else if (event.key === 'ArrowRight') {
            window.cpbnBookSource.turn(1);
        }
    });

    /*
     * Arrived here to read the book rather than to decide
     * anything — opened at the first page, with nothing to box.
     */
    var asked = document.querySelector('[data-book-on-load]');

    if (asked) {
        window.cpbnBookSource.open({
            url: asked.dataset.url,
            page: parseInt(asked.dataset.page, 10) || 1,
            spans: [],
            label: 'the book itself',
            title: asked.dataset.title || 'The book',
        });
    }
});
</script>

<style>
    /*
     * The page a value was read from, opened over the review.
     *
     * Gold around the words rather than a wash of highlighter
     * yellow over them: the point is to read the sentence, and a
     * box leaves the type underneath it alone.
     */
    /*
     * The file name in the heading, which opens the book here
     * rather than in a tab of its own. A button because that is
     * what it does, dressed as the link it replaced.
     */
    .book-link { padding: 0; font: inherit; color: #1a3a5c; background: none; border: none; border-bottom: 1px solid #c9a84c; cursor: pointer; }
    .book-link:hover { color: #8a6420; }

    .book-at { display: inline-flex; align-items: center; gap: 5px; padding: 2px 9px; font: inherit; font-size: 11px; font-weight: 600; letter-spacing: .03em; color: #8a6420; background: #fbf1de; border: 1px solid #f0e3c0; border-radius: 999px; cursor: pointer; }
    .book-at:hover { background: #c9a84c; border-color: #c9a84c; color: #0d1f33; }
    .book-at i { font-size: 10px; }

    .book-source { position: fixed; inset: 0; z-index: 1200; display: flex; align-items: center; justify-content: center; padding: 24px; background: rgba(13, 31, 51, .62); }
    .book-source[hidden] { display: none; }
    body.book-source-open { overflow: hidden; }

    .book-panel { display: flex; flex-direction: column; width: 100%; max-width: 980px; max-height: 100%; background: white; border-radius: 14px; overflow: hidden; box-shadow: 0 24px 60px rgba(13, 31, 51, .35); }
    .book-bar { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; padding: 16px 20px; background: #faf8f2; border-bottom: 1px solid #e5e7eb; }
    .book-bar h3 { margin: 0; font-family: 'Playfair Display', serif; font-size: 18px; font-weight: 700; color: #1a3a5c; }
    .book-bar p { margin: 4px 0 0; font-size: 12px; color: #6b7280; font-family: ui-monospace, Menlo, Consolas, monospace; }
    .book-dot { margin: 0 6px; color: #c9a84c; }
    .book-bar-actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
    .book-turn { display: inline-flex; align-items: center; gap: 4px; }
    .book-step { width: 30px; height: 30px; display: grid; place-items: center; background: transparent; border: 2px solid #e5e7eb; border-radius: 8px; color: #6b7280; cursor: pointer; font-size: 11px; }
    .book-step:hover:not(:disabled) { border-color: #c9a84c; color: #c9a84c; }
    .book-step:disabled { opacity: .4; cursor: not-allowed; }
    .book-jump { width: 58px; padding: 5px 6px; text-align: center; font: inherit; font-size: 12px; border: 2px solid #e5e7eb; border-radius: 8px; }
    .book-jump:focus { outline: none; border-color: #c9a84c; }

    .book-close { width: 34px; height: 34px; display: grid; place-items: center; background: transparent; border: 2px solid #e5e7eb; border-radius: 8px; color: #6b7280; cursor: pointer; }
    .book-close:hover { border-color: #c9a84c; color: #c9a84c; }

    .book-note { margin: 0; padding: 10px 20px; font-size: 12px; color: #6b7280; background: #fbfbfa; border-bottom: 1px solid #f3f4f6; }
    .book-note-warn { color: #8a6420; background: #fffbeb; }

    .book-stage { flex: 1; min-height: 0; overflow: auto; padding: 18px; background: #f3f4f6; display: flex; justify-content: center; align-items: flex-start; }
    .book-sheet { position: relative; line-height: 0; box-shadow: 0 2px 14px rgba(13, 31, 51, .18); }
    .book-sheet canvas { display: block; }
    .book-highlights { position: absolute; inset: 0; pointer-events: none; }

    .book-mark { position: absolute; border: 2px solid #c9a84c; border-radius: 3px; background: rgba(201, 168, 76, .16); box-shadow: 0 0 0 3px rgba(201, 168, 76, .18); }

    @media (max-width: 720px) {
        .book-source { padding: 0; }
        .book-panel { max-width: none; border-radius: 0; }
        .book-bar { flex-direction: column; }
    }
</style>
