@extends('layouts.app')

@section('title', 'Nueva Venta')
@section('page-title', 'Nueva Venta')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('ventas.index') }}">Ventas</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
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
    #tabla-items thead th { font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
    #resumen-total { border-top: 2px solid #e2e8f0; padding-top: 1rem; margin-top: .5rem; }
    #items-vacio { background: #f8fafc; border-radius: 12px; }
    .buscador-venta {
        position: relative;
    }
    .buscador-venta-input {
        border: 1.5px solid #c7d2fe !important;
        background: linear-gradient(180deg, #eef2ff 0%, #ffffff 100%) !important;
        border-radius: 12px !important;
        padding: .7rem 1rem .7rem 2.6rem !important;
        min-height: 48px;
        box-shadow: 0 6px 18px rgba(79,70,229,.08);
    }
    .buscador-venta-input:focus {
        border-color: #4f46e5 !important;
        box-shadow: 0 0 0 4px rgba(79,70,229,.18) !important;
        background: #fff !important;
    }
    .buscador-venta-icono {
        position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
        color: #4f46e5; pointer-events: none;
    }
    #resultados-productos {
        max-height: 280px; overflow-y: auto; border-radius: 12px;
        border: 1px solid #e0e7ff;
    }
    #resultados-productos .list-group-item {
        border: 0; border-bottom: 1px solid #eef2ff;
        background: #fff;
    }
    #resultados-productos .list-group-item:hover,
    #resultados-productos .list-group-item:focus {
        background: #eef2ff; color: #312e81;
    }
    html.dark-mode .buscador-venta-input,
    body.dark-mode .buscador-venta-input {
        background: rgba(8,20,34,.7) !important;
        border-color: rgba(99,102,241,.45) !important;
        color: #e6eef8 !important;
    }
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
                    <i class="fas fa-cart-plus mr-2 text-primary"></i>
                    Registrar Nueva Venta
                </h2>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('ventas.store') }}" method="POST" id="form-venta" novalidate>
                    @csrf

                    @if($errors->any())
                        <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
                            <i class="fas fa-circle-exclamation fa-lg flex-shrink-0"></i>
                            <div>
                                <strong>Corrige los siguientes errores:</strong>
                                <ul class="mb-0 mt-1 ps-3">
                                    @foreach($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <div class="row g-3 mb-4">
                        {{-- Cliente --}}
                        <div class="col-12 col-md-6">
                            <label for="venta_idCliente" class="form-label font-weight-semibold">
                                <i class="fas fa-user mr-1 text-primary"></i>
                                Cliente <span class="text-danger">*</span>
                            </label>
                            <select name="idCliente" id="venta_idCliente"
                                    class="form-select @error('idCliente') is-invalid @enderror" required>
                                <option value="">— Seleccione un cliente —</option>
                                @foreach($clientes as $c)
                                    <option value="{{ $c->idCliente }}"
                                        {{ old('idCliente') == $c->idCliente ? 'selected' : '' }}>
                                        {{ $c->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('idCliente')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    {{-- Sección de Productos --}}
                    <div class="border rounded-3 p-3 p-md-4 mb-4" style="background:rgba(248,250,252,.6);">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="h6 mb-0 font-weight-bold">
                                <i class="fas fa-boxes-stacked mr-2 text-primary"></i>
                                Productos de la Venta
                            </h3>
                        </div>

                        <label for="busqueda-producto" class="form-label font-weight-semibold">
                            Buscar y añadir al carrito
                        </label>
                        <div class="buscador-venta mb-2">
                            <i class="fas fa-magnifying-glass buscador-venta-icono"></i>
                            <input type="search" id="busqueda-producto" class="form-control buscador-venta-input"
                                   autocomplete="off" placeholder="Escribe el nombre o código (mín. 2 caracteres)...">
                        </div>
                        <div id="resultados-productos" class="list-group mb-3" aria-live="polite"></div>
                        <div id="error-busqueda-productos" class="small text-danger mb-2" role="alert"></div>

                        @error('items')
                            <div class="alert alert-warning py-2 mb-3">
                                <i class="fas fa-triangle-exclamation mr-1"></i> {{ $message }}
                            </div>
                        @enderror

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tabla-items">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="min-width:220px;">Producto</th>
                                        <th scope="col" style="width:110px;">Cantidad</th>
                                        <th scope="col" style="width:120px;" class="text-end">Precio Unit.</th>
                                        <th scope="col" style="width:130px;" class="text-center">Stock Disp.</th>
                                        <th scope="col" style="width:120px;" class="text-end">Subtotal</th>
                                        <th scope="col" style="width:46px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-items"></tbody>
                            </table>
                        </div>

                        <div id="items-vacio" class="text-center text-muted py-4">
                            <i class="fas fa-cart-shopping fa-2x mb-2 d-block opacity-25"></i>
                            <span class="small">Busca un producto y selecciónalo para añadirlo al carrito.</span>
                        </div>

                        {{-- Total general --}}
                        <div id="resumen-total" class="d-flex justify-content-end align-items-center gap-3 flex-wrap" style="display:none!important;">
                            <span class="text-muted font-weight-semibold">Total de la venta:</span>
                            <span id="total-general-display">Bs 0.00</span>
                        </div>
                    </div>

                    {{-- Acciones --}}
                    <div class="d-flex gap-2 pt-2">
                        <button type="submit" class="btn btn-primary px-4" id="btn-guardar">
                            <i class="fas fa-save mr-1"></i> Registrar Venta
                        </button>
                        <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary px-3">
                            <i class="fas fa-arrow-left mr-1"></i> Volver
                        </a>
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
    const tbody        = document.getElementById('tbody-items');
    const vacioBanner  = document.getElementById('items-vacio');
    const resumenTotal = document.getElementById('resumen-total');
    const totalDisplay = document.getElementById('total-general-display');
    const buscador = document.getElementById('busqueda-producto');
    const resultados = document.getElementById('resultados-productos');
    const errorBusqueda = document.getElementById('error-busqueda-productos');
    const rutaBusqueda = @json(route('productos.buscar'));
    let temporizadorBusqueda;
    let consultaActual;

    function fmt(val) {
        return 'Bs ' + parseFloat(val || 0).toLocaleString('es-BO', {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        });
    }

    function recalcularTotal() {
        let total = 0;
        tbody.querySelectorAll('.order-item-row').forEach(function (fila) {
            const precio   = parseFloat(fila.querySelector('.celda-precio').dataset.precio || 0);
            const cantidad = Math.max(1, parseInt(fila.querySelector('.input-cantidad').value, 10) || 0);
            const subtotal = precio * cantidad;
            fila.querySelector('.celda-subtotal').textContent = precio > 0 ? fmt(subtotal) : '—';
            total += precio > 0 ? subtotal : 0;
        });
        totalDisplay.textContent = fmt(total);
    }

    function actualizarStockBadge(fila, stock) {
        const badge = fila.querySelector('.celda-stock .stock-badge');
        badge.className = 'stock-badge';
        if (stock === null || stock === undefined) {
            badge.textContent = '—';
        } else if (stock === 0) {
            badge.classList.add('zero');
            badge.textContent = 'Sin stock';
        } else if (stock <= 5) {
            badge.classList.add('low');
            badge.textContent = stock + ' u. (bajo)';
        } else {
            badge.classList.add('ok');
            badge.textContent = stock + ' u.';
        }
    }

    function actualizarVacio() {
        const hayFilas = tbody.querySelectorAll('.order-item-row').length > 0;
        vacioBanner.style.display   = hayFilas ? 'none' : '';
        resumenTotal.style.setProperty('display', hayFilas ? 'flex' : 'none', 'important');
    }

    function agregarFila(producto) {
        const existente = tbody.querySelector('[data-producto-id="' + producto.id + '"]');
        if (existente) {
            const input = existente.querySelector('.input-cantidad');
            if (Number(input.value) >= producto.stock) {
                errorBusqueda.textContent = 'No hay más unidades disponibles de ' + producto.nombre + '.';
                return;
            }
            input.value = Number(input.value) + 1;
            recalcularTotal();
            errorBusqueda.textContent = '';
            return;
        }

        const fila = document.createElement('tr');
        fila.className = 'order-item-row';
        fila.dataset.productoId = String(producto.id);
        fila.innerHTML = `
          <td><input type="hidden" name="items[${indice}][idProducto]" value="${Number(producto.id)}"><strong class="nombre-producto"></strong></td>
          <td><input type="number" name="items[${indice}][cantidad]" class="form-control input-cantidad" min="1" value="1" required></td>
          <td class="price-cell text-end celda-precio"></td>
          <td class="text-center celda-stock"><span class="stock-badge ok"></span></td>
          <td class="subtotal-cell text-end celda-subtotal">Bs 0.00</td>
          <td><button type="button" class="btn btn-outline-danger btn-sm btn-quitar-item"><i class="fas fa-trash" aria-hidden="true"></i></button></td>`;
        indice++;
        tbody.appendChild(fila);

        const inputCant    = fila.querySelector('.input-cantidad');
        const celdaPrecio  = fila.querySelector('.celda-precio');
        fila.querySelector('.nombre-producto').textContent = producto.nombre;
        inputCant.setAttribute('aria-label', 'Cantidad de ' + producto.nombre);
        fila.querySelector('.btn-quitar-item').setAttribute('aria-label', 'Quitar ' + producto.nombre + ' de la venta');
        celdaPrecio.dataset.precio = String(producto.precio);
        celdaPrecio.textContent = fmt(producto.precio);
        inputCant.max = String(producto.stock);
        fila.querySelector('.stock-badge').textContent = producto.stock + ' u.';

        inputCant.addEventListener('input', function () {
            if (parseInt(this.value, 10) > producto.stock) this.value = producto.stock;
            if (parseInt(this.value, 10) < 1) this.value = 1;
            recalcularTotal();
        });

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
            if (!respuesta.ok) throw new Error('No se pudo buscar productos (HTTP ' + respuesta.status + ').');
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
                detalle.className = 'text-muted ml-2';
                detalle.textContent = 'Bs ' + Number(producto.precio).toFixed(2) + ' · ' + producto.stock + ' disponibles';
                boton.append(nombre, detalle);
                boton.addEventListener('click', () => agregarFila(producto));
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

    // Validar antes de enviar
    document.getElementById('form-venta').addEventListener('submit', function (e) {
        const filas = tbody.querySelectorAll('.order-item-row');
        if (filas.length === 0) {
            e.preventDefault();
            alert('Debe agregar al menos un producto a la venta antes de guardar.');
            return;
        }
    });

    actualizarVacio();
})();
</script>
@endpush
