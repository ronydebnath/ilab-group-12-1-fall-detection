<?php

namespace App\Filament\Resources\ElderlyProfileResource\Pages;

use App\Filament\Resources\ElderlyProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewElderlyProfile extends ViewRecord
{
    protected static string $resource = ElderlyProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Basic Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('full_name')
                            ->label('Full Name'),
                        Infolists\Components\TextEntry::make('date_of_birth')
                            ->date(),
                        Infolists\Components\TextEntry::make('gender')
                            ->badge(),
                        Infolists\Components\ImageEntry::make('profile_photo')
                            ->circular(),
                        Infolists\Components\TextEntry::make('height')
                            ->suffix(' cm'),
                        Infolists\Components\TextEntry::make('weight')
                            ->suffix(' kg'),
                        Infolists\Components\TextEntry::make('blood_type'),
                        Infolists\Components\TextEntry::make('national_id'),
                    ])->columns(2),

                Infolists\Components\Section::make('Contact Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('primary_phone'),
                        Infolists\Components\TextEntry::make('secondary_phone'),
                        Infolists\Components\TextEntry::make('email'),
                        Infolists\Components\TextEntry::make('current_address')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('emergency_contact_name'),
                        Infolists\Components\TextEntry::make('emergency_contact_phone'),
                        Infolists\Components\TextEntry::make('emergency_contact_relationship'),
                    ])->columns(2),

                Infolists\Components\Section::make('Health Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('medical_conditions')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('allergies')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('current_medications')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('disabilities')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('mobility_status')
                            ->badge(),
                        Infolists\Components\TextEntry::make('vision_status')
                            ->badge(),
                        Infolists\Components\TextEntry::make('hearing_status')
                            ->badge(),
                        Infolists\Components\TextEntry::make('last_medical_checkup')
                            ->date(),
                    ])->columns(2),

                Infolists\Components\Section::make('Care Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('primaryCarer.name')
                            ->label('Primary Carer'),
                        Infolists\Components\TextEntry::make('secondaryCarer.name')
                            ->label('Secondary Carer'),
                        Infolists\Components\TextEntry::make('care_level')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'basic' => 'success',
                                'moderate' => 'warning',
                                'intensive' => 'danger',
                            }),
                        Infolists\Components\TextEntry::make('special_care_instructions')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('daily_routine_notes')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('dietary_restrictions')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('preferred_language'),
                    ])->columns(2),

                Infolists\Components\Section::make('Device Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('device_id'),
                        Infolists\Components\TextEntry::make('device_status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'inactive' => 'danger',
                            }),
                        Infolists\Components\TextEntry::make('last_device_check')
                            ->date(),
                        Infolists\Components\TextEntry::make('device_battery_level')
                            ->suffix('%'),
                        Infolists\Components\TextEntry::make('device_location'),
                    ])->columns(2),

                Infolists\Components\Section::make('Additional Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('preferred_hospital'),
                        Infolists\Components\TextEntry::make('insurance_information')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('living_situation')
                            ->badge(),
                        Infolists\Components\TextEntry::make('activity_level')
                            ->badge(),
                        Infolists\Components\TextEntry::make('notes')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
} 