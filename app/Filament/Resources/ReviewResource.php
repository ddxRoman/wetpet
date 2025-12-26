<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';
    protected static ?string $navigationGroup = 'Отзывы';
    protected static ?string $modelLabel = 'Отзыв';
    protected static ?string $pluralModelLabel = 'Отзывы';

    public static function getPages(): array
{
    return [
        'index' => Pages\ListReviews::route('/'),
        'edit'  => Pages\EditReview::route('/{record}/edit'),
    ];
}


    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Информация об отзыве')
                ->schema([
                    // 🔹 Пользователь
                    Forms\Components\TextInput::make('user_name')
                        ->label('Пользователь')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn($record) => $record?->user?->name ?? '—'),

                    // 🔹 Клиника (через полиморфную связь)
                    Forms\Components\TextInput::make('clinic_name')
                        ->label('Клиника')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(function ($record) {
                            if (! $record) return '—';
                            $reviewable = $record->reviewable;
                            if ($reviewable && $reviewable instanceof \App\Models\Clinic) {
                                return $reviewable->name;
                            }
                            return '—';
                        }),

                    // 🔹 Что понравилось
                    Forms\Components\TextInput::make('liked')
                        ->label('Что понравилось')
                        ->disabled()
                        ->dehydrated(false),

                    // 🔹 Что не понравилось
                    Forms\Components\TextInput::make('disliked')
                        ->label('Что не понравилось')
                        ->disabled()
                        ->dehydrated(false),

                    // 🔹 Комментарий
                    Forms\Components\Textarea::make('content')
                        ->label('Комментарий клиента')
                        ->disabled()
                        ->dehydrated(false)
                        ->rows(5),

                    // 🔹 Кнопки действий
                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('mark_disputed')
                            ->label('Пометить как оспоренный')
                            ->color('danger')
                            ->icon('heroicon-o-flag')
                            ->requiresConfirmation()
                            ->visible(fn($record) => $record && $record->status !== 'disputed')
                            ->action(function ($record) {
                                $record->update(['status' => 'disputed']);
                                \Filament\Notifications\Notification::make()
                                    ->title('Отзыв помечен как оспоренный')
                                    ->success()
                                    ->send();
                            }),

                        Forms\Components\Actions\Action::make('delete_review')
                            ->label('Удалить отзыв')
                            ->icon('heroicon-o-trash')
                            ->color('gray')
                            ->requiresConfirmation()
                            ->action(function ($record) {
                                $record->delete();
                                \Filament\Notifications\Notification::make()
                                    ->title('Отзыв удалён')
                                    ->success()
                                    ->send();
                                return redirect(\App\Filament\Resources\ReviewResource::getUrl('index'));
                            }),
                    ]),
                ])
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),

            Tables\Columns\TextColumn::make('user.name')
                ->label('Пользователь')
                ->searchable(),

            Tables\Columns\TextColumn::make('reviewable.name')
                ->label('Клиника')
                ->getStateUsing(function ($record) {
                    if ($record->reviewable && $record->reviewable instanceof \App\Models\Clinic) {
                        return $record->reviewable->name;
                    }
                    return '—';
                }),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Дата')
                ->dateTime('d.m.Y H:i'),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
            Tables\Actions\Action::make('markDisputed')
                ->label('Оспоренный')
                ->icon('heroicon-o-flag')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn($record) => $record->status !== 'disputed')
                ->action(fn($record) => $record->update(['status' => 'disputed'])),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }
}
