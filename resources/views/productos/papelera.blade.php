@extends('layouts.app')

@section('title', 'Papelera')
@section('page-title', 'Papelera de Productos')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('productos.index') }}">Productos</a></li>
    <li class="breadcrumb-item active">Papelera</li>
@endsection

@section('content')

<div class="callout callout-warning">
    <i class="fas fa-triangle-exclamation mr-2"></i>
    Los productos en la papelera no aparecen en el sistema. Puedes restaurarlos o eliminarlos permanentemente.
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-trash-can mr-2 text-danger"></i>
            Productos Eliminados
            <span class="badge badge-danger ml-2">{{ $productos->count() }}</span>
        </h5>
        <a href="{{ route('productos.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tablaPapelera" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Categoría</th>
                        <th>Eliminado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $p)
                        <tr class="bg-light">
                            <td>
                                @if($p->imagen)
                                    <img src="{{ asset('storage/' . $p->imagen) }}"
                                         width="48" height="48"
                                         class="rounded"
                                         style="object-fit:cover; opacity:.6;">
                                @else
                                    <div class="d-flex align-items-center justify-content-center bg-secondary rounded"
                                         style="width:48px;height:48px; opacity:.5;">
                                        <i class="fas fa-image text-white"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <s class="text-muted">{{ $p->nombre }}</s>
                            </td>
                            <td class="text-muted">Bs {{ number_format($p->precio, 2) }}</td>
                            <td class="text-muted">{{ $p->categoria->nombre ?? '—' }}</td>
                            <td>
                                <span class="badge badge-warning">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $p->deleted_at->format('d/m/Y H:i') }}
                                </span>
                            </td>
                            <td>
                                <form action="{{ route('productos.restore', $p->idProducto) }}"
                                      method="POST" class="d-inline">
                                    @csrf @method('PUT')
                                    <button class="btn btn-success btn-sm mr-1">
                                        <i class="fas fa-rotate-left mr-1"></i> Restaurar
                                    </button>
                                </form>
                                <form action="{{ route('productos.forceDelete', $p->idProducto) }}"
                                      method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('¿Eliminar PERMANENTEMENTE? No se puede deshacer.')">
                                        <i class="fas fa-trash mr-1"></i> Borrar
                                    </button>
                                </form>
                            </td>
                        </tr>
                   @empty
<tr>
    <td></td>
    <td></td>
    <td class="text-center text-muted">
        La papelera está vacía
    </td>
    <td></td>
    <td></td>
    <td></td>
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
        $('#tablaPapelera').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            order: [[4, 'desc']],
            columnDefs: [{ orderable: false, targets: [0, 5] }]
        });
    });
</script>
@endpush
