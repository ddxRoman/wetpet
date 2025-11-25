<?php

namespace App\Filament\Resources;

use App\Models\Doctor;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Resources\DoctorResource\Pages;


class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;
    protected static ?string $navigationLabel = 'Врачи';
    protected static ?string $navigationGroup = 'Контент сайта';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('name')
                ->label('Имя')
                ->required(),

Forms\Components\Select::make('specialization')
    ->label('Специализация')
    ->options(
        \App\Models\Service::query()
            ->whereNotNull('specialization_doctor')
            ->distinct()
            ->pluck('specialization_doctor', 'specialization_doctor')
            ->toArray()
    )
    ->searchable()
    ->required(),


            Forms\Components\TextInput::make('experience')
                ->label('Опыт (лет)')
                ->numeric(),

            Forms\Components\FileUpload::make('photo')
                ->label('Фото')
                ->directory('doctors')
                ->image()
                ->imagePreviewHeight('150'),

            Forms\Components\Select::make('clinic_id')
                ->label('Клиника')
                ->relationship('clinic', 'name')
                ->searchable()
                ->preload()
                ->required(),
        ]);
}


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Фото')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),

                    Tables\Columns\TextColumn::make('clinic.name')   // 👈 новое
                        ->label('Клиника')
                        ->sortable()
                        ->searchable(),
                        
                Tables\Columns\TextColumn::make('specialization')
                    ->label('Специализация'),
                    

                Tables\Columns\TextColumn::make('experience')
                    ->label('Опыт (лет)'),
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
            'index' => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'edit' => Pages\EditDoctor::route('/{record}/edit'),
        ];
    }
}
