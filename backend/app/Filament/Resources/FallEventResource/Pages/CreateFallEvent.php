<?php

namespace App\Filament\Resources\FallEventResource\Pages;

use App\Filament\Resources\FallEventResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\AlertSystemService;

class CreateFallEvent extends CreateRecord
{
    protected static string $resource = FallEventResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $record = parent::handleRecordCreation($data);
        // Send notifications if status is 'detected'
        if ($record->status === 'detected') {
            app(\App\Services\AlertSystemService::class)->processFallEvent($record);
        }
        return $record;
    }
}
