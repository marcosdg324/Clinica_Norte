<?php

namespace App\Domains\Imaging\Filament\Resources\ImagingEquipmentResource\Pages;

use App\Domains\Imaging\Filament\Resources\ImagingEquipmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateImagingEquipment extends CreateRecord
{
    protected static string $resource = ImagingEquipmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
