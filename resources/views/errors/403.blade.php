@extends('layouts.app')

@section('title', 'Acceso Denegado')
@section('page-title', 'Error 403')

@section('content')

<div class="row justify-content-center">
<div class="col-md-6 text-center py-5">
    <div class="card shadow-sm" style="border-radius:16px;">
        <div class="card-body py-5">
            <div style="font-size:5rem;">🔒</div>
            <h2 class="font-weight-bold mt-3" style="color:#1e293b;">Acceso Denegado</h2>
            <p class="text-muted">No tienes permisos para acceder a esta sección.<br>
               Contacta al administrador si crees que es un error.</p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary mt-3">
                <i class="fas fa-home mr-2"></i> Volver al Dashboard
            </a>
        </div>
    </div>
</div>
</div>

@endsection
