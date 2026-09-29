<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Equivalente a CheckRol (admin web) pero para la API: en vez de
 * redirigir a /login o abortar con una página de error HTML, responde
 * JSON — lo único que un cliente móvil puede interpretar.
 */
class CheckRolApi
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! auth()->check()) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $rolUsuario = strtolower(auth()->user()->rol);
        $roles = array_map('strtolower', $roles);

        if (! in_array($rolUsuario, $roles)) {
            return response()->json(['message' => 'No tienes permiso para esta acción.'], 403);
        }

        return $next($request);
    }
}
