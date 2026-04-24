<?php

namespace App\Filament\Resources\AnimalPages;

use App\Models\AnimalDetail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
// Импортируем классы страниц из этого же пространства имен
use App\Filament\Resources\AnimalPages\ListAnimalDetails;
use App\Filament\Resources\AnimalPages\CreateAnimalDetail;
use App\Filament\Resources\AnimalPages\EditAnimalDetail;

class AnimalDetailResource extends Resource
{
protected static ?string $model = AnimalDetail::class;

    protected static ?string $slug = 'animal-details';

    // 1. Название самой группы (родительский пункт меню)
    protected static ?string $navigationGroup = 'Животные';

    // 2. Название конкретного пункта в меню
    protected static ?string $navigationLabel = 'Породы';

    // 3. Заголовок внутри самой страницы (над таблицей/формой)
    protected static ?string $pluralModelLabel = 'Породы';
    
    // 4. Заголовок для одной записи (н-р: "Создать Породу")
    protected static ?string $modelLabel = 'Породу';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Связь с породой')
                    ->schema([
                        Forms\Components\Select::make('animal_id')
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
                    ])->columns(3),

                Forms\Components\Section::make('Описание')
                    ->schema([
                        Forms\Components\Textarea::make('short_description')
                            ->label('Краткое описание')
                            ->rows(3),
                        Forms\Components\RichEditor::make('full_description')
                            ->label('Полное описание')
                            ->columnSpanFull(),
                    ]),

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
                Tables\Columns\TextColumn::make('animal.breed')->label('Порода')->searchable(),
                Tables\Columns\ImageColumn::make('photo')->label('Фото'),
                Tables\Columns\TextColumn::make('weight_range')->label('Вес'),
                Tables\Columns\TextColumn::make('lifespan')->label('Жизнь'),
            ])
            ->filters([])
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