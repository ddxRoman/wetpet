<?php

namespace App\Filament\Resources;

use App\Models\ClinicOwner;
use App\Models\OrganizationOwner;
use App\Models\DoctorOwner;
use App\Models\SpecialistOwner;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\OwnerVerificationResource\Pages;
use Filament\Forms\Components\{Toggle, Textarea, Section, Placeholder, Repeater, ViewField};
use Filament\Tables\Columns\{TextColumn, IconColumn};
use Filament\Tables\Filters\{SelectFilter, TernaryFilter};
use Filament\Tables\Actions\{Action, ViewAction, DeleteAction, BulkActionGroup, DeleteBulkAction};
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OwnerVerificationResource extends Resource
{
    // Базовая модель — ClinicOwner, остальные типы подмешиваются через union в query()
    protected static ?string $model = ClinicOwner::class;

    protected static ?string $navigationIcon   = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel  = 'Верификация владельцев';
    protected static ?string $navigationGroup  = 'Управление';
    protected static ?string $modelLabel       = 'Заявка на верификацию';
    protected static ?string $pluralModelLabel = 'Заявки на верификацию';
    protected static ?int    $navigationSort   = 1;

    // Бейдж с количеством необработанных заявок
    public static function getNavigationBadge(): ?string
    {
        $count =
            ClinicOwner::where('is_confirmed', false)->count() +
            OrganizationOwner::where('is_confirmed', false)->count() +
            DoctorOwner::where('is_confirmed', false)->count() +
            SpecialistOwner::where('is_confirmed', false)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    // ── Собираем все 4 таблицы в единый список через UNION ALL ──
    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $clinics = DB::table('clinic_owners')
                    ->join('users', 'users.id', '=', 'clinic_owners.user_id')
                    ->join('clinics', 'clinics.id', '=', 'clinic_owners.clinic_id')
                    ->select([
                        'clinic_owners.id',
                        'clinic_owners.user_id',
                        'clinic_owners.is_confirmed',
                        'clinic_owners.admin_comment',
                        'clinic_owners.created_at',
                        'users.name as user_name',
                        'users.email as user_email',
                        'clinics.name as entity_name',
                        DB::raw("'clinic' as entity_type"),
                        DB::raw("clinic_owners.clinic_id as entity_id"),
                    ]);

                $organizations = DB::table('organization_owners')
                    ->join('users', 'users.id', '=', 'organization_owners.user_id')
                    ->join('organizations', 'organizations.id', '=', 'organization_owners.organization_id')
                    ->select([
                        'organization_owners.id',
                        'organization_owners.user_id',
                        'organization_owners.is_confirmed',
                        'organization_owners.admin_comment',
                        'organization_owners.created_at',
                        'users.name as user_name',
                        'users.email as user_email',
                        'organizations.name as entity_name',
                        DB::raw("'organization' as entity_type"),
                        DB::raw("organization_owners.organization_id as entity_id"),
                    ]);

                $doctors = DB::table('doctor_owners')
                    ->join('users', 'users.id', '=', 'doctor_owners.user_id')
                    ->join('doctors', 'doctors.id', '=', 'doctor_owners.doctor_id')
                    ->select([
                        'doctor_owners.id',
                        'doctor_owners.user_id',
                        'doctor_owners.is_confirmed',
                        'doctor_owners.admin_comment',
                        'doctor_owners.created_at',
                        'users.name as user_name',
                        'users.email as user_email',
                        'doctors.name as entity_name',
                        DB::raw("'doctor' as entity_type"),
                        DB::raw("doctor_owners.doctor_id as entity_id"),
                    ]);

                $specialists = DB::table('specialist_owners')
                    ->join('users', 'users.id', '=', 'specialist_owners.user_id')
                    ->join('specialists', 'specialists.id', '=', 'specialist_owners.specialist_id')
                    ->select([
                        'specialist_owners.id',
                        'specialist_owners.user_id',
                        'specialist_owners.is_confirmed',
                        'specialist_owners.admin_comment',
                        'specialist_owners.created_at',
                        'users.name as user_name',
                        'users.email as user_email',
                        'specialists.name as entity_name',
                        DB::raw("'specialist' as entity_type"),
                        DB::raw("specialist_owners.specialist_id as entity_id"),
                    ]);

                // ВАЖНО: уникальный id для строки таблицы (иначе Filament не сможет
                // различить записи из разных таблиц с одинаковым числовым id)
                $union = $clinics->unionAll($organizations)->unionAll($doctors)->unionAll($specialists);

                return ClinicOwner::query()->fromSub($union, 'owner_requests');
            })
            ->columns([
                TextColumn::make('entity_type')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'clinic'       => 'Клиника',
                        'organization' => 'Организация',
                        'doctor'       => 'Врач',
                        'specialist'   => 'Специалист',
                        default        => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'clinic'       => 'info',
                        'organization' => 'success',
                        'doctor'       => 'warning',
                        'specialist'   => 'primary',
                        default        => 'gray',
                    }),

                TextColumn::make('user_name')
                    ->label('Пользователь')
                    ->searchable()
                    ->description(fn ($record) => $record->user_email),

                TextColumn::make('entity_name')
                    ->label('Объект')
                    ->searchable(),

                IconColumn::make('is_confirmed')
                    ->label('Статус')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                TextColumn::make('admin_comment')
                    ->label('Комментарий')
                    ->limit(40)
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Дата заявки')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('entity_type')
                    ->label('Тип объекта')
                    ->options([
                        'clinic'       => 'Клиника',
                        'organization' => 'Организация',
                        'doctor'       => 'Врач',
                        'specialist'   => 'Специалист',
                    ]),
                TernaryFilter::make('is_confirmed')
                    ->label('Статус')
                    ->trueLabel('Подтверждённые')
                    ->falseLabel('Ожидают проверки'),
            ])
            ->actions([
                // Просмотр документов + подтверждение/отклонение — одна модалка
                Action::make('review')
                    ->label('Открыть заявку')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->color('primary')
                    ->modalHeading(fn ($record) => 'Заявка: ' . $record->entity_name)
                    ->modalWidth('2xl')
                    ->form(function ($record) {
                        $documents = self::getDocumentsFor($record->entity_type, $record->id);

                        return [
                            Placeholder::make('user_info')
                                ->label('Пользователь')
                                ->content($record->user_name . ' (' . $record->user_email . ')'),

                            Placeholder::make('entity_info')
                                ->label('Объект')
                                ->content($record->entity_name),

                            Placeholder::make('documents_list')
                                ->label('Загруженные документы')
                                ->content(function () use ($documents) {
                                    if ($documents->isEmpty()) {
                                        return new \Illuminate\Support\HtmlString(
                                            '<span class="text-gray-400">Документы пока не загружены</span>'
                                        );
                                    }
                                    $html = '<div style="display:flex;flex-direction:column;gap:8px;">';
                                    foreach ($documents as $doc) {
                                        $url = Storage::url($doc->path);
                                        $html .= '<a href="' . $url . '" target="_blank" style="color:#2563eb;text-decoration:underline;">📄 '
                                            . e($doc->original_name ?: 'Документ #' . $doc->id) . '</a>';
                                        if ($doc->comment) {
                                            $html .= '<div style="font-size:12px;color:#666;margin-left:20px;">' . e($doc->comment) . '</div>';
                                        }
                                    }
                                    $html .= '</div>';
                                    return new \Illuminate\Support\HtmlString($html);
                                }),

                            Toggle::make('is_confirmed')
                                ->label('Подтверждено')
                                ->default($record->is_confirmed)
                                ->onColor('success')
                                ->offColor('danger'),

                            Textarea::make('admin_comment')
                                ->label('Комментарий администратора')
                                ->default($record->admin_comment)
                                ->placeholder('Причина отклонения или примечание...')
                                ->rows(3),
                        ];
                    })
                    ->action(function (array $data, $record) {
                        $table = match ($record->entity_type) {
                            'clinic'       => 'clinic_owners',
                            'organization' => 'organization_owners',
                            'doctor'       => 'doctor_owners',
                            'specialist'   => 'specialist_owners',
                        };

                        DB::table($table)->where('id', $record->id)->update([
                            'is_confirmed'  => $data['is_confirmed'],
                            'admin_comment' => $data['admin_comment'],
                            'updated_at'    => now(),
                        ]);

                        Notification::make()
                            ->title($data['is_confirmed'] ? 'Право подтверждено' : 'Статус обновлён')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function form(Form $form): Form
    {
        // Форма редактирования через стандартный edit-экран не используется —
        // вся работа происходит через action 'review' в таблице.
        return $form->schema([]);
    }

    private static function getDocumentsFor(string $entityType, int $ownerRowId)
    {
        $modelClass = match ($entityType) {
            'clinic'       => \App\Models\ClinicOwner::class,
            'organization' => \App\Models\OrganizationOwner::class,
            'doctor'       => \App\Models\DoctorOwner::class,
            'specialist'   => \App\Models\SpecialistOwner::class,
        };

        $ownerRow = $modelClass::find($ownerRowId);

        return $ownerRow ? $ownerRow->documents : collect();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOwnerVerifications::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
