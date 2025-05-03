<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FallEventResource\Pages;
use App\Filament\Resources\FallEventResource\RelationManagers;
use App\Models\FallEvent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FallEventResource extends Resource
{
    protected static ?string $model = FallEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'Fall Detection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('elderly_id')
                    ->relationship('elderly', 'full_name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\DateTimePicker::make('detected_at')
                    ->required(),
                Forms\Components\DateTimePicker::make('resolved_at'),
                Forms\Components\Select::make('status')
                    ->options([
                        'detected' => 'Detected',
                        'safe' => 'Safe',
                        'alerted' => 'Alerted',
                        'resolved' => 'Resolved',
                        'false_alarm' => 'False Alarm',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('false_alarm')
                    ->label('False Alarm'),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('sensor_data')
                    ->keyLabel('Sensor')
                    ->valueLabel('Value')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('elderly.full_name')
                    ->label('Elderly')
                    ->searchable(),
                Tables\Columns\TextColumn::make('detected_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'detected' => 'warning',
                        'safe' => 'success',
                        'alerted' => 'danger',
                        'resolved' => 'success',
                        'false_alarm' => 'gray',
                        default => 'secondary',
                    }),
                Tables\Columns\IconColumn::make('false_alarm')
                    ->boolean(),
                Tables\Columns\TextColumn::make('resolved_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(30),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'detected' => 'Detected',
                        'safe' => 'Safe',
                        'alerted' => 'Alerted',
                        'resolved' => 'Resolved',
                        'false_alarm' => 'False Alarm',
                    ]),
                Tables\Filters\TrashedFilter::make(),
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
            'index' => Pages\ListFallEvents::route('/'),
            'create' => Pages\CreateFallEvent::route('/create'),
            'edit' => Pages\EditFallEvent::route('/{record}/edit'),
        ];
    }
}
