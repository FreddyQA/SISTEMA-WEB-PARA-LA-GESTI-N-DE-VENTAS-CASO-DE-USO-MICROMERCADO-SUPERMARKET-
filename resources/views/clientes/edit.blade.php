@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <h2>Editar Cliente</h2>

    <form action="{{ route('clientes.update', $cliente->idCliente) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label>Nombre</label>
            <input type="text" name="nombre" class="form-control"
                     value="{{ old('nombre', $cliente->nombre) }}" minlength="2" maxlength="100" required>
            @error('nombre')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control"
                   value="{{ old('email', $cliente->email) }}" maxlength="100" required>
            @error('email')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label>Teléfono</label>
            <input type="text" name="telefono" class="form-control"
                   value="{{ old('telefono', $cliente->telefono) }}" minlength="7" maxlength="20">
            @error('telefono')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label>Dirección</label>
            <input type="text" name="direccion" class="form-control"
                   value="{{ old('direccion', $cliente->direccion) }}" minlength="5" maxlength="150">
            @error('direccion')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <button class="btn btn-warning">Actualizar</button>
        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Volver</a>

    </form>

</div>

@endsection