<?php

namespace App\Domains\Auth\Filament\Resources;

use App\Domains\Auth\Filament\Resources\UserResource\Pages;
use App\Domains\Auth\Models\Role;
use App\Domains\Auth\Traits\HasModulePermissions;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    use HasModulePermissions;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Usuario del Sistema';

    protected static ?string $pluralModelLabel = 'Usuarios del Sistema';

    // ─── Scope: solo personal clínico (sin pacientes) ─────────────────────────

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereDoesntHave('patient');
    }

    // ─── Autorización ─────────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasDirectPermission('auth.access') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasDirectPermission('auth.access') ?? false;
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

    // ─── Formulario ──────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── Datos Personales ──────────────────────────────────────────────
            Forms\Components\Section::make('Datos Personales')
                ->icon('heroicon-o-user')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre completo')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label('Correo electrónico')
                        ->email()
                        ->required()
                        ->unique(table: 'users', column: 'email', ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('password')
                        ->label('Contraseña')
                        ->password()
                        ->revealable()
                        ->helperText('Mínimo 8 caracteres y máximo 12: mayúsculas, minúsculas, números y símbolos.')
                        ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null
                        )
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->minLength(8)
                        ->rules([
                            'regex:/[A-Z]/',
                            'regex:/[a-z]/',
                            'regex:/[0-9]/',
                            'regex:/[\W_]/',
                        ])
                        ->validationMessages([
                            'min_length' => 'La contraseña debe tener al menos 8 caracteres.',
                            'min' => 'La contraseña debe tener al menos 8 caracteres.',
                            'max_length' => 'La contraseña no puede superar los 12 caracteres.',
                            'max' => 'La contraseña no puede superar los 12 caracteres.',
                            'regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula, un número y un símbolo especial.',
                        ])
                        ->maxLength(12),

                    Forms\Components\TextInput::make('password_confirmation')
                        ->label('Confirmar contraseña')
                        ->password()
                        ->revealable()
                        ->dehydrated(false)
                        ->same('password')
                        ->required(fn (string $operation): bool => $operation === 'create'),
                ])
                ->columns(2),

            // ── Rol del usuario ───────────────────────────────────────────────
            Forms\Components\Section::make('Rol del Usuario')
                ->icon('heroicon-o-shield-check')
                ->description('El rol define el conjunto base de permisos del usuario. Los permisos por módulo (abajo) son adicionales y exclusivos para este usuario.')
                ->schema([
                    Forms\Components\Select::make('role_id')
                        ->label('Rol asignado')
                        ->options(
                            Role::whereNotIn('name', ['Paciente'])
                                ->orderBy('name')
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->native(false)
                        ->live()
                        ->required()
                        ->dehydrated(false)
                        ->validationMessages([
                            'required' => 'Debe asignar un rol al usuario.',
                        ])
                        ->afterStateUpdated(function (int|string|null $state, Set $set) {
                            if (! $state) {
                                foreach (array_keys(static::moduleDefinitions()) as $moduleKey) {
                                    $set("module_{$moduleKey}", false);
                                }

                                return;
                            }
                            $role = Role::with('permissions')->find($state);
                            if (! $role) {
                                return;
                            }
                            $rolePermissions = $role->permissions->pluck('name');
                            foreach (array_keys(static::moduleDefinitions()) as $moduleKey) {
                                $permName = static::modulePermissionName($moduleKey);
                                $set("module_{$moduleKey}", $rolePermissions->contains($permName));
                            }
                        }),
                ])
                ->columns(1),

            // ── Permisos por Módulo ────────────────────────────────────────────
            Forms\Components\Section::make('Permisos por Módulo')
                ->icon('heroicon-o-key')
                ->description('Activa o desactiva el acceso completo a cada módulo para este usuario de forma individual. Al activar un módulo se asignan automáticamente sus permisos granulares.')
                ->schema(
                    collect(array_keys(static::moduleDefinitions()))->map(
                        fn ($key) => static::buildModuleToggle($key)
                    )->toArray()
                )
                ->columns(4),
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
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Rol')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verificado')
                    ->boolean()
                    ->getStateUsing(fn ($record) => (bool) $record->email_verified_at)
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->label('Filtrar por rol')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
