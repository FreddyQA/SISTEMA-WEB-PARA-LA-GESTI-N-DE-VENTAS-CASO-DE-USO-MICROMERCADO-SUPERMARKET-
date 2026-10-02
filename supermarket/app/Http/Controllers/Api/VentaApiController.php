<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\UpdateVentaRequest;
use App\Http\Resources\VentaResource;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Http\Request;

class VentaApiController extends Controller
{
    public function __construct(private VentaService $ventas)
    {
    }

    // GET /api/ventas?cliente=
    public function index(Request $request)
    {
        $ventas = Venta::with(['cliente', 'usuario', 'detalles.producto'])
            ->when($request->filled('cliente'), fn ($q) => $q->where('idCliente', $request->cliente))
            ->latest('fecha')
            ->paginate(20);

        return VentaResource::collection($ventas);
    }

    // GET /api/ventas/{id}
    public function show($id)
    {
        $venta = Venta::with(['cliente', 'usuario', 'detalles.producto'])->findOrFail($id);

        return new VentaResource($venta);
    }

    // POST /api/ventas — misma validación y misma lógica transaccional
    // (VentaService) que usa el admin web; stock y totales nunca pueden
    // divergir entre lo que vende la app móvil y lo que vende el admin.
    public function store(StoreVentaRequest $request)
    {
        $venta = $this->ventas->crear($request->validated(), $request->user()->id);
        $venta->load(['cliente', 'usuario', 'detalles.producto']);

        return (new VentaResource($venta))->response()->setStatusCode(201);
    }

    // PUT /api/ventas/{id} (admin) — editar cliente/fecha/items/estado, o
    // simplemente anular: el botón "Anular" de la app manda los mismos
    // los datos actuales de la venta con estado=anulado (VentaResource expone
    // "items" con el idProducto de cada línea).
    public function update(UpdateVentaRequest $request, $id)
    {
        $venta = $this->ventas->actualizar((int) $id, $request->validated());
        $venta->load(['cliente', 'usuario', 'detalles.producto']);

        return new VentaResource($venta);
    }

    // DELETE /api/ventas/{id} (admin)
    public function destroy($id)
    {
        $this->ventas->eliminar((int) $id);

        return response()->json(['message' => 'Venta eliminada correctamente.']);
    }
}
