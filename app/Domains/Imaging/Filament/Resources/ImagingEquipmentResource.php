<?php

namespace App\Domains\Imaging\Filament\Resources;

use App\Domains\Imaging\Filament\Resources\ImagingEquipmentResource\Pages;
use App\Domains\Imaging\Models\ImagingEquipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ImagingEquipmentResource extends Resource
{
    protected static ?string $model = ImagingEquipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $navigationGroup = 'Estudios de Imagen';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Equipo';

    protected static ?string $pluralModelLabel = 'Equipos de imagen';

    // ─── Autorización ─────────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasDirectPermission('imaging.access') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasDirectPermission('imaging.access') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasDirectPermission('imaging.access') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasDirectPermission('imaging.access') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->hasDirectPermission('imaging.access') ?? false;
    }

    // ─── Formulario ──────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Información del equipo')
                ->icon('heroicon-o-computer-desktop')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre del equipo')
                        ->required()
                        ->prefixIcon('heroicon-o-identification')
                        ->maxLength(255)
                        ->validationMessages(['required' => 'El nombre del equipo es obligatorio.']),

                    Forms\Components\Select::make('type')
                        ->label('Tipo de equipo')
                        ->required()
                        ->native(false)
                        ->options([
                            'ecógrafo' => 'Ecógrafo',
                            'rayos_x' => 'Rayos X',
                            'tomógrafo' => 'Tomógrafo',
                            'otro' => 'Otro',
                        ])
                        ->validationMessages(['required' => 'Debe seleccionar el tipo de equipo.']),

                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->required()
                        ->native(false)
                        ->default('disponible')
                        ->options([
                            'disponible' => 'Disponible',
                            'mantenimiento' => 'En mantenimiento',
                            'fuera_de_servicio' => 'Fuera de servicio',
                        ])
                        ->validationMessages(['required' => 'Debe seleccionar el estado del equipo.']),

                    Forms\Components\Textarea::make('description')
                        ->label('Descripción')
                        ->nullable()
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    // ─── Tabla ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Equipo')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'ecógrafo' => 'Ecógrafo',
                        'rayos_x' => 'Rayos X',
                        'tomógrafo' => 'Tomógrafo',
                        'otro' => 'Otro',
                        default => ucfirst($state),
                    })
                    ->colors([
                        'info' => 'ecógrafo',
                        'warning' => 'rayos_x',
                        'success' => 'tomógrafo',
                        'gray' => 'otro',
                    ])
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'disponible' => 'Disponible',
                        'mantenimiento' => 'En mantenimiento',
                        'fuera_de_servicio' => 'Fuera de servicio',
                        default => ucfirst($state),
                    })
                    ->colors([
                        'success' => 'disponible',
                        'warning' => 'mantenimiento',
                        'danger' => 'fuera_de_servicio',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'ecógrafo' => 'Ecógrafo',
                        'rayos_x' => 'Rayos X',
                        'tomógrafo' => 'Tomógrafo',
                        'otro' => 'Otro',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'disponible' => 'Disponible',
                        'mantenimiento' => 'En mantenimiento',
                        'fuera_de_servicio' => 'Fuera de servicio',
                    ])
                    ->native(false),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImagingEquipment::route('/'),
            'create' => Pages\CreateImagingEquipment::route('/create'),
            'edit' => Pages\EditImagingEquipment::route('/{record}/edit'),
        ];
    }
}
