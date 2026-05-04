<?php

namespace App\Domains\Imaging\Filament\Resources\ImagingStudyResource\Pages;

use App\Domains\Imaging\Filament\Resources\ImagingStudyResource;
use App\Domains\Imaging\Models\ImagingStatusHistory;
use Filament\Resources\Pages\CreateRecord;

class CreateImagingStudy extends CreateRecord
{
    protected static string $resource = ImagingStudyResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function afterCreate(): void
    {
        // Registrar el estado inicial en el historial
        ImagingStatusHistory::create([
            'imaging_study_id' => $this->record->id,
            'old_status' => null,
            'new_status' => $this->record->status,
            'changed_by' => auth()->id(),
            'notes' => 'Estudio de imagen creado.',
        ]);
    }
}
