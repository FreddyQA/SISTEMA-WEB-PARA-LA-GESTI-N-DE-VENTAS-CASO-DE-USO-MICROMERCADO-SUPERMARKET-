@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <h2>Editar Pedido #{{ $pedido->idPedido }}</h2>

    <form action="{{ route('pedidos.update', $pedido->idPedido) }}" method="POST" id="form-pedido">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label>Cliente</label>
            <select name="idCliente" class="form-control">
                @foreach($clientes as $c)
                    <option value="{{ $c->idCliente }}"
                        {{ old('idCliente', $pedido->idCliente) == $c->idCliente ? 'selected' : '' }}>
                        {{ $c->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label>Estado</label>
            <select name="estado" class="form-control">
                <option value="completado" {{ old('estado', $pedido->estado) == 'completado' ? 'selected' : '' }}>Completado</option>
                <option value="anulado" {{ old('estado', $pedido->estado) == 'anulado' ? 'selected' : '' }}>Anulado</option>
            </select>
        </div>

        <div class="form-group mb-3">
            <label>Fecha</label>
            <input type="date" name="fecha" class="form-control"
                   value="{{ old('fecha', \Carbon\Carbon::parse($pedido->fecha)->format('Y-m-d')) }}">
        </div>

        <hr>
        <h5>Productos</h5>
        @error('items')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        <table class="table" id="tabla-items">
            <thead>
                <tr>
                    <th style="width:50%">Producto</th>
                    <th style="width:20%">Cantidad</th>
                    <th style="width:20%">Stock disp.</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="btn-add-item">
            <i class="fas fa-plus"></i> Agregar producto
        </button>

        <p class="text-muted small">
            Nota: el stock mostrado es el disponible ahora mismo (sin contar lo que este pedido ya tenía reservado).
            Al guardar, el sistema recalcula el stock automáticamente.
        </p>

        <div>
            <button class="btn btn-warning">Actualizar</button>
            <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">Volver</a>
        </div>

    </form>

</div>

<template id="fila-item-template">
    <tr class="fila-item">
        <td>
            <select name="items[__INDEX__][idProducto]" class="form-control select-producto" required>
                <option value="">-- Seleccione --</option>
                @foreach($productos as $p)
                    <option value="{{ $p->idProducto }}" data-precio="{{ $p->precio }}" data-stock="{{ $p->stock }}">
                        {{ $p->nombre }} — Bs {{ number_format($p->precio, 2) }}
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" name="items[__INDEX__][cantidad]" class="form-control input-cantidad" min="1" value="1" required>
        </td>
        <td class="celda-stock text-muted">—</td>
        <td>
            <button type="button" class="btn btn-danger btn-sm btn-quitar-item"><i class="fas fa-trash"></i></button>
        </td>
    </tr>
</template>

@endsection

@push('scripts')
<script>
(function () {
    let indice = 0;
    const tbody = document.querySelector('#tabla-items tbody');
    const template = document.getElementById('fila-item-template');

    function agregarFila(idProductoSeleccionado, cantidadInicial) {
        const html = template.innerHTML.replaceAll('__INDEX__', indice++);
        const wrapper = document.createElement('tbody');
        wrapper.innerHTML = html;
        const fila = wrapper.firstElementChild;
        tbody.appendChild(fila);

        const select = fila.querySelector('.select-producto');
        const cantidadInput = fila.querySelector('.input-cantidad');

        if (idProductoSeleccionado) {
            select.value = idProductoSeleccionado;
        }
        if (cantidadInicial) {
            cantidadInput.value = cantidadInicial;
        }

        select.addEventListener('change', function () {
            const opt = this.selectedOptions[0];
            const stock = opt ? (opt.getAttribute('data-stock') || '—') : '—';
            fila.querySelector('.celda-stock').textContent = stock;
        });
        select.dispatchEvent(new Event('change'));

        fila.querySelector('.btn-quitar-item').addEventListener('click', function () {
            fila.remove();
        });
    }

    document.getElementById('btn-add-item').addEventListener('click', () => agregarFila());

    const detalles = @json($pedido->detalles->map(fn($d) => ['idProducto' => $d->idProducto, 'cantidad' => $d->cantidad]));
    if (detalles.length > 0) {
        detalles.forEach(d => agregarFila(d.idProducto, d.cantidad));
    } else {
        agregarFila();
    }
})();
</script>
@endpush
