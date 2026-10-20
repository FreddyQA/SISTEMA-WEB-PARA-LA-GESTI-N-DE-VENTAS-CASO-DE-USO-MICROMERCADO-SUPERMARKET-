@extends('layouts.app')

@section('title', 'Categorías')
@section('page-title', 'Categorías')
@section('breadcrumb')
    <li class="breadcrumb-item active">Categorías</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-layer-group mr-2 text-primary"></i>
            Lista de Categorías
        </h5>
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
                                       class="btn btn-warning btn-sm mr-1">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('categorias.destroy', $cat->idCategoria) }}"
                                          method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm"
                                                onclick="return confirm('¿Eliminar esta categoría?')">
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
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-layer-group fa-2x mb-2 d-block"></i>
                                No hay categorías registradas
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
        $('#tablaCategorias').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: [4] }]
        });
    });
</script>
@endpush
