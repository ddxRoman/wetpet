<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AutoRenewalResource\Pages;
use App\Models\AutoRenewal;
use App\Services\Payments\AutoRenewalService;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AutoRenewalResource extends Resource
{
    protected static ?string $model = AutoRenewal::class;

    protected static ?string $navigationIcon  = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'Автопродления';
    protected static ?string $navigationGroup = 'Монетизация';
    protected static ?string $pluralLabel     = 'Автопродления';
    protected static ?string $label           = 'Автопродление';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = AutoRenewal::where('status', 'active')->count();

        return $count ? (string) $count : null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('user.name')->label('Пользователь')->searchable(),
                TextColumn::make('kind')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'premium' ? 'Премиум' : 'Продвижение'),
                TextColumn::make('title')
                    ->label('Что продлевается')
                    ->getStateUsing(fn (AutoRenewal $record) => $record->title())
                    ->wrap(),
                TextColumn::make('plan')
                    ->label('Период')
                    ->formatStateUsing(fn ($state, AutoRenewal $record) => $record->planLabel()),
                TextColumn::make('amount')
                    ->label('Сумма, ₽')
                    ->getStateUsing(fn (AutoRenewal $record) => $record->amount())
                    ->placeholder('—'),
                TextColumn::make('payment_method_title')->label('Способ оплаты')->placeholder('—'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ['active' => 'Включено', 'canceled' => 'Отключено', 'failed' => 'Ошибка списания'][$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success',
                        'failed' => 'danger',
                        default  => 'gray',
                    }),
                TextColumn::make('next_charge')
                    ->label('Следующее списание')
                    ->getStateUsing(function (AutoRenewal $record) {
                        if (! $record->isActive()) {
                            return null;
                        }

                        $expires = app(AutoRenewalService::class)->expiresAt($record);

                        return $expires
                            ? $expires->copy()->subHours((int) config('premium.auto_renew.charge_before_hours'))->format('d.m.Y')
                            : null;
                    })
                    ->placeholder('—'),
                TextColumn::make('attempts')->label('Неудачных попыток')->toggleable(),
                TextColumn::make('last_attempt_at')->label('Последняя попытка')->dateTime('d.m.Y H:i')->placeholder('—')->toggleable(),
                TextColumn::make('canceled_at')->label('Отключено')->dateTime('d.m.Y H:i')->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Создано')->dateTime('d.m.Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options(['active' => 'Включено', 'canceled' => 'Отключено', 'failed' => 'Ошибка списания']),
                Tables\Filters\SelectFilter::make('kind')
                    ->label('Тип')
                    ->options(['premium' => 'Премиум', 'boost' => 'Продвижение']),
            ])
            ->actions([
                Tables\Actions\Action::make('disable')
                    ->label('Отключить')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Списания прекратятся, сохранённый способ оплаты будет удалён. Подписка действует до конца оплаченного срока.')
                    ->visible(fn (AutoRenewal $record) => $record->isActive())
                    ->action(function (AutoRenewal $record) {
                        app(AutoRenewalService::class)->cancel($record);

                        Notification::make()->title('Автопродление отключено')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAutoRenewals::route('/'),
        ];
    }
}
