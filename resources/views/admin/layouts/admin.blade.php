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
            Admin — CareerPath BN
        @endif
    </title>
    {{--
        The side menu's size and whether it is hidden, set before the
        page draws so it never opens and then snaps shut. On a phone
        it always starts hidden.
    --}}
    <script>
        (function () {
            var root = document.documentElement;
            var saved = {};

            try {
                saved = JSON.parse(localStorage.getItem('cpbn.sidebar') || '{}') || {};
            } catch (e) {}

            var width = Number(saved.width) || 260;
            width = width < 150 ? 64 : Math.min(420, width);

            root.style.setProperty('--sidebar-width', width + 'px');
            root.classList.toggle('sidebar-rail', width === 64);

            if (window.matchMedia('(max-width: 768px)').matches || saved.collapsed === true) {
                root.classList.add('sidebar-collapsed');
            }
        })();
    </script>
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=2" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    <style>
        /* ============================================
           ADMIN LAYOUT - FOLLOWING STUDENT DESIGN
           ============================================ */

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
            --success: #2d8f5c;
            --danger: #c0392b;
            --warning: #e67e22;
            --gold: #c9a84c;
            --gold-wash: #fbf1de;
            --font-display: 'Playfair Display', serif;
            --font-body: 'Inter', -apple-system, sans-serif;
            --font-mono: 'IBM Plex Mono', ui-monospace, monospace;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: var(--font-body);
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            -webkit-font-smoothing: antialiased;
        }

        /* ---------- Sidebar ---------- */
        .admin-sidebar {
            width: 260px;
            min-height: 100vh;
            background: var(--primary-dark);
            color: #f5f1e6;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            flex-shrink: 0;
        }

        .sidebar-brand {
            padding: 24px 24px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(245, 241, 230, 0.1);
        }

        .sidebar-brand .brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
            font-size: 20px;
            flex-shrink: 0;
        }

        .sidebar-brand .brand-text {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 18px;
            color: white;
        }

        .sidebar-brand .brand-text span {
            color: var(--accent);
        }

        .sidebar-brand .brand-sub {
            font-family: var(--font-mono);
            font-size: 10px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--accent-light);
            display: block;
            margin-top: -2px;
        }

        .sidebar-nav {
            padding: 16px 14px;
            flex: 1;
        }

        .sidebar-nav .nav-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: rgba(255, 255, 255, 0.3);
            padding: 12px 14px 8px;
            font-family: var(--font-mono);
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 6px;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 13.5px;
            transition: all 0.2s ease;
            margin-bottom: 2px;
        }

        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.06);
            color: white;
        }

        .sidebar-link.active {
            background: rgba(201, 168, 76, 0.15);
            color: var(--accent-light);
            font-weight: 500;
        }

        .sidebar-link i {
            width: 18px;
            font-size: 14px;
        }

        .sidebar-link .badge {
            margin-left: auto;
            background: var(--accent);
            color: var(--primary-dark);
            padding: 1px 10px;
            border-radius: 100px;
            font-size: 10px;
            font-weight: 600;
        }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        .sidebar-footer .nav-user-wrapper {
            position: relative;
            display: block;
            width: 100%;
        }

        .sidebar-footer .nav-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 12px 6px 6px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s ease;
            user-select: none;
            width: 100%;
        }

        .sidebar-footer .nav-user:hover {
            background: rgba(255, 255, 255, 0.06);
        }

        .sidebar-footer .nav-user .avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

        .sidebar-footer .nav-user .user-info {
            flex: 1;
            min-width: 0;
        }

        .sidebar-footer .nav-user .name {
            font-size: 13px;
            font-weight: 500;
            color: #f5f1e6;
            display: block;
            line-height: 1.3;
        }

        .sidebar-footer .nav-user .role {
            font-family: var(--font-mono);
            font-size: 10px;
            color: rgba(255, 255, 255, 0.4);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            display: block;
            line-height: 1.3;
        }

        .sidebar-footer .nav-user .chevron {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.4);
            transition: transform 0.3s ease;
            flex-shrink: 0;
        }

        .sidebar-footer .nav-user.active .chevron {
            transform: rotate(180deg);
        }

        /* Dropdown - hidden by default, shows only Logout */
        .sidebar-footer .nav-user-wrapper .dropdown {
            display: none;
            position: absolute;
            bottom: calc(100% + 8px);
            right: 0;
            min-width: 180px;
            z-index: 9999;
        }

        .sidebar-footer .nav-user-wrapper.active .dropdown {
            display: block;
        }

        .sidebar-footer .nav-user-wrapper .dropdown .dropdown-menu {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 8px 40px rgba(26, 58, 92, 0.15);
            padding: 6px;
        }

        .sidebar-footer .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 6px;
            color: #1a1a2e;
            text-decoration: none;
            font-size: 13px;
            transition: background 0.2s ease;
            cursor: pointer;
            width: 100%;
            border: none;
            background: transparent;
            text-align: left;
            font-family: var(--font-body);
        }

        .sidebar-footer .dropdown-item:hover {
            background: #f4f6f9;
        }

        .sidebar-footer .dropdown-item i {
            width: 18px;
            color: #6b7280;
            font-size: 14px;
        }

        .sidebar-footer .dropdown-item.danger {
            color: #c0392b;
        }

        .sidebar-footer .dropdown-item.danger i {
            color: #c0392b;
        }

        .sidebar-footer .dropdown-item.danger:hover {
            background: rgba(192, 57, 43, 0.08);
        }

        /* ---------- Main Content ---------- */
        .admin-main {
            flex: 1;
            padding: 32px 36px 56px;
            max-width: calc(100% - 260px);
            overflow-y: auto;
        }

        /* Footer */
        .admin-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .admin-footer p {
            font-size: 12px;
            color: var(--text-muted);
            margin: 0;
        }

        .admin-footer-logos {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .admin-footer-logo-link {
            display: inline-block;
            transition: var(--transition);
        }

        .admin-footer-logo-link:hover {
            transform: translateY(-2px);
        }

        .admin-footer-logo {
            height: 26px;
            width: auto;
            object-fit: contain;
            opacity: 0.75;
            transition: var(--transition);
        }

        .admin-footer-logo:hover {
            opacity: 1;
        }

        /* Three-line wordmark: a little taller so its text stays readable. */
        .admin-footer-logo.is-stacked {
            height: 34px;
        }

        .admin-footer-logo-fallback {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            background: var(--gold-wash);
            border-radius: 100px;
            color: var(--accent-dark);
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
        }

        .admin-footer-credit {
            font-size: 11px;
            color: var(--text-muted);
        }

        @media (max-width: 768px) {
            .admin-footer {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        /* ============================================
           SHARED ADMIN THEME
           Matching the student profile page, so every admin
           screen reads as one product rather than several.
           ============================================ */

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-header h1 i {
            color: var(--accent);
        }

        .page-header .subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin-top: 2px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-family: inherit;
        }

        .btn-primary {
            background: var(--accent);
            color: var(--primary-dark);
        }

        .btn-primary:hover {
            background: var(--accent-light);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(201, 168, 76, 0.25);
        }

        .btn-outline {
            background: transparent;
            color: var(--text);
            border: 2px solid var(--border);
        }

        .btn-outline:hover {
            border-color: var(--accent);
            color: var(--accent);
            transform: translateY(-2px);
        }

        .btn-subtle {
            background: #f3f4f6;
            color: var(--text);
        }

        .btn-subtle:hover {
            background: #e5e7eb;
        }

        .btn-sm {
            padding: 7px 14px;
            font-size: 12px;
        }

        .card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 24px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        }

        .card-header {
            margin-bottom: 16px;
        }

        .card-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .card-header h3 i {
            color: var(--accent);
        }

        .card-heading {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary);
            margin: 0 0 14px;
        }

        .text-right {
            text-align: right;
        }

        .card-header .card-note {
            color: var(--text-muted);
            font-size: 13px;
            margin-top: 4px;
        }

        /*
         * Text flows as one paragraph, with the icon in its own column.
         * (As a flex row, a link or bold label inside the sentence became
         * a separate column and squeezed the text, badly on phones.)
         */
        .info-banner {
            position: relative;
            display: block;
            padding: 12px 18px 12px 44px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            line-height: 1.5;
        }

        .info-banner > i:first-child {
            position: absolute;
            top: 15px;
            left: 18px;
        }

        .info-banner-blue {
            background: #e8f0fe;
            border-left: 4px solid var(--primary-light);
            color: #1e4870;
        }

        .info-banner-gold {
            background: var(--gold-wash);
            border-left: 4px solid var(--accent);
            color: #8a6420;
        }

        .admin-table {
            width: 100%;
            font-size: 13px;
            border-collapse: collapse;
        }

        .admin-table thead tr {
            text-align: left;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
        }

        .admin-table th {
            padding: 8px 0;
            font-weight: 600;
            font-size: 12px;
        }

        .admin-table td {
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }

        .admin-table tbody tr:last-child td {
            border-bottom: none;
        }

        .cell-title {
            font-weight: 600;
            color: var(--text);
            display: block;
        }

        .cell-sub {
            font-size: 11px;
            color: var(--text-muted);
            display: block;
            margin-top: 1px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 11px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-pill-green {
            background: #e9f3ee;
            color: var(--success);
        }

        .status-pill-gold {
            background: var(--gold-wash);
            color: #8a6420;
        }

        .status-pill-blue {
            background: #e8f0fe;
            color: var(--primary-light);
        }

        .status-pill-muted {
            background: #f3f4f6;
            color: var(--text-muted);
        }

        .link {
            color: var(--primary-light);
            background: none;
            border: 0;
            padding: 0;
            font-family: inherit;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .link:hover {
            color: var(--accent);
            text-decoration: underline;
        }

        .link-danger {
            color: var(--danger);
        }

        .link-danger:hover {
            color: #a93226;
        }

        .link-disabled {
            color: #d1d5db;
            cursor: not-allowed;
        }

        .row-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
        }

        .row-actions form {
            display: inline;
        }

        .field-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .field-input {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            background: white;
            transition: all 0.3s ease;
        }

        .field-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(26, 58, 92, 0.08);
        }

        .field-hint {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .field-grid {
            display: grid;
            gap: 16px;
            margin-bottom: 16px;
        }

        .field-grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .field-grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        @media (max-width: 768px) {
            .field-grid-2,
            .field-grid-3 {
                grid-template-columns: 1fr;
            }
        }

        .empty-text {
            color: #9ca3af;
            font-size: 13px;
            text-align: center;
            padding: 24px 0;
        }

        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffc107;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .sidebar-logo-badge {
            display: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            box-shadow: 0 0 0 2px rgba(201, 168, 76, 0.45);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-main {
                padding: 20px 16px 40px;
            }
        }

        @media (max-width: 480px) {
            .admin-main {
                padding: 16px 12px 32px;
            }
        }

        /* ---------- The side menu: resized, narrowed, hidden ----------
         *
         * Dragged wider or narrower by its right edge. Narrowed far
         * enough it becomes a strip of icons, each keeping its name
         * as a tooltip; dragged to the edge, or hidden with its
         * button, it is gone, and a button at the top left brings
         * it back. Pointing at that button or the screen's left
         * edge shows it for a look; clicking opens it again.
         */
        .admin-sidebar {
            width: var(--sidebar-width, 260px);
            transition: width 0.2s ease, transform 0.2s ease;
        }

        .admin-main {
            max-width: calc(100% - var(--sidebar-width, 260px));
            transition: max-width 0.2s ease, padding 0.2s ease;
        }

        .sidebar-dragging .admin-sidebar,
        .sidebar-dragging .admin-main {
            transition: none;
        }

        .sidebar-dragging,
        .sidebar-dragging * {
            cursor: col-resize !important;
            user-select: none !important;
        }

        .admin-sidebar .sidebar-brand {
            position: relative;
            gap: 8px;
        }

        .admin-sidebar .sidebar-logo-full {
            max-width: calc(100% - 44px);
            object-fit: contain;
        }

        .sidebar-toggle,
        .sidebar-open-button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            padding: 0;
            border: 0;
            border-radius: 8px;
            background: transparent;
            cursor: pointer;
            flex-shrink: 0;
        }

        .sidebar-toggle {
            color: rgba(255, 255, 255, 0.75);
        }

        .sidebar-toggle:hover,
        .sidebar-toggle:focus-visible {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .sidebar-toggle svg,
        .sidebar-open-button svg {
            width: 20px;
            height: 20px;
        }

        /* The edge to drag. */
        .sidebar-resizer {
            position: absolute;
            top: 0;
            right: 0;
            width: 6px;
            height: 100%;
            cursor: col-resize;
            z-index: 5;
        }

        .sidebar-resizer::after {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 2px;
            width: 2px;
            background: transparent;
            transition: background 0.15s ease;
        }

        .sidebar-resizer:hover::after,
        .sidebar-dragging .sidebar-resizer::after {
            background: var(--accent, #c9a84c);
        }

        .admin-sidebar {
            overflow-x: hidden;
        }

        .admin-sidebar-wrap {
            position: sticky;
            top: 0;
            height: 100vh;
            flex-shrink: 0;
            z-index: 20;
        }

        /* Icons only. */
        .sidebar-rail .admin-sidebar .sidebar-brand {
            flex-direction: column;
            padding: 14px 6px !important;
            justify-content: center;
        }

        .sidebar-rail .admin-sidebar .sidebar-logo-full {
            display: none !important;
        }

        .sidebar-rail .admin-sidebar .sidebar-logo-badge {
            display: block;
        }

        .sidebar-rail .admin-sidebar .sidebar-link span,
        .sidebar-rail .admin-sidebar .sidebar-link .badge,
        .sidebar-rail .admin-sidebar .nav-label,
        .sidebar-rail .admin-sidebar .sidebar-user-info,
        .sidebar-rail .admin-sidebar .sidebar-logout-text {
            display: none;
        }

        .sidebar-rail .admin-sidebar .sidebar-nav {
            padding: 12px 6px;
        }

        .sidebar-rail .admin-sidebar .sidebar-link {
            justify-content: center;
            padding: 12px;
        }

        .sidebar-rail .admin-sidebar .sidebar-link i {
            font-size: 18px;
            width: auto;
        }

        .sidebar-rail .admin-sidebar .sidebar-footer {
            padding: 12px 6px !important;
        }

        .sidebar-rail .admin-sidebar .sidebar-user {
            flex-direction: column;
        }

        .sidebar-rail .admin-sidebar .sidebar-logout {
            padding: 8px !important;
        }

        .sidebar-rail .admin-sidebar .sidebar-logout i {
            margin-right: 0 !important;
        }

        /* Hidden. */
        .sidebar-open-button {
            display: none;
            position: fixed;
            top: 14px;
            left: 12px;
            z-index: 1050;
            background: var(--primary-dark, #0d1f33);
            color: #fff;
            box-shadow: 0 2px 8px rgba(13, 31, 51, 0.25);
        }

        .sidebar-open-button:hover,
        .sidebar-open-button:focus-visible {
            background: var(--primary, #1a3a5c);
        }

        .sidebar-edge {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 8px;
            height: 100vh;
            z-index: 1040;
            cursor: col-resize;
        }

        .sidebar-collapsed .admin-sidebar-wrap {
            width: 0;
        }

        .sidebar-collapsed .admin-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1060;
            transform: translateX(-100%);
            box-shadow: none;
        }

        .sidebar-collapsed .admin-main {
            max-width: 100%;
            padding-left: 64px;
        }

        .sidebar-collapsed .sidebar-open-button,
        .sidebar-collapsed .sidebar-edge {
            display: flex;
        }

        .sidebar-collapsed .sidebar-resizer {
            display: none;
        }

        /* A look while hidden, or opened on a phone: over the page. */
        .sidebar-collapsed.sidebar-peek .admin-sidebar,
        .sidebar-collapsed.sidebar-overlay .admin-sidebar {
            transform: none;
            box-shadow: 8px 0 24px rgba(0, 0, 0, 0.25);
        }

        .sidebar-backdrop {
            display: none;
        }

        .sidebar-overlay .sidebar-backdrop {
            display: block;
            position: fixed;
            inset: 0;
            z-index: 1055;
            background: rgba(13, 31, 51, 0.35);
        }

        @media (max-width: 768px) {
            .sidebar-resizer,
            .sidebar-edge {
                display: none !important;
            }

            .sidebar-collapsed .admin-main {
                padding-left: 60px;
            }

            .sidebar-open-button,
            .sidebar-toggle {
                width: 40px;
                height: 40px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .admin-sidebar,
            .admin-main {
                transition: none;
            }
        }

        /* File upload fields: branded button instead of the browser default */
        input[type="file"] {
            font-family: inherit;
            font-size: 13px;
            color: var(--text-muted, #6b7280);
            cursor: pointer;
        }

        input[type="file"]::file-selector-button {
            margin-right: 12px;
            padding: 8px 16px;
            border: 0;
            border-radius: 8px;
            background: var(--primary, #1a3a5c);
            color: #fff;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        input[type="file"]::file-selector-button:hover {
            background: var(--primary-light, #2a5a8c);
        }

        input[type="file"]:focus-visible::file-selector-button {
            outline: 2px solid var(--accent, #c9a84c);
            outline-offset: 2px;
        }


        /* ============================================
           ADMIN DESIGN REFINEMENTS (layout only)
           ============================================ */

        /* Label-style cells must stay table cells, or columns drift apart. */
        .admin-table td.cell-sub,
        .admin-table td.cell-title,
        .admin-table th.cell-sub {
            display: table-cell;
        }

        .admin-table th,
        .admin-table td {
            padding-right: 16px;
        }

        .admin-table th:last-child,
        .admin-table td:last-child {
            padding-right: 0;
        }

        .admin-table td {
            vertical-align: middle;
        }

        .admin-table th {
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-size: 11px;
        }

        .admin-table tbody tr {
            transition: background-color 0.15s ease;
        }

        .admin-table tbody tr:hover {
            background: #fcfbf7;
        }

        /* Row actions: small outlined buttons instead of loose text links. */
        .row-actions {
            gap: 6px;
            flex-wrap: wrap;
        }

        .row-actions .link,
        .row-actions .link-disabled {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: white;
            color: var(--primary);
            font-size: 12px;
            font-weight: 600;
            line-height: 1.3;
            text-decoration: none;
            white-space: nowrap;
        }

        .row-actions .link:hover {
            background: #faf7ee;
            border-color: var(--accent);
            color: var(--primary);
            text-decoration: none;
        }

        .row-actions .link-danger {
            color: var(--danger);
        }

        .row-actions .link-danger:hover {
            background: #fdf1f0;
            border-color: #f0b8b1;
            color: #a93226;
        }

        .row-actions .link-disabled {
            color: #c3c7ce;
            background: #fafafa;
            cursor: not-allowed;
        }

        /* Phone country picker fills its field like any other input. */
        .iti {
            display: block;
            width: 100%;
        }

        /*
         * Wide tables scroll inside their card rather than
         * compressing past the point of being readable.
         *
         * The padding is deliberate: without it the last column
         * sits flush against the edge at the end of the scroll,
         * with nothing to show the row has finished.
         */
        /*
         * Two names for the same job. The BIICF and dashboard
         * pages were written against .table-responsive, which
         * nothing defined — there is no Bootstrap here — so those
         * tables had neither a floor nor anywhere to scroll.
         * Both names are honoured rather than rewriting the
         * markup of six pages.
         */
        .table-scroll,
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-right: 16px;
            padding-bottom: 4px;
        }

        /*
         * The floor below which a table stops condensing and
         * starts scrolling. Tables carrying more columns raise it
         * for themselves with --table-floor, since one shared
         * number either squashes the widest table or makes the
         * narrowest scroll for no reason.
         */
        .table-scroll .admin-table,
        .table-responsive .table,
        .table-responsive .cpbn-table {
            min-width: var(--table-floor, 820px);
        }

        /* Small inline forms (add a row, quick edit) line up neatly. */
        .inline-form {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }

        .inline-form .field-input {
            width: auto;
            min-width: 0;
            flex: 1 1 200px;
        }

        .inline-form .field-input.is-short {
            flex: 0 1 120px;
        }

        .inline-form .btn {
            align-self: stretch;
        }

        .add-row {
            margin-top: 16px;
            padding: 14px 16px;
            border: 1px dashed #e2dccb;
            border-radius: 10px;
            background: #fcfbf7;
        }

        .add-row-label {
            display: block;
            margin-bottom: 8px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        /* Filter pills above a list. */
        .filter-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }

        /* Search box with an icon. */
        .search-field {
            position: relative;
            margin-bottom: 14px;
        }

        .search-field i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 13px;
            pointer-events: none;
        }

        .search-field .field-input {
            padding-left: 38px;
        }

        /* Settings strip (e.g. advertisement placement options). */
        .settings-strip {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 12px;
            padding: 14px 16px;
            margin-bottom: 16px;
            border-radius: 10px;
            background: #f9fafb;
            border: 1px solid #f0f1f3;
        }

        .settings-strip .btn {
            height: 44px;
        }
    </style>
    @include('partials.motion')
    @include('partials.attention')
</head>
<body>

    @php
        /* The panel icon: a window with its side bar marked. */
        $sidebarIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4.5" width="18" height="15" rx="3"></rect><line x1="9" y1="4.5" x2="9" y2="19.5"></line></svg>';
    @endphp

    <button
        type="button"
        class="sidebar-open-button"
        id="sidebarOpen"
        aria-controls="adminSidebar"
        aria-expanded="false"
        aria-label="Open menu"
        title="Open menu"
    >{!! $sidebarIcon !!}</button>

    <div class="sidebar-edge" id="sidebarEdge" aria-hidden="true"></div>

    <!-- Sidebar -->
    <div class="admin-sidebar-wrap">
    <aside class="admin-sidebar" id="adminSidebar">
                <div class="sidebar-brand" style="justify-content: center; padding: 20px 16px;">
    <img
        src="{{ asset('images/careerpath-logo-v2.png') }}?v={{ filemtime(public_path('images/careerpath-logo-v2.png')) }}"
        alt="CareerPath BN"
        class="sidebar-logo-full"
        style="height: 64px; width: auto; display: block; background: white; padding: 6px 10px; border-radius: 6px;"
    >
    {{-- With the menu narrowed to icons, the round badge stands in for the logo. --}}
    <img
        src="{{ asset('images/careerpath-badge.png') }}"
        alt="CareerPath BN"
        class="sidebar-logo-badge"
    >

    <button
        type="button"
        class="sidebar-toggle"
        id="sidebarToggle"
        aria-controls="adminSidebar"
        aria-expanded="true"
        aria-label="Close menu"
        title="Close menu"
    >{!! $sidebarIcon !!}</button>
</div>

        <nav class="sidebar-nav">
            <div class="nav-label">Main</div>
            @php
                $isLecturerOnly = auth()->user()->role === 'lecturer';
                $dashboardRoute = $isLecturerOnly ? route('lecturer.dashboard') : route('admin.dashboard');
                $dashboardActive = request()->routeIs('admin.dashboard') || request()->routeIs('lecturer.dashboard');
                $pendingStudentIds = app(\App\Services\Student\StudentAttention::class)->pendingStudentIds(auth()->user());
                $pendingTip = $pendingStudentIds === 1
                    ? '1 student needs their student ID verified.'
                    : $pendingStudentIds.' students need their student ID verified.';

                if ($isLecturerOnly && $pendingStudentIds > 0) {
                    $pendingTip = rtrim($pendingTip, '.').' in your classes.';
                }
            @endphp
            <a href="{{ $dashboardRoute }}" class="sidebar-link {{ $dashboardActive ? 'active' : '' }}">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
                        @if(auth()->user()->role === 'lecturer')
                <a
                    href="{{ route('lecturer.students.index') }}"
                    class="sidebar-link {{
                        request()->routeIs('lecturer.students.*')
                            ? 'active'
                            : ''
                    }}"
                    @if($pendingStudentIds > 0)
                        data-attn-tip="{{ $pendingTip }}"
                        data-attn-auto="pending-ids-{{ $pendingStudentIds }}"
                    @endif
                >
                    <i class="fas fa-user-graduate"></i>
                    <span>My Students</span>
                    @if($pendingStudentIds > 0)
                        <span class="badge attn-count">{{ $pendingStudentIds > 99 ? '99+' : $pendingStudentIds }}</span>
                    @endif
                </a>
            @else
                <a
                    href="{{ route('admin.students.index') }}"
                    class="sidebar-link {{
                        request()->routeIs('admin.students.*')
                            ? 'active'
                            : ''
                    }}"
                    @if($pendingStudentIds > 0)
                        data-attn-tip="{{ $pendingTip }}"
                        data-attn-auto="pending-ids-{{ $pendingStudentIds }}"
                    @endif
                >
                    <i class="fas fa-user-graduate"></i>
                    <span>Students</span>
                    @if($pendingStudentIds > 0)
                        <span class="badge attn-count">{{ $pendingStudentIds > 99 ? '99+' : $pendingStudentIds }}</span>
                    @endif
                </a>
            @endif
                        @if(auth()->user()->role === 'admin')
                @php
                    $unassignedLecturers = \App\Models\User::where('role', 'lecturer')
                        ->whereNotIn('id', \App\Models\LecturerAssignment::select('user_id'))
                        ->count();
                    $lecturerTip = $unassignedLecturers === 1
                        ? '1 lecturer needs to be assigned to a group.'
                        : $unassignedLecturers.' lecturers need to be assigned to a group.';
                @endphp
                <a href="{{ route('admin.lecturers.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.lecturers.*') ? 'active' : '' }}"
                   @if($unassignedLecturers > 0)
                       data-attn-tip="{{ $lecturerTip }}"
                       data-attn-auto="unassigned-lecturers-{{ $unassignedLecturers }}"
                   @endif
                >
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Lecturers</span>
                    @if($unassignedLecturers > 0)
                        <span class="badge attn-count">{{ $unassignedLecturers > 99 ? '99+' : $unassignedLecturers }}</span>
                    @endif
                </a>
            @endif
            <a href="{{ route('admin.careers.index') }}" class="sidebar-link {{ request()->routeIs('admin.careers.*') ? 'active' : '' }}">
                <i class="fas fa-briefcase"></i>
                <span>Careers</span>
            </a>
            @if(auth()->user()->role === 'lecturer')
                <div class="nav-label" style="margin-top:16px;">Account</div>
                <a href="{{ route('lecturer.settings') }}" class="sidebar-link {{ request()->routeIs('lecturer.settings') ? 'active' : '' }}">
                    <i class="fas fa-id-badge"></i>
                    <span>My Profile</span>
                </a>
            @endif
            @if(auth()->user()->role === 'admin')
                <div class="nav-label" style="margin-top:16px;">BIICF Management</div>
                @php
                    $biicfCounts = [
                        'subSectors' => \App\Models\BiicfSubSector::count(),
                        'jobRoles' => \App\Models\BiicfJobRole::count(),
                        'competencies' => \App\Models\BiicfCompetency::count(),
                        'trainings' => \App\Models\BiicfTraining::count(),
                    ];
                @endphp
                <a href="{{ route('admin.biicf.sub-sectors') }}" class="sidebar-link {{ request()->routeIs('admin.biicf.sub-sectors*') ? 'active' : '' }}"
                   data-attn-tip="There {{ $biicfCounts['subSectors'] === 1 ? 'is 1 ICT sub-sector' : 'are '.$biicfCounts['subSectors'].' ICT sub-sectors' }} available in the BIICF framework.">
                    <i class="fas fa-layer-group"></i>
                    <span>Sub-Sectors <span class="badge attn-count">{{ $biicfCounts['subSectors'] }}</span></span>
                </a>
                <a href="{{ route('admin.biicf.job-roles') }}" class="sidebar-link {{ request()->routeIs('admin.biicf.job-roles*') ? 'active' : '' }}"
                   data-attn-tip="{{ $biicfCounts['jobRoles'] === 1 ? '1 job role is' : $biicfCounts['jobRoles'].' job roles are' }} available for career matching.">
                    <i class="fas fa-briefcase"></i>
                    <span>Job Roles <span class="badge attn-count">{{ $biicfCounts['jobRoles'] }}</span></span>
                </a>
                <a href="{{ route('admin.biicf.competencies') }}" class="sidebar-link {{ request()->routeIs('admin.biicf.competencies*') ? 'active' : '' }}"
                   data-attn-tip="{{ $biicfCounts['competencies'] === 1 ? '1 competency is' : $biicfCounts['competencies'].' competencies are' }} available for students to choose from.">
                    <i class="fas fa-tools"></i>
                    <span>Competencies <span class="badge attn-count">{{ $biicfCounts['competencies'] }}</span></span>
                </a>
                <a href="{{ route('admin.biicf.trainings') }}" class="sidebar-link {{ request()->routeIs('admin.biicf.trainings*') ? 'active' : '' }}"
                   data-attn-tip="{{ $biicfCounts['trainings'] === 1 ? '1 training programme is' : $biicfCounts['trainings'].' training programmes are' }} available to suggest to students.">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Trainings <span class="badge attn-count">{{ $biicfCounts['trainings'] }}</span></span>
                </a>
                {{--
                    Where a new edition of the reference book is
                    loaded, reviewed and applied. The count is
                    what is still waiting on a decision, since an
                    edition part way through a review is the one
                    thing here that needs attention.
                --}}
                @php
                    $editionsPending = \App\Models\BiicfEditionChange::query()
                        ->where('decision', 'pending')
                        ->count();
                @endphp
                <a href="{{ route('admin.biicf.editions.index') }}" class="sidebar-link {{ request()->routeIs('admin.biicf.editions*') ? 'active' : '' }}"
                   data-attn-tip="{{ $editionsPending === 0 ? 'No uploaded edition is waiting on a decision.' : $editionsPending.' differences from an uploaded edition are waiting on a decision.' }}">
                    <i class="fas fa-book"></i>
                    <span>Editions
                        @if($editionsPending > 0)
                            <span class="badge attn-count">{{ $editionsPending }}</span>
                        @endif
                    </span>
                </a>
            @endif
            @if(auth()->user()->role === 'admin')
                <div
                    class="nav-label"
                    style="margin-top:16px;"
                >
                    Business Management
                </div>

                <a
                    href="{{ route(
                        'admin.business.plans.index'
                    ) }}"
                    class="sidebar-link {{
                        request()->routeIs(
                            'admin.business.plans.*'
                        )
                            ? 'active'
                            : ''
                    }}"
                >
                    <i class="fas fa-sliders-h"></i>
                    <span>
                        Plans & Features
                    </span>
                </a>

                <a
                    href="{{ route(
                        'admin.business.advertisements.index'
                    ) }}"
                    class="sidebar-link {{
                        request()->routeIs(
                            'admin.business.advertisements.*'
                        )
                            ? 'active'
                            : ''
                    }}"
                >
                    <i class="fas fa-bullhorn"></i>
                    <span>
                        Advertisements
                    </span>
                </a>

                <a
                    href="{{ route(
                        'admin.business.grants.index'
                    ) }}"
                    class="sidebar-link {{
                        request()->routeIs(
                            'admin.business.grants.*'
                        )
                            ? 'active'
                            : ''
                    }}"
                >
                    <i class="fas fa-ticket"></i>
                    <span>
                        Access Grants
                    </span>
                </a>

                <a
                    href="{{ route(
                        'admin.business.groups.index'
                    ) }}"
                    class="sidebar-link {{
                        request()->routeIs(
                            'admin.business.groups.*'
                        )
                            ? 'active'
                            : ''
                    }}"
                >
                    <i class="fas fa-school"></i>
                    <span>
                        Academic Groups
                    </span>
                </a>

                <a
                    href="{{ route(
                        'admin.business.sponsorship.index'
                    ) }}"
                    class="sidebar-link {{
                        request()->routeIs(
                            'admin.business.sponsorship.*'
                        )
                            ? 'active'
                            : ''
                    }}"
                >
                    <i class="fas fa-handshake"></i>
                    <span>
                        Sponsorship
                    </span>
                </a>
            @endif
                        @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>Manage Users</span>
                </a>

                <a
                    href="{{ route(
                        'admin.business.innovation-lab.index'
                    ) }}"
                    class="sidebar-link {{
                        request()->routeIs(
                            'admin.business.innovation-lab.*'
                        )
                            ? 'active'
                            : ''
                    }}"
                >
                    <i class="fas fa-lightbulb"></i>
                    <span>
                        Innovation Lab
                    </span>
                </a>

                <a
                    href="{{ route('admin.business.guides.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.business.guides.*') ? 'active' : '' }}"
                >
                    <i class="fas fa-circle-question"></i>
                    <span>Section Guides</span>
                </a>
            @endif
        </nav>

        <!-- Footer - Simple Logout Button -->
