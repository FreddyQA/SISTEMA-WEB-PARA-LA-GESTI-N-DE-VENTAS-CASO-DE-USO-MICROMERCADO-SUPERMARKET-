<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaApiController;
use App\Http\Controllers\Api\ClienteApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\VentaApiController;
use App\Http\Controllers\Api\ProductoApiController;
use App\Http\Controllers\Api\ReporteApiController;
use App\Http\Controllers\Api\UsuarioApiController;
use Illuminate\Support\Facades\Route;

/**
 * ═════════════════════════════════════════════════════════════════════════════
 * RUTAS DE LA API REST (routes/api.php) - CONSUMIDAS POR LA APP MÓVIL EXPO
 * ═════════════════════════════════════════════════════════════════════════════
 * 
 * Propósito Académico / Proyecto de Grado:
 * Expone los endpoints de la API RESTful que permiten la interoperabilidad entre el
 * backend Laravel y la aplicación móvil React Native / Expo.
 * 
 * Mecanismos de Seguridad Clave:
 * 1. Rate Limiting (Protección contra ataques de fuerza bruta y DoS):
 *    Aplica el middleware 'throttle:api' para limitar la tasa de peticiones por minuto.
 * 2. Autenticación por Tokens Bearer (`api.token`):
 *    No utiliza cookies ni sesiones de navegador; cada petición incluye un token
 *    criptográfico en la cabecera `Authorization: Bearer <token>`.
 * 3. Control de Acceso Basado en Roles (RBAC con `api.rol:administrador`):
 *    Protege las operaciones sensibles (crear usuarios, ver reportes, anular ventas,
 *    modificar inventario) restringiéndolas exclusivamente al Administrador General.
 */

// ── RUTA PÚBLICA DE AUTENTICACIÓN ───────────────────────────────────────────
// POST /api/login: Valida correo y contraseña, emite y retorna el token Bearer
Route::post('/login', [AuthController::class, 'login']);

// Sincronización en tiempo real sin recargar
Route::get('/sync/estado', fn () => response()->json(\App\Services\SyncService::getState()));

// ── RUTAS PROTEGIDAS CON AUTENTICACIÓN BEARER Y RATE LIMITING ────────────────
Route::middleware(['api.token', 'throttle:60,1'])->group(function () {

    // Gestión de sesión móvil
    Route::post('/logout', [AuthController::class, 'logout']); // Invalida el token en la BD
    Route::get('/me',      [AuthController::class, 'me']);     // Retorna los datos del usuario logueado

    // Panel de Control (Dashboard)
    Route::get('/dashboard', [DashboardApiController::class, 'index']); // KPIs de ventas y stock

    // Catálogos de lectura libre para cualquier usuario autenticado (Vendedor o Admin)
    Route::get('/categorias',  [CategoriaApiController::class, 'index']);

    // ── SUB-GRUPO: OPERACIONES EXCLUSIVAS DEL ROL "ADMINISTRADOR" ────────────
    Route::middleware(['api.rol:administrador'])->group(function () {
        // Gestión completa de Categorías (CRUD)
        Route::post('/categorias',          [CategoriaApiController::class, 'store']);
        Route::put('/categorias/{id}',      [CategoriaApiController::class, 'update']);
        Route::delete('/categorias/{id}',   [CategoriaApiController::class, 'destroy']);

        // Reportes analíticos de ventas (Inteligencia de Negocios)
        Route::get('/reportes/ventas',      [ReporteApiController::class, 'ventas']);

        // Gestión y subida de imágenes de Productos
        Route::post('/productos',           [ProductoApiController::class, 'store']);
        Route::put('/productos/{id}',       [ProductoApiController::class, 'update']);
        Route::delete('/productos/{id}',    [ProductoApiController::class, 'destroy']);
        Route::post('/productos/{id}/imagen',   [ProductoApiController::class, 'subirImagen']);
        Route::delete('/productos/{id}/imagen', [ProductoApiController::class, 'quitarImagen']);

        // Modificación y baja de Clientes
        Route::put('/clientes/{id}',        [ClienteApiController::class, 'update']);
        Route::delete('/clientes/{id}',     [ClienteApiController::class, 'destroy']);

        // Modificación y anulación de ventas ya emitidos
        Route::put('/ventas/{id}',         [VentaApiController::class, 'update']);
        Route::delete('/ventas/{id}',      [VentaApiController::class, 'destroy']);

        // Conserva las llamadas antiguas de clientes móviles mientras migran a /ventas.
        Route::put('/pedidos/{id}',         [VentaApiController::class, 'update']);
        Route::delete('/pedidos/{id}',      [VentaApiController::class, 'destroy']);

        // Gestión completa de Cuentas de Usuario y Privilegios
        Route::get('/usuarios',             [UsuarioApiController::class, 'index']);
        Route::post('/usuarios',            [UsuarioApiController::class, 'store']);
        Route::put('/usuarios/{id}',        [UsuarioApiController::class, 'update']);
        Route::delete('/usuarios/{id}',     [UsuarioApiController::class, 'destroy']);
        Route::post('/usuarios/{id}/imagen',   [UsuarioApiController::class, 'subirImagen']);
        Route::delete('/usuarios/{id}/imagen', [UsuarioApiController::class, 'quitarImagen']);
    });

    // ── RUTAS OPERATIVAS COMPARTIDAS (VENDEDORES Y ADMINISTRADORES) ──────────
    // Catálogo de Productos y búsqueda óptica por SKU / Código de Barras
    Route::get('/productos',             [ProductoApiController::class, 'index']);
    Route::get('/productos/codigo/{sku}', [ProductoApiController::class, 'porCodigo']); // Consumido por el escáner
    Route::get('/productos/{id}',        [ProductoApiController::class, 'show']);

    // Consulta y alta rápida de Clientes al momento de facturar
    Route::get('/clientes',  [ClienteApiController::class, 'index']);
    Route::post('/clientes', [ClienteApiController::class, 'store']);

    // Emisión y consulta de Ventas (Punto de Venta POS Móvil)
    // Cualquier empleado autenticado puede emitir ventas y consultar su historial
    Route::get('/ventas',       [VentaApiController::class, 'index']);
    Route::get('/ventas/{id}',  [VentaApiController::class, 'show']);
    Route::post('/ventas',      [VentaApiController::class, 'store']);

    // Alias temporales para versiones previas de la aplicación móvil.
    Route::get('/pedidos',       [VentaApiController::class, 'index']);
    Route::get('/pedidos/{id}',  [VentaApiController::class, 'show']);
    Route::post('/pedidos',      [VentaApiController::class, 'store']);
});
