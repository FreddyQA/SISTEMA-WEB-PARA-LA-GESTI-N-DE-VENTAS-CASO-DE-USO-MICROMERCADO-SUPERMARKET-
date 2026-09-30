<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\SincronizaImagenPublica;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductoResource;
use App\Models\Producto;
use App\Services\VentaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoApiController extends Controller
{
    use SincronizaImagenPublica;

    public function __construct(private VentaService $inventario) {}

    // Misma validación que ProductoController@store/@update (admin web).
    // La imagen no se gestiona desde la app móvil en esta versión — queda
    // igual que estaba (al crear, sin imagen; al editar, se conserva la
    // que ya tenía); se puede asignar/cambiar desde el admin web.
    private array $reglas = [
        'nombre' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\p{L}\p{N}\s\.\,\-\_\'\"\(\)\/\%\+\&\:\#\°\º\ª\*\´\`]+$/u'],
        'precio' => 'required|numeric|min:0|max:999999.99|regex:/^\d+(\.\d{1,2})?$/',
        'stock' => 'required|integer|min:0|max:99999',
        'idCategoria' => 'required|exists:categorias,idCategoria',
        'fecha_vencimiento' => ['nullable', 'date', 'after_or_equal:today'],
    ];

    private array $mensajes = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
        'nombre.max' => 'El nombre no debe superar los 100 caracteres.',
        'nombre.regex' => 'El nombre solo puede contener letras, números, espacios y símbolos comunes (. , - _ \' " ( ) / % + & : # ° ´).',
        'precio.required' => 'El precio es obligatorio.',
        'precio.numeric' => 'El precio debe ser un número.',
        'precio.min' => 'El precio no puede ser negativo.',
        'precio.max' => 'El precio no debe superar 999,999.99.',
        'precio.regex' => 'El precio solo puede tener hasta 2 decimales.',
        'stock.required' => 'El stock es obligatorio.',
        'stock.integer' => 'El stock debe ser un número entero.',
        'stock.min' => 'El stock no puede ser negativo.',
        'stock.max' => 'El stock no debe superar 99,999.',
        'idCategoria.required' => 'Seleccione una categoría.',
        'idCategoria.exists' => 'La categoría seleccionada no existe o no es válida.',
        'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento no puede ser anterior a hoy.',
    ];

    // GET /api/productos?q=&categoria=
    public function index(Request $request)
    {
        $productos = Producto::with(['categoria', 'lotes'])
            ->when($request->filled('q'), fn ($q) => $q->where('nombre', 'like', '%'.$request->q.'%'))
            ->when($request->filled('categoria'), fn ($q) => $q->where('idCategoria', $request->categoria))
            ->orderBy('nombre')
            ->paginate(20);

        return ProductoResource::collection($productos);
    }

    // GET /api/productos/{id}
    public function show($id)
    {
        $producto = Producto::with(['categoria', 'lotes'])->findOrFail($id);

        return new ProductoResource($producto);
    }

    // GET /api/productos/codigo/{sku}  — usado por el escáner de la app móvil
    public function porCodigo(string $sku)
    {
        $skuLimpio = trim($sku);

        // Si viene en formato JSON desde el código QR generado por la web (ej: {"sku":"PRD-0001",...})
        if (str_starts_with($skuLimpio, '{')) {
            $json = json_decode($skuLimpio, true);
            if (is_array($json)) {
                $skuLimpio = trim($json['sku'] ?? $json['idProducto'] ?? $json['id'] ?? '');
            }
        }

        // Admite tanto "PRD-0001" / "PRD-1" como número entero de ID "1"
        $id = null;
        if (preg_match('/^PRD-(\d+)$/i', $skuLimpio, $m)) {
            $id = (int) $m[1];
        } elseif (ctype_digit($skuLimpio)) {
            $id = (int) $skuLimpio;
        }

        if (! $id) {
            return response()->json(['message' => 'Código no reconocido.'], 404);
        }

        $producto = Producto::with(['categoria', 'lotes'])->find($id);

        if (! $producto) {
            return response()->json(['message' => 'No existe un producto con ese código.'], 404);
        }

        return new ProductoResource($producto);
    }

    // POST /api/productos (admin) — misma lógica de restauración desde
    // papelera que ProductoController@store.
    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas, $this->mensajes);
        $stockObjetivo = (int) $datos['stock'];
        $fechaVencimiento = $datos['fecha_vencimiento'] ?? null;
        unset($datos['stock'], $datos['fecha_vencimiento']);

        $existente = Producto::withTrashed()->where('nombre', $datos['nombre'])->first();

        if ($existente) {
            if ($existente->trashed()) {
                $existente->restore();
                $existente->update($datos);
                $this->inventario->ajustarStock($existente, $stockObjetivo, $fechaVencimiento);

                return new ProductoResource($existente->load(['categoria', 'lotes']));
            }

            return response()->json([
                'message' => 'Ya existe un producto activo con este nombre.',
                'errors' => ['nombre' => ['Ya existe un producto activo con este nombre.']],
            ], 422);
        }

        $producto = Producto::create([...$datos, 'stock' => 0]);
        $this->inventario->ajustarStock($producto, $stockObjetivo, $fechaVencimiento);

        return (new ProductoResource($producto->load(['categoria', 'lotes'])))
            ->response()->setStatusCode(201);
    }

    // PUT /api/productos/{id} (admin)
    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);
        $datos = $request->validate($this->reglas, $this->mensajes);
        $stockObjetivo = (int) $datos['stock'];
        $fechaVencimiento = $datos['fecha_vencimiento'] ?? null;
        unset($datos['stock'], $datos['fecha_vencimiento']);

        $existente = Producto::withTrashed()
            ->where('nombre', $datos['nombre'])
            ->where('idProducto', '!=', $producto->idProducto)
            ->first();

        if ($existente) {
            $estado = $existente->trashed() ? 'en la papelera' : 'activo';

            return response()->json([
                'message' => "Ya existe otro producto ({$estado}) con este nombre.",
                'errors' => ['nombre' => ["Ya existe otro producto ({$estado}) con este nombre."]],
            ], 422);
        }

        $producto->update($datos);
        $this->inventario->ajustarStock($producto, $stockObjetivo, $fechaVencimiento);

        return new ProductoResource($producto->load(['categoria', 'lotes']));
    }

    // POST /api/productos/{id}/imagen (admin) — multipart con el campo "imagen".
    // Endpoint aparte del PUT JSON porque PHP no procesa archivos subidos en
    // peticiones PUT (solo en POST), y así la app puede guardar primero los
    // datos del producto y luego subir la foto sin mezclar los dos formatos.
    // POST /api/productos/{id}/imagen (admin) — Soporta multipart/form-data y base64 JSON
    public function subirImagen(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);
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
            if ($info === false || ! isset($info['mime'])) {
                return response()->json(['message' => 'El archivo proporcionado no es una imagen válida.'], 422);
            }

            $mimesPermitidos = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ];

            if (! isset($mimesPermitidos[$info['mime']])) {
                return response()->json(['message' => 'La imagen debe ser de tipo JPG, PNG o WEBP.'], 422);
            }

            $ext = $mimesPermitidos[$info['mime']];
            $nombre = 'productos/'.uniqid('prd_').'.'.$ext;
            Storage::disk('public')->put($nombre, $binario);
            $ruta = $nombre;
        } elseif ($request->hasFile('imagen')) {
            $request->validate([
                'imagen' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            ], [
                'imagen.required' => 'Selecciona una imagen.',
                'imagen.image' => 'El archivo debe ser una imagen.',
                'imagen.mimes' => 'La imagen debe ser de tipo JPG, JPEG, PNG o WEBP.',
                'imagen.max' => 'La imagen no debe superar 2MB.',
            ]);

            $ruta = $request->file('imagen')->store('productos', 'public');
        } else {
            return response()->json([
                'message' => 'No se recibió ninguna imagen (se requiere archivo "imagen" o texto "imagen_base64").',
                'errors' => ['imagen' => ['Selecciona una imagen.']],
            ], 422);
        }

        if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
            Storage::disk('public')->delete($producto->imagen);
            $this->removeImageFromPublicStorage($producto->imagen);
        }

        $this->syncImageToPublicStorage($ruta);
        $producto->update(['imagen' => $ruta]);

        return new ProductoResource($producto->load(['categoria', 'lotes']));
    }

    // DELETE /api/productos/{id}/imagen (admin)
    public function quitarImagen($id)
    {
        $producto = Producto::findOrFail($id);

        if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
            Storage::disk('public')->delete($producto->imagen);
            $this->removeImageFromPublicStorage($producto->imagen);
        }
        $producto->update(['imagen' => null]);

        return new ProductoResource($producto->load(['categoria', 'lotes']));
    }

    /**
     * DELETE /api/productos/{id} (Solo Administrador)
     *
     * Propósito y Fundamento de Ingeniería de Software:
     * Aplica eliminación lógica (Soft Delete) marcando la marca de tiempo `deleted_at`.
     * Este enfoque previene la pérdida de integridad referencial histórica con la tabla
     * `detalle_ventas`, permitiendo que las ventas pasadas sigan mostrando el nombre y precio
     * original del producto vendido. Además, permite al administrador restaurar el producto
     * desde la papelera de reciclaje web en caso de un borrado accidental.
     */
    public function destroy($id)
    {
        // 1. Localiza el producto activo por clave primaria o lanza excepción HTTP 404
        $producto = Producto::findOrFail($id);

        // 2. Ejecuta el Soft Delete (llena la columna deleted_at sin borrar la fila física de MySQL)
        $producto->delete();

        // 3. Retorna confirmación estructurada en formato JSON para el cliente móvil
        return response()->json(['message' => 'Producto enviado a la papelera.']);
    }
}
