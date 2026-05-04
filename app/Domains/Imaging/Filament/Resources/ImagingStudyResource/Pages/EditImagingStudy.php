<?php

namespace App\Domains\Imaging\Filament\Resources\ImagingStudyResource\Pages;

use App\Domains\Imaging\Filament\Resources\ImagingStudyResource;
use App\Domains\Imaging\Models\ImagingStatusHistory;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditImagingStudy extends EditRecord
{
    protected static string $resource = ImagingStudyResource::class;

    protected ?string $previousStatus = null;

    protected function beforeSave(): void
    {
        $this->previousStatus = $this->record->status;
    }

    protected function afterSave(): void
    {
        $newStatus = $this->record->status;

        if ($this->previousStatus !== $newStatus) {
            ImagingStatusHistory::create([
                'imaging_study_id' => $this->record->id,
                'old_status' => $this->previousStatus,
                'new_status' => $newStatus,
                'changed_by' => auth()->id(),
                'notes' => 'Estado actualizado desde formulario de edición.',
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
}
