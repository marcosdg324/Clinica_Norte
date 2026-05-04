<?php

namespace App\Domains\Auth\Filament\Resources\UserResource\Pages;

use App\Domains\Auth\Filament\Resources\UserResource;
use App\Domains\Auth\Models\Role;
use App\Domains\Auth\Traits\HasModulePermissions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use HasModulePermissions;

    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Tras crear el usuario, asigna el rol seleccionado y los permisos directos
     * correspondientes a los módulos activados en el formulario.
     */
    protected function afterCreate(): void
    {
        $roleId = $this->data['role_id'] ?? null;
        if ($roleId) {
            $role = Role::find($roleId);
            if ($role) {
                $this->getRecord()->syncRoles([$role]);
            }
        }
        $this->syncModulePermissions($this->getRecord());
    }
}
