<?php

namespace App\Domains\Patients\Filament\Resources\PatientResource\Pages;

use App\Domains\Patients\Filament\Resources\PatientResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListPatients extends ListRecords
{
    protected static string $resource = PatientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
