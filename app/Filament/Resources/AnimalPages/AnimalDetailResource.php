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
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

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
                        Forms\Components\Select::make('animal_breed')
                            ->relationship('animal', 'breed')
                            ->required()
                            ->searchable()
                            ->label('Порода'),

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
                        TextInput::make('seo_title')
                            ->label('Заголовок страницы (Title)')
                            ->extraInputAttributes(['id' => 'seo_title_input'])
                            ->maxLength(60)
                            ->live(onBlur: false)
                            ->helperText(new \Illuminate\Support\HtmlString('
                                Доступные теги: 
                                <button type="button" onclick="insertTag(\'seo_title_input\', \'{name}\')" style="color: #fbbf24; font-weight: bold; cursor: pointer;">{name}</button>, 
                                <button type="button" onclick="insertTag(\'seo_title_input\', \'{type}\')" style="color: #fbbf24; font-weight: bold; cursor: pointer;">{type}</button>
                            ')),

                        Textarea::make('seo_description')
                            ->label('Описание (Description)')
                            ->rows(3)
                            ->extraInputAttributes(['id' => 'seo_desc_input'])
                            ->maxLength(160)
                            ->live(onBlur: false)
                            ->helperText(new \Illuminate\Support\HtmlString('
                                Доступные теги: 
                                <button type="button" onclick="insertTag(\'seo_desc_input\', \'{name}\')" style="color: #fbbf24; font-weight: bold; cursor: pointer;">{name}</button>, 
                                <button type="button" onclick="insertTag(\'seo_desc_input\', \'{type}\')" style="color: #fbbf24; font-weight: bold; cursor: pointer;">{type}</button>
                                
                                <script>
                                    function insertTag(inputId, tag) {
                                        const input = document.getElementById(inputId);
                                        if (input) {
                                            const start = input.selectionStart;
                                            const end = input.selectionEnd;
                                            const text = input.value;
                                            input.value = text.substring(0, start) + tag + text.substring(end);
                                            input.focus();
                                            input.setSelectionRange(start + tag.length, start + tag.length);
                                            input.dispatchEvent(new Event(\'input\', { bubbles: true }));
                                        }
                                    }
                                </script>
                            ')),
                    ])
                    ->collapsed(),

                Forms\Components\Section::make('Дополнительные параметры')
                    ->schema([
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
                            $query->whereHas('animal', function ($q) use ($data) {
                                $q->where('species', $data['value']);
                            });
                        }
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                // 1. Кнопка "На сайт" в виде иконки
                Tables\Actions\Action::make('open_web')
                    ->label('На сайт')
                    ->icon('heroicon-o-globe-alt')
                    ->iconButton()
                    ->color('success')
                    ->url(function (AnimalDetail $record) {
                        $animal = $record->animal;
                        return $animal ? url("/animals/{$animal->species_slug}/{$animal->breed_slug}") : null;
                    })
                    ->openUrlInNewTab(),

                // 2. Кнопка "Изменить" в виде иконки
                Tables\Actions\EditAction::make()
                    ->label('Изменить')
                    ->iconButton()
                    ->color('warning'),
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