<?php

namespace App\Domains\Auth\Traits;

use App\Domains\Auth\Models\Permission;
use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\PermissionRegistrar;

/**
 * HasModulePermissions
 *
 * Trait compartido por UserResource y sus Pages.
 * Centraliza:
 *  - La definición de módulos del sistema (uno por módulo).
 *  - La construcción del toggle visual por módulo.
 *  - La lógica de llenado (afterFill) y sincronización (afterSave/afterCreate).
 *
 * Cada módulo tiene UN único permiso: "modulo.access".
 * El administrador puede asignar/revocar permisos directamente a cada usuario,
 * independientemente de su rol.
 */
trait HasModulePermissions
{
    /**
     * Definición de módulos del sistema.
     * Cada módulo corresponde a un único permiso: "moduleKey.access".
     *
     * @return array<string, array{label: string, icon: string}>
     */
    public static function moduleDefinitions(): array
    {
        return [
            'auth' => [
                'label' => 'Administración (Auth)',
                'icon' => 'heroicon-o-cog-6-tooth',
            ],
            'patients' => [
                'label' => 'Pacientes',
                'icon' => 'heroicon-o-heart',
            ],
            'orders' => [
                'label' => 'Órdenes',
                'icon' => 'heroicon-o-clipboard-document-list',
            ],
            'samples' => [
                'label' => 'Muestras',
                'icon' => 'heroicon-o-beaker',
            ],
            'results' => [
                'label' => 'Resultados',
                'icon' => 'heroicon-o-document-chart-bar',
            ],
            'payments' => [
                'label' => 'Pagos',
                'icon' => 'heroicon-o-banknotes',
            ],
            'reactivos' => [
                'label' => 'Reactivos',
                'icon' => 'heroicon-o-archive-box',
            ],
            'notifications' => [
                'label' => 'Notificaciones',
                'icon' => 'heroicon-o-bell',
            ],
            'catalog' => [
                'label' => 'Catálogo',
                'icon' => 'heroicon-o-book-open',
            ],
            'imaging' => [
                'label' => 'Imágenes',
                'icon' => 'heroicon-o-photo',
            ],
        ];
    }

    /**
     * Retorna el nombre del único permiso que controla el acceso a un módulo.
     * Ej: modulePermissionName('patients') → 'patients.access'
     */
    public static function modulePermissionName(string $moduleKey): string
    {
        return "{$moduleKey}.access";
    }

    /**
     * Construye el componente Toggle para un módulo dado.
     * El toggle es virtual (dehydrated: false) — la sincronización real
     * se hace en los hooks afterSave / afterCreate de las Pages.
     */
    public static function buildModuleToggle(string $moduleKey): Forms\Components\Toggle
    {
        $def = static::moduleDefinitions()[$moduleKey];

        return Forms\Components\Toggle::make("module_{$moduleKey}")
            ->label($def['label'])
            ->onColor('success')
            ->offColor('danger')
            ->onIcon('heroicon-m-check')
            ->offIcon('heroicon-m-x-mark')
            ->helperText("{$moduleKey}.access")
            ->dehydrated(false)
            ->columnSpan(1);
    }

    /**
     * Rellena los toggles en $this->data leyendo los permisos DIRECTOS del usuario.
     * Toggle ON = el usuario tiene el permiso "modulo.access" asignado directamente.
     *
     * @param  Model  $record  User con relación permissions
     */
    protected function fillModuleToggles(mixed $record): void
    {
        $ownedPermissions = $record->permissions->pluck('name');

        foreach (array_keys(static::moduleDefinitions()) as $moduleKey) {
            $this->data["module_{$moduleKey}"] = $ownedPermissions->contains(
                static::modulePermissionName($moduleKey)
            );
        }
    }

    /**
     * Sincroniza los permisos directos del usuario según el estado de los toggles.
     *
     * Toggle ON  → givePermissionTo("modulo.access")
     * Toggle OFF → revokePermissionTo("modulo.access")
     *
     * Adicionalmente, los permisos especiales (ej: samples.approve, samples.reject)
     * se sincronizan automáticamente desde los roles asignados al usuario,
     * ya que el sistema usa hasDirectPermission() para todos los checks.
     */
    protected function syncModulePermissions(mixed $record): void
    {
        $formState = $this->data;

        $toGive = [];
        $toRevoke = [];

        foreach (array_keys(static::moduleDefinitions()) as $moduleKey) {
            $permName = static::modulePermissionName($moduleKey);

            if ($formState["module_{$moduleKey}"] ?? false) {
                $toGive[] = $permName;
            } else {
                $toRevoke[] = $permName;
            }
        }

        if (! empty($toGive)) {
            $record->givePermissionTo(
                Permission::whereIn('name', $toGive)->get()
            );
        }

        if (! empty($toRevoke)) {
            $record->revokePermissionTo(
                Permission::whereIn('name', $toRevoke)->get()
            );
        }

        // ── Sincronizar permisos especiales desde los roles del usuario ────────
        // Como usamos hasDirectPermission() en resources/policies, los permisos
        // especiales (que no son *.access) deben existir también como directos.
        $record->load('roles.permissions');

        $specialPermsFromRole = $record->roles
            ->flatMap(fn ($role) => $role->permissions->pluck('name'))
            ->filter(fn (string $name) => ! str_ends_with($name, '.access'))
            ->unique()
            ->values()
            ->toArray();

        $allSpecialPermNames = Permission::where('name', 'not like', '%.access')
            ->pluck('name')
            ->toArray();

        $specialPermsToRevoke = array_diff($allSpecialPermNames, $specialPermsFromRole);

        if (! empty($specialPermsFromRole)) {
            $record->givePermissionTo(
                Permission::whereIn('name', $specialPermsFromRole)->get()
            );
        }

        if (! empty($specialPermsToRevoke)) {
            $record->revokePermissionTo(
                Permission::whereIn('name', $specialPermsToRevoke)->get()
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
