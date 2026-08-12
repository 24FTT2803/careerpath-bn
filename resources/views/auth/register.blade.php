<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create your account — CareerPath BN</title>

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
        .auth-shell{width:100%;max-width:960px;display:grid;grid-template-columns:1fr 1fr;gap:0;border:1px solid var(--line);border-radius:6px;overflow:hidden;background:var(--ink-2)}

        .auth-side{
            background:linear-gradient(160deg, var(--ink-3), var(--ink));
            padding:48px 40px;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            border-right:1px solid var(--line);
        }
        .auth-side .eyebrow{
            display:inline-flex;align-items:center;gap:8px;
            font-family:var(--font-mono);font-size:11.5px;letter-spacing:.12em;text-transform:uppercase;
            color:var(--gold-bright);margin-bottom:18px;
        }
        .auth-side .eyebrow::before{content:'';width:16px;height:1px;background:var(--gold-bright)}
        .auth-side h1{
            font-family:var(--font-display);font-weight:600;
            font-size:clamp(26px,3vw,32px);line-height:1.18;letter-spacing:-.01em;
        }
        .auth-side h1 em{color:var(--gold-bright);font-style:italic}
        .auth-side p{color:var(--paper-dim);margin-top:16px;font-size:14.5px;max-width:34ch}

        .side-stats{display:flex;gap:1px;background:var(--line);border:1px solid var(--line);border-radius:4px;overflow:hidden;margin-top:36px}
        .side-stat{background:var(--ink-2);padding:16px 14px;flex:1;text-align:center}
        .side-stat .n{font-family:var(--font-mono);font-size:20px;color:var(--gold-bright);font-weight:500}
        .side-stat .l{font-size:11px;color:var(--paper-dim);margin-top:2px}

        .auth-form-col{padding:48px 40px}
        .auth-form-col h2{font-family:var(--font-display);font-size:26px;font-weight:600;margin-bottom:6px}
        .auth-form-col .lede{color:var(--paper-dim);font-size:14px;margin-bottom:28px}

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

        .btn{
            display:inline-flex;align-items:center;justify-content:center;gap:8px;
            padding:12px 22px;border-radius:3px;font-size:14.5px;font-weight:500;
            border:1px solid transparent;transition:all .2s;cursor:pointer;white-space:nowrap;
        }
        .btn-gold{background:var(--gold);color:var(--ink);width:100%;margin-top:8px}
        .btn-gold:hover{background:var(--gold-bright)}

        .form-foot{margin-top:22px;text-align:center;font-size:13.5px;color:var(--paper-dim)}
        .form-foot a{color:var(--gold-bright);border-bottom:1px solid transparent;transition:border-color .2s}
        .form-foot a:hover{border-color:var(--gold-bright)}

        @media (max-width:760px){
            .auth-shell{grid-template-columns:1fr}
            .auth-side{border-right:none;border-bottom:1px solid var(--line)}
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
            <div class="nav-actions">
                <a href="{{ route('login') }}">Already have an account? Log in</a>
            </div>
        </div>
    </header>

    <main class="auth-main">
        <div class="auth-shell reveal">
            <div class="auth-side">
                <div>
                    <span class="eyebrow">Get started</span>
                    <h1>Find the career that actually <em>fits</em> your profile.</h1>
                    <p>Create your account to build a profile, get matched to BIICF-aligned careers, and see exactly what to work on next.</p>
                </div>
            </div>

            <div class="auth-form-col">
                <h2>Create your account</h2>
                <p class="lede">Takes about a minute — you can build out your profile after.</p>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="field">
                        <label for="name">Name</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Your full name" required autofocus autocomplete="name">
                        @error('name')<div class="err">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@pb.edu.bn" required autocomplete="username">
                        @error('email')<div class="err">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" placeholder="••••••••" required autocomplete="new-password">
                        @error('password')<div class="err">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirm password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" placeholder="••••••••" required autocomplete="new-password">
                        @error('password_confirmation')<div class="err">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-gold">Create account</button>

                    <div class="form-foot">
                        Already registered? <a href="{{ route('login') }}">Log in</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

</body>
</html>