<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up — CareerPath BN</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=2" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=2">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

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
            --radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        .container { max-width: 480px; margin: 0 auto; padding: 0 24px; }

        /*
         * Written as a class rather than inline, because a media
         * query cannot reach an inline style — which is why the
         * logo stayed at its desktop height on a phone and took
         * most of the band with it.
         */
        .auth-logo {
            height: 44px;
            width: auto;
            display: block;
        }

        .auth-header {
            padding: 20px 0;
            background: white;
            border-bottom: 1px solid var(--border);
        }

        .auth-header .container {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
            font-size: 18px;
        }

        .logo-text {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 20px;
            color: var(--primary);
        }

        .logo-text span { color: var(--accent); }

        .auth-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }

        .auth-card {
            background: var(--card);
            border-radius: var(--radius);
            padding: 32px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            width: 100%;
        }

        .auth-card .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--text-muted);
            font-size: 13px;
            text-decoration: none;
            margin-bottom: 16px;
            transition: var(--transition);
        }

        .auth-card .back-link:hover { color: var(--primary); }

        .auth-card .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 600;
            color: var(--accent);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .auth-card h1 {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .auth-card .subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 20px;
        }

        .field { margin-bottom: 14px; }

        .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .field label .required {
            color: #c0392b;
        }

        .field .input-wrapper {
            position: relative;
        }

        .field .input-wrapper input,
        .field .input-wrapper select {
            width: 100%;
            padding: 10px 44px 10px 14px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: var(--transition);
            background: white;
            appearance: none;
            -webkit-appearance: none;
        }

        .field .input-wrapper input:focus,
        .field .input-wrapper select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(26, 58, 92, 0.08);
        }

        .field .input-wrapper input::placeholder {
            color: #9ca3af;
        }

        .field .input-wrapper select { cursor: pointer; }

        .field .input-wrapper .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 4px;
            font-size: 16px;
            transition: var(--transition);
        }

        .field .input-wrapper .toggle-password:hover {
            color: var(--primary);
        }

        .field .input-wrapper .toggle-password i {
            pointer-events: none;
        }

        .field .error {
            color: #c0392b;
            font-size: 12px;
            margin-top: 4px;
        }

        .field .error-input {
            border-color: #c0392b !important;
            box-shadow: 0 0 0 4px rgba(192, 57, 43, 0.08) !important;
        }

        .name-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .checkbox-field {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 16px 0 20px;
        }

        .checkbox-field input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            accent-color: var(--primary);
            cursor: pointer;
            flex-shrink: 0;
        }

        .checkbox-field label {
            font-size: 13px;
            color: var(--text-muted);
            cursor: pointer;
            line-height: 1.5;
        }

        .checkbox-field label a {
            color: var(--primary);
            font-weight: 500;
            text-decoration: none;
        }

        .checkbox-field label a:hover { color: var(--accent); }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            font-family: inherit;
            width: 100%;
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

        .auth-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }

        .auth-footer p {
            font-size: 13px;
            color: var(--text-muted);
        }

        .auth-footer a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
        }

        .auth-footer a:hover { color: var(--accent); }

        .status {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .status-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /*
         * A tablet-width step, which was missing entirely: below
         * this the header band, the logo and the space around
         * the card all kept their desktop sizing, so the page
         * was mostly chrome before any of the form appeared.
         */
        @media (max-width: 768px) {
            .auth-header { padding: 14px 0; }
            .auth-logo { height: 34px; }
            .auth-main { padding: 28px 0; }
            .container { padding: 0 18px; }
        }

        @media (max-width: 480px) {
            .auth-header { padding: 10px 0; }
            .auth-logo { height: 28px; }
            .auth-main { padding: 18px 0; }
            .container { padding: 0 14px; }
            .auth-card { padding: 24px 16px; }
            .auth-card h1 { font-size: 22px; }
            .name-row { grid-template-columns: 1fr; }
        }

        /* Registration only: enhanced programme dropdown (uses the existing select). */
        .cpbn-programme-picker { position: relative; width: 100%; }

        .cpbn-programme-picker__trigger {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            width: 100%;
            min-height: 46px;
            padding: 10px 13px 10px 14px;
            border: 2px solid var(--border);
            border-radius: 8px;
            background: #fff;
            color: var(--text-muted);
            text-align: left;
            font: 500 14px/1.4 'Inter', sans-serif;
            cursor: pointer;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .cpbn-programme-picker__trigger:hover { border-color: #b7c7d8; }
        .cpbn-programme-picker__trigger:focus-visible,
        .cpbn-programme-picker.is-open .cpbn-programme-picker__trigger {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(201, 168, 76, .14);
        }
        .cpbn-programme-picker__value {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }
        .cpbn-programme-picker__value-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .cpbn-programme-picker__trigger.has-value { color: var(--text); }
        .cpbn-programme-picker__chevron {
            flex-shrink: 0;
            color: var(--primary);
            font-size: 12px;
            transition: transform .2s ease;
        }
        .cpbn-programme-picker.is-open .cpbn-programme-picker__chevron { transform: rotate(180deg); }

        .cpbn-programme-picker__menu {
            position: absolute;
            top: calc(100% + 7px);
            left: 0;
            right: 0;
            z-index: 30;
            overflow: hidden;
            padding: 10px;
            border: 1px solid #dae3eb;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 18px 42px rgba(13, 31, 51, .18), 0 3px 10px rgba(13, 31, 51, .07);
        }
        .cpbn-programme-picker__menu[hidden] { display: none; }
        .cpbn-programme-picker__search-wrap { position: relative; }
        .cpbn-programme-picker__search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #7b8da0;
            font-size: 13px;
            pointer-events: none;
        }
        .field .input-wrapper .cpbn-programme-picker__search {
            display: block;
            width: 100%;
            height: 40px;
            padding: 9px 12px 9px 35px;
            border: 1px solid #dce3ec;
            border-radius: 8px;
            background: #f8fafc;
            font: 400 13px 'Inter', sans-serif;
            color: var(--text);
            box-shadow: none;
        }
        .field .input-wrapper .cpbn-programme-picker__search:focus {
            outline: none;
            border-color: var(--accent);
            background: white;
            box-shadow: 0 0 0 3px rgba(201, 168, 76, .12);
        }
        .cpbn-programme-picker__heading {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            padding: 12px 8px 7px;
            color: #78889b;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
        }
        .cpbn-programme-picker__list {
            max-height: min(310px, 44vh);
            overflow-y: auto;
            overscroll-behavior: contain;
            scrollbar-width: thin;
            scrollbar-color: #c5d1dc transparent;
        }
        /* School dividers reuse the registration page's navy and gold palette. */
        .cpbn-programme-picker__group {
            margin: 11px 2px 6px;
            padding: 7px 9px;
            border-left: 3px solid var(--accent);
            border-radius: 5px;
            background: #f2f5f9;
            color: var(--primary);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .035em;
            line-height: 1.5;
        }
        .cpbn-programme-picker__group:first-child { margin-top: 2px; }
        .cpbn-programme-picker__option {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            min-height: 43px;
            margin: 2px 0;
            padding: 8px 9px;
            border: 1px solid transparent;
            border-radius: 8px;
            background: transparent;
            color: var(--text);
            text-align: left;
            font: 500 12px/1.45 'Inter', sans-serif;
            cursor: pointer;
            transition: background-color .15s ease, border-color .15s ease;
        }
        .cpbn-programme-picker__option:hover,
        .cpbn-programme-picker__option:focus-visible {
            outline: none;
            background: #edf3f9;
            border-color: #dce6f1;
        }
        .cpbn-programme-picker__option[aria-selected="true"] {
            color: var(--primary);
            background: #f6f3e8;
            border-color: #e7d9b3;
        }
        .cpbn-programme-picker__code {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 45px;
            padding: 4px 6px;
            border-radius: 6px;
            background: #eaf0f7;
            color: var(--primary);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .02em;
        }
        .cpbn-programme-picker__option[aria-selected="true"] .cpbn-programme-picker__code {
            background: #e9d9ac;
        }
        .cpbn-programme-picker__option-name { flex: 1; min-width: 0; }
        .cpbn-programme-picker__check { color: var(--accent-dark); font-size: 12px; }
        .cpbn-programme-picker__empty {
            padding: 18px 10px;
            color: var(--text-muted);
            font-size: 12px;
            text-align: center;
        }
        @media (max-width: 480px) {
            .cpbn-programme-picker__menu { padding: 8px; }
            .cpbn-programme-picker__option { font-size: 11.5px; }
            .cpbn-programme-picker__list { max-height: min(280px, 42vh); }
        }
    </style>
    @include('partials.motion', ['reveal' => false])
    @include('partials.auth-theme')
</head>
<body>
    @include('partials.auth-backdrop')

    <header class="auth-header">
        <div class="container">
            <a href="{{ url('/') }}" class="logo">
    <img
        src="{{ asset('images/careerpath-logo-v2.png') }}?v={{ filemtime(public_path('images/careerpath-logo-v2.png')) }}"
        alt="CareerPath BN"
        class="auth-logo"
    >
</a>
        </div>
    </header>

    <main class="auth-main">
        <div class="auth-shell">
            <div class="auth-card">
                <a href="{{ url('/') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>

                <div class="badge"><i class="fas fa-user-plus"></i> Get Started</div>
                <h1>Create Account</h1>
                <p class="subtitle">Start your career discovery journey with BIICF.</p>

                @if ($errors->any())
                    <div class="status status-error">
                        Please fix the errors below.
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                        <div class="field">
                        <label>Full Name (as on IC) <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input type="text" name="name" value="{{ old('name') }}"
                                   placeholder="Nur Aisyah binti Hassan" required
                                   maxlength="255" autocomplete="name"
                                   class="{{ $errors->has('name') ? 'error-input' : '' }}">
                        </div>
                        @error('name')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label>Email <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input type="email" name="email" value="{{ old('email') }}"
                                   placeholder="24FTT2803@student.pb.edu.bn" required
                                   class="{{ $errors->has('email') ? 'error-input' : '' }}">
                        </div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">
                            <i class="fas fa-info-circle"></i>
                            Use your Politeknik Brunei email. A student.pb.edu.bn
                            address becomes your verified Student ID. We will email
                            you a link to authenticate before your account is created.
                        </div>
                        @error('email')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label>Programme <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <div class="cpbn-programme-picker" id="cpbn-registration-programme">
                                <x-programme-select :selected="null" />
                            </div>
                        </div>
                        @error('programme')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label>Password <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input type="password" name="password" id="password"
                                   placeholder="Min. 8 characters" required
                                   class="{{ $errors->has('password') ? 'error-input' : '' }}">
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label>Confirm Password <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   placeholder="Confirm your password" required>
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password_confirmation', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="checkbox-field">
                        <input type="checkbox" name="terms" id="terms" required {{ old('terms') ? 'checked' : '' }}>
                        <label for="terms">
                            I agree to the <a href="{{ route('terms') }}" target="_blank">Terms of Service</a>
                            and <a href="{{ route('privacy') }}" target="_blank">Privacy Policy</a>
                            <span class="required">*</span>
                        </label>
                    </div>
                    @error('terms')
                        <div class="error" style="margin-top:-12px;margin-bottom:12px;">{{ $message }}</div>
                    @enderror

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-rocket"></i> Create Account
                    </button>
                </form>

                <div class="auth-footer">
                    <p>Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
                </div>
            </div>

            @include('partials.auth-visual', ['scene' => 'register'])
        </div>
    </main>

    <script>

        // Visual-only enhancement. The original <select name="programme"> remains
        // the source of options and the field submitted to Laravel.
        (() => {
            const picker = document.getElementById('cpbn-registration-programme');
            const select = picker?.querySelector('select[name="programme"]');
            if (!picker || !select) return;

            const entries = Array.from(select.options)
                .filter(option => option.value !== '')
                .map(option => {
                    const text = option.textContent.trim().replace(/\s+/g, ' ');
                    const delimiter = text.indexOf(' — ');
                    return {
                        value: option.value,
                        code: delimiter >= 0 ? text.slice(0, delimiter) : '',
                        name: delimiter >= 0 ? text.slice(delimiter + 3) : text,
                        group: option.parentElement?.tagName === 'OPTGROUP'
                            ? option.parentElement.label
                            : 'Other programmes',
                        search: [text, option.parentElement?.label || ''].join(' ').toLocaleLowerCase(),
                    };
                });
            if (!entries.length) return;

            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'cpbn-programme-picker__trigger';
            trigger.setAttribute('aria-label', 'Select your programme');
            trigger.setAttribute('aria-expanded', 'false');
            trigger.setAttribute('aria-haspopup', 'listbox');
            trigger.setAttribute('aria-controls', 'cpbn-registration-programme-list');

            const valueBox = document.createElement('span');
            valueBox.className = 'cpbn-programme-picker__value';
            const valueName = document.createElement('span');
            valueName.className = 'cpbn-programme-picker__value-name';
            valueBox.append(valueName);
            const chevron = document.createElement('i');
            chevron.className = 'fas fa-chevron-down cpbn-programme-picker__chevron';
            chevron.setAttribute('aria-hidden', 'true');
            trigger.append(valueBox, chevron);

            const menu = document.createElement('div');
            menu.className = 'cpbn-programme-picker__menu';
            menu.hidden = true;
            const searchWrap = document.createElement('div');
            searchWrap.className = 'cpbn-programme-picker__search-wrap';
            const searchIcon = document.createElement('i');
            searchIcon.className = 'fas fa-search cpbn-programme-picker__search-icon';
            searchIcon.setAttribute('aria-hidden', 'true');
            const search = document.createElement('input');
            search.type = 'search';
            search.className = 'cpbn-programme-picker__search';
            search.placeholder = 'Search by programme or code...';
            search.setAttribute('aria-label', 'Search programmes');
            search.autocomplete = 'off';
            searchWrap.append(searchIcon, search);

            const heading = document.createElement('div');
            heading.className = 'cpbn-programme-picker__heading';
            const headingName = document.createElement('span');
            headingName.textContent = 'Available programmes';
            const count = document.createElement('span');
            heading.append(headingName, count);
            const list = document.createElement('div');
            list.className = 'cpbn-programme-picker__list';
            list.id = 'cpbn-registration-programme-list';
            list.setAttribute('role', 'listbox');
            list.setAttribute('aria-label', 'Available programmes');
            const empty = document.createElement('div');
            empty.className = 'cpbn-programme-picker__empty';
            empty.textContent = 'No matching programmes found.';
            empty.hidden = true;
            menu.append(searchWrap, heading, list, empty);

            function updateTrigger() {
                const current = entries.find(entry => entry.value === select.value);
                valueBox.replaceChildren();
                if (current?.code) {
                    const code = document.createElement('span');
                    code.className = 'cpbn-programme-picker__code';
                    code.textContent = current.code;
                    valueBox.append(code);
                }
                valueName.textContent = current?.name || select.options[0]?.textContent.trim() || 'Select your programme';
                valueBox.append(valueName);
                trigger.classList.toggle('has-value', Boolean(current));
            }

            function renderOptions() {
                const term = search.value.trim().toLocaleLowerCase();
                const filtered = entries.filter(entry => entry.search.includes(term));
                count.textContent = String(filtered.length);
                list.replaceChildren();
                empty.hidden = filtered.length > 0;
                let lastGroup = null;
                for (const entry of filtered) {
                    if (entry.group !== lastGroup) {
                        const groupHeading = document.createElement('div');
                        groupHeading.className = 'cpbn-programme-picker__group';
                        groupHeading.textContent = entry.group;
                        groupHeading.setAttribute('role', 'presentation');
                        list.append(groupHeading);
                        lastGroup = entry.group;
                    }
                    const option = document.createElement('button');
                    option.type = 'button';
                    option.className = 'cpbn-programme-picker__option';
                    option.setAttribute('role', 'option');
                    option.setAttribute('aria-selected', String(entry.value === select.value));
                    if (entry.code) {
                        const code = document.createElement('span');
                        code.className = 'cpbn-programme-picker__code';
                        code.textContent = entry.code;
                        option.append(code);
                    }
                    const name = document.createElement('span');
                    name.className = 'cpbn-programme-picker__option-name';
                    name.textContent = entry.name;
                    option.append(name);
                    if (entry.value === select.value) {
                        const check = document.createElement('i');
                        check.className = 'fas fa-check cpbn-programme-picker__check';
                        check.setAttribute('aria-hidden', 'true');
                        option.append(check);
                    }
                    option.addEventListener('click', () => {
                        select.value = entry.value;
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                        closeMenu();
                        trigger.focus();
                    });
                    list.append(option);
                }
            }

            function openMenu() {
                menu.hidden = false;
                picker.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
                search.value = '';
                renderOptions();
                search.focus();
            }
            function closeMenu() {
                menu.hidden = true;
                picker.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
            }

            trigger.addEventListener('click', () => menu.hidden ? openMenu() : closeMenu());
            trigger.addEventListener('keydown', event => {
                if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openMenu();
                }
            });
            search.addEventListener('input', renderOptions);
            search.addEventListener('keydown', event => {
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    list.querySelector('.cpbn-programme-picker__option')?.focus();
                } else if (event.key === 'Enter') {
                    event.preventDefault();
                    list.querySelector('.cpbn-programme-picker__option')?.click();
                }
            });
            list.addEventListener('keydown', event => {
                const options = Array.from(list.querySelectorAll('.cpbn-programme-picker__option'));
                const index = options.indexOf(document.activeElement);
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    options[Math.min(index + 1, options.length - 1)]?.focus();
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    if (index === 0) search.focus();
                    else options[Math.max(index - 1, 0)]?.focus();
                }
            });
            picker.addEventListener('keydown', event => {
                if (event.key === 'Escape' && !menu.hidden) {
                    event.preventDefault();
                    closeMenu();
                    trigger.focus();
                }
            });
            document.addEventListener('pointerdown', event => {
                if (!picker.contains(event.target)) closeMenu();
            });
            document.addEventListener('focusin', event => {
                if (!picker.contains(event.target)) closeMenu();
            });
            select.addEventListener('change', () => {
                updateTrigger();
                if (!menu.hidden) renderOptions();
            });
            select.form?.addEventListener('reset', () => {
                window.requestAnimationFrame(() => {
                    updateTrigger();
                    closeMenu();
                });
            });

            // Hide the original control only after the UI is fully prepared;
            // the original select still submits the exact same option value.
            picker.append(trigger, menu);
            select.hidden = true;
            select.style.display = 'none';
            updateTrigger();
        })();

        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>

</body>
</html>