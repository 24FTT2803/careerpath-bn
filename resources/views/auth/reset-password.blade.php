<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password — CareerPath BN</title>

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
            --bg: #f4f6f9;
            --card: #ffffff;
            --text: #1a1a2e;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --shadow: 0 4px 24px rgba(26, 58, 92, 0.08);
            --radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --success: #2d8f5c;
            --danger: #c0392b;
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

        .container { max-width: 440px; margin: 0 auto; padding: 0 24px; }

        .auth-header {
            padding: 24px 0;
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
            padding: 48px 0;
        }

        .auth-card {
            background: var(--card);
            border-radius: var(--radius);
            padding: 40px 32px;
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
            margin-bottom: 20px;
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
            margin-bottom: 8px;
        }

        .auth-card h1 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .auth-card .subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .field { margin-bottom: 16px; }

        .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .field label .required { color: var(--danger); }

        .field .input-wrapper { position: relative; }

        .field .input-wrapper input {
            width: 100%;
            padding: 12px 44px 12px 14px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: var(--transition);
            background: white;
        }

        .field .input-wrapper input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(26, 58, 92, 0.08);
        }

        .field .input-wrapper input::placeholder { color: #9ca3af; }

        .field .input-wrapper input:disabled {
            background: #f3f4f6;
            color: var(--text-muted);
            cursor: not-allowed;
        }

        .field .input-wrapper .input-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 15px;
            pointer-events: none;
        }

        .field .error {
            color: var(--danger);
            font-size: 12px;
            margin-top: 4px;
        }

        .field .error-input {
            border-color: var(--danger) !important;
            box-shadow: 0 0 0 4px rgba(192, 57, 43, 0.08) !important;
        }

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
            padding-top: 20px;
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

        /* ============================================
           PASSWORD REQUIREMENTS CHECKLIST
           ============================================ */

        .password-rules {
            list-style: none;
            margin: 8px 0 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .password-rules li {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: var(--text-muted);
            transition: color 0.2s ease;
        }

        .password-rules li .rule-icon {
            width: 14px;
            height: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            flex-shrink: 0;
            color: #d1d5db;
            transition: color 0.2s ease;
        }

        .password-rules li.met .rule-icon {
            color: var(--success);
        }

        .password-rules li.met {
            color: var(--success);
        }

        /* ============================================
           CONFIRMATION MATCH INDICATOR
           ============================================ */

        .match-indicator {
            display: none;
            align-items: center;
            gap: 6px;
            margin-top: 6px;
            font-size: 12px;
        }

        .match-indicator.shown { display: flex; }

        .match-indicator.match {
            color: var(--success);
        }

        .match-indicator.mismatch {
            color: var(--danger);
        }

        .match-indicator i {
            font-size: 12px;
        }

        @media (max-width: 480px) {
            .auth-card { padding: 28px 20px; }
            .auth-card h1 { font-size: 24px; }
        }
    </style>
</head>
<body>

    <header class="auth-header">
        <div class="container">
            <a href="{{ url('/') }}" class="logo">
                                <img
                    src="{{ asset('images/careerpath-logo-v2.png') }}?v={{ filemtime(public_path('images/careerpath-logo-v2.png')) }}"
                    alt="CareerPath BN"
                    style="height: 60px; width: auto; display: block;"
                >
            </a>
        </div>
    </header>

    <main class="auth-main">
        <div class="container">
            <div class="auth-card">
                <a href="{{ route('login') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Log In
                </a>

                <div class="badge"><i class="fas fa-key"></i> Account Recovery</div>
                <h1>Choose a New Password</h1>
                <p class="subtitle">Make it something you haven't used before.</p>

                @if ($errors->any())
                    <div class="status status-error">
                        @foreach ($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('password.store') }}" id="resetPasswordForm">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    @if($request->email)
                        <div class="field">
                            <label>Email</label>
                            <div class="input-wrapper">
                                <input
                                    type="email"
                                    value="{{ $request->email }}"
                                    disabled
                                >
                                <i class="fas fa-envelope input-icon"></i>
                            </div>
                        </div>

                        {{-- The visible input is disabled, so the value has to be submitted separately. --}}
                        <input type="hidden" name="email" value="{{ $request->email }}">
                    @else
                        <div class="field">
                            <label for="email">Email <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="you@pb.edu.bn"
                                    required
                                    autofocus
                                    class="{{ $errors->has('email') ? 'error-input' : '' }}"
                                >
                                <i class="fas fa-envelope input-icon"></i>
                            </div>
                            @error('email')<div class="error">{{ $message }}</div>@enderror
                        </div>
                    @endif

                    <div class="field">
                        <label for="password">New Password <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="••••••••"
                                required
                                autocomplete="new-password"
                                class="{{ $errors->has('password') ? 'error-input' : '' }}"
                            >
                            <i class="fas fa-lock input-icon"></i>
                        </div>

                        {{-- Live checklist. Mirrors what the server enforces. --}}
                        <ul class="password-rules" id="passwordRules">
                            <li data-rule="length">
                                <span class="rule-icon"><i class="fas fa-circle"></i></span>
                                <span>At least 8 characters</span>
                            </li>
                        </ul>

                        @error('password')<div class="error">{{ $message }}</div>@enderror
                    </div>

                    <div class="field" style="margin-bottom: 24px;">
                        <label for="password_confirmation">Confirm New Password <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                placeholder="••••••••"
                                required
                                autocomplete="new-password"
                            >
                            <i class="fas fa-lock input-icon"></i>
                        </div>

                        {{-- Real-time match feedback. --}}
                        <div class="match-indicator" id="matchIndicator">
                            <i class="fas fa-check-circle" id="matchIcon"></i>
                            <span id="matchText"></span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Reset Password
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var passwordInput = document.getElementById('password');
            var confirmInput = document.getElementById('password_confirmation');
            var rulesList = document.getElementById('passwordRules');
            var matchIndicator = document.getElementById('matchIndicator');
            var matchIcon = document.getElementById('matchIcon');
            var matchText = document.getElementById('matchText');

            /*
             * Each rule here must match what the server enforces
             * in NewPasswordController. Currently that is
             * Laravel's Password::defaults(), which requires
             * eight characters and nothing else.
             *
             * If you ever tighten the server rule, add a matching
             * entry here so the user still sees the change live.
             */
            var rules = {
                length: function (value) {
                    return value.length >= 8;
                }
            };

            function updateRules() {
                var value = passwordInput.value;

                Object.keys(rules).forEach(function (key) {
                    var li = rulesList.querySelector(
                        '[data-rule="' + key + '"]'
                    );

                    if (!li) {
                        return;
                    }

                    var passes = rules[key](value);

                    li.classList.toggle('met', passes);
                });
            }

            function updateMatch() {
                var password = passwordInput.value;
                var confirmation = confirmInput.value;

                if (confirmation === '') {
                    matchIndicator.classList.remove('shown');
                    return;
                }

                matchIndicator.classList.add('shown');

                if (password === confirmation) {
                    matchIndicator.classList.add('match');
                    matchIndicator.classList.remove('mismatch');
                    matchIcon.className = 'fas fa-check-circle';
                    matchText.textContent = 'Passwords match';
                } else {
                    matchIndicator.classList.add('mismatch');
                    matchIndicator.classList.remove('match');
                    matchIcon.className = 'fas fa-times-circle';
                    matchText.textContent = 'Passwords do not match';
                }
            }

            passwordInput.addEventListener('input', function () {
                updateRules();
                updateMatch();
            });

            confirmInput.addEventListener('input', updateMatch);
        });
    </script>

</body>
</html>