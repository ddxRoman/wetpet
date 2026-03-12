<?php

namespace App\Filament\Resources;

use App\Models\Doctor;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Resources\DoctorResource\Pages;
use Illuminate\Support\Carbon;

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;
    protected static ?string $navigationLabel = 'Врачи';
    protected static ?string $navigationGroup = 'Контент сайта';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

public static function form(Form $form): Form
{
    return $form->schema([

Forms\Components\TextInput::make('name')
    ->label('Имя')
    ->required()
    ->reactive()
    ->afterStateUpdated(function ($state, callable $set, $get) {
        if (! $get('slug')) {
            $set('slug', \Illuminate\Support\Str::slug($state));
        }
    }),


Forms\Components\TextInput::make('slug')
    ->label('Slug')
    ->required()
    ->unique(ignoreRecord: true),

        // ───── СПЕЦИАЛИЗАЦИЯ ─────
Forms\Components\Select::make('specialization')
    ->label('Специализация')
    ->options(
        \App\Models\FieldOfActivity::query()
            ->where('type', 'specialist')
            ->where('activity', 'doctor')
            ->pluck('name', 'name')
    )
    ->searchable()
    ->required(),


Forms\Components\DatePicker::make('date_of_birth')
    ->label('Дата рождения')
    ->maxDate(now()->subYears(18))
    ->minDate(now()->subYears(70))
     ->reactive(),   // 🔥 ВАЖНО

        // ───── РЕГИОН (виртуальное поле) ─────
        Forms\Components\Select::make('region')
            ->label('Регион')
            ->options(
                \App\Models\City::query()
                    ->select('region')
                    ->distinct()
                    ->pluck('region', 'region')
            )
            ->searchable()
            ->reactive()
            ->afterStateHydrated(function (callable $set, $state, $record) {
                if ($record?->city?->region) {
                    $set('region', $record->city->region);
                }
            })
            ->afterStateUpdated(function (callable $set) {
                $set('city_id', null);
                $set('clinic_id', null);
            }),

        // ───── ГОРОД ─────
        Forms\Components\Select::make('city_id')
            ->label('Город')
            ->options(fn (callable $get) =>
                $get('region')
                    ? \App\Models\City::where('region', $get('region'))
                        ->pluck('name', 'id')
                    : []
            )
            ->searchable()
            ->reactive()
            ->required()
            ->afterStateUpdated(fn (callable $set) => $set('clinic_id', null)),

        // ───── КЛИНИКА ─────
Forms\Components\Select::make('clinic_id')
    ->label('Клиника')
    ->options(function (callable $get) {
        $cityId = $get('city_id');

        if (! $cityId) {
            return [];
        }

        $cityName = \App\Models\City::where('id', $cityId)->value('name');

        return \App\Models\Clinic::where('city', $cityName)
            ->pluck('name', 'id');
    })
    ->searchable()
    ->reactive(),

Forms\Components\TextInput::make('experience')
    ->label('Опыт (лет)')
    ->type('number')        // 🔥 ВАЖНО
    ->reactive()            // 🔥 ВАЖНО
    ->minValue(0)
    ->maxValue(function (callable $get) {
        $birthDate = $get('date_of_birth');

        if (! $birthDate) {
            return null;
        }

        return max(
            0,
            Carbon::parse($birthDate)->diffInYears(now()) - 18
        );
    })
    ->extraInputAttributes(function (callable $get) {
        $birthDate = $get('date_of_birth');

        if (! $birthDate) {
            return [];
        }

        return [
            'type' => 'number',
            'min'  => 0,
            'max'  => max(
                0,
                Carbon::parse($birthDate)->diffInYears(now()) - 18
            ),
        ];
    })
    ->helperText('Опыт не может быть больше чем период совершеннолетия врача'),


        Forms\Components\Select::make('exotic_animals')
            ->label('Экзотические животные')
            ->options([
                'yes' => 'Да',
                'no' => 'Нет',
            ]),

        Forms\Components\Select::make('On_site_assistance')
            ->label('Выезд на дом')
            ->options([
                'yes' => 'Да',
                'no' => 'Нет',
            ]),


Forms\Components\FileUpload::make('photo')
    ->label('Фото')
    ->directory('doctors')
    ->preserveFilenames()
    ->openable()
    ->downloadable()
    ->previewable()
    ->rules([]),



        Forms\Components\Textarea::make('description')
            ->label('Описание')
            ->rows(5)
            ->columnSpanFull(),

        // ───── КОНТАКТЫ ─────
        Forms\Components\Section::make('Контакты')
            ->relationship('contacts')
            ->schema([
                Forms\Components\TextInput::make('phone')
                    ->label('Телефон'),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email(),

                Forms\Components\TextInput::make('telegram')
                    ->label('Telegram'),

                Forms\Components\TextInput::make('whatsapp')
                    ->label('WhatsApp'),

                Forms\Components\TextInput::make('max')
                    ->label('Max'),
            ])
            ->columns(2)
            ->collapsible(),

        // ───── НАГРАДЫ ─────
        Forms\Components\HasManyRepeater::make('awards')
            ->relationship('awards')
            ->label('Награды')
            ->schema([
                Forms\Components\FileUpload::make('image')
                    ->label('Изображение')
                    ->directory('awards')
                    ->image(),

                Forms\Components\TextInput::make('title')
                    ->label('Название'),

                Forms\Components\Textarea::make('description')
                    ->label('Описание'),

                Forms\Components\Select::make('confirmed')
                    ->label('Статус')
                    ->options([
                        'pending' => 'На проверке',
                        'accepted' => 'Одобрена',
                        'rejected' => 'Отклонена',
                    ])
                    ->default('pending'),
            ])
            ->orderable()
            ->collapsible()
            ->columnSpanFull(),
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
                        
Tables\Columns\TextColumn::make('specialization_label')
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
