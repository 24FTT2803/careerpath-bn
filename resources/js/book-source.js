/**
 * Open the reference book at the place a value was read from.
 *
 * The reader records a page and a handful of positions for every
 * value it extracts. This draws that: the page as it is printed,
 * with a box around the words the value came from, so an
 * administrator deciding between the book's value and the
 * framework's can read the sentence rather than take anybody's
 * word for it.
 *
 * pdf.js is loaded the first time somebody asks for a page, not
 * on every page of the site. It is a megabyte of worker to
 * render a page of a book, and all but a handful of admin
 * screens have no book to render.
 */

let loading = null;

function pdfjs() {
    if (loading === null) {
        loading = (async () => {
            const lib = await import('pdfjs-dist');
            const worker = await import('pdfjs-dist/build/pdf.worker.min.mjs?url');

            lib.GlobalWorkerOptions.workerSrc = worker.default;

            return lib;
        })();
    }

    return loading;
}

/** One open document per edition, so flipping pages re-parses nothing. */
const documents = new Map();

async function documentAt(url) {
    if (!documents.has(url)) {
        const lib = await pdfjs();

        documents.set(
            url,
            lib.getDocument({ url, withCredentials: true }).promise
        );
    }

    return documents.get(url);
}

/**
 * Two readers of one PDF need not agree about where a page
 * begins — one counts up from the foot, the other down from the
 * head — and the reader that recorded these positions is not the
 * one drawing them. So a span is found by what it says, and its
 * position only decides between the places it says it.
 */
function normalise(text) {
    return String(text ?? '')
        .replace(/\s+/g, ' ')
        .trim()
        .toLowerCase();
}

function sameWords(a, b) {
    if (a === '' || b === '') {
        return false;
    }

    if (a === b) {
        return true;
    }

    /*
     * A printed line and a stored span are not always the same
     * string: the reader joins the runs of a line, and a line
     * can be set as several runs. Either containing the other is
     * the same words on the page.
     */
    const [shorter, longer] = a.length <= b.length ? [a, b] : [b, a];

    return shorter.length >= 8 && longer.includes(shorter);
}

/**
 * Which text items on the page a stored span refers to.
 *
 * @returns {Array<object>}
 */
function itemsFor(span, items, height) {
    const wanted = normalise(span.text);

    const matches = items.filter((item) => sameWords(normalise(item.str), wanted));

    if (matches.length < 2) {
        return matches;
    }

    /*
     * The same words twice on one page. The stored position
     * decides, measured both ways up the page, because the
     * reader that recorded it may have counted from either end.
     */
    const distance = (item) => {
        const y = item.transform[5];

        return Math.min(
            Math.abs(-y - span.y),
            Math.abs(height - y - span.y)
        );
    };

    const nearest = matches.reduce(
        (best, item) => (distance(item) < distance(best) ? item : best),
        matches[0]
    );

    return [nearest];
}

function boxFor(lib, item, viewport, scale) {
    const placed = lib.Util.transform(viewport.transform, item.transform);

    const width = item.width * scale;
    const height = item.height * scale;

    return {
        left: placed[4],
        top: placed[5] - height,
        width,
        height,
    };
}

/**
 * Render one page, and box the words.
 *
 * @returns {Promise<number>} how many spans were found
 */
async function draw(panel, { url, page, spans }) {
    const canvas = panel.querySelector('[data-book-canvas]');
    const layer = panel.querySelector('[data-book-highlights]');
    const lib = await pdfjs();

    const document_ = await documentAt(url);

    const number = Math.min(Math.max(1, Number(page) || 1), document_.numPages);
    const sheet = await document_.getPage(number);

    const unscaled = sheet.getViewport({ scale: 1 });

    /*
     * Wide enough to read, never wider than the panel. A page of
     * this book is three columns of small type: shrunk to fit a
     * phone it is a grey rectangle.
     */
    const room = panel.querySelector('[data-book-stage]').clientWidth || unscaled.width;
    const scale = Math.max(0.6, Math.min(2, room / unscaled.width));

    const viewport = sheet.getViewport({ scale });

    const ratio = window.devicePixelRatio || 1;

    canvas.width = Math.floor(viewport.width * ratio);
    canvas.height = Math.floor(viewport.height * ratio);
    canvas.style.width = `${Math.floor(viewport.width)}px`;
    canvas.style.height = `${Math.floor(viewport.height)}px`;

    const context = canvas.getContext('2d');

    context.setTransform(ratio, 0, 0, ratio, 0, 0);

    await sheet.render({ canvasContext: context, viewport }).promise;

    layer.style.width = `${Math.floor(viewport.width)}px`;
    layer.style.height = `${Math.floor(viewport.height)}px`;
    layer.innerHTML = '';

    const text = await sheet.getTextContent();

    const items = text.items.filter((item) => typeof item.str === 'string' && item.str.trim() !== '');

    let found = 0;

    (spans || []).forEach((span) => {
        itemsFor(span, items, unscaled.height).forEach((item) => {
            const box = boxFor(lib, item, viewport, scale);

            const mark = document.createElement('span');

            mark.className = 'book-mark';
            mark.style.left = `${box.left}px`;
            mark.style.top = `${box.top}px`;
            mark.style.width = `${box.width}px`;
            mark.style.height = `${box.height}px`;

            layer.appendChild(mark);

            found += 1;
        });
    });

    return found;
}

