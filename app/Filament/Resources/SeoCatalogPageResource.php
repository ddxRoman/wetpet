<?php

namespace App\Filament\Resources;

use App\Models\SeoCatalogPage;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\SeoCatalogPageResource\Pages;
use Filament\Forms\Components\{TextInput, Textarea, Section, Placeholder};
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;
use Illuminate\Support\HtmlString;

class SeoCatalogPageResource extends Resource
{
    protected static ?string $model = SeoCatalogPage::class;
    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';
    protected static ?string $navigationLabel = 'SEO Каталожных страниц';
    protected static ?string $navigationGroup = 'SEO';
    protected static ?string $modelLabel = 'SEO каталожной страницы';
    protected static ?string $pluralModelLabel = 'SEO Каталожных страниц';
    protected static ?int $navigationSort = 1;

    /**
     * Набор записей фиксирован (используется в контроллерах по ключу),
     * поэтому создание и удаление через админку отключены — только редактирование.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('label')
                            ->label('Страница')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),

                        Placeholder::make('variables_hint')
                            ->label('Доступные переменные')
                            ->content(function (?SeoCatalogPage $record) {
                                if (!$record) {
                                    return '—';
                                }

                                $vars = $record->availableVariables();

                                if (empty($vars)) {
                                    return 'Для этой страницы переменные не предусмотрены.';
                                }

                                $lines = array_map(
                                    fn (string $key) => '<code>{' . $key . '}</code> — ' . (SeoCatalogPage::VARIABLE_LABELS[$key] ?? $key),
                                    $vars
                                );

                                return new HtmlString(implode('<br>', $lines));
                            })
                            ->columnSpanFull(),

                        TextInput::make('title')
                            ->label('Title (заголовок страницы)')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Вставляйте переменные прямо в текст, например: Ветеринарные врачи в {city}')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Meta description')
                            ->required()
                            ->rows(4)
                            ->maxLength(500)
                            ->helperText('Если переменная не определена (например, город не выбран), вместо неё подставится значение по умолчанию.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Страница')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('key')
                    ->label('Ключ')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('title')
                    ->label('Title')
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->title),

                TextColumn::make('updated_at')
                    ->label('Обновлён')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id')
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeoCatalogPages::route('/'),
            'edit'  => Pages\EditSeoCatalogPage::route('/{record}/edit'),
        ];
    }
}
