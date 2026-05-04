<?php

namespace App\Domains\Catalog\Filament\Resources\ExamCategoryResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ExamsRelationManager extends RelationManager
{
    protected static string $relationship = 'exams';

    protected static ?string $title = 'Exámenes Asignados';

    protected static ?string $modelLabel = 'Examen';

    protected static ?string $pluralModelLabel = 'Exámenes';

    // Solo lectura: el alta/edición de exámenes pertenece al módulo Órdenes
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre del Examen')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->colors([
                        'info' => 'laboratorio',
                        'warning' => 'imagen',
                    ]),

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('BOB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(60)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->searchPlaceholder('Buscar por nombre...')
            ->paginated([10, 25, 50]);
    }
}