function showing(panel, state) {
    panel.querySelectorAll('[data-book-state]').forEach((node) => {
        node.hidden = node.dataset.bookState !== state;
    });
}

/** What the panel is showing right now, so it can turn a page. */
const showing_ = { url: null, page: 1, spans: [], label: '', title: '', pages: 1 };

async function render(panel) {
    showing(panel, 'loading');

    try {
        const found = await draw(panel, showing_);

        const document_ = await documentAt(showing_.url);

        showing_.pages = document_.numPages;

        panel.querySelector('[data-book-page]').textContent =
            `page ${showing_.page} of ${document_.numPages}`;

        const jump = panel.querySelector('[data-book-jump]');

        if (jump) {
            jump.value = showing_.page;
            jump.max = document_.numPages;
        }

        panel.querySelectorAll('[data-book-prev]').forEach((b) => { b.disabled = showing_.page <= 1; });
        panel.querySelectorAll('[data-book-next]').forEach((b) => { b.disabled = showing_.page >= document_.numPages; });

        /*
         * Nothing to box is not a failure to find anything. The
         * book opened to be read says so; a value's page that
         * could not be matched says the other thing.
         */
        showing(panel, showing_.spans.length === 0 ? 'plain' : (found > 0 ? 'drawn' : 'unmarked'));
    } catch (error) {
        showing(panel, 'failed');

        panel.querySelector('[data-book-why]').textContent = error?.message
            ? String(error.message)
            : 'The book could not be opened.';
    }
}

window.cpbnBookSource = {
    /**
     * @param {{url: string, page: number, spans: Array, label: string, title: string}} where
     */
    async open(where) {
        const panel = document.getElementById('bookSource');

        if (!panel) {
            return;
        }

        Object.assign(showing_, {
            url: where.url,
            page: Number(where.page) || 1,
            spans: where.spans || [],
            label: where.label || '',
            title: where.title || 'The book',
        });

        panel.hidden = false;
        document.body.classList.add('book-source-open');

        panel.querySelector('[data-book-title]').textContent = showing_.title;
        panel.querySelector('[data-book-field]').textContent = showing_.label;

        const full = panel.querySelector('[data-book-full]');

        if (full) {
            full.href = `${showing_.url}#page=${showing_.page}`;
        }

        await render(panel);
    },

    /**
     * Turn to a page.
     *
     * The boxes do not come with it: they belong to the page the
     * value was read from, and drawn on another page they would
     * be pointing at whatever happens to sit there.
     */
    async goTo(page) {
        const panel = document.getElementById('bookSource');

        if (!panel || panel.hidden || !showing_.url) {
            return;
        }

        const wanted = Math.min(Math.max(1, Number(page) || 1), showing_.pages);

        if (wanted === showing_.page) {
            return;
        }

        showing_.page = wanted;
        showing_.spans = [];
        showing_.label = 'the book itself';

        panel.querySelector('[data-book-field]').textContent = showing_.label;

        const full = panel.querySelector('[data-book-full]');

        if (full) {
            full.href = `${showing_.url}#page=${wanted}`;
        }

        await render(panel);
    },

    turn(by) {
        return this.goTo(showing_.page + by);
    },

    close() {
        const panel = document.getElementById('bookSource');

        if (!panel) {
            return;
        }

        panel.hidden = true;
        document.body.classList.remove('book-source-open');
    },
};
