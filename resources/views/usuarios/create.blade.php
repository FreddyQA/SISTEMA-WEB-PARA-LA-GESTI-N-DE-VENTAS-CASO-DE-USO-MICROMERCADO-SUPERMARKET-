@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fa-solid fa-user-plus mr-2"></i>
                Nuevo Usuario
            </h4>
        </div>

        <div class="card-body">

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Por favor corrige los siguientes errores:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form
                action="{{ route('usuarios.store') }}"
                method="POST"
                enctype="multipart/form-data"
                autocomplete="off"
                id="formNuevoUsuario"
            >

                @csrf

                {{-- Trampa señuelo para navegadores: absorbe el autocompletado automático de contraseñas guardadas --}}
                <div style="position: absolute; left: -9999px; top: -9999px; opacity: 0; pointer-events: none;" aria-hidden="true">
                    <input type="text" name="decoy_username" tabindex="-1" autocomplete="username" value="">
                    <input type="password" name="decoy_password" tabindex="-1" autocomplete="current-password" value="">
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-semibold">
                        Nombre Completo <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        placeholder="Ej: Carlos Mendoza"
                        autocomplete="off"
                        required
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-semibold">
                        Correo Electrónico <span class="text-danger">*</span>
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="user_email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        placeholder="correo@ejemplo.com"
                        autocomplete="off"
                        readonly
                        onfocus="this.removeAttribute('readonly');"
                        required
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-semibold">
                        Contraseña <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input
                            type="password"
                            name="password"
                            id="user_password"
                            class="form-control @error('password') is-invalid @enderror"
                            autocomplete="new-password"
                            readonly
                            onfocus="this.removeAttribute('readonly');"
                            placeholder="Mínimo 6 caracteres con letras y números"
                            required
                        >
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassVisibility('user_password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-semibold">
                        Confirmar Contraseña <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input
                            type="password"
                            name="password_confirmation"
                            id="user_password_confirmation"
                            class="form-control"
                            autocomplete="new-password"
                            readonly
                            onfocus="this.removeAttribute('readonly');"
                            placeholder="Repita la contraseña"
                            required
                        >
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassVisibility('user_password_confirmation', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-semibold">
                        Rol en el Sistema <span class="text-danger">*</span>
                    </label>
                    <select
                        name="rol"
                        class="form-select @error('rol') is-invalid @enderror"
                        required
                    >
                        <option value="">-- Seleccione un Rol --</option>
                        <option value="administrador" {{ old('rol') === 'administrador' ? 'selected' : '' }}>
                            Administrador (Acceso total)
                        </option>
                        <option value="invitado" {{ old('rol') === 'invitado' ? 'selected' : '' }}>
                            Invitado (Solo consulta)
                        </option>
                    </select>
                    @error('rol')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-semibold">Foto de Perfil</label>
                    <input
                        type="file"
                        name="foto"
                        class="form-control @error('foto') is-invalid @enderror"
                        accept=".jpg,.jpeg,.png,.webp"
                        onchange="previewImagen(event)"
                    >
                    @error('foto')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3" id="preview-container" style="display: none;">
                    <img
                        id="preview"
                        width="120"
                        height="120"
                        class="rounded-circle border shadow-sm"
                        style="object-fit: cover;"
                    >
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Guardar
                    </button>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>

            </form>

        </div>

    </div>

</div>

@push('scripts')
<script>
function previewImagen(event) {
    const input = event.target;
    const preview = document.getElementById('preview');
    const container = document.getElementById('preview-container');

    if (input.files && input.files[0]) {
        preview.src = URL.createObjectURL(input.files[0]);
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
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

// Limpiar inputs forzados por autofill del navegador al abrir la vista sin errores
document.addEventListener('DOMContentLoaded', function() {
    @if(!$errors->any())
        setTimeout(function() {
            const email = document.getElementById('user_email');
            const pass = document.getElementById('user_password');
            const passConf = document.getElementById('user_password_confirmation');
            if (email) email.value = '';
            if (pass) pass.value = '';
            if (passConf) passConf.value = '';
        }, 120);
    @endif
});
</script>
@endpush

@endsection