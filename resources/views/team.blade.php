<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Our Team — CareerPath BN</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=2" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=2">
    <meta name="description" content="Meet the Politeknik Brunei students behind CareerPath BN.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #1a3a5c;
            --primary-light: #2a5a8c;
            --primary-dark: #0d1f33;
            --accent: #c9a84c;
            --accent-light: #e8d4a0;
            --accent-dark: #a88830;
            --bg: #f4f6f9;
            --card: #ffffff;
            --text: #1a1a2e;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --shadow: 0 4px 24px rgba(26, 58, 92, 0.08);
            --shadow-hover: 0 8px 40px rgba(26, 58, 92, 0.15);
            --radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* ============================================
           HEADER / NAVIGATION (same as welcome page)
           ============================================ */
        .site-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            transition: var(--transition);
        }

        .site-header.scrolled {
            box-shadow: var(--shadow);
        }

        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 32px;
        }

        .nav-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent);
            transition: var(--transition);
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--primary);
        }

        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            font-family: inherit;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(26, 58, 92, 0.3);
        }

        .btn-accent {
            background: var(--accent);
            color: var(--primary-dark);
        }

        .btn-accent:hover {
            background: var(--accent-light);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(201, 168, 76, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        .btn-sm {
            padding: 8px 18px;
            font-size: 13px;
        }

        /* ============================================
           TEAM PAGE BANNER
           ============================================ */
        .team-hero {
            padding: 150px 0 90px;
            background: linear-gradient(135deg, rgba(13, 31, 51, 0.97) 0%, rgba(26, 58, 92, 0.95) 50%, rgba(42, 90, 140, 0.92) 100%);
            position: relative;
            overflow: hidden;
        }

        .team-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 60%;
            height: 200%;
            background: radial-gradient(circle, rgba(201, 168, 76, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .team-hero::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: var(--bg);
            clip-path: ellipse(70% 100% at 50% 100%);
        }

        .team-hero .container {
            position: relative;
            z-index: 1;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 24px;
            transition: var(--transition);
        }

        .back-link:hover {
            color: var(--accent);
        }

        .team-hero .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(201, 168, 76, 0.15);
            border: 1px solid rgba(201, 168, 76, 0.3);
            padding: 6px 16px;
            border-radius: 100px;
            color: var(--accent-light);
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .team-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            font-weight: 700;
            color: white;
            line-height: 1.15;
            margin-bottom: 16px;
        }

        .team-hero h1 span {
            color: var(--accent);
        }

        .team-hero p {
            font-size: 17px;
            color: rgba(255, 255, 255, 0.8);
            max-width: 560px;
            line-height: 1.7;
        }

        /* ============================================
           TEAM LIST (BIICF WG style)
           ============================================ */
        .team-section {
            padding: 40px 0 90px;
        }

        .team-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 48px;
            box-shadow: var(--shadow);
        }

        .team-group + .team-group {
            margin-top: 48px;
            padding-top: 40px;
            border-top: 1px solid var(--border);
        }

        .team-group-title {
            font-size: 12px;
            font-weight: 600;
            color: var(--accent-dark);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-bottom: 24px;
        }

        .team-list {
            display: flex;
            flex-direction: column;
            gap: 28px;
        }

        .team-member {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .team-photo {
            width: 96px;
            height: 120px;
            object-fit: cover;
            object-position: top;
            border-radius: var(--radius);
            border: 2px solid var(--accent);
            background: var(--bg);
            flex-shrink: 0;
            transition: var(--transition);
        }

        .team-member:hover .team-photo {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .team-name {
            font-size: 17px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .team-role {
            font-size: 15px;
            line-height: 1.6;
            color: var(--text-muted);
        }

        /* ============================================
           FOOTER (same as welcome page)
           ============================================ */
        .site-footer {
            background: var(--primary-dark);
            color: white;
            padding: 48px 0 24px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
            margin-bottom: 32px;
        }

        .footer-brand p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
            max-width: 300px;
            margin-top: 12px;
            line-height: 1.7;
        }

        .footer-logos {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .footer-logos .logo-link {
            display: inline-block;
            transition: var(--transition);
        }

        .footer-logos .logo-link:hover {
            transform: translateY(-2px);
            opacity: 0.8;
        }

        .footer-logos .footer-logo {
            height: 40px;
            width: auto;
            object-fit: contain;
            filter: brightness(0) invert(1) opacity(0.8);
            transition: var(--transition);
        }

        .footer-logos .footer-logo:hover {
            filter: brightness(0) invert(1) opacity(1);
        }

        .footer-logos .logo-fallback {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            font-weight: 500;
            transition: var(--transition);
        }

        .footer-logos .logo-fallback:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .footer-logos .logo-fallback i {
            color: var(--accent);
        }

        .footer-col h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 16px;
            color: white;
        }

        .footer-col a {
            display: block;
            color: rgba(255, 255, 255, 0.5);
            text-decoration: none;
            font-size: 13px;
            padding: 4px 0;
            transition: var(--transition);
        }

        .footer-col a:hover {
            color: var(--accent);
        }

        .footer-col a i {
            width: 20px;
            margin-right: 4px;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .footer-bottom p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.3);
            margin: 0;
        }

        .footer-bottom .credit {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.2);
        }

        .footer-col .contact-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 5px 0;
            color: rgba(255, 255, 255, 0.5);
            font-size: 13px;
            line-height: 1.6;
        }

        .footer-col .contact-row i {
            width: 16px;
            margin: 4px 0 0;
            flex-shrink: 0;
            text-align: center;
            color: rgba(255, 255, 255, 0.4);
            transition: var(--transition);
        }

        .footer-col .contact-row > span {
            min-width: 0;
            overflow-wrap: break-word;
        }

        .footer-col .contact-row a {
            display: inline;
            padding: 0;
        }

        .footer-col a.contact-row:hover,
        .footer-col a.contact-row:hover i,
        .footer-col .contact-row a:hover {
            color: var(--accent);
        }

        .footer-col .contact-label {
            display: block;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.35);
        }

        .footer-col .contact-sep {
            margin: 0 4px;
            color: rgba(255, 255, 255, 0.25);
        }

        .footer-bottom-text {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .footer-legal {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
        }

        .footer-legal a {
            color: rgba(255, 255, 255, 0.55);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-legal a:hover {
            color: var(--accent);
        }

        .footer-legal span {
            color: rgba(255, 255, 255, 0.25);
        }

        @media (min-width: 1025px) {
            .footer-grid {
                grid-template-columns: 1.7fr 0.9fr 0.9fr 1.5fr;
            }
        }

        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 1024px) {
            .footer-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .nav-links { display: none; }
            .team-hero h1 { font-size: 34px; }
            .team-card { padding: 32px 24px; }
            .footer-grid { grid-template-columns: 1fr; }
            .footer-bottom { flex-direction: column; text-align: center; }
            .footer-bottom-text { align-items: center; }
        }

        @media (max-width: 600px) {
            .site-header .logo { gap: 8px !important; }
            .site-header .logo img:first-child { height: 42px !important; width: 42px !important; }
            .site-header .logo img + img { height: 38px !important; }
            .nav-actions { gap: 8px; }
            .nav-actions .btn-sm { padding: 8px 14px; font-size: 12px; white-space: nowrap; }
        }

        @media (max-width: 480px) {
            .team-hero { padding: 120px 0 70px; }
            .team-hero h1 { font-size: 28px; }
            .team-member { gap: 16px; }
            .team-photo { width: 80px; height: 100px; }
            .team-role { font-size: 14px; }
            .footer-logos { gap: 14px 18px; }
            .footer-logos .footer-logo { height: 32px; }
        }

        @media (max-width: 380px) {
            .site-header .logo img:first-child { height: 36px !important; width: 36px !important; }
            .site-header .logo img + img { height: 32px !important; }
            .nav-actions .btn-sm { padding: 7px 11px; }
            .nav-actions .btn-sm i { display: none; }
        }

        /* ============================================
           ENTRANCE ANIMATIONS (same as welcome page)
           ============================================ */
        @media (prefers-reduced-motion: no-preference) {
            .reveal-stagger > * {
                opacity: 0;
                transform: translateY(20px);
                transition:
                    opacity 0.7s cubic-bezier(0.2, 0.7, 0.2, 1),
                    transform 0.7s cubic-bezier(0.2, 0.7, 0.2, 1);
            }

            .reveal-stagger.visible > * {
                opacity: 1;
                transform: translateY(0);
            }

            .reveal-stagger.visible > *:nth-child(1) { transition-delay: 0s; }
            .reveal-stagger.visible > *:nth-child(2) { transition-delay: 0.1s; }
            .reveal-stagger.visible > *:nth-child(3) { transition-delay: 0.2s; }
        }

        @media (prefers-reduced-motion: reduce) {
            .reveal-stagger > * {
                opacity: 1 !important;
                transform: none !important;
                transition: none !important;
            }
        }
    </style>
    @include('partials.motion', ['reveal' => false])
    @include('partials.attention')
</head>
<body>

    <!-- ============================================
    HEADER
    ============================================ -->
    <header class="site-header" id="site-header">
        <div class="container">
            <div class="header-inner">
                <a href="{{ url('/') }}" class="logo" style="display: inline-flex; align-items: center; gap: 12px; flex-shrink: 0; text-decoration: none;">
                    <img
                        src="{{ asset('images/careerpath-badge.png') }}?v={{ filemtime(public_path('images/careerpath-badge.png')) }}"
                        alt="CareerPath BN"
                        style="height: 64px; width: 64px; object-fit: contain; display: block; flex-shrink: 0;"
                    >
                    <img
                        src="{{ asset('images/careerpath-logo-v2.png') }}?v={{ filemtime(public_path('images/careerpath-logo-v2.png')) }}"
                        alt=""
                        aria-hidden="true"
                        style="height: 56px; width: auto; display: block; flex-shrink: 0;"
                    >
                </a>

                <nav class="nav-links">
                    <a href="{{ url('/#features') }}">Features</a>
                    <a href="{{ url('/#how-it-works') }}">How It Works</a>
                    <a href="{{ url('/#biicf') }}">BIICF</a>
                    <a href="{{ url('/#about') }}">About</a>
                </nav>

                <div class="nav-actions">
                    @if (Route::has('login'))
                        @auth
                            @if (auth()->user()->role === 'admin')
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-th-large"></i> Dashboard
                                </a>
                            @elseif (auth()->user()->role === 'lecturer')
                                <a href="{{ route('lecturer.dashboard') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-th-large"></i> Dashboard
                                </a>
                            @else
                                <a href="{{ route('student.dashboard') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-th-large"></i> Dashboard
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline btn-sm">
                                <i class="fas fa-sign-in-alt"></i> Log In
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-accent btn-sm">
                                    <i class="fas fa-user-plus"></i> Sign Up
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </header>

    <!-- ============================================
    BANNER
    ============================================ -->
    <section class="team-hero">
        <div class="container">
            <a href="{{ url('/') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
            <div>
                <div class="hero-badge">
                    <i class="fas fa-users"></i> Our Team
                </div>
            </div>
            <h1>The People Behind <span>CareerPath BN</span></h1>
            <p>A Final Year Project by SICT students at Politeknik Brunei, in collaboration with BIICF Brunei Innovation Lab.</p>
        </div>
    </section>

    <!-- ============================================
    TEAM
    ============================================ -->
    <section class="team-section">
        <div class="container">
            <div class="team-card">
                @include('partials.team')
            </div>
        </div>
    </section>

    <!-- ============================================
    FOOTER
    ============================================ -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <p>AI-powered career guidance platform aligned with the Brunei ICT Industry Competency Framework (BIICF).</p>
                    <div class="footer-logos">
                        <a href="https://www.pb.edu.bn" target="_blank" class="logo-link">
                            @if(file_exists(public_path('images/politeknik-logo.png')))
                                <img src="{{ asset('images/politeknik-logo.png') }}" alt="Politeknik Brunei" class="footer-logo">
                            @else
                                <span class="logo-fallback"><i class="fas fa-university"></i> Politeknik Brunei</span>
                            @endif
                        </a>
                        <a href="https://www.biicf.bn" target="_blank" class="logo-link">
                            @if(file_exists(public_path('images/biicf-logo.png')))
                                <img src="{{ asset('images/biicf-logo.png') }}" alt="BIICF" class="footer-logo">
                            @else
                                <span class="logo-fallback"><i class="fas fa-certificate"></i> BIICF</span>
                            @endif
                        </a>
                        <a href="https://www.aiti.gov.bn" target="_blank" class="logo-link">
                            @if(file_exists(public_path('images/aiti-logo.png')))
                                <img src="{{ asset('images/aiti-logo.png') }}" alt="AITI" class="footer-logo">
                            @else
                                <span class="logo-fallback"><i class="fas fa-building"></i> AITI</span>
                            @endif
                        </a>
                        <a href="https://www.instagram.com/innovationlab.bn/" target="_blank" rel="noopener" class="logo-link">
                            @if(file_exists(public_path('images/innovation-lab-logo.png')))
                                <img src="{{ asset('images/innovation-lab-logo.png') }}" alt="Brunei Innovation Lab" class="footer-logo">
                            @else
                                <span class="logo-fallback"><i class="fas fa-lightbulb"></i> Brunei Innovation Lab</span>
                            @endif
                        </a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <a href="{{ url('/#features') }}">Features</a>
                    <a href="{{ url('/#how-it-works') }}">How It Works</a>
                    <a href="{{ url('/#biicf') }}">BIICF</a>
                    <a href="#">Career Guide</a>
                </div>
                <div class="footer-col">
                    <h4>Resources</h4>
                    <a href="{{ route('student.biicf-explorer.index') }}">BIICF Framework</a>
                    <a href="{{ route('student.biicf-explorer.index') }}">ICT Sub-Sectors</a>
                    <a href="{{ route('student.biicf-explorer.index') }}">Competencies</a>
                    <a href="#">Training</a>
                </div>
                <div class="footer-col">
                    <h4>Contact</h4>
                    <a class="contact-row" href="https://www.google.com/maps/search/?api=1&query=Politeknik+Brunei+Ong+Sum+Ping" target="_blank" rel="noopener">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Politeknik Brunei, Jalan Ong Sum Ping</span>
                    </a>
                    <a class="contact-row" href="tel:+6732234630">
                        <i class="fas fa-phone"></i>
                        <span>+673 2234630 <small class="contact-label">(Politeknik Brunei official phone)</small></span>
                    </a>
                    <div class="contact-row">
                        <i class="fas fa-envelope"></i>
                        <span>
                            <a href="mailto:contact@pb.edu.bn"
                               data-attn-place="above"
                               data-attn-tip="Politeknik Brunei's official email, for questions about your studies, programmes or the school."
                               data-email-confirm="This is Politeknik Brunei's official email. Use it for questions about your studies, programmes or the school. For help with CareerPath BN itself, use our business email instead."
                               data-email-title="Email Politeknik Brunei"
                               data-email-owner="Official Politeknik Brunei email"
                               data-email-icon="fa-building-columns">contact@pb.edu.bn</a>
                            <span class="contact-sep">/</span>
                            <a href="mailto:careerpathbn@gmail.com"
                               data-attn-place="above"
                               data-attn-tip="CareerPath BN's business email, for help with this website, your account, Premium or partnerships."
                               data-email-confirm="This is the CareerPath BN team's business email. Use it for help with this website, your account, Premium or partnerships. For questions about your studies, contact Politeknik Brunei instead."
                               data-email-title="Email CareerPath BN"
                               data-email-owner="CareerPath BN business email"
                               data-email-tone="gold"
                               data-email-icon="fa-compass">careerpathbn@gmail.com</a>
                        </span>
                    </div>
                    <a class="contact-row" href="https://www.pb.edu.bn" target="_blank" rel="noopener">
                        <i class="fas fa-globe"></i>
                        <span>www.pb.edu.bn</span>
                    </a>
                    <a class="contact-row" href="https://www.instagram.com/careerpathbn/" target="_blank" rel="noopener">
                        <i class="fab fa-instagram"></i>
                        <span>@@careerpathbn <small class="contact-label">(CareerPath BN on Instagram)</small></span>
                    </a>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="footer-bottom-text">
                    <p>&copy; {{ date('Y') }} CareerPath BN. Developed by SICT Students, Politeknik Brunei.</p>
                    <p class="credit">In collaboration with AITI - Brunei ICT Industry Competency Framework (BIICF)</p>
                </div>
                <nav class="footer-legal" aria-label="Legal">
                    <a href="{{ route('privacy') }}">Privacy Policy</a>
                    <span aria-hidden="true">&middot;</span>
                    <a href="{{ route('terms') }}">Terms of Service</a>
                </nav>
            </div>
        </div>
    </footer>

    @include('partials.email-confirm')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var header = document.getElementById('site-header');

            window.addEventListener('scroll', function () {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });

            var targets = document.querySelectorAll('.reveal-stagger');

            if (!('IntersectionObserver' in window)) {
                targets.forEach(function (el) {
                    el.classList.add('visible');
                });

                return;
            }

            var observer = new IntersectionObserver(
                function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('visible');
                            observer.unobserve(entry.target);
                        }
                    });
                },
                {
                    threshold: 0.12,
                    rootMargin: '0px 0px -40px 0px'
                }
            );

            targets.forEach(function (el) {
                observer.observe(el);
            });
        });
    </script>

</body>
</html>
