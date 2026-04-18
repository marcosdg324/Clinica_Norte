<?php

namespace App\Domains\Auth\Traits;

use App\Domains\Auth\Models\Permission;
use Filament\Forms;

/**
 * HasModulePermissions
 *
 * Trait compartido por RoleResource y UserResource.
 * Centraliza:
 *  - La definición canónica de módulos y acciones.
 *  - La construcción del componente Toggle para cada módulo.
 *  - La lógica de llenado (afterFill) y sincronización (sync).
 *
 * Uso en Pages:
 *   use HasModulePermissions;
 *   …
 *   protected function afterFill(): void  { $this->fillModuleToggles($this->getRecord()); }
 *   protected function afterSave(): void  { $this->syncModulePermissions($this->getRecord()); }
 *   protected function afterCreate(): void { $this->syncModulePermissions($this->getRecord()); }
 */
trait HasModulePermissions
{
    /**
     * Módulos del sistema con etiqueta, ícono y los prefijos de permiso que los componen.
     *
     * El campo `prefixes` permite que un módulo "virtual" como `auth` agrupe
     * varios prefijos reales (users, roles, permissions).
     *
     * @return array<string, array{label: string, icon: string, prefixes: string[]}>
     */
    public static function moduleDefinitions(): array
    {
        return [
            'auth' => [
                'label'    => 'Administración (Auth)',
                'icon'     => 'heroicon-o-cog-6-tooth',
                'prefixes' => ['users', 'roles', 'permissions'],
            ],
            'patients' => [
                'label'    => 'Pacientes',
                'icon'     => 'heroicon-o-heart',
                'prefixes' => ['patients'],
            ],
            'orders' => [
                'label'    => 'Órdenes',
                'icon'     => 'heroicon-o-clipboard-document-list',
                'prefixes' => ['orders'],
            ],
            'samples' => [
                'label'    => 'Muestras',
                'icon'     => 'heroicon-o-beaker',
                'prefixes' => ['samples'],
            ],
            'results' => [
                'label'    => 'Resultados',
                'icon'     => 'heroicon-o-document-chart-bar',
                'prefixes' => ['results'],
            ],
            'billing' => [
                'label'    => 'Facturación',
                'icon'     => 'heroicon-o-banknotes',
                'prefixes' => ['billing'],
            ],
            'inventory' => [
                'label'    => 'Inventario',
                'icon'     => 'heroicon-o-archive-box',
                'prefixes' => ['inventory'],
            ],
            'notifications' => [
                'label'    => 'Notificaciones',
                'icon'     => 'heroicon-o-bell',
                'prefixes' => ['notifications'],
            ],
        ];
    }

    /** Acciones granulares estándar por módulo */
    public static function moduleActions(): array
    {
        return ['viewAny', 'view', 'create', 'update', 'delete'];
    }

    /**
     * Retorna los nombres de todos los permisos granulares de un módulo virtual.
     * Ej: modulePermissionNames('auth') →
     *     ['users.viewAny', 'users.view', ..., 'roles.viewAny', ..., 'permissions.delete']
     */
    public static function modulePermissionNames(string $moduleKey): array
    {
        $definition = static::moduleDefinitions()[$moduleKey] ?? null;
        if (!$definition) return [];

        $names = [];
        foreach ($definition['prefixes'] as $prefix) {
            foreach (static::moduleActions() as $action) {
                $names[] = "{$prefix}.{$action}";
            }
        }
        return $names;
    }

    /**
     * Construye el componente Toggle para un módulo dado.
     * El toggle es virtual (dehydrated: false) — la sincronización real
     * se hace en los hooks afterSave / afterCreate de las Pages.
     */
    public static function buildModuleToggle(string $moduleKey): Forms\Components\Toggle
    {
        $def = static::moduleDefinitions()[$moduleKey];

        $helperLabels = implode(' · ', static::moduleActions());

        return Forms\Components\Toggle::make("module_{$moduleKey}")
            ->label($def['label'])
            ->onColor('success')
            ->offColor('danger')
            ->onIcon('heroicon-m-check')
            ->offIcon('heroicon-m-x-mark')
            ->helperText($helperLabels)
            ->dehydrated(false)
            ->columnSpan(1);
    }

    /**
     * Rellena los toggles de módulos en $this->data leyendo los permisos del model.
     * Un toggle está ON si el model tiene AL MENOS UN permiso de ese módulo.
     *
     * @param \Illuminate\Database\Eloquent\Model $record  Role o User con relación permissions
     */
    protected function fillModuleToggles(mixed $record): void
    {
        // Solo permisos DIRECTOS del modelo (no heredados del rol).
        // Esto garantiza que los toggles reflejen únicamente lo que el admin
        // configuró explícitamente, sin incluir los permisos base del rol.
        $ownedPermissions = $record->permissions->pluck('name');

        foreach (array_keys(static::moduleDefinitions()) as $moduleKey) {
            $prefixes = static::moduleDefinitions()[$moduleKey]['prefixes'];

            $this->data["module_{$moduleKey}"] = $ownedPermissions->contains(
                fn ($p) => collect($prefixes)->contains(fn ($prefix) => str_starts_with($p, $prefix . '.'))
            );
        }
    }

    /**
     * Sincroniza los permisos granulares del model según los toggles activos.
     * Toggle ON  → asigna los 5 permisos del módulo (sin tocar los ya existentes de otros módulos).
     * Toggle OFF → quita los permisos del módulo.
     *
     * Usa givePermissionTo / revokePermissionTo para no pisar permisos de otros módulos.
     *
     * @param \Illuminate\Database\Eloquent\Model $record  Role o User
     */
    protected function syncModulePermissions(mixed $record): void
    {
        $formState = $this->data;

        $toGive   = [];
        $toRevoke = [];

        foreach (array_keys(static::moduleDefinitions()) as $moduleKey) {
            $names = static::modulePermissionNames($moduleKey);

            if ($formState["module_{$moduleKey}"] ?? false) {
                $toGive = array_merge($toGive, $names);
            } else {
                $toRevoke = array_merge($toRevoke, $names);
            }
        }

        if (!empty($toGive)) {
            $record->givePermissionTo(
                Permission::whereIn('name', $toGive)->get()
            );
        }

        if (!empty($toRevoke)) {
            $record->revokePermissionTo(
                Permission::whereIn('name', $toRevoke)->get()
            );
        }

        // Limpiar caché de Spatie
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
