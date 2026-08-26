<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log In — CareerPath BN</title>

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

        /* Header */
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

        /* Main */
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
        }

        .field {
            margin-bottom: 16px;
        }

        .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .field input {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: var(--transition);
            background: white;
        }

        .field input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(26, 58, 92, 0.08);
        }

        .field input::placeholder {
            color: #9ca3af;
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

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 20px 0;
        }

        .form-options label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text-muted);
            cursor: pointer;
        }

        .form-options label input[type="checkbox"] {
            accent-color: var(--primary);
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .form-options a {
            color: var(--accent);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: var(--transition);
        }

        .form-options a:hover { color: var(--accent-dark); }

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

        /* Status message */
        .status {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .status-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 480px) {
            .auth-card { padding: 28px 20px; }
            .auth-card h1 { font-size: 24px; }
            .form-options { flex-direction: column; gap: 12px; align-items: flex-start; }
        }
    </style>
</head>
<body>

    <header class="auth-header">
        <div class="container">
            <a href="{{ url('/') }}" class="logo">
                <div class="logo-icon"><i class="fas fa-compass"></i></div>
                <span class="logo-text">CareerPath <span>BN</span></span>
            </a>
        </div>
    </header>

    <main class="auth-main">
        <div class="container">
            <div class="auth-card">
                <a href="{{ url('/') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>

                <div class="badge"><i class="fas fa-key"></i> Authentication</div>
                <h1>Welcome Back</h1>
                <p class="subtitle">Log in to continue your career journey.</p>

                @if (session('status'))
                    <div class="status status-success">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="status status-error">
                        @foreach ($errors->all() as $error)
                            {{ $error }}
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                               placeholder="you@pb.edu.bn" required autofocus
                               class="{{ $errors->has('email') ? 'error-input' : '' }}">
                        @error('email')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password"
                               placeholder="••••••••" required
                               class="{{ $errors->has('password') ? 'error-input' : '' }}">
                        @error('password')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-options">
                        <label>
                            <input type="checkbox" name="remember" id="remember_me">
                            Remember me
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">Forgot password?</a>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Log In
                    </button>
                </form>

                <div class="auth-footer">
                    <p>Don't have an account? <a href="{{ route('register') }}">Sign up</a></p>
                </div>
            </div>
        </div>
    </main>

</body>
</html>