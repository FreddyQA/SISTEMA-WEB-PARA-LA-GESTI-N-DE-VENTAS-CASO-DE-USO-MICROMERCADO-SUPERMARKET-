@extends('layouts.app')

@section('title', 'Papelera')
@section('page-title', 'Papelera')
@section('use-datatables', 'true')
@section('breadcrumb')
    <li class="breadcrumb-item active">Papelera</li>
@endsection

@php
    $nCols        = count($meta['columnas']);
    $colEliminado = 3 + $nCols;   // columnas: chk, nombre, [extras…], estado, eliminado, acciones
    $colAcciones  = 4 + $nCols;
    $fem          = $meta['fem'] ?? false;
@endphp

@push('styles')
<style>
    /* ── Resumen superior ─────────────────────────────── */
    .papelera-resumen {
        display: flex; align-items: center; gap: 14px;
        padding: 16px 18px; margin-bottom: 18px;
        border-radius: 14px; color: #fff;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
    }
    .papelera-resumen-icono {
        width: 46px; height: 46px; flex: 0 0 46px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        background: rgba(255,255,255,.18); font-size: 1.2rem;
    }
    .papelera-resumen-titulo { font-weight: 700; font-size: 1.05rem; }
    .papelera-resumen-texto  { font-size: .85rem; opacity: .9; margin-top: 2px; }

    /* ── Pestañas por tipo ────────────────────────────── */
    .papelera-tabs { display: flex; flex-wrap: wrap; gap: 8px; list-style: none; padding: 0; margin: 0 0 16px; }
    .papelera-tab {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 9px 14px; border-radius: 12px; text-decoration: none;
        font-weight: 600; font-size: .92rem;
        color: #475569; background: #fff; border: 1px solid #e2e8f0;
        transition: transform .12s ease, box-shadow .12s ease;
    }
    .papelera-tab:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79,70,229,.15); color: #4f46e5; }
    .papelera-tab.activa {
        color: #fff; border-color: transparent;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        box-shadow: 0 6px 16px rgba(79,70,229,.35);
    }
    .papelera-tab-conteo {
        min-width: 24px; padding: 1px 8px; border-radius: 999px; text-align: center;
        font-size: .75rem; font-weight: 700; background: #ef4444; color: #fff;
    }
    .papelera-tab-conteo.vacio { background: #cbd5e1; color: #475569; }
    .papelera-tab.activa .papelera-tab-conteo { background: rgba(255,255,255,.25); color: #fff; }
    body.dark-mode .papelera-tab { background: var(--surface); border-color: rgba(255,255,255,.08); color: var(--muted); }
    body.dark-mode .papelera-tab:hover { color: #818cf8; }
    body.dark-mode .papelera-tab.activa { color: #fff; }
    body.dark-mode .papelera-tab-conteo.vacio { background: rgba(255,255,255,.12); color: var(--muted); }

    /* ── Tabla ────────────────────────────────────────── */
    .papelera-nombre { font-weight: 600; }
    .papelera-thumb {
        width: 38px; height: 38px; border-radius: 10px; object-fit: cover; flex: 0 0 38px; background: #eef2ff;
    }
    .papelera-thumb-vacio { display: inline-flex; align-items: center; justify-content: center; color: #4f46e5; font-size: .95rem; }
    body.dark-mode .papelera-thumb { background: var(--surface-soft); }
    #tablaPapelera tbody tr { cursor: pointer; transition: background .12s ease; }
    #tablaPapelera tbody tr.seleccionada { background: rgba(79,70,229,.10) !important; }
    body.dark-mode #tablaPapelera tbody tr.seleccionada { background: rgba(129,140,248,.14) !important; }
    .col-chk { width: 42px; }
    .papelera-chip {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 9px; border-radius: 999px; font-size: .74rem; font-weight: 600; white-space: nowrap;
    }
    .papelera-chip-ok      { background: #dcfce7; color: #166534; }
    .papelera-chip-bloqueo { background: #fee2e2; color: #991b1b; }
    .papelera-chip-aviso   { background: #fef3c7; color: #92400e; }
    .papelera-motivo { display: block; margin-top: 3px; font-size: .74rem; color: #64748b; max-width: 260px; white-space: normal; }
    body.dark-mode .papelera-motivo { color: var(--muted); }

    /* ── Estado vacío ─────────────────────────────────── */
    .papelera-vacio { text-align: center; padding: 56px 20px; }
    .papelera-vacio-icono {
        width: 78px; height: 78px; margin: 0 auto 14px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.9rem; color: #4f46e5; background: rgba(79,70,229,.10);
    }

    /* ── Barra de acciones en lote ────────────────────── */
    .papelera-barra {
        position: fixed; left: 50%; bottom: 22px; transform: translateX(-50%);
        z-index: 1050; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: center;
        padding: 10px 16px; border-radius: 16px; max-width: calc(100vw - 24px);
        background: #0f172a; color: #fff; box-shadow: 0 14px 34px rgba(2,6,23,.45);
        animation: papeleraBarraEntra .18s ease-out;
    }
    .papelera-barra[hidden] { display: none; }
    .papelera-barra .lbl { font-weight: 600; font-size: .9rem; }
    .papelera-barra .lbl small { display: block; font-weight: 400; opacity: .7; font-size: .74rem; }
    @keyframes papeleraBarraEntra { from { opacity: 0; transform: translate(-50%, 12px); } to { opacity: 1; transform: translate(-50%, 0); } }
</style>
@endpush

@section('content')

{{-- Resumen --}}
<div class="papelera-resumen">
    <div class="papelera-resumen-icono"><i class="fas fa-trash-can"></i></div>
    <div>
        <div class="papelera-resumen-titulo">
            @if($total === 0)
                La papelera está vacía
            @else
                {{ $total }} {{ $total === 1 ? 'elemento' : 'elementos' }} en la papelera
            @endif
        </div>
        <div class="papelera-resumen-texto">
            Restaura lo que eliminaste por error o bórralo definitivamente. Lo que ya tiene historial
            (ventas, ventas) no se puede borrar para no dañar los reportes.
        </div>
    </div>
</div>

{{-- Pestañas por tipo --}}
<ul class="papelera-tabs" role="tablist">
    @foreach($tipos as $clave => $t)
        <li role="presentation">
            <a href="{{ route('papelera.index', ['tipo' => $clave]) }}"
               class="papelera-tab {{ $clave === $tipo ? 'activa' : '' }}"
               role="tab" aria-selected="{{ $clave === $tipo ? 'true' : 'false' }}">
                <i class="fas {{ $t['icono'] }}"></i>
                <span>{{ $t['plural'] }}</span>
                <span class="papelera-tab-conteo {{ $conteos[$clave] === 0 ? 'vacio' : '' }}">{{ $conteos[$clave] }}</span>
            </a>
        </li>
    @endforeach
</ul>

<div class="card">
    @if($filas->isEmpty())
        <div class="papelera-vacio">
            <div class="papelera-vacio-icono"><i class="fas {{ $meta['icono'] }}"></i></div>
            <h2 class="h5 mb-1">No hay {{ mb_strtolower($meta['plural']) }} en la papelera</h2>
            <p class="text-muted mb-3">
                Cuando elimines {{ $fem ? 'una' : 'un' }} {{ $meta['singular'] }}, aparecerá aquí para que puedas recuperarl{{ $fem ? 'a' : 'o' }}.
            </p>
            <a href="{{ route($tipo . '.index') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Ir a {{ $meta['plural'] }}
            </a>
        </div>
    @else
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h5 mb-0">
                <i class="fas {{ $meta['icono'] }} me-2 text-danger"></i>
                {{ $meta['plural'] }} eliminad{{ $fem ? 'as' : 'os' }}
            </h2>
            <small class="text-muted">
                <i class="fas fa-hand-pointer me-1"></i>
                Toca una fila para seleccionarla y actuar sobre varias a la vez
            </small>
        </div>

        @if($truncado)
            <div class="alert alert-warning m-3 mb-0 py-2">
                Se muestran los {{ $maxLote }} más recientes. Restaura o elimina algunos para ver el resto.
            </div>
        @endif

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tablaPapelera" class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="col-chk">
                                <input type="checkbox" id="chkTodos" class="form-check-input" aria-label="Seleccionar todos">
                            </th>
                            <th>Nombre</th>
                            @foreach($meta['columnas'] as $col)
                                <th>{{ $col }}</th>
                            @endforeach
                            <th>Estado</th>
                            <th>Eliminado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($filas as $f)
                            <tr data-id="{{ $f['id'] }}"
                                data-nombre="{{ $f['nombre'] }}"
                                data-bloq-eliminar="{{ $f['bloqueoEliminar'] ? 1 : 0 }}"
                                data-bloq-restaurar="{{ $f['bloqueoRestaurar'] ? 1 : 0 }}">
                                <td class="col-chk">
                                    <input type="checkbox" class="form-check-input chk-fila" value="{{ $f['id'] }}"
                                           aria-label="Seleccionar {{ $f['nombre'] }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($tipo === 'productos')
                                            @if($f['imagen'])
                                                <img src="{{ $f['imagen'] }}" alt="" class="papelera-thumb" loading="lazy">
                                            @else
                                                <span class="papelera-thumb papelera-thumb-vacio"><i class="fas fa-box"></i></span>
                                            @endif
                                        @endif
                                        <span class="papelera-nombre">{{ $f['nombre'] }}</span>
                                    </div>
                                </td>
                                @foreach($f['celdas'] as $celda)
                                    <td class="text-muted">{{ $celda }}</td>
                                @endforeach
                                <td>
                                    @if($f['bloqueoRestaurar'])
                                        <span class="papelera-chip papelera-chip-aviso">
                                            <i class="fas fa-triangle-exclamation"></i> Restaura antes su categoría
                                        </span>
                                        <span class="papelera-motivo">{{ ucfirst($f['bloqueoRestaurar']) }}</span>
                                    @endif
                                    @if($f['bloqueoEliminar'])
                                        <span class="papelera-chip papelera-chip-bloqueo">
                                            <i class="fas fa-lock"></i> Con historial
                                        </span>
                                        <span class="papelera-motivo">{{ ucfirst($f['bloqueoEliminar']) }}</span>
                                    @else
                                        <span class="papelera-chip papelera-chip-ok">
                                            <i class="fas fa-check"></i> Se puede eliminar
                                        </span>
                                    @endif
                                </td>
                                <td data-order="{{ $f['eliminado']->timestamp }}">
                                    <span title="{{ $f['eliminado']->format('d/m/Y H:i') }}">
                                        {{ $f['eliminado']->locale('es')->diffForHumans() }}
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-success btn-sm btn-restaurar"
                                            data-id="{{ $f['id'] }}"
                                            @disabled($f['bloqueoRestaurar'])
                                            title="{{ $f['bloqueoRestaurar'] ? ucfirst($f['bloqueoRestaurar']) : 'Restaurar' }}">
                                        <i class="fas fa-rotate-left me-1"></i> Restaurar
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-eliminar"
                                            data-id="{{ $f['id'] }}"
                                            @disabled($f['bloqueoEliminar'])
                                            title="{{ $f['bloqueoEliminar'] ? ucfirst($f['bloqueoEliminar']) : 'Eliminar definitivamente' }}">
                                        <i class="fas fa-trash me-1"></i> Borrar
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

{{-- Barra flotante de acciones en lote --}}
<div id="barraLote" class="papelera-barra" hidden role="toolbar" aria-label="Acciones sobre la selección">
    <span class="lbl"><span id="lblSeleccion">0 seleccionados</span><small id="lblBloqueados"></small></span>
    <button type="button" id="btnLoteRestaurar" class="btn btn-success btn-sm">
        <i class="fas fa-rotate-left me-1"></i> <span>Restaurar</span>
    </button>
    <button type="button" id="btnLoteEliminar" class="btn btn-danger btn-sm">
        <i class="fas fa-trash me-1"></i> <span>Eliminar definitivamente</span>
    </button>
    <button type="button" id="btnLimpiarSeleccion" class="btn btn-sm btn-outline-light">Cancelar</button>
</div>

@endsection

@push('modals')
{{-- Confirmación de borrado definitivo (renderizado fuera de #main para evitar conflictos de apilamiento) --}}
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarTitulo" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="modalEliminarTitulo">
                    <i class="fas fa-triangle-exclamation text-danger me-2"></i> Eliminar definitivamente
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p id="modalEliminarTexto" class="mb-2"></p>
                <ul id="modalEliminarLista" class="mb-2 ps-3"></ul>
                <p id="modalEliminarAviso" class="small text-warning mb-2" hidden></p>
                <p class="small text-muted mb-0">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarEliminar" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i> Sí, eliminar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Formulario que envía las acciones (se llena por JS) --}}
<form id="formAccion" method="POST" class="d-none no-loading">
    @csrf
    <input type="hidden" name="tipo" value="{{ $tipo }}">
    <div id="idsLote"></div>
</form>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tablaEl = document.getElementById('tablaPapelera');
    if (!tablaEl) return; // pestaña vacía: no hay nada que activar

    const URL_RESTAURAR = @json(route('papelera.restaurar'));
    const URL_ELIMINAR  = @json(route('papelera.eliminar'));

    const seleccion  = new Set();
    const barra      = document.getElementById('barraLote');
    const lblSel     = document.getElementById('lblSeleccion');
    const lblBloq    = document.getElementById('lblBloqueados');
    const btnRest    = document.getElementById('btnLoteRestaurar');
    const btnElim    = document.getElementById('btnLoteEliminar');
    const chkTodos   = document.getElementById('chkTodos');
    const form       = document.getElementById('formAccion');
    const contenedor = document.getElementById('idsLote');
    const modalEl    = document.getElementById('modalEliminar');

    // Asegurar que el modal esté directamente colgado de body para evitar colisiones con el transform CSS de #main
    if (modalEl && modalEl.parentElement !== document.body) {
        document.body.appendChild(modalEl);
    }
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    let idsPendientesEliminar = [];

    // id → metadatos de cada fila (se leen una vez del HTML original)
    const meta = {};
    tablaEl.querySelectorAll('tbody tr').forEach(function (tr) {
        meta[tr.dataset.id] = {
            nombre: tr.dataset.nombre,
            bloqEliminar: tr.dataset.bloqEliminar === '1',
            bloqRestaurar: tr.dataset.bloqRestaurar === '1',
        };
    });

    const tabla = $('#tablaPapelera').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pageLength: 10,
        order: [[{{ $colEliminado }}, 'desc']],
        columnDefs: [{ orderable: false, targets: [0, {{ $colAcciones }}] }],
    });

    function plural(n, uno, varios) { return n + ' ' + (n === 1 ? uno : varios); }

    function actualizarBarra() {
        const ids = Array.from(seleccion);
        const n = ids.length;
        barra.hidden = n === 0;
        if (n === 0) { chkTodos.checked = false; chkTodos.indeterminate = false; return; }

        const puedenRestaurar = ids.filter(id => !meta[id].bloqRestaurar).length;
        const puedenEliminar  = ids.filter(id => !meta[id].bloqEliminar).length;
        const conHistorial    = n - puedenEliminar;

        lblSel.textContent = plural(n, 'seleccionado', 'seleccionados');
        lblBloq.textContent = conHistorial > 0
            ? plural(conHistorial, 'tiene historial', 'tienen historial') + ' y no se borrará'
            : '';
        btnRest.querySelector('span').textContent = 'Restaurar (' + puedenRestaurar + ')';
        btnRest.disabled = puedenRestaurar === 0;
        btnElim.querySelector('span').textContent = 'Eliminar (' + puedenEliminar + ')';
        btnElim.disabled = puedenEliminar === 0;

        const visibles = tabla.rows({ search: 'applied' }).nodes().toArray();
        const marcadas = visibles.filter(tr => seleccion.has(tr.dataset.id)).length;
        chkTodos.checked = visibles.length > 0 && marcadas === visibles.length;
        chkTodos.indeterminate = marcadas > 0 && marcadas < visibles.length;
    }

    function marcarFila(tr, marcar) {
        const id = tr.dataset.id;
        marcar ? seleccion.add(id) : seleccion.delete(id);
        tr.classList.toggle('seleccionada', marcar);
        const chk = tr.querySelector('.chk-fila');
        if (chk) chk.checked = marcar;
    }

    // Tras paginar/ordenar/buscar, DataTables vuelve a pintar filas: se re-sincroniza el estado visual.
    tabla.on('draw', function () {
        tabla.rows().nodes().toArray().forEach(function (tr) {
            const marcada = seleccion.has(tr.dataset.id);
            tr.classList.toggle('seleccionada', marcada);
            const chk = tr.querySelector('.chk-fila');
            if (chk) chk.checked = marcada;
        });
        actualizarBarra();
    });

    $('#tablaPapelera tbody').on('change', '.chk-fila', function () {
        marcarFila(this.closest('tr'), this.checked);
        actualizarBarra();
    });

    // Tocar cualquier parte de la fila (menos botones/enlaces) la selecciona
    $('#tablaPapelera tbody').on('click', 'tr', function (e) {
        if (e.target.closest('button, a, input, label')) return;
        marcarFila(this, !seleccion.has(this.dataset.id));
        actualizarBarra();
    });

    // "Seleccionar todos": solo las filas que cumplen el filtro actual, en todas las páginas
    chkTodos.addEventListener('change', function () {
        const marcar = chkTodos.checked;
        tabla.rows({ search: 'applied' }).nodes().toArray().forEach(tr => marcarFila(tr, marcar));
        actualizarBarra();
    });

    document.getElementById('btnLimpiarSeleccion').addEventListener('click', function () {
        tabla.rows().nodes().toArray().forEach(tr => marcarFila(tr, false));
        actualizarBarra();
    });

    // ── Envío de acciones ────────────────────────────
    function enviar(url, ids, boton) {
        contenedor.innerHTML = '';
        ids.forEach(function (id) {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'ids[]'; input.value = id;
            contenedor.appendChild(input);
        });
        form.action = url;
        if (boton) {
            boton.disabled = true;
            boton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Procesando…';
        }
        form.submit();
    }

    $('#tablaPapelera tbody').on('click', '.btn-restaurar', function () {
        enviar(URL_RESTAURAR, [this.dataset.id], this);
    });

    function pedirConfirmacionEliminar(ids) {
        idsPendientesEliminar = ids;
        const borrables = ids.filter(id => !meta[id].bloqEliminar);
        const omitidos  = ids.length - borrables.length;

        document.getElementById('modalEliminarTexto').textContent =
            borrables.length === 1
                ? 'Vas a eliminar definitivamente:'
                : 'Vas a eliminar definitivamente ' + borrables.length + ' elementos:';

        const lista = document.getElementById('modalEliminarLista');
        lista.innerHTML = '';
        borrables.slice(0, 6).forEach(function (id) {
            const li = document.createElement('li');
            li.textContent = meta[id].nombre;
            lista.appendChild(li);
        });
        if (borrables.length > 6) {
            const li = document.createElement('li');
            li.className = 'text-muted';
            li.textContent = 'y ' + (borrables.length - 6) + ' más…';
            lista.appendChild(li);
        }

        const aviso = document.getElementById('modalEliminarAviso');
        aviso.hidden = omitidos === 0;
        aviso.textContent = omitidos > 0
            ? plural(omitidos, 'elemento tiene historial y se omitirá.', 'elementos tienen historial y se omitirán.')
            : '';

        modal.show();
    }

    $('#tablaPapelera tbody').on('click', '.btn-eliminar', function () {
        pedirConfirmacionEliminar([this.dataset.id]);
    });
    btnElim.addEventListener('click', function () { pedirConfirmacionEliminar(Array.from(seleccion)); });
    btnRest.addEventListener('click', function () { enviar(URL_RESTAURAR, Array.from(seleccion), btnRest); });

    document.getElementById('btnConfirmarEliminar').addEventListener('click', function () {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Eliminando…';

        // Ocultar modal y remover inmediatamente cualquier backdrop y clases bloqueantes
        modal.hide();
        document.querySelectorAll('.modal-backdrop').forEach(function (el) { el.remove(); });
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');

        // Enviar formulario
        enviar(URL_ELIMINAR, idsPendientesEliminar, btn);
    });

    // Salvavidas: al cerrarse el modal (por Cancelar, Esc o clic fuera), limpiar backdrop y estilos
    modalEl.addEventListener('hidden.bs.modal', function () {
        document.querySelectorAll('.modal-backdrop').forEach(function (el) { el.remove(); });
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    });
});
</script>
@endpush
