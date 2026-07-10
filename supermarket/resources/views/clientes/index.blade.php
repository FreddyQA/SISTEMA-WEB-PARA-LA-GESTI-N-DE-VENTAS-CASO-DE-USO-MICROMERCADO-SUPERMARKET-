@extends('layouts.app')

@section('title', 'Clientes')
@section('page-title', 'Clientes')
@section('use-datatables', 'true')
@section('breadcrumb')
    <li class="breadcrumb-item active">Clientes</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">
            <i class="fas fa-users mr-2 text-warning"></i>
            Lista de Clientes
        </h2>
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
                                       class="btn btn-warning btn-sm mr-1" aria-label="Editar cliente {{ $c->nombre }}">
                                        <i class="fas fa-pen" aria-hidden="true"></i>
                                    </a>
                                    <form action="{{ route('clientes.destroy', $c->idCliente) }}"
                                          method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm"
                                                aria-label="Eliminar cliente {{ $c->nombre }}"
                                                onclick="return confirm('¿Eliminar este cliente? Esta acción es permanente.')">
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
                            <td colspan="6" class="text-center py-4 text-muted">
                                @if(request('q'))
                                    <i class="fas fa-search fa-2x mb-2 d-block"></i>
                                    No se encontraron clientes para "{{ request('q') }}"
                                @else
                                    <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                    No hay clientes registrados
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($clientes, 'links'))
        <div class="mt-3">
            {{ $clientes->links() }}
        </div>
        @endif
    </div>
</div>

@endsection

@push('styles')
<style>
    #tablaClientes th, #tablaClientes td {
        vertical-align: middle;
    }
    .dataTables_wrapper {
        padding: 0.75rem 1rem;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function () {
        @if($clientes->count() > 0)
        if ($.fn.DataTable) {
            $('#tablaClientes').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                    search: "Buscar:",
                    searchPlaceholder: "Buscar cliente en tiempo real..."
                },
                pageLength: 10,
                order: [[0, 'desc']],
                columnDefs: [{ orderable: false, targets: [5] }],
                @if(request('q'))
                search: { search: @json(request('q')) },
                @endif
                drawCallback: () => {
                    try {
                        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                            bootstrap.Tooltip.getOrCreateInstance(el);
                        });
                    } catch (e) {}
                },
                initComplete: function () {
                    // Si el usuario presiona Enter en el buscador, busca sobre todos los registros en el servidor
                    const searchInput = $('#tablaClientes_filter input');
                    searchInput.on('keypress', function (e) {
                        if (e.which === 13) {
                            window.location.href = "{{ route('clientes.index') }}?q=" + encodeURIComponent(this.value);
                        }
                    });
                }
            });
        }
        @endif
    });
</script>
@endpush
