<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    protected $primaryKey = 'idToken';

    protected $fillable = ['idUsuario', 'token_hash', 'nombre_dispositivo', 'last_used_at'];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'idUsuario');
    }

    /**
     * Genera un token nuevo (64 caracteres aleatorios), lo guarda hasheado
     * y devuelve el valor EN TEXTO PLANO — solo existe en memoria en este
     * momento; después de esto, ni la base de datos ni el servidor pueden
     * volver a recuperarlo, igual que con una contraseña.
     */
    public static function emitirPara(User $usuario, ?string $nombreDispositivo = null): string
    {
        $plano = bin2hex(random_bytes(32));

        self::create([
            'idUsuario'          => $usuario->id,
            'token_hash'         => hash('sha256', $plano),
            'nombre_dispositivo' => $nombreDispositivo,
            'last_used_at'       => now(),
        ]);

        return $plano;
    }
}
