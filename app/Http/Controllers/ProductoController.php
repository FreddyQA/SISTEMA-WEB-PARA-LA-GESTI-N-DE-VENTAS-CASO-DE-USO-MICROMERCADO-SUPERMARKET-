<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Categoria;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductosExport;

class ProductoController extends Controller
{
    // LISTAR
    public function index()
    {
        $productos = Producto::with('categoria')->get();
        return view('productos.index', compact('productos'));
    }

    // FORM CREAR
    public function create()
    {
        $this->soloDueno();
        $categorias = Categoria::all();
        return view('productos.create', compact('categorias'));
    }

    // GUARDAR
    public function store(Request $request)
    {
        $this->soloDueno();

        $request->validate([
            'nombre'      => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.\,\-\_\']+$/',
            'precio'      => 'required|numeric|min:0|max:999999.99|regex:/^\d+(\.\d{1,2})?$/',
            'stock'       => 'required|integer|min:0|max:99999',
            'idCategoria' => 'required|exists:categorias,idCategoria',
            'imagen'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'nombre.required'      => 'El nombre es obligatorio.',
            'nombre.min'           => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max'           => 'El nombre no debe superar los 100 caracteres.',
            'nombre.regex'         => 'El nombre solo puede contener letras, números, espacios y . , - _ \'',
            'precio.required'      => 'El precio es obligatorio.',
            'precio.numeric'       => 'El precio debe ser un número.',
            'precio.min'           => 'El precio no puede ser negativo.',
            'precio.max'           => 'El precio no debe superar 999,999.99.',
            'precio.regex'         => 'El precio solo puede tener hasta 2 decimales.',
            'stock.required'       => 'El stock es obligatorio.',
            'stock.integer'        => 'El stock debe ser un número entero.',
            'stock.min'            => 'El stock no puede ser negativo.',
            'stock.max'            => 'El stock no debe superar 99,999.',
            'idCategoria.required' => 'Seleccione una categoría.',
            'idCategoria.exists'   => 'La categoría seleccionada no existe o no es válida.',
            'imagen.image'         => 'El archivo debe ser una imagen.',
            'imagen.mimes'         => 'La imagen debe ser de tipo JPG, JPEG, PNG o WEBP.',
            'imagen.max'           => 'La imagen no debe superar 2MB.',
        ]);

        $datos = $request->only(['nombre', 'precio', 'stock', 'idCategoria']);

        // Verificar si ya existe un producto con este nombre (activo o en papelera)
        $existente = Producto::withTrashed()
            ->where('nombre', $datos['nombre'])
            ->first();

        if ($existente) {
            if ($existente->trashed()) {
                // Si el producto estaba en la papelera, restaurarlo y actualizar datos
                if ($request->hasFile('imagen')) {
                    if ($existente->imagen && Storage::disk('public')->exists($existente->imagen)) {
                        Storage::disk('public')->delete($existente->imagen);
                        $this->removeImageFromPublicStorage($existente->imagen);
                    }
                    $rutaImagen = $request->file('imagen')->store('productos', 'public');
                    $this->syncImageToPublicStorage($rutaImagen);
                    $datos['imagen'] = $rutaImagen;
                }

                $existente->restore();
                $existente->update($datos);

                return redirect()->route('productos.index')
                    ->with('success', 'El producto "' . $existente->nombre . '" se encontraba en la papelera y ha sido restaurado con éxito.');
            }

            return back()
                ->withErrors(['nombre' => 'Ya existe un producto activo con este nombre.'])
                ->withInput();
        }

        if ($request->hasFile('imagen')) {
            $rutaImagen = $request->file('imagen')->store('productos', 'public');
            $this->syncImageToPublicStorage($rutaImagen);
            $datos['imagen'] = $rutaImagen;
        }

        Producto::create($datos);

        return redirect()->route('productos.index')
            ->with('success', 'Producto registrado correctamente.');
    }

    // FORM EDITAR
    public function edit($id)
    {
        $this->soloDueno();
        $producto   = Producto::findOrFail($id);
        $categorias = Categoria::all();
        return view('productos.edit', compact('producto', 'categorias'));
    }

