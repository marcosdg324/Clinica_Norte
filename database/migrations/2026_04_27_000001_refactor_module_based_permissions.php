<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migración que reemplaza el sistema de permisos granulares por acciones
 * (users.viewAny, users.view, users.create...) con un sistema basado en
 * módulos: un único permiso por módulo (modulo.access) más permisos
 * especiales explícitos (samples.approve, samples.reject).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Limpiar todas las asignaciones de permisos existentes ──────────
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_permissions')->truncate();

        // ── 2. Eliminar todos los permisos granulares anteriores ──────────────
        DB::table('permissions')->truncate();

        // ── 3. Insertar los nuevos permisos basados en módulos ────────────────
        $now = now();

        $permissions = [
            // Permisos de acceso por módulo (uno por módulo)
            ['name' => 'auth.access',          'guard_name' => 'web'],
            ['name' => 'patients.access',      'guard_name' => 'web'],
            ['name' => 'orders.access',        'guard_name' => 'web'],
            ['name' => 'samples.access',       'guard_name' => 'web'],
            ['name' => 'results.access',       'guard_name' => 'web'],
            ['name' => 'payments.access',      'guard_name' => 'web'],
            ['name' => 'reactivos.access',     'guard_name' => 'web'],
            ['name' => 'notifications.access', 'guard_name' => 'web'],
            ['name' => 'catalog.access',       'guard_name' => 'web'],
            ['name' => 'imaging.access',       'guard_name' => 'web'],

            // Permisos especiales para acciones específicas de muestras
            ['name' => 'samples.approve', 'guard_name' => 'web'],
            ['name' => 'samples.reject',  'guard_name' => 'web'],
        ];

        foreach ($permissions as &$perm) {
            $perm['created_at'] = $now;
            $perm['updated_at'] = $now;
        }

        DB::table('permissions')->insert($permissions);
    }

    public function down(): void
    {
        // Revertir: limpiar permisos nuevos
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('permissions')->truncate();
    }
};
