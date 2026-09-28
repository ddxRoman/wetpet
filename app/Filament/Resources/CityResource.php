<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CityResource\Pages;
use App\Models\City;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static ?string $navigationIcon  = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Города';
    protected static ?string $navigationGroup = 'Города и Регионы';
    protected static ?int    $navigationSort  = 2;

    protected static ?string $modelLabel       = 'Город';
    protected static ?string $pluralModelLabel = 'Города';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Город')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Название города')
                        ->required()
                        ->maxLength(255),

                    // Регион как отдельная сущность не хранится: он существует, пока в нём есть город.
                    // Поэтому новый регион создаётся прямо здесь — вместе с первым городом.
                    Forms\Components\Toggle::make('new_region')
                        ->label('Новый регион (нет в списке)')
                        ->live()
                        ->dehydrated(true)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('region')
                        ->label('Регион')
                        ->searchable()
                        ->options(function (?City $record) {
                            $options = static::regionOptions();
                            if ($record?->region && ! isset($options[$record->region])) {
                                $options[$record->region] = $record->region;
                            }
                            return $options;
                        })
                        ->visible(fn (Forms\Get $get) => ! $get('new_region'))
                        ->required(fn (Forms\Get $get) => ! $get('new_region')),

                    Forms\Components\TextInput::make('region_new')
                        ->label('Название нового региона')
                        ->helperText('Регион появится в списке вместе с этим городом.')
                        ->maxLength(255)
                        ->visible(fn (Forms\Get $get) => (bool) $get('new_region'))
                        ->required(fn (Forms\Get $get) => (bool) $get('new_region')),

                    Forms\Components\TextInput::make('country')
                        ->label('Страна')
                        ->default('Россия')
                        ->maxLength(255),

                    Forms\Components\Toggle::make('large_city')
                        ->label('Крупный город'),

                    Forms\Components\Select::make('verified')
                        ->label('Статус')
                        ->options([
                            'confirmed'   => 'Подтверждён',
                            'unconfirmed' => 'Не подтверждён',
                        ])
                        ->default('confirmed')
                        ->required(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Город')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('region')->label('Регион')->searchable()->sortable(),
                Tables\Columns\IconColumn::make('large_city')->label('Крупный')->boolean()->toggleable(),
                Tables\Columns\TextColumn::make('verified')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'confirmed' ? 'Подтверждён' : 'Не подтверждён')
                    ->color(fn ($state) => $state === 'confirmed' ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('created_at')->label('Добавлен')->dateTime('d.m.Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('region')
                    ->label('Регион')
                    ->searchable()
                    ->options(fn () => static::regionOptions()),
                Tables\Filters\SelectFilter::make('verified')
                    ->label('Статус')
                    ->options(['confirmed' => 'Подтверждён', 'unconfirmed' => 'Не подтверждён']),
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

    // Регионы берём из справочника городов
    public static function regionOptions(): array
    {
        return City::whereNotNull('region')
            ->where('region', '!=', '')
            ->distinct()
            ->orderBy('region')
            ->pluck('region', 'region')
            ->toArray();
    }

    // Из виртуальных полей формы (new_region / region_new) собираем реальную колонку region
    public static function prepareRegion(array $data): array
    {
        if (! empty($data['new_region'])) {
            $data['region'] = trim($data['region_new'] ?? '');
        }
        unset($data['new_region'], $data['region_new']);

        return $data;
    }

    // Уникальный слаг из названия (колонка slug уникальна)
    public static function makeSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'city';
        $slug = $base;
        $i = 2;
        while (City::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCities::route('/'),
            'create' => Pages\CreateCity::route('/create'),
            'edit'   => Pages\EditCity::route('/{record}/edit'),
        ];
    }
}
