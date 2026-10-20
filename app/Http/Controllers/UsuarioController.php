<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UsuarioController extends Controller
{
    public function index()
    {
        $this->soloDueno();

        $usuarios = User::all();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $this->soloDueno();

        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        $this->soloDueno();

        $datos = $request->validate([
            'name'     => 'required|string|min:2|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.\,\-\']+$/',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:6|max:255|confirmed|regex:/^(?=.*[A-Za-z])(?=.*\d).+$/',
            'rol'      => 'required|in:administrador,invitado',
            'foto'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ], [
            'name.required'  => 'El nombre es obligatorio.',
            'name.min'       => 'El nombre debe tener al menos 2 caracteres.',
            'name.max'       => 'El nombre no debe superar los 255 caracteres.',
            'name.regex'     => 'El nombre solo puede contener letras, espacios y . , - \'',
            'email.required' => 'El correo es obligatorio.',
            'email.email'    => 'Ingrese un correo válido.',
            'email.max'      => 'El correo no debe superar los 255 caracteres.',
            'email.unique'   => 'Este correo ya está registrado.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 6 caracteres.',
            'password.max'       => 'La contraseña no debe superar los 255 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.regex'     => 'La contraseña debe contener al menos una letra y un número.',
            'rol.required'   => 'Seleccione un rol.',
            'rol.in'         => 'El rol seleccionado no es válido.',
            'foto.image'     => 'El archivo debe ser una imagen.',
            'foto.mimes'     => 'La foto debe ser de tipo JPG, JPEG, PNG o WEBP.',
            'foto.max'       => 'La foto no debe superar 2MB.',
        ]);

        $datos['password'] = Hash::make($request->password);

        if ($request->hasFile('foto')) {

            $rutaFoto = $request->file('foto')
                ->store('usuarios', 'public');

            $this->syncImageToPublicStorage($rutaFoto);
            $datos['foto'] = $rutaFoto;
        }

        User::create($datos);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario registrado correctamente.');
    }

    public function edit(string $id)
    {
        $this->soloDueno();

        $usuario = User::findOrFail($id);

        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, string $id)
    {
        $this->soloDueno();

        $usuario = User::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|min:2|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.\,\-\']+$/',
            'email' => 'required|email|max:255|unique:users,email,' . $usuario->id,
            'rol'   => 'required|in:administrador,invitado',
            'foto'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ], [
            'name.required'  => 'El nombre es obligatorio.',
            'name.min'       => 'El nombre debe tener al menos 2 caracteres.',
            'name.max'       => 'El nombre no debe superar los 255 caracteres.',
            'name.regex'     => 'El nombre solo puede contener letras, espacios y . , - \'',
            'email.required' => 'El correo es obligatorio.',
            'email.email'    => 'Ingrese un correo válido.',
            'email.max'      => 'El correo no debe superar los 255 caracteres.',
            'email.unique'   => 'Este correo ya está registrado.',
            'rol.required'   => 'Seleccione un rol.',
            'rol.in'         => 'El rol seleccionado no es válido.',
            'foto.image'     => 'El archivo debe ser una imagen.',
            'foto.mimes'     => 'La foto debe ser de tipo JPG, JPEG, PNG o WEBP.',
            'foto.max'       => 'La foto no debe superar 2MB.',
        ]);

        $datos = [
            'name' => $request->name,
            'email' => $request->email,
            'rol' => $request->rol,
        ];

        if ($request->filled('password')) {

            $request->validate([
                'password' => 'confirmed|min:6|max:255|regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'
            ], [
                'password.min'       => 'La contraseña debe tener al menos 6 caracteres.',
                'password.max'       => 'La contraseña no debe superar los 255 caracteres.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'password.regex'     => 'La contraseña debe contener al menos una letra y un número.',
            ]);

            $datos['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('foto')) {

            if ($usuario->foto && Storage::disk('public')->exists($usuario->foto)) {

                Storage::disk('public')->delete($usuario->foto);
                $this->removeImageFromPublicStorage($usuario->foto);
            }

            $rutaFoto = $request->file('foto')
                ->store('usuarios', 'public');

            $this->syncImageToPublicStorage($rutaFoto);
            $datos['foto'] = $rutaFoto;
        }

        $usuario->update($datos);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    private function syncImageToPublicStorage(string $path): void
    {
        $source = storage_path('app/public/' . $path);
        $target = public_path('storage/' . $path);

        if (!file_exists($source) || file_exists($target)) {
            return;
        }

        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0755, true);
        }

        copy($source, $target);
    }

    private function removeImageFromPublicStorage(?string $path): void
    {
        if (!$path) {
            return;
        }

        $target = public_path('storage/' . $path);

        if (file_exists($target)) {
            unlink($target);
        }
    }

    public function destroy(string $id)
    {
        $this->soloDueno();

        $usuario = User::findOrFail($id);

        if (auth()->id() == $usuario->id) {

            return redirect()
                ->route('usuarios.index')
                ->with('error', 'No puedes eliminar tu propio usuario.');
        }

        if ($usuario->foto && Storage::disk('public')->exists($usuario->foto)) {

            Storage::disk('public')->delete($usuario->foto);
            $this->removeImageFromPublicStorage($usuario->foto);
        }

        $usuario->delete();

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }
}