<?php

namespace App\Domains\Orders\Filament\Resources;

use App\Domains\Orders\Filament\Resources\ExamResource\Pages;
use App\Domains\Orders\Models\Exam;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static ?string $navigationIcon  = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = 'Órdenes y Exámenes';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $modelLabel       = 'Examen';
    protected static ?string $pluralModelLabel = 'Exámenes';

    // ─── Autorización ─────────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasDirectPermission('exams.viewAny') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasDirectPermission('exams.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasDirectPermission('exams.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasDirectPermission('exams.delete') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->hasDirectPermission('exams.view') ?? false;
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

                    Forms\Components\Select::make('type')
                        ->label('Tipo')
                        ->required()
                        ->options([
                            'laboratorio' => 'Laboratorio',
                            'imagen'      => 'Imagen',
                        ])
                        ->native(false),

                    Forms\Components\TextInput::make('price')
                        ->label('Precio (Bs.)')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Bs.')
                        ->placeholder('0.00'),

                    Forms\Components\Toggle::make('is_urgent_possible')
                        ->label('¿Puede realizarse con urgencia?')
                        ->onColor('warning')
                        ->offColor('gray')
                        ->default(false),

                    Forms\Components\Textarea::make('description')
                        ->label('Descripción')
                        ->nullable()
                        ->rows(3)
                        ->maxLength(2000)
                        ->placeholder('Descripción del examen, indicaciones, etc.')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            // ── Requisitos previos ─────────────────────────────────────────────
            Forms\Components\Section::make('Requisitos previos')
                ->icon('heroicon-o-clipboard-document-check')
                ->description('Condiciones que el paciente debe cumplir antes del examen.')
                ->schema([
                    Forms\Components\Repeater::make('requirements')
                        ->label('')
                        ->relationship('requirements')
                        ->schema([
                            Forms\Components\Textarea::make('description')
                                ->label('Requisito')
                                ->required()
                                ->rows(2)
                                ->maxLength(500)
                                ->placeholder('Ej: Ayunas de 8 horas mínimo'),
                        ])
                        ->addActionLabel('Agregar requisito')
                        ->reorderable(false)
                        ->collapsible()
                        ->columnSpanFull(),
                ]),
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

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'laboratorio' => 'Laboratorio',
                        'imagen'      => 'Imagen',
                        default       => ucfirst($state),
                    })
                    ->colors([
                        'info'    => 'laboratorio',
                        'success' => 'imagen',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('BOB')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_urgent_possible')
                    ->label('Urgencia')
                    ->boolean()
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->trueIcon('heroicon-o-bolt')
                    ->falseIcon('heroicon-o-minus-circle'),

                Tables\Columns\TextColumn::make('requirements.description')
                    ->label('Requisitos previos')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->placeholder('—')
                    ->wrap()
                    ->toggleable()
                    ->visible(fn () => ! auth()->user()?->hasRole('Bioquímico')),

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
                        'imagen'      => 'Imagen',
                    ]),

                Tables\Filters\TernaryFilter::make('is_urgent_possible')
                    ->label('¿Urgencia posible?')
                    ->trueLabel('Sí')
                    ->falseLabel('No'),

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
            'index'  => Pages\ListExams::route('/'),
            'create' => Pages\CreateExam::route('/create'),
            'view'   => Pages\ViewExam::route('/{record}'),
            'edit'   => Pages\EditExam::route('/{record}/edit'),
        ];
    }
}
