@extends('layouts.app')

@section('title', 'Editar Usuario')
@section('page-title', 'Gestión de Usuarios')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('usuarios.index') }}">Usuarios</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')

<div class="container mt-4">

    <div class="card shadow">

        <div class="card-header bg-warning">

            <h2 class="h4">
                <i class="fa-solid fa-user-pen"></i>
                Editar Usuario
            </h2>

        </div>

        <div class="card-body">

            <form
                action="{{ route('usuarios.update', $usuario->id) }}"
                method="POST"
                enctype="multipart/form-data"
            >

                @csrf
                @method('PUT')

                <div class="mb-3">

                    <label class="form-label" for="edit_name">Nombre</label>

                    <input
                        type="text"
                        name="name"
                        id="edit_name"
                        class="form-control"
                        value="{{ $usuario->name }}"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label" for="edit_email">Email</label>

                    <input
                        type="email"
                        name="email"
                        id="edit_email"
                        class="form-control"
                        value="{{ $usuario->email }}"
                        required
                    >

                </div>

                <div class="mb-3">
                    <label class="form-label" for="edit_password">
                        Nueva Contraseña
                    </label>
                    <div class="input-group">
                        <input
                            type="password"
                            name="password"
                            id="edit_password"
                            class="form-control @error('password') is-invalid @enderror"
                            autocomplete="new-password"
                            readonly
                            onfocus="this.removeAttribute('readonly');"
                            placeholder="Dejar vacío para mantener la actual"
                        >
                        <button class="btn btn-outline-secondary" type="button" aria-label="Mostrar contraseña" aria-pressed="false" onclick="togglePassVisibility('edit_password', this)">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                        </button>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="text-muted">
                        Dejar vacío para mantener la contraseña actual
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="edit_password_confirmation">
                        Confirmar Nueva Contraseña
                    </label>
                    <div class="input-group">
                        <input
                            type="password"
                            name="password_confirmation"
                            id="edit_password_confirmation"
                            class="form-control"
                            autocomplete="new-password"
                            readonly
                            onfocus="this.removeAttribute('readonly');"
                            placeholder="Confirme la nueva contraseña"
                        >
                        <button class="btn btn-outline-secondary" type="button" aria-label="Mostrar confirmación de contraseña" aria-pressed="false" onclick="togglePassVisibility('edit_password_confirmation', this)">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">

                    <label class="form-label" for="edit_rol">Rol</label>

                    <select
                        name="rol"
                        id="edit_rol"
                        class="form-select"
                        required
                    >

                        <option value="administrador"
                            {{ $usuario->rol == 'administrador' ? 'selected' : '' }}>
                            Dueño
                        </option>

                        <option value="invitado"
                            {{ $usuario->rol == 'invitado' ? 'selected' : '' }}>
                            Empleado
                        </option>

                    </select>

                </div>

                <div class="mb-3">

                    <label class="form-label" for="edit_foto">
                        Foto de Perfil
                    </label>

                    <input
                        type="file"
                        name="foto"
                        id="edit_foto"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp"
                        onchange="previewImagen(event)"
                    >

                </div>

                <div class="mb-3">

                    @if($usuario->foto)

                        <img
                            src="{{ asset('storage/' . $usuario->foto) }}"
                            id="preview"
                            alt="{{ $usuario->name }}"
                            width="150"
                            class="rounded-circle border"
                            style="object-fit: cover; height:150px;"
                        >

                    @else

                        <img
                            id="preview"
                            alt="Vista previa de la nueva foto"
                            width="150"
                            class="rounded-circle border"
                            style="display:none; object-fit: cover; height:150px;"
                        >

                    @endif

                </div>

                <button type="submit" class="btn btn-success">

                    <i class="fa-solid fa-floppy-disk"></i>

                    Actualizar

                </button>

                <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">

                    Cancelar

                </a>

            </form>

        </div>

    </div>

</div>

<script>

function previewImagen(event)
{
    let input = event.target;
    let preview = document.getElementById('preview');

    if (input.files && input.files[0]) {

        preview.src = URL.createObjectURL(input.files[0]);

        preview.style.display = 'block';
    }
}

function togglePassVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        btn.setAttribute('aria-label', inputId.endsWith('confirmation') ? 'Ocultar confirmación de contraseña' : 'Ocultar contraseña');
        btn.setAttribute('aria-pressed', 'true');
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        btn.setAttribute('aria-label', inputId.endsWith('confirmation') ? 'Mostrar confirmación de contraseña' : 'Mostrar contraseña');
        btn.setAttribute('aria-pressed', 'false');
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

</script>

@endsection