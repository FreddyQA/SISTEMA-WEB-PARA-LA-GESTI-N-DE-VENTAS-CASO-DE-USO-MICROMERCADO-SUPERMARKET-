<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Supermarket — Sistema de Ventas y Gestión Empresarial. Proyecto de grado con panel de control, inventario, pedidos, reportes y códigos QR.">
    <title>@yield('title', 'Panel') | Supermarket</title>

    <!-- Favicon inline SVG (SUPERMARKET, sin archivos externos) -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0%25' stop-color='%234f46e5'/><stop offset='100%25' stop-color='%237c3aed'/></linearGradient></defs><rect width='64' height='64' rx='14' fill='url(%23g)'/><path d='M18 44V20h6v11h7V20h6v24h-6V31H24v13z' fill='white'/><circle cx='47' cy='32' r='8' fill='white' fill-opacity='.9'/><path d='M43.5 32l2.5 2.5 5-5' stroke='%234f46e5' stroke-width='2.6' stroke-linecap='round' stroke-linejoin='round' fill='none'/></svg>">
    <link rel="apple-touch-icon" href="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0%25' stop-color='%234f46e5'/><stop offset='100%25' stop-color='%237c3aed'/></linearGradient></defs><rect width='64' height='64' rx='14' fill='url(%23g)'/><path d='M18 44V20h6v11h7V20h6v24h-6V31H24v13z' fill='white'/><circle cx='47' cy='32' r='8' fill='white' fill-opacity='.9'/><path d='M43.5 32l2.5 2.5 5-5' stroke='%234f46e5' stroke-width='2.6' stroke-linecap='round' stroke-linejoin='round' fill='none'/></svg>">

    <!-- ════════════════════════════════════════════════════════
         DARK MODE PRE-APPLY — EVITA PARPADEO (FOUC)
         Aplicar la clase dark-mode ANTES de que el navegador pinte
         ════════════════════════════════════════════════════════ -->
    <script>
        (function () {
            try {
                var isDark = localStorage.getItem('darkMode') === '1';
                var root = document.documentElement;
                if (isDark) {
                    root.classList.add('dark-mode');
                    root.style.setProperty('--bg-base', '#030712');
                    root.style.setProperty('--pt-bg', 'linear-gradient(135deg, #030712, #0b1530)');
                } else {
                    root.style.setProperty('--bg-base', '#f8fafc');
                    root.style.setProperty('--pt-bg', 'linear-gradient(135deg, #f8fafc, #eef2ff)');
                }
            } catch (e) {}
        })();
    </script>
    <style>
        html, body { background-color: var(--bg-base, #f8fafc); }
        html.dark-mode, html.dark-mode body, body.dark-mode {
            background-color: #030712 !important;
            --bg-base: #030712 !important;
            --pt-bg: linear-gradient(135deg, #030712, #0b1530) !important;
        }
        /* Prevenir transiciones automáticas durante la carga para eliminar parpadeo */
        .no-trans * , .no-trans *::before, .no-trans *::after {
            transition: none !important;
            animation: none !important;
        }
    </style>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    @vite('resources/js/codes.js')
    <!-- Librerías de códigos QR y Barras de alta disponibilidad -->
    <script src="{{ asset('vendor/JsBarcode.all.min.js') }}"></script>
    <script src="{{ asset('vendor/qrcode.min.js') }}"></script>



    <style>
        /* ══════════════════════════════════════════
           BASE
        ══════════════════════════════════════════ */
        :root {
                --primary: #4f46e5;
            --table-text: #0f172a;
        }

        /* Dark mode: paleta invertida y más profesional */
        body.dark-mode {
            --surface: #071026;
            --surface-soft: #0b1530;
            --text: #e6eef8;
            --muted: #9fb6cf;
            --muted-2: #94a3b8;
            --table-text: #dbeafe;
            background: linear-gradient(180deg,#030516 0%, #071026 100%);
            color: var(--text);
        }
        body.dark-mode #sidebar { background: linear-gradient(180deg,#041227,#071833); box-shadow: none; }
        body.dark-mode #topbar { background: rgba(2,6,23,.72); border-bottom-color: rgba(255,255,255,.03); box-shadow: 0 6px 20px rgba(2,6,23,.5); }
        body.dark-mode .sidebar-link { color: rgba(230,238,250,.9); }
        body.dark-mode .sidebar-link .link-label { color: var(--text); }
        body.dark-mode .sidebar-link:hover { background: rgba(255,255,255,.02); }
        body.dark-mode .card, body.dark-mode .page-panel { background: linear-gradient(180deg, rgba(255,255,255,.02), rgba(255,255,255,.01)) !important; color: var(--text); border-color: rgba(255,255,255,.03) !important; }
        body.dark-mode .stat-card .stat-number, body.dark-mode .stat-card .stat-label { color: #fff !important; }
        body.dark-mode .topbar-title { color: var(--text); }
        body.dark-mode .topbar-user .user-info strong { color: var(--text); }
        body.dark-mode .topbar-user .user-info span { color: var(--muted-2); }
        body.dark-mode .page-header h1 { color: var(--text); }
        body.dark-mode .breadcrumb-item a { color: #818cf8; }
        body.dark-mode .breadcrumb-item.active { color: var(--muted-2); }
        body.dark-mode .breadcrumb-item + .breadcrumb-item::before { color: var(--muted-2); }
        body.dark-mode .card-header {
            background: rgba(8,20,34,.65) !important;
            color: var(--text) !important;
            border-bottom-color: rgba(255,255,255,.04) !important;
        }
        body.dark-mode .page-panel {
            background: rgba(2,6,23,.65) !important;
            border-color: rgba(255,255,255,.03) !important;
        }
        body.dark-mode .table thead th {
            background: rgba(8,20,34,.6) !important;
            color: #bfdbfe !important;
            border-bottom-color: rgba(255,255,255,.04) !important;
        }
        body.dark-mode .table td {
            color: var(--table-text) !important;
            border-color: rgba(255,255,255,.04) !important;
        }
        body.dark-mode .table-hover tbody tr:hover { background: rgba(255,255,255,.02) !important; }
        body.dark-mode .form-control,
        body.dark-mode .form-select {
            background: rgba(8,20,34,.55) !important;
            color: var(--text) !important;
            border-color: rgba(148,163,184,.25) !important;
        }
        body.dark-mode .form-control::placeholder {
            color: rgba(159,182,207,.55);
        }
        body.dark-mode .form-control:focus,
        body.dark-mode .form-select:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99,102,241,.18) !important;
            background: rgba(8,20,34,.7) !important;
        }
        body.dark-mode .form-label {
            color: #dbeafe;
        }
        body.dark-mode .form-text,
        body.dark-mode .text-muted {
            color: var(--muted-2) !important;
        }
        body.dark-mode .input-group-text {
            background: rgba(8,20,34,.55);
            color: var(--text);
            border-color: rgba(148,163,184,.25);
        }
        body.dark-mode .btn-primary   { background: #4f46e5 !important; border-color: #4f46e5 !important; color: #fff !important; }
        body.dark-mode .btn-primary:hover   { background: #4338ca !important; border-color: #4338ca !important; }
        body.dark-mode .btn-success   { background: #10b981 !important; border-color: #10b981 !important; color: #fff !important; }
        body.dark-mode .btn-success:hover   { background: #059669 !important; border-color: #059669 !important; }
        body.dark-mode .btn-warning   { background: #f59e0b !important; border-color: #f59e0b !important; color: #0f172a !important; }
        body.dark-mode .btn-warning:hover   { background: #d97706 !important; border-color: #d97706 !important; }
        body.dark-mode .btn-danger    { background: #ef4444 !important; border-color: #ef4444 !important; color: #fff !important; }
        body.dark-mode .btn-danger:hover    { background: #dc2626 !important; border-color: #dc2626 !important; }
        body.dark-mode .btn-secondary { background: #64748b !important; border-color: #64748b !important; color: #fff !important; }
        body.dark-mode .btn-outline-secondary {
            color: #cbd5e1 !important;
            border-color: rgba(148,163,184,.35) !important;
        }
        body.dark-mode .btn-outline-secondary:hover {
            background: rgba(148,163,184,.12) !important;
            color: #e2e8f0 !important;
        }
        body.dark-mode #toggleSidebar { color: #cbd5e1; }
        body.dark-mode #toggleSidebar:hover { background: rgba(255,255,255,.06); color: var(--text); }
        body.dark-mode .alert-success {
            background: rgba(16,185,129,.12) !important;
            color: #6ee7b7 !important;
            border: 1px solid rgba(16,185,129,.2) !important;
            box-shadow: 0 2px 8px rgba(16,185,129,.08) !important;
        }
        body.dark-mode .alert-danger {
            background: rgba(239,68,68,.12) !important;
            color: #fca5a5 !important;
            border: 1px solid rgba(239,68,68,.2) !important;
            box-shadow: 0 2px 8px rgba(239,68,68,.08) !important;
        }
        body.dark-mode .alert-warning {
            background: rgba(245,158,11,.12) !important;
            color: #fcd34d !important;
            border: 1px solid rgba(245,158,11,.2) !important;
        }
        body.dark-mode .alert-info {
            background: rgba(14,165,233,.12) !important;
            color: #7dd3fc !important;
            border: 1px solid rgba(14,165,233,.2) !important;
        }
        body.dark-mode .alert .btn-close {
            filter: invert(1) grayscale(100%) brightness(1.4);
        }
        body.dark-mode .status-pill {
            color: #a5b4fc;
            background: rgba(79,70,229,.12);
        }
        body.dark-mode .status-pill.success {
            color: #6ee7b7;
            background: rgba(16,185,129,.12);
        }
        body.dark-mode footer.page-footer {
            color: var(--muted-2);
            border-top-color: rgba(255,255,255,.04);
        }
        body.dark-mode .dataTables_wrapper .dataTables_length,
        body.dark-mode .dataTables_wrapper .dataTables_filter,
        body.dark-mode .dataTables_wrapper .dataTables_info,
        body.dark-mode .dataTables_wrapper .dataTables_processing,
        body.dark-mode .dataTables_wrapper .dataTables_paginate {
            color: var(--muted-2) !important;
        }
        body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,
        body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
            color: var(--muted-2) !important;
        }
        body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: var(--muted-2) !important;
        }
        body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: linear-gradient(135deg,#4f46e5,#7c3aed) !important;
            color: #fff !important;
            border-color: transparent !important;
        }
        body.dark-mode a {
            color: #818cf8;
        }
        body.dark-mode a:hover {
            color: #a5b4fc;
        }
        body.dark-mode .text-danger  { color: #f87171 !important; }
        body.dark-mode .text-success { color: #4ade80 !important; }
        body.dark-mode .text-warning { color: #fbbf24 !important; }
        body.dark-mode .text-info    { color: #38bdf8 !important; }
        body.dark-mode .text-primary { color: #818cf8 !important; }
        body.dark-mode hr { border-top-color: rgba(255,255,255,.06) !important; }
        body.dark-mode .dropdown-menu {
            background: rgba(8,20,34,.95) !important;
            border-color: rgba(255,255,255,.05) !important;
        }
        body.dark-mode .dropdown-item {
            color: var(--text) !important;
        }
        body.dark-mode .dropdown-item:hover,
        body.dark-mode .dropdown-item:focus {
            background: rgba(79,70,229,.12) !important;
            color: var(--text) !important;
        }

        /* ══════════════════════════════════════════
           HTML.DARK-MODE MIRRORS — anti-FOUC (pre-aplicados en HEAD antes de body)
           Cualquier selector body.dark-mode importante debe tener su mirror html.dark-mode
        ══════════════════════════════════════════ */
        html.dark-mode,
        html.dark-mode body {
            background: linear-gradient(180deg,#030516 0%, #071026 100%) !important;
            color: #e6eef8 !important;
        }
        html.dark-mode #sidebar { background: linear-gradient(180deg,#041227,#071833); box-shadow: none; }
        html.dark-mode #topbar { background: rgba(2,6,23,.72); border-bottom-color: rgba(255,255,255,.03); box-shadow: 0 6px 20px rgba(2,6,23,.5); }
        html.dark-mode .sidebar-link { color: rgba(230,238,250,.9); }
        html.dark-mode .sidebar-link .link-label { color: #e6eef8; }
        html.dark-mode .sidebar-link:hover { background: rgba(255,255,255,.02); }
        html.dark-mode .card, html.dark-mode .page-panel { background: linear-gradient(180deg, rgba(255,255,255,.02), rgba(255,255,255,.01)) !important; color: #e6eef8; border-color: rgba(255,255,255,.03) !important; }
        html.dark-mode .stat-card .stat-number, html.dark-mode .stat-card .stat-label { color: #fff !important; }
        html.dark-mode .topbar-title { color: #e6eef8; }
        html.dark-mode .topbar-user .user-info strong { color: #e6eef8; }
        html.dark-mode .topbar-user .user-info span { color: #94a3b8; }
        html.dark-mode .page-header h1 { color: #e6eef8; }
        html.dark-mode .breadcrumb-item a { color: #818cf8; }
        html.dark-mode .breadcrumb-item.active { color: #94a3b8; }
        html.dark-mode .breadcrumb-item + .breadcrumb-item::before { color: #94a3b8; }
        html.dark-mode .card-header {
            background: rgba(8,20,34,.65) !important;
            color: #e6eef8 !important;
            border-bottom-color: rgba(255,255,255,.04) !important;
        }
        html.dark-mode .page-panel {
            background: rgba(2,6,23,.65) !important;
            border-color: rgba(255,255,255,.03) !important;
        }
        html.dark-mode .table thead th {
            background: rgba(8,20,34,.6) !important;
            color: #bfdbfe !important;
            border-bottom-color: rgba(255,255,255,.04) !important;
        }
        html.dark-mode .table td {
            color: #dbeafe !important;
            border-color: rgba(255,255,255,.04) !important;
        }
        html.dark-mode .table-hover tbody tr:hover { background: rgba(255,255,255,.02) !important; }
        html.dark-mode .form-control,
        html.dark-mode .form-select {
            background: rgba(8,20,34,.55) !important;
            color: #e6eef8 !important;
            border-color: rgba(148,163,184,.25) !important;
        }
        html.dark-mode .form-control::placeholder { color: rgba(159,182,207,.55); }
        html.dark-mode .form-control:focus,
        html.dark-mode .form-select:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99,102,241,.18) !important;
            background: rgba(8,20,34,.7) !important;
        }
        html.dark-mode .form-label { color: #dbeafe; }
        html.dark-mode .form-text,
        html.dark-mode .text-muted { color: #94a3b8 !important; }
        html.dark-mode .input-group-text {
            background: rgba(8,20,34,.55);
            color: #e6eef8;
            border-color: rgba(148,163,184,.25);
        }
        html.dark-mode .btn-primary   { background: #4f46e5 !important; border-color: #4f46e5 !important; color: #fff !important; }
        html.dark-mode .btn-primary:hover   { background: #4338ca !important; border-color: #4338ca !important; }
        html.dark-mode .btn-success   { background: #10b981 !important; border-color: #10b981 !important; color: #fff !important; }
        html.dark-mode .btn-success:hover   { background: #059669 !important; border-color: #059669 !important; }
        html.dark-mode .btn-warning   { background: #f59e0b !important; border-color: #f59e0b !important; color: #0f172a !important; }
        html.dark-mode .btn-warning:hover   { background: #d97706 !important; border-color: #d97706 !important; }
        html.dark-mode .btn-danger    { background: #ef4444 !important; border-color: #ef4444 !important; color: #fff !important; }
        html.dark-mode .btn-danger:hover    { background: #dc2626 !important; border-color: #dc2626 !important; }
        html.dark-mode .btn-secondary { background: #64748b !important; border-color: #64748b !important; color: #fff !important; }
        html.dark-mode .btn-outline-secondary {
            color: #cbd5e1 !important;
            border-color: rgba(148,163,184,.35) !important;
        }
        html.dark-mode .btn-outline-secondary:hover {
            background: rgba(148,163,184,.12) !important;
            color: #e2e8f0 !important;
        }
        html.dark-mode #toggleSidebar { color: #cbd5e1; }
        html.dark-mode #toggleSidebar:hover { background: rgba(255,255,255,.06); color: #e6eef8; }
        html.dark-mode .alert-success {
            background: rgba(16,185,129,.12) !important;
            color: #6ee7b7 !important;
            border: 1px solid rgba(16,185,129,.2) !important;
            box-shadow: 0 2px 8px rgba(16,185,129,.08) !important;
        }
        html.dark-mode .alert-danger {
            background: rgba(239,68,68,.12) !important;
            color: #fca5a5 !important;
            border: 1px solid rgba(239,68,68,.2) !important;
            box-shadow: 0 2px 8px rgba(239,68,68,.08) !important;
        }
        html.dark-mode .alert-warning {
            background: rgba(245,158,11,.12) !important;
            color: #fcd34d !important;
            border: 1px solid rgba(245,158,11,.2) !important;
        }
        html.dark-mode .alert-info {
            background: rgba(14,165,233,.12) !important;
            color: #7dd3fc !important;
            border: 1px solid rgba(14,165,233,.2) !important;
        }
        html.dark-mode .alert .btn-close { filter: invert(1) grayscale(100%) brightness(1.4); }
        html.dark-mode .status-pill { color: #a5b4fc; background: rgba(79,70,229,.12); }
        html.dark-mode .status-pill.success { color: #6ee7b7; background: rgba(16,185,129,.12); }
        html.dark-mode footer.page-footer { color: #94a3b8; border-top-color: rgba(255,255,255,.04); }
        html.dark-mode .dataTables_wrapper .dataTables_length,
        html.dark-mode .dataTables_wrapper .dataTables_filter,
        html.dark-mode .dataTables_wrapper .dataTables_info,
        html.dark-mode .dataTables_wrapper .dataTables_processing,
        html.dark-mode .dataTables_wrapper .dataTables_paginate {
            color: #94a3b8 !important;
        }
        html.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        html.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,
        html.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
            color: #94a3b8 !important;
        }
        html.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button { color: #94a3b8 !important; }
        html.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        html.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: linear-gradient(135deg,#4f46e5,#7c3aed) !important;
            color: #fff !important;
            border-color: transparent !important;
        }
        html.dark-mode a { color: #818cf8; }
        html.dark-mode a:hover { color: #a5b4fc; }
        html.dark-mode .text-danger  { color: #f87171 !important; }
        html.dark-mode .text-success { color: #4ade80 !important; }
        html.dark-mode .text-warning { color: #fbbf24 !important; }
        html.dark-mode .text-info    { color: #38bdf8 !important; }
        html.dark-mode .text-primary { color: #818cf8 !important; }
        html.dark-mode hr { border-top-color: rgba(255,255,255,.06) !important; }
        html.dark-mode .dropdown-menu {
            background: rgba(8,20,34,.95) !important;
            border-color: rgba(255,255,255,.05) !important;
        }
        html.dark-mode .dropdown-item { color: #e6eef8 !important; }
        html.dark-mode .dropdown-item:hover,
        html.dark-mode .dropdown-item:focus {
            background: rgba(79,70,229,.12) !important;
            color: #e6eef8 !important;
        }

        /* ══════════════════════════════════════════
           MEJORAS NUMÉRICAS MODO DÍA + TABLAS (contraste y atractivo visual)
        ══════════════════════════════════════════ */
        :root {
            --num-light-bg: linear-gradient(135deg, #0f172a 0%, #334155 100%);
            --num-success: #047857;
            --num-danger:  #b91c1c;
            --num-warning: #b45309;
            --num-primary: #4338ca;
        }
        .stat-number {
            font-weight: 800 !important;
            letter-spacing: -0.02em;
            font-size: 1.95rem !important;
            line-height: 1.1;
        }
        /* Badges / totales de tablas modo día con fuerte contraste */
        .badge-total, .reporte-total, .total-monto {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
            color: #f8fafc !important;
            padding: .55rem .9rem !important;
            border-radius: 14px !important;
            font-weight: 800 !important;
            letter-spacing: -0.01em !important;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.18), inset 0 1px 0 rgba(255,255,255,.08) !important;
            border: 1px solid rgba(15, 23, 42, 0.08) !important;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-size: 1rem !important;
        }
        .badge-total i, .reporte-total i, .total-monto i { opacity: .95; }

        .badge-num-primary {
            background: linear-gradient(135deg, #4f46e5, #6d28d9) !important;
            color: #fff !important;
            padding: .4rem .75rem !important;
            border-radius: 10px !important;
            font-weight: 700 !important;
            box-shadow: 0 6px 16px rgba(79, 70, 229, .28) !important;
        }
        .badge-num-success {
            background: linear-gradient(135deg, #059669, #047857) !important;
            color: #fff !important;
            padding: .4rem .75rem !important;
            border-radius: 10px !important;
            font-weight: 700 !important;
            box-shadow: 0 6px 16px rgba(5, 150, 105, .28) !important;
        }
        .badge-num-danger {
            background: linear-gradient(135deg, #dc2626, #991b1b) !important;
            color: #fff !important;
            padding: .4rem .75rem !important;
            border-radius: 10px !important;
            font-weight: 700 !important;
            box-shadow: 0 6px 16px rgba(220, 38, 38, .28) !important;
        }
        .badge-num-warning {
            background: linear-gradient(135deg, #d97706, #92400e) !important;
            color: #fff !important;
            padding: .4rem .75rem !important;
            border-radius: 10px !important;
            font-weight: 700 !important;
            box-shadow: 0 6px 16px rgba(217, 119, 6, .28) !important;
        }
        .badge-num-info {
            background: linear-gradient(135deg, #0891b2, #0e7490) !important;
            color: #fff !important;
            padding: .4rem .75rem !important;
            border-radius: 10px !important;
            font-weight: 700 !important;
            box-shadow: 0 6px 16px rgba(8, 145, 178, .28) !important;
        }

        /* Mejoras globales de tablas (modo día + dark) */
        .table thead th {
            font-weight: 700 !important;
            letter-spacing: .01em;
            font-size: .78rem;
            text-transform: uppercase;
            padding-top: .9rem !important;
            padding-bottom: .9rem !important;
        }
        .table tbody td {
            padding-top: .85rem !important;
            padding-bottom: .85rem !important;
            vertical-align: middle !important;
            font-size: .92rem;
        }
        .table-striped tbody tr:nth-of-type(odd) > * {
            --bs-table-accent-bg: rgba(99, 102, 241, .035);
        }
        html.dark-mode .table-striped tbody tr:nth-of-type(odd) > * {
            --bs-table-accent-bg: rgba(99, 102, 241, .05);
        }
        .table-hover tbody tr:hover {
            --bs-table-accent-bg: rgba(79, 70, 229, .06) !important;
        }
        .table-responsive {
            border: 1px solid rgba(226,232,240,.8);
            border-radius: 14px;
            background: rgba(255,255,255,.58);
        }
        .table tbody tr {
            transition: background-color .18s ease, box-shadow .18s ease;
        }
        .table tbody tr:hover {
            box-shadow: inset 3px 0 0 #6366f1;
        }
        .table td strong { color: #172554; }
        .table .badge-light {
            background: #f1f5f9 !important;
            color: #334155 !important;
            border-color: #cbd5e1 !important;
        }
        html.dark-mode .table-responsive {
            border-color: rgba(148,163,184,.18);
            background: rgba(2,6,23,.38);
        }
        html.dark-mode .table td strong { color: #f8fafc; }
        html.dark-mode .table td,
        html.dark-mode .table td .text-muted,
        html.dark-mode .table td small { color: #e2e8f0 !important; }
        html.dark-mode .dataTables_wrapper label,
        html.dark-mode .dataTables_wrapper .dataTables_info { color: #cbd5e1 !important; }
        html.dark-mode .table .badge-light {
            background: rgba(148,163,184,.16) !important;
            color: #e2e8f0 !important;
            border-color: rgba(148,163,184,.3) !important;
        }
        html.dark-mode .table-hover tbody tr:hover,
        body.dark-mode .table-hover tbody tr:hover {
            --bs-table-accent-bg: rgba(99, 102, 241, .1) !important;
            background: rgba(99, 102, 241, .1) !important;
        }
        html.dark-mode .table,
        body.dark-mode .table {
            --bs-table-bg: transparent !important;
            --bs-table-color: inherit !important;
            background-color: transparent !important;
        }
        html.dark-mode .dataTables_wrapper .dataTables_filter input,
        body.dark-mode .dataTables_wrapper .dataTables_filter input,
        html.dark-mode .dataTables_wrapper .dataTables_length select,
        body.dark-mode .dataTables_wrapper .dataTables_length select {
            background: rgba(8,20,34,.7) !important;
            color: #f1f5f9 !important;
            border-color: rgba(148,163,184,.25) !important;
            border-radius: 8px !important;
        }
        html.dark-mode .page-link,
        body.dark-mode .page-link {
            background: rgba(8,20,34,.65) !important;
            border-color: rgba(255,255,255,.08) !important;
            color: #cbd5e1 !important;
        }
        html.dark-mode .page-item.active .page-link,
        body.dark-mode .page-item.active .page-link {
            background: linear-gradient(135deg, #4f46e5, #7c3aed) !important;
            border-color: transparent !important;
            color: #fff !important;
        }
        html.dark-mode .page-item.disabled .page-link,
        body.dark-mode .page-item.disabled .page-link {
            background: rgba(8,20,34,.35) !important;
            border-color: rgba(255,255,255,.04) !important;
            color: #64748b !important;
        }
        /* Redondear esquinas de tarjetas-tabla y tarjetas de página */
        .card, .page-panel, .modal-content {
            border-radius: 18px !important;
            overflow: hidden;
        }
        .card-header {
            border-top-left-radius: 18px !important;
            border-top-right-radius: 18px !important;
        }
        .dataTables_wrapper .row:first-child {
            margin-bottom: .9rem !important;
        }
        .dataTables_wrapper .row:last-child {
            margin-top: 1rem !important;
        }
        .dataTables_wrapper .paginate_button {
            border-radius: 10px !important;
            margin: 0 2px !important;
        }
        /* Sombras y bordes premium para tarjetas generales */
        .card, .page-panel {
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.05), inset 0 1px 0 rgba(255,255,255,.6) !important;
        }
        html.dark-mode .card, html.dark-mode .page-panel {
            box-shadow: 0 12px 32px rgba(0, 0, 0, .35), inset 0 1px 0 rgba(255,255,255,.04) !important;
        }
        body {
            /* simpler, centered soft background to avoid large empty pale column */
            background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
            color: var(--text);
            margin: 0;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* ══════════════════════════════════════════
           HERO / PROFILE
        ══════════════════════════════════════════ */
        .welcome-hero {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(79,70,229,.12), rgba(14,165,233,.08), rgba(255,255,255,.7));
            border: 1px solid rgba(148,163,184,.18);
            border-radius: 24px;
            box-shadow: 0 18px 35px rgba(15,23,42,.08);
            padding: 1.5rem;
        }
        .welcome-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(18px);
            opacity: .38;
            pointer-events: none;
        }
        .welcome-blob-1 {
            width: 200px;
            height: 200px;
            background: rgba(99,102,241,.25);
            right: -40px;
            top: -50px;
        }
        .welcome-blob-2 {
            width: 180px;
            height: 180px;
            background: rgba(16,185,129,.18);
            left: -20px;
            bottom: -60px;
        }
        .profile-summary {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .avatar-xl {
            width: 74px;
            height: 74px;
            border-radius: 22px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            box-shadow: 0 10px 30px rgba(79,70,229,.35);
            color: #fff;
            font-size: 1.8rem;
            font-weight: 800;
            border: 3px solid rgba(255,255,255,.7);
            flex-shrink: 0;
        }
        .avatar-xl img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .welcome-hello {
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #4f46e5;
            margin-bottom: .25rem;
        }
        .welcome-name {
            font-size: clamp(1.6rem, 2vw, 2.2rem);
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }
        .welcome-rol {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            margin-top: .25rem;
            background: rgba(79,70,229,.08);
            color: #374151;
            font-weight: 700;
            border-radius: 999px;
            padding: .45rem .8rem;
            font-size: .76rem;
        }
        .welcome-lead {
            position: relative;
            z-index: 1;
            color: #475569;
            font-size: .96rem;
            line-height: 1.6;
        }
        .welcome-actions {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: .6rem;
        }
        body.dark-mode .welcome-hero {
            background: linear-gradient(135deg, rgba(79,70,229,.20), rgba(14,165,233,.10), rgba(15,23,42,.75));
            border-color: rgba(255,255,255,.06);
            box-shadow: 0 18px 35px rgba(2,6,23,.35);
        }
        body.dark-mode .welcome-hello {
            color: #a5b4fc;
        }
        body.dark-mode .welcome-name {
            color: #f8fafc;
        }
        body.dark-mode .welcome-rol {
            background: rgba(99,102,241,.16);
            color: #e2e8f0;
        }
        body.dark-mode .welcome-lead {
            color: #cbd5e1;
        }

        /* ══════════════════════════════════════════
           SIDEBAR
        ══════════════════════════════════════════ */
        #sidebar {
            width: 260px;
            min-height: 100vh;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: transform .28s ease;
            box-shadow: 4px 0 20px rgba(0,0,0,.25);
        }

        /* Brand */
        .sidebar-brand {
            padding: 20px 22px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .sidebar-brand .brand-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; color: #fff; flex-shrink: 0;
        }
        .sidebar-brand .brand-text {
            font-size: 1.05rem; font-weight: 700;
            color: #fff; letter-spacing: .3px; line-height: 1.2;
        }
        .sidebar-brand .brand-sub {
            font-size: .7rem; color: rgba(255,255,255,.45);
            font-weight: 400;
        }

        /* User panel */
        .sidebar-user {
            padding: 16px 18px;
            border-bottom: 1px solid rgba(255,255,255,.07);
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 88px;
        }
        .sidebar-user img {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,.2);
            flex-shrink: 0;
            box-shadow: 0 8px 22px rgba(15,23,42,.25);
        }
        .sidebar-user > div:last-child {
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 4px;
        }
        .sidebar-user .user-name {
            font-size: .9rem;
            font-weight: 700;
            color: #f8fafc;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-user .user-role {
            font-size: .7rem;
            color: rgba(255,255,255,.45);
        }
        .sidebar-user .role-badge {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            background: rgba(245,158,11,.18);
            color: #fef3c7;
            font-size: .64rem;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 999px;
            border: 1px solid rgba(245,158,11,.3);
        }
        .sidebar-user .role-badge.invitado {
            background: rgba(79,70,229,.18);
            color: #dbeafe;
            border-color: rgba(79,70,229,.3);
        }

        /* Nav */
        .sidebar-nav {
            flex: 1;
            padding: 12px 10px;
            overflow-y: auto;
        }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 4px; }

        .nav-section-title {
            font-size: .65rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1.2px;
            color: rgba(255,255,255,.3);
            padding: 14px 12px 6px;
        }

        .sidebar-link {
            display: flex; align-items: center; gap: 11px;
            padding: 10px 14px;
            border-radius: 10px;
            margin-bottom: 2px;
            color: rgba(255,255,255,.72);
            text-decoration: none;
            font-size: .875rem; font-weight: 500;
            transition: all .18s ease;
            position: relative;
            white-space: nowrap;
        }
        /* allow adjusting sidebar label size independent from icon size */
        :root { --sidebar-label-size: .98rem; }
        .sidebar-link .link-label {
            display: inline-block;
            font-size: var(--sidebar-label-size);
            font-weight: 600;
            line-height: 1.2;
        }
        .sidebar-link:hover {
            background: rgba(255,255,255,.10);
            color: #fff;
            transform: translateX(2px);
        }
        .sidebar-link.active {
            background: linear-gradient(135deg, rgba(79,70,229,.55), rgba(124,58,237,.42));
            color: #fff;
            box-shadow: 0 4px 12px rgba(79,70,229,.24);
        }
        .sidebar-link.active::before {
            content: '';
            position: absolute; left: 0; top: 16%; bottom: 16%;
            width: 3px; border-radius: 0 3px 3px 0;
            background: #818cf8;
        }
        .sidebar-link i {
            width: 20px; text-align: center;
            font-size: 1rem; flex-shrink: 0;
        }

        /* ══════════════════════════════════════════
           TOPBAR
        ══════════════════════════════════════════ */
        #topbar {
            position: fixed;
            top: 0; left: 260px; right: 0;
            height: 68px;
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(148, 163, 184, .2);
            display: flex; align-items: center;
            padding: 0 24px;
            z-index: 999;
            gap: 12px;
            box-shadow: 0 10px 30px rgba(15,23,42,.06);
            transition: left .28s ease;
        }

        #toggleSidebar {
            background: none; border: none;
            width: 36px; height: 36px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: #64748b; cursor: pointer;
            transition: background .15s;
        }
        #toggleSidebar:hover { background: #f1f5f9; color: #1e293b; }

        .topbar-title {
            font-size: 1rem; font-weight: 600; color: #1e293b;
            flex: 1;
        }

        .topbar-user {
            display: flex; align-items: center; gap: 10px;
        }
        .topbar-user img {
            width: 36px; height: 36px;
            border-radius: 50%; object-fit: cover;
            border: 2px solid #e2e8f0;
        }
        .topbar-user .user-info { line-height: 1.25; text-align: right; }
        .topbar-user .user-info strong { font-size: .85rem; color: #1e293b; display: block; }
        .topbar-user .user-info span { font-size: .72rem; color: #94a3b8; }

        .btn-logout {
            background: #ef4444; color: #fff; border: none;
            padding: 6px 14px; border-radius: 8px;
            font-size: .8rem; font-weight: 600;
            cursor: pointer; transition: background .15s;
            display: flex; align-items: center; gap: 6px;
        }
        .btn-logout:hover { background: #dc2626; }

        /* ══════════════════════════════════════════
           MAIN CONTENT
        ══════════════════════════════════════════ */
        #main {
            margin-left: 260px;
            margin-top: 68px;
            flex: 1;
            padding: 28px 48px 40px;
            min-height: calc(100vh - 68px);
            transition: margin-left .28s ease;
            position: relative;
            z-index: 1;
        }

        .page-header {
            margin-bottom: 24px;
        }
        .page-header h1 {
            font-size: 1.55rem; font-weight: 700;
            color: #0f172a; margin: 0 0 6px;
            letter-spacing: -.02em;
        }
        .breadcrumb {
            background: none !important;
            padding: 0 !important;
            margin: 0 !important;
            font-size: .8rem;
        }
        .breadcrumb-item + .breadcrumb-item::before { color: #94a3b8; }
        .breadcrumb-item a { color: #6366f1; text-decoration: none; }
        .breadcrumb-item.active { color: #94a3b8; }

        /* ══════════════════════════════════════════
           STAT CARDS
        ══════════════════════════════════════════ */
        .stat-card {
            border-radius: 16px !important;
            border: none !important;
            padding: 22px !important;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,.12) !important;
        }
        .stat-card .bg-shape {
            position: absolute;
            right: -20px; bottom: -20px;
            width: 110px; height: 110px;
            border-radius: 50%;
            background: rgba(255,255,255,.1);
        }
        .stat-card .bg-shape2 {
            position: absolute;
            right: 20px; top: -30px;
            width: 70px; height: 70px;
            border-radius: 50%;
            background: rgba(255,255,255,.07);
        }
        .stat-card .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            background: rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; margin-bottom: 14px;
        }
        .stat-card .stat-number {
            font-size: 2.1rem; font-weight: 700; line-height: 1;
            margin-bottom: 4px;
        }
        .stat-card .stat-label {
            font-size: .82rem; opacity: .8; margin-bottom: 14px;
        }
        .stat-card .stat-link {
            font-size: .77rem; color: rgba(255,255,255,.75);
            text-decoration: none; display: inline-flex;
            align-items: center; gap: 5px;
        }
        .stat-card .stat-link:hover { color: #fff; }

        .card-indigo  { background: linear-gradient(135deg, #4f46e5, #7c3aed); }
        .card-emerald { background: linear-gradient(135deg, #10b981, #059669); }
        .card-amber   { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .card-rose    { background: linear-gradient(135deg, #f43f5e, #e11d48); }
        .card-slate   { background: linear-gradient(135deg, #334155, #1e293b); }

        /* ══════════════════════════════════════════
           KPI CARDS — DASHBOARD PREMIUM
        ══════════════════════════════════════════ */
        .kpi-card {
            position: relative;
            border-radius: 20px !important;
            padding: 20px 22px !important;
            overflow: hidden;
            color: #fff;
            border: none !important;
            transition: all .3s cubic-bezier(.2,.9,.3,1);
            box-shadow: 0 12px 32px rgba(15,23,42,.12) !important;
            min-height: 142px;
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 100% 0%, rgba(255,255,255,.18), transparent 45%),
                radial-gradient(circle at 0% 100%, rgba(0,0,0,.08), transparent 40%);
            pointer-events: none;
        }
        .kpi-card::after {
            content: '';
            position: absolute;
            width: 160px; height: 160px;
            right: -60px; bottom: -60px;
            border-radius: 50%;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
            pointer-events: none;
        }
        .kpi-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 48px rgba(15,23,42,.2) !important;
        }
        .kpi-icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            background: rgba(255,255,255,.18);
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255,255,255,.22);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
            color: #fff;
            margin-bottom: 14px;
            position: relative;
            z-index: 1;
            box-shadow: 0 6px 18px rgba(0,0,0,.1);
        }
        .kpi-num {
            position: relative;
            z-index: 1;
            font-size: 1.95rem;
            font-weight: 800;
            letter-spacing: -.02em;
            line-height: 1;
            color: #fff;
            margin-bottom: 6px;
            text-shadow: 0 2px 8px rgba(0,0,0,.12);
        }
        .kpi-num.kpi-num-big {
            font-size: 1.75rem;
        }
        .kpi-lbl {
            position: relative;
            z-index: 1;
            font-size: .78rem;
            font-weight: 500;
            color: rgba(255,255,255,.88);
            margin-bottom: 14px;
            letter-spacing: .01em;
        }
        .kpi-foot {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,.14);
            border: 1px solid rgba(255,255,255,.18);
            color: rgba(255,255,255,.95);
            font-size: .72rem;
            font-weight: 600;
            text-decoration: none;
            transition: all .2s;
        }
        .kpi-foot:hover {
            background: rgba(255,255,255,.22);
            color: #fff;
            text-decoration: none;
            transform: translateX(2px);
        }
        .kpi-foot.kpi-foot-soft {
            background: rgba(0,0,0,.1);
            border-color: rgba(255,255,255,.08);
            color: rgba(255,255,255,.9);
        }
        .kpi-indigo {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #7c3aed 100%);
        }
        .kpi-emerald {
            background: linear-gradient(135deg, #059669 0%, #10b981 50%, #14b8a6 100%);
        }
        .kpi-cyan {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 50%, #06b6d4 100%);
        }
        .kpi-violet {
            background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 50%, #a855f7 100%);
        }
        .kpi-rose {
            background: linear-gradient(135deg, #e11d48 0%, #f43f5e 50%, #fb7185 100%);
        }
        .kpi-amber {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 50%, #fbbf24 100%);
        }
        .kpi-gradient-success {
            background: linear-gradient(135deg, #047857 0%, #059669 30%, #10b981 70%, #34d399 100%);
            background-size: 200% 200%;
            animation: kpiShimmer 6s ease infinite;
        }
        @keyframes kpiShimmer {
            0%,100% { background-position: 0% 50%; }
            50%     { background-position: 100% 50%; }
        }

        /* Mini badges del dashboard */
        .mini-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .01em;
        }
        .mini-badge-indigo {
            background: rgba(99,102,241,.1);
            color: #4f46e5;
            border: 1px solid rgba(99,102,241,.15);
        }
        .mini-badge-amber {
            background: rgba(245,158,11,.1);
            color: #b45309;
            border: 1px solid rgba(245,158,11,.15);
        }
        .mini-badge-success {
            background: rgba(16,185,129,.1);
            color: #047857;
            border: 1px solid rgba(16,185,129,.15);
        }
        .mini-badge-violet {
            background: rgba(124,58,237,.1);
            color: #6d28d9;
            border: 1px solid rgba(124,58,237,.15);
        }
        .badge-danger-pill {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 11px;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(239,68,68,.1), rgba(244,63,94,.1));
            color: #b91c1c;
            border: 1px solid rgba(239,68,68,.18);
            font-size: .72rem;
            font-weight: 700;
        }
        .badge-success-pill {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 11px;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(16,185,129,.1), rgba(20,184,166,.1));
            color: #047857;
            border: 1px solid rgba(16,185,129,.18);
            font-size: .72rem;
            font-weight: 700;
        }

        /* Glass cards para gráficos y top listas */
        .glass-card {
            background: rgba(255,255,255,.72) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(226,232,240,.8) !important;
            border-radius: 22px !important;
            box-shadow: 0 14px 40px rgba(15,23,42,.06) !important;
            overflow: hidden;
        }
        .glass-card .card-header {
            background: linear-gradient(180deg, rgba(255,255,255,.85), rgba(248,250,252,.7)) !important;
            border-bottom: 1px solid rgba(226,232,240,.7) !important;
            padding: 18px 22px !important;
            font-size: .92rem;
        }
        body.dark-mode .glass-card {
            background: rgba(11,20,44,.72) !important;
            border-color: rgba(255,255,255,.05) !important;
            box-shadow: 0 18px 50px rgba(0,0,0,.3) !important;
        }
        body.dark-mode .glass-card .card-header {
            background: linear-gradient(180deg, rgba(8,20,34,.75), rgba(5,15,34,.55)) !important;
            border-bottom-color: rgba(255,255,255,.05) !important;
            color: #e2e8f0 !important;
        }
        body.dark-mode .mini-badge-indigo { background: rgba(99,102,241,.16); color: #a5b4fc; border-color: rgba(99,102,241,.25); }
        body.dark-mode .mini-badge-amber  { background: rgba(245,158,11,.16); color: #fcd34d; border-color: rgba(245,158,11,.25); }
        body.dark-mode .mini-badge-success{ background: rgba(16,185,129,.16); color: #6ee7b7; border-color: rgba(16,185,129,.25); }
        body.dark-mode .mini-badge-violet { background: rgba(124,58,237,.16); color: #c4b5fd; border-color: rgba(124,58,237,.25); }
        body.dark-mode .badge-danger-pill {
            background: linear-gradient(135deg, rgba(239,68,68,.18), rgba(244,63,94,.15));
            color: #fca5a5;
            border-color: rgba(239,68,68,.25);
        }
        body.dark-mode .badge-success-pill {
            background: linear-gradient(135deg, rgba(16,185,129,.18), rgba(20,184,166,.15));
            color: #6ee7b7;
            border-color: rgba(16,185,129,.25);
        }

        /* ══════════════════════════════════════════
           TOP LISTS — Productos y Clientes
        ══════════════════════════════════════════ */
        .list-list {
            display: flex;
            flex-direction: column;
        }
        .list-row {
            display: grid;
            grid-template-columns: 48px 1fr auto;
            gap: 14px;
            align-items: center;
            padding: 14px 22px;
            transition: all .2s ease;
            position: relative;
            border-bottom: 1px solid rgba(226,232,240,.6);
        }
        .list-row:last-child { border-bottom: none; }
        .list-row:hover {
            background: linear-gradient(90deg, rgba(99,102,241,.04), rgba(124,58,237,.02), transparent);
        }
        .list-row::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            background: transparent;
            transition: all .2s;
        }
        .list-row:hover::before {
            background: linear-gradient(180deg, #4f46e5, #7c3aed);
        }
        .list-pos {
            width: 40px; height: 40px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem;
            font-weight: 800;
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            color: #64748b;
            box-shadow: 0 4px 10px rgba(15,23,42,.04);
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }
        .list-pos.list-pos-0 {
            background: linear-gradient(135deg, #fcd34d, #f59e0b);
            color: #78350f;
            box-shadow: 0 8px 20px rgba(245,158,11,.3);
        }
        .list-pos.list-pos-0::after {
            content: '\f521';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: -8px; right: -6px;
            font-size: .75rem;
            background: #fff;
            width: 18px; height: 18px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #f59e0b;
            box-shadow: 0 2px 6px rgba(0,0,0,.1);
        }
        .list-pos.list-pos-1 {
            background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
            color: #334155;
            box-shadow: 0 6px 14px rgba(148,163,184,.25);
        }
        .list-pos.list-pos-2 {
            background: linear-gradient(135deg, #fed7aa, #fdba74);
            color: #7c2d12;
            box-shadow: 0 6px 14px rgba(249,115,22,.2);
        }
        .list-pos.list-pos-3, .list-pos.list-pos-4 {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            color: #475569;
            box-shadow: 0 4px 10px rgba(15,23,42,.05);
        }
        .list-data { min-width: 0; }
        .list-title {
            font-size: .92rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .list-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 4px 10px;
            font-size: .74rem;
            color: #64748b;
            font-weight: 500;
        }
        .list-meta span {
            display: inline-flex; align-items: center; gap: 4px;
        }
        .list-meta i {
            font-size: .7rem;
            color: #94a3b8;
        }
        .list-amount {
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: -.01em;
            color: #0f172a;
            background: linear-gradient(135deg, rgba(79,70,229,.08), rgba(16,185,129,.08));
            padding: 7px 13px;
            border-radius: 12px;
            border: 1px solid rgba(99,102,241,.1);
            white-space: nowrap;
            position: relative;
            z-index: 1;
        }
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 50px 24px;
            color: #94a3b8;
            font-size: .88rem;
            font-weight: 500;
        }
        .empty-state i {
            font-size: 2rem;
            color: #cbd5e1;
        }

        /* Dark mode — lists */
        body.dark-mode .list-row {
            border-bottom-color: rgba(255,255,255,.04);
        }
        body.dark-mode .list-row:hover {
            background: linear-gradient(90deg, rgba(99,102,241,.08), rgba(124,58,237,.04), transparent);
        }
        body.dark-mode .list-pos {
            background: linear-gradient(135deg, rgba(255,255,255,.06), rgba(255,255,255,.02));
            color: #94a3b8;
            box-shadow: 0 4px 10px rgba(0,0,0,.2);
            border: 1px solid rgba(255,255,255,.05);
        }
        body.dark-mode .list-pos.list-pos-0 {
            background: linear-gradient(135deg, #fcd34d, #f59e0b);
            color: #78350f;
            border-color: transparent;
            box-shadow: 0 10px 24px rgba(245,158,11,.35);
        }
        body.dark-mode .list-pos.list-pos-0::after { color: #b45309; }
        body.dark-mode .list-pos.list-pos-1 {
            background: linear-gradient(135deg, #e2e8f0, #94a3b8);
            color: #1e293b;
            border-color: transparent;
            box-shadow: 0 8px 18px rgba(148,163,184,.15);
        }
        body.dark-mode .list-pos.list-pos-2 {
            background: linear-gradient(135deg, #fed7aa, #fb923c);
            color: #7c2d12;
            border-color: transparent;
            box-shadow: 0 8px 18px rgba(249,115,22,.25);
        }
        body.dark-mode .list-title {
            color: #e2e8f0;
        }
        body.dark-mode .list-meta {
            color: #94a3b8;
        }
        body.dark-mode .list-meta i {
            color: #64748b;
        }
        body.dark-mode .list-amount {
            color: #f1f5f9;
            background: linear-gradient(135deg, rgba(99,102,241,.14), rgba(16,185,129,.12));
            border-color: rgba(99,102,241,.2);
            box-shadow: 0 4px 14px rgba(0,0,0,.15);
        }
        body.dark-mode .empty-state { color: #64748b; }
        body.dark-mode .empty-state i { color: #475569; }

        /* ══════════════════════════════════════════
           CARDS GENERALES
        ══════════════════════════════════════════ */
        .card {
            border: none !important;
            border-radius: 18px !important;
            box-shadow: var(--shadow) !important;
            background: rgba(255,255,255,.95) !important;
            backdrop-filter: blur(10px);
        }
        /* No permitir que la regla genérica .card sobrescriba las tarjetas de estadísticas */
        .card:not(.stat-card) {
            background: rgba(255,255,255,.95) !important;
        }
        .card.stat-card:not([class*="card-"]):not([class*="bg-gradient-"]) {
            background: rgba(255,255,255,.95) !important;
        }
        /* Re-definir gradientes para las stat-cards con mayor especificidad */
        .stat-card.card-indigo, .card.stat-card.card-indigo { background: linear-gradient(135deg, #4f46e5, #7c3aed) !important; color: #fff !important; }
        .stat-card.card-emerald, .card.stat-card.card-emerald { background: linear-gradient(135deg, #10b981, #059669) !important; color: #fff !important; }
        .stat-card.card-amber, .card.stat-card.card-amber   { background: linear-gradient(135deg, #f59e0b, #d97706) !important; color: #fff !important; }
        .stat-card.card-rose, .card.stat-card.card-rose    { background: linear-gradient(135deg, #f43f5e, #e11d48) !important; color: #fff !important; }
        .stat-card.card-slate, .card.stat-card.card-slate   { background: linear-gradient(135deg, #334155, #1e293b) !important; color: #fff !important; }
        /* Gradientes para reportes */
        .stat-card.bg-gradient-success, .card.stat-card.bg-gradient-success {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 10px 25px -5px rgba(16,185,129,.4) !important;
        }
        .stat-card.bg-gradient-primary, .card.stat-card.bg-gradient-primary {
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 10px 25px -5px rgba(79,70,229,.4) !important;
        }
        .stat-card.bg-gradient-warning, .card.stat-card.bg-gradient-warning {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 10px 25px -5px rgba(245,158,11,.4) !important;
        }
        .stat-card .stat-number { color: #ffffff !important; font-weight: 800; text-shadow: 0 2px 8px rgba(0,0,0,.25); }
        .stat-card .stat-label { color: rgba(255,255,255,.95) !important; font-weight: 700; text-shadow: 0 1px 4px rgba(0,0,0,.2); }
        .card-header {
            background: rgba(248,250,252,.95) !important;
            border-bottom: 1px solid rgba(226,232,240,.8) !important;
            border-radius: 18px 18px 0 0 !important;
            padding: 16px 20px !important;
            font-weight: 700;
            color: #0f172a;
        }
        .card-header.bg-primary { background: linear-gradient(135deg,#4f46e5,#7c3aed) !important; }
        .card-header.bg-warning { background: linear-gradient(135deg,#f59e0b,#d97706) !important; color:#fff !important; }
        .card-header.bg-success { background: linear-gradient(135deg,#10b981,#059669) !important; }
        .card-header.bg-dark    { background: linear-gradient(135deg,#334155,#1e293b) !important; }

          /* ══════════════════════════════════════════
              TABLES
          ══════════════════════════════════════════ */
        .table thead th {
            background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%) !important;
            color: #475569 !important;
            font-size: .73rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: .6px !important;
            border-bottom: 2px solid #e8ecf0 !important;
            padding: 12px 14px !important;
            white-space: nowrap;
        }
        .table td {
            vertical-align: middle !important;
            font-size: .9rem !important;
            color: var(--table-text) !important;
            padding: 12px 14px !important;
            border-color: #f1f5f9 !important;
        }
        .table-hover tbody tr:hover { background: linear-gradient(90deg, rgba(99,102,241,.04), rgba(124,58,237,.03)) !important; }

        /* Estilos para badges y totales en modo claro (día) */
        .bg-gradient-success { background: linear-gradient(135deg,#10b981,#059669) !important; color: #fff !important; }
        .bg-gradient-primary { background: linear-gradient(135deg,#4f46e5,#7c3aed) !important; color: #fff !important; }
        .bg-gradient-warning { background: linear-gradient(135deg,#f59e0b,#d97706) !important; color: #fff !important; }

        /* Tarjetas estadísticas con gradiente: números en blanco brillante y alta legibilidad en modo día y noche */
        .stat-card.bg-gradient-success .stat-number,
        .stat-card.bg-gradient-primary .stat-number,
        .stat-card.bg-gradient-warning .stat-number,
        .stat-card.card-indigo .stat-number,
        .stat-card.card-emerald .stat-number,
        .stat-card.card-amber .stat-number,
        .stat-card.card-rose .stat-number,
        .stat-card.card-slate .stat-number,
        .card.stat-card.bg-gradient-success .stat-number,
        .card.stat-card.bg-gradient-primary .stat-number,
        .card.stat-card.bg-gradient-warning .stat-number {
            font-size: 1.85rem !important;
            letter-spacing: -0.02em;
            color: #ffffff !important;
            text-shadow: 0 2px 10px rgba(2,6,23,.35) !important;
            font-weight: 800 !important;
        }
        .stat-card .stat-label,
        .card.stat-card .stat-label {
            color: rgba(255,255,255,.95) !important;
            font-weight: 700 !important;
            text-shadow: 0 1px 4px rgba(2,6,23,.25);
        }

        /* Tabla Pedidos / Reportes: badges de estado y totales destacados */
        #tablaPedidos tbody td:nth-child(3) .badge,
        #tablaReporte tbody td:nth-child(3) .badge {
            background: linear-gradient(135deg,#0ea5e9,#0284c7) !important;
            color: #fff !important;
            font-weight: 700;
            padding: 5px 9px !important;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(2,132,199,.15);
        }

        /* Columna Total en reportes: destacar en día */
        #tablaReporte tbody td:nth-child(5) strong {
            background: linear-gradient(135deg,#059669,#10b981);
            color: #fff !important;
            padding: 6px 10px; border-radius: 8px; display: inline-block;
            box-shadow: 0 6px 18px rgba(5,150,105,.08);
            font-weight: 800;
        }

        /* Alineación y espaciado para productos en Pedidos */
        #tablaPedidos tbody td:nth-child(3) { padding-top: 8px !important; }
        #tablaPedidos tbody td:nth-child(3) .badge { white-space: normal !important; }

        /* Mejoras visuales específicas por tabla */
        /* Categorías: columna 'Productos' (4ª columna) - centrar y contraste */
        #tablaCategorias tbody td:nth-child(4) {
            text-align: center !important;
            color: #0f172a !important;
            font-weight: 700;
        }
        #tablaCategorias tbody td:nth-child(4) .badge {
            background: linear-gradient(135deg,#4f46e5,#7c3aed) !important;
            color: #fff !important;
            box-shadow: 0 2px 8px rgba(79,70,229,.18);
            font-weight: 700;
        }

        /* Productos: centrar Nombre/Precio/Stock y mejorar contraste de badges */
        #tablaProductos tbody td:nth-child(3) { /* Nombre */
            text-align: left !important;
            color: var(--table-text) !important;
            font-weight: 700;
        }
        #tablaProductos tbody td:nth-child(4), /* Precio */
        #tablaProductos tbody td:nth-child(5)  /* Stock */ {
            text-align: center !important;
            vertical-align: middle !important;
        }
        /* Precio */
        #tablaProductos tbody td:nth-child(4) .badge {
            background: linear-gradient(135deg,#059669,#10b981) !important;
            color: #fff !important;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(5,150,105,.12);
            font-size: .9rem !important;
            padding: 6px 10px !important;
        }
        /* Stock: enfatizar según nivel */
        #tablaProductos tbody td:nth-child(5) .badge {
            font-weight: 800;
            font-size: .95rem !important;
            padding: 6px 10px !important;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }
        /* estilos concretos por clase de badge (según lógica del blade) */
        #tablaProductos tbody td:nth-child(5) .badge-danger {
            background: linear-gradient(135deg,#ef4444,#dc2626) !important;
            color: #fff !important;
        }
        #tablaProductos tbody td:nth-child(5) .badge-warning {
            background: linear-gradient(135deg,#f59e0b,#d97706) !important;
            color: #fff !important;
        }
        #tablaProductos tbody td:nth-child(5) .badge-success {
            background: linear-gradient(135deg,#10b981,#059669) !important;
            color: #fff !important;
        }

        /* Pedidos: hacer 'Estado' más visible (5ª columna) */
        #tablaPedidos tbody td:nth-child(5) .badge {
            text-transform: uppercase;
            font-weight: 800;
            padding: 6px 10px !important;
            border-radius: 999px;
        }
        #tablaPedidos tbody td:nth-child(5) .badge.badge-success {
            background: linear-gradient(135deg,#059669,#10b981) !important;
            color: #fff !important;
            box-shadow: 0 2px 10px rgba(5,150,105,.12);
        }
        #tablaPedidos tbody td:nth-child(5) .badge.badge-secondary {
            background: linear-gradient(135deg,#6b7280,#374151) !important;
            color: #fff !important;
            box-shadow: 0 2px 10px rgba(55,65,81,.08);
        }

        /* Usuarios: resaltar rol (5ª columna) */
        #tablaUsuarios tbody td:nth-child(5) .badge {
            font-weight: 800;
            color: #fff !important;
            box-shadow: 0 2px 10px rgba(15,23,42,.06);
        }
        #tablaUsuarios tbody td:nth-child(5) .badge.badge-danger {
            background: linear-gradient(135deg,#ef4444,#dc2626) !important;
        }
        #tablaUsuarios tbody td:nth-child(5) .badge.badge-primary {
            background: linear-gradient(135deg,#4f46e5,#7c3aed) !important;
        }

        /* Resaltar contenido no visible / texto truncado: hacer overflow visible en badges y permitir wrap cuando sea necesario */
        .table td .badge, .table td span {
            white-space: normal !important;
        }
        /* Aumentar contraste al pasar el ratón para ver contenido oculto */
        .table tbody tr:hover td { filter: brightness(1.02); }

        /* Tabla Pedidos: alinear contenido de 'Productos' (3ª columna) correctamente y evitar desajustes */
        #tablaPedidos tbody td:nth-child(3) {
            text-align: left !important;
            vertical-align: top !important;
            padding-top: 10px !important;
        }
        #tablaPedidos tbody td:nth-child(3) .badge {
            display: block !important;
            width: 100% !important;
            box-shadow: 0 2px 6px rgba(15,23,42,.04);
            background: linear-gradient(135deg,#0ea5e9,#0284c7) !important;
            color: #fff !important;
            font-weight: 700;
            margin-bottom: 6px !important;
        }

        /* Modo noche: unificar tonos y mejorar CONTRASTE en tablas */
        body.dark-mode {
            --table-text: #e2e8f0;
        }
        body.dark-mode .table thead th {
            background: rgba(8,20,34,.78) !important;
            color: #f1f5f9 !important;
            font-size: .78rem !important;
            font-weight: 800 !important;
            letter-spacing: .7px !important;
            border-bottom: 2px solid rgba(99,102,241,.45) !important;
            padding: 14px 16px !important;
        }
        body.dark-mode .card, body.dark-mode .page-panel {
            background: rgba(2,6,23,.65) !important;
            border-color: rgba(255,255,255,.03) !important;
        }
        body.dark-mode .table {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: rgba(255,255,255,.012);
            border-color: rgba(255,255,255,.04) !important;
        }
        body.dark-mode .table td {
            color: var(--table-text) !important;
            font-size: .96rem !important;
            font-weight: 500 !important;
            line-height: 1.55 !important;
            padding: 14px 16px !important;
            border-color: rgba(255,255,255,.04) !important;
        }
        body.dark-mode .table td strong {
            color: #f8fafc !important;
            font-weight: 700 !important;
        }
        body.dark-mode .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(255,255,255,.012) !important;
        }
        body.dark-mode .table-hover tbody tr:hover {
            background: linear-gradient(90deg, rgba(99,102,241,.10), rgba(124,58,237,.06)) !important;
        }
        body.dark-mode .table-hover tbody tr:hover td {
            color: #fff !important;
        }
        body.dark-mode .table .badge {
            font-size: .85rem !important;
            padding: 6px 12px !important;
            font-weight: 700 !important;
            border-radius: 10px !important;
        }
        /* Badge genérico light (usado en categoría de productos) - adaptar dark-mode */
        body.dark-mode .badge.badge-light,
        body.dark-mode .badge-light,
        body.dark-mode span.badge.bg-light {
            background: rgba(99,102,241,.18) !important;
            color: #c7d2fe !important;
            border-color: rgba(99,102,241,.35) !important;
        }
        body.dark-mode .text-dark {
            color: #e2e8f0 !important;
        }
        /* Categorías tabla: columna Productos */
        body.dark-mode #tablaCategorias tbody td:nth-child(4) {
            color: #e2e8f0 !important;
        }
        body.dark-mode #tablaCategorias tbody td:nth-child(4) .badge {
            background: linear-gradient(135deg,#4f46e5,#7c3aed) !important;
            color: #fff !important;
            box-shadow: 0 4px 14px rgba(79,70,229,.22) !important;
        }
        /* Productos tabla */
        body.dark-mode #tablaProductos tbody td:nth-child(3) {
            color: #f1f5f9 !important;
            font-weight: 700 !important;
            font-size: 1rem !important;
        }
        body.dark-mode #tablaProductos tbody td:nth-child(4) .badge {
            background: linear-gradient(135deg,#059669,#10b981) !important;
            color: #fff !important;
            font-weight: 800 !important;
            box-shadow: 0 4px 14px rgba(5,150,105,.22) !important;
            font-size: .95rem !important;
        }
        body.dark-mode #tablaProductos tbody td:nth-child(5) .badge {
            font-weight: 800 !important;
            font-size: 1rem !important;
            text-shadow: 0 1px 2px rgba(0,0,0,.25);
        }
        body.dark-mode #tablaProductos tbody td:nth-child(5) .badge-danger {
            background: linear-gradient(135deg,#ef4444,#dc2626) !important;
            color: #fff !important;
            box-shadow: 0 4px 14px rgba(239,68,68,.22) !important;
        }
        body.dark-mode #tablaProductos tbody td:nth-child(5) .badge-warning {
            background: linear-gradient(135deg,#f59e0b,#d97706) !important;
            color: #fff !important;
            box-shadow: 0 4px 14px rgba(245,158,11,.22) !important;
        }
        body.dark-mode #tablaProductos tbody td:nth-child(5) .badge-success {
            background: linear-gradient(135deg,#10b981,#059669) !important;
            color: #fff !important;
            box-shadow: 0 4px 14px rgba(16,185,129,.22) !important;
        }
        /* Pedidos tabla: productos y estado */
        body.dark-mode #tablaPedidos tbody td:nth-child(3) .badge,
        body.dark-mode #tablaReporte tbody td:nth-child(3) .badge,
        body.dark-mode #tablaReporte tbody td:nth-child(3) span {
            background: linear-gradient(135deg,#0284c7,#0ea5e9) !important;
            color: #fff !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 14px rgba(2,132,199,.20) !important;
        }
        body.dark-mode #tablaReporte tbody td:nth-child(5) strong {
            background: linear-gradient(135deg,#059669,#10b981) !important;
            color: #fff !important;
            box-shadow: 0 4px 14px rgba(5,150,105,.22) !important;
            font-weight: 800 !important;
        }
        body.dark-mode #tablaPedidos tbody td:nth-child(5) .badge.badge-success {
            background: linear-gradient(135deg,#059669,#10b981) !important;
            color: #fff !important;
            box-shadow: 0 4px 14px rgba(5,150,105,.22) !important;
            font-weight: 800 !important;
        }
        body.dark-mode #tablaPedidos tbody td:nth-child(5) .badge.badge-secondary {
            background: linear-gradient(135deg,#475569,#1e293b) !important;
            color: #e2e8f0 !important;
            box-shadow: 0 4px 14px rgba(15,23,42,.35) !important;
            font-weight: 800 !important;
        }
        /* Usuarios tabla: rol */
        body.dark-mode #tablaUsuarios tbody td:nth-child(5) .badge {
            font-weight: 800 !important;
            color: #fff !important;
        }
        body.dark-mode #tablaUsuarios tbody td:nth-child(5) .badge-danger {
            background: linear-gradient(135deg,#ef4444,#dc2626) !important;
            box-shadow: 0 4px 14px rgba(239,68,68,.22) !important;
        }
        body.dark-mode #tablaUsuarios tbody td:nth-child(5) .badge-primary {
            background: linear-gradient(135deg,#4f46e5,#7c3aed) !important;
            box-shadow: 0 4px 14px rgba(79,70,229,.22) !important;
        }
        body.dark-mode .table .bg-light,
        body.dark-mode .table img + div.bg-light {
            background: rgba(255,255,255,.06) !important;
        }
        body.dark-mode .table img + div.bg-light i {
            color: #94a3b8 !important;
        }

        .page-panel {
            background: rgba(255,255,255,.95);
            border: 1px solid rgba(226,232,240,.8);
            border-radius: 20px;
            box-shadow: var(--shadow);
            padding: 22px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(79,70,229,.08);
            color: var(--primary);
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .status-pill.success {
            background: rgba(16,185,129,.12);
            color: var(--success);
        }

        /* ══════════════════════════════════════════
           BUTTONS
        ══════════════════════════════════════════ */
        .btn { border-radius: 10px !important; font-weight: 600 !important; font-size: .85rem !important; transition: all .2s ease; }
        .btn:hover { transform: translateY(-1px); }
        .btn-sm { border-radius: 8px !important; font-size: .8rem !important; }
        .btn-primary   { background: #4f46e5 !important; border-color: #4f46e5 !important; }
        .btn-primary:hover   { background: #4338ca !important; border-color: #4338ca !important; }
        .btn-success   { background: #10b981 !important; border-color: #10b981 !important; }
        .btn-success:hover   { background: #059669 !important; border-color: #059669 !important; }
        .btn-warning   { background: #f59e0b !important; border-color: #f59e0b !important; color:#fff !important; }
        .btn-warning:hover   { background: #d97706 !important; border-color: #d97706 !important; }
        .btn-danger    { background: #ef4444 !important; border-color: #ef4444 !important; }
        .btn-danger:hover    { background: #dc2626 !important; border-color: #dc2626 !important; }
        .btn-secondary { background: #64748b !important; border-color: #64748b !important; color:#fff !important; }

        #toggleDarkMode {
            width: 38px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(71,85,105,.28) !important;
            background: rgba(255,255,255,.72) !important;
            color: #334155 !important;
            box-shadow: 0 4px 12px rgba(15,23,42,.08);
        }
        #toggleDarkMode:hover,
        #toggleDarkMode:focus-visible,
        #toggleDarkMode:active {
            background: #e0e7ff !important;
            border-color: #6366f1 !important;
            color: #3730a3 !important;
        }
        html.dark-mode #toggleDarkMode,
        body.dark-mode #toggleDarkMode {
            background: rgba(30,41,59,.92) !important;
            border-color: rgba(165,180,252,.5) !important;
            color: #fef3c7 !important;
            box-shadow: 0 4px 16px rgba(0,0,0,.28);
        }
        html.dark-mode #toggleDarkMode:hover,
        html.dark-mode #toggleDarkMode:focus-visible,
        html.dark-mode #toggleDarkMode:active,
        body.dark-mode #toggleDarkMode:hover,
        body.dark-mode #toggleDarkMode:focus-visible,
        body.dark-mode #toggleDarkMode:active {
            background: #334155 !important;
            border-color: #fbbf24 !important;
            color: #fde68a !important;
        }

        /* ══════════════════════════════════════════
           BADGES
        ══════════════════════════════════════════ */
        .badge { border-radius: 6px !important; font-weight: 500 !important; }
        .badge.bg-primary { background: #4f46e5 !important; }
        .badge.bg-success { background: #10b981 !important; }
        .badge.bg-warning { background: #f59e0b !important; color:#fff !important; }
        .badge.bg-danger  { background: #ef4444 !important; }
        .badge.bg-secondary { background: #94a3b8 !important; }
        .badge.bg-info    { background: #0ea5e9 !important; }

        /* ══════════════════════════════════════════
           ETIQUETAS PRODUCTO (QR + BARCODE)
        ══════════════════════════════════════════ */
        .label-preview {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px 20px;
            color: #0f172a;
            max-width: 420px;
            margin: 0 auto;
        }
        .label-preview .label-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
            line-height: 1.2;
        }
        .label-preview .label-sku {
            font-size: .78rem;
            font-weight: 700;
            color: #4f46e5;
            letter-spacing: .8px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .label-preview .label-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 8px 0 12px;
            gap: 10px;
        }
        .label-preview .label-price {
            font-size: 1.25rem;
            font-weight: 800;
            color: #059669;
        }
        .label-preview .label-cat {
            font-size: .75rem;
            color: #475569;
            background: #eef2ff;
            padding: 4px 10px;
            border-radius: 999px;
            font-weight: 600;
        }
        .label-preview .label-codes {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 14px;
            align-items: center;
        }
        .label-preview .label-barcode {
            text-align: center;
        }
        .label-preview .label-barcode svg {
            width: 100%;
            height: 62px;
            display: block;
        }
        .label-preview .label-barcode-text {
            font-family: 'Courier New', ui-monospace, monospace;
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: 2.5px;
            margin-top: 4px;
            color: #0f172a;
        }
        .label-preview .label-qr {
            text-align: center;
        }
        .label-preview .label-qr canvas,
        .label-preview .label-qr img {
            border: 4px solid #fff;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(15,23,42,.08);
            background: #fff;
            display: inline-block !important;
        }
        .label-preview .label-footer {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #cbd5e1;
            text-align: center;
            font-size: .68rem;
            color: #64748b;
            font-weight: 500;
        }

        body.dark-mode .label-preview {
            background: rgba(8,20,34,.75);
            border-color: rgba(99,102,241,.25);
            color: #e2e8f0;
        }
        body.dark-mode .label-preview .label-title {
            color: #f1f5f9;
        }
        body.dark-mode .label-preview .label-sku {
            color: #a5b4fc;
        }
        body.dark-mode .label-preview .label-price {
            color: #4ade80;
        }
        body.dark-mode .label-preview .label-cat {
            background: rgba(99,102,241,.18);
            color: #c7d2fe;
        }
        body.dark-mode .label-preview .label-barcode-text {
            color: #e2e8f0;
        }
        body.dark-mode .label-preview .label-footer {
            border-top-color: rgba(255,255,255,.08);
            color: #94a3b8;
        }

        /* Modal custom */
        .modal-content {
            border-radius: 20px !important;
            border: none !important;
            box-shadow: 0 24px 60px rgba(15,23,42,.25) !important;
        }
        .modal-header {
            background: linear-gradient(135deg,#4f46e5,#7c3aed) !important;
            color: #fff !important;
            border-radius: 20px 20px 0 0 !important;
            border: none !important;
            padding: 16px 22px !important;
        }
        .modal-header .btn-close { filter: invert(1) grayscale(100%) brightness(1.6); }
        .modal-header .modal-title { font-weight: 800 !important; }

        @media print {
            body > *:not(#labelPrintArea) { display: none !important; }
            body { background: #fff !important; padding: 0 !important; margin: 0 !important; }
            #labelPrintArea {
                position: fixed;
                top: 0; left: 0;
                width: 100%;
                padding: 20px;
                background: #fff;
            }
            .label-preview {
                border: none !important;
                box-shadow: none !important;
                max-width: 100%;
                page-break-inside: avoid;
                break-inside: avoid;
                margin: 0 0 18px 0 !important;
            }
            .label-preview .label-qr canvas,
            .label-preview .label-qr img,
            .label-preview .label-barcode svg {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        /* ══════════════════════════════════════════
           PRODUCTOS DENTRO DE TABLAS (Pedidos / Reportes)
        ══════════════════════════════════════════ */
        .product-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 280px;
        }
        .product-list .product-card {
            display: grid;
            grid-template-columns: 36px 1fr auto;
            gap: 12px;
            align-items: center;
            padding: 9px 12px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            box-shadow: 0 2px 5px rgba(15,23,42,.04);
            transition: all .2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .product-list .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79,70,229,.1);
            border-color: #c7d2fe;
        }
        .product-card .p-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: #fff;
            font-size: .88rem;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(79,70,229,.25);
        }
        .product-card .p-info {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .product-card .p-name {
            font-size: .86rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.25;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .product-card .p-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px 10px;
            font-size: .72rem;
            color: #64748b;
            font-weight: 600;
        }
        .product-card .p-meta span.p-unit {
            background: #f1f5f9;
            color: #475569;
            padding: 2px 7px;
            border-radius: 6px;
            font-weight: 700;
        }
        .product-card .p-meta span.p-unit::before { content: 'c/u: '; color: #94a3b8; font-weight: 500; }
        .product-card .p-sub {
            text-align: right;
            min-width: 86px;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 3px;
        }
        .product-card .p-sub .p-qty {
            font-size: .72rem;
            color: #fff;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            padding: 2px 8px;
            border-radius: 999px;
            font-weight: 800;
            letter-spacing: .3px;
            box-shadow: 0 2px 6px rgba(79,70,229,.25);
        }
        .product-card .p-sub .p-amount {
            font-size: .88rem;
            font-weight: 800;
            color: #059669;
        }
        .product-card.is-deleted .p-icon {
            background: linear-gradient(135deg, #64748b, #475569);
            box-shadow: 0 4px 10px rgba(71,85,105,.2);
        }
        .product-card.is-deleted .p-name {
            color: #94a3b8;
            text-decoration: line-through;
        }
        .product-list .product-summary {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            padding: 6px 4px 0;
            margin-top: 2px;
            border-top: 1px dashed #cbd5e1;
            gap: 6px;
        }
        .product-list .product-summary .ps-pill {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 3px 9px;
            border-radius: 999px;
            font-weight: 700;
            font-size: .73rem;
            color: #334155;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .product-list .product-summary .ps-pill i { opacity: .85; }

        /* Dark-mode: productos en pedidos/reportes */
        body.dark-mode .product-list .product-card {
            border-color: rgba(255,255,255,.08);
            background: rgba(30,41,59,.75);
            box-shadow: 0 4px 12px rgba(0,0,0,.25);
        }
        body.dark-mode .product-list .product-card:hover {
            border-color: rgba(99,102,241,.45);
            background: rgba(30,41,59,.95);
            box-shadow: 0 8px 24px rgba(0,0,0,.35);
        }
        body.dark-mode .product-card .p-name {
            color: #f1f5f9 !important;
        }
        body.dark-mode .product-card .p-meta span.p-unit {
            background: rgba(255,255,255,.06);
            color: #94a3b8 !important;
        }
        body.dark-mode .product-card .p-meta span.p-unit::before { color: #64748b !important; }
        body.dark-mode .product-card .p-sub .p-amount {
            color: #34d399 !important;
        }
        body.dark-mode .product-card.is-deleted .p-name {
            color: #64748b !important;
        }
        body.dark-mode .product-list .product-summary {
            border-top-color: rgba(255,255,255,.1);
        }
        body.dark-mode .product-list .product-summary .ps-pill {
            background: rgba(255,255,255,.06);
            color: #e2e8f0 !important;
        }
        body.dark-mode .product-card .p-sub .p-qty {
            color: #fff !important;
        }

        /* ══════════════════════════════════════════
           FORMS
        ══════════════════════════════════════════ */
        .form-control, .form-select {
            border-radius: 9px !important;
            border: 1.5px solid #e2e8f0 !important;
            font-size: .9rem !important;
            padding: 9px 14px !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99,102,241,.15) !important;
        }
        .form-label { font-weight: 600; font-size: .85rem; color: #374151; margin-bottom: 6px; }

        /* ══════════════════════════════════════════
           ALERTS / TOASTS
        ══════════════════════════════════════════ */
        .alert {
            border-radius: 10px !important;
            border: none !important;
            font-size: .875rem !important;
        }
        .alert-success { background: #ecfdf5 !important; color: #065f46 !important;
            box-shadow: 0 2px 8px rgba(16,185,129,.15) !important; }
        .alert-danger  { background: #fef2f2 !important; color: #7f1d1d !important;
            box-shadow: 0 2px 8px rgba(239,68,68,.15) !important; }
        .toast-container {
            z-index: 1080;
        }
        .toast {
            border: 1px solid rgba(148,163,184,.18) !important;
            border-radius: 16px !important;
            box-shadow: 0 16px 34px rgba(15,23,42,.12) !important;
            overflow: hidden;
            background: rgba(255,255,255,.94);
            backdrop-filter: blur(10px);
        }
        .toast-header {
            background: transparent;
            border-bottom: 0;
            padding: 0.85rem 1rem 0.35rem;
            font-weight: 600;
        }
        .toast-body {
            padding: 0.2rem 1rem 1rem;
            font-size: .9rem;
            color: #334155;
        }
        .toast-success {
            border-left: 4px solid #10b981 !important;
        }
        .toast-danger {
            border-left: 4px solid #ef4444 !important;
        }
        body.dark-mode .toast {
            background: rgba(15,23,42,.9) !important;
            border-color: rgba(148,163,184,.16) !important;
            box-shadow: 0 18px 38px rgba(2,6,23,.45) !important;
        }
        body.dark-mode .toast-body {
            color: #e2e8f0 !important;
        }
        body.dark-mode .toast-header {
            color: #f8fafc !important;
        }

        /* ══════════════════════════════════════════
           SIDEBAR COLLAPSED
        ══════════════════════════════════════════ */
        @media (min-width: 769px) {
            body.sidebar-collapsed #sidebar {
                width: 78px;
                transform: translateX(0);
            }
            body.sidebar-collapsed #topbar { left: 78px; }
            body.sidebar-collapsed #main { margin-left: 78px; }
            body.sidebar-collapsed .sidebar-brand {
                justify-content: center;
                padding: 20px 12px;
            }
            /* mini mode: keep only icons visible; hide labels but keep structure for tooltips
               use visibility:hidden to avoid layout shift in some cases */
            body.sidebar-collapsed .sidebar-brand > div:last-child,
            body.sidebar-collapsed .sidebar-user > div,
            body.sidebar-collapsed .sidebar-user .role-badge,
            body.sidebar-collapsed .nav-section-title,
            body.sidebar-collapsed .sidebar-link .link-label {
                display: none;
            }
            body.sidebar-collapsed .sidebar-user {
                justify-content: center;
                padding: 16px 10px;
            }
            body.sidebar-collapsed .sidebar-user img {
                width: 38px;
                height: 38px;
            }
            body.sidebar-collapsed .sidebar-link {
                justify-content: center;
                padding: 10px 12px;
                gap: 0;
            }
            body.sidebar-collapsed .sidebar-link i {
                font-size: 1.05rem;
            }
            body.sidebar-collapsed .sidebar-brand .brand-icon {
                width: 42px;
                height: 42px;
            }
        }

        /* ══════════════════════════════════════════
           RESPONSIVE
        ══════════════════════════════════════════ */
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-260px); }
            #topbar  { left: 0; }
            #main    { margin-left: 0; }
            body.sidebar-open #sidebar { transform: translateX(0); }
        }

        /* ══════════════════════════════════════════
           FOOTER
        ══════════════════════════════════════════ */
        /* ══════════════════════════════════════════
           FOOTER (100% TRANSPARENTE, COMPACTO Y ELEGANTE)
        ══════════════════════════════════════════ */
        footer.page-footer {
            text-align: center;
            font-size: .75rem;
            color: #64748b;
            padding: 12px 20px 8px !important;
            border-top: 1px solid rgba(226,232,240,.45) !important;
            margin-top: 20px !important;
            background: transparent !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            box-shadow: none !important;
            position: relative;
        }
        footer.page-footer::before {
            display: none !important;
        }
        footer.page-footer .footer-inner {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            max-width: 1400px;
            margin: 0 auto;
        }
        footer.page-footer .footer-brand {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-weight: 700;
            color: #1e293b;
            font-size: .84rem;
            padding: 0;
            border-radius: 0;
            background: transparent !important;
            border: none !important;
        }
        footer.page-footer .footer-brand .store-icon {
            width: 24px; height: 24px;
            border-radius: 7px;
            background: linear-gradient(145deg, #4f46e5 0%, #7c3aed 100%);
            color: #fff;
            display: inline-flex; justify-content: center; align-items: center;
            font-size: .72rem;
            box-shadow: 0 3px 10px rgba(79,70,229,.25);
        }
        footer.page-footer .footer-brand .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #10b981;
            display: inline-block;
        }
        footer.page-footer .footer-meta {
            display: inline-flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px 12px;
            font-weight: 500;
            color: #64748b;
            font-size: .74rem;
        }
        footer.page-footer .footer-meta strong {
            color: #1e293b;
            font-weight: 700;
        }
        footer.page-footer .footer-copy {
            font-size: .72rem;
            color: #94a3b8;
            margin-top: 0;
        }
        body.dark-mode footer.page-footer, html.dark-mode footer.page-footer {
            border-top-color: rgba(255,255,255,.05) !important;
            color: #94a3b8 !important;
            background: transparent !important;
        }
        body.dark-mode footer.page-footer .footer-brand, html.dark-mode footer.page-footer .footer-brand {
            color: #f1f5f9 !important;
            background: transparent !important;
            border: none !important;
        }
        body.dark-mode footer.page-footer .footer-meta, html.dark-mode footer.page-footer .footer-meta {
            color: #94a3b8 !important;
        }
        body.dark-mode footer.page-footer .footer-meta strong, html.dark-mode footer.page-footer .footer-meta strong {
            color: #e2e8f0 !important;
        }
        body.dark-mode footer.page-footer .footer-copy, html.dark-mode footer.page-footer .footer-copy {
            color: #64748b !important;
        }

        /* ══════════════════════════════════════════
           PAGE TRANSITIONS — adaptativo y sin parpadeo
        ══════════════════════════════════════════ */
        #pageTransitionOverlay {
            position: fixed; inset: 0;
            background: var(--pt-bg, linear-gradient(135deg, #f8fafc, #eef2ff));
            z-index: 99999;
            display: flex; align-items: center; justify-content: center;
            opacity: 1;
            pointer-events: none;
            transition: opacity .35s cubic-bezier(.2,.9,.3,1), visibility .35s ease;
        }
        html.dark-mode #pageTransitionOverlay,
        body.dark-mode #pageTransitionOverlay {
            background: linear-gradient(135deg, #030712, #0b1530) !important;
        }
        #pageTransitionOverlay .pt-loader-wrap {
            display: flex; flex-direction: column; align-items: center; gap: 16px;
        }
        #pageTransitionOverlay .pt-logo {
            width: 60px; height: 60px;
            border-radius: 18px;
            background: linear-gradient(145deg, #4f46e5 0%, #6d28d9 55%, #7c3aed 100%);
            display: flex; justify-content: center; align-items: center;
            font-size: 24px; color: #fff;
            box-shadow: 0 14px 34px rgba(79,70,229,.35), inset 0 1px 0 rgba(255,255,255,.25);
            animation: ptLogoPulse 2s ease-in-out infinite;
        }
        @keyframes ptLogoPulse {
            0%,100% { transform: scale(1); box-shadow: 0 14px 34px rgba(79,70,229,.35); }
            50%     { transform: scale(1.05); box-shadow: 0 20px 45px rgba(79,70,229,.45); }
        }
        #pageTransitionOverlay.hide {
            opacity: 0;
            visibility: hidden;
        }
        #pageTransitionOverlay .pt-spinner {
            width: 32px; height: 32px;
            border: 3px solid rgba(79,70,229,.15);
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: ptSpin .85s linear infinite;
        }
        #pageTransitionOverlay .pt-text {
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #4f46e5;
        }
        @keyframes ptSpin { to { transform: rotate(360deg); } }
        body.dark-mode #pageTransitionOverlay .pt-spinner,
        html.dark-mode #pageTransitionOverlay .pt-spinner {
            border-color: rgba(129,140,248,.2);
            border-top-color: #818cf8;
        }
        body.dark-mode #pageTransitionOverlay .pt-text,
        html.dark-mode #pageTransitionOverlay .pt-text {
            color: #a5b4fc;
        }

        /* Animación de fade + slide suave para el contenido principal */
        #main {
            opacity: 0;
            transform: translateY(8px);
            transition:
                opacity .5s cubic-bezier(.2,.9,.3,1),
                transform .5s cubic-bezier(.2,.9,.3,1),
                margin-left .28s ease;
        }
        #main.is-ready {
            opacity: 1;
            transform: translateY(0);
        }

        /* Las cards se revelan con stagger suave */
        .card, .glass-card, .kpi-card, .welcome-hero, .page-panel {
            animation: revealCard .55s cubic-bezier(.2,.9,.3,1) both;
        }
        .card:nth-of-type(2), .glass-card:nth-of-type(2), .kpi-card:nth-of-type(2) { animation-delay: .05s; }
        .card:nth-of-type(3), .glass-card:nth-of-type(3), .kpi-card:nth-of-type(3) { animation-delay: .10s; }
        .card:nth-of-type(4), .glass-card:nth-of-type(4), .kpi-card:nth-of-type(4) { animation-delay: .15s; }
        .card:nth-of-type(5), .glass-card:nth-of-type(5), .kpi-card:nth-of-type(5) { animation-delay: .20s; }
        .card:nth-of-type(6), .glass-card:nth-of-type(6), .kpi-card:nth-of-type(6) { animation-delay: .25s; }
        .kpi-card:nth-of-type(7) { animation-delay: .30s; }
        @keyframes revealCard {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Aplicar transiciones más suaves a links activos y botones */
        a, button, .sidebar-link, .btn, .form-control, .form-select,
        .card, .kpi-card, .toast, .alert, .dropdown-item, .modal-content {
            transition-duration: .22s;
            transition-timing-function: cubic-bezier(.2,.9,.3,1);
        }

        /* Overlay móvil */
        #sidebarOverlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.4);
            z-index: 999;
        }
            .sidebar-footer { pointer-events: auto; }
        @media (max-width: 768px) {
            body.sidebar-open #sidebarOverlay { display: block; }
        }
    </style>

    @stack('styles')
</head>
<body class="no-trans">
<script>
    (function () {
        try {
            if (localStorage.getItem('darkMode') === '1') {
                document.body.classList.add('dark-mode');
                document.documentElement.classList.add('dark-mode');
            }
        } catch (e) {}
    })();
</script>

{{-- Page Transition Overlay — evita parpadeos al navegar entre secciones --}}
<div id="pageTransitionOverlay" aria-hidden="true">
    <div class="pt-loader-wrap">
        <div class="pt-logo">
            <i class="fas fa-store"></i>
        </div>
        <div class="pt-spinner" role="status"></div>
        <div class="pt-text">Cargando...</div>
    </div>
</div>

{{-- Overlay móvil --}}
<div id="sidebarOverlay" onclick="toggleSidebar()"></div>

{{-- ══════════ SIDEBAR ══════════ --}}
<nav id="sidebar">

    {{-- Brand --}}
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <div class="brand-icon">
            <i class="fas fa-store"></i>
        </div>
        <div>
            <div class="brand-text">Supermarket</div>
            <div class="brand-sub">Gestión empresarial</div>
        </div>
    </a>

    {{-- User panel --}}
    <div class="sidebar-user">
        @if(auth()->check())
            @php
                $foto = auth()->user()->foto ?? null;
                $fotoExists = false;
                try {
                    $fotoExists = $foto && \Illuminate\Support\Facades\Storage::disk('public')->exists($foto);
                } catch (\Exception $e) {
                    $fotoExists = false;
                }
            @endphp
            @if($fotoExists)
                <img src="{{ asset('storage/' . $foto) }}" alt="Foto">
            @else
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=4f46e5&color=fff&bold=true" alt="Avatar">
            @endif
        @endif
        <div>
            @if(auth()->check())
    <div class="user-name">{{ auth()->user()->name }}</div>
@endif
            @if(auth()->check() && auth()->user()->rol === 'administrador')
                <div class="role-badge">👑 Administrador</div>
            @else
                <div class="role-badge invitado">👤 Empleado</div>
            @endif
        </div>
    </div>

    {{-- Navigation --}}
    <div class="sidebar-nav">

        <div class="nav-section-title">Principal</div>

        <a href="{{ route('dashboard') }}"
           class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           {{ request()->routeIs('dashboard') ? 'aria-current="page"' : '' }}
           data-bs-toggle="tooltip" data-bs-placement="right" data-bs-container="body" title="Inicio">
            <i class="fas fa-house"></i>
            <span class="link-label">Inicio</span>
        </a>

        <div class="nav-section-title">Catálogo</div>

        <a href="{{ route('categorias.index') }}"
           class="sidebar-link {{ request()->routeIs('categorias.*') ? 'active' : '' }}"
           {{ request()->routeIs('categorias.*') ? 'aria-current="page"' : '' }}
           data-bs-toggle="tooltip" data-bs-placement="right" data-bs-container="body" title="Categorías">
            <i class="fas fa-layer-group"></i>
            <span class="link-label">Categorías</span>
        </a>

        <a href="{{ route('productos.index') }}"
           class="sidebar-link {{ request()->routeIs('productos.*') && !request()->routeIs('productos.papelera') ? 'active' : '' }}"
           {{ request()->routeIs('productos.*') && !request()->routeIs('productos.papelera') ? 'aria-current="page"' : '' }}
           data-bs-toggle="tooltip" data-bs-placement="right" data-bs-container="body" title="Productos">
            <i class="fas fa-box"></i>
            <span class="link-label">Productos</span>
        </a>

        <div class="nav-section-title">Ventas</div>

        <a href="{{ route('clientes.index') }}"
           class="sidebar-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}"
           {{ request()->routeIs('clientes.*') ? 'aria-current="page"' : '' }}
           data-bs-toggle="tooltip" data-bs-placement="right" data-bs-container="body" title="Clientes">
            <i class="fas fa-users"></i>
            <span class="link-label">Clientes</span>
        </a>

        <a href="{{ route('pedidos.index') }}"
           class="sidebar-link {{ request()->routeIs('pedidos.*') ? 'active' : '' }}"
           {{ request()->routeIs('pedidos.*') ? 'aria-current="page"' : '' }}
           data-bs-toggle="tooltip" data-bs-placement="right" data-bs-container="body" title="Pedidos">
            <i class="fas fa-cart-shopping"></i>
            <span class="link-label">Pedidos</span>
        </a>

        <a href="{{ route('reportes.ventas') }}"
           class="sidebar-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}"
           {{ request()->routeIs('reportes.*') ? 'aria-current="page"' : '' }}
           data-bs-toggle="tooltip" data-bs-placement="right" data-bs-container="body" title="Reportes">
            <i class="fas fa-chart-line"></i>
            <span class="link-label">Reportes</span>
        </a>

       @if(auth()->check() && strtolower(auth()->user()->rol) === 'administrador')

            <div class="nav-section-title">Administración</div>

            <a href="{{ route('usuarios.index') }}"
               class="sidebar-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}"
               {{ request()->routeIs('usuarios.*') ? 'aria-current="page"' : '' }}
               data-bs-toggle="tooltip" data-bs-placement="right" data-bs-container="body" title="Usuarios">
                <i class="fas fa-user-shield"></i>
                <span class="link-label">Usuarios</span>
            </a>

            <a href="{{ route('productos.papelera') }}"
               class="sidebar-link {{ request()->routeIs('productos.papelera') ? 'active' : '' }}"
               {{ request()->routeIs('productos.papelera') ? 'aria-current="page"' : '' }}
               data-bs-toggle="tooltip" data-bs-placement="right" data-bs-container="body" title="Papelera">
                <i class="fas fa-trash-can"></i>
                <span class="link-label">Papelera</span>
            </a>

        @endif

    </div>

</nav>

{{-- ══════════ TOPBAR ══════════ --}}
<header id="topbar">

    <button id="toggleSidebar" onclick="toggleSidebar()" title="Menú">
        <i class="fas fa-bars"></i>
    </button>

    <div class="topbar-title">
        @yield('page-title', 'Dashboard')
    </div>

    <div class="topbar-actions ms-auto d-flex align-items-center gap-2">
        <button id="toggleDarkMode" type="button" class="btn btn-sm btn-outline-secondary" title="Alternar modo oscuro">
            <i class="fas fa-moon"></i>
        </button>
    </div>

    <div class="topbar-user">
        <div class="user-info d-none d-md-block">
           @if(auth()->check())
               <strong>{{ auth()->user()->name }}</strong>
               <span>{{ strtolower(auth()->user()->rol) === 'administrador' ? '👑 Administrador' : '👤 Invitado' }}</span>
           @endif
        </div>
           @if(auth()->check())
                @php
                    $topFoto = auth()->user()->foto ?? null;
                    $topFotoExists = false;
                    try {
                        $topFotoExists = $topFoto && \Illuminate\Support\Facades\Storage::disk('public')->exists($topFoto);
                    } catch (\Exception $e) {
                        $topFotoExists = false;
                    }
                @endphp
                @if($topFotoExists)
                    <img src="{{ asset('storage/' . $topFoto) }}" alt="foto">
                @else
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=4f46e5&color=fff&bold=true" alt="Avatar">
                @endif
            @endif
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="btn-logout">
                <i class="fas fa-right-from-bracket"></i>
                <span class="d-none d-md-inline">Salir</span>
            </button>
        </form>
    </div>

</header>

{{-- ══════════ CONTENT ══════════ --}}
<main id="main">

    {{-- Breadcrumb --}}
    @hasSection('breadcrumb')
    <div class="page-header">
        <h1>@yield('page-title', 'Dashboard')</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                @yield('breadcrumb')
            </ol>
        </nav>
    </div>
    @endif

    {{-- Toasts --}}
    <div class="toast-container position-fixed top-0 end-0 p-3">
        @if(session('success'))
            <div class="toast toast-success align-items-center text-white border-0 show" role="alert" aria-live="polite" aria-atomic="true" data-bs-delay="5000">
                <div class="toast-header text-success">
                    <i class="fas fa-circle-check me-2"></i>
                    <strong class="me-auto">Operación exitosa</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
                <div class="toast-body">
                    {{ session('success') }}
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="toast toast-danger align-items-center text-white border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="6000">
                <div class="toast-header text-danger">
                    <i class="fas fa-circle-exclamation me-2"></i>
                    <strong class="me-auto">Atención</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
                <div class="toast-body">
                    {{ session('error') }}
                </div>
            </div>
        @endif
    </div>

    @yield('content')

    <footer class="page-footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <span class="store-icon"><i class="fas fa-store"></i></span>
                <strong>Supermarket</strong>
                <span class="dot"></span>
                <span class="text-muted" style="font-size:.72rem;">v1.0 · Sistema De Ventas</span>
            </div>
            <div class="footer-meta">
                <span>Inventario · Ventas · Pedidos · Reportes</span>
            </div>
            <div class="footer-copy">
                © {{ date('Y') }} Supermarket
            </div>
        </div>
    </footer>

</main>

{{-- Scripts CRÍTICOS (sin async/defer) — disponibles inmediatamente --}}
<script>
    window.__barcodeReady = !!(window.JsBarcode && typeof window.JsBarcode === 'function');
    window.__qrReady      = !!(window.QRCode && typeof window.QRCode.toCanvas === 'function' && typeof window.QRCode.toDataURL === 'function');
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    function updateSidebarUI() {
        const collapsed = document.body.classList.contains('sidebar-collapsed');
        const toggleBtn = document.getElementById('toggleSidebar');

        if (toggleBtn) {
            toggleBtn.innerHTML = collapsed
                ? '<i class="fas fa-chevron-right"></i>'
                : '<i class="fas fa-bars"></i>';
            toggleBtn.setAttribute('title', collapsed ? 'Expandir menú' : 'Contraer menú');
        }

        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
            // ensure tooltip is appended to body so it displays over collapsed sidebar
            bootstrap.Tooltip.getOrCreateInstance(element, { container: 'body' });
        });
    }

    function toggleSidebar() {
        if (window.innerWidth <= 768) {
            document.body.classList.toggle('sidebar-open');
        } else {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebar', document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
            updateSidebarUI();
        }
    }

    if (localStorage.getItem('sidebar') === '1' && window.innerWidth > 768) {
        document.body.classList.add('sidebar-collapsed');
    }

    document.addEventListener('DOMContentLoaded', updateSidebarUI);
    // Dark mode: toggle, persist and apply — SIN PARPADEO
    function applyDarkMode(enabled) {
        // Bloquear transiciones durante el cambio para evitar flashes
        document.body.classList.add('no-trans');
        document.documentElement.classList.toggle('dark-mode', !!enabled);
        document.body.classList.toggle('dark-mode', !!enabled);
        const btn = document.getElementById('toggleDarkMode');
        if (btn) {
            btn.setAttribute('aria-pressed', enabled ? 'true' : 'false');
            btn.innerHTML = enabled ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
            btn.title = enabled ? 'Desactivar modo oscuro' : 'Activar modo oscuro';
        }
        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { isDark: !!enabled } }));
        // Forzar reflow y quitar bloqueo de transiciones
        void document.body.offsetWidth;
        setTimeout(function () { document.body.classList.remove('no-trans'); }, 30);
    }

    function toggleDarkMode() {
        const enabled = !document.body.classList.contains('dark-mode');
        try { localStorage.setItem('darkMode', enabled ? '1' : '0'); } catch(e) {}
        applyDarkMode(enabled);
    }

    // Sincronizar estado del botón de Dark Mode al cargar
    document.addEventListener('DOMContentLoaded', () => {
        const dmBtn = document.getElementById('toggleDarkMode');
        const isDark = localStorage.getItem('darkMode') === '1' || document.documentElement.classList.contains('dark-mode');
        if (isDark) {
            document.documentElement.classList.add('dark-mode');
            document.body.classList.add('dark-mode');
        }
        if (dmBtn) {
            dmBtn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            dmBtn.innerHTML = isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
            dmBtn.title = isDark ? 'Desactivar modo oscuro' : 'Activar modo oscuro';
            dmBtn.addEventListener('click', toggleDarkMode);
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth <= 768) {
            document.body.classList.remove('sidebar-collapsed');
            document.body.classList.remove('sidebar-open');
            updateSidebarUI();
        } else {
            const shouldCollapse = localStorage.getItem('sidebar') === '1';
            document.body.classList.toggle('sidebar-collapsed', shouldCollapse);
            updateSidebarUI();
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.toast').forEach((toastEl) => {
            const toast = bootstrap.Toast.getOrCreateInstance(toastEl);
            toast.show();
        });

        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(a => {
                const bs = bootstrap.Alert.getOrCreateInstance(a);
                bs.close();
            });
        }, 4500);

        /* ════════════════════════════════════
           PAGE TRANSITIONS — fade sin parpadeo
           + quitar clase no-trans para habilitar animaciones
        ════════════════════════════════════ */
        const mainEl = document.getElementById('main');
        const overlayEl = document.getElementById('pageTransitionOverlay');

        function removeNoTrans() {
            // Habilitar transiciones después del primer paint
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    document.body.classList.remove('no-trans');
                });
            });
        }

        function hideOverlay() {
            if (mainEl) mainEl.classList.add('is-ready');
            if (overlayEl) {
                overlayEl.classList.add('hide');
                setTimeout(() => {
                    if (overlayEl) {
                        overlayEl.style.visibility = 'hidden';
                        overlayEl.style.opacity = '0';
                    }
                }, 380);
            }
            removeNoTrans();
        }

        function isDownloadOrExportLink(el, href) {
            if (!href) return false;
            if (el.hasAttribute('download')) return true;
            if (el.classList.contains('no-overlay') || el.classList.contains('no-transition')) return true;
            if (el.dataset.noOverlay !== undefined || el.dataset.noTransition !== undefined) return true;
            const h = href.toLowerCase();
            return h.includes('export') ||
                   h.includes('download') ||
                   h.includes('-pdf') ||
                   h.includes('-excel') ||
                   h.includes('.pdf') ||
                   h.includes('.xlsx') ||
                   h.includes('.xls') ||
                   h.includes('.csv') ||
                   h.includes('/pdf') ||
                   h.includes('/excel');
        }

        let navFallbackTimer = null;
        function showOverlayAndNavigate(href) {
            document.body.classList.add('no-trans');
            const isDark = document.body.classList.contains('dark-mode') || document.documentElement.classList.contains('dark-mode');
            if (overlayEl) {
                overlayEl.style.background = isDark
                    ? 'linear-gradient(135deg, #030712, #0b1530)'
                    : 'linear-gradient(135deg, #f8fafc, #eef2ff)';
                overlayEl.classList.remove('hide');
                overlayEl.style.visibility = 'visible';
                overlayEl.style.opacity = '1';
            }
            if (mainEl) mainEl.classList.remove('is-ready');

            // Fallback: si tras iniciar la navegación la página no se descarga (ej. descarga de archivo o cancelación)
            clearTimeout(navFallbackTimer);
            navFallbackTimer = setTimeout(() => {
                hideOverlay();
            }, 2500);

            setTimeout(() => { window.location.href = href; }, 140);
        }

        // Activar el overlay al hacer clic en enlaces del sidebar / navegación interna
        document.querySelectorAll('a.sidebar-link, a.page-link, .breadcrumb a, nav a').forEach((link) => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                if (!href || href === '#' || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:')) return;
                if (href.startsWith('http') && !href.startsWith(window.location.origin)) return;
                const target = link.getAttribute('target');
                if (target === '_blank') return;
                if (isDownloadOrExportLink(link, href)) return;
                if (link.closest('[data-bs-toggle]') || link.closest('[data-route]')) return;
                e.preventDefault();
                showOverlayAndNavigate(href);
            });
        });

        // Botones <a> btn con href interno (excluyendo descargas y exportaciones)
        document.querySelectorAll('a.btn:not([target="_blank"]):not([download])').forEach((btn) => {
            const href = btn.getAttribute('href');
            if (!href || href === '#' || (href.startsWith('http') && !href.startsWith(window.location.origin))) return;
            if (isDownloadOrExportLink(btn, href)) return;
            btn.addEventListener('click', (e) => {
                if (btn.closest('[data-bs-toggle]') || btn.closest('.modal-footer')) return;
                if (isDownloadOrExportLink(btn, href)) return;
                e.preventDefault();
                showOverlayAndNavigate(href);
            });
        });

        // Ocultar overlay si el usuario vuelve a la página o la ventana recupera foco tras descargar
        window.addEventListener('pageshow', (e) => {
            hideOverlay();
        });
        window.addEventListener('focus', () => {
            if (overlayEl && !overlayEl.classList.contains('hide')) {
                setTimeout(hideOverlay, 400);
            }
        });

        // Cerrar overlay al terminar de cargar
        function finalizarCarga() {
            // Si Chart.js o scripts adicionales están cargando, esperar un poco más
            const extraDelay = (typeof Chart !== 'undefined' || window.__barcodeLibsPromise) ? 100 : 60;
            setTimeout(hideOverlay, extraDelay);
        }
        if (document.readyState === 'complete') {
            finalizarCarga();
        } else {
            window.addEventListener('load', finalizarCarga);
            // Fallback por si el evento load tarda (evita overlay infinito)
            setTimeout(hideOverlay, 900);
        }
        // Remover no-trans incluso si algo falla, para que la UI sea interactiva
        setTimeout(removeNoTrans, 300);
    });


</script>

@stack('scripts')
</body>
</html>