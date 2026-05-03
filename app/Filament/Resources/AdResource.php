<?php

namespace App\Filament\Resources;

use App\Models\Ad;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\AdResource\Pages;
use Filament\Forms\Components\{FileUpload, Select, TextInput, Textarea, Toggle, Section};
use Filament\Tables\Columns\{ImageColumn, TextColumn, IconColumn};
use Filament\Tables;

class AdResource extends Resource
{
    protected static ?string $model = Ad::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основная информация')
                    ->schema([
                        TextInput::make('title')
                            ->label('Заголовок')
                            ->required()
                            ->maxLength(255),
                        
                        Select::make('user_id')
                            ->label('Автор')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload() // Помогает убрать лаги при поиске
                            ->required(),

                        Select::make('animal_id')
                            ->label('Тип животного')
                            ->relationship('animal', 'species')
                            ->preload()
                            ->required(),

                        Textarea::make('description')
                            ->label('Описание')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Цена и Состояние')
                    ->schema([
                        Select::make('price_type')
                            ->label('Тип цены')
                            ->options([
                                'fixed' => 'Фиксированная',
                                'free' => 'Бесплатно',
                                'exchange' => 'Обмен',
                            ])
                            ->default('fixed')
                            ->live() // Заменяем reactive() на live() для v3
                            ->required(),

                        TextInput::make('price')
                            ->label('Цена')
                            ->numeric()
                            ->prefix('₽')
                            ->hidden(fn ($get) => $get('price_type') !== 'fixed'),

                        Select::make('condition')
                            ->label('Состояние')
                            ->options([
                                'new' => 'Новое',
                                'used' => 'Б/У',
                            ])
                            ->required(),
                    ])->columns(3),

                Section::make('Контакты и Локация')
                    ->schema([
                        TextInput::make('phone')
                            ->label('Телефон')
                            ->tel()
                            ->required(),
                        
                        TextInput::make('city')
                            ->label('Город')
                            ->required(),

                        TextInput::make('address')
                            ->label('Адрес'),
                    ])->columns(3),

                Section::make('Медиа и Статус')
                    ->schema([
                        FileUpload::make('photos')
                            ->label('Фотографии')
                            ->image()
                            ->multiple()
                            ->directory('ads')
                            ->maxFiles(5)
                            ->reorderable(),

                        Toggle::make('is_active')
                            ->label('Активно')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photos')
                    ->label('Фото')
                    ->circular()
                    ->limit(1),

                TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Автор')
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Цена')
                    ->sortable()
                    ->formatStateUsing(fn ($record) => 
                        $record->price_type === 'fixed' 
                            ? number_format($record->price, 0, '.', ' ') . ' ₽' 
                            : __("{$record->price_type}")
                    ),

                TextColumn::make('city')
                    ->label('Город')
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('Статус')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('animal_id')
                    ->label('Тип животного')
                    ->relationship('animal', 'species'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListAds::route('/'),
            'create' => Pages\CreateAd::route('/create'),
            'edit' => Pages\EditAd::route('/{record}/edit'),
        ];
    }
}