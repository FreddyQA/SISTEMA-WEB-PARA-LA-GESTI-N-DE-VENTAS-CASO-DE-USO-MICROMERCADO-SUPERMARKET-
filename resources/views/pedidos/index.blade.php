@extends('layouts.app')

@section('title', 'Pedidos')
@section('page-title', 'Pedidos')
@section('breadcrumb')
    <li class="breadcrumb-item active">Pedidos</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-cart-shopping mr-2 text-danger"></i>
            Lista de Pedidos
        </h5>
        @if(auth()->user()->rol === 'administrador')
            <a href="{{ route('pedidos.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-cart-plus mr-1"></i> Nuevo Pedido
            </a>
        @endif
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tablaPedidos" class="table table-hover mb-0">
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
                    @forelse($pedidos as $p)
                        <tr>
                            <td>{{ $p->idPedido }}</td>
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
                                    <a href="{{ route('pedidos.edit', $p->idPedido) }}"
                                       class="btn btn-warning btn-sm mr-1">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('pedidos.destroy', $p->idPedido) }}"
                                          method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm"
                                                onclick="return confirm('¿Eliminar este pedido? El stock reservado se devolverá al inventario.')">
                                            <i class="fas fa-trash"></i>
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
                                No hay pedidos registrados
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
    $(document).ready(function () {
        $('#tablaPedidos').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: [2, 7] }]
        });
    });
</script>
@endpush
