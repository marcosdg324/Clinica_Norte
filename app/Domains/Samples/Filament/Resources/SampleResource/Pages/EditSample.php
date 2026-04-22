<?php

namespace App\Domains\Samples\Filament\Resources\SampleResource\Pages;

use App\Domains\Samples\Filament\Resources\SampleResource;
use App\Domains\Samples\Models\SampleStatusHistory;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSample extends EditRecord
{
    protected static string $resource = SampleResource::class;

    protected ?string $previousStatus = null;

    protected function beforeSave(): void
    {
        $this->previousStatus = $this->record->status;
    }

    protected function afterSave(): void
    {
        $newStatus = $this->record->status;

        if ($this->previousStatus !== $newStatus) {
            SampleStatusHistory::create([
                'sample_id'  => $this->record->id,
                'old_status' => $this->previousStatus,
                'new_status' => $newStatus,
                'changed_by' => auth()->id(),
                'notes'      => 'Estado actualizado desde formulario de edición.',
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
