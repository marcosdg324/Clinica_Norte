<?php

namespace App\Domains\Orders\Filament\Resources;

use App\Domains\Orders\Filament\Resources\OrderResource\Pages;
use App\Domains\Orders\Models\Exam;
use App\Domains\Orders\Models\Order;
use App\Domains\Patients\Models\Patient;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Órdenes y Exámenes';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $modelLabel       = 'Orden';
    protected static ?string $pluralModelLabel = 'Órdenes';

    // ─── Autorización ─────────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasDirectPermission('orders.viewAny') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasDirectPermission('orders.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasDirectPermission('orders.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasDirectPermission('orders.delete') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->hasDirectPermission('orders.view') ?? false;
    }

    // ─── Formulario ──────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── Información principal ──────────────────────────────────────────
            Forms\Components\Section::make('Información de la Orden')
                ->icon('heroicon-o-clipboard-document-list')
                ->schema([
                    Forms\Components\TextInput::make('order_number')
                        ->label('Número de orden')
                        ->required()
                        ->unique(table: 'orders', column: 'order_number', ignoreRecord: true)
                        ->validationMessages([
                            'unique'   => 'Este número de orden ya existe.',
                            'required' => 'El número de orden es obligatorio.',
                        ])
                        ->default(fn () => 'ORD-' . strtoupper(uniqid()))
                        ->prefixIcon('heroicon-o-hashtag')
                        ->maxLength(50),

                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->required()
                        ->options([
                            'pendiente'  => 'Pendiente',
                            'en_proceso' => 'En proceso',
                            'completado' => 'Completado',
                            'cancelado'  => 'Cancelado',
                        ])
                        ->default('pendiente')
                        ->native(false),

                    Forms\Components\Select::make('patient_id')
                        ->label('Paciente')
                        ->required()
                        ->relationship('patient', 'first_name')
                        ->getOptionLabelFromRecordUsing(
                            fn (Patient $record) => "{$record->first_name} {$record->last_name} — CI: {$record->ci}"
                        )
                        ->searchable(['first_name', 'last_name', 'ci'])
                        ->preload(false)
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
                ])
                ->columns(2),

            // ── Programación ───────────────────────────────────────────────────
            Forms\Components\Section::make('Programación')
                ->icon('heroicon-o-calendar-days')
                ->schema([
                    Forms\Components\Toggle::make('is_urgent')
                        ->label('¿Orden urgente?')
                        ->onColor('danger')
                        ->offColor('gray')
                        ->onIcon('heroicon-m-bolt')
                        ->offIcon('heroicon-m-minus')
                        ->default(false)
                        ->columnSpanFull(),

                    Forms\Components\DatePicker::make('scheduled_date')
                        ->label('Fecha programada')
                        ->nullable()
                        ->minDate(now()->toDateString())
                        ->displayFormat('d/m/Y')
                        ->native(false),

                    Forms\Components\TimePicker::make('scheduled_time')
                        ->label('Hora programada')
                        ->nullable()
                        ->seconds(false),
                ])
                ->columns(2),

            // ── Exámenes asignados ─────────────────────────────────────────────
            Forms\Components\Section::make('Exámenes asignados')
                ->icon('heroicon-o-beaker')
                ->description('Seleccione uno o varios exámenes para esta orden.')
                ->schema([
                    Forms\Components\Select::make('exams')
                        ->label('Exámenes')
                        ->relationship('exams', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->native(false)
                        ->required()
                        ->live()
                        ->validationMessages(['required' => 'Debe asignar al menos un examen a la orden.'])
                        ->columnSpanFull(),

                    Forms\Components\Placeholder::make('exam_requirements_preview')
                        ->label('Requisitos previos de los exámenes seleccionados')
                        ->content(function (Forms\Get $get): HtmlString {
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
                                $html .= '<p class="font-semibold text-sm text-gray-700 dark:text-gray-200">' . e($exam->name) . '</p>';
                                if ($exam->requirements->isEmpty()) {
                                    $html .= '<p class="mt-1 text-xs italic text-gray-400">Sin requisitos previos.</p>';
                                } else {
                                    $html .= '<ul class="mt-1 list-disc list-inside space-y-1">';
                                    foreach ($exam->requirements as $req) {
                                        $html .= '<li class="text-sm text-gray-600 dark:text-gray-300">' . e($req->description) . '</li>';
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
                    ->formatStateUsing(fn ($state, Order $record) =>
                        $record->patient?->first_name . ' ' . $record->patient?->last_name
                    )
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('patient', function ($q) use ($search) {
                            $q->where('first_name', 'ilike', "%{$search}%")
                              ->orWhere('last_name', 'ilike', "%{$search}%");
                        });
                    })
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pendiente'  => 'Pendiente',
                        'en_proceso' => 'En proceso',
                        'completado' => 'Completado',
                        'cancelado'  => 'Cancelado',
                        default      => ucfirst($state),
                    })
                    ->colors([
                        'warning' => 'pendiente',
                        'info'    => 'en_proceso',
                        'success' => 'completado',
                        'danger'  => 'cancelado',
                    ])
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_urgent')
                    ->label('Urgente')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->trueIcon('heroicon-o-bolt')
                    ->falseIcon('heroicon-o-minus-circle'),

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
                        'pendiente'  => 'Pendiente',
                        'en_proceso' => 'En proceso',
                        'completado' => 'Completado',
                        'cancelado'  => 'Cancelado',
                    ]),

                Tables\Filters\TernaryFilter::make('is_urgent')
                    ->label('¿Urgente?')
                    ->trueLabel('Sí')
                    ->falseLabel('No'),

                Tables\Filters\Filter::make('scheduled_date')
                    ->label('Con fecha programada')
                    ->query(fn (Builder $query) => $query->whereNotNull('scheduled_date')),

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
            ->defaultSort('created_at', 'desc');
    }

    // ─── Páginas ──────────────────────────────────────────────────────────────

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view'   => Pages\ViewOrder::route('/{record}'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
