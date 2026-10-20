<?php

/**
 * ═════════════════════════════════════════════════════════════════════════════
 * RUTAS DE LA APLICACIÓN WEB DE ESCRITORIO (routes/web.php)
 * ═════════════════════════════════════════════════════════════════════════════
 * 
 * Propósito Académico / Proyecto de Grado:
 * Define las rutas del panel administrativo de escritorio desarrollado con Blade,
 * Bootstrap 5 y Vite.
 * 
 * Arquitectura de Seguridad:
 * 1. Autenticación con Estado (Stateful Sessions): Utiliza cookies cifradas de sesión
 *    y protección automática contra falsificación de peticiones en sitios cruzados (CSRF).
 * 2. Control de Roles (RBAC):
 *    - Middleware `auth`: Cualquier usuario identificado puede consultar catálogos y registrar ventas.
 *    - Middleware `rol:administrador`: Restringe la alteración de precios, stock, borrado de registros,
 *      papelera de reciclaje, analítica avanzada de ventas y gestión de usuarios.
 * 3. Compatibilidad Multi-Base de Datos: El Dashboard detecta dinámicamente si el motor
 *    es MySQL o SQLite para formular las funciones de agregación por fecha (`DATE_FORMAT` vs `strftime`).
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PapeleraController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\Auth\LoginController;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\LoteInventario;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// Redirección inicial: Si está logueado va al Dashboard; si no, al formulario de Login
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

// Rutas de autenticación web de sesión
Route::get('/login',   [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login',  [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ── RUTAS ACCESIBLES PARA CUALQUIER USUARIO AUTENTICADO (ADMIN O INVITADO) ──
Route::middleware(['auth'])->group(function () {

    /**
     * Dashboard Principal de la Web:
     * Calcula métricas globales, gráficos temporales de Chart.js y rankings.
     */
    Route::get('/dashboard', function () {
        $hoy = Carbon::now();
        $inicioMes = $hoy->copy()->startOfMonth();
        $fechaMin = Venta::min('fecha');
        $fechaMax = Venta::max('fecha');
        $inicio = $fechaMin
            ? Carbon::parse($fechaMin)->startOfMonth()
            : $inicioMes->copy()->subMonths(5);
        $fin = $fechaMax
            ? Carbon::parse($fechaMax)->endOfMonth()
            : $inicioMes->endOfMonth();

        // Detección del motor SQL para funciones de fecha agnósticas a la base de datos
        $driver = DB::connection()->getDriverName();
        $mesExpr = $driver === 'sqlite' ? "strftime('%Y-%m', fecha)" : 'DATE_FORMAT(fecha,"%Y-%m")';
        $diaExpr = $driver === 'sqlite' ? "date(fecha)" : 'DATE(fecha)';

        // Agrupación mensual de ingresos excluyendo ventas anuladas
        $ventasPorMes = Venta::whereNotNull('fecha')
            ->where('estado', '!=', 'anulado')
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->selectRaw("{$mesExpr} as mes, SUM(total) as total, COUNT(idVenta) as cantidad")
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $labelsMes = collect();
        $dataVentasMes = collect();
        $dataCantMes = collect();
        for ($cursor = $inicio->copy(); $cursor->lessThanOrEqualTo($fin); $cursor->addMonth()) {
            $k = $cursor->format('Y-m');
            $labelMes = $cursor->locale('es')->isoFormat('MMM YY');
            $match = $ventasPorMes->firstWhere('mes', $k);
            $labelsMes->push($labelMes);
            $dataVentasMes->push((float)($match->total ?? 0));
            $dataCantMes->push((int)($match->cantidad ?? 0));
        }

        // Top 5 productos con mayor generación de ingresos (excluyendo ventas anuladas o eliminadas)
        $topProductos = DetalleVenta::join('productos', 'detalle_ventas.idProducto', '=', 'productos.idProducto')
            ->join('ventas', 'detalle_ventas.idVenta', '=', 'ventas.idVenta')
            ->where('ventas.estado', '!=', 'anulado')
            ->whereNull('ventas.deleted_at')
            ->selectRaw('productos.idProducto, productos.nombre,
                         SUM(detalle_ventas.cantidad) as unidades,
                         SUM(detalle_ventas.subtotal)   as ingresos')
            ->groupBy('productos.idProducto', 'productos.nombre')
            ->orderByDesc('ingresos')
            ->limit(5)
            ->get();

        // Top 5 clientes más recurrentes (excluyendo ventas anuladas o eliminadas)
        $topClientes = Venta::join('clientes', 'ventas.idCliente', '=', 'clientes.idCliente')
            ->where('ventas.estado', '!=', 'anulado')
            ->whereNull('ventas.deleted_at')
            ->selectRaw('clientes.idCliente, clientes.nombre,
                         COUNT(ventas.idVenta) as ventas,
                         SUM(ventas.total) as total')
            ->groupBy('clientes.idCliente', 'clientes.nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Alerta de stock crítico (<= 5) y valuación total del inventario
        $stockBajoCant = Producto::where('stock', '<=', 5)->count();
        $inventarioValuado = Producto::sum(DB::raw('stock * precio'));

        // Ventas por día para el gráfico lineal
        $ventasPorDia = Venta::selectRaw("{$diaExpr} as dia, SUM(total) as total")
            ->whereNotNull('fecha')
            ->where('estado', '!=', 'anulado')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        $ventasCompletados = Venta::where('estado', 'completado')->count();
        $ventasAnulados    = Venta::where('estado', 'anulado')->count();

        return view('dashboard', [
            'totalCategorias'   => Categoria::count(),
            'totalProductos'    => Producto::count(),
            'totalClientes'     => Cliente::count(),
            'totalTransacciones' => Venta::count(),
            'totalVentasCompletados' => $ventasCompletados,
            'totalVentasAnulados'    => $ventasAnulados,
            'totalVentas'        => Venta::where('estado', '!=', 'anulado')->sum('total'),
            'inventarioValuado' => (float)$inventarioValuado,
            'stockBajoCant'     => (int)$stockBajoCant,
            'lotesVencidos'     => (int) LoteInventario::where('cantidad', '>', 0)
                ->whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '<', $hoy->toDateString())
                ->distinct()
                ->count('idProducto'),
            'lotesPorVencer'    => (int) LoteInventario::where('cantidad', '>', 0)
                ->whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '>=', $hoy->toDateString())
                ->whereDate('fecha_vencimiento', '<=', $hoy->copy()->addDays(30)->toDateString())
                ->distinct()
                ->count('idProducto'),
            'labels'            => $ventasPorDia->pluck('dia'),
            'data'              => $ventasPorDia->pluck('total'),
            'labelsMes'         => $labelsMes,
            'dataVentasMes'     => $dataVentasMes,
            'dataCantMes'       => $dataCantMes,
            'topProductos'      => $topProductos,
            'topClientes'       => $topClientes,
        ]);
    })->name('dashboard');

    // Exportación de catálogo de productos
    Route::get('/productos-pdf',   [ProductoController::class, 'exportPDF'])->name('productos.pdf');
    Route::get('/productos-excel', [ProductoController::class, 'exportExcel'])->name('productos.excel');
    Route::get('/productos/buscar', [ProductoController::class, 'buscar'])->name('productos.buscar');

    // Consulta de catálogos (solo lectura)
    Route::resource('categorias',  CategoriaController::class)->only(['index']);
    Route::resource('productos',    ProductoController::class)->only(['index']);
    Route::resource('clientes',     ClienteController::class)->only(['index']);

    // Módulo de Ventas: Ambos roles pueden listar, crear y registrar ventas
    Route::resource('ventas',    VentaController::class)->only(['index', 'create', 'store']);

    // Endpoint AJAX ligero para sincronización en vivo del catálogo y ventas (Web y Móvil)
    Route::get('/sync/estado', function () {
        return response()->json(\App\Services\SyncService::getState());
    })->name('sync.estado');

    // Endpoint AJAX ligero para polling de actualización en tiempo real de ventas.
    // Devuelve el conteo total y el timestamp del último venta para que el frontend
    // detecte cambios sin necesidad de recargar la página completa.
    Route::get('/ventas/poll', function () {
        $ultimo = Venta::latest('idVenta')->first();
        return response()->json([
            'total'         => Venta::count(),
            'ultimo_id'     => $ultimo?->idVenta,
            'ultimo_numero' => $ultimo?->numero ?? $ultimo?->idVenta,
            'ultimo_fecha'  => $ultimo?->created_at?->toISOString(),
        ]);
    })->name('ventas.poll');

    // Generación y descarga de códigos QR y códigos de barras Code-128
    Route::get('/productos/{id}/qr',      [ProductoController::class, 'descargarQr'])->name('productos.qr');
    Route::get('/productos/{id}/qr/imagen', [ProductoController::class, 'mostrarQr'])->name('productos.qr.image');
    Route::get('/productos/{id}/barcode', [ProductoController::class, 'descargarBarcode'])->name('productos.barcode');
});

