<?php

namespace App\Domains\Catalog\Filament\Resources\ExamCategoryResource\Pages;

use App\Domains\Catalog\Filament\Resources\ExamCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExamCategories extends ListRecords
{
    protected static string $resource = ExamCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
