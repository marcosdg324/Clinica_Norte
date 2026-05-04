<?php

namespace App\Domains\Auth\Filament\Resources\PatientUserResource\Pages;

use App\Domains\Auth\Filament\Resources\PatientUserResource;
use Filament\Resources\Pages\ListRecords;

class ListPatientUsers extends ListRecords
{
    protected static string $resource = PatientUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Sin botón "Crear" — los usuarios paciente se crean desde el módulo Pacientes
        ];
    }
}
