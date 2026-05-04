<?php

namespace App\Domains\Catalog\Filament\Resources;

use App\Domains\Catalog\Filament\Resources\ExamCategoryResource\Pages;
use App\Domains\Catalog\Filament\Resources\ExamCategoryResource\RelationManagers;
use App\Domains\Catalog\Models\ExamCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExamCategoryResource extends Resource
{
    protected static ?string $model = ExamCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Catálogo';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Categoría de Examen';

    protected static ?string $pluralModelLabel = 'Catálogo de Exámenes';

    // ─── Autorización ─────────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasDirectPermission('catalog.access') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasDirectPermission('catalog.access') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasDirectPermission('catalog.access') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasDirectPermission('catalog.access') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->hasDirectPermission('catalog.access') ?? false;
    }

    // ─── Formulario ──────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── Datos de la Categoría ─────────────────────────────────────────
            Forms\Components\Section::make('Datos de la Categoría')
                ->icon('heroicon-o-book-open')
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label('Tipo')
                        ->required()
                        ->options([
                            'laboratorio' => 'Laboratorio',
                            'imagen' => 'Imagen',
                        ])
                        ->native(false)
                        ->prefixIcon('heroicon-o-tag'),

                    Forms\Components\TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(255)
                        ->unique(table: 'exam_categories', column: 'name', ignoreRecord: true)
                        ->validationMessages([
                            'required' => 'El nombre de la categoría es obligatorio.',
                            'unique' => 'Ya existe una categoría con ese nombre.',
                        ])
                        ->placeholder('Ej: Hemograma completo'),

                    Forms\Components\Textarea::make('description')
                        ->label('Descripción')
                        ->nullable()
                        ->rows(3)
                        ->maxLength(1000)
                        ->placeholder('Descripción clínica de la categoría...')
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Activa')
                        ->default(true)
                        ->helperText('Las categorías inactivas no aparecen en nuevas órdenes.'),
                ])
                ->columns(2),
        ]);
    }

    // ─── Tabla ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->colors([
                        'info' => 'laboratorio',
                        'warning' => 'imagen',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(60)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('exams_count')
                    ->label('Exámenes')
                    ->counts('exams')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('parameters_count')
                    ->label('Parámetros')
                    ->counts('parameters')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('requirements_count')
                    ->label('Requisitos')
                    ->counts('requirements')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->searchPlaceholder('Buscar por nombre...')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'laboratorio' => 'Laboratorio',
                        'imagen' => 'Imagen',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Solo activas')
                    ->falseLabel('Solo inactivas')
                    ->placeholder('Todas'),

                Tables\Filters\TrashedFilter::make()
                    ->label('Incluir eliminadas'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public static function getRelations(): array
    {
        return [
            RelationManagers\ExamParametersRelationManager::class,
            RelationManagers\ExamRequirementsRelationManager::class,
            RelationManagers\ExamsRelationManager::class,
        ];
    }

    // ─── Páginas ─────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExamCategories::route('/'),
            'create' => Pages\CreateExamCategory::route('/create'),
            'view' => Pages\ViewExamCategory::route('/{record}'),
            'edit' => Pages\EditExamCategory::route('/{record}/edit'),
        ];
    }
}
