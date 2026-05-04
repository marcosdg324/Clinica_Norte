<?php

namespace App\Domains\Auth\Filament\Resources\PatientUserResource\Pages;

use App\Domains\Auth\Filament\Resources\PatientUserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPatientUser extends EditRecord
{
    protected static string $resource = PatientUserResource::class;

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
}
