<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VentaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->idVenta,
            'numero'  => $this->numero,
            'fecha'   => $this->fecha ? ($this->fecha instanceof \Carbon\CarbonInterface ? $this->fecha->format('Y-m-d') : (string) $this->fecha) : null,
            'estado'  => $this->estado,
            'total'   => (float) ($this->total ?? 0),
            'cliente' => $this->whenLoaded('cliente', fn () => $this->cliente ? [
                'id'     => $this->cliente->idCliente,
                'nombre' => $this->cliente->nombre,
            ] : null),
            'vendedor' => $this->whenLoaded('usuario', fn () => $this->usuario ? [
                'id'     => $this->usuario->id,
                'nombre' => $this->usuario->name,
            ] : null),
            'items' => $this->whenLoaded('detalles', fn () => $this->detalles->map(fn ($d) => [
                'producto_id'     => $d->idProducto,
                'producto_nombre' => $d->producto?->nombre ?? 'Producto',
                'cantidad'        => $d->cantidad,
                'precio_unitario' => (float) ($d->precio_unitario ?? 0),
                'subtotal'        => (float) ($d->subtotal ?? 0),
            ])),
        ];
    }
}
