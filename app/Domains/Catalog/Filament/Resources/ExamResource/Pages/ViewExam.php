<?php

namespace App\Domains\Catalog\Filament\Resources\ExamResource\Pages;

use App\Domains\Catalog\Filament\Resources\ExamResource;
use Filament\Actions;
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

                    TextEntry::make('category.name')
                        ->label('Categoría')
                        ->badge()
                        ->color('gray')
                        ->placeholder('Sin categoría'),
                ]),

            Section::make('Requisitos Previos')
                ->icon('heroicon-o-clipboard-document-list')
                ->schema([
                    RepeatableEntry::make('requirements')
                        ->label('')
                        ->schema([
                            TextEntry::make('description')
                                ->hiddenLabel()
                                ->columnSpanFull(),
                        ])
                        ->columns(1)
                        ->contained(false),
                ]),
        ]);
    }
}
