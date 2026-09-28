<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OwnerFeedbackResource\Pages;
use App\Models\OwnerFeedback;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OwnerFeedbackResource extends Resource
{
    protected static ?string $model = OwnerFeedback::class;

    protected static ?string $navigationIcon   = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel  = 'Обратная связь';
    protected static ?string $navigationGroup  = 'Пользователи';
    protected static ?string $modelLabel       = 'Сообщение';
    protected static ?string $pluralModelLabel = 'Обратная связь';
    protected static ?int    $navigationSort   = 5;

    public static function getNavigationBadge(): ?string
    {
        $count = OwnerFeedback::where('is_read', false)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Кто удалил')
                ->schema([
                    TextEntry::make('user_name')->label('Пользователь'),
                    TextEntry::make('created_at')->label('Дата')->dateTime('d.m.Y H:i'),
                ])->columns(2),

            Section::make('Данные удалённой карточки')
                ->schema([
                    TextEntry::make('type_label')->label('Тип карточки')->badge(),
                    TextEntry::make('entity_name')->label('Название / имя')->placeholder('—'),
                    TextEntry::make('activity_type')->label('Тип организации / специализация')->placeholder('—'),
                    TextEntry::make('region')->label('Регион')->placeholder('—'),
                    TextEntry::make('city')->label('Город')->placeholder('—'),
                    TextEntry::make('address')->label('Адрес')->placeholder('—'),
                ])->columns(2),

            Section::make('Причина удаления')
                ->schema([
                    TextEntry::make('reason')->hiddenLabel()->columnSpanFull()->prose(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (OwnerFeedback $record) => static::getUrl('view', ['record' => $record]))
            ->columns([
                Tables\Columns\IconColumn::make('is_read')->label('')->boolean()
                    ->trueIcon('heroicon-o-check-circle')->falseIcon('heroicon-s-bell-alert')
                    ->trueColor('gray')->falseColor('warning'),
                Tables\Columns\TextColumn::make('created_at')->label('Дата')->dateTime('d.m.Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('user_name')->label('Пользователь')->searchable(),
                Tables\Columns\TextColumn::make('type_label')->label('Тип')->badge(),
                Tables\Columns\TextColumn::make('entity_name')->label('Название')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('city')->label('Город')->searchable(),
                Tables\Columns\TextColumn::make('reason')->label('Причина')->limit(60),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_read')->label('Прочитано'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOwnerFeedback::route('/'),
            'view'  => Pages\ViewOwnerFeedback::route('/{record}'),
        ];
    }
}
