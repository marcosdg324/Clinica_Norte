<?php

namespace App\Domains\Catalog\Filament\Resources;

use App\Domains\Catalog\Filament\Resources\ExamResource\Pages;
use App\Domains\Catalog\Models\Exam;
use App\Domains\Catalog\Models\ExamCategory;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Catálogo';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Examen';

    protected static ?string $pluralModelLabel = 'Exámenes';

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

            // ── Datos del Examen ───────────────────────────────────────────────
            Forms\Components\Section::make('Datos del Examen')
                ->icon('heroicon-o-beaker')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre del examen')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ej: Hemograma completo')
                        ->prefixIcon('heroicon-o-document-text'),

                    Forms\Components\Select::make('exam_category_id')
                        ->label('Categoría del catálogo')
                        ->relationship('category', 'name')
                        ->options(
                            ExamCategory::where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->nullable()
                        ->placeholder('Sin categoría vinculada')
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state) {
                                $category = ExamCategory::find($state);
                                if ($category) {
                                    $set('type', $category->type);
                                }
                            }
                        })
                        ->helperText('Vincula este examen con su definición en el Catálogo.'),

                    Forms\Components\Select::make('type')
                        ->label('Tipo')
                        ->required()
                        ->options([
                            'laboratorio' => 'Laboratorio',
                            'imagen' => 'Imagen',
                        ])
                        ->native(false)
                        ->disabled(fn (Forms\Get $get): bool => filled($get('exam_category_id')))
                        ->dehydrated(true)
                        ->helperText(fn (Forms\Get $get): string => filled($get('exam_category_id'))
                            ? 'Heredado de la categoría seleccionada.'
                            : 'Selecciona un tipo o vincula una categoría del catálogo.'),

                    Forms\Components\TextInput::make('price')
                        ->label('Precio (Bs.)')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Bs.')
                        ->placeholder('0.00'),
                ])
                ->columns(2),

            // ── Requisitos Previos ─────────────────────────────────────────────
            Forms\Components\Section::make('Requisitos Previos')
                ->icon('heroicon-o-clipboard-document-list')
                ->description('Define los requisitos que el paciente debe cumplir antes de realizarse este examen.')
                ->schema([
                    Forms\Components\Repeater::make('requirements')
                        ->relationship('requirements')
                        ->label('')
                        ->schema([
                            Forms\Components\TextInput::make('description')
                                ->hiddenLabel()
                                ->required()
                                ->maxLength(500)
                                ->placeholder('Ej: Ayuno de 8 horas')
                                ->columnSpanFull(),
                        ])
                        ->columns(1)
                        ->addActionLabel('Agregar requisito')
                        ->reorderable(false)
                        ->collapsible()
                        ->collapsed()
                        ->itemLabel(fn (array $state): ?string => $state['description'] ?? 'Nuevo requisito')
                        ->expandAction(fn (Action $action) => $action->icon('heroicon-m-pencil-square')->label(''))
                        ->collapseAction(fn (Action $action) => $action->icon('heroicon-m-pencil-square')->label(''))
                        ->collapseAllAction(fn (Action $action) => $action->hidden())
                        ->expandAllAction(fn (Action $action) => $action->hidden())
                        ->defaultItems(0),
                ])
                ->collapsible(),

        ]);
    }

    // ─── Tabla ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->placeholder('—')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'laboratorio' => 'Laboratorio',
                        'imagen' => 'Imagen',
                        default => ucfirst($state),
                    })
                    ->colors([
                        'info' => 'laboratorio',
                        'success' => 'imagen',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('BOB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'laboratorio' => 'Laboratorio',
                        'imagen' => 'Imagen',
                    ]),

                Tables\Filters\TrashedFilter::make(),
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
            ])
            ->defaultSort('name', 'asc');
    }

    // ─── Páginas ──────────────────────────────────────────────────────────────

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExams::route('/'),
            'create' => Pages\CreateExam::route('/create'),
            'view' => Pages\ViewExam::route('/{record}'),
            'edit' => Pages\EditExam::route('/{record}/edit'),
        ];
    }
}
