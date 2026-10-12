<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BoostPaymentResource\Pages;
use App\Models\BoostPayment;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BoostPaymentResource extends Resource
{
    protected static ?string $model = BoostPayment::class;

    protected static ?string $navigationIcon  = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Платежи за продвижение';
    protected static ?string $navigationGroup = 'Монетизация';
    protected static ?string $pluralLabel     = 'Платежи за продвижение';
    protected static ?string $label           = 'Платёж';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('user.name')->label('Пользователь')->searchable(),
                TextColumn::make('entity_type')
                    ->label('Тип')
                    ->formatStateUsing(fn ($state) => $state === 'clinic' ? 'Клиника' : 'Организация')
                    ->badge(),
                TextColumn::make('entity_id')->label('ID карточки')->sortable(),
                TextColumn::make('package')
                    ->label('Пакет')
                    ->formatStateUsing(fn ($state) => config("boost.plans.{$state}.title", $state)),
                TextColumn::make('period')
                    ->label('Период')
                    ->formatStateUsing(fn ($state) => config("boost.periods.{$state}.label", $state)),
                TextColumn::make('amount')->label('Сумма, ₽')->sortable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'paid'    => 'success',
                        'pending' => 'warning',
                        default   => 'gray',
                    }),
                TextColumn::make('gateway')->label('Шлюз')->toggleable(),
                TextColumn::make('paid_at')->label('Оплачен')->dateTime('d.m.Y H:i')->sortable()->placeholder('—'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options(['pending' => 'Ожидает оплаты', 'paid' => 'Оплачен', 'canceled' => 'Отменён']),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBoostPayments::route('/'),
        ];
    }
}
