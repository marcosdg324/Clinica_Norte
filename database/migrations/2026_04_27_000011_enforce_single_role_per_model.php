<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Enforce single-role-per-user at the database level.
 *
 * 1. Clean up: if any model instance already has multiple roles assigned,
 *    keep only the one with the lowest role_id (earliest assigned) and delete the rest.
 * 2. Add a UNIQUE constraint on (model_type, model_id) in model_has_roles
 *    so the database rejects any attempt to insert a second role for the same user.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Limpiar registros duplicados (mantener solo el primer rol asignado) ──

        $duplicates = DB::table('model_has_roles')
            ->select('model_type', 'model_id')
            ->groupBy('model_type', 'model_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            // Obtener el role_id más bajo (el primero asignado) para este model
            $keepRoleId = DB::table('model_has_roles')
                ->where('model_type', $dup->model_type)
                ->where('model_id', $dup->model_id)
                ->orderBy('role_id')
                ->value('role_id');

            // Borrar todos los demás roles del mismo model
            DB::table('model_has_roles')
                ->where('model_type', $dup->model_type)
                ->where('model_id', $dup->model_id)
                ->where('role_id', '!=', $keepRoleId)
                ->delete();
        }

        // ── 2. Agregar restricción UNIQUE (model_type, model_id) ─────────────────

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->unique(['model_type', 'model_id'], 'model_has_roles_model_unique');
        });
    }

    public function down(): void
    {
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropUnique('model_has_roles_model_unique');
        });
    }
};
