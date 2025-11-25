<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AwardResource\Pages;
use App\Filament\Resources\AwardResource\RelationManagers;
use App\Models\Award;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AwardResource extends Resource
{
    protected static ?string $model = Award::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

public static function form(Form $form): Form
{
    return $form
        ->schema([
            
            Forms\Components\Select::make('doctor_id')
                ->label('Доктор')
                ->relationship('doctor', 'name')
                ->disabled()
->dehydrated(false)

                ->required(),

            Forms\Components\Select::make('clinic_id')
                ->label('Клиника')
                ->relationship('clinic', 'name')
                ->disabled()


                ->required(),

            // Фото награды
            Forms\Components\FileUpload::make('image')
                ->label('Фото')
                ->image()
                ->directory('awards')
                ->imagePreviewHeight('150')
                ->openable(), // Позволяет открыть картинку на весь экран

            // Статус награды
            Forms\Components\Select::make('confirmed')
                ->label('Статус')
                ->options([
                    'pending' => 'На проверке',
                    'accepted' => 'Одобрена',
                    'rejected' => 'Отклонена',
                ])
                ->required(),
        ]);
}


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('doctor.name')
    ->label('Доктор')
    ->toggleable(),

Tables\Columns\TextColumn::make('clinic.name')
    ->label('Клиника')
    ->toggleable(),

Tables\Columns\ImageColumn::make('image')
    ->label('Фото'),

Tables\Columns\SelectColumn::make('confirmed')
    ->label('Статус')
    ->options([
        'pending' => 'На проверке',
        'accepted' => 'Одобрена',
        'rejected' => 'Отклонена',
    ])
    ->afterStateUpdated(function ($record, $state) {
        if ($state === 'accepted' && $record->doctor) {
            $record->doctor->notify(new \App\Notifications\AwardApproved($record));
        }
    }),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAwards::route('/'),
            'create' => Pages\CreateAward::route('/create'),
            'edit' => Pages\EditAward::route('/{record}/edit'),
        ];
    }
}