    // ACTUALIZAR
    public function update(Request $request, $id)
    {
        $this->soloDueno();

        $producto = Producto::findOrFail($id);

        $request->validate([
            'nombre'      => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.\,\-\_\']+$/',
            'precio'      => 'required|numeric|min:0|max:999999.99|regex:/^\d+(\.\d{1,2})?$/',
            'stock'       => 'required|integer|min:0|max:99999',
            'idCategoria' => 'required|exists:categorias,idCategoria',
            'imagen'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'nombre.required'      => 'El nombre es obligatorio.',
            'nombre.min'           => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max'           => 'El nombre no debe superar los 100 caracteres.',
            'nombre.regex'         => 'El nombre solo puede contener letras, números, espacios y . , - _ \'',
            'precio.required'      => 'El precio es obligatorio.',
            'precio.numeric'       => 'El precio debe ser un número.',
            'precio.min'           => 'El precio no puede ser negativo.',
            'precio.max'           => 'El precio no debe superar 999,999.99.',
            'precio.regex'         => 'El precio solo puede tener hasta 2 decimales.',
            'stock.required'       => 'El stock es obligatorio.',
            'stock.integer'        => 'El stock debe ser un número entero.',
            'stock.min'            => 'El stock no puede ser negativo.',
            'stock.max'            => 'El stock no debe superar 99,999.',
            'idCategoria.required' => 'Seleccione una categoría.',
            'idCategoria.exists'   => 'La categoría seleccionada no existe o no es válida.',
            'imagen.image'         => 'El archivo debe ser una imagen.',
            'imagen.mimes'         => 'La imagen debe ser de tipo JPG, JPEG, PNG o WEBP.',
            'imagen.max'           => 'La imagen no debe superar 2MB.',
        ]);

        // Verificar si otro producto (activo o en papelera) ya usa este nombre
        $existente = Producto::withTrashed()
            ->where('nombre', $request->nombre)
            ->where('idProducto', '!=', $producto->idProducto)
            ->first();

        if ($existente) {
            $estado = $existente->trashed() ? 'en la papelera' : 'activo';
            return back()
                ->withErrors(['nombre' => "Ya existe otro producto ($estado) con este nombre."])
                ->withInput();
        }

        $datos = $request->only(['nombre', 'precio', 'stock', 'idCategoria']);

        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior
            if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
                Storage::disk('public')->delete($producto->imagen);
                $this->removeImageFromPublicStorage($producto->imagen);
            }
            $rutaImagen = $request->file('imagen')->store('productos', 'public');
            $this->syncImageToPublicStorage($rutaImagen);
            $datos['imagen'] = $rutaImagen;
        }

        $producto->update($datos);

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    // ELIMINAR (Soft Delete)
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

    public function destroy($id)
    {
        $this->soloDueno();
        $producto = Producto::findOrFail($id);
        $producto->delete();

        return redirect()->route('productos.index')
            ->with('success', 'Producto enviado a la papelera.');
    }

    // PAPELERA
    public function trashed()
    {
        $this->soloDueno();
        $productos = Producto::onlyTrashed()->with('categoria')->get();
        return view('productos.papelera', compact('productos'));
    }

    // RESTAURAR
    public function restore($id)
    {
        $this->soloDueno();
        $producto = Producto::onlyTrashed()->findOrFail($id);
        $producto->restore();

        return redirect()->route('productos.papelera')
            ->with('success', 'Producto restaurado correctamente.');
    }

    // ELIMINAR PERMANENTE
    public function forceDelete($id)
    {
        $this->soloDueno();
        $producto = Producto::onlyTrashed()->findOrFail($id);

        if ($producto->detallePedidos()->count() > 0) {
            return redirect()->route('productos.papelera')
                ->with('error', 'No se puede eliminar permanentemente el producto "' . $producto->nombre . '" porque tiene ventas registradas en el historial.');
        }

        if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
            Storage::disk('public')->delete($producto->imagen);
            $this->removeImageFromPublicStorage($producto->imagen);
        }

        $producto->forceDelete();

        return redirect()->route('productos.papelera')
            ->with('success', 'Producto eliminado permanentemente.');
    }

    // EXPORTAR PDF
    public function exportPDF()
    {
        $productos = Producto::with('categoria')->orderBy('idCategoria')->orderBy('nombre')->get();

        $totalProductos    = $productos->count();
        $totalStock        = (int) $productos->sum('stock');
        $inventarioValuado = (float) $productos->sum(fn($p) => $p->precio * $p->stock);
        $stockCritico      = $productos->where('stock', '<=', 5)->count();
        $stockAgotado      = $productos->where('stock', 0)->count();
        $stockNormal       = $productos->where('stock', '>', 5)->count();
        $precioPromedio    = $totalProductos > 0 ? $productos->avg('precio') : 0;

        // Agrupación por categoría para sección de resumen
        $porCategoria = $productos->groupBy(fn($p) => $p->categoria->nombre ?? 'Sin Categoría')
            ->map(fn($grupo) => [
                'total'   => $grupo->count(),
                'stock'   => (int) $grupo->sum('stock'),
                'valor'   => (float) $grupo->sum(fn($p) => $p->precio * $p->stock),
            ]);

        $pdf = Pdf::loadView('productos.pdf', compact(
            'productos',
            'totalProductos',
            'totalStock',
            'inventarioValuado',
            'stockCritico',
            'stockAgotado',
            'stockNormal',
            'precioPromedio',
            'porCategoria'
        ));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('productos_' . now()->format('Ymd') . '.pdf');
    }

    // EXPORTAR EXCEL
    public function exportExcel()
    {
        return Excel::download(
            new ProductosExport,
            'productos_' . now()->format('Ymd') . '.xlsx'
        );
    }
}
