<?php

namespace App\Filament\Resources;

use App\Models\News;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Str;
use App\Filament\Resources\NewsResource\Pages;
use Filament\Forms\Components\{
    TextInput, Textarea, RichEditor, FileUpload,
    Toggle, Section, Tabs, Placeholder
};
use Filament\Tables\Columns\{TextColumn, IconColumn, ImageColumn};
use Filament\Tables;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationLabel = 'Новости';
    protected static ?string $navigationGroup = 'Контент';
    protected static ?string $modelLabel = 'Новость';
    protected static ?string $pluralModelLabel = 'Новости';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Новость')
                    ->tabs([

                        // ── Вкладка 1: Основное ──────────────────────────────
                        Tabs\Tab::make('Контент')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Заголовок')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, callable $set) =>
                                                $set('slug', Str::slug($state))
                                            )
                                            ->columnSpanFull(),

                                        TextInput::make('slug')
                                            ->label('URL (slug)')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->helperText('Заполняется автоматически из заголовка')
                                            ->columnSpanFull(),

                                        Textarea::make('excerpt')
                                            ->label('Краткое описание (анонс)')
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->helperText('Показывается в списке новостей и используется как SEO-описание если SEO-поле пустое')
                                            ->columnSpanFull(),

                                        RichEditor::make('content')
                                            ->label('Текст новости')
                                            ->required()
                                            ->toolbarButtons([
                                                'bold', 'italic', 'underline', 'strike',
                                                'h2', 'h3',
                                                'bulletList', 'orderedList',
                                                'link', 'blockquote',
                                                'undo', 'redo',
                                            ])
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // ── Вкладка 2: Медиа ─────────────────────────────────
                        Tabs\Tab::make('Медиа')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('Обложка')
                                    ->schema([
                                        FileUpload::make('image')
                                            ->label('Главное фото')
                                            ->image()
                                            ->disk('public')
                                            ->directory('news/covers')
                                            ->imageEditor()
                                            ->helperText('Рекомендуемый размер: 1200×630px')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Галерея')
                                    ->schema([
                                        FileUpload::make('images')
                                            ->label('Дополнительные фотографии')
                                            ->image()
                                            ->multiple()
                                            ->disk('public')
                                            ->directory('news/galleries')
                                            ->reorderable()
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // ── Вкладка 3: SEO ───────────────────────────────────
                        Tabs\Tab::make('SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Section::make('Мета-теги')
                                    ->description('Если поля пустые — данные берутся автоматически из заголовка и анонса')
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->label('SEO Title')
                                            ->maxLength(255)
                                            ->placeholder('Автоматически: "Заголовок — Зверозор"')
                                            ->helperText('Рекомендуется 50–70 символов')
                                            ->live()
                                            ->suffix(fn ($state) => strlen($state ?? '') . ' / 70')
                                            ->columnSpanFull(),

                                        Textarea::make('seo_description')
                                            ->label('SEO Description')
                                            ->rows(3)
                                            ->maxLength(320)
                                            ->placeholder('Автоматически: из анонса или начало текста')
                                            ->helperText('Рекомендуется 120–160 символов')
                                            ->live()
                                            ->suffix(fn ($state) => strlen($state ?? '') . ' / 160')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Open Graph (соцсети)')
                                    ->description('Как будет выглядеть ссылка при публикации в VK, Telegram, Facebook')
                                    ->schema([
                                        FileUpload::make('og_image')
                                            ->label('OG-картинка')
                                            ->image()
                                            ->disk('public')
                                            ->directory('news/og')
                                            ->helperText('Рекомендуемый размер: 1200×630px. Если пустое — берётся обложка новости')
                                            ->columnSpanFull(),

                                        // Превью как будет выглядеть в поиске
                                        Placeholder::make('seo_preview')
                                            ->label('Превью в поиске Google')
                                            ->content(fn ($record) => $record
                                                ? view('filament.seo-preview', ['record' => $record])
                                                : 'Заполните заголовок и описание чтобы увидеть превью'
                                            )
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // ── Вкладка 4: Настройки ─────────────────────────────
                        Tabs\Tab::make('Настройки')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Toggle::make('is_published')
                                            ->label('Опубликована')
                                            ->default(true),
                                    ]),
                            ]),

                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->disk('public')
                    ->width(60)
                    ->height(40)
                    ->defaultImageUrl(asset('images/default-news-share.webp')),

                TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->sortable()
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->title),

                TextColumn::make('views')
                    ->label('Просмотры')
                    ->sortable()
                    ->alignCenter(),

                IconColumn::make('seo_title')
                    ->label('SEO')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(fn ($record) => $record->seo_title ? 'SEO заполнено' : 'SEO не заполнено (авто)')
                    ->alignCenter(),

                IconColumn::make('is_published')
                    ->label('Опубликована')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Статус')
                    ->trueLabel('Опубликованные')
                    ->falseLabel('Черновики'),
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit'   => Pages\EditNews::route('/{record}/edit'),
        ];
    }
}
