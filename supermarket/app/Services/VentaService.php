<?php

namespace App\Services;

use App\Models\DetalleVenta;
use App\Models\LoteInventario;
use App\Models\Venta;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * SERVICIO CENTRAL DE GESTIÓN DE VENTAS Y CONTROL DE INVENTARIO (VentaService)
 * 
 * Propósito Académico / Proyecto de Grado:
 * Implementa el patrón "Service Layer" (Capa de Servicio) de la arquitectura empresarial.
 * Desacopla la lógica de negocio de los controladores HTTP, permitiendo que tanto el
 * panel web de escritorio (VentaController) como la API REST móvil (Api\VentaApiController)
 * compartan exactamente las mismas reglas de negocio sin duplicación de código (Principio DRY).
 * 
 * Conceptos Clave para la Defensa ante el Jurado:
 * 1. Atomicidad Transaccional (ACID): Todas las inserciones y deducciones de stock se ejecutan
 *    dentro de un bloque `DB::transaction()`. Si ocurre un fallo (ej: corte de luz o stock insuficiente),
 *    la base de datos ejecuta un Rollback automático y no deja datos corruptos o a medio guardar.
 * 2. Prevención de Condiciones de Carrera (Race Conditions): Emplea bloqueos pesimistas
 *    `->lockForUpdate()` sobre las filas de la tabla `productos`. Esto previene que dos cajeros
 *    simultáneos (uno en la web y otro en la app móvil) vendan la última unidad al mismo milisegundo.
 * 3. Precisión Numérica Monetaria: Emplea la extensión BCMath de PHP (`bcmul`, `bcadd`) con 2
 *    decimales en lugar de operaciones aritméticas flotantes estándar (`*`, `+`), eliminando
 *    los errores de redondeo binario propios de los números IEEE 754 de punto flotante.
 */
class VentaService
{
    /**
     * Registra una nueva transacción de venta de forma atómica.
     *
     * @param  array{idCliente:int, fecha:string, items:array<int, array{idProducto:int, cantidad:int}>} $datos
     * @param  int|null $idUsuario ID del usuario autenticado que emitió la venta (cajero/vendedor)
     * @return Venta Instancia de la venta recién creada con su total definitivo calculado
     * @throws ValidationException Si el stock en almacén es inferior a la cantidad solicitada
     */
    public function crear(array $datos, ?int $idUsuario): Venta
    {
        return DB::transaction(function () use ($datos, $idUsuario) {
            // Calcula el siguiente número de folio secuencial
            $siguienteNumero = (Venta::max('numero') ?? 0) + 1;

            // Paso 1: Crea la cabecera de la venta con total inicial en 0.
            $venta = Venta::create([
                'idCliente' => $datos['idCliente'],
                'idUsuario' => $idUsuario,
                'fecha'     => $datos['fecha'],
                'estado'    => 'completado',
                'total'     => 0,
                'numero'    => $siguienteNumero,
            ]);

            // Paso 2: Itera los productos, valida stock disponible, descuenta inventario y crea líneas de detalle
            $total = $this->registrarDetalles($venta, $datos['items']);

            // Paso 3: Actualiza el total real consolidado en el encabezado
            $venta->update(['total' => $total]);

            return $venta;
        });
    }

    /**
     * Modifica una venta existente (edición desde el administrador web).
     *
     * @param  int   $idVenta Identificador de la venta a editar
     * @param  array{idCliente:int, fecha:string, estado:string, items:array<int, array{idProducto:int, cantidad:int}>} $datos
     * @return Venta Venta actualizada
     */
    public function actualizar(int $idVenta, array $datos): Venta
    {
        return DB::transaction(function () use ($datos, $idVenta) {
            // Bloquea la venta para impedir cambios simultáneos durante la edición.
            $venta = Venta::with('detalles')->lockForUpdate()->findOrFail($idVenta);

            // Mapeo de precios históricos: conserva el precio pactado de cada ítem existente
            $preciosHistoricos = $venta->detalles->pluck('precio_unitario', 'idProducto')->toArray();

            $estabaCompletado = $venta->estado === 'completado';

            // Si la venta anterior había descontado stock, se restituye primero al inventario.
            if ($estabaCompletado) {
                foreach ($venta->detalles as $detalleAnterior) {
                    $this->restaurarDetalle($detalleAnterior);
                }
            }

            // Elimina las líneas de detalle antiguas para reemplazarlas limpiamente
            $venta->detalles()->delete();

            // Solo descuenta stock si el nuevo estado de la venta permanece en 'completado'.
            $descontarStock = $datos['estado'] === 'completado';

            // Registra las nuevas líneas conservando precios históricos y calcula el nuevo monto total
            $total = $this->registrarDetalles($venta, $datos['items'], $descontarStock, $preciosHistoricos);

            // Actualiza los datos de cabecera de la venta.
            $venta->update([
                'idCliente' => $datos['idCliente'],
                'fecha'     => $datos['fecha'],
                'estado'    => $datos['estado'],
                'total'     => $total,
            ]);

            return $venta;
        });
    }

