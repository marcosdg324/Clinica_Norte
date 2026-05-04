<?php

namespace App\Domains\Orders\Filament\Resources;

use App\Domains\Catalog\Models\Exam;
use App\Domains\Catalog\Models\ExamCategory;
use App\Domains\Imaging\Models\ImagingEquipment;
use App\Domains\Orders\Filament\Resources\OrderResource\Pages;
use App\Domains\Orders\Models\Order;
use App\Domains\Patients\Models\Patient;
use App\Models\User;
use App\Support\ResponsibleClinicalStaffScoping;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Órdenes';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Orden';

    protected static ?string $pluralModelLabel = 'Órdenes';

    // ─── Autorización ─────────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasDirectPermission('orders.access') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasDirectPermission('orders.access') ?? false;
    }

    public static function canEdit($record): bool
    {
        if (! (auth()->user()?->hasDirectPermission('orders.access') ?? false)) {
            return false;
        }
        // La Recepcionista solo puede editar mientras la orden esté pendiente
        if (auth()->user()?->hasRole('Recepcionista') && $record->status !== 'pendiente') {
            return false;
        }

        return true;
    }

    public static function canDelete($record): bool
    {
        if (! (auth()->user()?->hasDirectPermission('orders.access') ?? false)) {
            return false;
        }
        // La Recepcionista solo puede borrar si la orden está pendiente o cancelada
        if (auth()->user()?->hasRole('Recepcionista') && ! in_array($record->status, ['pendiente', 'cancelada'])) {
            return false;
        }

        return true;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->hasDirectPermission('orders.access') ?? false;
    }

    // ─── Formulario ──────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Select::make('type')
                ->label('Tipo de examen')
                ->required()
                ->native(false)
                ->live()
                ->options([
                    'laboratorio' => 'Laboratorio',
                    'imagen' => 'Imagen',
                ])
                ->default('laboratorio')
                ->prefixIcon('heroicon-o-beaker')
                ->validationMessages(['required' => 'Debe seleccionar el tipo de orden.']),

            // ── Información principal ──────────────────────────────────────────
            Forms\Components\Section::make('Información de la Orden')
                ->icon('heroicon-o-clipboard-document-list')
                ->schema([
                    Forms\Components\TextInput::make('order_number')
                        ->label('Número de orden')
                        ->required()
                        ->unique(table: 'orders', column: 'order_number', ignoreRecord: true)
                        ->validationMessages([
                            'unique' => 'Este número de orden ya existe.',
                            'required' => 'El número de orden es obligatorio.',
                        ])
                        ->default(fn () => 'ORD-'.strtoupper(uniqid()))
                        ->prefixIcon('heroicon-o-hashtag')
                        ->maxLength(50),

                    Forms\Components\Select::make('patient_id')
                        ->label('Paciente')
                        ->required()
                        ->searchable()
                        ->getSearchResultsUsing(function (string $search) {
                            return Patient::where('first_name', 'ilike', "%{$search}%")
                                ->orWhere('last_name', 'ilike', "%{$search}%")
                                ->orWhere('ci', 'ilike', "%{$search}%")
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn (Patient $p) => [
                                    $p->id => "{$p->first_name} {$p->last_name} — CI: {$p->ci}",
                                ]);
                        })
                        ->getOptionLabelUsing(function ($value) {
                            $p = Patient::find($value);

                            return $p ? "{$p->first_name} {$p->last_name} — CI: {$p->ci}" : null;
                        })
                        ->native(false)
                        ->validationMessages(['required' => 'Debe seleccionar un paciente.']),

                    Forms\Components\Select::make('receptionist_id')
                        ->label('Recepcionista')
                        ->required()
                        ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->native(false)
                        ->default(fn () => auth()->id())
                        ->validationMessages(['required' => 'Debe seleccionar un recepcionista.']),

                    Forms\Components\Select::make('responsible_user_id')
                        ->label(fn (Get $get) => $get('type') === 'imagen'
                            ? 'Tecnólogo de imagen responsable'
                            : 'Bioquímico responsable')
                        ->required()
                        ->options(function (Get $get) {
                            return match ($get('type')) {
                                'imagen' => User::role('Tecnólogo de Imagen')->orderBy('name')->pluck('name', 'id'),
                                default => User::role('Bioquímico')->orderBy('name')->pluck('name', 'id'),
                            };
                        })
                        ->searchable()
                        ->native(false)
                        ->rules([
                            function (Get $get) {
                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                    if (! $value) {
                                        return;
                                    }
                                    $user = User::find($value);
                                    if (! $user) {
                                        $fail('El usuario seleccionado no es válido.');

                                        return;
                                    }
                                    $type = $get('type') ?? 'laboratorio';
                                    if ($type === 'imagen' && ! $user->hasRole('Tecnólogo de Imagen')) {
                                        $fail('En órdenes de imagen debe asignar un Tecnólogo de Imagen.');
                                    }
                                    if ($type === 'laboratorio' && ! $user->hasRole('Bioquímico')) {
                                        $fail('En órdenes de laboratorio debe asignar un Bioquímico.');
                                    }
                                };
                            },
                        ])
                        ->validationMessages(['required' => 'Debe asignar un responsable para esta orden.']),
                ])
                ->columns(2),

            // ── Programación ───────────────────────────────────────────────────
            Forms\Components\Section::make('Programación')
                ->icon('heroicon-o-calendar-days')
                ->schema([
                    Forms\Components\DatePicker::make('scheduled_date')
                        ->label('Fecha programada')
                        ->required()
                        ->minDate(now()->toDateString())
                        ->displayFormat('d/m/Y')
                        ->native(false)
                        ->live()
                        ->validationMessages(['required' => 'La fecha programada es obligatoria.']),

                    Forms\Components\TimePicker::make('scheduled_time')
                        ->label('Hora programada')
                        ->required()
                        ->seconds(false)
                        ->live()
                        ->validationMessages(['required' => 'La hora programada es obligatoria.']),

                    Forms\Components\Select::make('equipment_id')
                        ->label('Equipo de imagen')
                        ->nullable()
                        ->native(false)
                        ->searchable()
                        ->placeholder('Sin asignar')
                        ->prefixIcon('heroicon-o-cpu-chip')
                        ->visible(fn (Get $get) => $get('type') === 'imagen')
                        ->required(fn (Get $get) => $get('type') === 'imagen')
                        ->options(function (Get $get, ?Order $record) {
                            $date = $get('scheduled_date');
                            $time = $get('scheduled_time');

                            $query = ImagingEquipment::where('status', 'disponible')
                                ->orderBy('name');

                            if ($date && $time) {
                                $occupied = Order::where('type', 'imagen')
                                    ->where('scheduled_date', $date)
                                    ->where('scheduled_time', $time)
                                    ->whereNotNull('equipment_id')
                                    ->when($record?->id, fn ($q) => $q->where('id', '!=', $record->id))
                                    ->pluck('equipment_id')
                                    ->filter()
                                    ->toArray();

                                if (! empty($occupied)) {
                                    $query->whereNotIn('id', $occupied);
                                }
                            }

                            return $query->pluck('name', 'id');
                        })
                        ->validationMessages(['required' => 'Debe seleccionar un equipo para órdenes de imagen.']),
                ])
                ->columns(2),

            // ── Exámenes asignados ─────────────────────────────────────────────
            Forms\Components\Section::make('Exámenes asignados')
                ->icon('heroicon-o-beaker')
                ->description('Seleccione uno o varios exámenes para esta orden.')
                ->schema([
                    Forms\Components\Hidden::make('exams')
                        ->default([])
                        ->rules(['array'])
                        ->validationMessages(['required' => 'Debe seleccionar al menos un examen.']),

                    Forms\Components\View::make('exam-selector')
                        ->viewData(fn (Get $get) => [
                            'orderType' => $get('type') ?? 'laboratorio',
                            'laboratoryCategories' => ExamCategory::with('exams')
                                ->where('type', 'laboratorio')
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->get(),
                            'imagingCategories' => ExamCategory::with('exams')
                                ->where('type', 'imagen')
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->get(),
                        ])
                        ->columnSpanFull(),

                    Forms\Components\Placeholder::make('exam_requirements_preview')
                        ->label('Requisitos previos de los exámenes seleccionados')
                        ->content(function (Get $get): HtmlString {
                            $examIds = $get('exams');
                            if (empty($examIds)) {
                                return new HtmlString(
                                    '<p class="text-sm italic text-gray-400">Seleccione exámenes para ver sus requisitos previos.</p>'
                                );
                            }
                            $exams = Exam::with('requirements')
                                ->whereIn('id', $examIds)
                                ->get();
                            $html = '<div class="space-y-3">';
                            foreach ($exams as $exam) {
                                $html .= '<div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800">';
                                $html .= '<p class="font-semibold text-sm text-gray-700 dark:text-gray-200">'.e($exam->name).'</p>';
                                if ($exam->requirements->isEmpty()) {
                                    $html .= '<p class="mt-1 text-xs italic text-gray-400">Sin requisitos previos.</p>';
                                } else {
                                    $html .= '<ul class="mt-1 list-disc list-inside space-y-1">';
                                    foreach ($exam->requirements as $req) {
                                        $html .= '<li class="text-sm text-gray-600 dark:text-gray-300">'.e($req->description).'</li>';
                                    }
                                    $html .= '</ul>';
                                }
                                $html .= '</div>';
                            }
                            $html .= '</div>';

                            return new HtmlString($html);
                        })
                        ->columnSpanFull(),
                ]),
        ]);
    }

    // ─── Tabla ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('N° Orden')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('patient.first_name')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($state, Order $record) => $record->patient?->first_name.' '.$record->patient?->last_name
                    )
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('patient', function ($q) use ($search) {
                            $q->where('first_name', 'ilike', "%{$search}%")
                                ->orWhere('last_name', 'ilike', "%{$search}%");
                        });
                    })
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipo de examen')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'laboratorio' => 'Laboratorio',
                        'imagen' => 'Imagen',
                        default => ucfirst($state),
                    })
                    ->colors([
                        'info' => 'laboratorio',
                        'warning' => 'imagen',
                    ])
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pendiente' => 'Pendiente',
                        'en_proceso' => 'En proceso',
                        'completada' => 'Completada',
                        'cancelada' => 'Cancelada',
                        default => ucfirst($state),
                    })
                    ->colors([
                        'warning' => 'pendiente',
                        'info' => 'en_proceso',
                        'success' => 'completada',
                        'danger' => 'cancelada',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('scheduled_date')
                    ->label('Fecha prog.')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Sin programar'),

                Tables\Columns\TextColumn::make('scheduled_time')
                    ->label('Hora prog.')
                    ->time('H:i')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('receptionist.name')
                    ->label('Recepcionista')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('responsibleUser.name')
                    ->label('Responsable asignado')
                    ->placeholder('Sin asignar')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('exams_count')
                    ->label('Exámenes')
                    ->counts('exams')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_proceso' => 'En proceso',
                        'completada' => 'Completada',
                        'cancelada' => 'Cancelada',
                    ]),

                Tables\Filters\Filter::make('sin_asignar')
                    ->label('Sin responsable asignado')
                    ->query(fn (Builder $query) => $query->whereNull('responsible_user_id')),

                Tables\Filters\Filter::make('mis_ordenes')
                    ->label('Mis órdenes')
                    ->query(fn (Builder $query) => $query->where('responsible_user_id', auth()->id())),

                Tables\Filters\Filter::make('scheduled_date')
                    ->label('Con fecha programada')
                    ->query(fn (Builder $query) => $query->whereNotNull('scheduled_date')),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->visible(fn (Order $record): bool => static::canEdit($record)),

                Tables\Actions\Action::make('cancelar_orden')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar orden')
                    ->modalDescription('¿Está seguro de que desea cancelar esta orden? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, cancelar')
                    ->visible(fn (Order $record): bool => in_array($record->status, ['pendiente', 'en_proceso'])
                        && (bool) auth()->user()?->hasDirectPermission('orders.access')
                        && ! (auth()->user()?->hasRole('Recepcionista') && $record->status !== 'pendiente')
                    )
                    ->action(function (Order $record): void {
                        $record->update(['status' => 'cancelada']);
                    }),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Order $record): bool => static::canDelete($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return ResponsibleClinicalStaffScoping::scopeOrderQueryForPanel(parent::getEloquentQuery());
    }

    // ─── Páginas ──────────────────────────────────────────────────────────────

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
