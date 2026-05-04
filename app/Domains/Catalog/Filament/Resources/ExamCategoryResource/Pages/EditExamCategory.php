<?php

namespace App\Domains\Catalog\Filament\Resources\ExamCategoryResource\Pages;

use App\Domains\Catalog\Filament\Resources\ExamCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExamCategory extends EditRecord
{
    protected static string $resource = ExamCategoryResource::class;

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
}
