<?php

namespace App\Filament\Resources\FallEventResource\Pages;

use App\Filament\Resources\FallEventResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFallEvents extends ListRecords
{
    protected static string $resource = FallEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
