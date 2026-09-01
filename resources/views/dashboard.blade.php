@extends('layouts.app')

@section('title', 'Panel Principal')
@section('page-title', 'Panel Principal')
@section('breadcrumb')
    <li class="breadcrumb-item active">Inicio</li>
@endsection

@push('styles')
<style>
/* ══════════════════════════════════════════
   DASHBOARD PREMIUM UPGRADE
══════════════════════════════════════════ */
.dashboard-glow {
    position: absolute; inset: 0; pointer-events: none; border-radius: inherit; overflow: hidden; opacity: .85;
}
.dashboard-glow::after {
    content: ''; position: absolute; width: 60%; height: 120%;
    background: conic-gradient(from 200deg, rgba(255,255,255,0) 0deg, rgba(255,255,255,.28) 40deg, rgba(255,255,255,0) 80deg, rgba(124,58,237,.22) 170deg, rgba(255,255,255,0) 260deg);
    right: -20%; top: -30%; filter: blur(14px);
    animation: dash-spin 14s linear infinite;
}
@keyframes dash-spin { to { transform: rotate(360deg); } }

/* Welcome hero premium */
.welcome-hero::before {
    content: '';
    position: absolute; inset: 1px; border-radius: inherit;
    background: linear-gradient(135deg, rgba(255,255,255,.65), rgba(255,255,255,.15));
    pointer-events: none; z-index: 0;
}
html.dark-mode .welcome-hero::before {
    background: linear-gradient(135deg, rgba(255,255,255,.05), rgba(255,255,255,.01));
}
.welcome-hero::after {
    content: '';
    position: absolute; left: 24px; right: 24px; bottom: 14px; height: 3px;
    border-radius: 999px;
    background: linear-gradient(90deg, rgba(79,70,229,.0), rgba(79,70,229,.55), rgba(16,185,129,.55), rgba(255,255,255,.0));
    animation: dash-border-flow 7s ease-in-out infinite;
    z-index: 0;
}
@keyframes dash-border-flow { 0%,100% { opacity: .4; transform: scaleX(.92);} 50% { opacity: .9; transform: scaleX(1);} }

.welcome-premium-badge {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .32rem .65rem; border-radius: 999px;
    background: linear-gradient(135deg, rgba(79,70,229,.12), rgba(16,185,129,.12));
    border: 1px solid rgba(79,70,229,.18);
    color: #4338ca; font-weight: 700; font-size: .74rem;
    box-shadow: 0 4px 12px rgba(79,70,229,.1);
}
html.dark-mode .welcome-premium-badge {
    background: linear-gradient(135deg, rgba(129,140,248,.18), rgba(16,185,129,.18));
    color: #a5b4fc; border-color: rgba(129,140,248,.25);
}
.welcome-premium-badge .dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.25);
    animation: dash-pulse 1.8s ease-out infinite;
}
@keyframes dash-pulse { 0% { box-shadow: 0 0 0 0 rgba(16,185,129,.45);} 100% { box-shadow: 0 0 0 14px rgba(16,185,129,0);} }

.welcome-actions .btn {
    position: relative; overflow: hidden;
    backdrop-filter: blur(10px);
    transition: transform .22s cubic-bezier(.2,.9,.3,1), box-shadow .22s;
}
.welcome-actions .btn::after {
    content:''; position:absolute; top:0; left:-120%; width: 60%; height: 100%;
    background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,.38), rgba(255,255,255,0));
    transform: skewX(-20deg);
    animation: dash-sheen 3.4s ease-in-out infinite;
}
@keyframes dash-sheen { 0%,40%,100% { left:-120%;} 55%,85% { left: 140%;} }
.welcome-actions .btn:hover { transform: translateY(-2px); }

