<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Categoria;
use App\Services\VentaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductosExport;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;
use Picqer\Barcode\Types\TypeCode128;
use Picqer\Barcode\Renderers\PngRenderer;
use App\Http\Controllers\Concerns\SincronizaImagenPublica;
use App\Support\Sku;

class ProductoController extends Controller
{
    use SincronizaImagenPublica;

    public function __construct(private VentaService $inventario)
    {
    }

    // LISTAR PRODUCTOS
    // Se obtienen todos los productos activos con su categoría y lotes de inventario.
    // DataTables se encarga de la ordenación, filtrado en tiempo real y paginación ("Anterior" y "Siguiente") en el cliente.
    public function index()
    {
        $productos = Producto::with(['categoria', 'lotes' => fn ($query) => $query->where('cantidad', '>', 0)])
            ->orderBy('nombre')
            ->get();
        return view('productos.index', compact('productos'));
    }

    public function buscar(Request $request)
    {
        $datos = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $termino = $datos['q'];
        $productos = Producto::with('lotes')
            ->where('stock', '>', 0)
            ->where(function ($query) use ($termino) {
                $query->where('nombre', 'like', '%' . $termino . '%');
                if (preg_match('/(\d+)/', $termino, $coincidencias)) {
                    $query->orWhere('idProducto', (int) $coincidencias[1]);
                }
            })
            ->orderBy('nombre')
            ->limit(15)
            ->get()
            ->map(fn (Producto $producto) => [
                'id' => $producto->idProducto,
                'nombre' => $producto->nombre,
                'precio' => (float) $producto->precio,
                'stock' => $producto->stock_disponible,
            ])
            ->filter(fn (array $producto) => $producto['stock'] > 0)
            ->values();

        return response()->json(['data' => $productos]);
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

        // $datos queda con exactamente los campos ya validados abajo (incluida
        // 'imagen', que más adelante se sobrescribe con la ruta ya guardada en
        // disco). Usar el array que retorna validate() en vez de un
        // $request->only([...]) por separado evita que, si el día de mañana se
        // agrega o quita un campo a las reglas, alguien olvide actualizar
        // también la lista del only() y queden desincronizados.
        $datos = $request->validate([
            'nombre'      => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\p{L}\p{N}\s\.\,\-\_\'\"\(\)\/\%\+\&\:\#\°\º\ª\*\´\`]+$/u'],
            'precio'      => 'required|numeric|min:0|max:999999.99|regex:/^\d+(\.\d{1,2})?$/',
            'stock'       => 'required|integer|min:0|max:99999',
            'idCategoria' => 'required|exists:categorias,idCategoria',
            'fecha_vencimiento' => ['nullable', 'date', 'after_or_equal:today'],
            'imagen'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'nombre.required'      => 'El nombre es obligatorio.',
            'nombre.min'           => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max'           => 'El nombre no debe superar los 100 caracteres.',
            'nombre.regex'         => 'El nombre solo puede contener letras, números, espacios y símbolos comunes (. , - _ \' " ( ) / % + & : # ° ´).',
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
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento no puede ser anterior a hoy.',
            'imagen.image'         => 'El archivo debe ser una imagen.',
            'imagen.mimes'         => 'La imagen debe ser de tipo JPG, JPEG, PNG o WEBP.',
            'imagen.max'           => 'La imagen no debe superar 2MB.',
        ]);
        unset($datos['imagen']);
        $stockObjetivo = (int) $datos['stock'];
        $fechaVencimiento = $datos['fecha_vencimiento'] ?? null;
        unset($datos['stock'], $datos['fecha_vencimiento']);

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
                $this->inventario->ajustarStock($existente, $stockObjetivo, $fechaVencimiento);

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

        $producto = Producto::create([...$datos, 'stock' => 0]);
        $this->inventario->ajustarStock($producto, $stockObjetivo, $fechaVencimiento);