<div class="sidebar-footer" style="padding:16px 20px;border-top:1px solid rgba(255,255,255,0.06);">
    <div class="sidebar-user" style="display:flex;align-items:center;gap:10px;">
        <!-- Avatar -->
        <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg, #c9a84c, #e8d4a0);color:#0d1f33;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;overflow:hidden;">
            @if(auth()->user()->avatar)
                <img src="{{ asset('storage/'.ltrim(auth()->user()->avatar, '/')) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
            @else
                {{ substr(auth()->user()->name, 0, 1) }}
            @endif
        </div>
        
        <!-- User Info -->
        <div class="sidebar-user-info" style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:500;color:#ffffff;line-height:1.3;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</div>
            <div style="font-family:'IBM Plex Mono',ui-monospace,monospace;font-size:10px;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:0.04em;line-height:1.3;">{{ ucfirst(auth()->user()->role) }}</div>
        </div>
        
        <!-- Logout Button -->
        <button onclick="confirmLogout()" class="sidebar-logout" title="Logout" aria-label="Logout" style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.1);color:#f5f1e6;padding:6px 14px;border-radius:6px;cursor:pointer;font-size:12px;transition:all 0.3s ease;font-family:inherit;white-space:nowrap;">
            <i class="fas fa-sign-out-alt" style="color:#c9a84c;margin-right:4px;"></i><span class="sidebar-logout-text"> Logout</span>
        </button>
    </div>
