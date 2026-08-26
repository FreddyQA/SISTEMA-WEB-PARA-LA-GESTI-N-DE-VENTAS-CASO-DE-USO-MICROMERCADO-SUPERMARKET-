@extends('layouts.app')

@section('title', 'Reporte de Ventas')
@section('page-title', 'Reporte de Ventas')
@section('use-datatables', 'true')
@section('breadcrumb')
    <li class="breadcrumb-item active">Reportes</li>
@endsection

@push('styles')
<style>
    .report-stat-card {
        min-height: 140px;
        justify-content: center;
        border-radius: 16px !important;
        border: none !important;
        padding: 20px 24px !important;
        position: relative;
        overflow: hidden;
        animation: report-card-in .55s cubic-bezier(.2,.9,.3,1) both;
        transition: all .25s ease;
    }
    .report-stat-card:nth-child(2) { animation-delay: .06s; }
    .report-stat-card:nth-child(3) { animation-delay: .12s; }
    .report-stat-card:nth-child(4) { animation-delay: .18s; }
    .report-stat-card.bg-gradient-success {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
        box-shadow: 0 10px 24px -6px rgba(16, 185, 129, 0.4) !important;
    }
    .report-stat-card.bg-gradient-primary {
        background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%) !important;
        box-shadow: 0 10px 24px -6px rgba(79, 70, 229, 0.4) !important;
    }
    .report-stat-card.bg-gradient-purple {
        background: linear-gradient(135deg, #7c3aed 0%, #9333ea 100%) !important;
        box-shadow: 0 10px 24px -6px rgba(147, 51, 234, 0.4) !important;
    }
    .report-stat-card.bg-gradient-warning {
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%) !important;
        box-shadow: 0 10px 24px -6px rgba(245, 158, 11, 0.4) !important;
    }
    .report-stat-card .stat-icon {
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 3.8rem;
        opacity: 0.18;
        color: #ffffff !important;
        pointer-events: none;
    }
    .report-stat-card .stat-number {
        color: #ffffff !important;
        font-size: clamp(1.6rem, 2.5vw, 2.1rem) !important;
        font-weight: 900 !important;
        letter-spacing: -0.02em;
        font-variant-numeric: tabular-nums;
        text-shadow: 0 2px 8px rgba(0,0,0,.2);
        margin-top: 4px;
        line-height: 1.1;
    }
    .report-stat-card .stat-label {
        color: rgba(255,255,255,.92) !important;
        font-size: .80rem !important;
        font-weight: 700 !important;
        letter-spacing: .06em;
        text-transform: uppercase;
        text-shadow: 0 1px 3px rgba(0,0,0,.2);
    }
    .report-stat-card:hover {
        transform: translateY(-3px) scale(1.01);
    }
    @keyframes report-card-in {
        from { opacity: 0; transform: translateY(14px) scale(.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Estilos con tarjetas, sombras y pastillas de colores para productos en reportes */
    .report-product-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 280px;
        max-width: 440px;
    }
    .report-product-card {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 8px 10px;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.06);
        font-size: .84rem;
        transition: all .2s ease;
    }
    .report-product-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 3px 8px rgba(15, 23, 42, 0.08);
        transform: translateY(-1px);
    }
    body.dark-mode .report-product-card,
    html.dark-mode .report-product-card {
        background: rgba(15, 23, 42, 0.75);
        border-color: rgba(51, 65, 85, 0.9);
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.25);
    }
    body.dark-mode .report-product-card:hover,
    html.dark-mode .report-product-card:hover {
        background: rgba(30, 41, 59, 0.85);
        border-color: #6366f1;
    }
    .report-product-card.is-deleted {
        opacity: 0.65;
        border-style: dashed;
        background: rgba(254, 242, 242, 0.6);
    }
    body.dark-mode .report-product-card.is-deleted,
    html.dark-mode .report-product-card.is-deleted {
        background: rgba(69, 10, 10, 0.25);
        border-color: rgba(239, 68, 68, 0.4);
    }
    .rp-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: rgba(99, 102, 241, 0.12);
        color: #4f46e5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .95rem;
        flex-shrink: 0;
    }
    body.dark-mode .rp-icon,
    html.dark-mode .rp-icon {
        background: rgba(99, 102, 241, 0.25);
        color: #818cf8;
    }
    .rp-icon-deleted {
        background: rgba(239, 68, 68, 0.12);
        color: #dc2626;
    }
    body.dark-mode .rp-icon-deleted,
    html.dark-mode .rp-icon-deleted {
        background: rgba(239, 68, 68, 0.25);
        color: #f87171;
    }
    .rp-info {
        flex: 1;
        min-width: 0;
    }
    .rp-title-row {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    .rp-name {
        font-weight: 700;
        color: #1e293b;
        font-size: .86rem;
        line-height: 1.25;
        background: transparent !important;
    }
    body.dark-mode .rp-name,
    html.dark-mode .rp-name {
        color: #f1f5f9;
        background: transparent !important;
    }
    .rp-cat-badge {
        font-size: .75rem;
        padding: 1px 6px;
        border-radius: 6px;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        font-weight: 500;
    }
    body.dark-mode .rp-cat-badge,
    html.dark-mode .rp-cat-badge {
        background: rgba(51, 65, 85, 0.6);
        color: #94a3b8;
        border-color: rgba(71, 85, 105, 0.6);
    }
    .rp-meta {
        font-size: .75rem;
        color: #64748b;
        margin-top: 2px;
        background: transparent !important;
    }
    body.dark-mode .rp-meta,
    html.dark-mode .rp-meta {
        color: #94a3b8;
        background: transparent !important;
    }
    .rp-unit {
        background: transparent !important;
    }
    body.dark-mode .rp-unit,
    html.dark-mode .rp-unit {
        background: transparent !important;
    }
    .rp-sub {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 2px;
        flex-shrink: 0;
    }
    .rp-qty-badge {
        font-size: .75rem;
        font-weight: 700;
        background: rgba(79, 70, 229, 0.1);
        color: #4338ca;
        border: 1px solid rgba(79, 70, 229, 0.2);
        padding: 2px 7px;
        border-radius: 6px;
        white-space: nowrap;
    }
    body.dark-mode .rp-qty-badge,
    html.dark-mode .rp-qty-badge {
        background: rgba(99, 102, 241, 0.2);
        color: #a5b4fc;
        border-color: rgba(99, 102, 241, 0.4);
    }
    .rp-amount {
        font-weight: 700;
        color: #047857;
        font-size: .85rem;
        white-space: nowrap;
        background: transparent !important;
    }
    body.dark-mode .rp-amount,
    html.dark-mode .rp-amount {
        color: #34d399;
        background: transparent !important;
    }
    .report-product-summary {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 6px;
        padding-top: 6px;
        border-top: 1px dashed rgba(203, 213, 225, 0.5);
    }
    body.dark-mode .report-product-summary,
    html.dark-mode .report-product-summary {
        border-top-color: rgba(71, 85, 105, 0.4);
    }
    .rps-pill {
        font-size: .75rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 20px;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    body.dark-mode .rps-pill,
    html.dark-mode .rps-pill {
        background: rgba(51, 65, 85, 0.5);
        color: #94a3b8;
        border-color: rgba(71, 85, 105, 0.5);
    }
    .rps-pill.rps-total {
        background: rgba(16, 185, 129, 0.1);
        color: #047857;
        border-color: rgba(16, 185, 129, 0.2);
        font-weight: 700;
    }
    body.dark-mode .rps-pill.rps-total,
    html.dark-mode .rps-pill.rps-total {
        background: rgba(52, 211, 153, 0.15);
        color: #34d399;
        border-color: rgba(52, 211, 153, 0.3);
    }
    .btn-preset {
        font-size: .875rem;
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: 600;
        transition: all .2s;
    }
    #tablaReporte th, #tablaReporte td {
        vertical-align: middle;
    }
    #tablaReporte_wrapper {
        padding: 0.75rem 1rem;
    }
