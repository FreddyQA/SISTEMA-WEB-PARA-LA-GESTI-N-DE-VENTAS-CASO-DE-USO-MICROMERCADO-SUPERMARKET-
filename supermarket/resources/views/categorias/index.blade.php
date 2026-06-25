@extends('layouts.app')

@section('title', 'Categorías')
@section('page-title', 'Categorías')
@section('use-datatables', 'true')
@section('breadcrumb')
    <li class="breadcrumb-item active">Categorías</li>
@endsection

@section('content')

<div id="contenedorCategorias" class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <h2 class="h5 mb-0">
                <i class="fas fa-layer-group mr-2 text-primary"></i>
                Lista de Categorías
            </h2>
            <span id="liveCategoryIndicator" class="live-indicator d-none">
                <span class="live-dot-pulse"></span>
                <span>Actualizado en vivo</span>
            </span>
        </div>
        @if(auth()->user()->rol === 'administrador')
            <a href="{{ route('categorias.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Nueva Categoría
            </a>
        @endif
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tablaCategorias" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Productos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categorias as $cat)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $cat->nombre }}</strong></td>
                            <td class="text-muted">{{ $cat->descripcion ?? '—' }}</td>
                            <td>
                                <span class="badge badge-primary">
                                    {{ $cat->productos->count() }} productos
                                </span>
                            </td>
                            <td>
                                @if(auth()->user()->rol === 'administrador')
                                    <a href="{{ route('categorias.edit', $cat->idCategoria) }}"
                                       class="btn btn-warning btn-sm mr-1" aria-label="Editar categoría {{ $cat->nombre }}">
                                        <i class="fas fa-pen" aria-hidden="true"></i>
                                    </a>
                                    <form action="{{ route('categorias.destroy', $cat->idCategoria) }}"
                                          method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm"
                                                aria-label="Eliminar categoría {{ $cat->nombre }}"
                                                onclick="return confirm('¿Eliminar esta categoría?')">
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
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-layer-group fa-2x mb-2 d-block"></i>
                                No hay categorías registradas
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $categorias->links() }}
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .live-dot-pulse {
        width: 8px;
        height: 8px;
        background-color: #10b981;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulseLiveCat 1.8s infinite;
    }
    @keyframes pulseLiveCat {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .live-indicator {
        background: rgba(16, 185, 129, 0.12);
        color: #065f46;
        border: 1px solid rgba(16, 185, 129, 0.35);
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.76rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    body.dark-mode .live-indicator {
        background: rgba(16, 185, 129, 0.2);
        color: #6ee7b7;
        border-color: rgba(16, 185, 129, 0.4);
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function () {
        function inicializarDataTable() {
            if (!$.fn.DataTable) return;
            const filas = $('#tablaCategorias tbody tr');
            if (filas.length === 0 || (filas.length === 1 && filas.find('td').hasClass('text-center'))) {
                return;
            }
            if ($.fn.DataTable.isDataTable('#tablaCategorias')) {
                $('#tablaCategorias').DataTable().destroy();
            }
            $('#tablaCategorias').DataTable({
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
                order: [[0, 'desc']],
                paging: false,
                info: false,
                searching: false,
                columnDefs: [{ orderable: false, targets: [4] }]
            });
        }

        inicializarDataTable();

        // ── AUTO-ACTUALIZACIÓN EN TIEMPO REAL SIN REFRESCAR ────────────────
        let isRefreshingCat = false;

        async function recargarCategoriasSinRefrescar() {
            if (isRefreshingCat) return;
            isRefreshingCat = true;
            try {
                const resp = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!resp.ok) return;
                const htmlTexto = await resp.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(htmlTexto, 'text/html');

                const nuevoCard = doc.getElementById('contenedorCategorias');
                const actualCard = document.getElementById('contenedorCategorias');

                if (nuevoCard && actualCard) {
                    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tablaCategorias')) {
                        $('#tablaCategorias').DataTable().destroy();
                    }

                    actualCard.innerHTML = nuevoCard.innerHTML;
                    inicializarDataTable();

                    const ind = document.getElementById('liveCategoryIndicator');
                    if (ind) {
                        ind.classList.remove('d-none');
                        setTimeout(() => ind.classList.add('d-none'), 4000);
                    }
                }
            } catch (err) {
                console.warn('Error al auto-actualizar categorías:', err);
            } finally {
                isRefreshingCat = false;
            }
        }

        // Escucha cambios globales en categorías
        window.addEventListener('sistema:sync', function (e) {
            if (e.detail && e.detail.categorias) {
                recargarCategoriasSinRefrescar();
            }
        });
    });
</script>
@endpush
