<?php

namespace App\Domains\Auth\Filament\Resources\UserResource\Pages;

use App\Domains\Auth\Filament\Resources\UserResource;
use App\Domains\Auth\Traits\HasModulePermissions;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    use HasModulePermissions;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Pre-rellena los toggles de módulo leyendo los permisos directos del usuario.
     * Toggle ON = el usuario tiene al menos un permiso del módulo de forma directa.
     */
    protected function afterFill(): void
    {
        $this->fillModuleToggles($this->getRecord());
    }

    /**
     * Tras guardar el usuario, sincroniza sus permisos directos
     * según los toggles activos en el formulario.
     */
    protected function afterSave(): void
    {
        $this->syncModulePermissions($this->getRecord());
    }
}
