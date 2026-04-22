<?php

namespace App\Domains\Orders\Filament\Resources\ExamResource\Pages;

use App\Domains\Orders\Filament\Resources\ExamResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListExams extends ListRecords
{
    protected static string $resource = ExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
