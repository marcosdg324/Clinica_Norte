<?php

namespace App\Domains\Auth\Filament\Resources;

use App\Domains\Auth\Filament\Resources\PatientUserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class PatientUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Usuario Paciente';

    protected static ?string $pluralModelLabel = 'Usuarios Pacientes';

    // ─── Scope: solo usuarios vinculados a un paciente ────────────────────────

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('patient');
    }

    // ─── Autorización ─────────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasDirectPermission('auth.access') ?? false;
    }

    public static function canCreate(): bool
    {
        return false; // Los usuarios paciente se crean automáticamente al registrar un paciente
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasDirectPermission('auth.access') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasDirectPermission('auth.access') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->hasDirectPermission('auth.access') ?? false;
    }

    // ─── Formulario (solo edición de credenciales) ────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Credenciales de Acceso al Portal')
                ->icon('heroicon-o-key')
                ->description('Solo se puede modificar el correo y la contraseña del usuario paciente. Los datos personales se editan desde el módulo Pacientes.')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre completo')
                        ->disabled(),

                    Forms\Components\TextInput::make('email')
                        ->label('Correo electrónico')
                        ->email()
                        ->required()
                        ->unique(table: 'users', column: 'email', ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('password')
                        ->label('Nueva contraseña')
                        ->password()
                        ->revealable()
                        ->helperText('Dejar en blanco para mantener la contraseña actual.')
                        ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null
                        )
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->minLength(8)
                        ->maxLength(12)
                        ->nullable(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Datos del Paciente Vinculado')
                ->icon('heroicon-o-heart')
                ->schema([
                    Forms\Components\Placeholder::make('patient_ci')
                        ->label('CI / Cédula')
                        ->content(fn (User $record): string => $record->patient?->ci ?? '—'),

                    Forms\Components\Placeholder::make('patient_name')
                        ->label('Nombre completo')
                        ->content(fn (User $record): string => $record->patient?->full_name ?? '—'),

                    Forms\Components\Placeholder::make('patient_phone')
                        ->label('Teléfono')
                        ->content(fn (User $record): string => $record->patient?->phone ?? '—'),

                    Forms\Components\Placeholder::make('patient_gender')
                        ->label('Género')
                        ->content(fn (User $record): string => match ($record->patient?->gender) {
                            'masculino' => 'Masculino',
                            'femenino' => 'Femenino',
                            default => '—',
                        }),
                ])
                ->columns(2),
        ]);
    }

    // ─── Tabla ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.ci')
                    ->label('CI / Cédula')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('Paciente')
                    ->searchable(['patients.first_name', 'patients.last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Correo (acceso portal)')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Correo copiado'),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verificado')
                    ->boolean()
                    ->getStateUsing(fn ($record) => (bool) $record->email_verified_at)
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Buscar por CI, nombre o correo...')
            ->filters([
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Verificación')
                    ->nullable()
                    ->trueLabel('Solo verificados')
                    ->falseLabel('Solo no verificados')
                    ->placeholder('Todos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar credenciales'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatientUsers::route('/'),
            'edit' => Pages\EditPatientUser::route('/{record}/edit'),
        ];
    }
}
