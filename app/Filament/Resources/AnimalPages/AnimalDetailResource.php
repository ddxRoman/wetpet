<?php

namespace App\Filament\Resources\AnimalPages;

use App\Models\AnimalDetail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\AnimalPages\ListAnimalDetails;
use App\Filament\Resources\AnimalPages\CreateAnimalDetail;
use App\Filament\Resources\AnimalPages\EditAnimalDetail;

class AnimalDetailResource extends Resource
{
    protected static ?string $model = AnimalDetail::class;
    protected static ?string $slug = 'animal-details';
    protected static ?string $navigationGroup = 'Животные';
    protected static ?string $navigationLabel = 'Породы';
    protected static ?string $pluralModelLabel = 'Породы';
    protected static ?string $modelLabel = 'Породу';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Связь с породой')
                    ->schema([
                        // ИСПРАВЛЕНО: имя поля должно быть animal_breed, как в БД
                        Forms\Components\Select::make('animal_breed')
                            ->relationship('animal', 'breed')
                            ->required()
                            ->searchable()
                            ->label('Порода'),
                            
                        // ИСПРАВЛЕНО: убран storeFileNamesIn, так как он тут не нужен
                        Forms\Components\FileUpload::make('photo')
                            ->image()
                            ->directory('animals-details')
                            ->label('Фото'),
                    ])->columns(2),

                Forms\Components\Section::make('Характеристики')
                    ->schema([
                        Forms\Components\TextInput::make('weight_range')->label('Вес'),
                        Forms\Components\TextInput::make('height_range')->label('Рост'),
                        Forms\Components\TextInput::make('lifespan')->label('Срок жизни'),
                        // Добавлено поле type, так как оно есть в БД (varchar 255)
                        Forms\Components\TextInput::make('type')->label('Тип (категория)'),
                    ])->columns(2),

                Forms\Components\Section::make('Описание')
                    ->schema([
                        Forms\Components\Textarea::make('short_description')
                            ->label('Краткое описание')
                            ->rows(3),
                        Forms\Components\RichEditor::make('full_description')
                            ->label('Полное описание')
                            ->columnSpanFull(),
                    ]),

                    Forms\Components\Section::make('SEO Настройки')
    ->schema([
        Forms\Components\TextInput::make('seo_title')
            ->label('Заголовок страницы (Title)')
            ->placeholder('Если пусто, возьмем название породы'),
        Forms\Components\Textarea::make('seo_description')
            ->label('Описание (Description)')
            ->rows(3),
    ])->collapsed(),

                Forms\Components\Section::make('Дополнительные параметры')
                    ->schema([
                        // Важно: убедись, что в модели AnimalDetail стоит $casts['features'] = 'array'
                        Forms\Components\KeyValue::make('features')
                            ->label('Особенности (JSON)')
                            ->keyLabel('Параметр')
                            ->valueLabel('Значение')
                            ->addActionLabel('Добавить строку'),
                    ]),
            ]);
    }

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('animal.breed')
                ->label('Порода')
                ->searchable()
                ->sortable(),

            Tables\Columns\ImageColumn::make('photo')
                ->label('Фото'),

            Tables\Columns\TextColumn::make('weight_range')
                ->label('Вес'),

            Tables\Columns\TextColumn::make('lifespan')
                ->label('Жизнь'),
                
            Tables\Columns\TextColumn::make('type')
                ->label('Тип')
                ->badge()
                ->color('gray'),
        ])
        ->filters([
            // ИСПОЛЬЗУЕМ ОБЫЧНЫЙ SelectFilter БЕЗ relationship()
            // Это исключит конфликты со связями и бесконечную загрузку
            Tables\Filters\SelectFilter::make('species')
                ->label('Вид животного')
                ->options(function () {
                    return \App\Models\Animal::query()
                        ->distinct()
                        ->whereNotNull('species')
                        ->pluck('species', 'species')
                        ->toArray();
                })
                ->query(function ($query, array $data) {
                    if (!empty($data['value'])) {
                        // Фильтруем основную таблицу через связь
                        $query->whereHas('animal', function ($q) use ($data) {
                            $q->where('species', $data['value']);
                        });
                    }
                })
                ->searchable()
                ->preload(),
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
            'index' => ListAnimalDetails::route('/'),
            'create' => CreateAnimalDetail::route('/create'),
            'edit' => EditAnimalDetail::route('/{record}/edit'),
        ];
    }
}