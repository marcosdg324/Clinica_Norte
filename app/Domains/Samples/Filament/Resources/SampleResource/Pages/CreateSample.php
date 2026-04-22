<?php

namespace App\Domains\Samples\Filament\Resources\SampleResource\Pages;

use App\Domains\Samples\Filament\Resources\SampleResource;
use App\Domains\Samples\Models\SampleStatusHistory;
use Filament\Resources\Pages\CreateRecord;

class CreateSample extends CreateRecord
{
    protected static string $resource = SampleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function afterCreate(): void
    {
        // Registrar el estado inicial en el historial
        SampleStatusHistory::create([
            'sample_id'  => $this->record->id,
            'old_status' => null,
            'new_status' => $this->record->status,
            'changed_by' => auth()->id(),
            'notes'      => 'Muestra creada.',
        ]);
    }
}
