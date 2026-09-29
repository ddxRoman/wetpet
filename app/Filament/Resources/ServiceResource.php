<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\FieldOfActivity;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
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

    /**
     * Направления организаций: field_of_activities.type = 'organization'.
     * В базе (и в остальном коде) услуга хранит название направления, поэтому ключ = значение = name.
     */
    protected static function organizationSpecializations(): array
    {
        return FieldOfActivity::query()
            ->where('type', 'organization')
            ->orderBy('name')
            ->pluck('name', 'name')
            ->toArray();
    }

    /**
     * Специализации людей (врачи/специалисты) для выбранного направления организации.
     * Связь — по колонке activity в field_of_activities: у «Груминг салон» activity = grooming,
     * у «Грумер» тоже grooming; у «Ветеринарная клиника» и у всех врачей activity = doctor.
     *
     *  - направление выбрано и есть в справочнике → только его специалисты (может быть пусто,
     *    например у «Ветаптека» специалистов нет);
     *  - направление не выбрано (или значение не из справочника) → все специалисты и врачи.
     */
    protected static function specialistSpecializationsFor(?string $organizationName): array
    {
        $query = FieldOfActivity::query()->where('type', 'specialist');

        if (filled($organizationName)) {
            $activity = FieldOfActivity::where('type', 'organization')
                ->where('name', $organizationName)
                ->value('activity');

            if ($activity !== null) {
                $query->where('activity', $activity);
            }
        }

        return $query->orderBy('name')->pluck('name', 'name')->toArray();
    }

    /**
     * Есть ли у выбранного направления организации связанные специалисты.
     */
    protected static function organizationHasNoSpecialists(?string $organizationName): bool
    {
        if (blank($organizationName)) {
            return false;
        }

        $activity = FieldOfActivity::where('type', 'organization')
            ->where('name', $organizationName)
            ->value('activity');

        return $activity !== null && ! FieldOfActivity::where('type', 'specialist')->where('activity', $activity)->exists();
    }

    /**
     * Если у услуги уже сохранено значение, которого нет в справочнике (старые данные —
     * например «Терапия» или «Зооцентр»), оставляем его в списке, чтобы при редактировании
     * оно не пропало и не затёрлось при сохранении. $known — все значения справочника
     * (по умолчанию — ключи $options).
     */
    protected static function withCurrentValue(array $options, ?string $current, ?array $known = null): array
    {
        $known ??= array_keys($options);

        if (filled($current) && ! in_array($current, $known, true) && ! array_key_exists($current, $options)) {
            $options = [$current => $current . ' (не из справочника)'] + $options;
        }

        return $options;
    }

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

                    Forms\Components\Select::make('specialization')
                        ->label('Специализация организации')
                        ->options(fn (?Service $record) => self::withCurrentValue(
                            self::organizationSpecializations(),
                            $record?->specialization
                        ))
                        ->searchable()
                        ->live()
                        ->placeholder('— не выбрано —')
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            // Если ранее выбранная специализация не относится к новому направлению — сбрасываем её.
                            $allowed = self::specialistSpecializationsFor($get('specialization'));

                            if (! array_key_exists((string) $get('specialization_doctor'), $allowed)) {
                                $set('specialization_doctor', null);
                            }
                        })
                        ->helperText('Направление деятельности организации, для которого услуга считается «своей» (необязательно). Список берётся из «Направлений» с типом «Организация».'),

                    Forms\Components\Select::make('specialization_doctor')
                        ->label('Специализация врача / специалиста')
                        ->options(fn (Get $get, ?Service $record) => self::withCurrentValue(
                            self::specialistSpecializationsFor($get('specialization')),
                            $record?->specialization_doctor,
                            FieldOfActivity::where('type', 'specialist')->pluck('name')->all()
                        ))
                        ->searchable()
                        ->placeholder('— не выбрано —')
                        ->helperText(fn (Get $get) => self::organizationHasNoSpecialists($get('specialization'))
                            ? 'Для выбранного направления организации специализации врачей/специалистов не предусмотрены.'
                            : 'Зависит от «Специализации организации»: показываются специалисты выбранного направления (для «Ветеринарная клиника» — врачи, для «Груминг салон» — грумеры и т. д.). Если направление не выбрано — все специалисты и врачи.'),
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
