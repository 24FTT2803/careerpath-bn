<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in — CareerPath BN</title>

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

        /* header */
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

        /* auth section */
        .auth-main{flex:1;display:flex;align-items:center;justify-content:center;padding:64px 20px}
        .auth-card{
            width:100%;max-width:420px;
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

        .field{margin-bottom:18px}
        .field label{
            display:block;font-family:var(--font-mono);font-size:11px;letter-spacing:.08em;text-transform:uppercase;
            color:var(--paper-dim);margin-bottom:8px;
        }
        .field input{
            width:100%;background:var(--ink);border:1px solid var(--line);color:var(--paper);
            padding:12px 14px;border-radius:3px;font-size:14.5px;font-family:var(--font-body);
            transition:border-color .2s;
        }
        .field input:focus{outline:none;border-color:var(--gold-bright)}
        .field input::placeholder{color:rgba(199,194,180,0.45)}
        .err{color:var(--danger);font-size:12.5px;margin-top:6px}

        .row-between{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
        .remember{display:flex;align-items:center;gap:8px;font-size:13.5px;color:var(--paper-dim)}
        .remember input{accent-color:var(--gold);width:15px;height:15px}
        .forgot{font-size:13.5px;color:var(--gold-bright);border-bottom:1px solid transparent;transition:border-color .2s}
        .forgot:hover{border-color:var(--gold-bright)}

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
            <div class="nav-actions">
                @if (Route::has('register'))
                    <a href="{{ route('register') }}">Need an account? Sign up</a>
                @endif
            </div>
        </div>
    </header>

    <main class="auth-main">
        <div class="auth-card reveal">
            <span class="eyebrow">Welcome back</span>
            <h1>Log in</h1>
            <p class="lede">Pick up right where your career plan left off.</p>

            @if (session('status'))
                <div class="status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@pb.edu.bn" required autofocus autocomplete="username">
                    @error('email')<div class="err">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                    @error('password')<div class="err">{{ $message }}</div>@enderror
                </div>

                <div class="row-between">
                    <label class="remember">
                        <input type="checkbox" name="remember" id="remember_me">
                        Remember me
                    </label>
                    @if (Route::has('password.request'))
                        <a class="forgot" href="{{ route('password.request') }}">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="btn btn-gold">Log in</button>

                @if (Route::has('register'))
                    <div class="form-foot">
                        Don't have an account? <a href="{{ route('register') }}">Sign up</a>
                    </div>
                @endif
            </form>
        </div>
    </main>

</body>
</html>