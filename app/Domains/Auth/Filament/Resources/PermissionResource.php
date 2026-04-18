<?php

namespace App\Domains\Auth\Filament\Resources;

use App\Domains\Auth\Filament\Resources\PermissionResource\Pages;
use App\Domains\Auth\Models\Permission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $modelLabel         = 'Permiso';
    protected static ?string $pluralModelLabel   = 'Permisos';

    // ─── Autorización ─────────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasDirectPermission('permissions.viewAny') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    // ─── Formulario ──────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos del Permiso')
                ->icon('heroicon-o-key')
                ->description('El nombre debe seguir la convención: modulo.accion (ej: patients.view)')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre del permiso')
                        ->placeholder('modulo.accion  (ej: patients.view)')
                        ->required()
                        ->unique(table: 'permissions', column: 'name', ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('guard_name')
                        ->label('Guard')
                        ->default('web')
                        ->required()
                        ->maxLength(255),
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
                    ->label('Permiso')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function (string $state): string {
                        $moduleMap = [
                            'users'         => 'Usuarios',
                            'roles'         => 'Roles',
                            'permissions'   => 'Permisos',
                            'patients'      => 'Pacientes',
                            'orders'        => 'Órdenes',
                            'samples'       => 'Muestras',
                            'results'       => 'Resultados',
                            'billing'       => 'Facturación',
                            'inventory'     => 'Inventario',
                            'notifications' => 'Notificaciones',
                        ];
                        $actionMap = [
                            'viewAny' => 'Ver listado',
                            'view'    => 'Ver detalle',
                            'create'  => 'Crear',
                            'update'  => 'Editar',
                            'delete'  => 'Eliminar',
                        ];
                        [$mod, $action] = array_pad(explode('.', $state, 2), 2, '');
                        $modLabel    = $moduleMap[$mod]    ?? ucfirst($mod);
                        $actionLabel = $actionMap[$action] ?? ucfirst($action);
                        return "{$modLabel} → {$actionLabel}";
                    }),

                Tables\Columns\TextColumn::make('module')
                    ->label('Módulo')
                    ->getStateUsing(fn ($record) => explode('.', $record->name)[0] ?? $record->name)
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'users'         => 'Usuarios',
                        'roles'         => 'Roles',
                        'permissions'   => 'Permisos',
                        'patients'      => 'Pacientes',
                        'orders'        => 'Órdenes',
                        'samples'       => 'Muestras',
                        'results'       => 'Resultados',
                        'billing'       => 'Facturación',
                        'inventory'     => 'Inventario',
                        'notifications' => 'Notificaciones',
                        default         => ucfirst($state),
                    })
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('action')
                    ->label('Acción')
                    ->getStateUsing(fn ($record) => explode('.', $record->name)[1] ?? '')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'viewAny' => 'Ver listado',
                        'view'    => 'Ver detalle',
                        'create'  => 'Crear',
                        'update'  => 'Editar',
                        'delete'  => 'Eliminar',
                        default   => ucfirst($state),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'viewAny' => 'gray',
                        'view'    => 'info',
                        'create'  => 'success',
                        'update'  => 'warning',
                        'delete'  => 'danger',
                        default   => 'gray',
                    }),

                Tables\Columns\TextColumn::make('roles_count')
                    ->label('Roles')
                    ->counts('roles')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('module')
                    ->label('Módulo')
                    ->options([
                        'users'         => 'Usuarios',
                        'roles'         => 'Roles',
                        'permissions'   => 'Permisos',
                        'patients'      => 'Pacientes',
                        'orders'        => 'Órdenes',
                        'samples'       => 'Muestras',
                        'results'       => 'Resultados',
                        'billing'       => 'Facturación',
                        'inventory'     => 'Inventario',
                        'notifications' => 'Notificaciones',
                    ])
                    ->query(fn ($query, array $data) =>
                        isset($data['value']) && $data['value']
                            ? $query->where('name', 'like', $data['value'] . '.%')
                            : $query
                    ),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissions::route('/'),
        ];
    }
}
