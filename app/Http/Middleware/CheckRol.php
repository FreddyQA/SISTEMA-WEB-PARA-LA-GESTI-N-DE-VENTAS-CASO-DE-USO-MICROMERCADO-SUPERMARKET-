<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
{
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $rolUsuario = strtolower(auth()->user()->rol);

    $roles = array_map('strtolower', $roles);

    if (!in_array($rolUsuario, $roles)) {
        abort(403, 'No tienes permiso para acceder a esta sección.');
    }

    return $next($request);
}
}