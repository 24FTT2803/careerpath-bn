<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CareerPath BN — AI Career Guidance for Politeknik Brunei</title>
    <meta name="description" content="CareerPath BN maps your interests, competencies and academic profile to real BIICF-aligned careers.">

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
            --success: #2d8f5c;
            --danger: #c0392b;
            --warning: #e67e22;
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
           HEADER / NAVIGATION
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

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
            font-size: 20px;
        }

        .logo-text {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 22px;
            color: var(--primary);
        }

        .logo-text span {
            color: var(--accent);
        }

        .logo-sub {
            font-size: 10px;
            color: var(--text-muted);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            display: block;
            margin-top: -2px;
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

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-links a:hover::after {
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
           HERO SECTION
           ============================================ */
        .hero {
            padding: 140px 0 80px;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 50%, var(--primary-light) 100%);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 60%;
            height: 200%;
            background: radial-gradient(circle, rgba(201, 168, 76, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 80px;
            background: var(--bg);
            clip-path: ellipse(70% 100% at 50% 100%);
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .hero-badge {
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

        .hero-badge i {
            font-size: 14px;
        }

        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 52px;
            font-weight: 700;
            color: white;
            line-height: 1.1;
            margin-bottom: 20px;
        }

        .hero h1 span {
            color: var(--accent);
        }

        .hero p {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.8);
            max-width: 480px;
            line-height: 1.7;
            margin-bottom: 32px;
        }

        .hero-actions {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-top: 48px;
            padding-top: 32px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .hero-stat {
            text-align: left;
        }

        .hero-stat .number {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 700;
            color: var(--accent);
        }

        .hero-stat .label {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
        }

        .hero-image {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .hero-illustration {
            width: 100%;
            max-width: 480px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: var(--radius);
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .hero-illustration .icon-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .hero-illustration .icon-item {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: var(--transition);
        }

        .hero-illustration .icon-item:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-4px);
        }

        .hero-illustration .icon-item i {
            font-size: 28px;
            color: var(--accent);
            margin-bottom: 8px;
        }

        .hero-illustration .icon-item span {
            display: block;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.6);
        }

        /* ============================================
           FEATURES SECTION
           ============================================ */
        .features {
            padding: 80px 0;
            background: var(--bg);
        }

        .section-header {
            text-align: center;
            max-width: 640px;
            margin: 0 auto 48px;
        }

        .section-header .tag {
            display: inline-block;
            font-size: 12px;
            font-weight: 600;
            color: var(--accent);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .section-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 12px;
        }

        .section-header p {
            color: var(--text-muted);
            font-size: 16px;
            line-height: 1.7;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .feature-card {
            background: var(--card);
            border-radius: var(--radius);
            padding: 32px;
            border: 1px solid var(--border);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            opacity: 0;
            transition: var(--transition);
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
            border-color: var(--accent-light);
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: rgba(26, 58, 92, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--primary);
            margin-bottom: 16px;
        }

        .feature-card h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .feature-card p {
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.6;
        }

        /* ============================================
           HOW IT WORKS
           ============================================ */
        .how-it-works {
            padding: 80px 0;
            background: white;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }

        .step {
            text-align: center;
            padding: 32px 24px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            transition: var(--transition);
            position: relative;
        }

        .step:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
        }

        .step-number {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            margin: 0 auto 16px;
        }

        .step h4 {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .step p {
            color: var(--text-muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .step-arrow {
            position: absolute;
            right: -16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--border);
            font-size: 20px;
        }

        /* ============================================
           BIICF SECTION
           ============================================ */
        .biicf-section {
            padding: 80px 0;
            background: var(--primary-dark);
            color: white;
        }

        .biicf-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .biicf-content .tag {
            display: inline-block;
            font-size: 12px;
            font-weight: 600;
            color: var(--accent);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .biicf-content h2 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .biicf-content h2 span {
            color: var(--accent);
        }

        .biicf-content p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 16px;
            line-height: 1.7;
            margin-bottom: 24px;
        }

        .biicf-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .biicf-stat {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .biicf-stat .number {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--accent);
        }

        .biicf-stat .label {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
        }

        .biicf-image {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .biicf-image .placeholder {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: var(--radius);
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .biicf-image .placeholder i {
            font-size: 64px;
            color: var(--accent);
            margin-bottom: 16px;
        }

        .biicf-image .placeholder h4 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .biicf-image .placeholder p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 13px;
        }

        /* ============================================
           CTA SECTION
           ============================================ */
        .cta-section {
            padding: 80px 0;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -20%;
            width: 60%;
            height: 200%;
            background: radial-gradient(circle, rgba(201, 168, 76, 0.05) 0%, transparent 70%);
            pointer-events: none;
        }

        .cta-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 700;
            color: white;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }

        .cta-section p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 18px;
            max-width: 480px;
            margin: 0 auto 32px;
            position: relative;
            z-index: 1;
        }

        .cta-section .btn {
            position: relative;
            z-index: 1;
        }

        /* ============================================
           FOOTER
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

        .footer-brand .logo-text {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: white;
        }

        .footer-brand .logo-text span {
            color: var(--accent);
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
            gap: 20px;
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
            height: 50px;
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

        .footer-social {
            display: flex;
            gap: 16px;
        }

        .footer-social a {
            color: rgba(255, 255, 255, 0.3);
            font-size: 18px;
            transition: var(--transition);
        }

        .footer-social a:hover {
            color: var(--accent);
            transform: translateY(-2px);
        }

        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 1024px) {
            .hero h1 { font-size: 40px; }
            .features-grid { grid-template-columns: repeat(2, 1fr); }
            .steps-grid { grid-template-columns: repeat(2, 1fr); }
            .footer-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .hero-grid { grid-template-columns: 1fr; text-align: center; }
            .hero h1 { font-size: 32px; }
            .hero p { max-width: 100%; }
            .hero-stats { grid-template-columns: repeat(3, 1fr); }
            .features-grid { grid-template-columns: 1fr; }
            .steps-grid { grid-template-columns: 1fr; }
            .step-arrow { display: none; }
            .biicf-grid { grid-template-columns: 1fr; text-align: center; }
            .biicf-stats { grid-template-columns: 1fr 1fr; }
            .footer-grid { grid-template-columns: 1fr; }
            .nav-links { display: none; }
            .footer-bottom { flex-direction: column; text-align: center; }
            .hero-illustration .icon-grid { grid-template-columns: repeat(3, 1fr); }
        }

        @media (max-width: 480px) {
            .hero { padding: 120px 0 60px; }
            .hero h1 { font-size: 28px; }
            .hero-actions { flex-direction: column; align-items: center; }
            .hero-stats { grid-template-columns: 1fr; gap: 12px; }
            .hero-stat { text-align: center; }
            .biicf-stats { grid-template-columns: 1fr; }
            .footer-logos { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

    <!-- ============================================
    HEADER
    ============================================ -->
    <header class="site-header" id="site-header">
        <div class="container">
            <div class="header-inner">
                <a href="{{ url('/') }}" class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-compass"></i>
                    </div>
                    <div>
                        <span class="logo-text">CareerPath <span>BN</span></span>
                        <span class="logo-sub">Politeknik Brunei</span>
                    </div>
                </a>

                <nav class="nav-links">
                    <a href="#features">Features</a>
                    <a href="#how-it-works">How It Works</a>
                    <a href="#biicf">BIICF</a>
                    <a href="#about">About</a>
                </nav>

                <div class="nav-actions">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/student/dashboard') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-th-large"></i> Dashboard
                            </a>
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
    HERO
    ============================================ -->
    <section class="hero">
        <div class="container">
            <div class="hero-grid">
                <div>
                    <div class="hero-badge">
                        <i class="fas fa-robot"></i> AI-Powered Career Guidance
                    </div>
                    <h1>Discover Your <span>Career Path</span> with Confidence</h1>
                    <p>AI-powered career guidance platform aligned with the Brunei ICT Industry Competency Framework (BIICF).</p>
                    <div class="hero-actions">
                        <a href="{{ Route::has('register') ? route('register') : '#' }}" class="btn btn-accent">
                            <i class="fas fa-rocket"></i> Get Started Free
                        </a>
                        <a href="#how-it-works" class="btn btn-outline" style="border-color:rgba(255,255,255,0.3);color:white;">
                            <i class="fas fa-play-circle"></i> Learn More
                        </a>
                    </div>
                    <div class="hero-stats">
                        @php
                    $totalJobRoles = \App\Models\BiicfJobRole::count();
                    $totalCompetencies = \App\Models\BiicfCompetency::count();
                    $totalSubSectors = \App\Models\BiicfSubSector::count();
                    @endphp
                        <div class="hero-stat">
                            <div class="number">{{ $totalJobRoles }}+</div>
                            <div class="label">ICT Job Roles Mapped</div>
                        </div>
                        <div class="hero-stat">
                            <div class="number">{{ $totalCompetencies }}+</div>
                            <div class="label">Competencies Referenced</div>
                        </div>
                        <div class="hero-stat">
                            <div class="number">BIICF</div>
                            <div class="label">Framework Aligned</div>
                        </div>
                    </div>
                    <div style="height: 80px;"></div>
                </div>
                <div class="hero-image">
                    <div class="hero-illustration">
                        <div class="icon-grid">
                            <div class="icon-item">
                                <i class="fas fa-code"></i>
                                <span>Development</span>
                            </div>
                            <div class="icon-item">
                                <i class="fas fa-network-wired"></i>
                                <span>Networking</span>
                            </div>
                            <div class="icon-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>Security</span>
                            </div>
                            <div class="icon-item">
                                <i class="fas fa-database"></i>
                                <span>Data</span>
                            </div>
                            <div class="icon-item">
                                <i class="fas fa-cloud"></i>
                                <span>Cloud</span>
                            </div>
                            <div class="icon-item">
                                <i class="fas fa-brain"></i>
                                <span>AI</span>
                            </div>
                        </div>
                        <div style="text-align:center;margin-top:20px;padding-top:20px;border-top:1px solid rgba(255,255,255,0.08);">
                            <span style="color:rgba(255,255,255,0.4);font-size:12px;">Powered by</span>
                            <span style="color:var(--accent);font-weight:600;font-size:14px;display:block;">Brunei ICT Industry Competency Framework</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
   FEATURES
   ============================================ -->
<section class="features" id="features">
    <div class="container">
        <div class="section-header">
            <span class="tag"><i class="fas fa-star"></i> Features</span>
            <h2>Everything You Need for Career Success</h2>
            <p>From profile building to career matching, we've got you covered.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-user-cog"></i></div>
                <h3>Career Profiling</h3>
                <p>Build your complete profile with interests, competencies, projects, and aspirations.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-robot"></i></div>
                <h3>AI Career Matching</h3>
                <p>Get personalized career recommendations with clear, explainable reasons.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                <h3>Skill Gap Analysis</h3>
                <p>See exactly what skills you need and how to develop them.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-flag-checkered"></i></div>
                <h3>Milestone Tracking</h3>
                <p>Track your progress with personalized milestones and achievements.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-certificate"></i></div>
                <h3>BIICF Alignment</h3>
                <p>Mapped to the Brunei ICT Industry Competency Framework for accurate guidance.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-chart-pie"></i></div>
                <h3>Progress Analytics</h3>
                <p>Lecturers can track student progress, identify skill gaps, and provide targeted guidance.</p>
            </div>
        </div>
    </div>
</section>

    <!-- ============================================
    HOW IT WORKS
    ============================================ -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <div class="section-header">
                <span class="tag"><i class="fas fa-route"></i> How It Works</span>
                <h2>From Profile to Career in 4 Steps</h2>
                <p>Simple, structured, and effective career discovery.</p>
            </div>
            <div class="steps-grid">
                <div class="step">
                    <div class="step-number">1</div>
                    <h4>Build Your Profile</h4>
                    <p>Complete your profile with academics, skills, projects, and career aspirations.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h4>Get Matched</h4>
                    <p>AI analyzes your profile and recommends the best-fit BIICF careers.</p>
                    <span class="step-arrow">→</span>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h4>Identify Gaps</h4>
                    <p>See which competencies you need to develop for each career.</p>
                    <span class="step-arrow">→</span>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h4>Follow Your Plan</h4>
                    <p>Track your progress with milestones and development activities.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
    BIICF SECTION (DYNAMIC DATA FROM DATABASE)
    ============================================ -->
    <section class="biicf-section" id="biicf">
        <div class="container">
            <div class="biicf-grid">
                <div class="biicf-content">
                    <span class="tag"><i class="fas fa-certificate"></i> Framework Alignment</span>
                    <h2>Aligned with <span>BIICF</span></h2>
                    <p>The Brunei ICT Industry Competency Framework (BIICF) articulates the competencies needed to perform various ICT job roles. CareerPath BN makes it navigable for students.</p>
                    <div class="biicf-stats">
                        @php
                            use App\Models\BiicfJobRole;
                            use App\Models\BiicfCompetency;
                            use App\Models\BiicfSubSector;
                            use App\Models\BiicfProficiencyLevel;
                            
                            $totalJobRoles = BiicfJobRole::count();
                            $totalCompetencies = BiicfCompetency::count();
                            $totalSubSectors = BiicfSubSector::count();
                            $totalProficiencyLevels = BiicfProficiencyLevel::count();
                        @endphp
                        <div class="biicf-stat">
                            <div class="number">{{ $totalJobRoles }}</div>
                            <div class="label">Job Roles Mapped</div>
                        </div>
                        <div class="biicf-stat">
                            <div class="number">{{ $totalCompetencies }}</div>
                            <div class="label">Competencies</div>
                        </div>
                        <div class="biicf-stat">
                            <div class="number">{{ $totalSubSectors }}</div>
                            <div class="label">ICT Subsectors</div>
                        </div>
                        <div class="biicf-stat">
                            <div class="number">AITI</div>
                            <div class="label">Framework Issuer</div>
                        </div>
                    </div>
                </div>
                <div class="biicf-image">
                    <div class="placeholder">
                        <i class="fas fa-certificate"></i>
                        <h4>Brunei ICT Industry</h4>
                        <p>Competency Framework</p>
                        <div style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(255,255,255,0.08);">
                            <span style="color:rgba(255,255,255,0.3);font-size:11px;">Source: AITI — ICT Industry Competency Framework</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
    CTA SECTION
    ============================================ -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Discover Your Career Path?</h2>
            <p>Join hundreds of students who are already building their future with CareerPath BN.</p>
            <a href="{{ Route::has('register') ? route('register') : '#' }}" class="btn btn-accent" style="font-size:16px;padding:14px 40px;">
                <i class="fas fa-arrow-right"></i> Get Started Now
            </a>
        </div>
    </section>

    <!-- ============================================
    FOOTER
    ============================================ -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="logo-text">CareerPath <span>BN</span></div>
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
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <a href="#features">Features</a>
                    <a href="#how-it-works">How It Works</a>
                    <a href="#biicf">BIICF</a>
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
                    <a href="#"><i class="fas fa-university"></i> Politeknik Brunei</a>
                    <a href="#"><i class="fas fa-map-marker-alt"></i> Jalan Ong Sum Ping, BSB</a>
                    <a href="#"><i class="fas fa-envelope"></i> sict@pb.edu.bn</a>
                    <a href="#"><i class="fas fa-phone"></i> +673 123 4567</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} CareerPath BN. Developed by SICT Students, Politeknik Brunei.</p>
                <p class="credit">In collaboration with AITI - Brunei ICT Industry Competency Framework (BIICF)</p>
            </div>
        </div>
    </footer>

    <!-- ============================================
    SCROLL EFFECT
    ============================================ -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.getElementById('site-header');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
        });
    </script>

</body>
</html>