<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autentica peticiones de la API móvil mediante un header
 * "Authorization: Bearer <token>". Busca el hash del token recibido
 * entre los emitidos (ApiToken::emitirPara) y, si existe, autentica al
 * usuario dueño de ese token para el resto del ciclo de vida de la
 * petición (auth()->user() funciona igual que en el admin web).
 */
class ApiTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'No se proporcionó un token de acceso.'], 401);
        }

        $registro = ApiToken::where('token_hash', hash('sha256', $token))->first();

        if (! $registro) {
            return response()->json(['message' => 'Token inválido o expirado. Inicia sesión nuevamente.'], 401);
        }

        // Caducidad de seguridad: Tokens sin uso por más de 30 días se invalidan automáticamente
        if ($registro->last_used_at && $registro->last_used_at->lt(now()->subDays(30))) {
            $registro->delete();
            return response()->json(['message' => 'Token expirado por inactividad (30 días). Inicia sesión nuevamente.'], 401);
        }

        $usuario = $registro->usuario;

        if (! $usuario) {
            return response()->json(['message' => 'El usuario de este token ya no existe.'], 401);
        }

        $registro->update(['last_used_at' => now()]);

        auth()->setUser($usuario);
        $request->attributes->set('api_token', $registro);

        return $next($request);
    }
}
