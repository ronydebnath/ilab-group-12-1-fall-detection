<?php

namespace App\Filament\Resources\ElderlyProfileResource\Pages;

use App\Filament\Resources\ElderlyProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListElderlyProfiles extends ListRecords
{
    protected static string $resource = ElderlyProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 