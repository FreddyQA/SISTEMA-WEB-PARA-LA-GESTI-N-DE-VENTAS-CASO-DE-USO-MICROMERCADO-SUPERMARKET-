@extends('layouts.app')

@section('title', 'Editar Producto')
@section('page-title', 'Editar Producto')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('productos.index') }}">Productos</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')

<div class="row justify-content-center">
<div class="col-lg-7">

    <div class="card">
        <div class="card-header bg-warning">
            <h2 class="h5 mb-0">
                <i class="fas fa-pen mr-2"></i> Editar: {{ $producto->nombre }}
            </h2>
        </div>

        <div class="card-body">

            <form action="{{ route('productos.update', $producto->idProducto) }}"
                  method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="font-weight-semibold" for="producto_nombre">
                        Nombre <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="nombre" id="producto_nombre"
                           class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre', $producto->nombre) }}"
                           placeholder="Nombre del producto">
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-semibold" for="producto_precio">
                                Precio (Bs) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Bs</span>
                                </div>
                                <input type="number" step="0.01" name="precio" id="producto_precio"
                                       class="form-control @error('precio') is-invalid @enderror"
                                       value="{{ old('precio', $producto->precio) }}">
                            </div>
                            @error('precio')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-semibold" for="producto_stock">
                                Stock <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="stock" id="producto_stock"
                                   class="form-control @error('stock') is-invalid @enderror"
                                   value="{{ old('stock', $producto->stock) }}">
                            @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-weight-semibold" for="producto_idCategoria">
                        Categoría <span class="text-danger">*</span>
                    </label>
                    <select name="idCategoria" id="producto_idCategoria"
                            class="form-control @error('idCategoria') is-invalid @enderror">
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->idCategoria }}"
                                {{ old('idCategoria', $producto->idCategoria) == $cat->idCategoria ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('idCategoria')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="font-weight-semibold" for="imagenInput">Imagen del Producto</label>

                    @if($producto->imagen)
                        <div class="mb-2">
                            <img id="preview"
                                 src="{{ asset('storage/' . $producto->imagen) }}"
                                 alt="{{ $producto->nombre }}"
                                 class="rounded shadow-sm"
                                 style="max-width:180px; max-height:180px; object-fit:cover;">
                            <br>
                            <small class="text-muted mt-1 d-block">Imagen actual</small>
                        </div>
                    @else
                        <div id="previewContainer" style="display:none;" class="mb-2">
                            <img id="preview" class="rounded shadow-sm" alt="Vista previa de la nueva imagen"
                                 style="max-width:180px; max-height:180px; object-fit:cover;">
                        </div>
                    @endif

                    <div class="custom-file">
                        <input type="file" name="imagen"
                               class="custom-file-input @error('imagen') is-invalid @enderror"
                               id="imagenInput"
                               accept=".jpg,.jpeg,.png,.webp"
                               onchange="previewImagen(event)">
                        <label class="custom-file-label" for="imagenInput">
                            Cambiar imagen...
                        </label>
                    </div>
                    <small class="text-muted">Dejar vacío para mantener la imagen actual.</small>
                    @error('imagen')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="font-weight-semibold" for="producto_fecha_vencimiento">
                        Vencimiento del lote que se agrega <small class="text-muted">(opcional)</small>
                    </label>
                    <input type="date" name="fecha_vencimiento" id="producto_fecha_vencimiento"
                           class="form-control @error('fecha_vencimiento') is-invalid @enderror"
                           value="{{ old('fecha_vencimiento') }}" min="{{ now()->toDateString() }}">
                    <small class="form-text text-muted">
                        Stock actual: {{ $producto->stock }} (disponible para venta: {{ $producto->stock_disponible }}).
                        Si aumentas el stock, la diferencia se registra como un nuevo lote; si lo reduces, se descuenta primero del lote más próximo a vencer.
                        Lote próximo: {{ $producto->lotes->first(fn ($lote) => $lote->fecha_vencimiento === null || $lote->fecha_vencimiento->gte(now()->startOfDay()))?->fecha_vencimiento?->format('d/m/Y') ?? 'sin vencimiento registrado' }}.
                    </small>
                    @error('fecha_vencimiento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-warning mr-2">
                        <i class="fas fa-floppy-disk mr-1"></i> Actualizar
                    </button>
                    <a href="{{ route('productos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Cancelar
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
function previewImagen(event) {
    const file = event.target.files[0];
    const label = document.querySelector('.custom-file-label');
    if (file) {
        label.textContent = file.name;
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('preview');
            preview.src = e.target.result;
            const container = document.getElementById('previewContainer');
            if (container) container.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endpush
