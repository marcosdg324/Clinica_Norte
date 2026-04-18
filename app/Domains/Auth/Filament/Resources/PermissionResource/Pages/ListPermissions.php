<?php

namespace App\Domains\Auth\Filament\Resources\PermissionResource\Pages;

use App\Domains\Auth\Filament\Resources\PermissionResource;
use App\Domains\Auth\Models\Permission;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getSubheading(): ?string
    {
        $total = Permission::count();
        return "Total de permisos registrados en el sistema: {$total}";
    }
}
