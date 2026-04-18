<?php

namespace App\Domains\Auth\Filament\Resources\UserResource\Pages;

use App\Domains\Auth\Filament\Resources\UserResource;
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
     * Tras crear el usuario, asigna los permisos directos
     * correspondientes a los módulos activados en el formulario.
     */
    protected function afterCreate(): void
    {
        $this->syncModulePermissions($this->getRecord());
    }
}
