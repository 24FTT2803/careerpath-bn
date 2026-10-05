{{--
    Site-wide motion and micro-interactions.

    Include once inside <head>, after the page's own styles:
        @include('partials.motion')                      content reveals + interactions
        @include('partials.motion', ['reveal' => false]) interactions only (pages with their own animations)

    Opening animation (header drops in, logo spins/wipes in):
        - pages with reveal => false (home, log in, sign up...) play it on every visit;
        - student, lecturer and admin pages play it once per browser tab, on arrival.

    Everything is skipped for visitors who ask their system for reduced motion,
    and the page still works normally if JavaScript is off.
--}}
@php
    $motionReveal = $reveal ?? true;
    $motionIntro = $intro ?? ($motionReveal ? 'once' : 'always');
@endphp

<script>
    (function () {
        var root = document.documentElement;

        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        root.classList.add('motion-on');

        /* Opening animation: always on landing pages, once per tab elsewhere. */
        var introMode = @json($motionIntro);
        var playIntro = introMode === 'always';

        if (introMode === 'once') {
            try {
                var area = 'cpbn-intro:' + (window.location.pathname.split('/')[1] || 'home');

                if (! window.sessionStorage.getItem(area)) {
                    window.sessionStorage.setItem(area, '1');
                    playIntro = true;
                }
            } catch (error) {
                playIntro = false;
            }
        }

        if (playIntro) {
            root.classList.add('motion-intro');

            /* Hand control back to hover effects once the opening has played. */
            setTimeout(function () {
                root.classList.remove('motion-intro');
            }, 2200);
        }

        @if($motionReveal)
            root.classList.add('motion-pending');

            /* Never leave content hidden if something goes wrong. */
            setTimeout(function () {
                root.classList.remove('motion-pending');
            }, 1500);
        @endif
    })();
</script>

