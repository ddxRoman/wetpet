<?php

namespace App\Filament\Resources;

use App\Models\Organization;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Resources\OrganizationResource\Pages;

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;
    protected static ?string $navigationLabel = 'Организации';
    protected static ?string $navigationGroup = 'Контент сайта';
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('is_verified', false)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
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
                            ->url(fn ($record) => $record ? route('organizations.show', ['city' => $record->city_slug, 'slug' => $record->slug]) : null)
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

            Forms\Components\Hidden::make('slug_touched')
                ->default(false)
                ->dehydrated(false)
                ->afterStateHydrated(function (Forms\Components\Hidden $component, $record) {
                    // На редактировании уже существующей организации не трогаем слаг
                    // автоматически при правке названия/адреса — только вручную.
                    $component->state($record !== null);
                }),

            Forms\Components\TextInput::make('name')
                ->label('Название')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $set, $get) {
                    if (! $get('slug_touched')) {
                        $set('slug', Organization::generateUniqueSlug(
                            Organization::buildSlugSource($state, $get('city'), $get('street'), $get('house'))
                        ));
                    }
                }),

            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->helperText('Собирается автоматически из названия и адреса (город, улица, дом). Можно поправить вручную — после этого авто-обновление отключится.')
                ->required()
                ->unique(ignoreRecord: true)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (callable $set) => $set('slug_touched', true))
                ->suffixAction(
                    Forms\Components\Actions\Action::make('regenerate_slug')
                        ->label('Пересобрать')
                        ->icon('heroicon-o-arrow-path')
                        ->action(function (callable $set, callable $get) {
                            $set('slug', Organization::generateUniqueSlug(
                                Organization::buildSlugSource($get('name'), $get('city'), $get('street'), $get('house'))
                            ));
                            $set('slug_touched', false);
                        })
                ),

            Forms\Components\Select::make('field_of_activity_id')
                ->label('Сфера деятельности')
                ->relationship('activityType', 'name')
                ->searchable()
                ->preload(),

            Forms\Components\Section::make('Адрес')
                ->schema([
                    Forms\Components\TextInput::make('country')->label('Страна'),
                    Forms\Components\TextInput::make('region')->label('Регион'),
                    Forms\Components\TextInput::make('city')
                        ->label('Город')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            if (! $get('slug_touched')) {
                                $set('slug', Organization::generateUniqueSlug(
                                    Organization::buildSlugSource($get('name'), $state, $get('street'), $get('house'))
                                ));
                            }
                        }),
                    Forms\Components\TextInput::make('street')
                        ->label('Улица')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            if (! $get('slug_touched')) {
                                $set('slug', Organization::generateUniqueSlug(
                                    Organization::buildSlugSource($get('name'), $get('city'), $state, $get('house'))
                                ));
                            }
                        }),
                    Forms\Components\TextInput::make('house')
                        ->label('Дом')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            if (! $get('slug_touched')) {
                                $set('slug', Organization::generateUniqueSlug(
                                    Organization::buildSlugSource($get('name'), $get('city'), $get('street'), $state)
                                ));
                            }
                        }),
                    Forms\Components\TextInput::make('address_comment')->label('Комментарий к адресу')->columnSpanFull(),
                ])
                ->columns(3),

            Forms\Components\Section::make('Контакты')
                ->schema([
                    Forms\Components\TextInput::make('phone1')->label('Телефон 1'),
                    Forms\Components\TextInput::make('phone2')->label('Телефон 2'),
                    Forms\Components\TextInput::make('email')->label('Email')->email(),
                    Forms\Components\TextInput::make('website')->label('Сайт')->url(),
                    Forms\Components\TextInput::make('telegram')->label('Telegram'),
                    Forms\Components\TextInput::make('whatsapp')->label('VK'),
                    Forms\Components\TextInput::make('max')->label('Max'),
                ])
                ->columns(2),

            Forms\Components\Section::make('График работы')
                ->schema([
                    Forms\Components\TextInput::make('schedule')->label('Часы работы')->placeholder('09:00–20:00'),
                    Forms\Components\TextInput::make('workdays')->label('Рабочие дни')->placeholder('Пн–Вс'),
                ])
                ->columns(2),

            Forms\Components\FileUpload::make('logo')
                ->label('Логотип')
                ->image()
                ->directory('organizations/logos'),

            Forms\Components\Textarea::make('description')
                ->label('Описание')
                ->rows(5)
                ->columnSpanFull(),

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

                Tables\Columns\ImageColumn::make('logo')
                    ->label('Лого')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('activityType.name')
                    ->label('Сфера деятельности')
                    ->searchable(),

                Tables\Columns\TextColumn::make('city')
                    ->label('Город')
                    ->searchable()
                    ->sortable(),

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

                Tables\Filters\SelectFilter::make('city')
                    ->label('Город')
                    ->options(fn () => Organization::query()
                        ->whereNotNull('city')
                        ->distinct()
                        ->orderBy('city')
                        ->pluck('city', 'city')
                        ->toArray())
                    ->searchable(),
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

    public static function getRelations(): array
    {
        return [
            \App\Filament\RelationManagers\PhotosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrganizations::route('/'),
            'create' => Pages\CreateOrganization::route('/create'),
            'edit'   => Pages\EditOrganization::route('/{record}/edit'),
        ];
    }
}