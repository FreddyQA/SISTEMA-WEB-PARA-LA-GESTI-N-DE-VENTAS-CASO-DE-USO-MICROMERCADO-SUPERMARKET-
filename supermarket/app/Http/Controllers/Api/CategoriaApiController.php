<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoriaResource;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaApiController extends Controller
{
    private array $reglas = [
        'nombre'      => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.\,\-\_]+$/',
        'descripcion' => 'nullable|string|max:500',
    ];

    private array $mensajes = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.min'      => 'El nombre debe tener al menos 2 caracteres.',
        'nombre.max'      => 'El nombre no debe superar los 100 caracteres.',
        'nombre.regex'    => 'El nombre solo puede contener letras, números, espacios y . , - _',
        'descripcion.max' => 'La descripción no debe superar los 500 caracteres.',
    ];

    // GET /api/categorias
    public function index()
    {
        return CategoriaResource::collection(Categoria::orderBy('nombre')->get());
    }

    // POST /api/categorias (admin) — misma lógica que CategoriaController@store
    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas, $this->mensajes);

        $existente = Categoria::withTrashed()->where('nombre', $datos['nombre'])->first();

        if ($existente) {
            if ($existente->trashed()) {
                $existente->restore();
                $existente->update($datos);

                return new CategoriaResource($existente);
            }

            return response()->json([
                'message' => 'Ya existe una categoría activa con este nombre.',
                'errors'  => ['nombre' => ['Ya existe una categoría activa con este nombre.']],
            ], 422);
        }

        $categoria = Categoria::create($datos);

        return (new CategoriaResource($categoria))->response()->setStatusCode(201);
    }

    // PUT /api/categorias/{id} (admin)
    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);
        $datos = $request->validate($this->reglas, $this->mensajes);

        $existente = Categoria::withTrashed()
            ->where('nombre', $datos['nombre'])
            ->where('idCategoria', '!=', $categoria->idCategoria)
            ->first();

        if ($existente) {
            $estado = $existente->trashed() ? 'archivada en la papelera' : 'activa';

            return response()->json([
                'message' => "Ya existe otra categoría ({$estado}) con este nombre.",
                'errors'  => ['nombre' => ["Ya existe otra categoría ({$estado}) con este nombre."]],
            ], 422);
        }

        $categoria->update($datos);

        return new CategoriaResource($categoria);
    }

    // DELETE /api/categorias/{id} (admin)
    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);

        $cantProductos = $categoria->productos()->count();
        if ($cantProductos > 0) {
            return response()->json([
                'message' => "No se puede eliminar \"{$categoria->nombre}\" porque tiene {$cantProductos} producto(s) asociado(s).",
            ], 422);
        }

        $categoria->delete();

        return response()->json(['message' => 'Categoría eliminada correctamente.']);
    }
}
