<?php

namespace App\Filament\Resources\ElderlyProfileResource\Pages;

use App\Filament\Resources\ElderlyProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditElderlyProfile extends EditRecord
{
    protected static string $resource = ElderlyProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 