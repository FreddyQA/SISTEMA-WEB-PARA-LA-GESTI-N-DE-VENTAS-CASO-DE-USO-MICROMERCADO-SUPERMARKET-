@extends('layouts.app')

@section('title', 'Nuevo Producto')
@section('page-title', 'Nuevo Producto')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('productos.index') }}">Productos</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@section('content')

<div class="row justify-content-center">
<div class="col-lg-7">

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2 class="h5 mb-0">
                <i class="fas fa-plus mr-2"></i> Registrar Producto
            </h2>
        </div>

        <div class="card-body">

            <form action="{{ route('productos.store') }}" method="POST"
                  enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label class="font-weight-semibold" for="producto_nombre">
                        Nombre <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="nombre" id="producto_nombre"
                           class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre') }}"
                           placeholder="Ej: Arroz Superior 1kg">
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
                                       value="{{ old('precio') }}" placeholder="0.00">
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
                                   value="{{ old('stock') }}" placeholder="0">
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
                        <option value="">-- Seleccione categoría --</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->idCategoria }}"
                                {{ old('idCategoria') == $cat->idCategoria ? 'selected' : '' }}>
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
                    <div class="custom-file">
                        <input type="file" name="imagen"
                               class="custom-file-input @error('imagen') is-invalid @enderror"
                               id="imagenInput"
                               accept=".jpg,.jpeg,.png,.webp"
                               onchange="previewImagen(event)">
                        <label class="custom-file-label" for="imagenInput">
                            Seleccionar imagen...
                        </label>
                    </div>
                    <small class="text-muted">JPG, PNG, WEBP. Máx 2MB.</small>
                    @error('imagen')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror

                    <div id="previewContainer" class="mt-3" style="display:none;">
                        <img id="preview" class="rounded shadow-sm" alt="Vista previa de la imagen del producto"
                             style="max-width:200px; max-height:200px; object-fit:cover;">
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-weight-semibold" for="producto_fecha_vencimiento">
                        Fecha de vencimiento <small class="text-muted">(opcional)</small>
                    </label>
                    <input type="date" name="fecha_vencimiento" id="producto_fecha_vencimiento"
                           class="form-control @error('fecha_vencimiento') is-invalid @enderror"
                           value="{{ old('fecha_vencimiento') }}" min="{{ now()->toDateString() }}">
                    <small class="form-text text-muted">La cantidad inicial se registra como un lote. Si tiene fecha, las ventas descontarán primero lo más próximo a vencer (FEFO). Déjalo vacío si no vence.</small>
                    @error('fecha_vencimiento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr>

                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-floppy-disk mr-1"></i> Guardar
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
            document.getElementById('preview').src = e.target.result;
            document.getElementById('previewContainer').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endpush
