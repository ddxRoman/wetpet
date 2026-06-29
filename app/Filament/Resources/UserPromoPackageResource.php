<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserPromoPackageResource\Pages;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserPromoPackageResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon  = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Рекламные пакеты';
    protected static ?string $navigationGroup = 'Монетизация';
    protected static ?string $pluralLabel     = 'Рекламные пакеты';
    protected static ?string $label           = 'Пользователь';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('name')->label('Пользователь')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                IconColumn::make('has_promo_package')
                    ->label('Пакет активен')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextColumn::make('promo_package_expires_at')
                    ->label('Действует до')
                    ->date('d.m.Y')
                    ->placeholder('Бессрочно'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('has_promo_package')
                    ->label('Рекламный пакет'),
            ])
            ->actions([
                Action::make('manage_package')
                    ->label('Управление пакетом')
                    ->icon('heroicon-o-pencil-square')
                    ->form([
                        Toggle::make('has_promo_package')
                            ->label('Рекламный пакет активен')
                            ->default(fn ($record) => (bool) $record->has_promo_package)
                            ->onColor('success'),
                        DatePicker::make('promo_package_expires_at')
                            ->label('Действует до (оставьте пустым для бессрочного)')
                            ->default(fn ($record) => $record->promo_package_expires_at)
                            ->minDate(now()),
                    ])
                    ->action(function (array $data, $record) {
                        $record->update([
                            'has_promo_package'          => $data['has_promo_package'],
                            'promo_package_expires_at'   => $data['promo_package_expires_at'] ?? null,
                        ]);
                        Notification::make()
                            ->title('Рекламный пакет обновлён')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserPromoPackages::route('/'),
        ];
    }
}
