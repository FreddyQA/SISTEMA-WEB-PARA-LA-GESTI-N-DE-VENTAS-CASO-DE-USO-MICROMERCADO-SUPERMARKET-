<?php

namespace App\Support;

use Illuminate\Validation\Rule;

/**
 * REGLAS DE VALIDACIÓN CENTRALIZADAS
 * 
 * Propósito Académico / Proyecto de Grado:
 * Unifica las definiciones de esquemas de validación y mensajes de error
 * para evitar la duplicación de código en los 10 controladores del sistema
 * (5 controladores web de escritorio + 5 controladores de la API móvil REST).
 */
class ReglasValidacion
{
    // ── CLIENTES ─────────────────────────────────────────────────────────────
    public static function reglasCliente(?int $idCliente = null): array
    {
        return [
            'nombre'    => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.\,\-\']+$/',
            'email'     => [
                'required',
                'email',
                'max:100',
                $idCliente
                    ? Rule::unique('clientes', 'email')->ignore($idCliente, 'idCliente')
                    : 'unique:clientes,email',
            ],
            'telefono'  => 'nullable|string|min:7|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'direccion' => 'nullable|string|min:5|max:150|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.\,\-\#\/]+$/',
        ];
    }

    public static function mensajesCliente(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min'      => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max'      => 'El nombre no debe superar los 100 caracteres.',
            'nombre.regex'    => 'El nombre solo puede contener letras, espacios y . , - \'',
            'email.required'  => 'El correo es obligatorio.',
            'email.email'     => 'Ingrese un correo válido.',
            'email.max'       => 'El correo no debe superar los 100 caracteres.',
            'email.unique'    => 'El correo ya está registrado por otro cliente.',
            'telefono.min'    => 'El teléfono debe tener al menos 7 caracteres.',
            'telefono.max'    => 'El teléfono no debe superar los 20 caracteres.',
            'telefono.regex'  => 'El teléfono solo puede contener números, espacios, guiones y paréntesis.',
            'direccion.min'   => 'La dirección debe tener al menos 5 caracteres.',
            'direccion.max'   => 'La dirección no debe superar los 150 caracteres.',
            'direccion.regex' => 'La dirección solo puede contener letras, números, espacios y . , - # /',
        ];
    }

    // ── PRODUCTOS ────────────────────────────────────────────────────────────
    public static function reglasProducto(?int $idProducto = null): array
    {
        return [
            'nombre'      => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[\pL\pN\s\.\,\-\_\'\"\(\)\/\%\+\&\:\#\°\´]+$/u',
                $idProducto
                    ? Rule::unique('productos', 'nombre')->ignore($idProducto, 'idProducto')
                    : 'unique:productos,nombre',
            ],
            'precio'      => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'regex:/^\d+(\.\d{1,2})?$/'],
            'stock'       => ['required', 'integer', 'min:0', 'max:99999'],
            'idCategoria' => ['required', Rule::exists('categorias', 'idCategoria')->whereNull('deleted_at')],
        ];
    }

    public static function mensajesProducto(): array
    {
        return [
            'nombre.required'      => 'El nombre es obligatorio.',
            'nombre.min'           => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max'           => 'El nombre no debe superar los 100 caracteres.',
            'nombre.regex'         => 'El nombre solo puede contener letras, números, espacios y símbolos comunes.',
            'nombre.unique'        => 'Ya existe un producto registrado con este nombre.',
            'precio.required'      => 'El precio es obligatorio.',
            'precio.numeric'       => 'El precio debe ser un número.',
            'precio.min'           => 'El precio no puede ser negativo.',
            'precio.max'           => 'El precio excede el límite permitido.',
            'precio.regex'         => 'El precio solo puede tener hasta 2 decimales.',
            'stock.required'       => 'El stock es obligatorio.',
            'stock.integer'        => 'El stock debe ser un número entero.',
            'stock.min'            => 'El stock no puede ser negativo.',
            'stock.max'            => 'El stock no debe superar 99,999.',
            'idCategoria.required' => 'Seleccione una categoría.',
            'idCategoria.exists'   => 'La categoría seleccionada no existe o está archivada.',
        ];
    }

    // ── CATEGORÍAS ───────────────────────────────────────────────────────────
    public static function reglasCategoria(?int $idCategoria = null): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'min:2',
                'max:50',
                $idCategoria
                    ? Rule::unique('categorias', 'nombre')->ignore($idCategoria, 'idCategoria')
                    : 'unique:categorias,nombre',
            ],
        ];
    }

    public static function mensajesCategoria(): array
    {
        return [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.min'      => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max'      => 'El nombre no debe superar los 50 caracteres.',
            'nombre.unique'   => 'Ya existe una categoría con este nombre.',
        ];
    }

    // ── USUARIOS ─────────────────────────────────────────────────────────────
    public static function reglasUsuario(?int $idUsuario = null, bool $passwordRequerido = true): array
    {
        $reglas = [
            'name'  => 'required|string|min:2|max:100',
            'email' => [
                'required',
                'email',
                'max:100',
                $idUsuario
                    ? Rule::unique('users', 'email')->ignore($idUsuario, 'id')
                    : 'unique:users,email',
            ],
            'rol'   => 'required|in:administrador,invitado',
        ];

        if ($passwordRequerido) {
            $reglas['password'] = 'required|min:6|max:255|confirmed|regex:/^(?=.*[A-Za-z])(?=.*\d).+$/';
        } else {
            $reglas['password'] = 'nullable|min:6|max:255|confirmed|regex:/^(?=.*[A-Za-z])(?=.*\d).+$/';
        }

        return $reglas;
    }

    public static function mensajesUsuario(): array
    {
        return [
            'name.required'       => 'El nombre es obligatorio.',
            'name.min'            => 'El nombre debe tener al menos 2 caracteres.',
            'name.max'            => 'El nombre no debe superar los 100 caracteres.',
            'email.required'      => 'El correo electrónico es obligatorio.',
            'email.email'         => 'Ingrese un correo electrónico válido.',
            'email.unique'        => 'Este correo electrónico ya está registrado.',
            'rol.required'        => 'Seleccione un rol para el usuario.',
            'rol.in'              => 'El rol seleccionado no es válido.',
            'password.required'   => 'La contraseña es obligatoria.',
            'password.min'        => 'La contraseña debe tener al menos 6 caracteres.',
            'password.max'        => 'La contraseña no debe superar los 255 caracteres.',
            'password.confirmed'  => 'Las contraseñas no coinciden.',
            'password.regex'      => 'La contraseña debe contener al menos una letra y un número.',
        ];
    }
}
