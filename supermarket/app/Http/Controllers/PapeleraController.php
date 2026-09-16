<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SincronizaImagenPublica;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Services\SyncService;

/**
 * Papelera única del sistema: agrupa los registros eliminables en una
 * sola pantalla con
 * una pestaña por tipo. Restaurar y eliminar definitivamente funcionan en
 * lote (una sola fila es un lote de uno), con las mismas reglas de
 * integridad que ya existían por tipo:
 *
 *  - Producto:  no se elimina si aparece en ventas (detalle_ventas).
 *  - Categoría: no se elimina si algún producto (activo o en papelera) la usa.
 *  - Cliente:   no se elimina si tiene ventas (activos o en papelera).
 *
 * Y una regla nueva al restaurar: un producto no puede volver mientras su
 * categoría siga en la papelera (quedaría apuntando a una categoría que no
 * aparece en ningún listado ni selector); primero se restaura la categoría.
 */
class PapeleraController extends Controller
{
    use SincronizaImagenPublica;

    private const MAX_LOTE = 500;

    /** Metadatos por tipo: modelo, llave primaria, textos e ícono de la pestaña. */
    private function tipos(): array
    {
        return [
            'productos' => [
                'modelo' => Producto::class, 'pk' => 'idProducto',
                'plural' => 'Productos', 'singular' => 'producto', 'icono' => 'fa-box',
                'columnas' => ['Categoría', 'Precio'],
            ],
            'categorias' => [
                'modelo' => Categoria::class, 'pk' => 'idCategoria',
                'plural' => 'Categorías', 'singular' => 'categoría', 'icono' => 'fa-tags', 'fem' => true,
                'columnas' => ['Descripción'],
            ],
            'clientes' => [
                'modelo' => Cliente::class, 'pk' => 'idCliente',
                'plural' => 'Clientes', 'singular' => 'cliente', 'icono' => 'fa-users',
                'columnas' => ['Correo', 'Teléfono'],
            ],
        ];
    }

    // GET /papelera?tipo=productos|categorias|clientes
    public function index(Request $request)
    {
        $this->soloDueno();

        $tipos = $this->tipos();
        $tipo = $request->query('tipo');
        if (! is_string($tipo) || ! isset($tipos[$tipo])) {
            $tipo = 'productos';
        }

        $conteos = [];
        foreach ($tipos as $clave => $meta) {
            $conteos[$clave] = $meta['modelo']::onlyTrashed()->count();
        }

        $meta = $tipos[$tipo];
        $filas = $this->consulta($tipo)
            ->orderByDesc('deleted_at')
            ->limit(self::MAX_LOTE)
            ->get()
            ->map(fn (Model $m) => $this->fila($tipo, $m));

        return view('papelera.index', [
            'tipo'     => $tipo,
            'meta'     => $meta,
            'tipos'    => $tipos,
            'conteos'  => $conteos,
            'total'    => array_sum($conteos),
            'filas'    => $filas,
            'truncado' => $conteos[$tipo] > self::MAX_LOTE,
            'maxLote'  => self::MAX_LOTE,
        ]);
    }

    // POST /papelera/restaurar  (tipo, ids[])
    public function restaurar(Request $request)
    {
        $this->soloDueno();
        [$tipo, $ids] = $this->validarLote($request);
        $meta = $this->tipos()[$tipo];

        $restaurados = 0;
        $omitidos = [];

        foreach ($this->consulta($tipo)->whereIn($meta['pk'], $ids)->get() as $registro) {
            $motivo = $this->bloqueoRestaurar($tipo, $registro);
            if ($motivo) {
                $omitidos[] = $this->etiqueta($registro) . " ({$motivo})";
                continue;
            }
            $registro->restore();
            $restaurados++;
        }

        if ($restaurados > 0) {
            SyncService::touch($tipo);
            if ($tipo === 'categorias') {
                SyncService::touch('productos');
            }
        }

        return $this->responder($tipo, $meta, 'restaurado', $restaurados, $omitidos);
    }

    // POST /papelera/eliminar  (tipo, ids[])
    public function eliminar(Request $request)
    {
        $this->soloDueno();
        [$tipo, $ids] = $this->validarLote($request);
        $meta = $this->tipos()[$tipo];

        $eliminados = 0;
        $omitidos = [];

        foreach ($this->consulta($tipo)->whereIn($meta['pk'], $ids)->get() as $registro) {
            // Se revalida en el servidor: la interfaz deshabilita el botón de
            // los bloqueados, pero nunca se confía solo en lo que mande el cliente.
            $motivo = $this->bloqueoEliminar($tipo, $registro);
            if ($motivo) {
                $omitidos[] = $this->etiqueta($registro) . " ({$motivo})";
                continue;
            }

            try {
                if ($tipo === 'productos' && $registro->imagen && Storage::disk('public')->exists($registro->imagen)) {
                    Storage::disk('public')->delete($registro->imagen);
                    $this->removeImageFromPublicStorage($registro->imagen);
                }
                $registro->forceDelete();
                $eliminados++;
            } catch (QueryException) {
                // Última red de seguridad: si el motor rechaza el borrado por una
                // llave foránea que no se contempló arriba, se informa en vez de fallar.
                $omitidos[] = $this->etiqueta($registro) . ' (tiene registros relacionados)';
            }
        }

        if ($eliminados > 0) {
            SyncService::touch($tipo);
            if ($tipo === 'categorias') {
                SyncService::touch('productos');
            }
        }

        return $this->responder($tipo, $meta, 'eliminado', $eliminados, $omitidos, permanente: true);
    }

