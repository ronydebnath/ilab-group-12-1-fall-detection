<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FallEventResource\Pages;
use App\Models\FallEvent;
use App\Models\User;
use App\Models\ElderlyProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FallEventResource extends Resource
{
    protected static ?string $model = FallEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'Fall Detection';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('elderly_id')
                    ->label('Elderly Person')
                    ->options(
                        ElderlyProfile::with('user')->get()->mapWithKeys(function ($profile) {
                            return [$profile->id => optional($profile->user)->name ?: 'Unknown'];
                        })
                    )
                    ->required()
                    ->searchable(),
                Forms\Components\DateTimePicker::make('detected_at')
                    ->required()
                    ->default(now()),
                Forms\Components\TextInput::make('confidence_score')
                    ->label('Confidence Score (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%'),
                Forms\Components\Select::make('status')
                    ->options([
                        'detected' => 'Detected',
                        'confirmed' => 'Confirmed',
                        'false_alarm' => 'False Alarm',
                        'resolved' => 'Resolved',
                    ])
                    ->required()
                    ->default('detected'),
                Forms\Components\Textarea::make('notes')
                    ->rows(3)
                    ->required(fn (Forms\Get $get) => $get('status') === 'false_alarm'),
                Forms\Components\Select::make('resolved_by')
                    ->label('Resolved By')
                    ->options(
                        User::whereIn('role', ['admin', 'carer'])
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->visible(fn (Forms\Get $get) => in_array($get('status'), ['resolved', 'false_alarm'])),
                Forms\Components\DateTimePicker::make('resolved_at')
                    ->label('Resolved At')
                    ->visible(fn (Forms\Get $get) => in_array($get('status'), ['resolved', 'false_alarm'])),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('elderly.name')
                    ->label('Elderly Person')
                    ->getStateUsing(fn ($record) => optional(optional($record->elderly)->user)->name)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('detected_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('confidence_score')
                    ->numeric(2)
                    ->suffix('%'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'detected',
                        'warning' => 'confirmed',
                        'success' => 'resolved',
                        'gray' => 'false_alarm',
                    ])
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state === 'false_alarm' ? 'False Alarm' : ucfirst($state)),
                Tables\Columns\TextColumn::make('resolvedBy.name')
                    ->label('Resolved By')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('resolved_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('detected_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'detected' => 'Detected',
                        'confirmed' => 'Confirmed',
                        'false_alarm' => 'False Alarm',
                        'resolved' => 'Resolved',
                    ]),
                Tables\Filters\TernaryFilter::make('false_alarm_only')
                    ->label('Show Only False Alarms')
                    ->queries(
                        true: fn ($query) => $query->where('status', 'false_alarm'),
                        false: fn ($query) => $query->where('status', '!=', 'false_alarm'),
                    ),
                Tables\Filters\SelectFilter::make('elderly_id')
                    ->label('Elderly Person')
                    ->options(
                        ElderlyProfile::with('user')->get()->mapWithKeys(function ($profile) {
                            return [$profile->id => optional($profile->user)->name ?: 'Unknown'];
                        })
                    )
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('markFalseAlarm')
                    ->label('Mark as False Alarm')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn ($record) => $record->status !== 'false_alarm')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Reason/Notes')
                            ->required()
                    ])
                    ->action(function ($record, $data) {
                        $record->update([
                            'status' => 'false_alarm',
                            'notes' => $data['notes'],
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('bulkFalseAlarm')
                        ->label('Mark as False Alarm')
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['status' => 'false_alarm']);
                            }
                        }),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['elderly', 'resolvedBy']);
    }
}