</div>
        <div class="sidebar-resizer" id="sidebarResizer" aria-hidden="true"></div>
    </aside>
    </div>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <script>
        /*
         * The side menu, worked by its buttons (mouse, finger or
         * keyboard), by dragging its edge, and by Control and B
         * (Command and B on a Mac) for those who know it.
         *
         * Its width and whether it is hidden are remembered on a
         * wide screen. On a phone it opens over the page and closes
         * with a tap outside it.
         */
        (function () {
            var root = document.documentElement;
            var sidebar = document.getElementById('adminSidebar');
            var toggle = document.getElementById('sidebarToggle');
            var openButton = document.getElementById('sidebarOpen');
            var edge = document.getElementById('sidebarEdge');
            var resizer = document.getElementById('sidebarResizer');
            var backdrop = document.getElementById('sidebarBackdrop');
            var narrowQuery = window.matchMedia('(max-width: 768px)');
            var canHover = window.matchMedia('(hover: hover)').matches;

            var RAIL = 64;
            var RAIL_BELOW = 150;
            var HIDE_BELOW = 40;
            var MAX = 420;

            if (! sidebar || ! toggle || ! openButton) {
                return;
            }

            function load() {
                try {
                    return JSON.parse(localStorage.getItem('cpbn.sidebar') || '{}') || {};
                } catch (e) {
                    return {};
                }
            }

            function save(state) {
                try {
                    localStorage.setItem('cpbn.sidebar', JSON.stringify(state));
                } catch (e) {}
            }

            var saved = load();
            var width = snapWidth(Number(saved.width) || 260);

            function snapWidth(value) {
                return value < RAIL_BELOW ? RAIL : Math.min(MAX, Math.round(value));
            }

            function setWidth(value) {
                width = snapWidth(value);
                root.style.setProperty('--sidebar-width', width + 'px');
                root.classList.toggle('sidebar-rail', width === RAIL);
            }

            function isHidden() {
                return root.classList.contains('sidebar-collapsed');
            }

            function label() {
                var open = ! isHidden() || root.classList.contains('sidebar-overlay');

                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                openButton.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            /* Just hidden: no look until the pointer has moved off and back. */
            var holdPeek = false;

            function hide() {
                holdPeek = true;
                root.classList.add('sidebar-collapsed');
                root.classList.remove('sidebar-peek', 'sidebar-overlay');
                label();
            }

            function show() {
                root.classList.remove('sidebar-collapsed', 'sidebar-peek', 'sidebar-overlay');
                label();
            }

            function remember() {
                if (! narrowQuery.matches) {
                    save({ width: width, collapsed: isHidden() });
                }
            }

            function flip() {
                if (narrowQuery.matches) {
                    if (root.classList.contains('sidebar-overlay')) {
                        hide();
                    } else {
                        root.classList.add('sidebar-collapsed', 'sidebar-overlay');
                        label();
                    }

                    return;
                }

                isHidden() ? show() : hide();
                remember();
            }

            /* Each icon keeps its name as a tooltip for when the names are hidden. */
            document.querySelectorAll('.admin-sidebar .sidebar-link').forEach(function (link) {
                var name = link.querySelector('span');

                if (name && ! link.title) {
                    link.title = name.textContent.trim();
                }
            });

            toggle.addEventListener('click', flip);
            openButton.addEventListener('click', function () {
                if (narrowQuery.matches) {
                    flip();

                    return;
                }

                show();
                remember();
            });
            backdrop.addEventListener('click', hide);

            /* A look while hidden: pointing at the button or the left edge. */
            if (canHover) {
                var leaveTimer = null;

                var peek = function () {
                    if (isHidden() && ! holdPeek && ! narrowQuery.matches && ! root.classList.contains('sidebar-dragging')) {
                        clearTimeout(leaveTimer);
                        root.classList.add('sidebar-peek');
                    }
                };

                var unpeek = function () {
                    clearTimeout(leaveTimer);
                    leaveTimer = setTimeout(function () {
                        root.classList.remove('sidebar-peek');
                    }, 250);
                };

                openButton.addEventListener('mouseenter', peek);
                edge.addEventListener('mouseenter', peek);
                sidebar.addEventListener('mouseenter', function () {
                    if (root.classList.contains('sidebar-peek')) {
                        clearTimeout(leaveTimer);
                    }
                });
                sidebar.addEventListener('mouseleave', unpeek);

                openButton.addEventListener('mouseleave', unpeek);
                edge.addEventListener('mouseleave', unpeek);

                /* Anywhere away from the button and the edge, a look is allowed again. */
                document.addEventListener('mousemove', function (event) {
                    if (holdPeek && ! openButton.contains(event.target) && event.target !== edge) {
                        holdPeek = false;
                    }
                });
            }

            /*
             * Dragging: by the menu's right edge, or, while it is
             * hidden, from the screen's left edge to pull it back.
             * Narrow enough and it becomes icons; to the edge and it
             * hides.
             */
            function startDrag(event) {
                if (narrowQuery.matches || event.button !== 0) {
                    return;
                }

                event.preventDefault();

                var restore = width;
                var handle = event.currentTarget;

                handle.setPointerCapture && handle.setPointerCapture(event.pointerId);
                root.classList.add('sidebar-dragging');
                root.classList.remove('sidebar-peek');

                var move = function (moveEvent) {
                    var x = moveEvent.clientX;

                    if (x < HIDE_BELOW) {
                        root.classList.add('sidebar-collapsed');
                    } else {
                        root.classList.remove('sidebar-collapsed');
                        setWidth(x);
                    }
                };

                var stop = function () {
                    handle.removeEventListener('pointermove', move);
                    handle.removeEventListener('pointerup', stop);
                    handle.removeEventListener('pointercancel', stop);
                    root.classList.remove('sidebar-dragging');

                    /* Hidden by dragging: it comes back at the size it had. */
                    if (isHidden()) {
                        holdPeek = true;
                        setWidth(restore);
                    }

                    label();
                    remember();
                };

                handle.addEventListener('pointermove', move);
                handle.addEventListener('pointerup', stop);
                handle.addEventListener('pointercancel', stop);
            }

            resizer.addEventListener('pointerdown', startDrag);
            edge.addEventListener('pointerdown', startDrag);

            /* Back to the usual width. */
            resizer.addEventListener('dblclick', function () {
                setWidth(260);
                remember();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && root.classList.contains('sidebar-overlay')) {
                    hide();

                    return;
                }

                if (
                    (event.ctrlKey || event.metaKey)
                    && ! event.altKey
                    && ! event.shiftKey
                    && (event.key === 'b' || event.key === 'B')
                ) {
                    /* Left alone while typing, where it may mean bold. */
                    var target = event.target;

                    if (target && (target.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(target.tagName))) {
                        return;
                    }

                    event.preventDefault();
                    flip();
                }
            });

            if (narrowQuery.addEventListener) {
                narrowQuery.addEventListener('change', function () {
                    if (narrowQuery.matches) {
                        hide();
                    } else {
                        load().collapsed === true ? hide() : show();
                    }
                });
            }

            setWidth(width);
            label();
        })();
    </script>

    <!-- Main Content -->
    <main class="admin-main">
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                {{ session('warning') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        @yield('content')

                <!-- Footer -->
        <footer class="admin-footer">
            <p>&copy; {{ date('Y') }} CareerPath BN &middot; Politeknik Brunei &middot; <a href="https://www.instagram.com/careerpathbn/" target="_blank" rel="noopener" style="color:inherit;"><i class="fab fa-instagram"></i> @@careerpathbn</a></p>
            <div class="admin-footer-logos">
                @if(file_exists(public_path('images/politeknik-logo.png')))
                    <a href="https://www.pb.edu.bn" target="_blank" class="admin-footer-logo-link">
                        <img src="{{ asset('images/politeknik-logo.png') }}" alt="Politeknik Brunei" class="admin-footer-logo">
                    </a>
                @else
                    <a href="https://www.pb.edu.bn" target="_blank" class="admin-footer-logo-fallback">
                        <i class="fas fa-university"></i> Politeknik Brunei
                    </a>
                @endif
                @if(file_exists(public_path('images/biicf-logo.png')))
                    <a href="https://www.biicf.bn" target="_blank" class="admin-footer-logo-link">
                        <img src="{{ asset('images/biicf-logo.png') }}" alt="BIICF" class="admin-footer-logo">
                    </a>
                @else
                    <a href="https://www.biicf.bn" target="_blank" class="admin-footer-logo-fallback">
                        <i class="fas fa-certificate"></i> BIICF
                    </a>
                @endif
                @if(file_exists(public_path('images/aiti-logo.png')))
                    <a href="https://www.aiti.gov.bn" target="_blank" class="admin-footer-logo-link">
                        <img src="{{ asset('images/aiti-logo.png') }}" alt="AITI" class="admin-footer-logo">
                    </a>
                @else
                    <a href="https://www.aiti.gov.bn" target="_blank" class="admin-footer-logo-fallback">
                        <i class="fas fa-satellite-dish"></i> AITI
                    </a>
                @endif
                @if(file_exists(public_path('images/innovation-lab-logo.png')))
                    <a href="https://www.instagram.com/innovationlab.bn/" target="_blank" rel="noopener" class="admin-footer-logo-link">
                        <img src="{{ asset('images/innovation-lab-logo.png') }}" alt="Brunei Innovation Lab" class="admin-footer-logo is-stacked">
                    </a>
                @else
                    <a href="https://www.instagram.com/innovationlab.bn/" target="_blank" rel="noopener" class="admin-footer-logo-fallback">
                        <i class="fas fa-lightbulb"></i> Brunei Innovation Lab
                    </a>
                @endif
            </div>
        </footer>
    </main>

    <!-- Dropdown Toggle JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const wrapper = document.getElementById('navUserWrapper');
            const toggle = document.getElementById('navUserToggle');

            if (wrapper && toggle) {
                toggle.addEventListener('click', function(e) {
                    if (e.target.closest('.dropdown-item') || e.target.closest('form')) {
                        return;
                    }
                    wrapper.classList.toggle('active');
                    e.stopPropagation();
                });

                document.addEventListener('click', function(e) {
                    if (!wrapper.contains(e.target)) {
                        wrapper.classList.remove('active');
                    }
                });

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        wrapper.classList.remove('active');
                    }
                });
            }
        });

        // Logout confirmation using the existing modal system
       function confirmLogout() {
    if (typeof showConfirmModal === 'function') {
        showConfirmModal({
            title: 'Confirm Logout',
            message: 'Are you sure you want to log out?',
            confirmText: 'Yes, Log Out',
            cancelText: 'Cancel',
            type: 'warning',
            onConfirm: function() {
                document.getElementById('logout-form').submit();
            }
        });
    } else {
        if (confirm('Are you sure you want to log out?')) {
            document.getElementById('logout-form').submit();
        }
    }
}
    </script>
    
<script>
    /*
     * Popup confirmation for any admin form marked with data-confirm.
     * Runs before other submit handlers (capture phase), so nothing
     * starts until the admin says yes.
     */
    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (! form.matches || ! form.matches('form[data-confirm]')) {
            return;
        }

        if (form.dataset.confirmApproved === '1') {
            delete form.dataset.confirmApproved;
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        var message = form.dataset.confirm;

        var approve = function () {
            form.dataset.confirmApproved = '1';

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        };

        if (typeof window.showConfirmModal !== 'function') {
            if (window.confirm(message)) {
                approve();
            }

            return;
        }

        /* Names come from users, so show them as plain text only. */
        var safe = document.createElement('div');
        safe.textContent = message;

        window.showConfirmModal({
            title: form.dataset.confirmTitle || 'Please Confirm',
            message: safe.innerHTML,
            confirmText: form.dataset.confirmOk || 'Yes, Continue',
            cancelText: 'Cancel',
            type: form.dataset.confirmType || 'danger',
            onConfirm: approve,
        });
    }, true);
</script>

<!-- Hidden Logout Form -->
<form method="POST" action="{{ route('logout') }}" id="logout-form" style="display:none;">
    @csrf
</form>
    @include('partials.long-text')
</body>
</html>