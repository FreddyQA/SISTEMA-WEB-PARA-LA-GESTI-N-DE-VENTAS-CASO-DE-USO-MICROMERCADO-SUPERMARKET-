@extends('layouts.app')

@section('title', 'Reporte de Ventas')
@section('page-title', 'Reporte de Ventas')
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

    /* Estilos de productos en tabla */
    .product-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
        max-width: 380px;
    }
    .product-card {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(241, 245, 249, 0.6);
        padding: 5px 8px;
        border-radius: 8px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        font-size: .83rem;
    }
    body.dark-mode .product-card {
        background: rgba(15, 23, 42, 0.6);
        border-color: rgba(51, 65, 85, 0.8);
    }
    .product-card.is-deleted {
        opacity: 0.6;
        border-style: dashed;
    }
    .product-card .p-info {
        flex: 1;
        min-width: 0;
    }
    .product-card .p-name {
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .product-card .p-qty {
        font-weight: 700;
        color: #4f46e5;
        white-space: nowrap;
    }
    .product-card .p-amount {
        font-weight: 600;
        white-space: nowrap;
        color: #059669;
    }
    .btn-preset {
        font-size: .78rem;
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: 600;
        transition: all .2s;
    }
</style>
@endpush

@section('content')

{{-- ═══ PANEL DE FILTROS AVANZADO ═══ --}}
<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2 py-3 border-bottom">
        <h5 class="mb-0 font-weight-bold">
            <i class="fas fa-sliders mr-2 text-primary"></i>
            Filtros del Reporte
        </h5>
        {{-- Botones de presets rápidos --}}
        <div class="d-flex flex-wrap gap-1 align-items-center">
            <span class="text-muted small mr-1 font-weight-semibold">Atajos:</span>
            <button type="button" class="btn btn-outline-secondary btn-sm btn-preset" onclick="setPreset('hoy')">Hoy</button>
            <button type="button" class="btn btn-outline-secondary btn-sm btn-preset" onclick="setPreset('ayer')">Ayer</button>
            <button type="button" class="btn btn-outline-secondary btn-sm btn-preset" onclick="setPreset('7dias')">Últimos 7 días</button>
            <button type="button" class="btn btn-outline-secondary btn-sm btn-preset" onclick="setPreset('este_mes')">Este Mes</button>
            <button type="button" class="btn btn-outline-secondary btn-sm btn-preset" onclick="setPreset('mes_anterior')">Mes Anterior</button>
            <button type="button" class="btn btn-outline-secondary btn-sm btn-preset" onclick="setPreset('este_ano')">Este Año</button>
        </div>
    </div>

    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('reportes.ventas') }}" id="filtroVentasForm">
            <div class="row g-3 align-items-end">

                {{-- Fecha Inicio --}}
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label font-weight-semibold text-muted small text-uppercase">
                        <i class="far fa-calendar-alt mr-1 text-primary"></i> Fecha Inicio
                    </label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control"
                           value="{{ request('fecha_inicio') }}">
                </div>

                {{-- Fecha Fin --}}
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label font-weight-semibold text-muted small text-uppercase">
                        <i class="far fa-calendar-alt mr-1 text-primary"></i> Fecha Fin
                    </label>
                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control"
                           value="{{ request('fecha_fin') }}">
                </div>

                {{-- Cliente --}}
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label font-weight-semibold text-muted small text-uppercase">
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

                {{-- Estado del Pedido --}}
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label font-weight-semibold text-muted small text-uppercase">
                        <i class="fas fa-check-circle mr-1 text-primary"></i> Estado
                    </label>
                    <select name="estado" id="estado_pedido" class="form-select">
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
            <div class="stat-label">Total Pedidos</div>
            <div class="stat-number">{{ $totalPedidos }}</div>
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
        <h5 class="mb-0 font-weight-bold">
            <i class="fas fa-table-list mr-2 text-primary"></i>
            Detalle de Ventas Registradas ({{ $pedidos->count() }})
        </h5>

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
                        <th>Productos / Artículos</th>
                        <th class="text-center" style="width: 90px;">Cantidad</th>
                        <th class="text-center" style="width: 100px;">Estado</th>
                        <th class="text-end" style="width: 120px;">Total</th>
                        <th class="text-center" style="width: 110px;">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $p)
                        <tr>
                            <td>
                                <strong>#{{ $p->idPedido }}</strong>
                            </td>
                            <td>
                                <div class="font-weight-semibold">{{ $p->cliente->nombre ?? '—' }}</div>
                                @if(!empty($p->cliente->telefono))
                                    <small class="text-muted"><i class="fas fa-phone fa-xs mr-1"></i>{{ $p->cliente->telefono }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="product-list">
                                    @foreach($p->detalles as $d)
                                        @php
                                            $nombreProd = $d->producto->nombre ?? 'Producto eliminado';
                                            $eliminado  = !$d->producto;
                                        @endphp
                                        <div class="product-card {{ $eliminado ? 'is-deleted' : '' }}">
                                            <div class="p-info">
                                                <div class="p-name" title="{{ $nombreProd }}">
                                                    {{ $nombreProd }}
                                                </div>
                                            </div>
                                            <div class="p-qty">× {{ $d->cantidad }}</div>
                                            <div class="p-amount">Bs {{ number_format($d->subtotal, 2) }}</div>
                                        </div>
                                    @endforeach
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
                                <h6>No se encontraron ventas para los filtros seleccionados</h6>
                                <p class="small mb-0">Prueba ajustando el rango de fechas o limpiando los filtros.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
        $('#tablaReporte').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            order: [[0, 'desc']],
            pageLength: 25
        });
    });
</script>
@endpush