/* KPI cards con shimmer + badge tendencia */
.kpi-card {
    position: relative; overflow: hidden;
    transition: transform .28s cubic-bezier(.2,.9,.3,1), box-shadow .28s;
}
.kpi-card::before {
    content: '';
    position: absolute; inset: -1px; border-radius: inherit;
    background: linear-gradient(135deg, rgba(255,255,255,.55), rgba(255,255,255,0) 40%, rgba(255,255,255,.25) 70%, rgba(255,255,255,0));
    opacity: .7; z-index: 0; pointer-events: none;
    animation: kpi-shimmer 5.5s ease-in-out infinite;
    background-size: 250% 250%;
}
html.dark-mode .kpi-card::before {
    background: linear-gradient(135deg, rgba(255,255,255,.07), rgba(255,255,255,0) 40%, rgba(255,255,255,.05) 70%, rgba(255,255,255,0));
    background-size: 250% 250%;
}
@keyframes kpi-shimmer {
    0%,100% { background-position: 0% 50%;}
    50% { background-position: 100% 50%;}
}
.kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 24px 48px rgba(15,23,42,.18);
}
html.dark-mode .kpi-card:hover {
    box-shadow: 0 24px 48px rgba(0,0,0,.45);
}
.kpi-card::after {
    content:''; position:absolute; right:-35px; top:-35px; width:140px; height:140px;
    border-radius: 50%; filter: blur(28px); opacity: .35; pointer-events: none;
}
.kpi-indigo::after   { background: #6366f1; }
.kpi-emerald::after  { background: #10b981; }
.kpi-cyan::after     { background: #06b6d4; }
.kpi-violet::after   { background: #8b5cf6; }
.kpi-rose::after     { background: #f43f5e; }
.kpi-amber::after    { background: #f59e0b; }
.kpi-gradient-success::after { background: linear-gradient(135deg,#10b981,#06b6d4); }

.kpi-trend-badge {
    position: absolute; top: 10px; right: 10px; z-index: 2;
    padding: .18rem .48rem; border-radius: 999px;
    font-size: .68rem; font-weight: 800; letter-spacing: .01em;
    display: inline-flex; align-items: center; gap: .25rem;
    box-shadow: 0 4px 10px rgba(15,23,42,.15);
}
.kpi-trend-badge.up {
    background: rgba(16,185,129,.14); color: #047857;
    border: 1px solid rgba(16,185,129,.22);
}
html.dark-mode .kpi-trend-badge.up { color: #6ee7b7; }
.kpi-trend-badge.flat {
    background: rgba(100,116,139,.14); color: #334155;
    border: 1px solid rgba(100,116,139,.22);
}
html.dark-mode .kpi-trend-badge.flat { color: #cbd5e1; }
.kpi-trend-badge.warn {
    background: rgba(245,158,11,.14); color: #92400e;
    border: 1px solid rgba(245,158,11,.22);
}
html.dark-mode .kpi-trend-badge.warn { color: #fcd34d; }
.kpi-trend-badge.down {
    background: rgba(239,68,68,.12); color: #b91c1c;
    border: 1px solid rgba(239,68,68,.2);
}
html.dark-mode .kpi-trend-badge.down { color: #fca5a5; }

.kpi-num-big {
    letter-spacing: -0.03em;
    text-shadow: 0 2px 16px rgba(255,255,255,.18);
}

.kpi-num {
    font-variant-numeric: tabular-nums;
    text-shadow: 0 3px 14px rgba(2,6,23,.28);
}
.kpi-lbl {
    font-weight: 700;
    color: rgba(255,255,255,.96);
    text-shadow: 0 2px 8px rgba(2,6,23,.22);
}

/* Top lists: barra de progreso relativa */
.list-row {
    position: relative;
}
.list-amount {
    font-weight: 800 !important;
    letter-spacing: -0.015em;
}
.list-progress {
    position: absolute; left: 62px; right: 120px; bottom: 6px; height: 3px;
    border-radius: 999px;
    background: rgba(148,163,184,.18);
    overflow: hidden;
}
.list-progress > span {
    display: block; height: 100%;
    background: linear-gradient(90deg, #6366f1, #8b5cf6, #10b981);
    border-radius: inherit;
    box-shadow: 0 0 8px rgba(99,102,241,.35);
    transform-origin: left;
    animation: list-bar-pop .9s cubic-bezier(.2,.9,.3,1) both;
}
@keyframes list-bar-pop { from { transform: scaleX(0);} to { transform: scaleX(1);} }

@media (max-width: 576px) {
    .welcome-hero { padding: 1.25rem !important; }
    .kpi-num { font-size: 1.55rem !important; }
    .kpi-num.kpi-num-big { font-size: 1.35rem !important; }
}

/* Chart donut shadow */
#estadoChart { filter: drop-shadow(0 20px 30px rgba(79,70,229,.18)); }

/* Reveal staggered premium */
.dashboard-reveal {
    opacity: 0;
    transform: translateY(18px);
    animation: dash-reveal .7s cubic-bezier(.2,.9,.3,1) forwards;
}
@keyframes dash-reveal {
    to { opacity: 1; transform: translateY(0); }
}
.dashboard-reveal.delay-1 { animation-delay: .05s; }
.dashboard-reveal.delay-2 { animation-delay: .1s; }
.dashboard-reveal.delay-3 { animation-delay: .15s; }
.dashboard-reveal.delay-4 { animation-delay: .2s; }
.dashboard-reveal.delay-5 { animation-delay: .25s; }
.dashboard-reveal.delay-6 { animation-delay: .3s; }
.dashboard-reveal.delay-7 { animation-delay: .35s; }

/* ── Barra Ejecutiva ── */
.exec-bar {
    display: flex; align-items: center; flex-wrap: wrap; gap: 10px 18px;
    padding: 9px 18px 9px 16px;
    border-radius: 14px;
    background: rgba(255,255,255,.55);
    border: 1px solid rgba(226,232,240,.6);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 2px 12px rgba(15,23,42,.04);
    margin-bottom: 18px;
    font-size: .78rem;
    font-weight: 600;
}
html.dark-mode .exec-bar, body.dark-mode .exec-bar {
    background: rgba(8,20,34,.65) !important;
    border-color: rgba(255,255,255,.06) !important;
}
.exec-bar .exec-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 10px; border-radius: 999px;
    font-size: .74rem; font-weight: 700;
}
.exec-bar .exec-pill.online {
    background: rgba(16,185,129,.12); color: #047857; border: 1px solid rgba(16,185,129,.22);
}
html.dark-mode .exec-pill.online { color: #6ee7b7 !important; }
.exec-bar .exec-pill.currency {
    background: rgba(79,70,229,.1); color: #4338ca; border: 1px solid rgba(79,70,229,.18);
}
html.dark-mode .exec-pill.currency { color: #a5b4fc !important; }
.exec-bar .exec-pill.session {
    background: rgba(14,165,233,.1); color: #0369a1; border: 1px solid rgba(14,165,233,.18);
}
html.dark-mode .exec-pill.session { color: #7dd3fc !important; }
.exec-bar .exec-clock {
    margin-left: auto; font-variant-numeric: tabular-nums;
    font-size: .78rem; color: #475569; font-weight: 700; display: flex; align-items: center; gap: 5px;
}
html.dark-mode .exec-bar .exec-clock { color: #94a3b8 !important; }
.exec-bar .exec-sep { width: 1px; height: 18px; background: rgba(148,163,184,.3); }
</style>
@endpush

@section('content')

{{-- ═══════════════════════════════════════════════════════════
   BARRA EJECUTIVA DE ESTADO
   ═══════════════════════════════════════════════════════════ --}}
<script>
    (function(){
        var el = document.getElementById('execReloj');
        if (!el) return;
        function tick() {
            var n = new Date();
            el.textContent = String(n.getHours()).padStart(2,'0') + ':' +
                             String(n.getMinutes()).padStart(2,'0') + ':' +
                             String(n.getSeconds()).padStart(2,'0');
        }
        tick(); setInterval(tick, 1000);
    })();
</script>

{{-- ═══════════════════════════════════════════════════════════
   BIENVENIDA (PREMIUM)
   ═══════════════════════════════════════════════════════════ --}}
<div class="welcome-hero mb-4">
    <div class="dashboard-glow"></div>
    <div class="welcome-blob welcome-blob-1"></div>
    <div class="welcome-blob welcome-blob-2"></div>

    <div class="row g-3 align-items-center">
        <div class="col-lg-8">
            @php
                $horaActual = \Carbon\Carbon::now()->hour;
                $minutoActual = \Carbon\Carbon::now()->minute;
                $franja = 'noche';
                if ($horaActual >= 5 && $horaActual < 12) {
                    $franja = 'manana';
                } elseif ($horaActual >= 12 && $horaActual < 19) {
                    $franja = 'tarde';
                }
                $saludosPorFranja = [
                    'manana' => [
                        'Buenos días',
                        '¡Buen día!',
                        'Muy buenos días',
                        '¡Arriba que es temprano!',
                        'Buenos días, campeón',
                    ],
                    'tarde' => [
                        'Buenas tardes',
                        '¡Muy buenas tardes',
                        '¿Cómo va tu tarde?',
                        'Buenas tardes, vamos con todo',
                    ],
                    'noche' => [
                        'Buenas noches',
                        '¡Muy buenas noches',
                        '¿Cómo va tu noche?',
                        'Buenas noches, gran trabajo hoy',
                    ],
                ];
                $listaSaludos = $saludosPorFranja[$franja];
                $seed = (int)($horaActual * 60 + $minutoActual) + (int)date('d');
                $saludo = $listaSaludos[$seed % count($listaSaludos)];

                $mensajesMotivacionales = [
                    'Cada venta cuenta, cada cliente importa. Hoy es una nueva oportunidad para crecer.',
                    'La constancia es la clave del éxito en los negocios. ¡Sigue adelante!',
                    'Tu dedicación hoy construye el Supermarket del mañana. ¡Excelente jornada.',
                    'Grandes cosas nunca vienen de zonas de confort. ¡A por todas!',
                    'El servicio al cliente no es un departamento, es una actitud. ¡Sigue brillando!',
                    'Los pequeños detalles hacen grandes diferencias. Tu trabajo importa.',
                    'Cada producto en el estante es una historia de éxito por escribir.',
                    'El éxito no es casualidad, es trabajo bien hecho cada día.',
                    'Hoy es el día perfecto para superar tus propios récords.',
                    'En Supermarket crecemos juntos. ¡Gracias por ser parte del equipo!',
                    'Nunca es tarde para mejorar un proceso. Tu creatividad es invaluable.',
                    'La paciencia y la persistencia son las mejores herramientas del emprendedor.',
                    'Un cliente satisfecho es la mejor publicidad. ¡Sé extraordinario!',
                    'Cada detalle importa. Cada sonrisa a un cliente suma.',
                    'El trabajo en equipo hace que los sueños se cumplan. ¡Vamos por más!',
                ];
                $mensajeIndex = $seed % count($mensajesMotivacionales);
                $mensajeInicial = $mensajesMotivacionales[$mensajeIndex];
                $mensajesJson = json_encode($mensajesMotivacionales, JSON_UNESCAPED_UNICODE);

                $mesActual = \Carbon\Carbon::now()->locale('es')->isoFormat('MMMM YYYY');
            @endphp
            <div class="d-flex align-items-center gap-2 mb-2" style="position:relative;z-index:2;">
                <span class="welcome-premium-badge">
                    <span class="dot"></span>
                   
                </span>
                <span class="welcome-premium-badge" style="background:linear-gradient(135deg, rgba(245,158,11,.12), rgba(239,68,68,.12)); border-color: rgba(245,158,11,.18); color: #92400e;">
                    <i class="fas fa-calendar-day me-1"></i> {{ $mesActual }}
                </span>
            </div>
            <div class="profile-summary mb-2">
                <div class="avatar-xl">
                    @php
                        $iniciales = strtoupper(substr(auth()->user()->name ?? 'U', 0, 1));
                    @endphp
                    @if(auth()->user()->foto)
                        <img src="{{ asset('storage/'.auth()->user()->foto) }}" alt="Usuario">
                    @else
                        <span>{{ $iniciales }}</span>
                    @endif
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="welcome-hello">
                        <span id="saludoDinamico">{{ $saludo }}</span>
                        <span style="display:inline-block;margin-left:6px;">
                            <i id="iconoSaludo" class="fas fa-sun" style="color:#f59e0b;"></i>
                        </span>
                    </div>
                    <h2 class="welcome-name mb-0 text-truncate">
                        {{ auth()->user()->name }}
                    </h2>
                    <div class="welcome-rol">
                        @if(auth()->user()->rol === 'administrador')
                            <i class="fas fa-shield-halved me-1"></i> Administrador del Sistema
                        @else
                            <i class="fas fa-user me-1"></i> Usuario Invitado
                        @endif
                    </div>
                </div>
            </div>
            <p class="welcome-lead mt-3 mb-0">
                <strong id="mensajeMotivacional"
                        data-mensajes="{{ $mensajesJson }}"
                        data-mensaje-inicial="{{ $mensajeInicial }}"
                        style="display:inline;">{{ $mensajeInicial }}</strong>
                <span id="puntoRotatorio">
                    <i class="fas fa-quote-left" style="font-size:.72rem;opacity:.45;margin:0 6px;"></i>
                </span>
                <span class="d-block mt-1" id="fechaHoyTxt">
                    Hoy es {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }} ·
                    <span id="relojDigital" style="font-weight:600;color:#4f46e5;"></span>
                </span>
            </p>
        </div>
        <div class="col-lg-4">
            <div class="welcome-actions">
                @if(auth()->user()->rol === 'administrador')
                    <a href="{{ route('pedidos.create') }}" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-cart-plus me-2"></i> Registrar nuevo pedido
                    </a>
                    <a href="{{ route('reportes.ventas') }}" class="btn btn-success w-100 mb-2">
                        <i class="fas fa-chart-line me-2"></i> Ver reporte de ventas
                    </a>
                    <a href="{{ route('productos.create') }}" class="btn btn-warning w-100">
                        <i class="fas fa-plus me-2"></i> Agregar producto nuevo
                    </a>
                @else
                    <a href="{{ route('productos.index') }}" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-box me-2"></i> Consultar productos
                    </a>
                    <a href="{{ route('pedidos.index') }}" class="btn btn-info w-100">
                        <i class="fas fa-clipboard-list me-2"></i> Ver pedidos registrados
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
   KPI CARDS (PREMIUM CON TREND BADGE + REVEAL)
   ═══════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    <div class="col-6 col-md-4 col-lg-3 col-xl dashboard-reveal delay-1">
        <div class="kpi-card kpi-indigo">
            <span class="kpi-trend-badge up"><i class="fas fa-arrow-trend-up"></i> Activo</span>
            <div class="kpi-icon"><i class="fas fa-layer-group"></i></div>
            <div class="kpi-num">{{ $totalCategorias }}</div>
            <div class="kpi-lbl">Categorías</div>
            <a href="{{ route('categorias.index') }}" class="kpi-foot">
                Ir a Categorías <i class="fas fa-arrow-right fa-xs"></i>
            </a>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-3 col-xl dashboard-reveal delay-2">
        <div class="kpi-card kpi-emerald">
            <span class="kpi-trend-badge up"><i class="fas fa-arrow-trend-up"></i> +{{ $totalProductos > 0 ? round($totalProductos*0.06) : 0 }}</span>
            <div class="kpi-icon"><i class="fas fa-box"></i></div>
            <div class="kpi-num">{{ $totalProductos }}</div>
            <div class="kpi-lbl">Productos activos</div>
            <a href="{{ route('productos.index') }}" class="kpi-foot">
                Ir a Productos <i class="fas fa-arrow-right fa-xs"></i>
            </a>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-3 col-xl dashboard-reveal delay-3">
        <div class="kpi-card kpi-cyan">
            <span class="kpi-trend-badge flat"><i class="fas fa-minus"></i> Base</span>
            <div class="kpi-icon"><i class="fas fa-users"></i></div>
            <div class="kpi-num">{{ $totalClientes }}</div>
            <div class="kpi-lbl">Clientes</div>
            <a href="{{ route('clientes.index') }}" class="kpi-foot">
                Ir a Clientes <i class="fas fa-arrow-right fa-xs"></i>
            </a>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-3 col-xl dashboard-reveal delay-4">
        <div class="kpi-card kpi-violet">
            <span class="kpi-trend-badge up"><i class="fas fa-bag-shopping"></i> {{ $totalPedidos }}</span>
            <div class="kpi-icon"><i class="fas fa-cart-shopping"></i></div>
            <div class="kpi-num">{{ $totalPedidos }}</div>
            <div class="kpi-lbl">Pedidos totales</div>
            <a href="{{ route('pedidos.index') }}" class="kpi-foot">
                Ir a Pedidos <i class="fas fa-arrow-right fa-xs"></i>
            </a>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-4 col-xl dashboard-reveal delay-5">
        <div class="kpi-card kpi-rose">
            <span class="kpi-trend-badge up"><i class="fas fa-circle-check"></i>
                {{ $totalPedidos > 0 ? round(($totalPedidosCompletados / max(1,$totalPedidos))*100) : 0 }}%
            </span>
            <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
            <div class="kpi-num">{{ $totalPedidosCompletados }}</div>
            <div class="kpi-lbl">Pedidos completados</div>
            <div class="kpi-foot kpi-foot-soft">
                Anulados: <strong>{{ $totalPedidosAnulados }}</strong>
            </div>
        </div>
    </div>

    @if(auth()->user()->rol === 'administrador')
        <div class="col-6 col-md-6 col-lg-4 col-xl dashboard-reveal delay-6">
            <div class="kpi-card kpi-gradient-success">
                <span class="kpi-trend-badge up"><i class="fas fa-chart-line"></i> Top</span>
                <div class="kpi-icon"><i class="fas fa-sack-dollar"></i></div>
                <div class="kpi-num kpi-num-big">
                    Bs {{ number_format($totalVentas, 2) }}
                </div>
                <div class="kpi-lbl">Ingresos totales (pedidos)</div>
                <a href="{{ route('reportes.ventas') }}" class="kpi-foot">
                    Reporte completo <i class="fas fa-arrow-right fa-xs"></i>
                </a>
            </div>
        </div>

        <div class="col-6 col-md-6 col-lg-4 col-xl dashboard-reveal delay-7">
            <div class="kpi-card kpi-amber">
                @if($stockBajoCant > 0)
                    <span class="kpi-trend-badge warn"><i class="fas fa-triangle-exclamation"></i> Stock</span>
                @else
                    <span class="kpi-trend-badge up"><i class="fas fa-circle-check"></i> OK</span>
                @endif
                <div class="kpi-icon"><i class="fas fa-warehouse"></i></div>
                <div class="kpi-num kpi-num-big">
                    Bs {{ number_format($inventarioValuado, 2) }}
                </div>
                <div class="kpi-lbl">Valor inventario (stock × precio)</div>
                <div class="kpi-foot kpi-foot-soft">
                    @if($stockBajoCant > 0)
                        <span class="badge-danger-pill"><i class="fas fa-triangle-exclamation me-1"></i> {{ $stockBajoCant }} producto(s) con stock crítico</span>
                    @else
                        <span class="badge-success-pill"><i class="fas fa-circle-check me-1"></i> Todo en niveles correctos</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

</div>

{{-- ═══════════════════════════════════════════════════════════
   GRÁFICOS
   ═══════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-7">
        <div class="card h-100 glass-card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <i class="fas fa-chart-column me-2 text-primary"></i>
                    <strong>Ventas por día (histórico)</strong>
                </div>
                <span class="mini-badge mini-badge-indigo"><i class="fas fa-circle-info me-1"></i> Moneda: Bs</span>
            </div>
            <div class="card-body" style="min-height: 320px;">
                <canvas id="ventasChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card h-100 glass-card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <i class="fas fa-chart-pie me-2 text-warning"></i>
                    <strong>Estado de pedidos</strong>
                </div>
                <span class="mini-badge mini-badge-amber">Total {{ $totalPedidos }}</span>
            </div>
            <div class="card-body d-flex align-items-center" style="min-height: 320px;">
                <canvas id="estadoChart" class="mx-auto" style="max-height:300px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card glass-card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <i class="fas fa-chart-line me-2 text-success"></i>
                    <strong>Tendencia mensual — ventas & cantidad de pedidos</strong>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="mini-badge mini-badge-success"><i class="fas fa-chart-simple me-1"></i> Ventas (Bs)</span>
                    <span class="mini-badge mini-badge-violet"><i class="fas fa-bag-shopping me-1"></i> Cant. pedidos</span>
                </div>
            </div>
            <div class="card-body" style="min-height: 320px;">
                <canvas id="tendenciaChart"></canvas>
            </div>
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════
   TOP PRODUCTOS / TOP CLIENTES (con barra de progreso relativa)
   ═══════════════════════════════════════════════════════════ --}}
@php
    $maxProd = 0; $maxCli = 0;
    foreach ($topProductos as $tp) { $val = (float)($tp->ingresos ?? 0); if ($val > $maxProd) $maxProd = $val; }
    foreach ($topClientes as $tc)  { $val = (float)($tc->total ?? 0);    if ($val > $maxCli) $maxCli = $val; }
@endphp
<div class="row g-3">

    <div class="col-lg-6 dashboard-reveal delay-3">
        <div class="card h-100 glass-card">
            <div class="card-header">
                <i class="fas fa-trophy me-2" style="color:#f59e0b;"></i>
                <strong>Top 5 productos — mayores ingresos</strong>
                @if($maxProd > 0)
                    <span class="mini-badge mini-badge-amber ms-2"><i class="fas fa-fire me-1"></i> Máximo Bs {{ number_format($maxProd, 2) }}</span>
                @endif
            </div>
            <div class="card-body p-0">
                @if($topProductos->count())
                    <div class="list-list">
                        @foreach($topProductos as $idx => $tp)
                            @php
                                $ingresos = (float)($tp->ingresos ?? 0);
                                $pct = $maxProd > 0 ? max(8, min(100, ($ingresos / $maxProd) * 100)) : 10;
                            @endphp
                            <div class="list-row">
                                <div class="list-pos list-pos-{{ $idx }}">{{ $idx + 1 }}</div>
                                <div class="list-data">
                                    <div class="list-title">{{ $tp->nombre }}</div>
                                    <div class="list-meta">
                                        <span><i class="fas fa-cubes me-1"></i> {{ (int)$tp->unidades }} u. vendidas</span>
                                    </div>
                                    <div class="list-progress" aria-hidden="true"><span style="width: {{ $pct }}%; animation-delay: {{ $idx * 0.08 }}s;"></span></div>
                                </div>
                                <div class="list-amount badge-num-success" style="border-radius:10px;padding:.25rem .5rem;font-size:.82rem;">
                                    Bs {{ number_format($ingresos, 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-chart-simple fa-2x mb-2 d-block"></i>
                        Aún no hay datos de ventas.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6 dashboard-reveal delay-4">
        <div class="card h-100 glass-card">
            <div class="card-header">
                <i class="fas fa-crown me-2" style="color:#ef4444;"></i>
                <strong>Top 5 clientes — mayores compras</strong>
                @if($maxCli > 0)
                    <span class="mini-badge mini-badge-violet ms-2"><i class="fas fa-star me-1"></i> Límite Bs {{ number_format($maxCli, 2) }}</span>
                @endif
            </div>
            <div class="card-body p-0">
                @if($topClientes->count())
                    <div class="list-list">
                        @foreach($topClientes as $idx => $tc)
                            @php
                                $total = (float)($tc->total ?? 0);
                                $pct = $maxCli > 0 ? max(8, min(100, ($total / $maxCli) * 100)) : 10;
                            @endphp
                            <div class="list-row">
                                <div class="list-pos list-pos-{{ $idx }}">{{ $idx + 1 }}</div>
                                <div class="list-data">
                                    <div class="list-title">{{ $tc->nombre }}</div>
                                    <div class="list-meta">
                                        <span><i class="fas fa-file-invoice-dollar me-1"></i> {{ (int)$tc->pedidos }} pedido(s)</span>
                                    </div>
                                    <div class="list-progress" aria-hidden="true"><span style="width: {{ $pct }}%; animation-delay: {{ $idx * 0.08 }}s;"></span></div>
                                </div>
                                <div class="list-amount badge-num-primary" style="border-radius:10px;padding:.25rem .5rem;font-size:.82rem;">
                                    Bs {{ number_format($total, 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-user-group fa-2x mb-2 d-block"></i>
                        Aún no hay clientes con compras registradas.
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
$(document).ready(function () {

    /* ════════════════════════════════════════════════
       SALUDO DINÁMICO + RELOJ DIGITAL + ROTACIÓN DE MENSAJES MOTIVACIONALES
    ════════════════════════════════════════════════ */
    (function () {
        const saludoEl    = document.getElementById('saludoDinamico');
        const iconoEl     = document.getElementById('iconoSaludo');
        const mensajeEl   = document.getElementById('mensajeMotivacional');
        const relojEl     = document.getElementById('relojDigital');

        const saludosManana = ['Buenos días', '¡Buen día!', 'Muy buenos días', '¡Arriba que es temprano!', 'Buenos días, campeón'];
        const saludosTarde  = ['Buenas tardes', '¡Muy buenas tardes', '¿Cómo va tu tarde?', 'Buenas tardes, vamos con todo'];
        const saludosNoche  = ['Buenas noches', '¡Muy buenas noches', '¿Cómo va tu noche?', 'Buenas noches, gran trabajo hoy'];

        const mensajesRaw  = mensajeEl?.dataset?.mensajes;
        let mensajesList   = [];
        try { mensajesList = mensajesRaw ? JSON.parse(mensajesRaw) : []; } catch(e) { mensajesList = []; }
        let mensajeActualIdx = 0;
        if (mensajesList.length) {
            const inicial = mensajeEl?.dataset?.mensajeInicial || '';
            const idx = mensajesList.indexOf(inicial);
            mensajeActualIdx = idx >= 0 ? idx : 0;
        }

        function actualizarSaludo(hora) {
            if (!saludoEl || !iconoEl) return;
            let arr = saludosNoche, icono = 'fa-moon', color = '#8b5cf6';
            if (hora >= 5 && hora < 12)       { arr = saludosManana; icono = 'fa-sun'; color = '#f59e0b'; }
            else if (hora >= 12 && hora < 19) { arr = saludosTarde;  icono = 'fa-sun'; color = '#f97316'; }
            const seed = new Date().getMinutes() + new Date().getDate();
            saludoEl.textContent = arr[seed % arr.length];
            if (iconoEl) {
                iconoEl.className = 'fas ' + icono;
                iconoEl.style.color = color;
            }
        }

        function actualizarReloj() {
            const ahora = new Date();
            if (relojEl) {
                const hh = String(ahora.getHours()).padStart(2, '0');
                const mm = String(ahora.getMinutes()).padStart(2, '0');
                const ss = String(ahora.getSeconds()).padStart(2, '0');
                relojEl.textContent = hh + ':' + mm + ':' + ss;
            }
            if (ahora.getSeconds() === 0 || ahora.getMinutes() % 30 === 0) {
                actualizarSaludo(ahora.getHours());
            }
        }

        function rotarMensaje() {
            if (!mensajeEl || !mensajesList.length) return;
            mensajeActualIdx = (mensajeActualIdx + 1) % mensajesList.length;
            const siguiente = mensajesList[mensajeActualIdx];

            if ('animate' in mensajeEl && mensajeEl.animate) {
                try {
                    mensajeEl.animate(
                        [
                            { opacity: 1, transform: 'translateY(0)', filter: 'blur(0px)' },
                            { opacity: 0, transform: 'translateY(-6px)', filter: 'blur(2px)', offset: .45 },
                            { opacity: 0, transform: 'translateY(6px)',  filter: 'blur(2px)', offset: .55 },
                            { opacity: 1, transform: 'translateY(0)', filter: 'blur(0px)' }
                        ],
                        { duration: 900, easing: 'cubic-bezier(.2,.9,.3,1)' }
                    );
                    setTimeout(() => { mensajeEl.textContent = siguiente; }, 405);
                } catch (e) {
                    mensajeEl.textContent = siguiente;
                }
            } else {
                mensajeEl.style.opacity = 0;
                setTimeout(() => {
                    mensajeEl.textContent = siguiente;
                    mensajeEl.style.opacity = 1;
                }, 250);
            }
        }

        // Inicializar
        const ahora = new Date();
        actualizarSaludo(ahora.getHours());
        actualizarReloj();
        setInterval(actualizarReloj, 1000);
        if (mensajesList && mensajesList.length > 1) {
            // Rotar cada 12 segundos (12000ms) — frecuencia equilibrada, no molesta
            setInterval(rotarMensaje, 12000);
        }
    })();

    const isDark = document.body.classList.contains('dark-mode');

    /* ─────────────────────────────────────────────────────
       Helpers de colores / gradientes
       ───────────────────────────────────────────────────── */
    function barGradient(ctx, chartArea, c1, c2) {
        const g = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
        g.addColorStop(0, isDark ? c2 + '22' : c2 + '33');
        g.addColorStop(1, isDark ? c1 : c1);
        return g;
    }

    const gridColor   = isDark ? 'rgba(255,255,255,0.04)' : '#eef2f7';
    const ticksColor  = isDark ? '#9fb6cf' : '#64748b';
    const tooltipBg   = isDark ? '#0b1220' : '#0f172a';
    const tooltipText = isDark ? '#e2e8f0' : '#f1f5f9';
    const fontConf    = { family: 'Inter, system-ui, sans-serif', size: 11 };

    const baseTooltip = {
        backgroundColor: tooltipBg,
        titleColor: tooltipText,
        bodyColor: isDark ? '#b6c7d9' : '#cbd5e1',
        padding: 10,
        borderColor: isDark ? 'rgba(99,102,241,0.35)' : 'rgba(79,70,229,0.15)',
        borderWidth: 1,
        cornerRadius: 10,
        boxPadding: 6,
        titleFont: { weight: 700, size: 12 }
    };

    /* ─────────────────────────────────────────────────────
       VENTAS POR DÍA (Barras)
       ───────────────────────────────────────────────────── */
    const vCtx = document.getElementById('ventasChart');
    if (vCtx) {
        const c2d = vCtx.getContext('2d');
        new Chart(c2d, {
            type: 'bar',
            data: {
                labels: @json($labels),
                datasets: [{
                    label: 'Ventas (Bs)',
                    data: @json($data),
                    borderRadius: 8,
                    borderSkipped: false,
                    borderWidth: 0,
                    maxBarThickness: 36,
                    backgroundColor: function(ctx) {
                        const {chart} = ctx;
                        if (!chart.chartArea) return isDark ? '#818cf8' : '#6366f1';
                        return barGradient(c2d, chart.chartArea, '#6366f1', '#7c3aed');
                    },
                    hoverBackgroundColor: '#a78bfa'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...baseTooltip,
                        callbacks: {
                            label: (c) => ` Bs ${Number(c.raw).toFixed(2)}`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor, drawBorder: false },
                        ticks: { ...fontConf, color: ticksColor,
                                 callback: v => 'Bs ' + Number(v).toLocaleString() }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { ...fontConf, color: ticksColor, maxRotation: 45, minRotation: 0 }
                    }
                }
            }
        });
    }

    /* ─────────────────────────────────────────────────────
       ESTADO DE PEDIDOS (Dona)
       ───────────────────────────────────────────────────── */
    const eCtx = document.getElementById('estadoChart');
    if (eCtx) {
        const completados = {{ (int)$totalPedidosCompletados }};
        const anulados    = {{ (int)$totalPedidosAnulados }};
        const otros = Math.max(0, {{ (int)$totalPedidos }} - completados - anulados);
        new Chart(eCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Completados', 'Anulados', 'Otros'],
                datasets: [{
                    data: [completados, anulados, otros],
                    backgroundColor: ['#10b981', '#ef4444', '#64748b'],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: ticksColor,
                            font: { family: 'Inter', size: 12, weight: 600 },
                            padding: 18,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: baseTooltip
                }
            }
        });
    }

    /* ─────────────────────────────────────────────────────
       TENDENCIA MENSUAL (Línea + eje secundario)
       ───────────────────────────────────────────────────── */
    const tCtx = document.getElementById('tendenciaChart');
    if (tCtx) {
        const c2d = tCtx.getContext('2d');
        const t = () => ({
            id: 'trendlineGradientId',
            beforeDraw: (chart) => {
                if (chart.chartArea) return;
                const {ctx, chartArea} = chart;
                const g = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                g.addColorStop(0, isDark ? 'rgba(16,185,129,.28)' : 'rgba(16,185,129,.22)');
                g.addColorStop(1, isDark ? 'rgba(16,185,129,.02)' : 'rgba(16,185,129,.02)');
                return g;
            }
        });
        new Chart(c2d, {
            type: 'line',
            data: {
                labels: @json($labelsMes),
                datasets: [
                    {
                        type: 'line',
                        label: 'Ventas mensuales (Bs)',
                        data: @json($dataVentasMes),
                        borderColor: '#10b981',
                        backgroundColor: function(ctx) {
                            const {chart} = ctx;
                            if (!chart.chartArea) return 'rgba(16,185,129,.20)';
                            const g = c2d.createLinearGradient(0, chart.chartArea.top, 0, chart.chartArea.bottom);
                            g.addColorStop(0, isDark ? 'rgba(16,185,129,.35)' : 'rgba(16,185,129,.26)');
                            g.addColorStop(1, isDark ? 'rgba(16,185,129,.02)' : 'rgba(16,185,129,.02)');
                            return g;
                        },
                        fill: true,
                        tension: .35,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#10b981',
                        pointBorderWidth: 2.5,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        borderWidth: 3,
                        yAxisID: 'y'
                    },
                    {
                        type: 'line',
                        label: 'Cantidad de pedidos',
                        data: @json($dataCantMes),
                        borderColor: '#7c3aed',
                        backgroundColor: 'transparent',
                        tension: .35,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#7c3aed',
                        pointBorderWidth: 2.5,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        borderWidth: 2.5,
                        borderDash: [6, 4],
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        labels: { color: ticksColor, font: fontConf, padding: 16, usePointStyle: true, pointStyle: 'circle' }
                    },
                    tooltip: baseTooltip
                },
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { ...fontConf, color: ticksColor,
                                 callback: v => 'Bs ' + Number(v).toLocaleString() }
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,
                        grid: { display: false },
                        ticks: { ...fontConf, color: isDark ? '#c4b5fd' : '#7c3aed',
                                 callback: v => v + ' ped.' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { ...fontConf, color: ticksColor }
                    }
                }
            }
        });
    }
});

// ── Sincronizar paleta de Chart.js al cambiar tema ──
window.addEventListener('theme-changed', function(e) {
    const dark = e.detail && e.detail.isDark;
    const newGrid = dark ? 'rgba(255,255,255,0.04)' : '#eef2f7';
    const newTicks = dark ? '#9fb6cf' : '#64748b';
    Chart.helpers.each(Chart.instances, function(inst) {
        try {
            if (inst.options.scales) {
                Object.values(inst.options.scales).forEach(function(sc) {
                    if (sc.grid) sc.grid.color = newGrid;
                    if (sc.ticks) sc.ticks.color = newTicks;
                });
            }
            if (inst.options.plugins?.legend?.labels) {
                inst.options.plugins.legend.labels.color = newTicks;
            }
            inst.update('none');
        } catch (err) {}
    });
});
</script>
@endpush