    // ───────────────────────── helpers ─────────────────────────

    /** @return array{0:string,1:array<int>} */
    private function validarLote(Request $request): array
    {
        $datos = $request->validate([
            'tipo'  => ['required', Rule::in(array_keys($this->tipos()))],
            'ids'   => ['required', 'array', 'min:1', 'max:' . self::MAX_LOTE],
            'ids.*' => ['integer'],
        ], [
            'ids.required' => 'Selecciona al menos un elemento.',
            'ids.min'      => 'Selecciona al menos un elemento.',
        ]);

        return [$datos['tipo'], array_map('intval', $datos['ids'])];
    }

    /** Consulta base (solo eliminados) con lo necesario para mostrar y validar cada tipo. */
    private function consulta(string $tipo)
    {
        $query = $this->tipos()[$tipo]['modelo']::onlyTrashed();

        return match ($tipo) {
            'productos'   => $query->with(['categoria' => fn ($q) => $q->withTrashed()])->withCount('detalleVentas'),
            'categorias'  => $query->withCount(['productos' => fn ($q) => $q->withTrashed()]),
            'clientes'    => $query->withCount(['ventas' => fn ($q) => $q->withTrashed()]),
        };
    }

    private function bloqueoEliminar(string $tipo, Model $m): ?string
    {
        return match ($tipo) {
            'productos'   => $m->detalle_ventas_count > 0
                ? "aparece en {$m->detalle_ventas_count} línea(s) de venta" : null,
            'categorias'  => $m->productos_count > 0
                ? "tiene {$m->productos_count} producto(s) asociado(s), incluidos los de la papelera" : null,
            'clientes'    => $m->ventas_count > 0
                ? "tiene {$m->ventas_count} venta(s) asociado(s), incluidos los de la papelera" : null,
        };
    }

    private function bloqueoRestaurar(string $tipo, Model $m): ?string
    {
        if ($tipo === 'productos' && $m->categoria && $m->categoria->trashed()) {
            return "su categoría «{$m->categoria->nombre}» sigue en la papelera: restáurala primero";
        }

        return null;
    }

    private function etiqueta(Model $m): string
    {
        return '«' . $m->nombre . '»';
    }

    /** Fila lista para pintar en la tabla (la vista no necesita saber de modelos). */
    private function fila(string $tipo, Model $m): array
    {
        $celdas = match ($tipo) {
            'productos'   => [
                $m->categoria?->nombre ?? '—',
                'Bs ' . number_format((float) $m->precio, 2),
            ],
            'categorias'  => [$m->descripcion ?: '—'],
            'clientes'    => [$m->email ?: '—', $m->telefono ?: '—'],
        };

        return [
            'id'               => $m->getKey(),
            'nombre'           => $m->nombre,
            'imagen'           => $tipo === 'productos' && $m->imagen ? asset('storage/' . $m->imagen) : null,
            'celdas'           => $celdas,
            'eliminado'        => $m->deleted_at,
            'bloqueoEliminar'  => $this->bloqueoEliminar($tipo, $m),
            'bloqueoRestaurar' => $this->bloqueoRestaurar($tipo, $m),
        ];
    }

    private function responder(string $tipo, array $meta, string $verbo, int $hechos, array $omitidos, bool $permanente = false)
    {
        $redireccion = redirect()->route('papelera.index', ['tipo' => $tipo]);

        if ($hechos > 0) {
            // Concordancia de género: «categoría restaurada» / «producto restaurado».
            $participio = ($meta['fem'] ?? false) ? substr($verbo, 0, -1) . 'a' : $verbo;
            $sufijo = $permanente ? ' de forma permanente' : '';
            $redireccion->with('success', $hechos === 1
                ? ucfirst($meta['singular']) . " {$participio}{$sufijo}."
                : "{$hechos} " . mb_strtolower($meta['plural']) . " {$participio}s{$sufijo}.");
        }

        if ($omitidos) {
            $lista = implode('; ', array_slice($omitidos, 0, 4));
            $resto = count($omitidos) - 4;
            $accion = $permanente ? 'eliminar' : 'restaurar';
            $redireccion->with('error', "No se pudo {$accion}: {$lista}" . ($resto > 0 ? " y {$resto} más." : '.'));
        }

        return $redireccion;
    }
}