    /**
     * Elimina lógicamente una venta y renumera las restantes.
     * Si la venta estaba completada, devuelve las unidades vendidas al stock de almacén.
     * Después del borrado, recalcula los números de folio de todas las ventas
     * para que la secuencia quede sin huecos (1, 2, 3...).
     *
     * @param  int $idVenta Identificador de la venta
     */
    public function eliminar(int $idVenta): void
    {
        DB::transaction(function () use ($idVenta) {
            $venta = Venta::with('detalles')->lockForUpdate()->findOrFail($idVenta);

            // Restituye el stock al almacén si la venta estaba completada.
            if ($venta->estado === 'completado') {
                foreach ($venta->detalles as $detalle) {
                    $this->restaurarDetalle($detalle);
                }
            }

            // Aplica eliminación lógica (marca deleted_at)
            $venta->delete();

            // Renumera secuencialmente todas las ventas activas restantes.
            $this->renumerarVentas();
        });
    }

    /**
     * Renumera secuencialmente todas las ventas activas.
     * Garantiza que la columna `numero` (folio visible) sea una
     * secuencia continua sin huecos: 1, 2, 3, ...
     */
    public function renumerarVentas(): void
    {
        $ventas = Venta::orderBy('idVenta')->pluck('idVenta');

        foreach ($ventas as $index => $id) {
            DB::table('ventas')
                ->where('idVenta', $id)
                ->update(['numero' => $index + 1]);
        }
    }

    /**
     * Función privada interna encargada de:
     * 1. Bloquear pesimistamente cada fila de producto (`lockForUpdate()`).
     * 2. Verificar que haya suficiente inventario disponible.
     * 3. Conservar el precio unitario histórico si la línea ya existía en la venta.
     * 4. Calcular con alta precisión monetaria (BCMath) el subtotal de cada ítem.
     * 5. Crear los registros en la tabla `detalle_ventas`.
     * 6. Decrementar el stock en almacén.
     *
     * @param  Venta $venta Venta a la que pertenecen las líneas
     * @param  array<int, array{idProducto:int, cantidad:int}> $items Colección de ítems a registrar
     * @param  bool   $descontarStock Si debe reducir el stock en inventario (true) o solo recalcular (false)
     * @param  array<int, string|float> $preciosHistoricos Mapa de idProducto => precio histórico
     * @return string Total de la venta formateado en dos decimales (ej: "250.50")
     * @throws ValidationException Si el producto no tiene stock suficiente
     */
    private function registrarDetalles(Venta $venta, array $items, bool $descontarStock = true, array $preciosHistoricos = []): string
    {
        $total = '0';

        usort($items, fn (array $a, array $b) => $a['idProducto'] <=> $b['idProducto']);

        foreach ($items as $item) {
            // Bloqueo pesimista a nivel de fila (ROW-LEVEL LOCK) soportando productos archivados
            $producto = Producto::withTrashed()
                ->where('idProducto', $item['idProducto'])
                ->lockForUpdate()
                ->firstOrFail();

            // Si el producto está en papelera y es una nueva adición a la venta, no permitirlo
            if ($producto->trashed() && !isset($preciosHistoricos[$item['idProducto']])) {
                throw ValidationException::withMessages([
                    'items' => "El producto \"{$producto->nombre}\" está en la papelera y no se puede agregar a una venta.",
                ]);
            }

            $this->asegurarLoteLegado($producto);
            $lotes = collect();

            if ($descontarStock) {
                $hoy = now()->toDateString();
                $lotes = $producto->lotes()
                    ->where('cantidad', '>', 0)
                    ->where(fn ($query) => $query->whereNull('fecha_vencimiento')->orWhereDate('fecha_vencimiento', '>=', $hoy))
                    ->orderByRaw('fecha_vencimiento IS NULL')
                    ->orderBy('fecha_vencimiento')
                    ->orderBy('idLote')
                    ->lockForUpdate()
                    ->get();
            }

            $stockDisponible = (int) $lotes->sum('cantidad');
            if ($descontarStock && $stockDisponible < $item['cantidad']) {
                throw ValidationException::withMessages([
                    'items' => "Stock vigente insuficiente para \"{$producto->nombre}\" (disponible: {$stockDisponible}, solicitado: {$item['cantidad']}).",
                ]);
            }

            // Preservación del precio unitario histórico o asignación del precio de catálogo
            $precioUnitario = isset($preciosHistoricos[$item['idProducto']])
                ? (string) $preciosHistoricos[$item['idProducto']]
                : (string) $producto->precio;

            // Multiplicación exacta de precio unitario por cantidad con 2 decimales usando BCMath
            $subtotal = bcmul($precioUnitario, (string) $item['cantidad'], 2);

            // Registro persistente de la línea de detalle
            $detalle = DetalleVenta::create([
                'idVenta'        => $venta->idVenta,
                'idProducto'      => $producto->idProducto,
                'cantidad'        => $item['cantidad'],
                'precio_unitario' => $precioUnitario,
                'subtotal'        => $subtotal,
            ]);

            if ($descontarStock) {
                $pendiente = $item['cantidad'];
                foreach ($lotes as $lote) {
                    if ($pendiente === 0) {
                        break;
                    }
                    $descontar = min($pendiente, (int) $lote->cantidad);
                    $lote->decrement('cantidad', $descontar);
                    DB::table('detalle_venta_lotes')->insert([
                        'idDetalle' => $detalle->idDetalle,
                        'idLote' => $lote->idLote,
                        'cantidad' => $descontar,
                    ]);
                    $pendiente -= $descontar;
                }

                $producto->decrement('stock', $item['cantidad']);
            }

            // Suma acumulada de subtotales para obtener el total general
            $total = bcadd($total, $subtotal, 2);
        }

        return $total;
    }

