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
     * Permisos de acceso por módulo (uno por módulo = acceso completo al módulo).
     * El administrador puede asignar estos permisos directamente a cualquier usuario.
     */
    private array $modulePermissions = [
        'auth.access',
        'patients.access',
        'orders.access',
        'samples.access',
        'results.access',
        'payments.access',
        'reactivos.access',
        'notifications.access',
        'catalog.access',
        'imaging.access',
    ];

    /**
     * Permisos especiales para acciones específicas que no representan
     * acceso completo a un módulo, sino operaciones puntuales.
     */
    private array $specialPermissions = [
        'samples.approve',
        'samples.reject',
        'imaging.approve',
    ];

    public function run(): void
    {
        // Limpiar caché de permisos antes de comenzar
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── 1. CREAR PERMISOS DEL SISTEMA ───────────────────────────────────
        foreach (array_merge($this->modulePermissions, $this->specialPermissions) as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ─── 2. CREAR LOS ROLES BASE ───────────────────────────────────────────
        $admin = Role::firstOrCreate(['name' => 'Administrador',        'guard_name' => 'web']);
        $recepcionista = Role::firstOrCreate(['name' => 'Recepcionista',        'guard_name' => 'web']);
        $bioquimico = Role::firstOrCreate(['name' => 'Bioquímico',           'guard_name' => 'web']);
        $tecnologoImagen = Role::firstOrCreate(['name' => 'Tecnólogo de Imagen',  'guard_name' => 'web']);
        $medico = Role::firstOrCreate(['name' => 'Médico',               'guard_name' => 'web']);
        $paciente = Role::firstOrCreate(['name' => 'Paciente',             'guard_name' => 'web']);

        // ─── 3. ASIGNAR PERMISOS POR ROL ─────────────────────────────────────

        // Administrador: acceso completo a todos los módulos
        $admin->syncPermissions(Permission::all());

        // Recepcionista: pacientes, órdenes, pagos, muestras (registro), imagen (registro), notificaciones
        $recepcionista->syncPermissions(Permission::whereIn('name', [
            'patients.access',
            'orders.access',
            'payments.access',
            'samples.access',
            'imaging.access',
            'notifications.access',
        ])->get());

        // Bioquímico: laboratorio (muestras, resultados, reactivos, etc.).
        // El módulo de imagen lo cubre el rol Tecnólogo de Imagen.
        $bioquimico->syncPermissions(Permission::whereIn('name', [
            'samples.access',
            'samples.approve',
            'samples.reject',
            'results.access',
            'reactivos.access',
            'patients.access',
            'orders.access',
            'notifications.access',
        ])->get());

        // Tecnólogo de Imagen: imagen + los mismos accesos “transversales” que el Bioquímico
        // (pacientes, órdenes, notificaciones), más catálogo para mantener exámenes de imagen.
        // No incluye muestras / resultados / reactivos (solo laboratorio).
        $tecnologoImagen->syncPermissions(Permission::whereIn('name', [
            'imaging.access',
            'imaging.approve',
            'patients.access',
            'orders.access',
            'catalog.access',
            'notifications.access',
        ])->get());

        // Usuarios que ya tenían el rol: asegurar permisos directos nuevos (el panel usa hasDirectPermission).
        $tecnologoDirect = Permission::whereIn('name', [
            'patients.access',
            'orders.access',
            'catalog.access',
            'notifications.access',
            'imaging.access',
            'imaging.approve',
        ])->get();
        foreach (User::role('Tecnólogo de Imagen')->cursor() as $user) {
            if ($tecnologoDirect->isNotEmpty()) {
                $user->givePermissionTo($tecnologoDirect);
            }
        }

        // Médico: pacientes, órdenes (crea y consulta), resultados, notificaciones
        $medico->syncPermissions(Permission::whereIn('name', [
            'patients.access',
            'orders.access',
            'results.access',
            'notifications.access',
        ])->get());

        // Paciente: solo consulta sus propios resultados (la Policy filtra por ID)
        $paciente->syncPermissions(Permission::whereIn('name', [
            'results.access',
        ])->get());

        // ─── 4. CREAR USUARIO ADMINISTRADOR POR DEFECTO ───────────────────────
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@clinicanorte.com'],
            [
                'name' => 'Administrador del Sistema',
                'password' => Hash::make('Admin@2026!'),
                'email_verified_at' => now(),
            ]
        );
        $adminUser->syncRoles([$admin]);

        // El admin recibe TODOS los permisos directamente, además de por rol,
        // para que hasPermissionTo() resuelva sin depender del rol.
        $adminUser->syncPermissions(Permission::all());

        // Limpiar caché nuevamente al finalizar
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('✅ Roles y permisos (basados en módulos) creados correctamente.');
        $this->command->info('👤 Admin: admin@clinicanorte.com / Admin@2026!');
    }
}
