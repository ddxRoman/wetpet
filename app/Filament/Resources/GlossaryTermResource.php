<?php

namespace App\Filament\Resources;

use App\Models\GlossaryTerm;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\GlossaryTermResource\Pages;
use Filament\Forms\Components\{TextInput, Textarea, Toggle, Section, Select};
use Filament\Tables\Columns\{TextColumn, IconColumn};
use Filament\Tables;

class GlossaryTermResource extends Resource
{
    protected static ?string $model = GlossaryTerm::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Глоссарий';
    protected static ?string $navigationGroup = 'Контент';
    protected static ?string $modelLabel = 'Термин';
    protected static ?string $pluralModelLabel = 'Термины глоссария';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Термин и определение')
                    ->schema([
                        TextInput::make('term')
                            ->label('Термин')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Буква для алфавитного указателя подставится автоматически'),

                        Select::make('category')
                            ->label('Категория')
                            ->options(GlossaryTerm::categories())
                            ->nullable()
                            ->placeholder('Без категории'),

                        Textarea::make('definition')
                            ->label('Определение')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Настройки')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Опубликован')
                            ->default(true),

                        TextInput::make('slug')
                            ->label('Адрес страницы (slug)')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->rules(['nullable', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'])
                            ->validationMessages(['regex' => 'Только латинские буквы в нижнем регистре, цифры и дефисы.'])
                            ->helperText('Страница термина: /glossary/<slug>. Если оставить пустым — сформируется из названия термина.'),
                    ])->columns(2),

                Section::make('SEO страницы термина')
                    ->description('Необязательно. Если поле пустое, подставится общий шаблон из раздела «SEO → SEO Каталожных страниц» (запись «Термины глоссария»).')
                    ->schema([
                        TextInput::make('seo_title')
                            ->label('SEO Title')
                            ->maxLength(255)
                            ->helperText('Заголовок страницы в поиске. Оптимально до 60–70 символов.')
                            ->columnSpanFull(),

                        Textarea::make('seo_description')
                            ->label('SEO Description')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Описание в поиске. Оптимально до 150–160 символов.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('letter')
                    ->label('Буква')
                    ->sortable()
                    ->width(70)
                    ->badge()
                    ->color('primary'),

                TextColumn::make('term')
                    ->label('Термин')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('category')
                    ->label('Категория')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => GlossaryTerm::categories()[$state] ?? '—')
                    ->color(fn (?string $state) => match ($state) {
                        'general'    => 'gray',
                        'anatomy'    => 'info',
                        'diseases'   => 'danger',
                        'procedures' => 'success',
                        'legal'      => 'warning',
                        'platform'   => 'primary',
                        default      => 'gray',
                    }),


                IconColumn::make('is_active')
                    ->label('Опубликован')
                    ->boolean(),

                TextColumn::make('slug')
                    ->label('Адрес')
                    ->prefix('/glossary/')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('has_manual_seo')
                    ->label('Своё SEO')
                    ->boolean()
                    ->state(fn (GlossaryTerm $record) => filled($record->seo_title) || filled($record->seo_description))
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Обновлён')
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->defaultSort('letter')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Категория')
                    ->options(GlossaryTerm::categories()),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Статус')
                    ->trueLabel('Только опубликованные')
                    ->falseLabel('Только скрытые'),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('На сайте')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (GlossaryTerm $record) => route('glossary.show', $record->slug))
                    ->openUrlInNewTab()
                    ->visible(fn (GlossaryTerm $record) => filled($record->slug)),
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
            'index'  => Pages\ListGlossaryTerms::route('/'),
            'create' => Pages\CreateGlossaryTerm::route('/create'),
            'edit'   => Pages\EditGlossaryTerm::route('/{record}/edit'),
        ];
    }
}
