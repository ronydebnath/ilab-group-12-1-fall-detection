<?php

namespace App\Filament\Resources\FallEventResource\Pages;

use App\Filament\Resources\FallEventResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFallEvent extends EditRecord
{
    protected static string $resource = FallEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
