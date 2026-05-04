<?php

namespace App\Domains\Catalog\Filament\Resources\ExamCategoryResource\Pages;

use App\Domains\Catalog\Filament\Resources\ExamCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExamCategory extends CreateRecord
{
    protected static string $resource = ExamCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
