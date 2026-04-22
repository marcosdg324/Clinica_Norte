<?php

namespace App\Domains\Samples\Filament\Resources\SampleResource\Pages;

use App\Domains\Samples\Filament\Resources\SampleResource;
use App\Domains\Samples\Models\Sample;
use App\Domains\Samples\Models\SampleStatusHistory;
use Filament\Actions;
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
            'statusHistories.changedBy',
        ])->findOrFail($key);
    }

    protected function getHeaderActions(): array
    {
        return [
            // Acción rápida: cambiar estado desde la vista
            Actions\Action::make('change_status')
                ->label('Cambiar estado')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn () => auth()->user()?->hasDirectPermission('samples.update') ?? false)
                ->form([
                    \Filament\Forms\Components\Select::make('new_status')
                        ->label('Nuevo estado')
                        ->required()
                        ->options([
                            'recibida'    => 'Recibida',
                            'en_analisis' => 'En análisis',
                            'procesada'   => 'Procesada',
                            'rechazada'   => 'Rechazada',
                        ])
                        ->native(false),
                    \Filament\Forms\Components\Textarea::make('change_notes')
                        ->label('Notas del cambio')
                        ->nullable()
                        ->rows(2),
                ])
                ->action(function (array $data): void {
                    $sample    = $this->record;
                    $oldStatus = $sample->status;
                    $newStatus = $data['new_status'];

                    $sample->update(['status' => $newStatus]);

                    SampleStatusHistory::create([
                        'sample_id'  => $sample->id,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'changed_by' => auth()->id(),
                        'notes'      => $data['change_notes'] ?? null,
                    ]);

                    // Refrescar el record para que el infolist muestre el nuevo estado
                    $this->record = Sample::with([
                        'order.patient',
                        'exam',
                        'collectedBy',
                        'statusHistories.changedBy',
                    ])->findOrFail($sample->id);

                    Notification::make()
                        ->title('Estado actualizado')
                        ->body("La muestra pasó de \"{$oldStatus}\" a \"{$newStatus}\".")
                        ->success()
                        ->send();
                }),

            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
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
                            'recibida'    => 'Recibida',
                            'en_analisis' => 'En análisis',
                            'procesada'   => 'Procesada',
                            'rechazada'   => 'Rechazada',
                            default       => ucfirst($state),
                        })
                        ->color(fn (string $state) => match ($state) {
                            'recibida'    => 'info',
                            'en_analisis' => 'warning',
                            'procesada'   => 'success',
                            'rechazada'   => 'danger',
                            default       => 'gray',
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
                            ? $record->order->patient->first_name . ' ' . $record->order->patient->last_name
                            : '—'),

                    TextEntry::make('exam.name')
                        ->label('Examen'),

                    TextEntry::make('order.status')
                        ->label('Estado de la orden')
                        ->badge()
                        ->formatStateUsing(fn (string $state) => match ($state) {
                            'pendiente'  => 'Pendiente',
                            'en_proceso' => 'En proceso',
                            'completado' => 'Completado',
                            'cancelado'  => 'Cancelado',
                            default      => ucfirst($state),
                        })
                        ->color(fn (string $state) => match ($state) {
                            'pendiente'  => 'gray',
                            'en_proceso' => 'warning',
                            'completado' => 'success',
                            'cancelado'  => 'danger',
                            default      => 'gray',
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
                                    'recibida'    => 'Recibida',
                                    'en_analisis' => 'En análisis',
                                    'procesada'   => 'Procesada',
                                    'rechazada'   => 'Rechazada',
                                    null          => '—',
                                    default       => ucfirst($state),
                                })
                                ->color(fn (?string $state) => match ($state) {
                                    'recibida'    => 'info',
                                    'en_analisis' => 'warning',
                                    'procesada'   => 'success',
                                    'rechazada'   => 'danger',
                                    default       => 'gray',
                                }),

                            TextEntry::make('new_status')
                                ->label('Nuevo estado')
                                ->badge()
                                ->formatStateUsing(fn (string $state) => match ($state) {
                                    'recibida'    => 'Recibida',
                                    'en_analisis' => 'En análisis',
                                    'procesada'   => 'Procesada',
                                    'rechazada'   => 'Rechazada',
                                    default       => ucfirst($state),
                                })
                                ->color(fn (string $state) => match ($state) {
                                    'recibida'    => 'info',
                                    'en_analisis' => 'warning',
                                    'procesada'   => 'success',
                                    'rechazada'   => 'danger',
                                    default       => 'gray',
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
