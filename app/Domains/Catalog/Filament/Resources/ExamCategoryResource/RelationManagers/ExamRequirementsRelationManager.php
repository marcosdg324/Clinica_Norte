<?php

namespace App\Domains\Catalog\Filament\Resources\ExamCategoryResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ExamRequirementsRelationManager extends RelationManager
{
    protected static string $relationship = 'requirements';

    protected static ?string $title = 'Requisitos Previos';

    protected static ?string $modelLabel = 'Requisito';

    protected static ?string $pluralModelLabel = 'Requisitos Previos';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->label('')
                    ->searchable()
                    ->limit(80)
                    ->wrap(),
            ])
            ->groups([
                Tables\Grouping\Group::make('exam.name')
                    ->label('Examen')
                    ->titlePrefixedWithLabel(false)
                    ->collapsible(),
            ])
            ->defaultGroup('exam.name')
            ->defaultSort('exam_id', 'asc')
            ->emptyStateHeading('Sin requisitos registrados')
            ->emptyStateDescription('Gestiona los requisitos desde cada examen en el módulo de Órdenes y Exámenes.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }
}
