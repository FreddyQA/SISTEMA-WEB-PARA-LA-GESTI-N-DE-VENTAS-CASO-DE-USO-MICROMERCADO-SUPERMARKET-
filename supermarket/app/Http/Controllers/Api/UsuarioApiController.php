<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\SincronizaImagenPublica;
use App\Http\Controllers\Controller;
use App\Http\Resources\UsuarioResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Gestión de usuarios y roles desde la app móvil — exclusiva del
 * administrador (ver routes/api.php), con las mismas reglas de
 * seguridad que UsuarioController (admin web): nadie puede cambiarse
 * su propio rol ni eliminar su propia cuenta, para no quedar sin forma
 * de recuperar el acceso.
 */
class UsuarioApiController extends Controller
{
    use SincronizaImagenPublica;
    // GET /api/usuarios
    public function index()
    {
        return UsuarioResource::collection(User::orderBy('name')->get());
    }

    // POST /api/usuarios
    public function store(Request $request)
    {
        $datos = $request->validate([
            'name'     => 'required|string|min:2|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.\,\-\']+$/',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:6|max:255|confirmed|regex:/^(?=.*[A-Za-z])(?=.*\d).+$/',
            'rol'      => 'required|in:administrador,invitado',
        ], [
            'name.required'      => 'El nombre es obligatorio.',
            'name.regex'         => 'El nombre solo puede contener letras, espacios y . , - \'',
            'email.required'     => 'El correo es obligatorio.',
            'email.unique'       => 'Este correo ya está registrado.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.regex'     => 'La contraseña debe contener al menos una letra y un número.',
            'rol.required'       => 'Seleccione un rol.',
        ]);

        $datos['password'] = Hash::make($datos['password']);

        $usuario = User::create($datos);

        return (new UsuarioResource($usuario))->response()->setStatusCode(201);
    }

    // PUT /api/usuarios/{id}
    public function update(Request $request, string $id)
    {
        $usuario = User::findOrFail($id);

        $datos = $request->validate([
            'name'  => 'required|string|min:2|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.\,\-\']+$/',
            'email' => 'required|email|max:255|unique:users,email,' . $usuario->id,
            'rol'   => 'required|in:administrador,invitado',
        ], [
            'name.required'  => 'El nombre es obligatorio.',
            'name.regex'     => 'El nombre solo puede contener letras, espacios y . , - \'',
            'email.required' => 'El correo es obligatorio.',
            'email.unique'   => 'Este correo ya está registrado.',
            'rol.required'   => 'Seleccione un rol.',
        ]);

        if ($request->user()->id == $usuario->id && $datos['rol'] !== $request->user()->rol) {
            return response()->json([
                'message' => 'No puedes cambiar tu propio rol. Pide a otro administrador que lo haga.',
            ], 422);
        }

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'confirmed|min:6|max:255|regex:/^(?=.*[A-Za-z])(?=.*\d).+$/',
            ], [
                'password.min'       => 'La contraseña debe tener al menos 6 caracteres.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'password.regex'     => 'La contraseña debe contener al menos una letra y un número.',
            ]);
            $datos['password'] = Hash::make($request->password);
            // Seguridad: revocar tokens móviles activos para forzar nuevo inicio de sesión
            \App\Models\ApiToken::where('idUsuario', $usuario->id)->delete();
        }

        $usuario->update($datos);

        return new UsuarioResource($usuario);
    }

    // DELETE /api/usuarios/{id}
    public function destroy(Request $request, string $id)
    {
        $usuario = User::findOrFail($id);

        if ($request->user()->id == $usuario->id) {
            return response()->json(['message' => 'No puedes eliminar tu propio usuario.'], 422);
        }

        if ($usuario->foto && Storage::disk('public')->exists($usuario->foto)) {
            Storage::disk('public')->delete($usuario->foto);
            $this->removeImageFromPublicStorage($usuario->foto);
        }

        $usuario->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente.']);
    }

    // POST /api/usuarios/{id}/imagen — Soporta multipart/form-data y base64 JSON
    public function subirImagen(Request $request, $id)
    {
        $usuario = User::findOrFail($id);
        $ruta = null;

        if ($request->filled('imagen_base64')) {
            $base64 = $request->input('imagen_base64');
            if (preg_match('/^data:image\/\w+;base64,/', $base64)) {
                $base64 = substr($base64, strpos($base64, ',') + 1);
            }

            $binario = base64_decode($base64, true);
            if ($binario === false) {
                return response()->json(['message' => 'La imagen en base64 no es válida.'], 422);
            }

            if (strlen($binario) > 2097152) {
                return response()->json(['message' => 'La imagen no debe superar 2MB.'], 422);
            }

            // Verificación estricta del contenido real de la imagen mediante sus bytes mágicos
            $info = @getimagesizefromstring($binario);
            if ($info === false || !isset($info['mime'])) {
                return response()->json(['message' => 'El archivo proporcionado no es una imagen válida.'], 422);
            }

            $mimesPermitidos = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
            ];

            if (!isset($mimesPermitidos[$info['mime']])) {
                return response()->json(['message' => 'La imagen debe ser de tipo JPG, PNG o WEBP.'], 422);
            }

            $ext = $mimesPermitidos[$info['mime']];
            $nombre = 'usuarios/' . uniqid('usr_') . '.' . $ext;
            Storage::disk('public')->put($nombre, $binario);
            $ruta = $nombre;
        } elseif ($request->hasFile('imagen')) {
            $request->validate([
                'imagen' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            ], [
                'imagen.required' => 'Selecciona una imagen.',
                'imagen.image'    => 'El archivo debe ser una imagen.',
                'imagen.mimes'    => 'La imagen debe ser de tipo JPG, JPEG, PNG o WEBP.',
                'imagen.max'      => 'La imagen no debe superar 2MB.',
            ]);

            $ruta = $request->file('imagen')->store('usuarios', 'public');
        } else {
            return response()->json([
                'message' => 'No se recibió ninguna imagen (se requiere archivo "imagen" o texto "imagen_base64").',
                'errors'  => ['imagen' => ['Selecciona una imagen.']],
            ], 422);
        }

        if ($usuario->foto && Storage::disk('public')->exists($usuario->foto)) {
            Storage::disk('public')->delete($usuario->foto);
            $this->removeImageFromPublicStorage($usuario->foto);
        }

        $this->syncImageToPublicStorage($ruta);
        $usuario->update(['foto' => $ruta]);

        return new UsuarioResource($usuario);
    }

    // DELETE /api/usuarios/{id}/imagen
    public function quitarImagen($id)
    {
        $usuario = User::findOrFail($id);

        if ($usuario->foto && Storage::disk('public')->exists($usuario->foto)) {
            Storage::disk('public')->delete($usuario->foto);
            $this->removeImageFromPublicStorage($usuario->foto);
        }
        $usuario->update(['foto' => null]);

        return new UsuarioResource($usuario);
    }
}
