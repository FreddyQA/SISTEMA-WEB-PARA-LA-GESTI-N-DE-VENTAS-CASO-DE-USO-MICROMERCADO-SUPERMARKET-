<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClienteResource;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteApiController extends Controller
{
    // GET /api/clientes?q=
    public function index(Request $request)
    {
        $clientes = Cliente::when($request->filled('q'), function ($query) use ($request) {
                $q = $request->q;
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(20);

        return ClienteResource::collection($clientes);
    }

    // POST /api/clientes — mismas reglas que ClienteController@store (admin web)
    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre'    => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.\,\-\']+$/',
            'email'     => 'required|email|max:100',
            'telefono'  => 'nullable|string|min:7|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'direccion' => 'nullable|string|min:5|max:150|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.\,\-\#\/]+$/',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.regex'    => 'El nombre solo puede contener letras, espacios y . , - \'',
            'email.required'  => 'El correo es obligatorio.',
            'email.email'     => 'Ingrese un correo válido.',
            'telefono.regex'  => 'El teléfono solo puede contener números, espacios, guiones y paréntesis.',
            'direccion.regex' => 'La dirección solo puede contener letras, números, espacios y . , - # /',
        ]);

        $existente = Cliente::withTrashed()->where('email', $datos['email'])->first();

        if ($existente) {
            if ($existente->trashed()) {
                $existente->restore();
                $existente->update($datos);

                return new ClienteResource($existente);
            }

            return response()->json([
                'message' => 'Este correo ya está registrado por un cliente activo.',
                'errors'  => ['email' => ['Este correo ya está registrado por un cliente activo.']],
            ], 422);
        }

        $cliente = Cliente::create($datos);

        return (new ClienteResource($cliente))->response()->setStatusCode(201);
    }

    // PUT /api/clientes/{id} (admin) — misma validación que ClienteController@update
    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $datos = $request->validate([
            'nombre'    => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.\,\-\']+$/',
            'email'     => 'required|email|max:100',
            'telefono'  => 'nullable|string|min:7|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'direccion' => 'nullable|string|min:5|max:150|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.\,\-\#\/]+$/',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.regex'    => 'El nombre solo puede contener letras, espacios y . , - \'',
            'email.required'  => 'El correo es obligatorio.',
            'email.email'     => 'Ingrese un correo válido.',
            'telefono.regex'  => 'El teléfono solo puede contener números, espacios, guiones y paréntesis.',
            'direccion.regex' => 'La dirección solo puede contener letras, números, espacios y . , - # /',
        ]);

        $existente = Cliente::withTrashed()
            ->where('email', $datos['email'])
            ->where('idCliente', '!=', $cliente->idCliente)
            ->first();

        if ($existente) {
            $estado = $existente->trashed() ? 'en la papelera' : 'activo';
            $mensaje = "Ya existe otro cliente ({$estado}) con este correo electrónico.";

            return response()->json(['message' => $mensaje, 'errors' => ['email' => [$mensaje]]], 422);
        }

        $cliente->update($datos);

        return new ClienteResource($cliente);
    }

    // DELETE /api/clientes/{id} (admin)
    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);

        $cantVentas = $cliente->ventas()->count();
        if ($cantVentas > 0) {
            return response()->json([
                'message' => "No se puede eliminar al cliente \"{$cliente->nombre}\" porque tiene {$cantVentas} venta(s) asociado(s).",
            ], 422);
        }

        $cliente->delete();

        return response()->json(['message' => 'Cliente eliminado correctamente.']);
    }
}
