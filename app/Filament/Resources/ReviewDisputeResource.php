<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewDisputeResource\Pages;
use App\Models\ReviewDispute;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Спорные отзывы: владелец карточки оспорил отзыв, отзыв скрыт до решения админа.
 * Внутри — отзыв и два диалога: с владельцем карточки и с автором отзыва.
 */
class ReviewDisputeResource extends Resource
{
    protected static ?string $model = ReviewDispute::class;

    protected static ?string $navigationIcon   = 'heroicon-o-scale';
    protected static ?string $navigationGroup  = 'Отзывы';
    protected static ?string $navigationLabel  = 'Спорные отзывы';
    protected static ?string $modelLabel       = 'Спорный отзыв';
    protected static ?string $pluralModelLabel = 'Спорные отзывы';
    protected static ?int    $navigationSort   = 3;

    private const TYPE_LABELS = [
        \App\Models\Clinic::class       => 'Клиника',
        \App\Models\Organization::class => 'Организация',
        \App\Models\Doctor::class       => 'Врач',
        \App\Models\Specialist::class   => 'Специалист',
    ];

    /** В меню: сколько споров ждут решения. */
    public static function getNavigationBadge(): ?string
    {
        $count = ReviewDispute::where('status', ReviewDispute::STATUS_OPEN)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function typeLabel(?string $class): string
    {
        return self::TYPE_LABELS[$class] ?? '';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['review.user', 'review.reviewable', 'opener', 'messages']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (ReviewDispute $record) => static::getUrl('view', ['record' => $record]))
            ->columns([
                Tables\Columns\IconColumn::make('unread')
                    ->label('')
                    ->state(fn (ReviewDispute $r) => $r->messages->contains(fn ($m) => ! $m->is_admin && ! $m->is_read))
                    ->boolean()
                    ->trueIcon('heroicon-s-bell-alert')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->tooltip(fn (ReviewDispute $r) => $r->messages->contains(fn ($m) => ! $m->is_admin && ! $m->is_read)
                        ? 'Есть непрочитанные сообщения от пользователей' : null),

                Tables\Columns\TextColumn::make('created_at')->label('Дата')->dateTime('d.m.Y H:i')->sortable(),

                Tables\Columns\TextColumn::make('card')
                    ->label('Карточка')
                    ->state(fn (ReviewDispute $r) => $r->review?->reviewable?->name ?? '—')
                    ->description(fn (ReviewDispute $r) => static::typeLabel($r->review?->reviewable_type))
                    ->limit(40),

                Tables\Columns\TextColumn::make('review_text')
                    ->label('Отзыв')
                    ->state(fn (ReviewDispute $r) => Str::limit((string) ($r->review?->content ?: ($r->review?->liked ?: $r->review?->disliked)), 70) ?: '—')
                    ->wrap(),

                Tables\Columns\TextColumn::make('opener.name')->label('Владелец (оспорил)')->searchable(),

                Tables\Columns\TextColumn::make('review.user.name')->label('Автор отзыва'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ReviewDispute::STATUS_LABELS[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        ReviewDispute::STATUS_OPEN     => 'warning',
                        ReviewDispute::STATUS_RESTORED => 'success',
                        ReviewDispute::STATUS_REMOVED  => 'danger',
                        default                        => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options(ReviewDispute::STATUS_LABELS),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Открыть')
                    ->icon('heroicon-o-eye')
                    ->url(fn (ReviewDispute $record) => static::getUrl('view', ['record' => $record])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviewDisputes::route('/'),
            'view'  => Pages\ViewReviewDispute::route('/{record}'),
        ];
    }
}
