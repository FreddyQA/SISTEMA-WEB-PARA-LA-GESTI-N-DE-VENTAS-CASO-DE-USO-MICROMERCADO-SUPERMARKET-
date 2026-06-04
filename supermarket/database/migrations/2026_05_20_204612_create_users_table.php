<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade el sistema de roles y foto de perfil a la tabla `users`
     * ya creada por 0001_01_01_000000_create_users_table.
     *
     * NOTA: esta migración originalmente volvía a hacer Schema::create('users', ...),
     * lo que producía "table 'users' already exists" al correr `migrate:fresh` desde
     * cero. Se corrigió a un Schema::table (ALTER) que solo agrega las columnas nuevas.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rol')->default('invitado')->after('password');
            $table->string('foto')->nullable()->after('rol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rol', 'foto']);
        });
    }
};