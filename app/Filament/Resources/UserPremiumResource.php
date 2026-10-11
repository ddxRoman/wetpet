<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserPremiumResource\Pages;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserPremiumResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon  = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Премиум-подписки';
    protected static ?string $navigationGroup = 'Монетизация';
    protected static ?string $pluralLabel     = 'Премиум-подписки';
    protected static ?string $label           = 'Пользователь';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('name')->label('Пользователь')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                IconColumn::make('premium_active')
                    ->label('Премиум активен')
                    ->boolean()
                    ->getStateUsing(fn (User $record) => $record->isPremium())
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextColumn::make('premium_until')
                    ->label('Действует до')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->defaultSort('premium_until', 'desc')
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label('Только с активным Премиум')
                    ->query(fn ($query) => $query->where('premium_until', '>', now())),
            ])
            ->actions([
                Action::make('manage_premium')
                    ->label('Управление подпиской')
                    ->icon('heroicon-o-pencil-square')
                    ->form([
                        DateTimePicker::make('premium_until')
                            ->label('Премиум действует до (пустое поле — отключить)')
                            ->default(fn ($record) => $record->premium_until),
                    ])
                    ->action(function (array $data, $record) {
                        $record->update(['premium_until' => $data['premium_until'] ?? null]);

                        Notification::make()
                            ->title('Подписка обновлена')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserPremiums::route('/'),
        ];
    }
}
