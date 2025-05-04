<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ElderlyProfileResource\Pages;
use App\Models\ElderlyProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ElderlyProfileResource extends Resource
{
    protected static ?string $model = ElderlyProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Elderly Care';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->required()
                            ->maxDate(now()),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ])
                            ->required(),
                        Forms\Components\FileUpload::make('profile_photo')
                            ->image()
                            ->directory('profile-photos'),
                        Forms\Components\TextInput::make('height')
                            ->numeric()
                            ->suffix('cm'),
                        Forms\Components\TextInput::make('weight')
                            ->numeric()
                            ->suffix('kg'),
                        Forms\Components\TextInput::make('blood_type')
                            ->maxLength(5),
                        Forms\Components\TextInput::make('national_id')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('primary_phone')
                            ->tel()
                            ->required()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('secondary_phone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('current_address')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('emergency_contact_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('emergency_contact_phone')
                            ->tel()
                            ->required()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('emergency_contact_relationship')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Health Information')
                    ->schema([
                        Forms\Components\Textarea::make('medical_conditions')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('allergies')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('current_medications')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('disabilities')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('mobility_status')
                            ->options([
                                'independent' => 'Independent',
                                'needs_assistance' => 'Needs Assistance',
                                'wheelchair_bound' => 'Wheelchair Bound',
                            ])
                            ->required(),
                        Forms\Components\Select::make('vision_status')
                            ->options([
                                'normal' => 'Normal',
                                'glasses' => 'Glasses',
                                'impaired' => 'Impaired',
                            ])
                            ->required(),
                        Forms\Components\Select::make('hearing_status')
                            ->options([
                                'normal' => 'Normal',
                                'hearing_aid' => 'Hearing Aid',
                                'impaired' => 'Impaired',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('last_medical_checkup'),
                    ])->columns(2),

                Forms\Components\Section::make('Care Information')
                    ->schema([
                        Forms\Components\Select::make('primary_carer_id')
                            ->relationship('primaryCarer', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->rule(function (callable $get) {
                                return function ($attribute, $value, $fail) use ($get) {
                                    if ($value && $get('user_id') && $value == $get('user_id')) {
                                        $fail('The elderly person cannot be their own primary carer.');
                                    }
                                };
                            }),
                        Forms\Components\Select::make('secondary_carer_id')
                            ->relationship('secondaryCarer', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('care_level')
                            ->options([
                                'basic' => 'Basic',
                                'moderate' => 'Moderate',
                                'intensive' => 'Intensive',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('special_care_instructions')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('daily_routine_notes')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('dietary_restrictions')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('preferred_language')
                            ->required()
                            ->maxLength(50),
                    ])->columns(2),

                Forms\Components\Section::make('Device Information')
                    ->schema([
                        Forms\Components\TextInput::make('device_id')
                            ->maxLength(255),
                        Forms\Components\Select::make('device_status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('last_device_check'),
                        Forms\Components\TextInput::make('device_battery_level')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        Forms\Components\TextInput::make('device_location')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\TextInput::make('preferred_hospital')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('insurance_information')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('living_situation')
                            ->options([
                                'lives_alone' => 'Lives Alone',
                                'with_family' => 'With Family',
                                'assisted_living' => 'Assisted Living',
                            ])
                            ->required(),
                        Forms\Components\Select::make('activity_level')
                            ->options([
                                'active' => 'Active',
                                'moderate' => 'Moderate',
                                'sedentary' => 'Sedentary',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('age')
                    ->sortable(),
                Tables\Columns\TextColumn::make('primary_phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('primaryCarer.name')
                    ->label('Primary Carer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('care_level')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'basic' => 'success',
                        'moderate' => 'warning',
                        'intensive' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('device_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('care_level')
                    ->options([
                        'basic' => 'Basic',
                        'moderate' => 'Moderate',
                        'intensive' => 'Intensive',
                    ]),
                Tables\Filters\SelectFilter::make('device_status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListElderlyProfiles::route('/'),
            'create' => Pages\CreateElderlyProfile::route('/create'),
            'view' => Pages\ViewElderlyProfile::route('/{record}'),
            'edit' => Pages\EditElderlyProfile::route('/{record}/edit'),
        ];
    }
} 