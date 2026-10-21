{{--
    The book's page, its heading and its page controls.

    Shared by the panel that opens over a screen and the column
    that sits beside the framework's own values, because both are
    the same thing: one page of the book, with whatever is being
    checked boxed on it.

    $closable is false where there is nothing to close — a pane
    that is part of the page does not go away.
--}}
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

                @if($closable ?? true)
                    <button type="button" class="book-close" data-book-close aria-label="Close">
                        <i class="fas fa-xmark"></i>
                    </button>
                @endif
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
