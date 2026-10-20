@extends('layouts.app')

@section('title', 'Clientes')
@section('page-title', 'Clientes')
@section('breadcrumb')
    <li class="breadcrumb-item active">Clientes</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-users mr-2 text-warning"></i>
            Lista de Clientes
        </h5>
        @if(auth()->user()->rol === 'administrador')
            <a href="{{ route('clientes.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-user-plus mr-1"></i> Nuevo Cliente
            </a>
        @endif
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tablaClientes" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientes as $c)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-2"
                                         style="width:34px;height:34px;font-size:.8rem;font-weight:700;">
                                        {{ strtoupper(substr($c->nombre, 0, 2)) }}
                                    </div>
                                    <strong>{{ $c->nombre }}</strong>
                                </div>
                            </td>
                            <td><a href="mailto:{{ $c->email }}">{{ $c->email }}</a></td>
                            <td>{{ $c->telefono ?? '—' }}</td>
                            <td class="text-muted">{{ $c->direccion ?? '—' }}</td>
                            <td>
                                @if(auth()->user()->rol === 'administrador')
                                    <a href="{{ route('clientes.edit', $c->idCliente) }}"
                                       class="btn btn-warning btn-sm mr-1">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('clientes.destroy', $c->idCliente) }}"
                                          method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm"
                                                onclick="return confirm('¿Eliminar este cliente? Esta acción es permanente.')">
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
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                No hay clientes registrados
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
        $('#tablaClientes').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: [5] }]
        });
    });
</script>
@endpush
