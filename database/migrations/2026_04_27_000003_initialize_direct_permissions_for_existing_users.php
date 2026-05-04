<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\PermissionRegistrar;

/**
 * Inicializa permisos DIRECTOS para todos los usuarios que actualmente
 * solo tienen permisos heredados de sus roles.
 *
 * Contexto: El sistema ahora usa hasDirectPermission() en lugar de can()
 * para controlar el acceso a módulos. Esto permite que el administrador
 * restrinja o amplíe permisos por usuario independientemente del rol.
 *
 * Esta migración copia los permisos del rol como permisos directos del usuario
 * para todos los usuarios que aún no tienen permisos directos asignados.
 */
return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Obtener usuarios SIN permisos directos (solo tienen permisos via rol)
        $users = User::whereDoesntHave('permissions')
            ->with(['roles.permissions'])
            ->get();

        foreach ($users as $user) {
            $rolePermissions = $user->roles
                ->flatMap(fn ($role) => $role->permissions)
                ->unique('id');

            if ($rolePermissions->isNotEmpty()) {
                $user->syncPermissions($rolePermissions);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Revertir: eliminar permisos directos de usuarios que no son admin
        // (solo conservar los del administrador del sistema)
        $adminEmail = 'admin@clinicanorte.com';

        User::where('email', '!=', $adminEmail)
            ->with('permissions')
            ->get()
            ->each(fn ($user) => $user->syncPermissions([]));

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
