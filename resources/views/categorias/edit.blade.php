@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <h2>Editar Categoría</h2>

    <form action="{{ route('categorias.update', $categoria->idCategoria) }}" method="POST">

        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label>Nombre</label>
            <input type="text" name="nombre" class="form-control"
                     value="{{ old('nombre', $categoria->nombre) }}" minlength="2" maxlength="100" required>
            
            @error('nombre')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label>Descripción</label>
            <textarea name="descripcion" class="form-control" maxlength="500">{{ old('descripcion', $categoria->descripcion) }}</textarea>

            @error('descripcion')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <button class="btn btn-warning">Actualizar</button>
        <a href="{{ route('categorias.index') }}" class="btn btn-secondary">Volver</a>

    </form>

</div>

@endsection