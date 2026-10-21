{{--
    The behaviour behind the admin's four listings.

    They were server-paged, so every page of results was a round
    trip that reloaded the whole screen and dropped whatever
    sorting and filtering had been set. None of these tables is
    large — a hundred and thirty-nine competencies is the biggest
    — so the rows are sent once and the sorting, filtering and
    paging happen here. Sorting therefore survives paging because
    there is nothing to survive: the page never reloads.

    Included once per listing, before the table that uses it.
--}}
<script>
    window.adminTable = function (rows, options) {
        options = options || {};

        return {
            rows: rows,
            query: '',
            filters: options.filters || {},
            searchable: options.searchable || ['name'],
            sort: { key: options.sort || 'id', direction: options.direction || 'asc' },
            size: options.size || 15,
            page: 1,

            /*
             * A row matches when every filter set on it matches
             * and the search text appears in one of the columns
             * worth searching. A filter set to the empty string
             * is not set.
             */
            get filtered() {
                const needle = this.query.trim().toLowerCase();
                const names = Object.keys(this.filters);

                return this.rows.filter((row) => {
                    for (const name of names) {
                        const wanted = this.filters[name];

                        if (wanted === '' || wanted === null) {
                            continue;
                        }

                        const held = row[name];

                        if (Array.isArray(held)) {
                            if (!held.map(String).includes(String(wanted))) {
                                return false;
                            }

                            continue;
                        }

                        if (String(held ?? '') !== String(wanted)) {
                            return false;
                        }
                    }

                    if (needle === '') {
                        return true;
                    }

                    return this.searchable.some((key) => {
                        const value = row[key];

                        if (Array.isArray(value)) {
                            return value.join(' ').toLowerCase().includes(needle);
                        }

                        return String(value ?? '').toLowerCase().includes(needle);
                    });
                });
            },

            /*
             * Numbers compare as numbers and everything else as
             * text, so an ID column sorts 2 before 10 rather
             * than after it. A blank sorts last whichever way
             * the column is pointing: it is an absence, not a
             * smallest value.
             */
            get ordered() {
                const key = this.sort.key;
                const way = this.sort.direction === 'desc' ? -1 : 1;

                return [...this.filtered].sort((a, b) => {
                    const left = this.comparable(a[key]);
                    const right = this.comparable(b[key]);

                    if (left === null && right === null) return 0;
                    if (left === null) return 1;
                    if (right === null) return -1;

                    if (typeof left === 'number' && typeof right === 'number') {
                        return (left - right) * way;
                    }

                    return String(left).localeCompare(String(right), undefined, {
                        numeric: true,
                        sensitivity: 'base',
                    }) * way;
                });
            },

            comparable(value) {
                if (value === null || value === undefined || value === '') {
                    return null;
                }

                if (Array.isArray(value)) {
                    return value.length ? String(value[0]) : null;
                }

                return typeof value === 'number' ? value : String(value);
            },

            get pages() {
                return Math.max(1, Math.ceil(this.filtered.length / this.size));
            },

            get shown() {
                const from = (this.current - 1) * this.size;

                return this.ordered.slice(from, from + this.size);
            },

            /*
             * Clamped rather than stored, so narrowing the
             * filters while on the last page does not leave the
             * table looking empty.
             */
            get current() {
                return Math.min(this.page, this.pages);
            },

            get from() {
                return this.filtered.length === 0 ? 0 : (this.current - 1) * this.size + 1;
            },

            get to() {
                return Math.min(this.current * this.size, this.filtered.length);
            },

            /*
             * A window of page numbers around the current one.
             * All of them for a handful of pages; otherwise the
             * five nearest, with the ends always reachable.
             */
            get numbers() {
                const pages = this.pages;

                if (pages <= 7) {
                    return Array.from({ length: pages }, (_, i) => i + 1);
                }

                const around = [1, pages];

                for (let page = this.current - 2; page <= this.current + 2; page++) {
                    if (page > 1 && page < pages) {
                        around.push(page);
                    }
                }

                return [...new Set(around)].sort((a, b) => a - b);
            },

            sortBy(key) {
                if (this.sort.key === key) {
                    this.sort.direction = this.sort.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sort = { key: key, direction: 'asc' };
                }
            },

            caret(key) {
                if (this.sort.key !== key) {
                    return 'fas fa-sort';
                }

                return this.sort.direction === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
            },

            go(page) {
                this.page = Math.min(Math.max(1, page), this.pages);
            },

            reset() {
                this.query = '';
                this.page = 1;

                for (const name of Object.keys(this.filters)) {
                    this.filters[name] = '';
                }
            },

            /*
             * The confirmation the rest of the admin uses. Its
             * own wiring reads the page once on load, which
             * cannot see rows drawn here, so the dialog is asked
             * for directly.
             */
            confirmDelete(form, name) {
                window.showConfirmModal({
                    title: 'Confirm Delete',
                    message: 'Are you sure you want to delete "' + (name || 'this item') + '"? This action cannot be undone.',
                    confirmText: 'Yes, Delete',
                    cancelText: 'Cancel',
                    type: 'danger',
                    onConfirm: function () {
                        form.submit();
                    },
                });
            },
        };
    };
</script>

<style>
    /*
     * A sortable header says so: the whole cell is the control,
     * and the arrow shows which way the column is pointing.
     */
    .table th.sortable { cursor: pointer; user-select: none; white-space: nowrap; }
    .table th.sortable:hover { color: #1a3a5c; }
    .table th.sortable i { margin-left: 6px; font-size: 10px; opacity: .45; }
    .table th.sortable.is-sorted { color: #1a3a5c; }
    .table th.sortable.is-sorted i { opacity: 1; color: #c9a84c; }

    /*
     * The pager wraps instead of running off a narrow screen,
     * and the page numbers give way first — the count and the
     * next/previous controls are what a phone needs.
     */
    .pager { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-top: 16px; }
    .pager-count { font-size: 13px; color: #6b7280; }
    .pager-count select { margin-left: 8px; padding: 4px 8px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 13px; }
    .pager-pages { display: flex; flex-wrap: wrap; align-items: center; gap: 4px; }
    .pager-pages button { min-width: 34px; height: 34px; padding: 0 8px; border: 1px solid #e5e7eb; background: white; border-radius: 8px; font-size: 13px; font-weight: 500; color: #1a1a2e; cursor: pointer; }
    .pager-pages button:hover:not(:disabled) { border-color: #c9a84c; color: #c9a84c; }
    .pager-pages button.is-current { background: #1a3a5c; border-color: #1a3a5c; color: white; }
    .pager-pages button:disabled { opacity: .4; cursor: default; }
    .pager-number-wrap { display: inline-flex; align-items: center; gap: 4px; }
    .pager-gap { color: #9ca3af; padding: 0 2px; }
    .pager-of { display: none; font-size: 13px; color: #6b7280; padding: 0 6px; }

    @media (max-width: 640px) {
        .pager { justify-content: center; }
        .pager-pages .pager-number, .pager-gap { display: none; }
        .pager-of { display: inline; }
    }

    .filter-buttons { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }
    .filter-buttons button { padding: 6px 14px; border: 1px solid #e5e7eb; background: white; border-radius: 999px; font-size: 13px; font-weight: 500; color: #1a1a2e; cursor: pointer; }
    .filter-buttons button:hover { border-color: #c9a84c; }
    .filter-buttons button.is-on { background: #1a3a5c; border-color: #1a3a5c; color: white; }
</style>
