<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlertSystemConfigResource\Pages;
use App\Models\AlertSystemConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AlertSystemConfigResource extends Resource
{
    protected static ?string $model = AlertSystemConfig::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationGroup = 'Fall Detection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\Section::make('Alert Settings')
                    ->schema([
                        Forms\Components\Select::make('settings.notification_channels')
                            ->multiple()
                            ->options([
                                'email' => 'Email',
                                'sms' => 'SMS',
                                'push' => 'Push Notification',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('settings.alert_threshold')
                            ->numeric()
                            ->label('Alert Threshold (seconds)')
                            ->required(),
                        Forms\Components\TextInput::make('settings.escalation_delay')
                            ->numeric()
                            ->label('Escalation Delay (seconds)')
                            ->required(),
                        Forms\Components\TextInput::make('settings.max_escalation_level')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->required(),
                        Forms\Components\Section::make('Contact Priority')
                            ->schema([
                                Forms\Components\Toggle::make('settings.contact_priority.primary')
                                    ->label('Primary Contact')
                                    ->required(),
                                Forms\Components\Toggle::make('settings.contact_priority.secondary')
                                    ->label('Secondary Contact')
                                    ->required(),
                                Forms\Components\Toggle::make('settings.contact_priority.emergency')
                                    ->label('Emergency Contact')
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
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
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlertSystemConfigs::route('/'),
            'create' => Pages\CreateAlertSystemConfig::route('/create'),
            'edit' => Pages\EditAlertSystemConfig::route('/{record}/edit'),
        ];
    }
} 