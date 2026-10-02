<?php

namespace App\Http\Resources;

use App\Support\Sku;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->idProducto,
            'nombre'      => $this->nombre,
            'precio'      => (float) $this->precio,
            'stock'       => $this->stock,
            'stock_disponible' => $this->stock_disponible,
            'sku'         => Sku::format($this->idProducto),
            'imagen_url'  => $this->imagen ? asset('storage/' . $this->imagen) : null,
            'lotes'       => $this->whenLoaded('lotes', fn () => $this->lotes->map(fn ($lote) => [
                'id' => $lote->idLote,
                'cantidad' => $lote->cantidad,
                'fecha_vencimiento' => $lote->fecha_vencimiento?->format('Y-m-d'),
            ])),
            'categoria'   => $this->whenLoaded('categoria', fn () => [
                'id'     => $this->categoria->idCategoria,
                'nombre' => $this->categoria->nombre,
            ]),
        ];
    }
}
