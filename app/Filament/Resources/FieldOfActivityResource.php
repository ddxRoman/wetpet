<?php

namespace App\Filament\Resources;

use App\Models\FieldOfActivity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\FieldOfActivityResource\Pages;
use Illuminate\Support\Str;

class FieldOfActivityResource extends Resource
{
    protected static ?string $model = FieldOfActivity::class;

    protected static ?string $navigationIcon  = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Направления';
    protected static ?string $navigationGroup = 'Контент сайта';
    protected static ?int    $navigationSort  = 0;

    protected static ?string $modelLabel       = 'Направление';
    protected static ?string $pluralModelLabel = 'Направления';

    // Варианты типа карточки, которые видит админ.
    // Под капотом хранятся через существующие колонки type/activity:
    //   Доктор      -> type = specialist, activity = doctor
    //   Специалист  -> type = specialist, activity = <слаг названия>
    //   Организация -> type = organization, activity = <слаг названия>
    public const CARD_TYPES = [
        'doctor'       => 'Доктор',
        'specialist'   => 'Специалист',
        'organization' => 'Организация',
    ];

    // Определяет тип карточки записи по её реальным колонкам type/activity.
    public static function resolveCardType(?string $type, ?string $activity): string
    {
        if ($type === 'organization') {
            return 'organization';
        }

        if ($activity === 'doctor') {
            return 'doctor';
        }

        return 'specialist';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Направление')
                    ->schema([
                        Forms\Components\Radio::make('card_type')
                            ->label('Тип карточки')
                            ->options(self::CARD_TYPES)
                            ->required()
                            ->inline()
                            ->live()
                            ->afterStateHydrated(function (Forms\Components\Radio $component, $record) {
                                if ($record) {
                                    $component->state(
                                        self::resolveCardType($record->type, $record->activity)
                                    );
                                }
                            })
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                // При переключении типа подтягиваем слаг из уже введённого названия.
                                if ($get('card_type') !== 'doctor' && filled($get('name'))) {
                                    $set('activity_slug', Str::slug($get('name'), '_'));
                                }
                            })
                            ->default('doctor'),

                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->helperText('Название специальности (например «Хирург») или направления деятельности организации (например «Зоомагазин»)')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, $state, $record) {
                                // Слаг для колонки activity генерируем автоматически из названия,
                                // но не трогаем зафиксированное значение 'doctor'.
                                if ($get('card_type') !== 'doctor') {
                                    $set('activity_slug', Str::slug($state, '_'));
                                }
                            }),

                        Forms\Components\TextInput::make('activity_slug')
                            ->label('Технический слаг направления')
                            ->helperText('Служебное значение для связки с похожими карточками (заполняется автоматически, при желании можно поправить)')
                            ->maxLength(255)
                            ->visible(fn (Get $get) => $get('card_type') !== 'doctor')
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, $record) {
                                if ($record && $record->activity !== 'doctor') {
                                    $component->state($record->activity);
                                }
                            }),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('card_type')
                    ->label('Тип карточки')
                    ->getStateUsing(fn (FieldOfActivity $record) => self::CARD_TYPES[self::resolveCardType($record->type, $record->activity)])
                    ->badge()
                    ->color(fn (FieldOfActivity $record) => match (self::resolveCardType($record->type, $record->activity)) {
                        'doctor'       => 'info',
                        'specialist'   => 'success',
                        'organization' => 'warning',
                    }),

                Tables\Columns\TextColumn::make('activity')
                    ->label('Направление')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('doctors_pivot_count')
                    ->label('Врачей')
                    ->counts('doctorsPivot')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('organizations_count')
                    ->label('Организаций')
                    ->counts('organizations')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('card_type')
                    ->label('Тип карточки')
                    ->options(self::CARD_TYPES)
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;

                        if (! $value) {
                            return $query;
                        }

                        return match ($value) {
                            'doctor'       => $query->where('type', 'specialist')->where('activity', 'doctor'),
                            'specialist'   => $query->where('type', 'specialist')->where('activity', '!=', 'doctor'),
                            'organization' => $query->where('type', 'organization'),
                        };
                    }),

                Tables\Filters\SelectFilter::make('activity')
                    ->label('Направление')
                    ->options(fn () => FieldOfActivity::query()
                        ->whereNotNull('activity')
                        ->distinct()
                        ->orderBy('activity')
                        ->pluck('activity', 'activity')
                        ->toArray()),

                Tables\Filters\SelectFilter::make('name')
                    ->label('Специальность')
                    ->options(fn () => FieldOfActivity::query()
                        ->distinct()
                        ->orderBy('name')
                        ->pluck('name', 'name')
                        ->toArray())
                    ->searchable(),
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

    // Переводим виртуальные поля формы (card_type / activity_slug) в реальные колонки type/activity.
    public static function prepareDataForSave(array $data): array
    {
        $cardType = $data['card_type'] ?? 'doctor';

        if ($cardType === 'doctor') {
            $data['type']     = 'specialist';
            $data['activity'] = 'doctor';
        } else {
            $data['type']     = $cardType === 'organization' ? 'organization' : 'specialist';
            $data['activity'] = ! empty($data['activity_slug'])
                ? $data['activity_slug']
                : Str::slug($data['name'] ?? '', '_');
        }

        unset($data['card_type'], $data['activity_slug']);

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFieldOfActivities::route('/'),
            'create' => Pages\CreateFieldOfActivity::route('/create'),
            'edit'   => Pages\EditFieldOfActivity::route('/{record}/edit'),
        ];
    }
}
