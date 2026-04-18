<?php

namespace App\Domains\Auth\Seeders;

use App\Domains\Auth\Models\Permission;
use App\Domains\Auth\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class AuthSeeder extends Seeder
{
    /**
     * Módulos del sistema y sus acciones disponibles.
     */
    private array $modules = [
        'users',
        'roles',
        'permissions',
        'patients',
        'orders',
        'samples',
        'results',
        'billing',
        'inventory',
        'notifications',
    ];

    private array $actions = ['viewAny', 'view', 'create', 'update', 'delete'];

    public function run(): void
    {
        // Limpiar caché de permisos antes de comenzar
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── 1. CREAR TODOS LOS PERMISOS DEL SISTEMA ─────────────────────────
        foreach ($this->modules as $module) {
            foreach ($this->actions as $action) {
                Permission::firstOrCreate([
                    'name'       => "{$module}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // ─── 2. CREAR LOS 5 ROLES BASE ────────────────────────────────────────
        $admin         = Role::firstOrCreate(['name' => 'Administrador',  'guard_name' => 'web']);
        $recepcionista = Role::firstOrCreate(['name' => 'Recepcionista',  'guard_name' => 'web']);
        $bioquimico    = Role::firstOrCreate(['name' => 'Bioquímico',     'guard_name' => 'web']);
        $medico        = Role::firstOrCreate(['name' => 'Médico',         'guard_name' => 'web']);
        $paciente      = Role::firstOrCreate(['name' => 'Paciente',       'guard_name' => 'web']);

        // ─── 3. ASIGNAR PERMISOS POR ROL ─────────────────────────────────────

        // Administrador: TODOS los permisos del sistema
        $admin->syncPermissions(Permission::all());

        // Recepcionista: gestiona pacientes, órdenes, facturación y muestras (solo lectura)
        $recepcionista->syncPermissions(Permission::whereIn('name', [
            'patients.viewAny', 'patients.view', 'patients.create', 'patients.update',
            'orders.viewAny',   'orders.view',   'orders.create',   'orders.update',
            'billing.viewAny',  'billing.view',  'billing.create',  'billing.update',
            'samples.viewAny',  'samples.view',
            'notifications.viewAny', 'notifications.view',
        ])->get());

        // Bioquímico: gestiona muestras, resultados e inventario
        $bioquimico->syncPermissions(Permission::whereIn('name', [
            'samples.viewAny',   'samples.view',   'samples.create',   'samples.update',
            'results.viewAny',   'results.view',   'results.create',   'results.update',
            'inventory.viewAny', 'inventory.view', 'inventory.create', 'inventory.update',
            'patients.viewAny',  'patients.view',
            'orders.viewAny',    'orders.view',
            'notifications.viewAny', 'notifications.view',
        ])->get());

        // Médico: consulta pacientes, crea/ve órdenes y lee resultados
        $medico->syncPermissions(Permission::whereIn('name', [
            'patients.viewAny', 'patients.view',
            'orders.viewAny',   'orders.view',   'orders.create',
            'results.viewAny',  'results.view',
            'notifications.viewAny', 'notifications.view',
        ])->get());

        // Paciente: solo ver sus propios resultados (la Policy filtra por ID)
        $paciente->syncPermissions(Permission::whereIn('name', [
            'results.view',
        ])->get());

        // ─── 4. CREAR USUARIO ADMINISTRADOR POR DEFECTO ───────────────────────
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@clinicanorte.com'],
            [
                'name'              => 'Administrador del Sistema',
                'password'          => Hash::make('Admin@2026!'),
                'email_verified_at' => now(),
            ]
        );
        $adminUser->syncRoles([$admin]);
        // El admin también recibe permisos DIRECTOS para que hasDirectPermission() funcione.
        // Solo el admin tiene permisos directos automáticos. El resto de usuarios
        // solo reciben permisos directos a través de los toggles de módulo.
        $adminUser->syncPermissions(Permission::all());

        // Limpiar caché nuevamente al finalizar
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('✅ Roles y permisos creados correctamente.');
        $this->command->info('👤 Admin: admin@clinicanorte.com / Admin@2026!');
    }
}