<style>
    /* Smooth cross-fade between pages (Chrome/Edge; other browsers just navigate normally). */
    @view-transition {
        navigation: auto;
    }

    ::view-transition-old(root),
    ::view-transition-new(root) {
        animation-duration: 0.22s;
        animation-timing-function: ease;
    }

    /* ---------- Content reveal ---------- */
    html.motion-pending main {
        opacity: 0;
    }

    html.motion-on [data-reveal] {
        opacity: 0;
        transform: translateY(14px);
    }

    html.motion-on [data-reveal="fade"] {
        transform: none;
    }

    html.motion-on [data-reveal].is-revealed {
        opacity: 1;
        transform: none;
        transition:
            opacity 0.55s cubic-bezier(0.22, 0.61, 0.36, 1) var(--reveal-delay, 0ms),
            transform 0.55s cubic-bezier(0.22, 0.61, 0.36, 1) var(--reveal-delay, 0ms);
    }

    /* ---------- Page-loading bar ---------- */
    .motion-progress {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 100000;
        width: 0;
        height: 3px;
        background: linear-gradient(90deg, #c9a84c, #e8d4a0);
        box-shadow: 0 0 8px rgba(201, 168, 76, 0.6);
        opacity: 0;
        pointer-events: none;
    }

    .motion-progress.is-active {
        width: 85%;
        opacity: 1;
        transition: width 2.5s cubic-bezier(0.1, 0.6, 0.2, 1), opacity 0.2s ease;
    }

    .motion-progress.is-done {
        width: 100%;
        opacity: 0;
        transition: width 0.2s ease, opacity 0.4s ease 0.2s;
    }

    /* ---------- Text links: underline that draws in ---------- */
    html.motion-on .motion-link {
        text-decoration: none;
        background-image: linear-gradient(currentColor, currentColor);
        background-repeat: no-repeat;
        background-position: 0 100%;
        background-size: 0% 1px;
        transition: background-size 0.28s ease, color 0.2s ease;
    }

    html.motion-on .motion-link:hover,
    html.motion-on .motion-link:focus-visible {
        background-size: 100% 1px;
        text-decoration: none;
    }

    html.motion-on .motion-link i,
    html.motion-on .motion-link svg {
        transition: transform 0.25s ease;
    }

    html.motion-on .motion-link:hover .fa-arrow-right,
    html.motion-on .motion-link:hover .fa-chevron-right,
    html.motion-on .motion-link:hover .fa-external-link-alt {
        transform: translateX(3px);
    }

    html.motion-on .motion-link:hover .fa-arrow-left {
        transform: translateX(-3px);
    }

    /* ---------- Clickable cards without their own hover ---------- */
    html.motion-on .motion-card {
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }

    html.motion-on .motion-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(26, 58, 92, 0.1);
    }

    /* ---------- Press feedback on buttons ---------- */
    html.motion-on :is(button, .btn, [role="button"], input[type="submit"], input[type="button"]):not(:disabled):active {
        transform: scale(0.97);
        transition-duration: 0.08s;
    }

    /* ---------- Form fields ease into focus ---------- */
    :where(input, select, textarea) {
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }

    /* ---------- Admin / lecturer sidebar ---------- */
    html.motion-on .sidebar-link {
        position: relative;
    }

    html.motion-on .sidebar-link::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        width: 3px;
        height: 0;
        border-radius: 3px;
        background: var(--accent, #c9a84c);
        transform: translateY(-50%);
        transition: height 0.25s ease;
    }

    html.motion-on .sidebar-link:hover::before {
        height: 45%;
    }

    html.motion-on .sidebar-link.active::before {
        height: 60%;
    }

    html.motion-on .sidebar-link i {
        transition: transform 0.25s ease, color 0.2s ease;
    }

    html.motion-on .sidebar-link:hover i {
        transform: translateX(3px);
    }

    /* ---------- Student header ---------- */
    html.motion-on .nav-user-wrapper.active .dropdown,
    html.motion-on .notif-popup:not([hidden]) {
        animation: motion-pop 0.18s ease-out;
        transform-origin: top right;
    }

    html.motion-on .dropdown-item i {
        transition: transform 0.2s ease, color 0.2s ease;
    }

    html.motion-on .dropdown-item:hover i {
        transform: translateX(2px);
        color: var(--primary, #1a3a5c);
    }

    html.motion-on .dropdown-item.danger:hover i {
        color: var(--danger, #c0392b);
    }

    html.motion-on .nav-notif:hover > .fa-bell {
        animation: motion-bell 0.6s ease;
    }

    html.motion-on .nav-back i {
        transition: transform 0.2s ease;
    }

    html.motion-on .nav-back:hover i {
        transform: translateX(-2px);
    }

    @keyframes motion-pop {
        from {
            opacity: 0;
            transform: translateY(-6px) scale(0.98);
        }

        to {
            opacity: 1;
            transform: none;
        }
    }

    @keyframes motion-bell {
        0%, 100% { transform: rotate(0); }
        20% { transform: rotate(14deg); }
        40% { transform: rotate(-12deg); }
        60% { transform: rotate(8deg); }
        80% { transform: rotate(-4deg); }
    }

    /* ---------- Opening animation ---------- */
    html.motion-intro :is(.site-header, .site-nav, .auth-header) {
        animation: motion-drop 0.7s cubic-bezier(0.2, 0.8, 0.2, 1) backwards;
    }

    html.motion-intro .admin-sidebar {
        animation: motion-slide-in 0.7s cubic-bezier(0.2, 0.8, 0.2, 1) backwards;
    }

    html.motion-intro img[src*="careerpath-badge"] {
        animation: motion-badge-in 1s cubic-bezier(0.2, 0.8, 0.2, 1) 0.15s backwards;
    }

    html.motion-intro img[src*="careerpath-logo"] {
        animation: motion-wipe 0.9s cubic-bezier(0.6, 0, 0.2, 1) 0.4s backwards;
    }

    html.motion-intro :is(.nav-links, .nav-actions, .nav-right) > * {
        animation: motion-fade-down 0.6s ease backwards;
    }

    html.motion-intro :is(.nav-links, .nav-actions, .nav-right) > :nth-child(1) { animation-delay: 0.45s; }
    html.motion-intro :is(.nav-links, .nav-actions, .nav-right) > :nth-child(2) { animation-delay: 0.53s; }
    html.motion-intro :is(.nav-links, .nav-actions, .nav-right) > :nth-child(3) { animation-delay: 0.61s; }
    html.motion-intro :is(.nav-links, .nav-actions, .nav-right) > :nth-child(4) { animation-delay: 0.69s; }
    html.motion-intro :is(.nav-links, .nav-actions, .nav-right) > :nth-child(n + 5) { animation-delay: 0.77s; }

    html.motion-intro .hero {
        animation: motion-hero-drift 1.8s cubic-bezier(0.2, 0.8, 0.2, 1) backwards;
    }

    html.motion-intro .auth-card {
        animation: motion-rise 0.7s cubic-bezier(0.2, 0.8, 0.2, 1) 0.2s backwards;
    }

    html.motion-intro .auth-card > * {
        animation: motion-fade-up 0.5s ease backwards;
    }

    html.motion-intro .auth-card > :nth-child(1) { animation-delay: 0.35s; }
    html.motion-intro .auth-card > :nth-child(2) { animation-delay: 0.41s; }
    html.motion-intro .auth-card > :nth-child(3) { animation-delay: 0.47s; }
    html.motion-intro .auth-card > :nth-child(4) { animation-delay: 0.53s; }
    html.motion-intro .auth-card > :nth-child(5) { animation-delay: 0.59s; }
    html.motion-intro .auth-card > :nth-child(6) { animation-delay: 0.65s; }
    html.motion-intro .auth-card > :nth-child(n + 7) { animation-delay: 0.71s; }

    @keyframes motion-drop {
        from { opacity: 0; transform: translateY(-100%); }
        to { opacity: 1; transform: none; }
    }

    @keyframes motion-slide-in {
        from { opacity: 0; transform: translateX(-40px); }
        to { opacity: 1; transform: none; }
    }

    @keyframes motion-badge-in {
        from { opacity: 0; transform: rotate(-180deg) scale(0.5); }
        to { opacity: 1; transform: none; }
    }

    @keyframes motion-wipe {
        from { clip-path: inset(0 100% 0 0); }
        to { clip-path: inset(0 0 0 0); }
    }

    @keyframes motion-fade-down {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: none; }
    }

    @keyframes motion-fade-up {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: none; }
    }

    @keyframes motion-rise {
        from { opacity: 0; transform: translateY(24px) scale(0.98); }
        to { opacity: 1; transform: none; }
    }

    @keyframes motion-hero-drift {
        from { background-position: center 45%; }
        to { background-position: center 30%; }
    }

    /* ---------- Logo hover: compass spins, wordmark shines ---------- */
    html.motion-on .motion-logo-host {
        cursor: pointer;
    }

    html.motion-on .motion-logo-host:hover img[src*="careerpath-badge"] {
        animation: motion-compass 1s cubic-bezier(0.3, 1.35, 0.5, 1);
    }

    .motion-logo-text {
        position: relative;
        display: inline-block;
        flex-shrink: 0;
        line-height: 0;
    }

    .motion-logo-text::after {
        content: '';
        position: absolute;
        top: var(--logo-top, 0);
        right: var(--logo-right, 0);
        bottom: var(--logo-bottom, 0);
        left: var(--logo-left, 0);
        pointer-events: none;
        opacity: 0;
        background: linear-gradient(110deg, transparent 35%, rgba(255, 255, 255, 0.9) 50%, transparent 65%);
        background-size: 250% 100%;
        -webkit-mask: var(--logo-mask) center / 100% 100% no-repeat;
        mask: var(--logo-mask) center / 100% 100% no-repeat;
    }

    html.motion-on .motion-logo-text img {
        transition: transform 0.3s ease, filter 0.3s ease;
    }

    html.motion-on .motion-logo-host:hover .motion-logo-text img {
        transform: translateY(-2px);
        filter: drop-shadow(0 4px 8px rgba(201, 168, 76, 0.35));
    }

    html.motion-on .motion-logo-host:hover .motion-logo-text::after {
        animation: motion-shine 0.9s ease;
    }

    @keyframes motion-compass {
        from { transform: rotate(0); }
        to { transform: rotate(360deg); }
    }

    @keyframes motion-shine {
        from { opacity: 1; background-position: 150% 0; }
        to { opacity: 1; background-position: -50% 0; }
    }

    /* ---------- In-page menu links (e.g. Features, How It Works) ---------- */
    /* The frosted header is costly to redraw on every frame of a long glide. */
    html.motion-gliding .site-header {
        -webkit-backdrop-filter: none;
        backdrop-filter: none;
    }

    html.motion-on .nav-links a.motion-current {
        color: var(--primary, #1a3a5c);
    }

    html.motion-on .nav-links a.motion-current::after {
        width: 100%;
    }

    /* ---------- Respect "reduce motion" and printing ---------- */
    @media (prefers-reduced-motion: reduce) {
        @view-transition {
            navigation: none;
        }
    }

    @media print {
        html.motion-pending main,
        html.motion-on [data-reveal] {
            opacity: 1 !important;
            transform: none !important;
        }

        .motion-progress {
            display: none !important;
        }
    }
</style>

<script>
    (function () {
        var root = document.documentElement;

        if (! root.classList.contains('motion-on')) {
            return;
        }

        var revealEnabled = @json($motionReveal);

        function ready(callback) {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', callback);
            } else {
                callback();
            }
        }

        /* ---------- Content reveal ---------- */
        function visibleChildren(element) {
            return Array.prototype.filter.call(element.children, function (child) {
                if (/^(SCRIPT|STYLE|TEMPLATE|LINK|META|NOSCRIPT)$/.test(child.tagName)) {
                    return false;
                }

                var style = getComputedStyle(child);

                return style.display !== 'none'
                    && style.position !== 'fixed'
                    && style.position !== 'absolute'
                    && style.visibility !== 'hidden'
                    && child.offsetHeight > 0;
            });
        }

        function isGroup(element) {
            var style = getComputedStyle(element);

            return style.display === 'grid'
                || style.display === 'inline-grid'
                || (style.display === 'flex' && style.flexWrap === 'wrap');
        }

        function containsFixed(element) {
            var all = element.getElementsByTagName('*');

            for (var i = 0; i < all.length && i < 1500; i++) {
                if (getComputedStyle(all[i]).position === 'fixed') {
                    return true;
                }
            }

            return false;
        }

        function collectItems(container) {
            var items = [];

            function add(element) {
                if (items.length >= 80) {
                    return;
                }

                /* Leave anything that already animates itself alone. */
                if (getComputedStyle(element).animationName !== 'none') {
                    return;
                }

                items.push(element);
            }

            function walk(element, depth) {
                var children = visibleChildren(element);

                if (children.length === 1 && depth < 5 && children[0].children.length) {
                    walk(children[0], depth + 1);

                    return;
                }

                /*
                 * A page wrapper that holds most of the content (with a
                 * footer or banner beside it) is opened up, so its cards
                 * arrive one by one instead of as a single block.
                 */
                if (depth < 5 && children.length <= 4) {
                    var total = 0;
                    var largest = null;

                    children.forEach(function (child) {
                        total += child.offsetHeight;

                        if (! largest || child.offsetHeight > largest.offsetHeight) {
                            largest = child;
                        }
                    });

                    if (largest && total > 0 && largest.offsetHeight / total >= 0.75
                        && ! isGroup(largest) && visibleChildren(largest).length > 1) {
                        children.forEach(function (child) {
                            if (child === largest) {
                                walk(child, depth + 1);
                            } else {
                                add(child);
                            }
                        });

                        return;
                    }
                }

                children.forEach(function (child) {
                    var group = isGroup(child) ? visibleChildren(child) : [];

                    if (group.length >= 2 && group.length <= 24) {
                        group.forEach(add);
                    } else {
                        add(child);
                    }
                });
            }

            walk(container, 0);

            return items;
        }

        function reveal(element, delay) {
            element.style.setProperty('--reveal-delay', delay + 'ms');
            element.classList.add('is-revealed');

            /* Hand the element back to its own styles once it has arrived. */
            setTimeout(function () {
                element.removeAttribute('data-reveal');
                element.classList.remove('is-revealed');
                element.style.removeProperty('--reveal-delay');
            }, delay + 650);
        }

        function setupReveal() {
            var container = document.querySelector('main');

            if (! container) {
                root.classList.remove('motion-pending');

                return;
            }

            var items = collectItems(container);
            var viewportHeight = window.innerHeight;
            var initial = [];
            var later = [];

            items.forEach(function (item) {
                var position = getComputedStyle(item).position;

                item.setAttribute(
                    'data-reveal',
                    position === 'sticky' || containsFixed(item) ? 'fade' : 'up'
                );

                if (item.getBoundingClientRect().top < viewportHeight) {
                    initial.push(item);
                } else {
                    later.push(item);
                }
            });

            root.classList.remove('motion-pending');

            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    initial.forEach(function (item, index) {
                        reveal(item, Math.min(index * 70, 420));
                    });
                });
            });

            if (! later.length) {
                return;
            }

            if (! ('IntersectionObserver' in window)) {
                later.forEach(function (item) {
                    reveal(item, 0);
                });

                return;
            }

            var waiting = later.slice();

            var observer = new IntersectionObserver(function (entries) {
                var order = 0;

                entries.forEach(function (entry) {
                    if (! entry.isIntersecting) {
                        return;
                    }

                    observer.unobserve(entry.target);
                    waiting.splice(waiting.indexOf(entry.target), 1);
                    reveal(entry.target, Math.min(order * 70, 280));
                    order++;
                });
            }, {
                threshold: 0,
            });

            later.forEach(function (item) {
                observer.observe(item);
            });

            /*
             * At the very bottom of the page (or of a scrolling content
             * area, as on admin pages), show anything still waiting.
             */
            document.addEventListener('scroll', function revealRest(event) {
                if (! waiting.length) {
                    document.removeEventListener('scroll', revealRest, true);

                    return;
                }

                var scroller = event.target === document
                    ? document.documentElement
                    : event.target;

                if (scroller.scrollTop + scroller.clientHeight >= scroller.scrollHeight - 4) {
                    waiting.splice(0).forEach(function (item) {
                        observer.unobserve(item);
                        reveal(item, 0);
                    });
                }
            }, { passive: true, capture: true });
        }

        /* ---------- Text links and clickable cards ---------- */
        function decorateLinks() {
            document.querySelectorAll('main a[href], nav a[href], footer a[href], .notif-popup a[href]').forEach(function (link) {
                if (/btn/.test(link.className) || link.querySelector('img')) {
                    return;
                }

                var style = getComputedStyle(link);

                if (style.display.indexOf('inline') === 0) {
                    var hasBackground = style.backgroundColor !== 'rgba(0, 0, 0, 0)'
                        && style.backgroundColor !== 'transparent';

                    if (! hasBackground
                        && style.textDecorationLine.indexOf('underline') === -1
                        && link.textContent.trim().length > 0
                    ) {
                        link.classList.add('motion-link');
                    }

                    return;
                }

                if (link.closest('main')
                    && style.transitionDuration.split(',').every(function (d) { return parseFloat(d) === 0; })
                    && link.offsetHeight > 40
                ) {
                    link.classList.add('motion-card');
                }
            });
        }

        /* ---------- Content that appears without a page load ---------- */
        /*
         * Tabs, side menus (e.g. BIICF Explorer), opened panels, extra
         * form fields and chat replies are swapped in by JavaScript, so
         * they never "load". Watch for them and ease them in.
         */
        var OPEN_CLASSES = ['active', 'show', 'open', 'is-open', 'is-active', 'visible', 'expanded'];
        var SKIP_TAGS = /^(SCRIPT|STYLE|TEMPLATE|BUTTON|A|INPUT|SELECT|TEXTAREA|LABEL|OPTION|SPAN|I|SVG|PATH|IMG|BR)$/;
        var lastTypedAt = 0;
        var watching = false;

        /* Typing in a search or text box filters lists live; don't animate that. */
        document.addEventListener('input', function (event) {
            var field = event.target;

            if (field.tagName === 'TEXTAREA'
                || (field.tagName === 'INPUT' && /^(text|search|email|tel|url|number|password)$/i.test(field.type || 'text'))) {
                lastTypedAt = Date.now();
            }
        }, true);

        function canAnimate(element) {
            if (element.nodeType !== 1 || SKIP_TAGS.test(element.tagName.toUpperCase())) {
                return false;
            }

            if (element.closest('[data-reveal], .motion-progress')) {
                return false;
            }

            var style = getComputedStyle(element);

            return style.display !== 'none'
                && style.visibility !== 'hidden'
                && style.animationName === 'none'
                && element.offsetHeight >= 24;
        }

        function appear(element, delay, gentle) {
            var tableish = /^(TR|TD|TH|TBODY|THEAD)$/.test(element.tagName);
            var still = gentle || tableish || containsFixed(element);

            element.animate(
                still
                    ? [{ opacity: 0 }, { opacity: 1 }]
                    : [{ opacity: 0, transform: 'translateY(10px)' }, { opacity: 1, transform: 'none' }],
                {
                    duration: 380,
                    delay: delay,
                    easing: 'cubic-bezier(0.22, 0.61, 0.36, 1)',
                    fill: 'backwards',
                }
            );
        }

        function staggerInside(element) {
            var groups = element.querySelectorAll(':scope > *, :scope > * > *, :scope > * > * > *');

            for (var i = 0; i < groups.length; i++) {
                if (! isGroup(groups[i])) {
                    continue;
                }

                var children = visibleChildren(groups[i]).slice(0, 18);

                if (children.length >= 2) {
                    children.forEach(function (child, index) {
                        appear(child, 60 + index * 40, true);
                    });

                    return;
                }
            }
        }

        function watchForNewContent(container) {
            var queue = [];
            var scheduled = false;
            var knownHidden = new WeakSet();

            function isHiddenNow(element) {
                return element.hasAttribute('hidden') || getComputedStyle(element).display === 'none';
            }

            function flush() {
                scheduled = false;

                if (Date.now() - lastTypedAt < 400) {
                    queue = [];

                    return;
                }

                var unique = queue.filter(function (element, index) {
                    return queue.indexOf(element) === index;
                });

                queue = [];

                /* Animate the outermost element only. */
                var outermost = unique.filter(function (element) {
                    return ! unique.some(function (other) {
                        return other !== element && other.contains(element);
                    });
                });

                outermost.filter(canAnimate).slice(0, 12).forEach(function (element, index) {
                    appear(element, Math.min(index * 50, 250), false);

                    if (element.offsetHeight > 160) {
                        staggerInside(element);
                    }
                });
            }

            var observer = new MutationObserver(function (mutations) {
                if (! watching) {
                    return;
                }

                mutations.forEach(function (mutation) {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach(function (node) {
                            if (node.nodeType === 1) {
                                queue.push(node);
                            }
                        });

                        return;
                    }

                    var element = mutation.target;
                    var before = mutation.oldValue || '';

                    if (mutation.attributeName === 'style' || mutation.attributeName === 'hidden') {
                        var wasHidden = knownHidden.has(element)
                            || (mutation.attributeName === 'style' && /display\s*:\s*none/.test(before))
                            || (mutation.attributeName === 'hidden' && mutation.oldValue !== null);

                        if (isHiddenNow(element)) {
                            knownHidden.add(element);
                        } else {
                            knownHidden.delete(element);

                            if (wasHidden) {
                                queue.push(element);
                            }
                        }
                    } else if (mutation.attributeName === 'class') {
                        var had = before.split(/\s+/);
                        var gained = OPEN_CLASSES.some(function (name) {
                            return element.classList.contains(name) && had.indexOf(name) === -1;
                        });

                        if (gained) {
                            queue.push(element);
                        }
                    }
                });

                if (queue.length && ! scheduled) {
                    scheduled = true;
                    requestAnimationFrame(function () {
                        requestAnimationFrame(flush);
                    });
                }
            });

            observer.observe(container, {
                subtree: true,
                childList: true,
                attributes: true,
                attributeFilter: ['style', 'hidden', 'class'],
                attributeOldValue: true,
            });

            /* Start after the page has settled, so loading itself isn't animated twice. */
            function start() {
                setTimeout(function () {
                    watching = true;
                }, 900);
            }

            if (document.readyState === 'complete') {
                start();
            } else {
                window.addEventListener('load', start);
            }
        }

        /* ---------- Links to a section on the same page ---------- */
        /*
         * e.g. the homepage menu (Features, How It Works, BIICF, About):
         * glide to the section, stop just below the fixed header, replay
         * the section's entrance, and keep the matching menu item marked.
         */
        function headerOffset() {
            var header = document.querySelector('.site-header, .site-nav, .auth-header');

            if (! header) {
                return 16;
            }

            var position = getComputedStyle(header).position;

            return position === 'fixed' || position === 'sticky'
                ? header.getBoundingClientRect().height + 16
                : 16;
        }

        /*
         * The parts of a section that play its entrance: the heading
         * first, then up to two rows/grids of cards.
         */
        function sectionParts(section) {
            var parts = [];
            var heading = section.querySelectorAll('.section-header > *, :scope > .container > h2, :scope > .container > p, :scope > h2');

            Array.prototype.slice.call(heading, 0, 4).forEach(function (element, index) {
                parts.push({ element: element, delay: index * 80, distance: 14 });
            });

            var all = section.querySelectorAll('*');
            var groupsUsed = 0;

            for (var i = 0; i < all.length && groupsUsed < 2; i++) {
                if (! isGroup(all[i])) {
                    continue;
                }

                var cards = visibleChildren(all[i]).slice(0, 12);

                if (cards.length < 2) {
                    continue;
                }

                cards.forEach(function (card, index) {
                    parts.push({ element: card, delay: 180 + index * 90, distance: 18 });
                });

                groupsUsed++;
            }

            return parts;
        }

        function hideParts(parts) {
            parts.forEach(function (part) {
                part.element.style.opacity = '0';
                part.element.style.transform = 'translateY(' + part.distance + 'px)';
            });
        }

        function playParts(parts, wasHidden) {
            parts.forEach(function (part) {
                /*
                 * Content that was already on screen only settles into
                 * place; it never blinks out and back in.
                 */
                var from = wasHidden
                    ? { opacity: 0, transform: 'translateY(' + part.distance + 'px)' }
                    : { opacity: 1, transform: 'translateY(6px)' };

                part.element.animate([from, { opacity: 1, transform: 'none' }], {
                    duration: wasHidden ? 550 : 400,
                    delay: wasHidden ? part.delay : part.delay / 2,
                    easing: 'cubic-bezier(0.22, 0.61, 0.36, 1)',
                    fill: 'backwards',
                });

                part.element.style.opacity = '';
                part.element.style.transform = '';
            });
        }

        var menuLinks = [];
        var visible = {};
        var currentId = null;
        var gliding = null;
        var lockedByClick = false;

        ['wheel', 'touchmove', 'keydown'].forEach(function (type) {
            window.addEventListener(type, function () {
                lockedByClick = false;
            }, { passive: true });
        });

        /* While gliding, keep the clicked item marked instead of every section passed. */
        function markCurrent() {
            var id = gliding || currentId;

            menuLinks.forEach(function (link) {
                link.classList.toggle('motion-current', link.getAttribute('href') === '#' + id);
            });
        }

        function setupSectionLinks() {
            document.addEventListener('click', function (event) {
                if (event.defaultPrevented || event.button !== 0
                    || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                    return;
                }

                var link = event.target.closest && event.target.closest('a[href^="#"]');
                var id = link ? decodeURIComponent(link.getAttribute('href').slice(1)) : '';
                var section = id ? document.getElementById(id) : null;

                if (! section || section.offsetHeight === 0) {
                    return;
                }

                event.preventDefault();

                var top = Math.max(section.getBoundingClientRect().top + window.scrollY - headerOffset(), 0);
                var box = section.getBoundingClientRect();
                var onScreen = box.top < window.innerHeight && box.bottom > 0;
                var parts = sectionParts(section);

                /* Let the page's own scroll reveal know this section is handled. */
                section.querySelectorAll('.reveal, .reveal-stagger').forEach(function (element) {
                    element.classList.add('visible');
                });

                /* Off-screen content can wait hidden, so it never blinks. */
                if (! onScreen) {
                    hideParts(parts);
                }

                if (history.pushState) {
                    history.pushState(null, '', '#' + id);
                }

                gliding = id;
                markCurrent();
                root.classList.add('motion-gliding');

                var finished = false;
                var settleTimer;
                var safetyTimer;

                function arrive() {
                    if (finished) {
                        return;
                    }

                    finished = true;
                    clearTimeout(settleTimer);
                    clearTimeout(safetyTimer);
                    window.removeEventListener('scroll', waitForStop);
                    root.classList.remove('motion-gliding');
                    gliding = null;

                    /*
                     * Keep the clicked item marked (a short last section
                     * can't reach the top of the screen) until the
                     * visitor scrolls on their own.
                     */
                    currentId = id;
                    lockedByClick = true;
                    markCurrent();
                    playParts(parts, ! onScreen);
                }

                /* Arrive once the page has actually stopped moving. */
                function waitForStop() {
                    clearTimeout(settleTimer);
                    settleTimer = setTimeout(arrive, 120);
                }

                if (Math.abs(window.scrollY - top) < 4) {
                    arrive();

                    return;
                }

                window.addEventListener('scroll', waitForStop, { passive: true });
                waitForStop();
                safetyTimer = setTimeout(arrive, 2500);
                window.scrollTo({ top: top, behavior: 'smooth' });
            });

            /* Mark the menu item for the section currently on screen. */
            menuLinks = Array.prototype.filter.call(
                document.querySelectorAll('.nav-links a[href^="#"]'),
                function (link) {
                    return link.getAttribute('href').length > 1
                        && document.getElementById(link.getAttribute('href').slice(1));
                }
            );

            if (! menuLinks.length || ! ('IntersectionObserver' in window)) {
                return;
            }

            function markCurrentFromScroll() {
                if (gliding || lockedByClick) {
                    return;
                }

                var best = null;

                menuLinks.forEach(function (link) {
                    var id = link.getAttribute('href').slice(1);

                    if (visible[id] && (! best || visible[id] > visible[best])) {
                        best = id;
                    }
                });

                currentId = best;
                markCurrent();
            }

            var spy = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    visible[entry.target.id] = entry.isIntersecting ? entry.intersectionRatio : 0;
                });

                markCurrentFromScroll();
            }, {
                rootMargin: '-35% 0px -45% 0px',
                threshold: [0, 0.25, 0.5, 0.75, 1],
            });

            menuLinks.forEach(function (link) {
                spy.observe(document.getElementById(link.getAttribute('href').slice(1)));
            });
        }

        /*
         * Prepare every picture in the background once the page is idle,
         * so the first long glide down the page doesn't hitch while
         * images further down are decoded.
         */
        function warmUpImages() {
            var images = Array.prototype.slice.call(document.images);

            function next() {
                var image = images.shift();

                if (! image) {
                    return;
                }

                var done = function () {
                    (window.requestIdleCallback || setTimeout)(next);
                };

                if (image.decode && image.complete && image.naturalWidth) {
                    image.decode().then(done, done);
                } else {
                    done();
                }
            }

            (window.requestIdleCallback || setTimeout)(next);
        }

        window.addEventListener('load', function () {
            setTimeout(warmUpImages, 1200);
        });

        /* ---------- Logo: wrap the wordmark so it can shine ---------- */
        function decorateLogos() {
            document.querySelectorAll('img[src*="careerpath-logo"], img[src*="careerpath-badge"]').forEach(function (image) {
                var host = image.closest('a') || image.parentElement;

                if (host) {
                    host.classList.add('motion-logo-host');
                }

                if (image.src.indexOf('careerpath-logo') === -1
                    || image.parentElement.classList.contains('motion-logo-text')) {
                    return;
                }

                var wrapper = document.createElement('span');
                var style = getComputedStyle(image);

                wrapper.className = 'motion-logo-text';
                wrapper.style.setProperty('--logo-mask', 'url("' + (image.currentSrc || image.src) + '")');

                /* Line the shine up with the picture itself, inside any padding. */
                wrapper.style.setProperty('--logo-top', style.paddingTop);
                wrapper.style.setProperty('--logo-right', style.paddingRight);
                wrapper.style.setProperty('--logo-bottom', style.paddingBottom);
                wrapper.style.setProperty('--logo-left', style.paddingLeft);

                image.parentNode.insertBefore(wrapper, image);
                wrapper.appendChild(image);
            });
        }

        /* ---------- Loading bar while the next page opens ---------- */
        var bar;
        var barTimer;

        function startProgress() {
            if (! bar) {
                bar = document.createElement('div');
                bar.className = 'motion-progress';
                document.body.appendChild(bar);
            }

            bar.classList.remove('is-done', 'is-active');
            void bar.offsetWidth;
            bar.classList.add('is-active');

            /* Downloads never leave the page, so finish the bar anyway. */
            clearTimeout(barTimer);
            barTimer = setTimeout(stopProgress, 6000);
        }

        function stopProgress() {
            if (! bar) {
                return;
            }

            clearTimeout(barTimer);
            bar.classList.remove('is-active');
            bar.classList.add('is-done');
        }

        document.addEventListener('click', function (event) {
            if (event.defaultPrevented || event.button !== 0
                || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            var link = event.target.closest && event.target.closest('a[href]');

            if (! link || link.target === '_blank' || link.hasAttribute('download')) {
                return;
            }

            var href = link.getAttribute('href');

            if (! href || href.charAt(0) === '#' || /^(javascript|mailto|tel):/i.test(href)) {
                return;
            }

            if (link.origin !== window.location.origin) {
                return;
            }

            if (link.pathname === window.location.pathname && link.search === window.location.search && link.hash) {
                return;
            }

            startProgress();
        });

        document.addEventListener('submit', function (event) {
            if (! event.defaultPrevented && event.target.getAttribute('target') !== '_blank') {
                startProgress();
            }
        });

        /* Returning with the Back button restores the old page; hide the bar. */
        window.addEventListener('pageshow', stopProgress);

        ready(function () {
            decorateLogos();
            decorateLinks();
            setupSectionLinks();

            if (revealEnabled) {
                setupReveal();
            }

            var content = document.querySelector('main');

            if (content && 'MutationObserver' in window && Element.prototype.animate) {
                watchForNewContent(content);
            }
        });
    })();
</script>