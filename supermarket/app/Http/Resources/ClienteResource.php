<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->idCliente,
            'nombre'    => $this->nombre,
            'email'     => $this->email,
            'telefono'  => $this->telefono,
            'direccion' => $this->direccion,
        ];
    }
}