        return redirect()->route('productos.index')
            ->with('success', 'Producto registrado correctamente.');
    }

    // FORM EDITAR
    public function edit($id)
    {
        $this->soloDueno();
        $producto    = Producto::findOrFail($id);
        $categorias  = Categoria::all();
        $producto->load(['lotes' => fn ($query) => $query->where('cantidad', '>', 0)]);
        return view('productos.edit', compact('producto', 'categorias'));
    }

    // ACTUALIZAR
    public function update(Request $request, $id)
    {
        $this->soloDueno();

        $producto = Producto::findOrFail($id);

        $datos = $request->validate([
            'nombre'      => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\p{L}\p{N}\s\.\,\-\_\'\"\(\)\/\%\+\&\:\#\°\º\ª\*\´\`]+$/u'],
            'precio'      => 'required|numeric|min:0|max:999999.99|regex:/^\d+(\.\d{1,2})?$/',
            'stock'       => 'required|integer|min:0|max:99999',
            'idCategoria' => 'required|exists:categorias,idCategoria',
            'fecha_vencimiento' => ['nullable', 'date', 'after_or_equal:today'],
            'imagen'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'nombre.required'      => 'El nombre es obligatorio.',
            'nombre.min'           => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max'           => 'El nombre no debe superar los 100 caracteres.',
            'nombre.regex'         => 'El nombre solo puede contener letras, números, espacios y símbolos comunes (. , - _ \' " ( ) / % + & : # ° ´).',
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
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento no puede ser anterior a hoy.',
            'imagen.image'         => 'El archivo debe ser una imagen.',
            'imagen.mimes'         => 'La imagen debe ser de tipo JPG, JPEG, PNG o WEBP.',
            'imagen.max'           => 'La imagen no debe superar 2MB.',
        ]);
        $stockObjetivo = (int) $datos['stock'];
        $fechaVencimiento = $datos['fecha_vencimiento'] ?? null;
        unset($datos['stock'], $datos['fecha_vencimiento']);
        unset($datos['imagen']); // se procesa como archivo más abajo, no como campo directo

        // Verificar si otro producto (activo o en papelera) ya usa este nombre
        $existente = Producto::withTrashed()
            ->where('nombre', $datos['nombre'])
            ->where('idProducto', '!=', $producto->idProducto)
            ->first();

        if ($existente) {
            $estado = $existente->trashed() ? 'en la papelera' : 'activo';
            return back()
                ->withErrors(['nombre' => "Ya existe otro producto ($estado) con este nombre."])
                ->withInput();
        }

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
        $this->inventario->ajustarStock($producto, $stockObjetivo, $fechaVencimiento);

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    // ELIMINAR (Soft Delete)

    public function destroy($id)
    {
        $this->soloDueno();
        $producto = Producto::findOrFail($id);
        $producto->delete();

        return redirect()->route('productos.index')
            ->with('success', 'Producto enviado a la papelera.');
    }

    // CÓDIGO QR — genera y descarga un PNG con los datos del producto
    public function descargarQr($id)
    {
        $producto = Producto::findOrFail($id);
        $sku = Sku::format($producto->idProducto);

        $result = $this->generarCodigoQr($producto, $sku);

        return response($result->getString(), 200)
            ->header('Content-Type', $result->getMimeType())
            ->header('Content-Disposition', 'attachment; filename="qr_' . $sku . '.png"');
    }

    public function mostrarQr($id)
    {
        $producto = Producto::findOrFail($id);
        $result = $this->generarCodigoQr($producto, Sku::format($producto->idProducto));

        return response($result->getString(), 200)
            ->header('Content-Type', $result->getMimeType())
            ->header('Cache-Control', 'private, max-age=3600');
    }

    private function generarCodigoQr(Producto $producto, string $sku)
    {
        $contenido = json_encode([
            'sku'    => $sku,
            'nombre' => $producto->nombre,
            'precio' => (float) $producto->precio,
        ], JSON_UNESCAPED_UNICODE);

        return (new Builder(
            writer: new PngWriter(),
            data: $contenido,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 300,
            margin: 10,
        ))->build();
    }

    // CÓDIGO DE BARRAS (CODE128) — genera y descarga un PNG con el SKU del producto
    public function descargarBarcode($id)
    {
        $producto = Producto::findOrFail($id);
        $sku = Sku::format($producto->idProducto);

        $barcode  = (new TypeCode128())->getBarcode($sku);
        $renderer = new PngRenderer();
        $imagen   = $renderer->render($barcode, $barcode->getWidth() * 3, 80);

        return response($imagen, 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="barcode_' . $sku . '.png"');
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
