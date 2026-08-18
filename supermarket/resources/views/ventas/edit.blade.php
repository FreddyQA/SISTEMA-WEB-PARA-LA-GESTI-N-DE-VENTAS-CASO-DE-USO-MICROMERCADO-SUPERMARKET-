@extends('layouts.app')

@section('title', 'Editar Venta')
@section('page-title', 'Editar Venta')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('ventas.index') }}">Ventas</a></li>
    <li class="breadcrumb-item active">Editar #{{ $venta->numero ?? $venta->idVenta }}</li>
@endsection

@push('styles')
<style>
    .order-item-row td { vertical-align: middle; }
    .price-cell  { font-variant-numeric: tabular-nums; font-weight: 600; color: #475569; }
    .subtotal-cell { font-weight: 700; color: #059669; font-variant-numeric: tabular-nums; }
    #total-general-display {
        font-size: 1.5rem; font-weight: 900; color: #059669;
        letter-spacing: -0.02em; font-variant-numeric: tabular-nums;
    }
    .stock-badge { font-size: .72rem; padding: 2px 8px; border-radius: 8px; font-weight: 700; }
    .stock-badge.ok   { background: #d1fae5; color: #065f46; }
    .stock-badge.low  { background: #fef3c7; color: #92400e; }
    .stock-badge.zero { background: #fee2e2; color: #991b1b; }
    .buscador-venta { position: relative; }
    .buscador-venta-input {
        border: 1.5px solid #c7d2fe !important;
        background: linear-gradient(180deg, #eef2ff 0%, #ffffff 100%) !important;
        border-radius: 12px !important;
        padding: .7rem 1rem .7rem 2.6rem !important;
        min-height: 48px;
        box-shadow: 0 6px 18px rgba(79,70,229,.08);
    }
    .buscador-venta-icono {
        position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
        color: #4f46e5; pointer-events: none;
    }
    #resultados-productos { max-height: 280px; overflow-y: auto; border-radius: 12px; border: 1px solid #e0e7ff; }
    @media (max-width: 768px) {
        #tabla-items th, #tabla-items td { white-space: nowrap; }
        .buscador-venta-input { font-size: 16px !important; }
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent py-3 border-bottom">
                <h2 class="h5 mb-0 font-weight-bold">
                    <i class="fas fa-pen mr-2 text-warning"></i>
                    Editar Venta #{{ $venta->numero ?? $venta->idVenta }}
                </h2>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('ventas.update', $venta->idVenta) }}" method="POST" id="form-venta" novalidate>
                    @csrf
                    @method('PUT')

                    @if($errors->any())
                        <div class="alert alert-danger mb-4">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label for="venta_idCliente" class="form-label font-weight-semibold">Cliente</label>
                            <select name="idCliente" id="venta_idCliente" class="form-select" required>
                                @foreach($clientes as $c)
                                    <option value="{{ $c->idCliente }}" {{ old('idCliente', $venta->idCliente) == $c->idCliente ? 'selected' : '' }}>
                                        {{ $c->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="venta_estado" class="form-label font-weight-semibold">Estado</label>
                            <select name="estado" id="venta_estado" class="form-select">
                                <option value="completado" {{ old('estado', $venta->estado) == 'completado' ? 'selected' : '' }}>Completado</option>
                                <option value="anulado" {{ old('estado', $venta->estado) == 'anulado' ? 'selected' : '' }}>Anulado</option>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="fecha" value="{{ old('fecha', \Carbon\Carbon::parse($venta->fecha)->format('Y-m-d')) }}">

                    <div class="border rounded-3 p-3 p-md-4 mb-4" style="background:rgba(248,250,252,.6);">
                        <h3 class="h6 mb-3 font-weight-bold">
                            <i class="fas fa-boxes-stacked mr-2 text-primary"></i>
                            Productos de la Venta
                        </h3>
                        <label for="busqueda-producto" class="form-label font-weight-semibold">Buscar y añadir al carrito</label>
                        <div class="buscador-venta mb-2">
                            <i class="fas fa-magnifying-glass buscador-venta-icono"></i>
                            <input type="search" id="busqueda-producto" class="form-control buscador-venta-input"
                                   autocomplete="off" placeholder="Escribe el nombre o código (mín. 2 caracteres)...">
                        </div>
                        <div id="resultados-productos" class="list-group mb-3" aria-live="polite"></div>
                        <div id="error-busqueda-productos" class="small text-danger mb-2" role="alert"></div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tabla-items">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Producto</th>
                                        <th scope="col" style="width:110px;">Cantidad</th>
                                        <th scope="col" class="text-end">Precio Unit.</th>
                                        <th scope="col" class="text-center">Stock Disp.</th>
                                        <th scope="col" class="text-end">Subtotal</th>
                                        <th scope="col">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-items"></tbody>
                            </table>
                        </div>
                        <div id="items-vacio" class="text-center text-muted py-4">
                            Busca un producto y selecciónalo para añadirlo al carrito.
                        </div>
                        <div id="resumen-total" class="d-flex justify-content-end align-items-center gap-3 flex-wrap mt-3" style="display:none!important;">
                            <span class="text-muted font-weight-semibold">Total de la venta:</span>
                            <span id="total-general-display">Bs 0.00</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-warning px-4">Actualizar Venta</button>
                        <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary">Volver</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    let indice = 0;
    const tbody = document.getElementById('tbody-items');
    const vacioBanner = document.getElementById('items-vacio');
    const resumenTotal = document.getElementById('resumen-total');
    const totalDisplay = document.getElementById('total-general-display');
    const buscador = document.getElementById('busqueda-producto');
    const resultados = document.getElementById('resultados-productos');
    const errorBusqueda = document.getElementById('error-busqueda-productos');
    const rutaBusqueda = @json(route('productos.buscar'));
    const iniciales = @json($itemsIniciales);
    let temporizadorBusqueda;
    let consultaActual;

    function fmt(val) {
        return 'Bs ' + parseFloat(val || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function recalcularTotal() {
        let total = 0;
        tbody.querySelectorAll('.order-item-row').forEach(function (fila) {
            const precio = parseFloat(fila.querySelector('.celda-precio').dataset.precio || 0);
            const cantidad = Math.max(1, parseInt(fila.querySelector('.input-cantidad').value, 10) || 0);
            const subtotal = precio * cantidad;
            fila.querySelector('.celda-subtotal').textContent = fmt(subtotal);
            total += subtotal;
        });
        totalDisplay.textContent = fmt(total);
    }
    function actualizarVacio() {
        const hayFilas = tbody.querySelectorAll('.order-item-row').length > 0;
        vacioBanner.style.display = hayFilas ? 'none' : '';
        resumenTotal.style.setProperty('display', hayFilas ? 'flex' : 'none', 'important');
    }
    function agregarFila(producto, cantidadInicial) {
        const existente = tbody.querySelector('[data-producto-id="' + producto.id + '"]');
        if (existente) {
            const input = existente.querySelector('.input-cantidad');
            input.value = Number(input.value) + (cantidadInicial || 1);
            recalcularTotal();
            return;
        }
        const fila = document.createElement('tr');
        fila.className = 'order-item-row';
        fila.dataset.productoId = String(producto.id);
        fila.innerHTML = `
          <td><input type="hidden" name="items[${indice}][idProducto]" value="${Number(producto.id)}"><strong class="nombre-producto"></strong></td>
          <td><input type="number" name="items[${indice}][cantidad]" class="form-control input-cantidad" min="1" value="${cantidadInicial || 1}" required></td>
          <td class="price-cell text-end celda-precio"></td>
          <td class="text-center celda-stock"><span class="stock-badge ok"></span></td>
          <td class="subtotal-cell text-end celda-subtotal">Bs 0.00</td>
          <td><button type="button" class="btn btn-outline-danger btn-sm btn-quitar-item"><i class="fas fa-trash" aria-hidden="true"></i></button></td>`;
        indice++;
        tbody.appendChild(fila);
        fila.querySelector('.nombre-producto').textContent = producto.nombre;
        const celdaPrecio = fila.querySelector('.celda-precio');
        celdaPrecio.dataset.precio = String(producto.precio);
        celdaPrecio.textContent = fmt(producto.precio);
        fila.querySelector('.input-cantidad').setAttribute('aria-label', 'Cantidad de ' + producto.nombre);
        fila.querySelector('.btn-quitar-item').setAttribute('aria-label', 'Quitar ' + producto.nombre + ' de la venta');
        fila.querySelector('.stock-badge').textContent = (producto.stock ?? 0) + ' u.';
        fila.querySelector('.input-cantidad').addEventListener('input', recalcularTotal);
        fila.querySelector('.btn-quitar-item').addEventListener('click', function () {
            fila.remove();
            actualizarVacio();
            recalcularTotal();
        });
        actualizarVacio();
        recalcularTotal();
    }

    async function buscarProductos() {
        const termino = buscador.value.trim();
        resultados.replaceChildren();
        errorBusqueda.textContent = '';
        if (termino.length < 2) return;
        if (consultaActual) consultaActual.abort();
        consultaActual = new AbortController();
        resultados.textContent = 'Buscando...';
        try {
            const respuesta = await fetch(rutaBusqueda + '?q=' + encodeURIComponent(termino), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal: consultaActual.signal,
            });
            if (!respuesta.ok) throw new Error('No se pudo buscar productos.');
            const datos = await respuesta.json();
            resultados.replaceChildren();
            if (!datos.data.length) {
                resultados.textContent = 'No se encontraron productos disponibles.';
                return;
            }
            datos.data.forEach((producto) => {
                const boton = document.createElement('button');
                boton.type = 'button';
                boton.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                const nombre = document.createElement('span');
                nombre.textContent = producto.nombre;
                const detalle = document.createElement('small');
                detalle.className = 'text-muted';
                detalle.textContent = 'Bs ' + Number(producto.precio).toFixed(2) + ' · ' + producto.stock + ' disponibles';
                boton.append(nombre, detalle);
                boton.addEventListener('click', () => agregarFila(producto, 1));
                resultados.appendChild(boton);
            });
        } catch (error) {
            if (error.name !== 'AbortError') errorBusqueda.textContent = error.message;
        }
    }

    buscador.addEventListener('input', function () {
        clearTimeout(temporizadorBusqueda);
        temporizadorBusqueda = setTimeout(buscarProductos, 250);
    });
    document.getElementById('form-venta').addEventListener('submit', function (e) {
        if (tbody.querySelectorAll('.order-item-row').length === 0) {
            e.preventDefault();
            alert('Debe agregar al menos un producto a la venta.');
        }
    });
    iniciales.forEach((item) => agregarFila(item, item.cantidad));
    actualizarVacio();
})();
</script>
@endpush
