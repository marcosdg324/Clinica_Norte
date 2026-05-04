<?php

namespace App\Domains\Orders\Filament\Resources\OrderResource\Pages;

use App\Domains\Orders\Filament\Resources\OrderResource;
use App\Domains\Orders\Models\Order;
use Filament\Actions;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function resolveRecord(int|string $key): Order
    {
        return Order::with(['patient', 'receptionist', 'responsibleUser', 'equipment', 'exams.requirements'])->findOrFail($key);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => OrderResource::canEdit($this->record)),
            Actions\DeleteAction::make()
                ->visible(fn () => OrderResource::canDelete($this->record)),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            Section::make('Información de la Orden')
                ->icon('heroicon-o-clipboard-document-list')
                ->columns(2)
                ->schema([
                    TextEntry::make('order_number')
                        ->label('N° Orden')
                        ->copyable()
                        ->badge()
                        ->color('gray'),

                    TextEntry::make('type')
                        ->label('Tipo de orden')
                        ->badge()
                        ->formatStateUsing(fn (string $state) => match ($state) {
                            'laboratorio' => 'Laboratorio',
                            'imagen' => 'Imagen',
                            default => ucfirst($state),
                        })
                        ->color(fn (string $state) => match ($state) {
                            'laboratorio' => 'primary',
                            'imagen' => 'success',
                            default => 'gray',
                        }),

                    TextEntry::make('status')
                        ->label('Estado')
                        ->badge()
                        ->formatStateUsing(fn (string $state) => match ($state) {
                            'pendiente' => 'Pendiente',
                            'en_proceso' => 'En proceso',
                            'completada' => 'Completada',
                            'cancelada' => 'Cancelada',
                            default => ucfirst($state),
                        })
                        ->color(fn (string $state) => match ($state) {
                            'pendiente' => 'warning',
                            'en_proceso' => 'info',
                            'completada' => 'success',
                            'cancelada' => 'danger',
                            default => 'gray',
                        }),

                    TextEntry::make('patient.first_name')
                        ->label('Paciente')
                        ->formatStateUsing(fn ($state, $record) => $record->patient?->first_name.' '.$record->patient?->last_name.
                            ' — CI: '.$record->patient?->ci
                        ),

                    TextEntry::make('receptionist.name')
                        ->label('Recepcionista'),

                    TextEntry::make('responsibleUser.name')
                        ->label(fn (): string => $this->record->type === 'imagen'
                            ? 'Tecnólogo de imagen asignado'
                            : 'Bioquímico asignado')
                        ->badge()
                        ->color('info')
                        ->placeholder('Sin asignar'),

                    TextEntry::make('scheduled_date')
                        ->label('Fecha programada')
                        ->date('d/m/Y')
                        ->placeholder('Sin programar'),

                    TextEntry::make('scheduled_time')
                        ->label('Hora programada')
                        ->time('H:i')
                        ->placeholder('—'),

                    TextEntry::make('equipment.name')
                        ->label('Equipo de imagen asignado')
                        ->badge()
                        ->color('warning')
                        ->placeholder('Sin equipo asignado')
                        ->visible(fn ($record) => $record->type === 'imagen'),

                    TextEntry::make('created_at')
                        ->label('Creada el')
                        ->dateTime('d/m/Y H:i'),
                ]),

            Section::make('Exámenes asignados')
                ->icon('heroicon-o-beaker')
                ->schema([
                    RepeatableEntry::make('exams')
                        ->label('')
                        ->schema([
                            TextEntry::make('name')
                                ->label('Nombre')
                                ->weight('bold'),

                            TextEntry::make('type')
                                ->label('Tipo')
                                ->badge()
                                ->formatStateUsing(fn (string $state) => match ($state) {
                                    'laboratorio' => 'Laboratorio',
                                    'imagen' => 'Imagen',
                                    default => ucfirst($state),
                                })
                                ->color(fn (string $state) => match ($state) {
                                    'laboratorio' => 'info',
                                    'imagen' => 'success',
                                    default => 'gray',
                                }),

                            TextEntry::make('price')
                                ->label('Precio')
                                ->money('BOB'),

                            TextEntry::make('exam_requirements')
                                ->label('Requisitos previos')
                                ->getStateUsing(
                                    fn ($record) => $record->requirements->pluck('description')->all()
                                )
                                ->listWithLineBreaks()
                                ->bulleted()
                                ->placeholder('Sin requisitos previos.')
                                ->columnSpanFull(),
                        ])
                        ->columns(4)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
