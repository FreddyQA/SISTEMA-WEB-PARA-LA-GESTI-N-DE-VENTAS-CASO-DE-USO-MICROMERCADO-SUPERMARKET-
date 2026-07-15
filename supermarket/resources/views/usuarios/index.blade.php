@extends('layouts.app')

@section('title', 'Usuarios')
@section('page-title', 'Gestión de Usuarios')
@section('use-datatables', 'true')
@section('breadcrumb')
    <li class="breadcrumb-item active">Usuarios</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">
            <i class="fas fa-user-shield mr-2 text-dark"></i>
            Lista de Usuarios
        </h2>
        <a href="{{ route('usuarios.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-user-plus mr-1"></i> Nuevo Usuario
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tablaUsuarios" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $u)
                        <tr>
                            <td>
                                @if($u->foto)
                                    <img src="{{ asset('storage/' . $u->foto) }}"
                                         alt="{{ $u->name }}"
                                         width="42" height="42"
                                         class="rounded-circle"
                                         style="object-fit:cover;">
                                @else
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($u->name) }}&background={{ $u->rol === 'administrador' ? 'ef4444' : '4f46e5' }}&color=fff&bold=true"
                                         alt="{{ $u->name }}"
                                         width="42" height="42" class="rounded-circle">
                                @endif
                            </td>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $u->name }}</strong>
                                @if($u->id === auth()->id())
                                    <span class="badge badge-success ml-1">Tú</span>
                                @endif
                            </td>
                            <td>{{ $u->email }}</td>
                            <td>
                                @if($u->rol === 'administrador')
                                    <span class="badge badge-danger px-2 py-1">
                                        <i class="fas fa-crown mr-1"></i> Administrador
                                    </span>
                                @else
                                    <span class="badge badge-primary px-2 py-1">
                                        <i class="fas fa-user mr-1"></i> Empleado
                                    </span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('usuarios.edit', $u->id) }}"
                                   class="btn btn-warning btn-sm mr-1" aria-label="Editar usuario {{ $u->name }}">
                                    <i class="fas fa-pen" aria-hidden="true"></i>
                                </a>
                                @if($u->id !== auth()->id())
                                    <form action="{{ route('usuarios.destroy', $u->id) }}"
                                          method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm"
                                                aria-label="Eliminar usuario {{ $u->name }}"
                                                onclick="return confirm('¿Eliminar usuario {{ $u->name }}?')">
                                            <i class="fas fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No hay usuarios registrados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $usuarios->links() }}
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        @if($usuarios->count() > 0)
        $('#tablaUsuarios').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            order: [[1, 'asc']],
            paging: false,
            info: false,
            searching: false,
            columnDefs: [{ orderable: false, targets: [0, 5] }]
        });
        @endif
    });
</script>
@endpush
