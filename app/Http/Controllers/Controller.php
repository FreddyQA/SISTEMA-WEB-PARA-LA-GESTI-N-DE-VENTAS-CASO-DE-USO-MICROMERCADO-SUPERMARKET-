<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller
{
    protected function soloDueno(): void
    {
        if (strtolower(auth()->user()->rol) !== 'administrador') {
        abort(403, 'Acceso no autorizado.');
    }
    }
}