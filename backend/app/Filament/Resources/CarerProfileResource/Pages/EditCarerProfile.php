<?php

namespace App\Filament\Resources\CarerProfileResource\Pages;

use App\Filament\Resources\CarerProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCarerProfile extends EditRecord
{
    protected static string $resource = CarerProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 