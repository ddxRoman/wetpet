<?php

namespace App\Filament\Resources;

use App\Models\Specialist;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Resources\SpecialistResource\Pages;

class SpecialistResource extends Resource
{
    protected static ?string $model = Specialist::class;
    protected static ?string $navigationLabel = 'Специалисты';
    protected static ?string $navigationGroup = 'Контент сайта';
    protected static ?string $navigationIcon = 'heroicon-o-identification';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('is_verified', false)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    /**
     * Автозаполнение slug при создании: ФИО + организация,
     * а если организации нет — ФИО + адрес частной практики.
     */
    protected static function fillSlug(callable $set, callable $get, string $operation): void
    {
        if ($operation !== 'create') {
            return; // slug существующих записей не меняем — сломаются ссылки
        }

        $set('slug', Specialist::generateSlug(
            $get('name'),
            $get('organization_id'),
            $get('street'),
            $get('house'),
        ));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Модерация')
                ->schema([
                    Forms\Components\Toggle::make('is_verified')
                        ->label('Проверено администратором')
                        ->helperText('Запись видна на сайте только после проверки')
                        ->onColor('success')
                        ->offColor('warning'),

                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('open_card')
                            ->label('Открыть карточку на сайте')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->color('gray')
                            ->url(fn ($record) => $record ? route('specialists.show', $record) : null)
                            ->openUrlInNewTab()
                            ->visible(fn ($record) => $record !== null),
                    ]),

                    Forms\Components\Placeholder::make('creator_info')
                        ->label('Кто добавил')
                        ->content(fn ($record) => $record?->creator?->name
                            ? $record->creator->name . ' (' . $record->creator->email . ')'
                            : 'Добавлено администратором / системой'),
                ])
                ->columns(3),

            Forms\Components\TextInput::make('name')
                ->label('ФИО')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set, callable $get, string $operation) => static::fillSlug($set, $get, $operation)),

            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->helperText('Формируется автоматически из ФИО и организации (или адреса частной практики). Можно изменить вручную.')
                ->required()
                ->unique(ignoreRecord: true),

            // ───── СПЕЦИАЛИЗАЦИИ (несколько) ─────
            Forms\Components\Select::make('specializations')
                ->label('Специализации')
                ->multiple()
                ->relationship(
                    name: 'specializations',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn ($query) => $query
                        ->where('type', 'specialist')
                        ->where('activity', '!=', 'doctor'),
                )
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('organization_id')
                ->label('Организация')
                ->relationship('organization', 'name')
                ->searchable()
                ->preload()
                ->live()
                ->helperText('Если специалист работает в организации — регион и город подтянутся из её адреса.')
                ->afterStateUpdated(function ($state, callable $set, callable $get, string $operation) {
                    if ($state) {
                        $organization = \App\Models\Organization::find($state);

                        if ($organization) {
                            $cityName = trim((string) $organization->city);

                            // Сначала ищем по названию + региону, затем только по названию
                            // (регион у организации вводится вручную и может не совпадать с cities.region).
                            $matchedCity = \App\Models\City::query()
                                ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($cityName)])
                                ->when($organization->region, fn ($q, $region) => $q->orderByRaw('region = ? DESC', [$region]))
                                ->first();

                            // Регион берём у найденного города, чтобы он был в списке и город отображался.
                            $set('region', $matchedCity?->region ?? $organization->region);
                            $set('city_id', $matchedCity?->id);
                        }
                    }

                    static::fillSlug($set, $get, $operation);
                }),

            Forms\Components\Section::make('Частная практика')
                ->description('Город подставляется из организации, если она выбрана. Улицу и дом укажите, если специалист ведёт частную практику — в том числе параллельно с работой в организации.')
                ->schema([
                    Forms\Components\Select::make('region')
                        ->label('Регион')
                        ->options(fn () => \App\Models\City::query()
                            ->whereNotNull('region')
                            ->where('region', '!=', '')
                            ->distinct()
                            ->orderBy('region')
                            ->pluck('region', 'region'))
                        ->searchable()
                        ->live()
                        ->dehydrated(false)
                        ->afterStateHydrated(function (Forms\Components\Select $component, $record) {
                            if ($record?->city) {
                                $component->state($record->city->region);
                            }
                        })
                        ->afterStateUpdated(fn (callable $set) => $set('city_id', null)),

                    Forms\Components\Select::make('city_id')
                        ->label('Город')
                        ->options(fn (callable $get) => $get('region')
                            ? \App\Models\City::query()
                                ->where('region', $get('region'))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                            : [])
                        ->searchable()
                        ->live()
                        ->placeholder(fn (callable $get) => $get('region') ? 'Выберите город' : 'Сначала выберите регион')
                        ->required(),

                    Forms\Components\TextInput::make('street')
                        ->label('Улица')
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set, callable $get, string $operation) => static::fillSlug($set, $get, $operation)),

                    Forms\Components\TextInput::make('house')
                        ->label('Дом')
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set, callable $get, string $operation) => static::fillSlug($set, $get, $operation)),
                ])
                ->columns(2)
                ->collapsible(),

            Forms\Components\DatePicker::make('date_of_birth')
                ->label('Дата рождения')
                ->maxDate(now()->subYears(16))
                ->live(),

            Forms\Components\TextInput::make('practice_started_at')
                ->label('Начало практики (год и месяц)')
                ->type('month')
                ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('Y-m') : null)
                ->extraInputAttributes(fn (callable $get) => [
                    'min' => \App\Models\Specialist::earliestPracticeStart($get('date_of_birth')),
                    'max' => now()->format('Y-m'),
                ])
                ->rules(fn (callable $get) => \App\Models\Specialist::practiceStartRules($get('date_of_birth')))
                ->validationMessages(['after_or_equal' => 'Начало практики не может быть раньше чем через 16 лет после даты рождения.'])
                ->helperText('Укажите год и месяц начала практики, и по этим данным будет рассчитан стаж'),

            Forms\Components\Select::make('exotic_animals')
                ->label('Экзотические животные')
                ->options(['yes' => 'Да', 'no' => 'Нет']),

            Forms\Components\Select::make('On_site_assistance')
                ->label('Выезд на дом')
                ->options(['yes' => 'Да', 'no' => 'Нет']),

            Forms\Components\FileUpload::make('photo')
                ->label('Фото')
                ->image()
                ->directory('specialists/photos'),

            Forms\Components\Textarea::make('description')
                ->label('Описание')
                ->rows(5)
                ->columnSpanFull(),

            Forms\Components\Section::make('Контакты')
                ->relationship('contacts')
                ->schema([
                    Forms\Components\TextInput::make('phone')->label('Телефон'),
                    Forms\Components\TextInput::make('email')->label('Email')->email(),
                    Forms\Components\TextInput::make('telegram_text')->label('Telegram'),
                    Forms\Components\TextInput::make('whatsapp_text')->label('VK'),
                    Forms\Components\TextInput::make('max_text')->label('Max'),
                ])
                ->columns(2)
                ->collapsible(),

            Forms\Components\Section::make('SEO')
                ->schema([
                    Forms\Components\TextInput::make('seo_title')->label('SEO заголовок')->maxLength(255),
                    Forms\Components\Textarea::make('seo_description')->label('SEO описание')->maxLength(320)->rows(3),
                ])
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_verified')
                    ->label('Проверено')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('photo')
                    ->label('Фото')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('specialization')
                    ->label('Специализация')
                    ->searchable(),

                Tables\Columns\TextColumn::make('city.name')
                    ->label('Город')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Организация')
                    ->searchable(),

                Tables\Columns\TextColumn::make('experience_label')
                    ->label('Стаж')
                    ->placeholder('Данные не указаны'),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Кто добавил')
                    ->placeholder('Администратор/система')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата добавления')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('Статус проверки')
                    ->trueLabel('Только проверенные')
                    ->falseLabel('Только непроверенные')
                    ->native(false),

                Tables\Filters\SelectFilter::make('organization_id')
                    ->label('Организация')
                    ->relationship('organization', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('city_id')
                    ->label('Город')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('is_verified', 'asc')
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSpecialists::route('/'),
            'create' => Pages\CreateSpecialist::route('/create'),
            'edit'   => Pages\EditSpecialist::route('/{record}/edit'),
        ];
    }
}