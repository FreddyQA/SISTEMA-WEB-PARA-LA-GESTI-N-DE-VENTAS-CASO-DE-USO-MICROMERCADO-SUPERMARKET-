@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <div class="card shadow">

        <div class="card-header bg-warning">

            <h4>
                <i class="fa-solid fa-user-pen"></i>
                Editar Usuario
            </h4>

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

                    <label class="form-label">Nombre</label>

                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        value="{{ $usuario->name }}"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label">Email</label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="{{ $usuario->email }}"
                        required
                    >

                </div>

                <div class="mb-3">
                    <label class="form-label">
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
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassVisibility('edit_password', this)">
                            <i class="fas fa-eye"></i>
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
                    <label class="form-label">
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
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassVisibility('edit_password_confirmation', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">

                    <label class="form-label">Rol</label>

                    <select
                        name="rol"
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

                    <label class="form-label">
                        Foto de Perfil
                    </label>

                    <input
                        type="file"
                        name="foto"
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
                            width="150"
                            class="rounded-circle border"
                            style="object-fit: cover; height:150px;"
                        >

                    @else

                        <img
                            id="preview"
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
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

</script>

@endsection