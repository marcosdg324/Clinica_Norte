<?php

namespace App\Domains\Orders\Filament\Resources\OrderResource\Pages;

use App\Domains\Orders\Filament\Resources\OrderResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Validar que se haya seleccionado al menos un examen
    protected function beforeCreate(): void
    {
        $data = $this->form->getState();
        if (empty($data['exams'])) {
            Notification::make()
                ->title('Sin exámenes seleccionados')
                ->body('Debe seleccionar al menos un examen para crear la orden.')
                ->danger()
                ->send();
            $this->halt();
        }
    }

    // Sincronizar la relación many-to-many al crear
    protected function handleRecordCreation(array $data): Model
    {
        $examIds = array_map('intval', $data['exams'] ?? []);
        unset($data['exams']);

        $record = static::getModel()::create($data);
        $record->exams()->sync($examIds);

        return $record;
    }
}
