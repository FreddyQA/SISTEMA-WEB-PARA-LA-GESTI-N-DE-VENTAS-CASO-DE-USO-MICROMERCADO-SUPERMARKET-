<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

/**
 * Controlador API para el Panel de Control (Dashboard) Móvil.
 * 
 * Propósito para Proyecto de Grado:
 * Proporciona un endpoint REST de alto rendimiento (/api/dashboard) que consolida
 * los Indicadores Clave de Desempeño (KPIs) del negocio en una sola llamada HTTP,
 * optimizando el consumo de ancho de banda y la batería del dispositivo móvil.
 * 
 * Reglas de negocio aplicadas:
 * 1. Excluye ventas en estado "anulado" para mantener la precisión financiera.
 * 2. Define el umbral de stock bajo en <= 5 unidades para alertas tempranas de inventario.
 * 3. Provee las últimas 5 ventas para permitir trazabilidad inmediata desde la pantalla principal.
 */
class DashboardApiController extends Controller
{
    /**
     * Retorna el resumen estadístico y las ventas recientes para la app móvil.
     * 
     * @return JsonResponse JSON con métricas de ventas, stock, productos, clientes y actividad reciente.
     */
    public function index(): JsonResponse
    {
        // Fecha actual formateada (YYYY-MM-DD) según la zona horaria del sistema (America/La_Paz)
        $hoy = Carbon::now()->toDateString();
        
        // Primer día del mes corriente para calcular el acumulado mensual
        $inicioMes = Carbon::now()->startOfMonth()->toDateString();

        $ventasHoy = Venta::where('estado', '!=', 'anulado')->whereDate('fecha', $hoy);
        $transaccionesHoy = (clone $ventasHoy)->count();
        $montoVentasHoy = (float) (clone $ventasHoy)->sum('total');
        $ultimasVentas = Venta::with('cliente')
            ->latest('idVenta')
            ->take(5)
            ->get()
            ->map(function ($venta) {
                return [
                    'id'      => $venta->idVenta,
                    'numero'  => $venta->numero ?? $venta->idVenta,
                    'fecha'   => $venta->fecha,
                    'estado'  => $venta->estado,
                    'total'   => (float) $venta->total,
                    'cliente' => $venta->cliente ? [
                        'id'     => $venta->cliente->idCliente,
                        'nombre' => $venta->cliente->nombre,
                    ] : null,
                ];
            });

        return response()->json([
            // Total de productos activos en el catálogo (excluye eliminados por SoftDeletes)
            'total_productos' => Producto::count(),

            // Total de clientes registrados en el sistema
            'total_clientes'  => Cliente::count(),

            // Cantidad de productos en nivel crítico de inventario (stock <= 5)
            'stock_bajo'      => Producto::where('stock', '<=', 5)->count(),

            // Monto total en dinero vendido durante el día de hoy (excluyendo ventas anuladas)
            'ventas_hoy' => $montoVentasHoy,

            // Número total de transacciones de venta completadas el día de hoy
            'transacciones_hoy' => $transaccionesHoy,

            // Monto acumulado de ventas en el mes en curso (desde el día 1 hasta hoy)
            'ventas_mes'      => (float) Venta::where('estado', '!=', 'anulado')
                ->where('fecha', '>=', $inicioMes)
                ->sum('total'),

            // Respuesta canónica del módulo de ventas para la app móvil.
            'ultimas_ventas' => $ultimasVentas,
        ]);
    }
}
