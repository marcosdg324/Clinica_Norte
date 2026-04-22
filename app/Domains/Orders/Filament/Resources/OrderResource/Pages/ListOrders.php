<?php

namespace App\Domains\Orders\Filament\Resources\OrderResource\Pages;

use App\Domains\Orders\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
