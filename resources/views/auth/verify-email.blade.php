<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirm Your Email — CareerPath BN</title>
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
            --accent: #c9a84c;
            --bg: #f4f6f9;
            --card: #ffffff;
            --text: #1a1a2e;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --shadow: 0 4px 24px rgba(26, 58, 92, 0.08);
            --radius: 12px;
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

        .container {
            max-width: 480px;
            margin: 0 auto;
            padding: 0 24px;
        }

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

        .auth-logo {
            height: 44px;
            width: auto;
            display: block;
        }

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
            text-align: center;
        }

        .verify-mark {
            width: 56px;
            height: 56px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(201, 168, 76, 0.14);
            color: var(--accent);
            font-size: 22px;
        }

        .auth-card h1 {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .auth-card p {
            font-size: 14px;
            line-height: 1.7;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .verify-address {
            font-weight: 600;
            color: var(--text);
            word-break: break-all;
        }

        .notice {
            margin: 18px 0 0;
            padding: 12px 16px;
            border-radius: 8px;
            border-left: 3px solid #2f855a;
            background: #f0fdf4;
            font-size: 13px;
            line-height: 1.6;
            color: #276749;
            text-align: left;
        }

        .actions {
            margin-top: 26px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-primary {
            display: inline-block;
            width: 100%;
            padding: 13px 20px;
            border: none;
            border-radius: 10px;
            background: var(--primary);
            color: white;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .btn-primary:hover {
            background: var(--primary-light);
        }

        .btn-plain {
            background: none;
            border: none;
            color: var(--text-muted);
            font-family: inherit;
            font-size: 13px;
            cursor: pointer;
            text-decoration: underline;
        }

        .btn-plain:hover {
            color: var(--primary);
        }

        .hint {
            margin-top: 22px;
            font-size: 12px;
            line-height: 1.6;
            color: var(--text-muted);
        }

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
            .auth-card { padding: 28px 20px; }
            .auth-card h1 { font-size: 22px; }
        }
    </style>
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

                <div class="verify-mark">
                    <i class="fas fa-envelope-open-text"></i>
                </div>

                <h1>Confirm your email</h1>

                <p>
                    We have sent a confirmation link to
                    <span class="verify-address">{{ auth()->user()->email }}</span>.
                    Open it to finish setting up your account.
                </p>

                @if (session('status') === 'verification-link-sent')
                    <div class="notice">
                        <i class="fas fa-circle-check"></i>
                        A new link is on its way. It can take a
                        minute or two to arrive.
                    </div>
                @endif

                <div class="actions">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf

                        <button type="submit" class="btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            Send the link again
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit" class="btn-plain">
                            Sign out
                        </button>
                    </form>
                </div>

                <p class="hint">
                    Nothing in your inbox? Check the spam folder,
                    and make sure
                    <span class="verify-address">{{ auth()->user()->email }}</span>
                    is the address you meant to use.
                </p>

            </div>

            @include('partials.auth-visual', ['scene' => 'verify'])
        </div>
    </main>

</body>
</html>
