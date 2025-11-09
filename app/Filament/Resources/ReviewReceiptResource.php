<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewReceiptResource\Pages;
use App\Models\ReviewReceipt;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ReviewReceiptResource extends Resource
{
    protected static ?string $model = ReviewReceipt::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationGroup = 'Отзывы';
    protected static ?string $modelLabel = 'Чек отзыва';
    protected static ?string $pluralModelLabel = 'Чеки отзывов';

    public static function form(Form $form): Form
    {
        return $form->schema([

            // 🔹 Информация об отзыве
            Forms\Components\Section::make('Информация об отзыве')
                ->schema([
                    Forms\Components\Placeholder::make('review_liked')
                        ->label('Что понравилось')
                        ->content(function ($record) {
                            if (! $record) {
                                return '—';
                            }
                            $review = Review::find($record->review_id);
                            return $review?->liked ?: '—';
                        }),

                    Forms\Components\Placeholder::make('review_disliked')
                        ->label('Что не понравилось')
                        ->content(function ($record) {
                            if (! $record) {
                                return '—';
                            }
                            $review = Review::find($record->review_id);
                            return $review?->disliked ?: '—';
                        }),

                    Forms\Components\Placeholder::make('review_content')
                        ->label('Комментарий клиента')
                        ->content(function ($record) {
                            if (! $record) {
                                return '—';
                            }
                            $review = Review::find($record->review_id);
                            $text = $review?->content ? nl2br(e($review->content)) : '—';
                            return new HtmlString("<div class='text-sm leading-relaxed'>{$text}</div>");
                        }),
                ])
                ->columns(1),

            // 🔹 Информация о клинике
            Forms\Components\Section::make('Клиника')
                ->schema([
                    Forms\Components\Select::make('clinic_id')
                        ->relationship('clinic', 'name')
                        ->label('Клиника')
                        ->disabled()
                        ->dehydrated(false),
                ]),

            // 🔹 Сам чек и кнопка для открытия
            Forms\Components\Section::make('Файл чека')
                ->schema([
                    Forms\Components\FileUpload::make('path')
                        ->label('Файл чека')
                        ->imagePreviewHeight('150')
                        ->directory('clinics/review_receipts')
                        ->columnSpanFull(),

                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('open_receipt')
                            ->label('Открыть чек')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->color('primary')
                            ->visible(fn($record) => $record && $record->path)
                            ->url(fn($record) => asset('storage/' . $record->path), true)
                            ->extraAttributes(['target' => '_blank']),
                    ]),
                ]),

            // 🔹 Статус проверки
            Forms\Components\Section::make('Статус проверки')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Статус проверки')
                        ->options([
                            'pending' => 'Отложен',
                            'verified' => 'Подтверждён',
                            'rejected' => 'Отклонён',
                        ])
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
            Tables\Columns\TextColumn::make('clinic.name')->label('Клиника'),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'pending' => 'gray',
                    'verified' => 'success',
                    'rejected' => 'danger',
                })
                ->label('Статус'),
            Tables\Columns\ImageColumn::make('path')
                ->label('Чек')
                ->height(50),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Добавлен')
                ->dateTime('d.m.Y H:i'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviewReceipts::route('/'),
            'edit' => Pages\EditReviewReceipt::route('/{record}/edit'),
        ];
    }
}
