@extends('layouts.app')

@section('title', 'Productos')
@section('page-title', 'Productos')
@section('use-datatables', 'true')
@section('use-product-codes', 'true')
@section('breadcrumb')
    <li class="breadcrumb-item active">Productos</li>
@endsection

@section('content')

<div id="contenedorProductos" class="card">

    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <h2 class="h5 mb-0">
                <i class="fas fa-box mr-2 text-success"></i>
                Lista de Productos
            </h2>
            <span id="liveCatalogIndicator" class="live-indicator d-none">
                <span class="live-dot-pulse"></span>
                <span>Actualizado en vivo</span>
            </span>
        </div>

        <div class="d-flex flex-wrap gap-2 align-items-center">

           @if(auth()->user()->rol === 'administrador')

    <a href="{{ route('productos.create') }}" class="btn btn-primary btn-sm mr-1">
        Nuevo
    </a>

    <a href="{{ route('papelera.index', ['tipo' => 'productos']) }}" class="btn btn-secondary btn-sm mr-1">
        Papelera
    </a>

@endif

<a href="{{ route('productos.pdf') }}" class="btn btn-danger btn-sm mr-1" download>
    <i class="fas fa-file-pdf mr-1"></i> PDF
</a>

<a href="{{ route('productos.excel') }}" class="btn btn-success btn-sm" download>
    <i class="fas fa-file-excel mr-1"></i> Excel
</a>

        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tablaProductos" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Categoría</th>
                        <th>Vencimiento</th>
                        <th class="text-center">Códigos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $p)
                        <?php
                            $sku = \App\Support\Sku::format($p->idProducto);
                            $stockVenta = $p->stock_disponible;
                        ?>
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                @if($p->imagen)
                                    <img src="{{ asset('storage/' . $p->imagen) }}"
                                         alt="{{ $p->nombre }}"
                                         width="52" height="52"
                                         class="rounded"
                                         style="object-fit:cover;">
                                @else
                                    <div class="d-flex align-items-center justify-content-center bg-light rounded"
                                         style="width:52px;height:52px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </td>

                            <td>
                                <strong>{{ $p->nombre }}</strong>
                            </td>

                            <td>
                                <span class="badge badge-success" style="font-size:.85rem;padding:5px 10px;">
                                    Bs {{ number_format($p->precio, 2) }}
                                </span>
                            </td>

                            <td>
                                @if($stockVenta <= 5)
                                    <span class="badge badge-danger">{{ $stockVenta }}</span>
                                @elseif($stockVenta <= 20)
                                    <span class="badge badge-warning">{{ $stockVenta }}</span>
                                @else
                                    <span class="badge badge-success">{{ $stockVenta }}</span>
                                @endif
                                @if($p->stock > $stockVenta)
                                    <div class="small text-danger mt-1">{{ $p->stock - $stockVenta }} vencidas</div>
                                @endif
                            </td>

                            <td>
                                <span class="badge badge-light text-dark border">
                                    {{ $p->categoria->nombre ?? '-' }}
                                </span>
                            </td>

                            <td>
                                @php($loteProximo = $p->lotes->first())
                                @if($loteProximo?->fecha_vencimiento)
                                    @php($diasVence = now()->startOfDay()->diffInDays($loteProximo->fecha_vencimiento, false))
                                    <span class="badge {{ $diasVence < 0 ? 'badge-danger' : ($diasVence <= 30 ? 'badge-warning' : 'badge-light') }}">
                                        {{ $diasVence < 0 ? 'Vencido' : 'Vence' }} {{ $loteProximo->fecha_vencimiento->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-muted small">{{ $p->lotes->isEmpty() ? '—' : 'No perecedero' }}</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <button type="button"
                                        class="btn btn-outline-primary btn-sm btn-label-window shadow-xs d-inline-flex align-items-center gap-1 mb-1"
                                        title="Abrir e imprimir etiqueta (Código de Barras y QR)"
                                        data-id="{{ $p->idProducto }}"
                                        data-sku="{{ $sku }}"
                                        data-nombre="{{ $p->nombre }}"
                                        data-precio="{{ $p->precio }}"
                                        data-stock="{{ $stockVenta }}"
                                        data-categoria="{{ $p->categoria->nombre ?? '-' }}"
                                        data-qr-image="{{ route('productos.qr.image', $p->idProducto) }}">
                                    <i class="fas fa-barcode"></i>
                                    <i class="fas fa-qrcode"></i>
                                    <span>Etiqueta</span>
                                </button>
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('productos.qr', $p->idProducto) }}"
                                       class="btn btn-outline-dark btn-sm" title="Descargar código QR" aria-label="Descargar código QR de {{ $p->nombre }}">
                                        <i class="fas fa-qrcode"></i>
                                    </a>
                                    <a href="{{ route('productos.barcode', $p->idProducto) }}"
                                       class="btn btn-outline-dark btn-sm" title="Descargar código de barras" aria-label="Descargar código de barras de {{ $p->nombre }}">
                                        <i class="fas fa-barcode"></i>
                                    </a>
                                </div>
                            </td>

                            <td>
                                @if(auth()->user()->rol === 'administrador')
                                    <a href="{{ route('productos.edit', $p->idProducto) }}"
                                       class="btn btn-warning btn-sm mr-1" aria-label="Editar {{ $p->nombre }}">
                                        <i class="fas fa-pen" aria-hidden="true"></i>
                                    </a>
                                    <form action="{{ route('productos.destroy', $p->idProducto) }}"
                                          method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm"
                                                aria-label="Eliminar {{ $p->nombre }}"
                                                onclick="return confirm('¿Enviar a papelera?')">
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
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-box-open fa-2x mb-2 d-block"></i>
                                No hay productos registrados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection

