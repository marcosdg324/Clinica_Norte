<?php

namespace App\Domains\Orders\Filament\Resources\ExamResource\Pages;

use App\Domains\Orders\Filament\Resources\ExamResource;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewExam extends ViewRecord
{
    protected static string $resource = ExamResource::class;

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

            Section::make('Datos del Examen')
                ->icon('heroicon-o-beaker')
                ->columns(2)
                ->schema([
                    TextEntry::make('name')
                        ->label('Nombre')
                        ->weight('bold'),

                    TextEntry::make('type')
                        ->label('Tipo')
                        ->badge()
                        ->formatStateUsing(fn (string $state) => match ($state) {
                            'laboratorio' => 'Laboratorio',
                            'imagen'      => 'Imagen',
                            default       => ucfirst($state),
                        })
                        ->color(fn (string $state) => match ($state) {
                            'laboratorio' => 'info',
                            'imagen'      => 'success',
                            default       => 'gray',
                        }),

                    TextEntry::make('price')
                        ->label('Precio')
                        ->money('BOB'),

                    IconEntry::make('is_urgent_possible')
                        ->label('¿Urgencia posible?')
                        ->boolean()
                        ->trueColor('warning')
                        ->falseColor('gray'),

                    TextEntry::make('description')
                        ->label('Descripción')
                        ->columnSpanFull()
                        ->placeholder('Sin descripción.'),
                ]),

            Section::make('Requisitos previos')
                ->icon('heroicon-o-clipboard-document-check')
                ->schema([
                    RepeatableEntry::make('requirements')
                        ->label('')
                        ->schema([
                            TextEntry::make('description')
                                ->label('Requisito'),
                        ])
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
