<?php

namespace App\Filament\Resources;

use App\Models\Faq;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\FaqResource\Pages;
use Filament\Forms\Components\{TextInput, Textarea, Toggle, Section, Select};
use Filament\Tables\Columns\{TextColumn, IconColumn, BadgeColumn};
use Filament\Tables;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;
    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'FAQ';
    protected static ?string $navigationGroup = 'Контент';
    protected static ?string $modelLabel = 'Вопрос';
    protected static ?string $pluralModelLabel = 'Вопросы и ответы';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Вопрос и ответ')
                    ->schema([
                        TextInput::make('question')
                            ->label('Вопрос')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Textarea::make('answer')
                            ->label('Ответ')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),

                Section::make('Настройки')
                    ->schema([
                        Select::make('category')
                            ->label('Категория')
                            ->options([
                                'general'  => 'Общие вопросы',
                                'account'  => 'Аккаунт',
                                'clinics'  => 'Клиники и врачи',
                                'reviews'  => 'Отзывы',
                                'payments' => 'Оплата',
                            ])
                            ->nullable()
                            ->placeholder('Без категории'),

                        TextInput::make('sort_order')
                            ->label('Порядок сортировки')
                            ->numeric()
                            ->default(0)
                            ->helperText('Меньше = выше в списке'),

                        Toggle::make('is_active')
                            ->label('Опубликован')
                            ->default(true),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('№')
                    ->sortable()
                    ->width(60),

                TextColumn::make('question')
                    ->label('Вопрос')
                    ->searchable()
                    ->limit(80)
                    ->tooltip(fn ($record) => $record->question),

                TextColumn::make('category')
                    ->label('Категория')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match($state) {
                        'general'  => 'Общие',
                        'account'  => 'Аккаунт',
                        'clinics'  => 'Клиники',
                        'reviews'  => 'Отзывы',
                        'payments' => 'Оплата',
                        default    => '—',
                    })
                    ->color(fn (?string $state) => match($state) {
                        'general'  => 'gray',
                        'account'  => 'info',
                        'clinics'  => 'success',
                        'reviews'  => 'warning',
                        'payments' => 'danger',
                        default    => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('Опубликован')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Обновлён')
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')  // drag-and-drop сортировка
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Категория')
                    ->options([
                        'general'  => 'Общие вопросы',
                        'account'  => 'Аккаунт',
                        'clinics'  => 'Клиники и врачи',
                        'reviews'  => 'Отзывы',
                        'payments' => 'Оплата',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Статус')
                    ->trueLabel('Только опубликованные')
                    ->falseLabel('Только скрытые'),
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
            'index'  => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit'   => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
