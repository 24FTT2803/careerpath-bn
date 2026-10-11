<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @hasSection('title')
            @yield('title') — CareerPath BN
        @else
            CareerPath BN
        @endif
    </title>
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=2" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=2">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts (same as the rest of the site) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    {{--
        Layout for the Terms of Service and Privacy Policy pages.
        Navy and gold like the rest of CareerPath BN: a moving
        banner at the top, a contents list that follows you down
        the page, and the policy itself in a white card.
    --}}
    <style>
        :root {
            --navy-900: #0d1f33;
            --navy-700: #1a3a5c;
            --navy-500: #2a5a8c;
            --gold: #c9a84c;
            --gold-light: #e8d4a0;
            --gold-wash: #faf5e6;
            --paper: #f4f2ec;
            --ink: #1c2633;
            --muted: #5b6675;
            --line: #e6e2d6;
            --font-display: 'Playfair Display', Georgia, serif;
            --font-body: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
            --header-height: 68px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            background: var(--paper);
            color: var(--ink);
            font-family: var(--font-body);
            font-size: 15.5px;
            line-height: 1.75;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        a { color: var(--navy-500); text-decoration: none; }
        a:hover { color: var(--navy-700); text-decoration: underline; }

        /* ---------- Header ---------- */
        .legal-header {
            position: sticky;
            top: 0;
            z-index: 50;
            height: var(--header-height);
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--line);
        }

        .legal-header-inner {
            max-width: 1160px;
            height: 100%;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .legal-logo img {
            display: block;
            height: 40px;
            width: auto;
        }

        .legal-progress {
            position: absolute;
            left: 0;
            bottom: -1px;
            height: 3px;
            width: 100%;
            transform-origin: 0 50%;
            transform: scaleX(0);
            background: linear-gradient(90deg, var(--gold), var(--gold-light));
        }

        /* "Back to ..." (where you came from) */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 999px;
            border: 1px solid var(--navy-700);
            color: var(--navy-700);
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .back-link:hover {
            background: var(--navy-700);
            color: #fff;
            text-decoration: none;
            transform: translateX(-2px);
        }

        /* ---------- Banner ---------- */
        .legal-hero {
            position: relative;
            overflow: hidden;
            padding: 64px 24px 112px;
            color: #fff;
            background: linear-gradient(135deg, var(--navy-700) 0%, var(--navy-500) 50%, var(--navy-700) 100%);
            background-size: 220% 220%;
        }

        .legal-hero-circles span {
            position: absolute;
            border-radius: 50%;
            background: rgba(201, 168, 76, 0.1);
            pointer-events: none;
        }

        .legal-hero-circles span:nth-child(1) { width: 380px; height: 380px; top: -140px; right: -80px; }
        .legal-hero-circles span:nth-child(2) { width: 200px; height: 200px; bottom: -60px; left: 6%; background: rgba(255, 255, 255, 0.05); }
        .legal-hero-circles span:nth-child(3) { width: 90px; height: 90px; top: 30%; left: 42%; background: rgba(201, 168, 76, 0.14); }

        .legal-hero-inner {
            position: relative;
            max-width: 1160px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .legal-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 999px;
            background: rgba(201, 168, 76, 0.16);
            border: 1px solid rgba(232, 212, 160, 0.35);
            color: var(--gold-light);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .legal-hero h1 {
            margin: 18px 0 12px;
            font-family: var(--font-display);
            font-size: clamp(32px, 5vw, 48px);
            line-height: 1.15;
            font-weight: 700;
        }

        .legal-lead {
            max-width: 640px;
            color: rgba(255, 255, 255, 0.82);
            font-size: 16.5px;
        }

        .legal-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 24px;
        }

        .legal-meta span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 14px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.14);
            font-size: 13px;
            color: rgba(255, 255, 255, 0.88);
        }

        .legal-meta i { color: var(--gold-light); }
        .legal-readtime { font-weight: inherit; }

        /* Switch between the two documents */
        .legal-switch {
            display: inline-flex;
            gap: 4px;
            margin-top: 28px;
            padding: 4px;
            border-radius: 999px;
            background: rgba(13, 31, 51, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .legal-switch a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 999px;
            color: rgba(255, 255, 255, 0.78);
            font-size: 14px;
            font-weight: 600;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .legal-switch a:hover {
            color: #fff;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.08);
        }

        .legal-switch a.is-current {
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            color: var(--navy-900);
        }

        /* ---------- Body ---------- */
        .legal-main {
            position: relative;
            z-index: 1;
            max-width: 1160px;
            margin: -72px auto 0;
            padding: 0 24px 72px;
        }

        .legal-layout {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            gap: 28px;
            align-items: start;
        }

        /* Contents list */
        .legal-toc {
            position: sticky;
            top: calc(var(--header-height) + 20px);
            max-height: calc(100vh - var(--header-height) - 40px);
            overflow: auto;
            padding: 20px 14px 18px;
            border-radius: 16px;
            background: #fff;
            border: 1px solid var(--line);
            box-shadow: 0 18px 40px -28px rgba(13, 31, 51, 0.45);
        }

        .legal-toc summary {
            list-style: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 8px 10px;
            color: var(--muted);
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            cursor: default;
        }

        .legal-toc summary::-webkit-details-marker { display: none; }
        .legal-toc summary i { display: none; }

        .legal-toc ol { list-style: none; }

        .legal-toc a {
            display: flex;
            gap: 10px;
            padding: 7px 8px;
            border-radius: 8px;
            color: var(--muted);
            font-size: 13.5px;
            line-height: 1.4;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .legal-toc a span {
            flex: 0 0 auto;
            min-width: 20px;
            color: var(--gold);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .legal-toc a:hover {
            background: var(--gold-wash);
            color: var(--navy-900);
            text-decoration: none;
        }

        .legal-toc a.is-active {
            background: var(--navy-700);
            color: #fff;
        }

        .legal-toc a.is-active span { color: var(--gold-light); }

        /* The document */
        .legal-card {
            min-width: 0;
            padding: 48px 56px;
            border-radius: 20px;
            background: #fff;
            border: 1px solid var(--line);
            box-shadow: 0 30px 60px -40px rgba(13, 31, 51, 0.5);
        }

        .legal-content { counter-reset: legal-section; }

        .legal-content > section + section {
            margin-top: 8px;
            padding-top: 30px;
            border-top: 1px solid var(--line);
        }

        .legal-content h2 {
            counter-increment: legal-section;
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 14px;
            scroll-margin-top: calc(var(--header-height) + 24px);
            font-family: var(--font-display);
            font-size: 23px;
            font-weight: 700;
            line-height: 1.3;
            color: var(--navy-900);
        }

        .legal-content h2::before {
            content: counter(legal-section, decimal-leading-zero);
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 11px;
            background: linear-gradient(135deg, var(--navy-700), var(--navy-500));
            color: var(--gold-light);
            font-family: var(--font-body);
            font-size: 13px;
            font-weight: 700;
        }

        .legal-content h3 {
            margin: 22px 0 8px;
            font-size: 15.5px;
            font-weight: 700;
            color: var(--navy-700);
        }

        .legal-content p { margin-bottom: 14px; }
        .legal-content strong { color: var(--navy-900); }

        .legal-content ul {
            list-style: none;
            margin: 6px 0 18px;
        }

        .legal-content ul li {
            position: relative;
            padding-left: 22px;
            margin-bottom: 8px;
        }

        .legal-content ul li::before {
            content: '';
            position: absolute;
            left: 3px;
            top: 0.72em;
            width: 7px;
            height: 7px;
            border-radius: 2px;
            background: var(--gold);
            transform: rotate(45deg);
        }

        /* Key points at the top */
        .legal-summary {
            margin-bottom: 36px;
            padding: 24px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--gold-wash), #fff);
            border: 1px solid rgba(201, 168, 76, 0.4);
        }

        .legal-summary-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            font-family: var(--font-display);
            font-size: 19px;
            font-weight: 700;
            color: var(--navy-900);
        }

        .legal-summary-title i { color: var(--gold); }

        .legal-points {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .legal-point {
            display: flex;
            gap: 12px;
            padding: 14px;
            border-radius: 12px;
            background: #fff;
            border: 1px solid var(--line);
            font-size: 14px;
            line-height: 1.55;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .legal-point:hover {
            transform: translateY(-2px);
            border-color: rgba(201, 168, 76, 0.55);
            box-shadow: 0 12px 24px -18px rgba(13, 31, 51, 0.5);
        }

        .legal-point i {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: var(--navy-700);
            color: var(--gold-light);
            font-size: 14px;
        }

        .legal-point strong {
            display: block;
            margin-bottom: 2px;
            color: var(--navy-900);
        }

        /* Grid of labelled items (what we collect, who sees it...) */
        .legal-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin: 8px 0 20px;
        }

        .legal-item {
            padding: 16px 18px;
            border-radius: 12px;
            background: #fbfaf6;
            border: 1px solid var(--line);
            border-left: 3px solid var(--gold);
            font-size: 14px;
            line-height: 1.6;
        }

        .legal-item strong {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
            font-size: 14.5px;
        }

        .legal-item strong i { color: var(--gold); width: 16px; text-align: center; }

        /* Notes */
        .callout {
            display: flex;
            gap: 14px;
            margin: 6px 0 18px;
            padding: 16px 18px;
            border-radius: 12px;
            background: #eef3f9;
            border: 1px solid #d6e2ef;
            font-size: 14.5px;
        }

        .callout > i {
            margin-top: 4px;
            color: var(--navy-500);
        }

        .callout p:last-child { margin-bottom: 0; }

        .callout--gold {
            background: var(--gold-wash);
            border-color: rgba(201, 168, 76, 0.45);
        }

        .callout--gold > i { color: var(--gold); }

        /* Contact card */
        .legal-contact {
            margin-top: 10px;
            padding: 24px;
            border-radius: 16px;
            color: rgba(255, 255, 255, 0.86);
            background: linear-gradient(135deg, var(--navy-900), var(--navy-700));
        }

        .legal-contact strong { color: #fff; }

        .legal-contact-rows {
            display: grid;
            gap: 10px;
            margin-top: 14px;
        }

        .legal-contact-row {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            font-size: 14.5px;
            line-height: 1.55;
        }

        .legal-contact-row > i {
            margin-top: 3px;
            width: 18px;
            text-align: center;
            color: var(--gold-light);
        }

        .legal-contact a { color: var(--gold-light); }

        .legal-footer-nav {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-top: 36px;
            padding-top: 24px;
            border-top: 1px solid var(--line);
            color: var(--muted);
            font-size: 13.5px;
        }

        /* Back to top */
        .legal-top {
            position: fixed;
            right: 22px;
            bottom: 22px;
            z-index: 40;
            width: 46px;
            height: 46px;
            border: 0;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            color: var(--navy-900);
            box-shadow: 0 12px 26px -10px rgba(13, 31, 51, 0.55);
            cursor: pointer;
            opacity: 0;
            transform: translateY(12px);
            pointer-events: none;
            transition: opacity 0.25s ease, transform 0.25s ease;
        }

        .legal-top.is-shown {
            opacity: 1;
            transform: none;
            pointer-events: auto;
        }

        .legal-top:hover { transform: translateY(-3px); }

        .legal-page-footer {
            padding: 22px 24px 30px;
            text-align: center;
            color: var(--muted);
            font-size: 13px;
        }

        /* ---------- Motion ---------- */
        @media (prefers-reduced-motion: no-preference) {
            .legal-hero { animation: legal-bg-shift 18s ease-in-out infinite; }
            .legal-hero-circles span { animation: legal-float 7s ease-in-out infinite; }
            .legal-hero-circles span:nth-child(2) { animation-delay: 2s; }
            .legal-hero-circles span:nth-child(3) { animation-delay: 4s; animation-duration: 5s; }

            @keyframes legal-bg-shift {
                0%, 100% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
            }

            @keyframes legal-float {
                0%, 100% { transform: translate(0, 0) scale(1); }
                50% { transform: translate(18px, -24px) scale(1.08); }
            }
        }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
        }

        /* ---------- Smaller screens ---------- */
        @media (max-width: 960px) {
            .legal-layout { grid-template-columns: minmax(0, 1fr); gap: 16px; }

            /* The contents list folds into a tap-to-open panel */
            .legal-toc {
                position: static;
                max-height: none;
                padding: 14px;
            }

            .legal-toc summary {
                padding: 2px 6px;
                cursor: pointer;
                color: var(--navy-700);
                font-size: 12.5px;
            }

            .legal-toc summary i {
                display: inline-block;
                transition: transform 0.2s ease;
            }

            .legal-toc[open] summary { padding-bottom: 10px; }
            .legal-toc[open] summary i { transform: rotate(180deg); }

            .legal-card { padding: 36px 32px; }
        }

        @media (max-width: 640px) {
            :root { --header-height: 60px; }

            body { font-size: 15px; }

            .legal-header-inner { padding: 0 14px; }
            .legal-logo img { height: 30px; }
            .back-link { padding: 7px 13px; font-size: 13px; }

            .legal-hero { padding: 40px 16px 92px; }
            .legal-hero-inner { padding: 0; }
            .legal-lead { font-size: 15px; }
            .legal-meta span { font-size: 12px; padding: 6px 11px; }

            .legal-switch { display: flex; width: 100%; }
            .legal-switch a { flex: 1 1 0; justify-content: center; padding: 8px 10px; font-size: 13px; }

            .legal-main { padding: 0 12px 56px; }
            .legal-card { padding: 26px 18px; border-radius: 16px; }

            .legal-content h2 { font-size: 20px; gap: 12px; }
            .legal-content h2::before { width: 32px; height: 32px; font-size: 12px; border-radius: 9px; }

            .legal-summary { padding: 18px 14px; }
            .legal-points,
            .legal-grid { grid-template-columns: minmax(0, 1fr); }

            .legal-top { right: 14px; bottom: 14px; width: 42px; height: 42px; }
        }

        @media (max-width: 380px) {
            .back-link { font-size: 12px; padding: 6px 10px; }
            .legal-logo img { height: 26px; }
        }

        /* ---------- Printing ---------- */
        @media print {
            .legal-header,
            .legal-toc,
            .legal-switch,
            .legal-top,
            .legal-footer-nav { display: none !important; }

            body { background: #fff; }
            .legal-hero { padding: 0 0 16px; color: var(--ink); background: none; animation: none; }
            .legal-hero-circles { display: none; }
            .legal-lead, .legal-meta span { color: var(--ink); }
            .legal-main { margin: 0; padding: 0; }
            .legal-layout { display: block; }
            .legal-card { padding: 0; border: 0; box-shadow: none; }
        }
    </style>
    @include('partials.motion')
</head>
<body>
    <header class="legal-header">
        <div class="legal-header-inner">
            <a href="{{ url('/') }}" class="legal-logo" aria-label="CareerPath BN home">
                <img
                    src="{{ asset('images/careerpath-logo-v2.png') }}?v={{ filemtime(public_path('images/careerpath-logo-v2.png')) }}"
                    alt="CareerPath BN"
                >
            </a>

            @include('pages._back-link')
        </div>
        <span class="legal-progress" id="legalProgress" aria-hidden="true"></span>
    </header>

    <section class="legal-hero">
        <div class="legal-hero-circles" aria-hidden="true"><span></span><span></span><span></span></div>

        <div class="legal-hero-inner">
            <span class="legal-eyebrow">
                <i class="fas @yield('icon', 'fa-scale-balanced')"></i> @yield('eyebrow', 'Legal')
            </span>

            <h1>@yield('title')</h1>

            <p class="legal-lead">@yield('lead')</p>

            <div class="legal-meta">
                <span><i class="far fa-calendar"></i> Last updated: @yield('updated')</span>
                <span><i class="far fa-clock"></i> <b id="legalReadTime" class="legal-readtime">A few minutes to read</b></span>
                <span><i class="fas fa-user-group"></i> Students, lecturers and administrators</span>
            </div>

            <nav class="legal-switch" aria-label="Legal documents">
                <a href="{{ route('terms') }}" class="{{ request()->routeIs('terms') ? 'is-current' : '' }}">
                    <i class="fas fa-file-contract"></i> Terms of Service
                </a>
                <a href="{{ route('privacy') }}" class="{{ request()->routeIs('privacy') ? 'is-current' : '' }}">
                    <i class="fas fa-shield-halved"></i> Privacy Policy
                </a>
            </nav>
        </div>
    </section>

    <main class="legal-main">
        <div class="legal-layout">
            <details class="legal-toc" id="legalToc" open>
                <summary>On this page <i class="fas fa-chevron-down"></i></summary>
                <ol id="legalTocList"></ol>
            </details>

            <article class="legal-card">
                @hasSection('summary')
                    <div class="legal-summary">
                        <div class="legal-summary-title"><i class="fas fa-bolt"></i> The short version</div>
                        <div class="legal-points">
                            @yield('summary')
                        </div>
                    </div>
                @endif

                <div class="legal-content" id="legalContent">
                    @yield('content')
                </div>
            </article>
        </div>
    </main>

    <footer class="legal-page-footer">
        &copy; {{ date('Y') }} CareerPath BN &middot; School of Information and Communication Technology, Politeknik Brunei
    </footer>

    <button type="button" class="legal-top" id="legalTop" aria-label="Back to top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        (function () {
            var content = document.getElementById('legalContent');
            var list = document.getElementById('legalTocList');
            var toc = document.getElementById('legalToc');
            var headings = content ? content.querySelectorAll('h2') : [];
            var links = [];

            /* Contents list, built from the section headings */
            Array.prototype.forEach.call(headings, function (heading, index) {
                if (! heading.id) {
                    heading.id = 'section-' + (index + 1);
                }

                var number = String(index + 1).padStart(2, '0');
                var item = document.createElement('li');
                var link = document.createElement('a');
                var label = document.createElement('span');

                label.textContent = number;
                link.href = '#' + heading.id;
                link.appendChild(label);
                link.appendChild(document.createTextNode(heading.textContent.trim()));
                item.appendChild(link);
                list.appendChild(item);
                links.push(link);
            });

            /* Closed by default on phones, where it sits above the text */
            var narrow = window.matchMedia('(max-width: 960px)');

            function fitToc() {
                toc.open = ! narrow.matches;
            }

            fitToc();

            if (narrow.addEventListener) {
                narrow.addEventListener('change', fitToc);
            }

            toc.querySelector('summary').addEventListener('click', function (event) {
                if (! narrow.matches) {
                    event.preventDefault();
                }
            });

            links.forEach(function (link) {
                link.addEventListener('click', function () {
                    if (narrow.matches) {
                        toc.open = false;
                    }
                });
            });

            /* Rough reading time */
            var words = content ? content.textContent.trim().split(/\s+/).length : 0;
            var readTime = document.getElementById('legalReadTime');

            if (readTime && words) {
                readTime.textContent = 'About ' + Math.max(1, Math.round(words / 220)) + ' min read';
            }

            /* Reading progress, current section, back-to-top */
            var progress = document.getElementById('legalProgress');
            var topButton = document.getElementById('legalTop');
            var ticking = false;

            function update() {
                ticking = false;

                var scrolled = window.scrollY;
                var height = document.documentElement.scrollHeight - window.innerHeight;

                progress.style.transform = 'scaleX(' + (height > 0 ? Math.min(1, scrolled / height) : 0) + ')';
                topButton.classList.toggle('is-shown', scrolled > 600);

                var current = 0;
                var line = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--header-height'), 10) + 60;

                Array.prototype.forEach.call(headings, function (heading, index) {
                    if (heading.getBoundingClientRect().top <= line) {
                        current = index;
                    }
                });

                if (height > 0 && scrolled >= height - 4) {
                    current = headings.length - 1;
                }

                links.forEach(function (link, index) {
                    link.classList.toggle('is-active', index === current);
                });
            }

            window.addEventListener('scroll', function () {
                if (! ticking) {
                    ticking = true;
                    requestAnimationFrame(update);
                }
            }, { passive: true });

            window.addEventListener('resize', update);
            update();

            topButton.addEventListener('click', function () {
                window.scrollTo({ top: 0 });
            });
        })();
    </script>
</body>
</html>
