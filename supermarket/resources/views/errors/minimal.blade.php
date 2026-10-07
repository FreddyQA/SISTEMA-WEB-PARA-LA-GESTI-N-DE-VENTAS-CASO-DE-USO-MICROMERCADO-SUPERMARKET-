<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Error') · Supermarket</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #0f172a, #1e1b4b, #4f46e5);
            background-size: 200% 200%;
            animation: gradientShift 12s ease infinite;
            padding: 24px;
        }
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50%      { background-position: 100% 50%; }
        }
        @media (prefers-reduced-motion: reduce) {
            body { animation: none; }
        }
        .error-card {
            background: rgba(255,255,255,.96);
            border-radius: 20px;
            padding: 48px 40px;
            max-width: 420px;
            width: 100%;
            text-align: center;
            box-shadow: 0 30px 70px rgba(0,0,0,.35);
            animation: cardIn .5s cubic-bezier(.2,.9,.3,1) both;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(16px) scale(.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .error-icon {
            width: 72px; height: 72px;
            margin: 0 auto 20px;
            border-radius: 18px;
            background: linear-gradient(145deg, #4f46e5 0%, #6d28d9 55%, #7c3aed 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: 32px;
            box-shadow: 0 14px 34px rgba(79,70,229,.35);
        }
        .error-code {
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: #7c3aed;
            margin-bottom: 6px;
        }
        h1 {
            font-size: 1.4rem;
            color: #1e1b4b;
            margin: 0 0 12px;
        }
        p {
            color: #64748b;
            font-size: .92rem;
            line-height: 1.55;
            margin: 0 0 26px;
        }
        .btn-volver {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 22px;
            border-radius: 10px;
            background: #4f46e5;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: .88rem;
            transition: background .18s ease, transform .18s ease;
        }
        .btn-volver:hover { background: #4338ca; transform: translateY(-1px); color: #fff; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">@yield('icon', '⚠️')</div>
        <div class="error-code">@yield('code', 'Error')</div>
        <h1>@yield('heading', 'Ocurrió un problema')</h1>
        <p>@yield('message', 'Intenta nuevamente en unos momentos.')</p>
        <a href="{{ url('/') }}" class="btn-volver">
            <span>&larr;</span> Volver al inicio
        </a>
    </div>
</body>
</html>
