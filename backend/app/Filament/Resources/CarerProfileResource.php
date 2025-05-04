<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarerProfileResource\Pages;
use App\Models\CarerProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CarerProfileResource extends Resource
{
    protected static ?string $model = CarerProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Forms\Components\TextInput::make('phone_number')
                            ->required()
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('emergency_contact_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('emergency_contact_phone')
                            ->required()
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('address')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Professional Information')
                    ->schema([
                        Forms\Components\TextInput::make('qualification')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('specialization')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('years_of_experience')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(50),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'on_leave' => 'On Leave',
                            ])
                            ->required()
                            ->default('active'),
                    ])->columns(2),

                Forms\Components\Section::make('Workload Management')
                    ->schema([
                        Forms\Components\TextInput::make('max_elderly_capacity')
                            ->numeric()
                            ->default(5)
                            ->minValue(1)
                            ->maxValue(20),
                        Forms\Components\TextInput::make('current_elderly_count')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                        Forms\Components\KeyValue::make('availability_schedule')
                            ->keyLabel('Day')
                            ->valueLabel('Hours')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('qualification')
                    ->searchable(),
                Tables\Columns\TextColumn::make('years_of_experience')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_elderly_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'on_leave' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('last_active_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'on_leave' => 'On Leave',
                    ]),
            ])
            ->actions([
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
            'index' => Pages\ListCarerProfiles::route('/'),
            'create' => Pages\CreateCarerProfile::route('/create'),
            'edit' => Pages\EditCarerProfile::route('/{record}/edit'),
        ];
    }
} 