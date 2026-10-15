<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add role and profile-photo fields to the users table.
     */
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            throw new RuntimeException(
                'The users table must exist before adding role and profile-photo fields.'
            );
        }

        $hasRol = Schema::hasColumn('users', 'rol');
        $hasFoto = Schema::hasColumn('users', 'foto');

        if (!$hasRol || !$hasFoto) {
            Schema::table('users', function (Blueprint $table) use ($hasRol, $hasFoto) {
                if (!$hasRol) {
                    $table->string('rol')->default('invitado');
                }

                if (!$hasFoto) {
                    $table->string('foto')->nullable();
                }
            });
        }
    }

    /**
     * Remove only the columns introduced by this migration.
     */
    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $hasFoto = Schema::hasColumn('users', 'foto');
        $hasRol = Schema::hasColumn('users', 'rol');

        if ($hasFoto || $hasRol) {
            Schema::table('users', function (Blueprint $table) use ($hasFoto, $hasRol) {
                if ($hasFoto) {
                    $table->dropColumn('foto');
                }

                if ($hasRol) {
                    $table->dropColumn('rol');
                }
            });
        }
    }
};
