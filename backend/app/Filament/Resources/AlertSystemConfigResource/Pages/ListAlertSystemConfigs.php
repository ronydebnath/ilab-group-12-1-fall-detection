<?php

namespace App\Filament\Resources\AlertSystemConfigResource\Pages;

use App\Filament\Resources\AlertSystemConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAlertSystemConfigs extends ListRecords
{
    protected static string $resource = AlertSystemConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 