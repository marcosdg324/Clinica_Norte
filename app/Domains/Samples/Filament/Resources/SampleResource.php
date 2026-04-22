<?php

namespace App\Domains\Samples\Filament\Resources;

use App\Domains\Orders\Models\Exam;
use App\Domains\Orders\Models\Order;
use App\Domains\Samples\Filament\Resources\SampleResource\Pages;
use App\Domains\Samples\Models\Sample;
use App\Domains\Samples\Models\SampleStatusHistory;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SampleResource extends Resource
{
    protected static ?string $model = Sample::class;

    protected static ?string $navigationIcon  = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = 'Muestras';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $modelLabel       = 'Muestra';
    protected static ?string $pluralModelLabel = 'Muestras';

    // ─── Autorización ─────────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasDirectPermission('samples.viewAny') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasDirectPermission('samples.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasDirectPermission('samples.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasDirectPermission('samples.delete') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->hasDirectPermission('samples.view') ?? false;
    }

    // ─── Formulario ──────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── Identificación de la muestra ──────────────────────────────────
            Forms\Components\Section::make('Identificación')
                ->icon('heroicon-o-beaker')
                ->schema([
                    Forms\Components\TextInput::make('barcode')
                        ->label('Código de barras')
                        ->required()
                        ->unique(table: 'samples', column: 'barcode', ignoreRecord: true)
                        ->default(fn () => Sample::generateBarcode())
                        ->prefixIcon('heroicon-o-qr-code')
                        ->maxLength(50)
                        ->suffixAction(
                            Forms\Components\Actions\Action::make('regenerate_barcode')
                                ->icon('heroicon-m-arrow-path')
                                ->tooltip('Generar nuevo código')
                                ->action(fn (Forms\Set $set) => $set('barcode', Sample::generateBarcode()))
                        )
                        ->validationMessages([
                            'unique'   => 'Este código de barras ya está en uso.',
                            'required' => 'El código de barras es obligatorio.',
                        ]),

                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->required()
                        ->options([
                            'recibida'    => 'Recibida',
                            'en_analisis' => 'En análisis',
                            'procesada'   => 'Procesada',
                            'rechazada'   => 'Rechazada',
                        ])
                        ->default('recibida')
                        ->native(false),
                ])
                ->columns(2),

            // ── Asociación ────────────────────────────────────────────────────
            Forms\Components\Section::make('Orden y Examen')
                ->icon('heroicon-o-clipboard-document-list')
                ->schema([
                    Forms\Components\Select::make('order_id')
                        ->label('Orden')
                        ->required()
                        ->options(
                            fn () => Order::with('patient')
                                ->orderByDesc('created_at')
                                ->get()
                                ->mapWithKeys(fn (Order $o) => [
                                    $o->id => $o->order_number
                                        . ' — '
                                        . ($o->patient?->first_name . ' ' . $o->patient?->last_name),
                                ])
                        )
                        ->searchable()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set) => $set('exam_id', null))
                        ->validationMessages(['required' => 'Debe seleccionar una orden.']),

                    Forms\Components\Select::make('exam_id')
                        ->label('Examen')
                        ->required()
                        ->options(function (Get $get) {
                            $orderId = $get('order_id');
                            if (! $orderId) {
                                return [];
                            }
                            $order = Order::with('exams')->find($orderId);
                            return $order?->exams->pluck('name', 'id') ?? [];
                        })
                        ->searchable()
                        ->native(false)
                        ->disabled(fn (Get $get) => ! $get('order_id'))
                        ->validationMessages(['required' => 'Debe seleccionar un examen.']),
                ])
                ->columns(2),

            // ── Recolección ───────────────────────────────────────────────────
            Forms\Components\Section::make('Recolección')
                ->icon('heroicon-o-user-circle')
                ->schema([
                    Forms\Components\DateTimePicker::make('collected_at')
                        ->label('Fecha y hora de recolección')
                        ->nullable()
                        ->displayFormat('d/m/Y H:i')
                        ->native(false),

                    Forms\Components\Select::make('collected_by')
                        ->label('Recolectado por')
                        ->required()
                        ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->native(false)
                        ->default(fn () => auth()->id())
                        ->validationMessages(['required' => 'Debe indicar quién recolectó la muestra.']),

                    Forms\Components\TextInput::make('location')
                        ->label('Ubicación / Gabinete')
                        ->nullable()
                        ->prefixIcon('heroicon-o-map-pin')
                        ->maxLength(255),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notas')
                        ->nullable()
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    // ─── Tabla ───────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('barcode')
                    ->label('Código de barras')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-o-qr-code'),

                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('N° Orden')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.patient.first_name')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($record) => $record->order?->patient
                        ? $record->order->patient->first_name . ' ' . $record->order->patient->last_name
                        : '—')
                    ->searchable(query: fn ($query, $search) => $query->whereHas(
                        'order.patient',
                        fn ($q) => $q->where('first_name', 'ilike', "%{$search}%")
                            ->orWhere('last_name', 'ilike', "%{$search}%")
                    )),

                Tables\Columns\TextColumn::make('exam.name')
                    ->label('Examen')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'recibida'    => 'Recibida',
                        'en_analisis' => 'En análisis',
                        'procesada'   => 'Procesada',
                        'rechazada'   => 'Rechazada',
                        default       => ucfirst($state),
                    })
                    ->colors([
                        'info'    => 'recibida',
                        'warning' => 'en_analisis',
                        'success' => 'procesada',
                        'danger'  => 'rechazada',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('collected_at')
                    ->label('Recolectada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('collectedBy.name')
                    ->label('Recolectado por')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('location')
                    ->label('Ubicación')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'recibida'    => 'Recibida',
                        'en_analisis' => 'En análisis',
                        'procesada'   => 'Procesada',
                        'rechazada'   => 'Rechazada',
                    ])
                    ->native(false),

                Tables\Filters\Filter::make('collected_at')
                    ->label('Fecha de recolección')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Desde')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Hasta')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'],  fn ($q, $v) => $q->whereDate('collected_at', '>=', $v))
                            ->when($data['until'], fn ($q, $v) => $q->whereDate('collected_at', '<=', $v));
                    }),

                Tables\Filters\SelectFilter::make('order_id')
                    ->label('Orden')
                    ->options(
                        fn () => Order::with('patient')
                            ->orderByDesc('created_at')
                            ->get()
                            ->mapWithKeys(fn (Order $o) => [
                                $o->id => $o->order_number
                                    . ' — '
                                    . ($o->patient?->first_name . ' ' . $o->patient?->last_name),
                            ])
                    )
                    ->searchable()
                    ->native(false),
            ])
            ->actions([
                // Acción rápida: cambiar estado
                Tables\Actions\Action::make('change_status')
                    ->label('Cambiar estado')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn () => auth()->user()?->hasDirectPermission('samples.update') ?? false)
                    ->form([
                        Forms\Components\Select::make('new_status')
                            ->label('Nuevo estado')
                            ->required()
                            ->options([
                                'recibida'    => 'Recibida',
                                'en_analisis' => 'En análisis',
                                'procesada'   => 'Procesada',
                                'rechazada'   => 'Rechazada',
                            ])
                            ->native(false),
                        Forms\Components\Textarea::make('change_notes')
                            ->label('Notas del cambio')
                            ->nullable()
                            ->rows(2),
                    ])
                    ->action(function (Sample $record, array $data): void {
                        $oldStatus = $record->status;
                        $newStatus = $data['new_status'];

                        $record->update(['status' => $newStatus]);

                        SampleStatusHistory::create([
                            'sample_id'  => $record->id,
                            'old_status' => $oldStatus,
                            'new_status' => $newStatus,
                            'changed_by' => auth()->id(),
                            'notes'      => $data['change_notes'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Estado actualizado')
                            ->body("La muestra pasó de \"{$oldStatus}\" a \"{$newStatus}\".")
                            ->success()
                            ->send();
                    }),

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

    // ─── Pages ───────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSamples::route('/'),
            'create' => Pages\CreateSample::route('/create'),
            'view'   => Pages\ViewSample::route('/{record}'),
            'edit'   => Pages\EditSample::route('/{record}/edit'),
        ];
    }

    // ─── Query con eager loading ──────────────────────────────────────────────

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['order.patient', 'exam', 'collectedBy']);
    }
}
