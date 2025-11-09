<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Пользователи';
    protected static ?string $modelLabel = 'Пользователь';
    protected static ?string $pluralModelLabel = 'Пользователи';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Имя')
                ->required(),

            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->required(),

            Forms\Components\TextInput::make('phone')
                ->label('Телефон')
                ->tel(),

            Forms\Components\Select::make('status')
                ->label('Статус')
                ->options([
                    'active' => 'Активен',
                    'ban' => 'Забанен',
                ])
                ->default('active'),

            Forms\Components\Toggle::make('is_admin')
                ->label('Администратор')
                ->inline(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')
                ->label('ID')
                ->sortable(),

            Tables\Columns\TextColumn::make('name')
                ->label('Имя')
                ->searchable(),

            Tables\Columns\TextColumn::make('email')
                ->label('Email')
                ->searchable(),

            Tables\Columns\TextColumn::make('phone')
                ->label('Телефон'),

            // Используем TextColumn + badge() вместо BadgeColumn->enum()
Tables\Columns\TextColumn::make('status')
    ->label('Статус')
    ->badge()
    ->formatStateUsing(fn($state) => [
        'active' => 'Активен',
        'ban' => 'Забанен',
    ][$state] ?? $state)
    ->colors([
        'success' => 'active',
        'danger'  => 'ban',
    ]),


            Tables\Columns\IconColumn::make('is_admin')
                ->label('Админ')
                ->boolean(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),

            Tables\Actions\Action::make('ban')
                ->label('Забанить')
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn($record) => $record->status === 'active' && !$record->is_admin)
                ->action(function ($record) {
                    $record->update(['status' => 'ban']);
                    Notification::make()
                        ->title('Пользователь забанен')
                        ->success()
                        ->send();
                }),

            Tables\Actions\Action::make('unban')
                ->label('Разбанить')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn($record) => $record->status === 'ban')
                ->action(function ($record) {
                    $record->update(['status' => 'active']);
                    Notification::make()
                        ->title('Пользователь разбанен')
                        ->success()
                        ->send();
                }),

            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