// ── RUTAS EXCLUSIVAS DEL ROL "ADMINISTRADOR" ────────────────────────────────
Route::middleware(['auth', 'rol:administrador'])->group(function () {

    // Escritura completa en catálogos (crear, editar, eliminar)
    Route::resource('categorias',  CategoriaController::class)->except(['index', 'show']);
    Route::resource('productos',    ProductoController::class)->except(['index', 'show']);
    Route::resource('clientes',     ClienteController::class)->except(['index', 'show']);

    // Solo el administrador puede editar o eliminar ventas registrados
    Route::resource('ventas',    VentaController::class)->only(['edit', 'update', 'destroy']);

    // Reportes analíticos con exportación a PDF y Excel
    Route::get('/reportes/ventas',        [ReporteController::class, 'ventas'])->name('reportes.ventas');
    Route::get('/reportes/ventas/pdf',   [ReporteController::class, 'exportPDF'])->name('reportes.ventas.pdf');
    Route::get('/reportes/ventas/excel', [ReporteController::class, 'exportExcel'])->name('reportes.ventas.excel');

    // Papelera unificada de reciclaje con restauración y purga en lote
    Route::get('/papelera',            [PapeleraController::class, 'index'])->name('papelera.index');
    Route::post('/papelera/restaurar', [PapeleraController::class, 'restaurar'])->name('papelera.restaurar');
    Route::post('/papelera/eliminar',  [PapeleraController::class, 'eliminar'])->name('papelera.eliminar');

    // Redirecciones compatibles para las antiguas URLs de papelera
    foreach (['productos', 'categorias', 'clientes'] as $tipoPapelera) {
        Route::get("/{$tipoPapelera}/papelera", fn () => redirect()->route('papelera.index', ['tipo' => $tipoPapelera]))
            ->name("{$tipoPapelera}.papelera");
    }

    // Administración de usuarios y control de accesos
    Route::resource('usuarios', UsuarioController::class)->except(['show']);
});
