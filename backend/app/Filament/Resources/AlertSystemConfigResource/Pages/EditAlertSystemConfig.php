<?php

namespace App\Filament\Resources\AlertSystemConfigResource\Pages;

use App\Filament\Resources\AlertSystemConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAlertSystemConfig extends EditRecord
{
    protected static string $resource = AlertSystemConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 