<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteNoticeResource\Pages;
use App\Models\Animal;
use App\Models\City;
use App\Models\SiteNotice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Уведомления для посетителей сайта: модальное окно поверх страницы с текстом и/или картинкой.
 * Задаётся: расписание и частота показа, аудитория (питомцы / город / регион), таймер кнопки закрытия.
 */
class SiteNoticeResource extends Resource
{
    protected static ?string $model = SiteNotice::class;

    protected static ?string $navigationIcon   = 'heroicon-o-bell-alert';
    protected static ?string $navigationGroup  = 'Контент';
    protected static ?string $navigationLabel  = 'Уведомления';
    protected static ?string $modelLabel       = 'Уведомление';
    protected static ?string $pluralModelLabel = 'Уведомления';
    protected static ?int    $navigationSort   = 10;

    /** В меню — сколько уведомлений показывается сейчас. */
    public static function getNavigationBadge(): ?string
    {
        $count = SiteNotice::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'success';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('views')
            ->withSum('views as shown_total', 'shown_count');
    }

    public static function form(Form $form): Form
    {
        $tz = SiteNotice::TIMEZONE;

        return $form->schema([

            /* ───────────── Содержимое ───────────── */
            Forms\Components\Section::make('Что показываем')
                ->description('Нужен текст и/или картинка. Окно открывается поверх страницы.')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Заголовок (необязательно)')
                        ->maxLength(150)
                        ->placeholder('Например: Пропала собака в станице Динской'),

                    Forms\Components\Textarea::make('body')
                        ->label('Текст')
                        ->rows(6)
                        ->maxLength(3000)
                        ->requiredWithout('image')
                        ->helperText('Обычный текст, абзацы разделяйте пустой строкой. Разметка (теги) не поддерживается.'),

                    Forms\Components\FileUpload::make('image')
                        ->label('Картинка')
                        ->image()
                        ->imageEditor()
                        ->disk('public')
                        ->directory('site-notices')
                        ->visibility('public')
                        ->maxSize(5120)
                        ->requiredWithout('body')
                        ->helperText('JPG/PNG/WebP до 5 МБ. Показывается над текстом; можно загрузить без текста (например, ориентировку).'),
                ]),

            /* ───────────── Когда и как часто ───────────── */
            Forms\Components\Section::make('Когда показывать')
                ->description("Все даты и время — по московскому времени ({$tz}).")
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->label('Уведомление включено')
                        ->default(true)
                        ->inline(false),

                    Forms\Components\TextInput::make('priority')
                        ->label('Приоритет')
                        ->numeric()
                        ->default(0)
                        ->helperText('Если подходят сразу несколько уведомлений, раньше показывается то, у которого число больше.'),

                    Forms\Components\DateTimePicker::make('starts_at')
                        ->label('Показывать с')
                        ->timezone($tz)
                        ->seconds(false)
                        ->native(false)
                        ->displayFormat('d.m.Y H:i')
                        ->helperText('Пусто — сразу после сохранения.'),

                    Forms\Components\DateTimePicker::make('ends_at')
                        ->label('Показывать до')
                        ->timezone($tz)
                        ->seconds(false)
                        ->native(false)
                        ->displayFormat('d.m.Y H:i')
                        ->after('starts_at')
                        ->helperText('Пусто — без ограничения. Чтобы показать в один день, укажите начало и конец этого дня.'),

                    Forms\Components\TimePicker::make('daily_from')
                        ->label('Каждый день с (время)')
                        ->seconds(false)
                        ->helperText('Необязательно. Например, с 09:00.'),

                    Forms\Components\TimePicker::make('daily_until')
                        ->label('Каждый день до (время)')
                        ->seconds(false)
                        ->helperText('Необязательно. Можно «через полночь»: с 22:00 до 06:00.'),

                    Forms\Components\Select::make('frequency_type')
                        ->label('Как часто показывать одному человеку')
                        ->options(SiteNotice::FREQUENCIES)
                        ->default('once')
                        ->required()
                        ->live()
                        ->native(false)
                        ->helperText('«При каждой загрузке страницы» — очень навязчиво, используйте только для срочных объявлений.'),

                    Forms\Components\TextInput::make('frequency_value')
                        ->label(fn (Get $get) => $get('frequency_type') === 'days' ? 'Раз в сколько дней' : 'Раз в сколько часов')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(8760)
                        ->default(24)
                        ->visible(fn (Get $get) => in_array($get('frequency_type'), ['hours', 'days'], true))
                        ->required(fn (Get $get) => in_array($get('frequency_type'), ['hours', 'days'], true)),
                ]),

            /* ───────────── Кому ───────────── */
            Forms\Components\Section::make('Кому показывать')
                ->description('Если ничего не выбрано — всем посетителям. Условия складываются: нужно подойти под все заполненные (город и регион — любое из двух).')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('user_scope')
                        ->label('Посетители')
                        ->options(SiteNotice::SCOPES)
                        ->default('all')
                        ->required()
                        ->native(false)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('species')
                        ->label('Владельцы животных')
                        ->multiple()
                        ->options(fn () => Animal::query()
                            ->whereNotNull('species')->where('species', '!=', '')
                            ->distinct()->orderBy('species')->pluck('species', 'species')->all())
                        ->searchable()
                        ->helperText('Только зарегистрированные, у которых в профиле есть живой питомец такого вида (например, «Собака»).')
                        ->columnSpanFull(),

                    Forms\Components\Select::make('regions')
                        ->label('Регионы')
                        ->multiple()
                        ->options(fn () => City::query()
                            ->whereNotNull('region')->where('region', '!=', '')
                            ->distinct()->orderBy('region')->pluck('region', 'region')->all())
                        ->searchable()
                        ->helperText('Жители этих регионов: по городу, выбранному на сайте, или городу в профиле.'),

                    Forms\Components\Select::make('city_ids')
                        ->label('Города / населённые пункты')
                        ->multiple()
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search) => City::query()
                            ->where('name', 'like', '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%')
                            ->orderBy('name')->limit(50)->get()
                            ->mapWithKeys(fn (City $c) => [$c->id => $c->name . ' — ' . $c->region])->all())
                        ->getOptionLabelsUsing(fn (array $values) => City::query()
                            ->whereIn('id', $values)->get()
                            ->mapWithKeys(fn (City $c) => [$c->id => $c->name . ' — ' . $c->region])->all())
                        ->helperText('Начните вводить название. Подойдёт для «пропал питомец в станице Динской».'),
                ]),

            /* ───────────── Окно ───────────── */
            Forms\Components\Section::make('Кнопка закрытия')
                ->schema([
                    Forms\Components\TextInput::make('close_delay')
                        ->label('Крестик появится через (секунд)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(300)
                        ->default(0)
                        ->required()
                        ->suffix('сек')
                        ->helperText('0 — крестик сразу. До этого времени в углу окна идёт обратный отсчёт, закрыть окно нельзя.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('')->disk('public')->square()->size(56),

                Tables\Columns\TextColumn::make('title')
                    ->label('Уведомление')
                    ->state(fn (SiteNotice $r) => $r->title ?: Str::limit((string) $r->body, 60) ?: 'Только картинка')
                    ->description(fn (SiteNotice $r) => $r->title ? Str::limit((string) $r->body, 70) : null)
                    ->searchable(['title', 'body'])
                    ->wrap(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->state(fn (SiteNotice $r) => $r->status)
                    ->formatStateUsing(fn (string $state) => SiteNotice::statusLabel($state))
                    ->color(fn (string $state) => match ($state) {
                        'active'    => 'success',
                        'scheduled' => 'warning',
                        'expired'   => 'gray',
                        default     => 'danger',
                    }),

                Tables\Columns\TextColumn::make('schedule')
                    ->label('Когда')
                    ->state(fn (SiteNotice $r) => $r->scheduleLabel())
                    ->description(fn (SiteNotice $r) => $r->frequencyLabel())
                    ->wrap(),

                Tables\Columns\TextColumn::make('audience')
                    ->label('Кому')
                    ->state(fn (SiteNotice $r) => $r->audienceLabel())
                    ->wrap(),

                Tables\Columns\TextColumn::make('close_delay')
                    ->label('Крестик')
                    ->formatStateUsing(fn ($state) => (int) $state === 0 ? 'сразу' : "через {$state} с")
                    ->toggleable(),

                Tables\Columns\TextColumn::make('shown_total')
                    ->label('Показов')
                    ->state(fn (SiteNotice $r) => (int) $r->shown_total)
                    ->description(fn (SiteNotice $r) => 'людей: ' . (int) $r->views_count),

                Tables\Columns\ToggleColumn::make('is_active')->label('Вкл.'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Включено'),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Предпросмотр')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Так увидит посетитель')
                    ->modalContent(fn (SiteNotice $record) => view('filament.resources.site-notice.preview', ['notice' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Закрыть'),

                Tables\Actions\Action::make('onSite')
                    ->label('Показать на сайте')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (SiteNotice $record) => url('/') . '?preview_notice=' . $record->id)
                    ->openUrlInNewTab(),

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
            'index'  => Pages\ListSiteNotices::route('/'),
            'create' => Pages\CreateSiteNotice::route('/create'),
            'edit'   => Pages\EditSiteNotice::route('/{record}/edit'),
        ];
    }
}
