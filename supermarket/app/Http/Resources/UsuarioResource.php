<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'nombre'    => $this->name,
            'email'     => $this->email,
            'rol'       => $this->rol,
            'foto_url'  => $this->foto ? asset('storage/' . $this->foto) : null,
        ];
    }
}
