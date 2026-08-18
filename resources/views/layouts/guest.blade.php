<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'CareerPath BN')</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --ink: #0d1a2b;
            --paper: #f5f1e6;
            --gold: #cf9a3d;
            --gold-bright: #e9b95a;
            --font-display: 'Fraunces', Georgia, serif;
            --font-body: 'IBM Plex Sans', ui-sans-serif, system-ui, sans-serif;
        }

        *{margin:0;padding:0;box-sizing:border-box}
        body {
            background: var(--paper);
            color: var(--ink);
            font-family: var(--font-body);
            line-height: 1.7;
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container{max-width:900px;margin:0 auto;background:white;padding:48px;border-radius:8px;box-shadow:0 4px 24px rgba(0,0,0,0.06)}
        h1{font-family:var(--font-display);font-size:32px;font-weight:700;margin-bottom:6px}
        .date{color:#888;font-size:14px;margin-bottom:32px;display:block}
        h2{font-family:var(--font-display);font-size:20px;font-weight:600;margin:28px 0 12px}
        p{margin-bottom:12px}
        ul{margin:8px 0 16px 24px}
        a{color:var(--gold);text-decoration:none}
        a:hover{color:var(--gold-bright);text-decoration:underline}
        .back-link{display:inline-flex;align-items:center;gap:8px;margin-top:32px;padding:10px 20px;background:var(--gold);color:white;border-radius:4px;font-weight:500}
        .back-link:hover{background:var(--gold-bright);color:var(--ink);text-decoration:none}
    </style>
</head>
<body>
    <div class="container">
        @yield('content')
    </div>
</body>
</html>