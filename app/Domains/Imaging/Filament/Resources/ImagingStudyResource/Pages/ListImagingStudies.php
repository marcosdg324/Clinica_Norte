<?php

namespace App\Domains\Imaging\Filament\Resources\ImagingStudyResource\Pages;

use App\Domains\Imaging\Filament\Resources\ImagingStudyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListImagingStudies extends ListRecords
{
    protected static string $resource = ImagingStudyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
