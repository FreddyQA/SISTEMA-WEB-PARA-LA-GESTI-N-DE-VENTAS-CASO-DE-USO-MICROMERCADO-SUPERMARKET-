<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UsuarioResource;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    // POST /api/login
    public function login(Request $request)
    {
        $datos = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
            'dispositivo' => 'nullable|string|max:100',
        ]);

        // Mismo límite que el login web (5 intentos / 2 minutos por email+IP),
        // para que la app móvil no abra una vía distinta para fuerza bruta.
        $clave = 'api-login:' . strtolower($datos['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($clave, 5)) {
            $segundos = RateLimiter::availableIn($clave);

            return response()->json([
                'message' => "Demasiados intentos. Intenta de nuevo en {$segundos} segundos.",
            ], 429);
        }

        if (! Auth::once(['email' => $datos['email'], 'password' => $datos['password']])) {
            RateLimiter::hit($clave, 120);

            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        RateLimiter::clear($clave);

        $usuario = Auth::getUser();
        $token = ApiToken::emitirPara($usuario, $datos['dispositivo'] ?? null);

        return response()->json([
            'token'   => $token,
            'usuario' => new UsuarioResource($usuario),
        ]);
    }

    // POST /api/logout (auth)
    public function logout(Request $request)
    {
        $request->attributes->get('api_token')?->delete();

        return response()->json(['message' => 'Sesión cerrada.']);
    }

    // GET /api/me (auth)
    public function me(Request $request)
    {
        return new UsuarioResource($request->user());
    }
}
