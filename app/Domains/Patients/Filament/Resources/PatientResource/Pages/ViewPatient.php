<?php

namespace App\Domains\Patients\Filament\Resources\PatientResource\Pages;

use App\Domains\Patients\Filament\Resources\PatientResource;
use App\Domains\Patients\Models\Patient;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewPatient extends ViewRecord
{
    protected static string $resource = PatientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            Section::make('Identificación')
                ->icon('heroicon-o-identification')
                ->columns(3)
                ->schema([
                    TextEntry::make('ci')
                        ->label('Cédula de Identidad')
                        ->copyable()
                        ->badge()
                        ->color('gray'),

                    TextEntry::make('first_name')
                        ->label('Nombre'),

                    TextEntry::make('last_name')
                        ->label('Apellido'),

                    TextEntry::make('birth_date')
                        ->label('Fecha de nacimiento')
                        ->date('d/m/Y'),

                    TextEntry::make('age')
                        ->label('Edad')
                        ->getStateUsing(fn (Patient $record) => $record->birth_date->age . ' años'),

                    TextEntry::make('gender')
                        ->label('Género')
                        ->formatStateUsing(fn (string $state) => ucfirst($state))
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'masculino' => 'info',
                            'femenino'  => 'success',
                            default     => 'warning',
                        }),
                ]),

            Section::make('Datos de Contacto')
                ->icon('heroicon-o-phone')
                ->columns(2)
                ->schema([
                    TextEntry::make('phone')
                        ->label('Teléfono')
                        ->copyable(),

                    TextEntry::make('email')
                        ->label('Email')
                        ->copyable()
                        ->placeholder('Sin email registrado'),

                    TextEntry::make('address')
                        ->label('Dirección')
                        ->placeholder('Sin dirección registrada')
                        ->columnSpanFull(),
                ]),

            Section::make('Historial Clínico')
                ->icon('heroicon-o-clipboard-document-list')
                ->schema([
                    TextEntry::make('medical_history_notes')
                        ->label('Notas del historial clínico')
                        ->placeholder('Sin notas registradas')
                        ->columnSpanFull(),

                    // ── Placeholder para órdenes (Módulo 3) ──────────────────
                    TextEntry::make('orders_placeholder')
                        ->label('Órdenes')
                        ->getStateUsing(fn () => 'El módulo de órdenes aún no está implementado.')
                        ->color('gray')
                        ->columnSpanFull(),
                ]),

            Section::make('Registro')
                ->icon('heroicon-o-clock')
                ->columns(2)
                ->schema([
                    TextEntry::make('created_at')
                        ->label('Creado')
                        ->dateTime('d/m/Y H:i'),

                    TextEntry::make('updated_at')
                        ->label('Última modificación')
                        ->dateTime('d/m/Y H:i'),
                ]),
        ]);
    }
}
