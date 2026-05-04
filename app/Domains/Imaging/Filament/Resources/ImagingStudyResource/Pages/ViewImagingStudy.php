<?php

namespace App\Domains\Imaging\Filament\Resources\ImagingStudyResource\Pages;

use App\Domains\Imaging\Filament\Resources\ImagingStudyResource;
use App\Domains\Imaging\Models\ImagingStatusHistory;
use App\Domains\Imaging\Models\ImagingStudy;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewImagingStudy extends ViewRecord
{
    protected static string $resource = ImagingStudyResource::class;

    protected function resolveRecord(int|string $key): ImagingStudy
    {
        return ImagingStudy::with([
            'order.patient',
            'exam',
            'equipment',
            'responsibleUser',
            'statusHistories.changedBy',
        ])->findOrFail($key);
    }

    protected function getHeaderActions(): array
    {
        return [
            // ── Registrar Llegada (programado → paciente_presente) ──────────
            Actions\Action::make('registrar_llegada')
                ->label('Registrar llegada')
                ->icon('heroicon-o-user-circle')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Registrar llegada del paciente')
                ->modalDescription('El estudio pasará a estado "Paciente presente".')
                ->visible(
                    fn () => $this->record->status === 'programado'
                        && (auth()->user()?->hasDirectPermission('imaging.approve') ?? false)
                )
                ->action(function (): void {
                    $study = $this->record;
                    $oldStatus = $study->status;

                    $study->update(['status' => 'paciente_presente']);

                    ImagingStatusHistory::create([
                        'imaging_study_id' => $study->id,
                        'old_status' => $oldStatus,
                        'new_status' => 'paciente_presente',
                        'changed_by' => auth()->id(),
                        'notes' => 'Paciente registrado como presente.',
                    ]);

                    $this->record = ImagingStudy::with(['order.patient', 'exam', 'equipment', 'responsibleUser', 'statusHistories.changedBy'])->findOrFail($study->id);

                    Notification::make()
                        ->title('Llegada registrada')
                        ->body('El paciente fue registrado como presente.')
                        ->success()
                        ->send();
                }),

            // ── Iniciar Estudio (paciente_presente → en_proceso) ────────────
            Actions\Action::make('iniciar_estudio')
                ->label('Iniciar estudio')
                ->icon('heroicon-o-play-circle')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Iniciar estudio')
                ->modalDescription('El estudio pasará a estado "En proceso".')
                ->visible(
                    fn () => $this->record->status === 'paciente_presente'
                        && (auth()->user()?->hasDirectPermission('imaging.approve') ?? false)
                )
                ->action(function (): void {
                    $study = $this->record;
                    $oldStatus = $study->status;

                    $study->update(['status' => 'en_proceso']);

                    ImagingStatusHistory::create([
                        'imaging_study_id' => $study->id,
                        'old_status' => $oldStatus,
                        'new_status' => 'en_proceso',
                        'changed_by' => auth()->id(),
                        'notes' => 'Estudio iniciado.',
                    ]);

                    $this->record = ImagingStudy::with(['order.patient', 'exam', 'equipment', 'responsibleUser', 'statusHistories.changedBy'])->findOrFail($study->id);

                    Notification::make()
                        ->title('Estudio iniciado')
                        ->body('El estudio de imagen está en proceso.')
                        ->warning()
                        ->send();
                }),

            // ── Completar Estudio (en_proceso → completado) ─────────────────
            Actions\Action::make('completar_estudio')
                ->label('Completar estudio')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->modalHeading('Completar estudio de imagen')
                ->visible(
                    fn () => $this->record->status === 'en_proceso'
                        && (auth()->user()?->hasDirectPermission('imaging.approve') ?? false)
                )
                ->form([
                    FileUpload::make('result_file')
                        ->label('Archivo de resultado (PDF / imagen)')
                        ->nullable()
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                        ->disk('public')
                        ->directory('imaging-results')
                        ->maxSize(10240),

                    Textarea::make('result_notes')
                        ->label('Informe / notas del resultado')
                        ->nullable()
                        ->rows(4)
                        ->placeholder('Describa los hallazgos del estudio...'),
                ])
                ->action(function (array $data): void {
                    $study = $this->record;
                    $oldStatus = $study->status;

                    $study->update([
                        'status' => 'completado',
                        'result_file' => $data['result_file'] ?? null,
                        'result_notes' => $data['result_notes'] ?? null,
                    ]);

                    ImagingStatusHistory::create([
                        'imaging_study_id' => $study->id,
                        'old_status' => $oldStatus,
                        'new_status' => 'completado',
                        'changed_by' => auth()->id(),
                        'notes' => 'Estudio completado.'.($data['result_notes'] ? ' Informe adjunto.' : ''),
                    ]);

                    $this->record = ImagingStudy::with(['order.patient', 'exam', 'equipment', 'responsibleUser', 'statusHistories.changedBy'])->findOrFail($study->id);

                    Notification::make()
                        ->title('Estudio completado')
                        ->body('El estudio fue marcado como completado exitosamente.')
                        ->success()
                        ->send();
                }),

            // ── Cancelar Estudio ─────────────────────────────────────────────
            Actions\Action::make('cancelar_estudio')
                ->label('Cancelar estudio')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(
                    fn () => ! in_array($this->record->status, ['completado', 'cancelado'])
                        && (auth()->user()?->hasDirectPermission('imaging.access') ?? false)
                )
                ->form([
                    Textarea::make('rejection_reason')
                        ->label('Motivo de cancelación')
                        ->required()
                        ->rows(3)
                        ->placeholder('Describe el motivo por el que se cancela el estudio...'),
                ])
                ->action(function (array $data): void {
                    $study = $this->record;
                    $oldStatus = $study->status;

                    $study->update([
                        'status' => 'cancelado',
                        'rejection_reason' => $data['rejection_reason'],
                    ]);

                    ImagingStatusHistory::create([
                        'imaging_study_id' => $study->id,
                        'old_status' => $oldStatus,
                        'new_status' => 'cancelado',
                        'changed_by' => auth()->id(),
                        'notes' => 'Cancelado: '.$data['rejection_reason'],
                    ]);

                    $this->record = ImagingStudy::with(['order.patient', 'exam', 'equipment', 'responsibleUser', 'statusHistories.changedBy'])->findOrFail($study->id);

                    Notification::make()
                        ->title('Estudio cancelado')
                        ->body('El estudio fue cancelado y el motivo fue registrado.')
                        ->danger()
                        ->send();
                }),

            Actions\EditAction::make()
                ->visible(fn () => ! (auth()->user()?->hasAnyRole(['Bioquímico', 'Tecnólogo de Imagen']) ?? false)),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            // ── Identificación ──────────────────────────────────────────────
            Section::make('Identificación del Estudio')
                ->icon('heroicon-o-photo')
                ->columns(2)
                ->schema([
                    TextEntry::make('study_code')
                        ->label('Código de estudio')
                        ->copyable()
                        ->badge()
                        ->color('gray')
                        ->icon('heroicon-o-qr-code'),

                    TextEntry::make('status')
                        ->label('Estado')
                        ->badge()
                        ->formatStateUsing(fn (string $state) => match ($state) {
                            'programado' => 'Programado',
                            'paciente_presente' => 'Paciente presente',
                            'en_proceso' => 'En proceso',
                            'completado' => 'Completado',
                            'cancelado' => 'Cancelado',
                            default => ucfirst($state),
                        })
                        ->color(fn (string $state) => match ($state) {
                            'programado' => 'gray',
                            'paciente_presente' => 'info',
                            'en_proceso' => 'warning',
                            'completado' => 'success',
                            'cancelado' => 'danger',
                            default => 'gray',
                        }),
                ]),

            // ── Orden, Examen y Equipo ───────────────────────────────────────
            Section::make('Orden, Examen y Equipo')
                ->icon('heroicon-o-clipboard-document-list')
                ->columns(2)
                ->schema([
                    TextEntry::make('order.order_number')
                        ->label('N° Orden')
                        ->badge()
                        ->color('gray'),

                    TextEntry::make('order.patient.first_name')
                        ->label('Paciente')
                        ->getStateUsing(fn (ImagingStudy $record) => $record->order?->patient
                            ? $record->order->patient->first_name.' '.$record->order->patient->last_name
                            : '—'),

                    TextEntry::make('exam.name')
                        ->label('Examen'),

                    TextEntry::make('equipment.name')
                        ->label('Equipo asignado')
                        ->placeholder('Sin asignar'),

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

            // ── Programación ────────────────────────────────────────────────
            Section::make('Programación')
                ->icon('heroicon-o-calendar-days')
                ->columns(2)
                ->schema([
                    TextEntry::make('collected_at')
                        ->label('Fecha y hora del estudio')
                        ->dateTime('d/m/Y H:i')
                        ->placeholder('No registrada'),

                    TextEntry::make('order.scheduled_date')
                        ->label('Cita programada')
                        ->date('d/m/Y')
                        ->placeholder('Sin programar'),
                ]),

            // ── Resultado ───────────────────────────────────────────────────
            Section::make('Resultado del estudio')
                ->icon('heroicon-o-document-text')
                ->columns(1)
                ->visible(fn (ImagingStudy $record) => $record->status === 'completado')
                ->schema([
                    TextEntry::make('result_notes')
                        ->label('Informe / notas')
                        ->placeholder('Sin informe escrito.')
                        ->columnSpanFull(),

                    TextEntry::make('result_file')
                        ->label('Archivo adjunto')
                        ->placeholder('Sin archivo adjunto.')
                        ->formatStateUsing(fn (?string $state) => $state ? basename($state) : null),
                ]),

            // ── Cancelación ─────────────────────────────────────────────────
            Section::make('Motivo de cancelación')
                ->icon('heroicon-o-x-circle')
                ->visible(fn (ImagingStudy $record) => $record->status === 'cancelado')
                ->schema([
                    TextEntry::make('rejection_reason')
                        ->label('Motivo')
                        ->placeholder('—')
                        ->columnSpanFull(),
                ]),

            // ── Tecnólogo de imagen asignado ───────────────────────────────────
            Section::make('Tecnólogo de imagen asignado')
                ->icon('heroicon-o-user-group')
                ->columns(2)
                ->schema([
                    TextEntry::make('responsibleUser.name')
                        ->label('Profesional')
                        ->placeholder('Sin asignar')
                        ->badge()
                        ->color('info'),
                ]),

            // ── Trazabilidad ────────────────────────────────────────────────
            Section::make('Trazabilidad — Historial de estados')
                ->icon('heroicon-o-clock')
                ->description('Registro cronológico de todos los cambios de estado de este estudio.')
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
                                    'programado' => 'Programado',
                                    'paciente_presente' => 'Paciente presente',
                                    'en_proceso' => 'En proceso',
                                    'completado' => 'Completado',
                                    'cancelado' => 'Cancelado',
                                    null => '—',
                                    default => ucfirst($state),
                                })
                                ->color(fn (?string $state) => match ($state) {
                                    'programado' => 'gray',
                                    'paciente_presente' => 'info',
                                    'en_proceso' => 'warning',
                                    'completado' => 'success',
                                    'cancelado' => 'danger',
                                    default => 'gray',
                                }),

                            TextEntry::make('new_status')
                                ->label('Nuevo estado')
                                ->badge()
                                ->formatStateUsing(fn (string $state) => match ($state) {
                                    'programado' => 'Programado',
                                    'paciente_presente' => 'Paciente presente',
                                    'en_proceso' => 'En proceso',
                                    'completado' => 'Completado',
                                    'cancelado' => 'Cancelado',
                                    default => ucfirst($state),
                                })
                                ->color(fn (string $state) => match ($state) {
                                    'programado' => 'gray',
                                    'paciente_presente' => 'info',
                                    'en_proceso' => 'warning',
                                    'completado' => 'success',
                                    'cancelado' => 'danger',
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
                        ->label('Creado el')
                        ->dateTime('d/m/Y H:i'),

                    TextEntry::make('updated_at')
                        ->label('Última actualización')
                        ->dateTime('d/m/Y H:i'),
                ]),
        ]);
    }
}
