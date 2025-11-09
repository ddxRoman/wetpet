<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewReceiptResource\Pages;
use App\Models\ReviewReceipt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
        Forms\Components\Card::make([
            Forms\Components\Placeholder::make('review_info')
                ->label('Отзыв')
->content(function ($record) {
    return new \Illuminate\Support\HtmlString(
        view('admin_panel.review-card', ['record' => $record])->render()
    );
})

        ])->columnSpanFull(),

Forms\Components\Select::make('clinic_id')
    ->relationship('clinic', 'name')
    ->label('Клиника')
    ->disabled() // ❌ делаем поле неактивным
    ->dehydrated(false), // (чтобы Filament не пытался сохранять значение)


        Forms\Components\FileUpload::make('path')
            ->label('Файл чека')
            ->imagePreviewHeight('150')
            ->directory('clinics/review_receipts')
            ->columnSpanFull(),

        Forms\Components\Select::make('status')
            ->label('Статус проверки')
            ->options([
                'pending' => 'Отложен',
                'verified' => 'Подтверждён',
                'rejected' => 'Отклонён',
            ])
            ->required(),
    ]);

    

    

}


    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
            Tables\Columns\TextColumn::make('clinic.name')->label('Клиника'),
            Tables\Columns\TextColumn::make('review.id')->label('ID отзыва'),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
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
