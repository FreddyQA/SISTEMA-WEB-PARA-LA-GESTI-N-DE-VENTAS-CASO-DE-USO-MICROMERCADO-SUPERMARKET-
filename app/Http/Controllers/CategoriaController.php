<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;

class CategoriaController extends Controller
{
    // LISTAR
    public function index()
    {
        $categorias = Categoria::with('productos')->get();
        return view('categorias.index', compact('categorias'));
    }

    // FORM CREAR
    public function create()
    {
        return view('categorias.create');
    }

    // GUARDAR
    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre'      => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.\,\-\_]+$/',
            'descripcion' => 'nullable|string|max:500',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min'      => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max'      => 'El nombre no debe superar los 100 caracteres.',
            'nombre.regex'    => 'El nombre solo puede contener letras, números, espacios y . , - _',
            'descripcion.max' => 'La descripción no debe superar los 500 caracteres.',
        ]);

        // Verificar si la categoría ya existe (activa o eliminada por SoftDeletes)
        $existente = Categoria::withTrashed()
            ->where('nombre', $datos['nombre'])
            ->first();

        if ($existente) {
            if ($existente->trashed()) {
                // Si estaba en la papelera, restaurarla y actualizar sus datos
                $existente->restore();
                $existente->update($datos);

                return redirect()->route('categorias.index')
                    ->with('success', 'La categoría "' . $existente->nombre . '" existía previamente en la papelera y ha sido restaurada con éxito.');
            }

            return back()
                ->withErrors(['nombre' => 'Ya existe una categoría activa con este nombre.'])
                ->withInput();
        }

        Categoria::create($datos);

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría registrada correctamente');
    }

    // FORM EDITAR
    public function edit($id)
    {
        $categoria = Categoria::findOrFail($id);
        return view('categorias.edit', compact('categoria'));
    }

    // ACTUALIZAR
    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);

        $datos = $request->validate([
            'nombre'      => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.\,\-\_]+$/',
            'descripcion' => 'nullable|string|max:500',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min'      => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max'      => 'El nombre no debe superar los 100 caracteres.',
            'nombre.regex'    => 'El nombre solo puede contener letras, números, espacios y . , - _',
            'descripcion.max' => 'La descripción no debe superar los 500 caracteres.',
        ]);

        // Verificar si otra categoría (activa o archivada) ya usa este nombre
        $existente = Categoria::withTrashed()
            ->where('nombre', $datos['nombre'])
            ->where('idCategoria', '!=', $categoria->idCategoria)
            ->first();

        if ($existente) {
            $estado = $existente->trashed() ? 'archivada en la papelera' : 'activa';
            return back()
                ->withErrors(['nombre' => "Ya existe otra categoría ($estado) con este nombre."])
                ->withInput();
        }

        $categoria->update($datos);

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría actualizada correctamente');
    }

    // ELIMINAR
    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);

        // Validar que no tenga productos asociados para mantener la integridad
        $cantProductos = $categoria->productos()->count();
        if ($cantProductos > 0) {
            return redirect()->route('categorias.index')
                ->with('error', "No se puede eliminar la categoría \"{$categoria->nombre}\" porque tiene {$cantProductos} producto(s) asociado(s). Reasigne o elimine primero sus productos.");
        }

        $categoria->delete();

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría eliminada correctamente');
    }
}
