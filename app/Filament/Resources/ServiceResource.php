<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon  = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Услуги';
    protected static ?string $navigationGroup = 'Контент сайта';
    protected static ?int    $navigationSort  = 1;

    protected static ?string $modelLabel       = 'Услуга';
    protected static ?string $pluralModelLabel = 'Услуги';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Услуга')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Название услуги')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Например: «Вакцинация», «Стрижка когтей», «УЗИ брюшной полости»'),

                    Forms\Components\TextInput::make('specialization')
                        ->label('Специализация организации')
                        ->maxLength(255)
                        ->helperText('Направление деятельности организации, для которого услуга считается «своей» по умолчанию (необязательно)'),

                    Forms\Components\TextInput::make('specialization_doctor')
                        ->label('Специализация врача')
                        ->maxLength(255)
                        ->helperText('Специализация врача/специалиста, для которой услуга считается «своей» по умолчанию (необязательно)'),
                ])
                ->columns(1),

            Forms\Components\Section::make('SEO')
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('seo_title')
                        ->label('SEO заголовок (title)')
                        ->maxLength(255),

                    Forms\Components\Textarea::make('seo_description')
                        ->label('SEO описание (description)')
                        ->rows(3)
                        ->maxLength(320),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('specialization')
                    ->label('Специализация организации')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('specialization_doctor')
                    ->label('Специализация врача')
                    ->badge()
                    ->color('info')
                    ->placeholder('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('prices_count')
                    ->label('Использований')
                    ->counts('prices')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('specialization')
                    ->label('Специализация организации')
                    ->options(fn () => Service::query()
                        ->whereNotNull('specialization')
                        ->distinct()
                        ->orderBy('specialization')
                        ->pluck('specialization', 'specialization')
                        ->toArray()),

                Tables\Filters\SelectFilter::make('specialization_doctor')
                    ->label('Специализация врача')
                    ->options(fn () => Service::query()
                        ->whereNotNull('specialization_doctor')
                        ->distinct()
                        ->orderBy('specialization_doctor')
                        ->pluck('specialization_doctor', 'specialization_doctor')
                        ->toArray()),
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
            'index'  => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit'   => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
