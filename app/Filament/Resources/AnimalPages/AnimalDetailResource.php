<?php

namespace App\Filament\Resources\AnimalPages;

use App\Models\AnimalDetail;
use App\Models\Animal;
use App\Models\Pet;
use App\Models\Ad;
use App\Models\AnimalReview;
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
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

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
                Forms\Components\Section::make('Вид и порода')
                    ->schema([
                        Forms\Components\Select::make('species')
                            ->label('Тип животного')
                            ->options(fn () => \App\Models\Animal::query()
                                ->whereNotNull('species')
                                ->distinct()
                                ->orderBy('species')
                                ->pluck('species', 'species')
                                ->toArray())
                            ->required()
                            ->searchable()
                            ->native(false),

                        Forms\Components\TextInput::make('breed_name')
                            ->label('Название породы')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Введите новое название породы — такой породы в базе ещё не должно быть')
                            ->unique(
                                table: 'animals',
                                column: 'breed',
                                ignorable: fn (?AnimalDetail $record) => $record?->animal,
                            )
                            ->validationMessages([
                                'unique' => 'Такая порода уже существует в базе.',
                            ]),

                        Forms\Components\FileUpload::make('photo')
                            ->image()
                            ->directory('animals-details')
                            ->label('Фото')
                            ->columnSpanFull(),
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

                // 3. Удаление ПОРОДЫ целиком (не только карточки-описания).
                // Если на породу уже ссылаются объявления/питомцы/отзывы —
                // сначала просим выбрать породу-замену и переносим записи на неё.
                Tables\Actions\Action::make('delete_breed')
                    ->label('Удалить')
                    ->icon('heroicon-o-trash')
                    ->iconButton()
                    ->color('danger')
                    ->modalHeading(fn (AnimalDetail $record) => self::hasDependents($record->animal)
                        ? 'У породы есть связанные записи'
                        : 'Удалить породу?')
                    ->modalDescription(fn (AnimalDetail $record) => self::hasDependents($record->animal)
                        ? self::dependentsSummary($record->animal).' Выберите породу, на которую перенести эти записи перед удалением.'
                        : 'Порода и её карточка будут удалены безвозвратно. Это действие нельзя отменить.')
                    ->modalSubmitActionLabel('Удалить')
                    ->form(fn (AnimalDetail $record) => self::hasDependents($record->animal)
                        ? [
                            Forms\Components\Select::make('replacement_animal_id')
                                ->label('Перенести записи на породу')
                                ->helperText('Питомцы, объявления и отзывы этой породы будут привязаны к выбранной.')
                                ->options(fn () => Animal::query()
                                    ->when($record->animal, fn ($q) => $q->where('id', '!=', $record->animal->id))
                                    ->orderBy('species')->orderBy('breed')
                                    ->get()
                                    ->mapWithKeys(fn ($a) => [$a->id => "{$a->species} — {$a->breed}"]))
                                ->searchable()
                                ->required(),
                        ]
                        : [])
                    ->action(function (AnimalDetail $record, array $data) {
                        self::deleteOrMergeAnimal($record->animal, $data['replacement_animal_id'] ?? null);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('delete_breeds')
                        ->label('Удалить породы')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->modalHeading('Удаление пород')
                        ->modalDescription(function (\Illuminate\Support\Collection $records) {
                            $animals = $records->map->animal->filter();
                            $hasAny = $animals->contains(fn ($a) => self::hasDependents($a));

                            return $hasAny
                                ? 'У части выбранных пород есть связанные питомцы/объявления/отзывы. Выберите породу, на которую перенести все эти записи перед удалением.'
                                : 'Выбранные породы и их карточки будут удалены безвозвратно.';
                        })
                        ->form(function (\Illuminate\Support\Collection $records) {
                            $animalIds = $records->map->animal->filter()->pluck('id');
                            $hasAny = $records->map->animal->filter()->contains(fn ($a) => self::hasDependents($a));

                            return $hasAny
                                ? [
                                    Forms\Components\Select::make('replacement_animal_id')
                                        ->label('Перенести записи на породу')
                                        ->helperText('Нельзя выбрать одну из удаляемых пород.')
                                        ->options(fn () => Animal::query()
                                            ->whereNotIn('id', $animalIds)
                                            ->orderBy('species')->orderBy('breed')
                                            ->get()
                                            ->mapWithKeys(fn ($a) => [$a->id => "{$a->species} — {$a->breed}"]))
                                        ->searchable()
                                        ->required(),
                                ]
                                : [];
                        })
                        ->action(function (\Illuminate\Support\Collection $records, array $data) {
                            foreach ($records as $record) {
                                self::deleteOrMergeAnimal($record->animal, $data['replacement_animal_id'] ?? null);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    /**
     * Есть ли у породы связанные питомцы/объявления/отзывы.
     */
    protected static function hasDependents(?Animal $animal): bool
    {
        if (!$animal) {
            return false;
        }

        return Pet::where('animal_id', $animal->id)->exists()
            || Ad::where('animal_id', $animal->id)->exists()
            || AnimalReview::where('animal_id', $animal->id)->exists();
    }

    /**
     * Человеко-читаемая сводка "у породы N питомцев, M объявлений, K отзывов".
     */
    protected static function dependentsSummary(?Animal $animal): string
    {
        if (!$animal) {
            return '';
        }

        $parts = [];

        if ($count = Pet::where('animal_id', $animal->id)->count()) {
            $parts[] = "{$count} питомцев";
        }
        if ($count = Ad::where('animal_id', $animal->id)->count()) {
            $parts[] = "{$count} объявлений";
        }
        if ($count = AnimalReview::where('animal_id', $animal->id)->count()) {
            $parts[] = "{$count} отзывов";
        }

        return 'К этой породе привязаны: '.implode(', ', $parts).'.';
    }

    /**
     * Удаляет породу целиком. Если есть зависимые записи — сначала
     * переносит их на породу-замену (обязательный $replacementId),
     * иначе просто удаляет (карточка animal_details уйдёт каскадом).
     */
    protected static function deleteOrMergeAnimal(?Animal $animal, ?int $replacementId): void
    {
        if (!$animal) {
            Notification::make()
                ->title('У этой карточки нет привязанной породы — нечего удалять')
                ->danger()
                ->send();
            return;
        }

        if (self::hasDependents($animal)) {
            if (!$replacementId) {
                Notification::make()
                    ->title('Нужно выбрать породу для переноса записей')
                    ->danger()
                    ->send();
                return;
            }

            $replacement = Animal::find($replacementId);

            if (!$replacement) {
                Notification::make()
                    ->title('Порода для переноса не найдена')
                    ->danger()
                    ->send();
                return;
            }

            DB::transaction(function () use ($animal, $replacement) {
                Pet::where('animal_id', $animal->id)->update(['animal_id' => $replacement->id]);
                Ad::where('animal_id', $animal->id)->update(['animal_id' => $replacement->id]);
                AnimalReview::where('animal_id', $animal->id)->update(['animal_id' => $replacement->id]);

                // Каскадом удалит и animal_details — зависимых записей на этот
                // момент уже не осталось (все перенесены на $replacement).
                $animal->delete();
            });

            Notification::make()
                ->title("Порода «{$animal->breed}» удалена, записи перенесены на «{$replacement->breed}»")
                ->success()
                ->send();
        } else {
            $breedName = $animal->breed;
            $animal->delete();

            Notification::make()
                ->title("Порода «{$breedName}» удалена")
                ->success()
                ->send();
        }
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