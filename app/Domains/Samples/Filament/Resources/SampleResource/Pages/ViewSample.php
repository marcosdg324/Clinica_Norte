<?php

namespace App\Domains\Samples\Filament\Resources\SampleResource\Pages;

use App\Domains\Samples\Filament\Resources\SampleResource;
use App\Domains\Samples\Models\Sample;
use App\Domains\Samples\Models\SampleStatusHistory;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewSample extends ViewRecord
{
    protected static string $resource = SampleResource::class;

    protected function resolveRecord(int|string $key): Sample
    {
        return Sample::with([
            'order.patient',
            'exam',
            'collectedBy',
            'bioquimicoAsignado',
            'statusHistories.changedBy',
        ])->findOrFail($key);
    }

    protected function getHeaderActions(): array
    {
        return [
            // ── Aceptar muestra (Bioquímico: recibida → en_analisis) ────────────
            Actions\Action::make('aceptar_muestra')
                ->label('Aceptar muestra')
                ->icon('heroicon-o-beaker')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Aceptar muestra')
                ->modalDescription('La muestra pasará a estado "En análisis" y quedará asignada a ti.')
                ->visible(
                    fn () => $this->record->status === 'recibida'
                        && (auth()->user()?->hasDirectPermission('samples.approve') ?? false)
                        && $this->record->order?->responsible_user_id === auth()->id()
                )
                ->action(function (): void {
                    $sample = $this->record;
                    $oldStatus = $sample->status;

                    $sample->update([
                        'status' => 'en_analisis',
                        'bioquimico_asignado_id' => auth()->id(),
                    ]);

                    SampleStatusHistory::create([
                        'sample_id' => $sample->id,
                        'old_status' => $oldStatus,
                        'new_status' => 'en_analisis',
                        'changed_by' => auth()->id(),
                        'notes' => 'Muestra aceptada por bioquímico.',
                    ]);

                    $this->record = Sample::with(['order.patient', 'exam', 'collectedBy', 'bioquimicoAsignado', 'statusHistories.changedBy'])->findOrFail($sample->id);

                    Notification::make()
                        ->title('Muestra aceptada')
                        ->body('La muestra está ahora en análisis y asignada a ti.')
                        ->success()
                        ->send();
                }),

            // ── Procesar muestra (Bioquímico: en_analisis → procesada) ────────
            Actions\Action::make('procesar_muestra')
                ->label('Marcar procesada')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Marcar como procesada')
                ->modalDescription('Confirma que la muestra fue analizada y los resultados están listos.')
                ->visible(
                    fn () => $this->record->status === 'en_analisis'
                        && (auth()->user()?->hasDirectPermission('samples.approve') ?? false)
                )
                ->action(function (): void {
                    $sample = $this->record;
                    $oldStatus = $sample->status;

                    $sample->update(['status' => 'procesada']);

                    SampleStatusHistory::create([
                        'sample_id' => $sample->id,
                        'old_status' => $oldStatus,
                        'new_status' => 'procesada',
                        'changed_by' => auth()->id(),
                        'notes' => 'Muestra marcada como procesada.',
                    ]);

                    $this->record = Sample::with(['order.patient', 'exam', 'collectedBy', 'bioquimicoAsignado', 'statusHistories.changedBy'])->findOrFail($sample->id);

                    Notification::make()
                        ->title('Muestra procesada')
                        ->body('La muestra fue marcada como procesada exitosamente.')
                        ->success()
                        ->send();
                }),

            // ── Rechazar muestra ──────────────────────────────────────────────
            Actions\Action::make('rechazar_muestra')
                ->label('Rechazar muestra')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(
                    fn () => $this->record->status === 'en_analisis'
                        && (auth()->user()?->hasDirectPermission('samples.access') ?? false)
                        && (auth()->user()?->hasDirectPermission('samples.reject') ?? false)
                )
                ->form([
                    Textarea::make('motivo_rechazo')
                        ->label('Motivo de rechazo')
                        ->required()
                        ->rows(3)
                        ->placeholder('Describe el motivo por el que se rechaza la muestra...'),
                ])
                ->action(function (array $data): void {
                    $sample = $this->record;
                    $oldStatus = $sample->status;

                    $sample->update([
                        'status' => 'rechazada',
                        'motivo_rechazo' => $data['motivo_rechazo'],
                    ]);

                    SampleStatusHistory::create([
                        'sample_id' => $sample->id,
                        'old_status' => $oldStatus,
                        'new_status' => 'rechazada',
                        'changed_by' => auth()->id(),
                        'notes' => 'Rechazada: '.$data['motivo_rechazo'],
                    ]);

                    $this->record = Sample::with(['order.patient', 'exam', 'collectedBy', 'bioquimicoAsignado', 'statusHistories.changedBy'])->findOrFail($sample->id);

                    Notification::make()
                        ->title('Muestra rechazada')
                        ->body('La muestra fue rechazada y el motivo fue registrado.')
                        ->danger()
                        ->send();
                }),

            Actions\EditAction::make()
                ->visible(fn () => ! (auth()->user()?->hasRole('Bioquímico') ?? false)),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            // ── Identificación ──────────────────────────────────────────────
            Section::make('Identificación de la Muestra')
                ->icon('heroicon-o-beaker')
                ->columns(2)
                ->schema([
                    TextEntry::make('barcode')
                        ->label('Código de barras')
                        ->copyable()
                        ->badge()
                        ->color('gray')
                        ->icon('heroicon-o-qr-code'),

                    TextEntry::make('status')
                        ->label('Estado')
                        ->badge()
                        ->formatStateUsing(fn (string $state) => match ($state) {
                            'recibida' => 'Recibida',
                            'en_analisis' => 'En análisis',
                            'procesada' => 'Procesada',
                            'rechazada' => 'Rechazada',
                            default => ucfirst($state),
                        })
                        ->color(fn (string $state) => match ($state) {
                            'recibida' => 'info',
                            'en_analisis' => 'warning',
                            'procesada' => 'success',
                            'rechazada' => 'danger',
                            default => 'gray',
                        }),
                ]),

            // ── Orden y Examen ──────────────────────────────────────────────
            Section::make('Orden y Examen')
                ->icon('heroicon-o-clipboard-document-list')
                ->columns(2)
                ->schema([
                    TextEntry::make('order.order_number')
                        ->label('N° Orden')
                        ->badge()
                        ->color('gray'),

                    TextEntry::make('order.patient.first_name')
                        ->label('Paciente')
                        ->getStateUsing(fn (Sample $record) => $record->order?->patient
                            ? $record->order->patient->first_name.' '.$record->order->patient->last_name
                            : '—'),

                    TextEntry::make('exam.name')
                        ->label('Examen'),

                    TextEntry::make('order.status')
                        ->label('Estado de la orden')
                        ->badge()
                        ->formatStateUsing(fn (string $state) => match ($state) {
                            'pendiente' => 'Pendiente',
                            'en_proceso' => 'En proceso',
                            'completada' => 'Completada',
                            'cancelada' => 'Cancelada',
                            default => ucfirst($state),
                        })
                        ->color(fn (string $state) => match ($state) {
                            'pendiente' => 'gray',
                            'en_proceso' => 'warning',
                            'completada' => 'success',
                            'cancelada' => 'danger',
                            default => 'gray',
                        }),
                ]),

            // ── Recolección ─────────────────────────────────────────────────
            Section::make('Recolección')
                ->icon('heroicon-o-user-circle')
                ->columns(2)
                ->schema([
                    TextEntry::make('collected_at')
                        ->label('Fecha y hora de recolección')
                        ->dateTime('d/m/Y H:i')
                        ->placeholder('No registrada'),

                    TextEntry::make('collectedBy.name')
                        ->label('Recolectado por'),

                    TextEntry::make('location')
                        ->label('Ubicación / Gabinete')
                        ->placeholder('No especificada'),

                    TextEntry::make('notes')
                        ->label('Notas')
                        ->placeholder('Sin notas')
                        ->columnSpanFull(),
                ]),

            // ── Análisis / Bioquímico ────────────────────────────────────────
            Section::make('Análisis')
                ->icon('heroicon-o-user-group')
                ->columns(2)
                ->schema([
                    TextEntry::make('bioquimicoAsignado.name')
                        ->label('Bioquímico asignado')
                        ->placeholder('Sin asignar')
                        ->badge()
                        ->color('info'),

                    TextEntry::make('motivo_rechazo')
                        ->label('Motivo de rechazo')
                        ->placeholder('—')
                        ->visible(fn (Sample $record) => $record->status === 'rechazada')
                        ->columnSpanFull(),
                ]),

            // ── Trazabilidad ────────────────────────────────────────────────
            Section::make('Trazabilidad — Historial de estados')
                ->icon('heroicon-o-clock')
                ->description('Registro cronológico de todos los cambios de estado de esta muestra.')
                ->schema([
                    RepeatableEntry::make('statusHistories')
                        ->label('')
                        ->schema([
                            TextEntry::make('created_at')
                                ->label('Fecha')
                                ->dateTime('d/m/Y H:i'),

                            TextEntry::make('changedBy.name')
                                ->label('Cambió'),

                            TextEntry::make('old_status')
                                ->label('Estado anterior')
                                ->badge()
                                ->formatStateUsing(fn (?string $state) => match ($state) {
                                    'recibida' => 'Recibida',
                                    'en_analisis' => 'En análisis',
                                    'procesada' => 'Procesada',
                                    'rechazada' => 'Rechazada',
                                    null => '—',
                                    default => ucfirst($state),
                                })
                                ->color(fn (?string $state) => match ($state) {
                                    'recibida' => 'info',
                                    'en_analisis' => 'warning',
                                    'procesada' => 'success',
                                    'rechazada' => 'danger',
                                    default => 'gray',
                                }),

                            TextEntry::make('new_status')
                                ->label('Nuevo estado')
                                ->badge()
                                ->formatStateUsing(fn (string $state) => match ($state) {
                                    'recibida' => 'Recibida',
                                    'en_analisis' => 'En análisis',
                                    'procesada' => 'Procesada',
                                    'rechazada' => 'Rechazada',
                                    default => ucfirst($state),
                                })
                                ->color(fn (string $state) => match ($state) {
                                    'recibida' => 'info',
                                    'en_analisis' => 'warning',
                                    'procesada' => 'success',
                                    'rechazada' => 'danger',
                                    default => 'gray',
                                }),

                            TextEntry::make('notes')
                                ->label('Observaciones')
                                ->placeholder('—')
                                ->columnSpanFull(),
                        ])
                        ->columns(4)
                        ->contained(false),
                ]),

            // ── Metadatos ───────────────────────────────────────────────────
            Section::make('Registro')
                ->icon('heroicon-o-calendar')
                ->columns(2)
                ->collapsed()
                ->schema([
                    TextEntry::make('created_at')
                        ->label('Creada el')
                        ->dateTime('d/m/Y H:i'),

                    TextEntry::make('updated_at')
                        ->label('Última actualización')
                        ->dateTime('d/m/Y H:i'),
                ]),
        ]);
    }
}
