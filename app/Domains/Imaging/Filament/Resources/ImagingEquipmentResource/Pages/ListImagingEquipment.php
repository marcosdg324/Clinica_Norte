<?php

namespace App\Domains\Imaging\Filament\Resources\ImagingEquipmentResource\Pages;

use App\Domains\Imaging\Filament\Resources\ImagingEquipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListImagingEquipment extends ListRecords
{
    protected static string $resource = ImagingEquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
