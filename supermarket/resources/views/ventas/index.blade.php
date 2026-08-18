@extends('layouts.app')

@section('title', 'Ventas')
@section('page-title', 'Ventas')
@section('use-datatables', 'true')
@section('breadcrumb')
    <li class="breadcrumb-item active">Ventas</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <h2 class="h5 mb-0">
                <i class="fas fa-cart-shopping mr-2 text-danger"></i>
                Historial de Ventas
            </h2>
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1" style="font-size: 0.72rem; display: inline-flex; align-items: center; gap: 5px;">
                <span class="live-dot-pulse"></span> En vivo
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div id="liveOrderIndicator" class="live-indicator d-none">
                <i class="fas fa-bell text-success mr-1"></i>
                <span id="liveOrderText" class="fw-semibold small">¡Nueva venta registrada!</span>
            </div>
            <a href="{{ route('ventas.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-cart-plus mr-1"></i> Nueva Venta
            </a>
        </div>
    </div>

    <div class="card-body p-0" id="contenedorVentas">
        <div class="table-responsive">
            <table id="tablaVentas" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Productos</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Registrado por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventas as $p)
                        <tr>
                            <td>{{ $p->numero }}</td>
                            <td>
                                <i class="fas fa-user-circle text-muted mr-1"></i>
                                {{ $p->cliente->nombre ?? '—' }}
                            </td>
                            <td>
                                <div class="product-list">
                                    @foreach($p->detalles as $d)
                                        @php
                                            $nombreProd = $d->producto->nombre ?? 'Producto eliminado';
                                            $eliminado  = !$d->producto;
                                        @endphp
                                        <div class="product-card {{ $eliminado ? 'is-deleted' : '' }}">
                                            <div class="p-icon">
                                                <i class="fas {{ $eliminado ? 'fa-circle-xmark' : 'fa-box' }}"></i>
                                            </div>
                                            <div class="p-info">
                                                <div class="p-name" title="{{ $nombreProd }}">
                                                    {{ $nombreProd }}
                                                </div>
                                                <div class="p-meta">
                                                    <span class="p-unit">Bs {{ number_format($d->precio_unitario, 2) }}</span>
                                                </div>
                                            </div>
                                            <div class="p-sub">
                                                <span class="p-qty">× {{ $d->cantidad }} u.</span>
                                                <span class="p-amount">Bs {{ number_format($d->subtotal, 2) }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                    @php
                                        $totalQty  = $p->detalles->sum('cantidad');
                                        $totalSub  = $p->detalles->sum('subtotal');
                                        $nProd    = $p->detalles->count();
                                    @endphp
                                    <div class="product-summary">
                                        <span class="ps-pill">
                                            <i class="fas fa-bag-shopping"></i>
                                            {{ $nProd }} producto{{ $nProd !== 1 ? 's' : '' }}
                                        </span>
                                        <span class="ps-pill">
                                            <i class="fas fa-cubes"></i>
                                            {{ $totalQty }} unidades
                                        </span>
                                        <span class="ps-pill" title="Suma subtotales (detalle)">
                                            <i class="fas fa-calculator"></i>
                                            Bs {{ number_format($totalSub, 2) }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong class="text-success">
                                    Bs {{ number_format($p->total, 2) }}
                                </strong>
                            </td>
                            <td>
                                <span class="badge {{ $p->estado === 'anulado' ? 'badge-secondary' : 'badge-success' }}">
                                    {{ ucfirst($p->estado) }}
                                </span>
                            </td>
                            <td>
                                <i class="fas fa-calendar text-muted mr-1"></i>
                                {{ \Carbon\Carbon::parse($p->fecha)->format('d/m/Y') }}
                            </td>
                            <td>{{ $p->usuario->name ?? '—' }}</td>
                            <td>
                                @if(auth()->user()->rol === 'administrador')
                                    <a href="{{ route('ventas.edit', $p->idVenta) }}"
                                       class="btn btn-warning btn-sm mr-1" aria-label="Editar venta {{ $p->numero ?? $p->idVenta }}">
                                        <i class="fas fa-pen" aria-hidden="true"></i>
                                    </a>
                                    <form action="{{ route('ventas.destroy', $p->idVenta) }}"
                                          method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm"
                                                aria-label="Eliminar venta {{ $p->numero ?? $p->idVenta }}"
                                                onclick="return confirm('¿Eliminar esta venta? El stock reservado se devolverá al inventario.')">
                                            <i class="fas fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="badge badge-secondary">Solo lectura</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-cart-shopping fa-2x mb-2 d-block"></i>
                                No hay ventas registradas
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    #tablaVentas th, #tablaVentas td {
        vertical-align: middle;
    }
    .dataTables_wrapper {
        padding: 0.75rem 1rem;
    }
    .live-dot-pulse {
        width: 8px;
        height: 8px;
        background-color: #10b981;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulseLive 1.8s infinite;
    }
    @keyframes pulseLive {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .live-indicator {
        background: rgba(16, 185, 129, 0.12);
        color: #065f46;
        border: 1px solid rgba(16, 185, 129, 0.35);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
    }
    body.dark-mode .live-indicator {
        background: rgba(16, 185, 129, 0.2);
        color: #6ee7b7;
        border-color: rgba(16, 185, 129, 0.4);
    }
    .fila-nueva-animada {
        animation: highlightFila 4s ease-out;
    }
    @keyframes highlightFila {
        0% { background-color: rgba(16, 185, 129, 0.45) !important; }
        50% { background-color: rgba(16, 185, 129, 0.22) !important; }
        100% { background-color: transparent; }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function () {
        let tablaVentasInstance = null;
        let ultimoNumeroRegistrado = {{ $ventas->first()?->numero ?? 0 }};
        let isRefreshing = false;

        function configurarDataTable() {
            if (!$.fn.DataTable) return;
            const filas = $('#tablaVentas tbody tr');
            if (filas.length === 0 || (filas.length === 1 && filas.find('td').hasClass('text-center'))) {
                return;
            }
            if ($.fn.DataTable.isDataTable('#tablaVentas')) {
                $('#tablaVentas').DataTable().destroy();
            }
            tablaVentasInstance = $('#tablaVentas').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                    search: "Buscar:",
                    searchPlaceholder: "Buscar venta o producto en tiempo real...",
                    paginate: {
                        previous: "Anterior",
                        next: "Siguiente"
                    }
                },
                pageLength: 10,
                order: [[0, 'desc']],
                columnDefs: [{ orderable: false, targets: [2, 7] }],
                drawCallback: () => {
                    try {
                        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                            bootstrap.Tooltip.getOrCreateInstance(el);
                        });
                    } catch (e) {}
                }
            });
        }

        configurarDataTable();

        // ── ACTUALIZACIÓN AUTOMÁTICA EN TIEMPO REAL (POLLING LIGERO) ─────
        // Monitorea nuevos ventas cada 3 segundos sin recargar la página completa
        setInterval(async function () {
            if (isRefreshing) return;
            try {
                const resp = await fetch("{{ route('ventas.poll') }}", {
                    headers: { 'Accept': 'application/json' }
                });
                if (!resp.ok) return;
                const data = await resp.json();

                if (data.ultimo_numero && data.ultimo_numero > ultimoNumeroRegistrado) {
                    isRefreshing = true;
                    const nuevoNumero = data.ultimo_numero;
                    ultimoNumeroRegistrado = nuevoNumero;
                    mostrarAvisoNuevaVenta(nuevoNumero);
                    await recargarTablaSinRefrescar(nuevoNumero);
                    isRefreshing = false;
                }
            } catch (err) {
                // Silencioso ante pérdidas transitorias de red
            }
        }, 3000);

        async function recargarTablaSinRefrescar(nuevoNumero) {
            try {
                const resp = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!resp.ok) return;
                const htmlTexto = await resp.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(htmlTexto, 'text/html');

                const nuevoCard = doc.getElementById('contenedorVentas');
                const actualCard = document.getElementById('contenedorVentas');

                if (nuevoCard && actualCard) {
                    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tablaVentas')) {
                        $('#tablaVentas').DataTable().destroy();
                    }

                    actualCard.innerHTML = nuevoCard.innerHTML;
                    configurarDataTable();

                    // Resalta visualmente la fila de la nueva venta registrada.
                    if (nuevoNumero) {
                        const trs = document.querySelectorAll('#tablaVentas tbody tr');
                        trs.forEach(tr => {
                            const tdNum = tr.querySelector('td:first-child');
                            if (tdNum && tdNum.textContent.trim() == String(nuevoNumero)) {
                                tr.classList.add('fila-nueva-animada');
                            }
                        });
                    }
                }
            } catch (e) {
                console.warn('Error al actualizar la tabla de ventas:', e);
            }
        }

        function mostrarAvisoNuevaVenta(id) {
            const el = document.getElementById('liveOrderIndicator');
            const txt = document.getElementById('liveOrderText');
            if (el && txt) {
                txt.textContent = '¡Nuevo venta #' + id + ' recibido! Lista actualizada en vivo.';
                el.classList.remove('d-none');
                setTimeout(() => {
                    el.classList.add('d-none');
                }, 5000);
            }
        }
    });
</script>
@endpush
