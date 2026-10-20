<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Supermarket | Sistema de Gestión de Ventas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --bg: #07111f;
            --panel: rgba(255,255,255,0.92);
            --text: #0f172a;
            --muted: #64748b;
            --primary: #4f46e5;
            --primary-2: #7c3aed;
            --accent: #f59e0b;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background: linear-gradient(135deg, #081120 0%, #111c2f 40%, #1f2d49 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }
        body::before, body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(20px);
            opacity: .35;
        }
        body::before {
            width: 320px; height: 320px;
            background: #6366f1; top: -80px; left: -60px;
        }
        body::after {
            width: 260px; height: 260px;
            background: #f59e0b; right: -50px; bottom: -70px;
        }

        .hero-card {
            width: min(100%, 1100px);
            background: var(--panel);
            border-radius: 28px;
            box-shadow: 0 30px 70px rgba(0,0,0,.28);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }
        .hero-left {
            padding: 48px 42px;
            background: linear-gradient(135deg, rgba(79,70,229,.08), rgba(124,58,237,.12));
        }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            padding: 7px 12px;
            font-size: .8rem;
            font-weight: 700;
            margin-bottom: 18px;
        }
        h1 {
            font-size: clamp(1.8rem, 3vw, 2.6rem);
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 12px;
        }
        .lead {
            font-size: 1rem;
            color: var(--muted);
            max-width: 620px;
            margin-bottom: 24px;
        }
        .actions a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .btn-primary-hero {
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            color: white;
        }
        .btn-outline-hero {
            border: 1px solid #dbe3f0;
            color: var(--text);
            background: white;
        }
        .hero-right {
            padding: 42px;
            background: linear-gradient(135deg, #0f172a 0%, #18253f 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
        }
        .hero-icon {
            width: 96px; height: 96px; border-radius: 24px;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            display: flex; align-items: center; justify-content: center;
            font-size: 2.2rem; margin-bottom: 20px;
            box-shadow: 0 20px 40px rgba(79,70,229,.35);
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            width: 100%;
            margin-top: 16px;
        }
        .stat {
            background: rgba(255,255,255,.09);
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 16px;
            padding: 14px;
            backdrop-filter: blur(8px);
        }
        .stat strong { display: block; font-size: 1.2rem; }
        .stat small { color: rgba(255,255,255,.72); }
        @media (max-width: 768px) {
            .hero-card { display: block; }
            .hero-left, .hero-right { padding: 28px; }
            .stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="hero-card row g-0">
        <div class="hero-left col-lg-7">
            <div class="pill"><i class="fas fa-circle-check"></i> Proyecto listo para presentar</div>
            <h1>Supermarket</h1>
            <p class="lead">Sistema de gestión diseñado para administrar categorías, productos, clientes, pedidos y reportes con una experiencia profesional, clara y preparada para un proyecto de grado.</p>

            <div class="actions">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-primary-hero"><i class="fas fa-arrow-right"></i> Ir al panel</a>
                @else
                    <a href="{{ route('login') }}" class="btn-primary-hero"><i class="fas fa-right-to-bracket"></i> Iniciar sesión</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-outline-hero"><i class="fas fa-user-plus"></i> Crear cuenta</a>
                    @endif
                @endauth
            </div>

            <div class="mt-4 text-muted small">
                <i class="fas fa-shield-halved me-2"></i>Gestión simple, interfaz moderna y navegación pensada para mostrar resultados de forma profesional.
            </div>
        </div>

        <div class="hero-right col-lg-5">
            <div class="hero-icon"><i class="fas fa-store"></i></div>
            <h3 class="mb-2">Sistema de ventas</h3>
            <p class="mb-0" style="color: rgba(255,255,255,.8);">Control integral del negocio en una sola plataforma.</p>
            <div class="stats">
                <div class="stat">
                    <strong>Productos</strong>
                    <small>Inventario actualizado</small>
                </div>
                <div class="stat">
                    <strong>Pedidos</strong>
                    <small>Seguimiento en tiempo real</small>
                </div>
                <div class="stat">
                    <strong>Clientes</strong>
                    <small>Gestión organizada</small>
                </div>
                <div class="stat">
                    <strong>Reportes</strong>
                    <small>Decisiones más rápidas</small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
