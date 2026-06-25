@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <h2>Nueva Categoría</h2>

    <form action="{{ route('categorias.store') }}" method="POST">

        @csrf

        <div class="form-group mb-3">
            <label>Nombre</label>
            <input type="text" name="nombre" class="form-control"
                     value="{{ old('nombre') }}" minlength="2" maxlength="100" required>
            
            @error('nombre')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label>Descripción</label>
            <textarea name="descripcion" class="form-control" maxlength="500">{{ old('descripcion') }}</textarea>

            @error('descripcion')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <button class="btn btn-primary">Guardar</button>
        <a href="{{ route('categorias.index') }}" class="btn btn-secondary">Volver</a>

    </form>

</div>

@endsection