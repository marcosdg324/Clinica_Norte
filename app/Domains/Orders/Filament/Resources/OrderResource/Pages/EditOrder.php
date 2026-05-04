<?php

namespace App\Domains\Orders\Filament\Resources\OrderResource\Pages;

use App\Domains\Orders\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Cargar los IDs de exámenes existentes al abrir el formulario
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['exams'] = $this->record->exams()->pluck('exams.id')->map(fn ($id) => (int) $id)->toArray();

        return $data;
    }

    // Sincronizar la relación many-to-many al guardar
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $examIds = array_map('intval', $data['exams'] ?? []);
        unset($data['exams']);

        $record->update($data);
        $record->exams()->sync($examIds);

        return $record;
    }
}
