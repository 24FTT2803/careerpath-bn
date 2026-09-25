<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Too Large | CareerPath BN</title>
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
        a {
            display: inline-block;
            background: #1a3a5c;
            color: #fff;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
        }
        a:hover {
            background: #244b73;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="code">ERROR 413</div>
        <h1>File too large</h1>
        <p>{{ $message ?? 'The file you uploaded is too large. Please choose a smaller file and try again.' }}</p>
        <a href="{{ $backUrl ?? url('/') }}">Go back</a>
    </div>
</body>
</html>