    public function ajustarStock(Producto $producto, int $stockObjetivo, ?string $fechaVencimiento): void
    {
        DB::transaction(function () use ($producto, $stockObjetivo, $fechaVencimiento): void {
            $producto = Producto::withTrashed()
                ->whereKey($producto->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $this->asegurarLoteLegado($producto);

            $diferencia = $stockObjetivo - (int) $producto->stock;
            if ($diferencia > 0) {
                $producto->lotes()->create([
                    'cantidad' => $diferencia,
                    'fecha_vencimiento' => $fechaVencimiento,
                ]);
            } elseif ($diferencia < 0) {
                $pendiente = abs($diferencia);
                $lotes = $producto->lotes()
                    ->where('cantidad', '>', 0)
                    ->lockForUpdate()
                    ->get();

                foreach ($lotes as $lote) {
                    if ($pendiente === 0) {
                        break;
                    }
                    $descontar = min($pendiente, (int) $lote->cantidad);
                    $lote->decrement('cantidad', $descontar);
                    $pendiente -= $descontar;
                }
            }

            $producto->update(['stock' => $stockObjetivo]);
        });
    }

    private function asegurarLoteLegado(Producto $producto): void
    {
        if (! $producto->lotes()->exists() && (int) $producto->stock > 0) {
            $producto->lotes()->create([
                'cantidad' => (int) $producto->stock,
                'fecha_vencimiento' => null,
            ]);
        }
    }

    private function restaurarDetalle(DetalleVenta $detalle): void
    {
        $asignaciones = DB::table('detalle_venta_lotes')
            ->where('idDetalle', $detalle->idDetalle)
            ->get();

        if ($asignaciones->isEmpty()) {
            $producto = Producto::withTrashed()
                ->where('idProducto', $detalle->idProducto)
                ->lockForUpdate()
                ->firstOrFail();
            $lote = $producto->lotes()->whereNull('fecha_vencimiento')->lockForUpdate()->first();
            if ($lote) {
                $lote->increment('cantidad', $detalle->cantidad);
            } else {
                $producto->lotes()->create([
                    'cantidad' => $detalle->cantidad,
                    'fecha_vencimiento' => null,
                ]);
            }
        } else {
            foreach ($asignaciones as $asignacion) {
                LoteInventario::where('idLote', $asignacion->idLote)
                    ->lockForUpdate()
                    ->firstOrFail()
                    ->increment('cantidad', $asignacion->cantidad);
            }
        }

        Producto::withTrashed()
            ->where('idProducto', $detalle->idProducto)
            ->increment('stock', $detalle->cantidad);
    }
}