</style>
@endpush

@section('content')

{{-- ═══ PANEL DE FILTROS AVANZADO ═══ --}}
<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2 py-3 border-bottom">
        <h2 class="h5 mb-0 font-weight-bold">
            <i class="fas fa-sliders mr-2 text-primary"></i>
            Filtros del Reporte
        </h2>
        @php
            $fIni = request('fecha_inicio');
            $fFin = request('fecha_fin');
            $hoyStr  = \Carbon\Carbon::now()->toDateString();
            $ayerStr = \Carbon\Carbon::now()->subDay()->toDateString();
            $hace7Str = \Carbon\Carbon::now()->subDays(6)->toDateString();
            $esteMesIni = \Carbon\Carbon::now()->startOfMonth()->toDateString();
            $esteMesFin = \Carbon\Carbon::now()->endOfMonth()->toDateString();
        @endphp
        {{-- Botones de presets rápidos --}}
        <div class="d-flex flex-wrap gap-1 align-items-center">
            <span class="text-muted small mr-1 font-weight-semibold">Atajos:</span>
            <button type="button" class="btn {{ $fIni === $hoyStr && $fFin === $hoyStr ? 'btn-primary' : 'btn-outline-secondary' }} btn-sm btn-preset" onclick="setPreset('hoy')">Hoy</button>
            <button type="button" class="btn {{ $fIni === $ayerStr && $fFin === $ayerStr ? 'btn-primary' : 'btn-outline-secondary' }} btn-sm btn-preset" onclick="setPreset('ayer')">Ayer</button>
            <button type="button" class="btn {{ $fIni === $hace7Str && $fFin === $hoyStr ? 'btn-primary' : 'btn-outline-secondary' }} btn-sm btn-preset" onclick="setPreset('7dias')">Últimos 7 días</button>
            <button type="button" class="btn {{ $fIni === $esteMesIni && $fFin === $esteMesFin ? 'btn-primary' : 'btn-outline-secondary' }} btn-sm btn-preset" onclick="setPreset('este_mes')">Este Mes</button>
            <button type="button" class="btn btn-outline-secondary btn-sm btn-preset" onclick="setPreset('mes_anterior')">Mes Anterior</button>
            <button type="button" class="btn btn-outline-secondary btn-sm btn-preset" onclick="setPreset('este_ano')">Este Año</button>
        </div>
    </div>

    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('reportes.ventas') }}" id="filtroVentasForm">
            <div class="row g-3 align-items-end">

                {{-- Fecha Inicio --}}
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="fecha_inicio" class="form-label font-weight-semibold text-muted small text-uppercase">
                        <i class="far fa-calendar-alt mr-1 text-primary"></i> Fecha Inicio
                    </label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control"
                           value="{{ request('fecha_inicio') }}">
                </div>

                {{-- Fecha Fin --}}
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="fecha_fin" class="form-label font-weight-semibold text-muted small text-uppercase">
                        <i class="far fa-calendar-alt mr-1 text-primary"></i> Fecha Fin
                    </label>
                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control"
                           value="{{ request('fecha_fin') }}">
                </div>

                {{-- Cliente --}}
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="id_cliente" class="form-label font-weight-semibold text-muted small text-uppercase">
                        <i class="far fa-user mr-1 text-primary"></i> Cliente
                    </label>
                    <select name="id_cliente" id="id_cliente" class="form-select">
                        <option value="">Todos los clientes</option>
                        @foreach($clientes as $cli)
                            <option value="{{ $cli->idCliente }}" {{ request('id_cliente') == $cli->idCliente ? 'selected' : '' }}>
                                {{ $cli->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Estado del Venta --}}
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="estado_venta" class="form-label font-weight-semibold text-muted small text-uppercase">
                        <i class="fas fa-check-circle mr-1 text-primary"></i> Estado
                    </label>
                    <select name="estado" id="estado_venta" class="form-select">
                        <option value="todos" {{ request('estado') === 'todos' || !request('estado') ? 'selected' : '' }}>Todos los estados</option>
                        <option value="completado" {{ request('estado') === 'completado' ? 'selected' : '' }}>Solo Completados</option>
                        <option value="anulado" {{ request('estado') === 'anulado' ? 'selected' : '' }}>Solo Anulados</option>
                    </select>
                </div>

                {{-- Botones de Acción --}}
                <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-filter mr-1"></i> Aplicar Filtros
                        </button>
                        <a href="{{ route('reportes.ventas') }}" class="btn btn-outline-secondary px-3">
                            <i class="fas fa-rotate-left mr-1"></i> Limpiar Filtros
                        </a>
                    </div>

                    @php
                        $filtrosActivos = false;
                        if (request('fecha_inicio') || request('fecha_fin') || request('id_cliente') || (request('estado') && request('estado') !== 'todos')) {
                            $filtrosActivos = true;
                        }
                    @endphp

                    @if($filtrosActivos)
                        <div class="d-flex align-items-center gap-1 text-muted small">
                            <span class="badge bg-primary-subtle text-primary border px-2 py-1">
                                <i class="fas fa-info-circle mr-1"></i> Filtros aplicados
                            </span>
                        </div>
                    @endif
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ═══ TARJETAS DE TOTALES Y MÉTRICAS ═══ --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card report-stat-card bg-gradient-success">
            <i class="fas fa-money-bill-wave stat-icon"></i>
            <div class="stat-label">Total en Ventas</div>
            <div class="stat-number">Bs {{ number_format($totalVentas, 2) }}</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card report-stat-card bg-gradient-primary">
            <i class="fas fa-receipt stat-icon"></i>
            <div class="stat-label">Total de ventas</div>
            <div class="stat-number">{{ $cantidadVentas }}</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card report-stat-card bg-gradient-purple">
            <i class="fas fa-boxes-stacked stat-icon"></i>
            <div class="stat-label">Unidades Vendidas</div>
            <div class="stat-number">{{ $totalUnidades }}</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card report-stat-card bg-gradient-warning">
            <i class="fas fa-chart-line stat-icon"></i>
            <div class="stat-label">Ticket Promedio</div>
            <div class="stat-number">Bs {{ number_format($promedioVenta, 2) }}</div>
        </div>
    </div>
