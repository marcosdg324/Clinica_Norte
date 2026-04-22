<?php

namespace App\Domains\Orders\Filament\Resources\ExamResource\Pages;

use App\Domains\Orders\Filament\Resources\ExamResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExam extends CreateRecord
{
    protected static string $resource = ExamResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
