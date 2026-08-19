<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register — CareerPath BN</title>

    @fonts

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        :root{
            --ink:#0d1a2b;
            --ink-2:#132540;
            --ink-3:#1c3355;
            --paper:#f5f1e6;
            --paper-dim:#c7c2b4;
            --gold:#cf9a3d;
            --gold-bright:#e9b95a;
            --rose:#c65b4e;
            --line:rgba(245,241,230,0.14);
            --danger:#e08a7d;
            --success:#8fbf8a;
            --font-display:'Fraunces', Georgia, serif;
            --font-body:'IBM Plex Sans', ui-sans-serif, system-ui, sans-serif;
            --font-mono:'IBM Plex Mono', ui-monospace, monospace;
        }

        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{
            background:var(--ink);
            color:var(--paper);
            font-family:var(--font-body);
            font-size:16px;
            line-height:1.6;
            -webkit-font-smoothing:antialiased;
            min-height:100vh;
            display:flex;
            flex-direction:column;
        }
        a{color:inherit;text-decoration:none}
        .wrap{max-width:1180px;margin-inline:auto;padding-inline:28px}

        .reveal{opacity:0;transform:translateY(14px);animation:reveal .7s cubic-bezier(.2,.7,.2,1) forwards}
        @keyframes reveal{to{opacity:1;transform:translateY(0)}}
        @media (prefers-reduced-motion:reduce){.reveal{animation:none;opacity:1;transform:none}}

        header.site{
            border-bottom:1px solid var(--line);
            background:rgba(13,26,43,0.86);
            backdrop-filter:blur(10px);
        }
        .nav{display:flex;align-items:center;justify-content:space-between;padding-block:16px}
        .brand{display:flex;align-items:center;gap:10px;font-family:var(--font-display);font-size:20px;font-weight:600}
        .brand-mark{width:30px;height:30px;flex-shrink:0}
        .brand small{display:block;font-family:var(--font-mono);font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:var(--gold-bright);font-weight:400;margin-top:1px}
        .nav-actions a{font-size:14px;color:var(--paper-dim);transition:color .2s}
        .nav-actions a:hover{color:var(--paper)}

        .auth-main{flex:1;display:flex;align-items:center;justify-content:center;padding:64px 20px}
        .auth-card{
            width:100%;max-width:480px;
            background:var(--ink-2);border:1px solid var(--line);border-radius:6px;
            padding:44px 40px;
        }
        .eyebrow{
            display:inline-flex;align-items:center;gap:8px;
            font-family:var(--font-mono);font-size:11.5px;letter-spacing:.12em;text-transform:uppercase;
            color:var(--gold-bright);margin-bottom:16px;
        }
        .eyebrow::before{content:'';width:16px;height:1px;background:var(--gold-bright)}
        .auth-card h1{font-family:var(--font-display);font-size:28px;font-weight:600;margin-bottom:6px}
        .auth-card .lede{color:var(--paper-dim);font-size:14px;margin-bottom:28px}

        .status{
            background:rgba(143,191,138,0.1);border:1px solid rgba(143,191,138,0.35);color:var(--success);
            padding:12px 14px;border-radius:3px;font-size:13.5px;margin-bottom:22px;
        }

        .name-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .field{margin-bottom:18px}
        .field label{
            display:block;font-family:var(--font-mono);font-size:11px;letter-spacing:.08em;text-transform:uppercase;
            color:var(--paper-dim);margin-bottom:8px;
        }
        .field input, .field select{
            width:100%;background:var(--ink);border:1px solid var(--line);color:var(--paper);
            padding:12px 14px;border-radius:3px;font-size:14.5px;font-family:var(--font-body);
            transition:border-color .2s;
            appearance:none;
            -webkit-appearance:none;
        }
        .field input:focus, .field select:focus{outline:none;border-color:var(--gold-bright)}
        .field input::placeholder{color:rgba(199,194,180,0.45)}
        .field select option{background:var(--ink);color:var(--paper)}
        .err{color:var(--danger);font-size:12.5px;margin-top:6px}

        .btn{
            display:inline-flex;align-items:center;justify-content:center;gap:8px;
            padding:12px 22px;border-radius:3px;font-size:14.5px;font-weight:500;
            border:1px solid transparent;transition:all .2s;cursor:pointer;white-space:nowrap;
        }
        .btn-gold{background:var(--gold);color:var(--ink);width:100%}
        .btn-gold:hover{background:var(--gold-bright)}

        .form-foot{margin-top:22px;text-align:center;font-size:13.5px;color:var(--paper-dim)}
        .form-foot a{color:var(--gold-bright);border-bottom:1px solid transparent;transition:border-color .2s}
        .form-foot a:hover{border-color:var(--gold-bright)}

        .terms-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13.5px;
            color: var(--paper-dim);
            cursor: pointer;
            padding: 4px 0;
        }
        .terms-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--gold);
            cursor: pointer;
            flex-shrink: 0;
            appearance: checkbox;
            -webkit-appearance: checkbox;
        }
        .terms-wrapper a {
            color: var(--gold-bright);
            border-bottom: 1px solid transparent;
            transition: border-color .2s;
            text-decoration: none;
        }
        .terms-wrapper a:hover {
            border-color: var(--gold-bright);
        }

        /* Back button */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--paper-dim);
            font-size: 13px;
            transition: color .2s;
            margin-bottom: 16px;
        }
        .back-link:hover {
            color: var(--paper);
        }
        .back-link svg {
            width: 16px;
            height: 16px;
        }

        @media (max-width: 600px) {
            .name-grid {
                grid-template-columns: 1fr;
            }
            .auth-card {
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body>

    <header class="site">
        <div class="wrap nav">
            <a href="{{ url('/') }}" class="brand">
                <svg class="brand-mark" viewBox="0 0 32 32" fill="none">
                    <circle cx="16" cy="16" r="15" stroke="#cf9a3d" stroke-width="1.4"/>
                    <path d="M16 6 L19 15 L16 26 L13 15 Z" fill="#e9b95a"/>
                    <circle cx="16" cy="16" r="2" fill="#0d1a2b"/>
                </svg>
                <span>CareerPath BN<small>Politeknik Brunei</small></span>
            </a>
            <div class="nav-actions"></div>
        </div>
    </header>

    <main class="auth-main">
        <div class="auth-card reveal">
            <!-- Back Button -->
            <a href="{{ url('/') }}" class="back-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Back to Home
            </a>

            <span class="eyebrow">Get started</span>
            <h1>Create account</h1>
            <p class="lede">Start your career discovery journey with BIICF.</p>

            @if (session('status'))
                <div class="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="status" style="color:var(--danger);border-color:rgba(224,138,125,0.35);background:rgba(224,138,125,0.1);">
                    Please fix the errors below.
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="name-grid">
                    <div class="field">
                        <label for="first_name">First Name</label>
                        <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" placeholder="Ahmad" required autofocus>
                        @error('first_name')<div class="err">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label for="last_name">Last Name</label>
                        <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Bin Abdullah" required>
                        @error('last_name')<div class="err">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@pb.edu.bn" required>
                    @error('email')<div class="err">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="programme">Programme</label>
                    <select id="programme" name="programme" required>
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
                        <option value="Others" {{ old('programme') == 'Others' ? 'selected' : '' }}>
                            Others
                        </option>
                    </select>
                    @error('programme')<div class="err">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" placeholder="Min. 8 characters" required>
                    @error('password')<div class="err">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Confirm your password" required>
                </div>

                <div class="field" style="margin-bottom:24px; margin-top:8px;">
                    <label class="terms-wrapper">
                        <input type="checkbox" name="terms" required {{ old('terms') ? 'checked' : '' }}>
                        <span>I agree to the <a href="{{ route('terms') }}" target="_blank">Terms of Service</a> and <a href="{{ route('privacy') }}" target="_blank">Privacy Policy</a></span>
                    </label>
                    @error('terms')<div class="err">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-gold">Create account</button>

                <div class="form-foot">
                    Already have an account? <a href="{{ route('login') }}">Sign in</a>
                </div>
            </form>
        </div>
    </main>

</body>
</html>