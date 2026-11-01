// ============================================
// UNSAVED CHANGES, ON EVERY FORM THAT WRITES
// ============================================

/*
 * Any form that saves something (method POST) warns before its
 * changes are lost: on a refresh, on closing the tab, on typing
 * an address, and on a link anywhere, the page's own included.
 *
 * Each form is read the moment it is first touched, and compared
 * with that reading afterwards. So nothing a page's own scripts
 * set up on load counts as a change, and putting a value back
 * makes the form clean again.
 *
 * Edit Profile keeps its own, more detailed record
 * (window.formChanged), which is honoured here too. A form can
 * opt out with data-unsaved-ignore (searches that post, chat
 * boxes, uploads with their own guard), and a script that
 * changes a form by itself calls window.markUnsaved(form).
 */

const IGNORED_TYPES = ['submit', 'button', 'reset', 'image'];

const baselines = new WeakMap();
const forced = new WeakSet();

let leaving = false;

function isTracked(form) {
    if (! (form instanceof HTMLFormElement)) {
        return false;
    }

    if ((form.getAttribute('method') || 'get').toLowerCase() !== 'post') {
        return false;
    }

    if (
        form.id === 'profile-form'
        || form.id === 'logout-form'
        || form.hasAttribute('data-unsaved-ignore')
        || form.closest('[data-unsaved-ignore]')
    ) {
        return false;
    }

    /* A delete button or a toggle has nothing to type into. */
    return Array.from(form.elements).some((field) => {
        const type = (field.type || '').toLowerCase();

        return ! IGNORED_TYPES.includes(type)
            && type !== 'hidden'
            && field.tagName !== 'BUTTON'
            && field.tagName !== 'FIELDSET'
            && field.tagName !== 'OUTPUT';
    });
}

function readForm(form) {
    return JSON.stringify(
        Array.from(form.elements)
            .filter((field) => field.name
                && field.name !== '_token'
                && field.name !== '_method'
                && ! IGNORED_TYPES.includes((field.type || '').toLowerCase())
                && field.tagName !== 'BUTTON')
            .map((field) => {
                const type = (field.type || '').toLowerCase();

                if (type === 'checkbox' || type === 'radio') {
                    return [field.name, field.value, field.checked];
                }

                if (type === 'file') {
                    return [field.name, Array.from(field.files || []).map((file) => file.name + ':' + file.size)];
                }

                if (field.tagName === 'SELECT' && field.multiple) {
                    return [field.name, Array.from(field.selectedOptions).map((option) => option.value)];
                }

                return [field.name, field.value];
            })
    );
}

/* The form as it was just before it was first touched. */
function remember(form) {
    if (isTracked(form) && ! baselines.has(form)) {
        baselines.set(form, readForm(form));
    }
}

function isDirty(form) {
    if (! isTracked(form)) {
        return false;
    }

    if (forced.has(form)) {
        return true;
    }

    return baselines.has(form) && baselines.get(form) !== readForm(form);
}

/* Saved, or about to be: what it shows now is the new starting point. */
function settle(form) {
    forced.delete(form);

    if (baselines.has(form)) {
        baselines.set(form, readForm(form));
    }
}

export function hasUnsavedChanges() {
    if (leaving) {
        return false;
    }

    if (window.formChanged === true) {
        return true;
    }

    return Array.from(document.forms).some(isDirty);
}

function startWatching() {
    /* Read before the touch lands, so the touch itself is the change. */
    ['pointerdown', 'focusin', 'keydown', 'dragenter'].forEach((name) => {
        document.addEventListener(
            name,
            (event) => {
                const form = event.target?.closest?.('form');

                if (form) {
                    remember(form);
                }
            },
            true
        );
    });

    /*
     * Last to hear of a submit (on window, after every page script).
     * One that goes ahead is saving the form. One stopped by a page
     * script (a confirmation, a check, a background save) is left
     * as it is: if it goes ahead later, it is heard again.
     */
    window.addEventListener('submit', (event) => {
        if (! event.defaultPrevented && event.target instanceof HTMLFormElement) {
            settle(event.target);
        }
    });

    /* form.submit() sends without a submit event, so it is caught here. */
    const send = HTMLFormElement.prototype.submit;

    HTMLFormElement.prototype.submit = function () {
        settle(this);

        return send.call(this);
    };

    window.markUnsaved = (form) => {
        if (form instanceof HTMLFormElement && isTracked(form)) {
            remember(form);
            forced.add(form);
        }
    };

    window.markSaved = (form) => {
        if (form instanceof HTMLFormElement) {
            settle(form);
        }
    };

    window.hasUnsavedChanges = hasUnsavedChanges;

    /* For a script that has already asked the student itself. */
    window.leaveWithoutWarning = () => {
        leaving = true;
    };

    /* Refresh, closing the tab, typing an address: the browser's own warning. */
    window.addEventListener('beforeunload', (event) => {
        if (! hasUnsavedChanges()) {
            return;
        }

        event.preventDefault();
        event.returnValue = 'You have unsaved changes.';

        return event.returnValue;
    });

    /*
     * Links get the site's own warning, a link to this very page
     * included: following it reloads the page and loses the work
     * just the same. Only a jump within the page is let through.
     */
    document.addEventListener(
        'click',
        (event) => {
            if (
                event.defaultPrevented
                || event.button !== 0
                || event.ctrlKey
                || event.metaKey
                || event.shiftKey
                || event.altKey
            ) {
                return;
            }

            const link = event.target.closest?.('a[href]');

            if (! link || link.target === '_blank' || link.hasAttribute('download')) {
                return;
            }

            const href = link.getAttribute('href') || '';

            if (
                href === ''
                || href.startsWith('#')
                || href.startsWith('javascript:')
                || href.startsWith('mailto:')
                || href.startsWith('tel:')
            ) {
                return;
            }

            /* Edit Profile asks with its own words. */
            if (document.getElementById('profile-form')) {
                return;
            }

            if (! hasUnsavedChanges()) {
                return;
            }

            const destination = new URL(link.href, window.location.href);
            const current = new URL(window.location.href);

            if (
                destination.hash
                && destination.origin === current.origin
                && destination.pathname === current.pathname
                && destination.search === current.search
            ) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            const go = () => {
                leaving = true;
                window.location.href = link.href;
            };

            if (typeof window.showConfirmModal !== 'function') {
                if (window.confirm('You have unsaved changes. If you leave this page, they will be lost.')) {
                    go();
                }

                return;
            }

            window.showConfirmModal({
                title: 'Unsaved Changes',
                message: 'You have unsaved changes on this page. If you leave it, those changes will be lost.',
                confirmText: 'Leave Page',
                cancelText: 'Stay Here',
                type: 'warning',
                onConfirm: go,
            });
        },
        true
    );

    /* Returning with Back shows the old page again, still guarded. */
    window.addEventListener('pageshow', () => {
        leaving = false;
    });
}

startWatching();
