<?php

namespace App\Filament\Resources\FallEventResource\Pages;

use App\Filament\Resources\FallEventResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\AlertSystemService;

class CreateFallEvent extends CreateRecord
{
    protected static string $resource = FallEventResource::class;

    protected function afterCreate(): void
    {
        parent::afterCreate();
        // Send notifications if status is 'detected'
        if ($this->record->status === 'detected') {
            app(AlertSystemService::class)->processFallEvent($this->record);
        }
    }
}
