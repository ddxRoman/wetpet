<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BoostSubscriptionResource\Pages;
use App\Models\BoostSubscription;
use Filament\Forms\Components\DateTimePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BoostSubscriptionResource extends Resource
{
    protected static ?string $model = BoostSubscription::class;

    protected static ?string $navigationIcon  = 'heroicon-o-rocket-launch';
    protected static ?string $navigationLabel = 'Пакеты продвижения';
    protected static ?string $navigationGroup = 'Монетизация';
    protected static ?string $pluralLabel     = 'Пакеты продвижения';
    protected static ?string $label           = 'Пакет продвижения';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('entity_type')
                    ->label('Тип')
                    ->formatStateUsing(fn ($state) => ['clinic' => 'Клиника', 'doctor' => 'Врач', 'specialist' => 'Специалист'][$state] ?? 'Организация')
                    ->badge(),
                TextColumn::make('entity_name')
                    ->label('Карточка')
                    ->getStateUsing(fn (BoostSubscription $record) => $record->entityModel()?->name ?? '— (удалена)')
                    ->description(fn (BoostSubscription $record) => 'ID ' . $record->entity_id),
                TextColumn::make('package')
                    ->label('Пакет')
                    ->formatStateUsing(fn ($state) => config("boost.plans.{$state}.title", $state)),
                IconColumn::make('active')
                    ->label('Активен')
                    ->boolean()
                    ->getStateUsing(fn (BoostSubscription $record) => $record->isActive())
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextColumn::make('expires_at')
                    ->label('Действует до')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->defaultSort('expires_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label('Только активные')
                    ->query(fn ($query) => $query->where('expires_at', '>', now())),
                Tables\Filters\SelectFilter::make('package')
                    ->label('Пакет')
                    ->options(fn () => collect(config('boost.plans'))->map(fn ($p) => $p['title'])->all()),
                Tables\Filters\SelectFilter::make('entity_type')
                    ->label('Тип карточки')
                    ->options(['clinic' => 'Клиника', 'organization' => 'Организация', 'doctor' => 'Врач', 'specialist' => 'Специалист']),
            ])
            ->actions([
                Action::make('manage')
                    ->label('Изменить срок')
                    ->icon('heroicon-o-pencil-square')
                    ->form([
                        DateTimePicker::make('expires_at')
                            ->label('Пакет действует до (пустое поле — отключить)')
                            ->default(fn ($record) => $record->expires_at),
                    ])
                    ->action(function (array $data, $record) {
                        $record->update(['expires_at' => $data['expires_at'] ?? null]);

                        Notification::make()->title('Срок обновлён')->success()->send();
                    }),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBoostSubscriptions::route('/'),
        ];
    }
}
