<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\Auth\LoginController;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\DetallePedido;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
})->name('home');

Route::get('/login',   [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login',  [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        $hoy = Carbon::now();
        $inicioMes = $hoy->copy()->startOfMonth();
        $fechaMin = Pedido::min('fecha');
        $fechaMax = Pedido::max('fecha');
        $inicio = $fechaMin
            ? Carbon::parse($fechaMin)->startOfMonth()
            : $inicioMes->copy()->subMonths(5);
        $fin = $fechaMax
            ? Carbon::parse($fechaMax)->endOfMonth()
            : $inicioMes->endOfMonth();

        $ventasPorMes = Pedido::whereNotNull('fecha')
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->selectRaw('DATE_FORMAT(fecha,"%Y-%m") as mes, SUM(total) as total, COUNT(idPedido) as cantidad')
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

        $topProductos = DetallePedido::join('productos', 'detalle_pedidos.idProducto', '=', 'productos.idProducto')
            ->selectRaw('productos.idProducto, productos.nombre,
                         SUM(detalle_pedidos.cantidad) as unidades,
                         SUM(detalle_pedidos.subtotal)   as ingresos')
            ->groupBy('productos.idProducto', 'productos.nombre')
            ->orderByDesc('ingresos')
            ->limit(5)
            ->get();

        $topClientes = Pedido::join('clientes', 'pedidos.idCliente', '=', 'clientes.idCliente')
            ->selectRaw('clientes.idCliente, clientes.nombre,
                         COUNT(pedidos.idPedido) as pedidos,
                         SUM(pedidos.total) as total')
            ->groupBy('clientes.idCliente', 'clientes.nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $stockBajoCant = Producto::where('stock', '<=', 5)->count();
        $inventarioValuado = Producto::sum(DB::raw('stock * precio'));

        $ventasPorDia = Pedido::selectRaw('DATE(fecha) as dia, SUM(total) as total')
            ->whereNotNull('fecha')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        $pedidosCompletados = Pedido::where('estado', 'completado')->count();
        $pedidosAnulados    = Pedido::where('estado', 'anulado')->count();

        return view('dashboard', [
            'totalCategorias'   => Categoria::count(),
            'totalProductos'    => Producto::count(),
            'totalClientes'     => Cliente::count(),
            'totalPedidos'      => Pedido::count(),
            'totalPedidosCompletados' => $pedidosCompletados,
            'totalPedidosAnulados'    => $pedidosAnulados,
            'totalVentas'       => Pedido::sum('total'),
            'inventarioValuado' => (float)$inventarioValuado,
            'stockBajoCant'     => (int)$stockBajoCant,
            'labels'            => $ventasPorDia->pluck('dia'),
            'data'              => $ventasPorDia->pluck('total'),
            'labelsMes'         => $labelsMes,
            'dataVentasMes'     => $dataVentasMes,
            'dataCantMes'       => $dataCantMes,
            'topProductos'      => $topProductos,
            'topClientes'       => $topClientes,
        ]);
    })->name('dashboard');

    Route::get('/productos-pdf',   [ProductoController::class, 'exportPDF'])->name('productos.pdf');
    Route::get('/productos-excel', [ProductoController::class, 'exportExcel'])->name('productos.excel');

    Route::resource('categorias', CategoriaController::class)->only(['index']);
    Route::resource('productos',  ProductoController::class)->only(['index']);
    Route::resource('clientes',   ClienteController::class)->only(['index']);
    Route::resource('pedidos',    PedidoController::class)->only(['index']);
});

Route::middleware(['auth', 'rol:administrador'])->group(function () {

    Route::resource('categorias', CategoriaController::class)->except(['index', 'show']);
    Route::resource('productos',  ProductoController::class)->except(['index', 'show']);
    Route::resource('clientes',   ClienteController::class)->except(['index', 'show']);
    Route::resource('pedidos',    PedidoController::class)->except(['index', 'show']);

    Route::get('/reportes/ventas',        [ReporteController::class, 'ventas'])->name('reportes.ventas');

    Route::get('/productos/papelera',           [ProductoController::class, 'trashed'])->name('productos.papelera');
    Route::put('/productos/{id}/restore',       [ProductoController::class, 'restore'])->name('productos.restore');
    Route::delete('/productos/{id}/force-delete', [ProductoController::class, 'forceDelete'])->name('productos.forceDelete');

    Route::get('/reportes/ventas/pdf',   [ReporteController::class, 'exportPDF'])->name('reportes.ventas.pdf');
    Route::get('/reportes/ventas/excel', [ReporteController::class, 'exportExcel'])->name('reportes.ventas.excel');

    Route::resource('usuarios', UsuarioController::class)->except(['show']);
});
