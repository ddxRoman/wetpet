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

                    Forms\Components\Placeholder::make('creator_info')
                        ->label('Кто добавил')
                        ->content(fn ($record) => $record?->creator?->name
                            ? $record->creator->name . ' (' . $record->creator->email . ')'
                            : 'Добавлено администратором / системой'),
                ])
                ->columns(2),

            Forms\Components\TextInput::make('name')
                ->label('Имя')
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, $get) {
                    if (! $get('slug')) {
                        $set('slug', \Illuminate\Support\Str::slug($state));
                    }
                }),

            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\TextInput::make('specialization')
                ->label('Специализация')
                ->required(),

            Forms\Components\Select::make('city_id')
                ->label('Город')
                ->relationship('city', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('organization_id')
                ->label('Организация')
                ->relationship('organization', 'name')
                ->searchable()
                ->preload(),

            Forms\Components\TextInput::make('street')->label('Улица'),
            Forms\Components\TextInput::make('house')->label('Дом'),

            Forms\Components\TextInput::make('experience')
                ->label('Опыт (лет)')
                ->numeric()
                ->minValue(0)
                ->maxValue(70),

            Forms\Components\DatePicker::make('date_of_birth')
                ->label('Дата рождения'),

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

                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Организация')
                    ->searchable(),

                Tables\Columns\TextColumn::make('experience')
                    ->label('Опыт (лет)'),

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