@push('styles')
<style>
    /* Estilos de la tabla de productos */
    #tablaProductos th, #tablaProductos td {
        vertical-align: middle;
    }
    .btn-label-window {
        transition: all .2s ease;
    }
    .btn-label-window:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(79,70,229,.25) !important;
    }
    .live-dot-pulse {
        width: 8px;
        height: 8px;
        background-color: #10b981;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulseLiveProd 1.8s infinite;
    }
    @keyframes pulseLiveProd {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .live-indicator {
        background: rgba(16, 185, 129, 0.12);
        color: #065f46;
        border: 1px solid rgba(16, 185, 129, 0.35);
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.76rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    body.dark-mode .live-indicator {
        background: rgba(16, 185, 129, 0.2);
        color: #6ee7b7;
        border-color: rgba(16, 185, 129, 0.4);
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function () {
        function inicializarDataTable() {
            if (!$.fn.DataTable) return;
            const filas = $('#tablaProductos tbody tr');
            if (filas.length === 0 || (filas.length === 1 && filas.find('td').hasClass('text-center'))) {
                return;
            }
            if ($.fn.DataTable.isDataTable('#tablaProductos')) {
                $('#tablaProductos').DataTable().destroy();
            }
            $('#tablaProductos').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                    paginate: {
                        previous: "Anterior",
                        next: "Siguiente"
                    }
                },
                pageLength: 10,
                order: [[0, 'desc']],
                columnDefs: [{ orderable: false, targets: [1, 7, 8] }],
                drawCallback: () => {
                    try {
                        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                            bootstrap.Tooltip.getOrCreateInstance(el);
                        });
                    } catch (e) {}
                }
            });
        }

        inicializarDataTable();

        // ── AUTO-ACTUALIZACIÓN EN TIEMPO REAL SIN REFRESCAR ────────────────
        let isRefreshingProd = false;

        async function recargarProductosSinRefrescar() {
            if (isRefreshingProd) return;
            isRefreshingProd = true;
            try {
                const resp = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!resp.ok) return;
                const htmlTexto = await resp.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(htmlTexto, 'text/html');

                const nuevoCard = doc.getElementById('contenedorProductos');
                const actualCard = document.getElementById('contenedorProductos');

                if (nuevoCard && actualCard) {
                    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tablaProductos')) {
                        $('#tablaProductos').DataTable().destroy();
                    }

                    actualCard.innerHTML = nuevoCard.innerHTML;
                    inicializarDataTable();

                    // Muestra el indicador de sincronización en vivo
                    const ind = document.getElementById('liveCatalogIndicator');
                    if (ind) {
                        ind.classList.remove('d-none');
                        setTimeout(() => ind.classList.add('d-none'), 4000);
                    }
                }
            } catch (err) {
                console.warn('Error al auto-actualizar catálogo:', err);
            } finally {
                isRefreshingProd = false;
            }
        }

        // Escucha el evento global emitido por layouts/app.blade.php
        window.addEventListener('sistema:sync', function (e) {
            if (e.detail && (e.detail.productos || e.detail.categorias)) {
                recargarProductosSinRefrescar();
            }
        });

        // Abrir etiqueta en ventana emergente directamente
        $(document).on('click', '#tablaProductos .btn-label-window', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const btn = this;
            abrirEtiquetaDirectaEnVentana({
                id: btn.dataset.id,
                sku: btn.dataset.sku,
                nombre: btn.dataset.nombre,
                precio: btn.dataset.precio,
                stock: btn.dataset.stock,
                categoria: btn.dataset.categoria,
                qrImage: btn.dataset.qrImage,
            });
        });
    });

    // Función directa, ultra ligera, sin bloqueo de interfaz y con QR + Código de barras garantizados
    function abrirEtiquetaDirectaEnVentana(p) {
        const precioFormatted = 'Bs ' + Number(p.precio || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const escH = function(s) {
            return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
        };
        const nombreEsc = escH(p.nombre);
        const skuEsc = escH(p.sku);
        const catEsc = escH(p.categoria || '-');
        const stockEsc = escH(String(p.stock ?? 0));

        let qrString;
        try {
            qrString = typeof p.qr === 'string' ? p.qr : JSON.stringify(p.qr || { sku: p.sku, nombre: p.nombre, precio: p.precio });
        } catch(e) {
            qrString = JSON.stringify({ sku: p.sku, nombre: p.nombre, precio: p.precio });
        }

        const popup = window.open('', '_blank', 'width=620,height=780,scrollbars=yes,resizable=yes');
        if (!popup) {
            alert('El navegador bloqueó la ventana emergente. Por favor habilita las ventanas emergentes en la barra de direcciones.');
            return;
        }

        const jsBarcodeUrl = "{{ asset('vendor/JsBarcode.all.min.js') }}";
        const qrCodeUrl = "{{ asset('vendor/qrcode.min.js') }}";
        const docHtml = `<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Etiqueta — ${nombreEsc}</title>
<script src="${jsBarcodeUrl}"><\/script>
<script src="${qrCodeUrl}"><\/script>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background: #f1f5f9;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #0f172a;
    padding: 24px 16px;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
  .toolbar {
    max-width: 440px;
    margin: 0 auto 20px auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
    padding: 12px 18px;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(15,23,42,0.08);
  }
  .copies-ctrl {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: .88rem;
    font-weight: 700;
    color: #334155;
  }
  .copies-btn {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .15s;
  }
  .copies-btn:hover { background: #e2e8f0; }
  .copies-input {
    width: 44px;
    text-align: center;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 4px;
    font-weight: 700;
  }
  .actions { display: flex; gap: 8px; }
  .btn {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: .85rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: all .15s;
  }
  .btn-print {
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(79,70,229,0.3);
  }
  .btn-print:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(79,70,229,0.4); }
  .btn-close {
    background: #e2e8f0;
    color: #334155;
  }
  .btn-close:hover { background: #cbd5e1; }
  #labelsContainer {
    display: flex;
    flex-direction: column;
    gap: 18px;
    align-items: center;
  }
  .label-sticker {
    width: 100%;
    max-width: 440px;
    background: #ffffff;
    border: 2px dashed #cbd5e1;
    border-radius: 16px;
    padding: 20px 22px;
    box-shadow: 0 10px 25px rgba(15,23,42,0.06);
    page-break-inside: avoid;
    break-inside: avoid;
  }
  .label-header {
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 8px;
    margin-bottom: 12px;
  }
  .label-brand {
    font-size: .7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #4f46e5;
  }
  .label-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.25;
    margin-top: 3px;
  }
  .label-meta-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
  }
  .label-price {
    font-size: 1.45rem;
    font-weight: 900;
    color: #059669;
  }
  .label-cat {
    background: #f1f5f9;
    color: #475569;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: .75rem;
    font-weight: 700;
  }
  .label-codes-grid {
    display: grid;
    grid-template-columns: 1.3fr 1fr;
    gap: 12px;
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px;
  }
  .label-barcode-box {
    text-align: center;
  }
  .label-barcode-box svg {
    max-width: 100%;
    height: 52px;
  }
  .label-barcode-txt {
    font-family: monospace;
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: 2px;
    color: #1e293b;
    margin-top: 3px;
  }
  .label-qr-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }
  .label-qr-target {
    width: 104px;
    height: 104px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .label-qr-target img, .label-qr-target canvas {
    width: 100% !important;
    height: 100% !important;
    display: block;
    border-radius: 4px;
  }
  .label-footer-row {
    display: flex;
    justify-content: space-between;
    margin-top: 12px;
    padding-top: 8px;
    border-top: 1px dashed #cbd5e1;
    font-size: .72rem;
    color: #64748b;
    font-weight: 600;
  }
  @media print {
    body { background: #fff; padding: 0; }
    .toolbar { display: none !important; }
    .label-sticker { box-shadow: none !important; border: 1px solid #000 !important; margin: 0 0 16px 0 !important; }
  }
</style>
</head>
<body>
  <div class="toolbar">
    <div class="copies-ctrl">
      <span>Copias:</span>
      <button class="copies-btn" onclick="cambiarCopias(-1)">-</button>
      <input type="number" id="numCopias" class="copies-input" value="1" min="1" max="50" onchange="actualizarCopias()">
      <button class="copies-btn" onclick="cambiarCopias(1)">+</button>
    </div>
    <div class="actions">
      <button class="btn btn-print" onclick="window.print()">🖨 Imprimir</button>
      <button class="btn btn-close" onclick="window.close()">✕ Cerrar</button>
    </div>
  </div>

  <div id="labelsContainer"></div>

  <script>
    const productoData = {
      sku: ${JSON.stringify(p.sku)},
      nombre: ${JSON.stringify(p.nombre)},
      precioTxt: ${JSON.stringify(precioFormatted)},
      cat: ${JSON.stringify(p.categoria || '-')},
      stock: ${JSON.stringify(p.stock ?? 0)},
      qr: ${JSON.stringify(qrString)},
      qrImage: ${JSON.stringify(p.qrImage)}
    };

    function renderLabels(num) {
      const container = document.getElementById('labelsContainer');
      container.innerHTML = '';
      for (let i = 0; i < num; i++) {
        const div = document.createElement('div');
        div.className = 'label-sticker';
        div.innerHTML = \`
          <div class="label-header">
            <div class="label-brand">SUPERMARKET &bull; ETIQUETA OFICIAL</div>
            <div class="label-title">\${productoData.nombre}</div>
          </div>
          <div class="label-meta-row">
            <div class="label-price">\${productoData.precioTxt}</div>
            <div class="label-cat">\${productoData.cat}</div>
          </div>
          <div class="label-codes-grid">
            <div class="label-barcode-box">
              <svg id="bc_\${i}"></svg>
              <div class="label-barcode-txt">\${productoData.sku}</div>
            </div>
            <div class="label-qr-box">
              <div class="label-qr-target" id="qr_\${i}"></div>
            </div>
          </div>
          <div class="label-footer-row">
            <span>SKU: \${productoData.sku}</span>
            <span>Stock: \${productoData.stock} uds.</span>
          </div>
        \`;
        container.appendChild(div);

        try {
          if (window.JsBarcode) {
            JsBarcode('#bc_' + i, productoData.sku, {
              format: 'CODE128',
              lineColor: '#0f172a',
              width: 1.8,
              height: 48,
              displayValue: false,
              margin: 0,
              background: 'transparent'
            });
          }
        } catch (e) {}

        const qrEl = document.getElementById('qr_' + i);
        if (qrEl) {
          qrEl.innerHTML = '';
          try {
            if (window.QRCode) {
              new QRCode(qrEl, {
                text: productoData.sku,
                width: 104,
                height: 104,
                colorDark: '#0f172a',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
              });
            } else if (productoData.qrImage) {
              const img = document.createElement('img');
              img.src = productoData.qrImage;
              img.alt = 'Código QR de ' + productoData.sku;
              qrEl.appendChild(img);
            }
          } catch (e) {
            if (productoData.qrImage) {
              const img = document.createElement('img');
              img.src = productoData.qrImage;
              img.alt = 'Código QR de ' + productoData.sku;
              qrEl.appendChild(img);
            }
          }
        }
      }
    }

    function cambiarCopias(delta) {
      const input = document.getElementById('numCopias');
      let val = Math.max(1, Math.min(50, (parseInt(input.value) || 1) + delta));
      input.value = val;
      renderLabels(val);
    }

    function actualizarCopias() {
      const input = document.getElementById('numCopias');
      let val = Math.max(1, Math.min(50, parseInt(input.value) || 1));
      input.value = val;
      renderLabels(val);
    }

    function iniciarEtiqueta() {
      renderLabels(1);
    }
    if (document.readyState === 'complete') {
      iniciarEtiqueta();
    } else {
      window.addEventListener('load', iniciarEtiqueta);
    }
  <\/script>
</body>
</html>`;

        popup.document.open();
        popup.document.write(docHtml);
        popup.document.close();
        popup.focus();
    }
</script>
@endpush
