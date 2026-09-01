<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up — CareerPath BN</title>

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

        @media (max-width: 480px) {
            .auth-card { padding: 24px 16px; }
            .auth-card h1 { font-size: 22px; }
            .name-row { grid-template-columns: 1fr; }
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

                    <div class="name-row">
                        <div class="field">
                            <label>First Name <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <input type="text" name="first_name" value="{{ old('first_name') }}"
                                       placeholder="Ahmad" required
                                       class="{{ $errors->has('first_name') ? 'error-input' : '' }}">
                            </div>
                            @error('first_name')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label>Last Name <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <input type="text" name="last_name" value="{{ old('last_name') }}"
                                       placeholder="Bin Abdullah" required
                                       class="{{ $errors->has('last_name') ? 'error-input' : '' }}">
                            </div>
                            @error('last_name')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="field">
                        <label>Email <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input type="email" name="email" value="{{ old('email') }}"
                                   placeholder="you@pb.edu.bn" required
                                   class="{{ $errors->has('email') ? 'error-input' : '' }}">
                        </div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">
                            <i class="fas fa-info-circle"></i>
                            Use your gmail.com, pb.edu.bn, or student.pb.edu.bn email
                        </div>
                        @error('email')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label>Programme <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <select name="programme" required class="{{ $errors->has('programme') ? 'error-input' : '' }}">
                                <option value="">Select your programme</option>
                                <option value="Diploma in ICT (Application Development)" {{ old('programme') == 'Diploma in ICT (Application Development)' ? 'selected' : '' }}>
                                    DADT - Application Development
                                </option>
                                <option value="Diploma in ICT (Data Analytics)" {{ old('programme') == 'Diploma in ICT (Data Analytics)' ? 'selected' : '' }}>
                                    DDAT - Data Analytics
                                </option>
                                <option value="Diploma in ICT (Cloud Networking)" {{ old('programme') == 'Diploma in ICT (Cloud Networking)' ? 'selected' : '' }}>
                                    DCNG - Cloud Networking
                                </option>
                                <option value="Diploma in Business Information Systems" {{ old('programme') == 'Diploma in Business Information Systems' ? 'selected' : '' }}>
                                    DBIS - Business Information Systems
                                </option>
                            </select>
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
        </div>
    </main>

    <script>
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