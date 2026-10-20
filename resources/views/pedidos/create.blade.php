@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <h2>Nuevo Pedido</h2>

    <form action="{{ route('pedidos.store') }}" method="POST" id="form-pedido">
        @csrf

        <div class="form-group mb-3">
            <label>Cliente</label>
            <select name="idCliente" class="form-control">
                <option value="">-- Seleccione --</option>
                @foreach($clientes as $c)
                    <option value="{{ $c->idCliente }}"
                        {{ old('idCliente') == $c->idCliente ? 'selected' : '' }}>
                        {{ $c->nombre }}
                    </option>
                @endforeach
            </select>
            @error('idCliente')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label>Fecha</label>
            <input type="date" name="fecha" class="form-control"
                   value="{{ old('fecha', date('Y-m-d')) }}">
            @error('fecha')
                <small class="text-danger">{{ $message }}</small>
            @enderror
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

        <div id="items-vacio" class="text-muted mb-3">Aún no agregó productos.</div>

        <div>
            <button class="btn btn-primary">Guardar</button>
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
    const vacio = document.getElementById('items-vacio');

    function actualizarVacio() {
        vacio.style.display = tbody.children.length === 0 ? 'block' : 'none';
    }

    function agregarFila() {
        const html = template.innerHTML.replaceAll('__INDEX__', indice++);
        const wrapper = document.createElement('tbody');
        wrapper.innerHTML = html;
        const fila = wrapper.firstElementChild;
        tbody.appendChild(fila);

        fila.querySelector('.select-producto').addEventListener('change', function () {
            const opt = this.selectedOptions[0];
            const stock = opt ? (opt.getAttribute('data-stock') || '—') : '—';
            fila.querySelector('.celda-stock').textContent = stock;
            const cantidadInput = fila.querySelector('.input-cantidad');
            if (opt && opt.getAttribute('data-stock')) {
                cantidadInput.max = opt.getAttribute('data-stock');
            }
        });

        fila.querySelector('.btn-quitar-item').addEventListener('click', function () {
            fila.remove();
            actualizarVacio();
        });

        actualizarVacio();
    }

    document.getElementById('btn-add-item').addEventListener('click', agregarFila);

    // Al menos una fila para empezar
    agregarFila();
})();
</script>
@endpush