</div>

{{-- ═══ TABLA Y EXPORTACIONES ═══ --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2 py-3 border-bottom">
        <h2 class="h5 mb-0 font-weight-bold">
            <i class="fas fa-table-list mr-2 text-primary"></i>
            Detalle de Ventas Registradas ({{ $ventas->total() }})
        </h2>

        {{-- Botones de exportación: SIEMPRE DISPONIBLES con los filtros actuales --}}
        <div class="d-flex gap-2">
            <a href="{{ route('reportes.ventas.pdf', request()->query()) }}" class="btn btn-danger btn-sm" download>
                <i class="fas fa-file-pdf mr-1"></i> Exportar PDF
            </a>
            <a href="{{ route('reportes.ventas.excel', request()->query()) }}" class="btn btn-success btn-sm" download>
                <i class="fas fa-file-excel mr-1"></i> Exportar Excel
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tablaReporte" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Cliente</th>
                        <th style="min-width: 320px;">Productos / Artículos</th>
                        <th class="text-center" style="width: 90px;">Cantidad</th>
                        <th class="text-center" style="width: 100px;">Estado</th>
                        <th class="text-end" style="width: 120px;">Total</th>
                        <th class="text-center" style="width: 110px;">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventas as $p)
                        <tr>
                            <td>
                                <strong>#{{ $p->idVenta }}</strong>
                            </td>
                            <td>
                                <div class="font-weight-semibold">{{ $p->cliente->nombre ?? '—' }}</div>
                                @if(!empty($p->cliente->telefono))
                                    <small class="text-muted"><i class="fas fa-phone fa-xs mr-1"></i>{{ $p->cliente->telefono }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="report-product-list">
                                    @foreach($p->detalles as $d)
                                        @php
                                            $nombreProd = $d->producto->nombre ?? 'Producto eliminado';
                                            $eliminado  = !$d->producto;
                                            $catProd    = $d->producto->categoria->nombre ?? null;
                                        @endphp
                                        <div class="report-product-card {{ $eliminado ? 'is-deleted' : '' }}">
                                            <div class="rp-icon {{ $eliminado ? 'rp-icon-deleted' : '' }}">
                                                <i class="fas {{ $eliminado ? 'fa-circle-xmark' : 'fa-box' }}"></i>
                                            </div>
                                            <div class="rp-info">
                                                <div class="rp-title-row">
                                                    <span class="rp-name" title="{{ $nombreProd }}">{{ $nombreProd }}</span>
                                                    @if($catProd)
                                                        <span class="rp-cat-badge">{{ $catProd }}</span>
                                                    @endif
                                                </div>
                                                <div class="rp-meta">
                                                    <span class="rp-unit">c/u: Bs {{ number_format($d->precio_unitario, 2) }}</span>
                                                </div>
                                            </div>
                                            <div class="rp-sub">
                                                <span class="rp-qty-badge">📦 {{ $d->cantidad }} u.</span>
                                                <span class="rp-amount">Bs {{ number_format($d->subtotal, 2) }}</span>
                                            </div>
                                        </div>
                                    @endforeach

                                    @php
                                        $totalQty = $p->detalles->sum('cantidad');
                                        $totalSub = $p->detalles->sum('subtotal');
                                        $nProd    = $p->detalles->count();
                                    @endphp
                                    <div class="report-product-summary">
                                        <span class="rps-pill" title="Variedad de productos">
                                            <i class="fas fa-boxes-stacked mr-1"></i> {{ $nProd }} {{ $nProd === 1 ? 'artículo' : 'artículos' }}
                                        </span>
                                        <span class="rps-pill" title="Total unidades">
                                            <i class="fas fa-cubes mr-1"></i> {{ $totalQty }} u.
                                        </span>
                                        <span class="rps-pill rps-total" title="Subtotal acumulado">
                                            <i class="fas fa-calculator mr-1"></i> Bs {{ number_format($totalSub, 2) }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border font-weight-bold">
                                    {{ $p->detalles->sum('cantidad') }} u.
                                </span>
                            </td>
                            <td class="text-center">
                                @if($p->estado === 'anulado')
                                    <span class="badge bg-danger">Anulado</span>
                                @else
                                    <span class="badge bg-success">Completado</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <strong class="{{ $p->estado === 'anulado' ? 'text-muted text-decoration-line-through' : 'text-success' }}" style="font-size: 1.05rem;">
                                    Bs {{ number_format($p->total, 2) }}
                                </strong>
                            </td>
                            <td class="text-center text-muted">
                                {{ $p->fecha ? \Carbon\Carbon::parse($p->fecha)->format('d/m/Y') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-file-circle-xmark fa-3x mb-3 text-secondary d-block"></i>
                                <h3 class="h6">No hay ventas registradas en el período seleccionado</h3>
                                <p class="small mb-0">No se encontraron ventas para los filtros seleccionados. Prueba ajustando el rango de fechas o limpiando los filtros.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $ventas->links() }}
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function setPreset(tipo) {
        const now = new Date();
        const fInicio = document.getElementById('fecha_inicio');
        const fFin = document.getElementById('fecha_fin');

        function formatDate(d) {
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        if (tipo === 'hoy') {
            const str = formatDate(now);
            fInicio.value = str;
            fFin.value = str;
        } else if (tipo === 'ayer') {
            const ayer = new Date();
            ayer.setDate(now.getDate() - 1);
            const str = formatDate(ayer);
            fInicio.value = str;
            fFin.value = str;
        } else if (tipo === '7dias') {
            const hace7 = new Date();
            hace7.setDate(now.getDate() - 6);
            fInicio.value = formatDate(hace7);
            fFin.value = formatDate(now);
        } else if (tipo === 'este_mes') {
            const primero = new Date(now.getFullYear(), now.getMonth(), 1);
            const ultimo = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            fInicio.value = formatDate(primero);
            fFin.value = formatDate(ultimo);
        } else if (tipo === 'mes_anterior') {
            const primero = new Date(now.getFullYear(), now.getMonth() - 1, 1);
            const ultimo = new Date(now.getFullYear(), now.getMonth(), 0);
            fInicio.value = formatDate(primero);
            fFin.value = formatDate(ultimo);
        } else if (tipo === 'este_ano') {
            const primero = new Date(now.getFullYear(), 0, 1);
            const ultimo = new Date(now.getFullYear(), 11, 31);
            fInicio.value = formatDate(primero);
            fFin.value = formatDate(ultimo);
        }

        // Auto-enviar formulario al seleccionar preset
        document.getElementById('filtroVentasForm').submit();
    }

    $(document).ready(function () {
        @if($ventas->count() > 0)
        if ($.fn.DataTable) {
            $('#tablaReporte').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                    search: "Buscar:",
                    searchPlaceholder: "Filtrar por cliente, producto o fecha en tiempo real..."
                },
                order: [[0, 'desc']],
                paging: false,
                info: false,
                searching: true,
                columnDefs: [{ orderable: false, targets: [2] }]
            });
        }
        @endif
    });
</script>
@endpush
