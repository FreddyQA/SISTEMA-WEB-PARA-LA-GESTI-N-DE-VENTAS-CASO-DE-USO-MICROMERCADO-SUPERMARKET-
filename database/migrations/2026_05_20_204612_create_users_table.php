<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add role and profile-photo fields without recreating the users table.
     */
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('rol')->default('invitado');
                $table->string('foto')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'rol')) {
                $table->string('rol')->default('invitado');
            }

            if (!Schema::hasColumn('users', 'foto')) {
                $table->string('foto')->nullable();
            }
        });
    }

    /**
     * Remove only the columns introduced by this migration.
     */
    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'foto')) {
                $table->dropColumn('foto');
            }

            if (Schema::hasColumn('users', 'rol')) {
                $table->dropColumn('rol');
            }
        });
    }
};
