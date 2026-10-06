<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>No Access - CareerPath BN</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=2" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=2">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            color: #1a3a5c;
            padding: 16px;
            box-sizing: border-box;
        }
        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 40px 32px;
            max-width: 440px;
            width: 100%;
            text-align: center;
            box-shadow: 0 12px 40px rgba(26, 58, 92, 0.10);
        }
        .code {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: #c9a13b;
            margin-bottom: 8px;
        }
        h1 {
            font-size: 24px;
            margin: 0 0 12px;
        }
        p {
            color: #6b7280;
            line-height: 1.6;
            margin: 0 0 28px;
        }
        a.primary {
            display: inline-block;
            background: #1a3a5c;
            color: #fff;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
        }
        a.primary:hover {
            background: #244b73;
        }
        .secondary {
            margin: 18px 0 0;
            font-size: 13px;
        }
        .secondary a {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="code">ERROR 403</div>

        <h1>This page is not yours to open</h1>

        {{--
            The message comes from whichever check refused the
            request, so it can say which part of the site this
            was rather than only that something went wrong.
        --}}
        <p>
            {{
                $exception?->getMessage()
                    ?: 'Your account does not include this page. If you think it should, ask an administrator to check your role.'
            }}
        </p>

        @auth
            {{--
                One link for every role: the dashboard route works
                out where this person belongs, so nobody lands
                somewhere they cannot use either.
            --}}
            <a class="primary" href="{{ route('dashboard') }}">
                Go to my dashboard
            </a>

            <p class="secondary">
                Signed in as {{ auth()->user()->email }}.
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault();document.getElementById('signOut').submit();">
                    Sign out
                </a>
            </p>

            <form id="signOut" method="POST" action="{{ route('logout') }}" class="hidden">
                @csrf
            </form>
        @else
            <a class="primary" href="{{ route('login') }}">
                Sign in
            </a>
        @endauth
    </div>
</body>
</html>
