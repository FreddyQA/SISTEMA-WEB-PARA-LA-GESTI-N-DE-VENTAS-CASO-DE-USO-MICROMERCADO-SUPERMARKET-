<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Autenticación por token para la API REST que consume la app móvil.
     *
     * No se usa Laravel Sanctum (el paquete estándar para esto) porque
     * instalarlo requiere resolver dependencias contra Packagist, algo
     * que no siempre es posible según el entorno de red disponible al
     * momento de desplegar. Esta tabla implementa el mismo mecanismo que
     * usa Sanctum internamente para "personal access tokens": un token
     * aleatorio de 64 caracteres se entrega una sola vez al cliente (al
     * iniciar sesión), y solo su hash SHA-256 se guarda en la base de
     * datos — igual que una contraseña, el token en texto plano nunca
     * se puede recuperar a partir de lo almacenado.
     */
    public function up(): void
    {
        if (!Schema::hasTable('api_tokens')) {
            Schema::create('api_tokens', function (Blueprint $table) {
                $table->id('idToken');
                $table->foreignId('idUsuario')->constrained('users', 'id')->onDelete('cascade');
                $table->string('token_hash', 64)->unique();
                $table->string('nombre_dispositivo')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};
