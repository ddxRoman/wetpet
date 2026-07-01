<?php

namespace App\Filament\Resources;

use App\Models\OrganizationOwner;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\OrganizationOwnerResource\Pages;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class OrganizationOwnerResource extends Resource
{
    protected static ?string $model = OrganizationOwner::class;
    protected static ?string $navigationIcon   = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup  = 'Верификация';
    protected static ?string $navigationLabel  = 'Организации';
    protected static ?string $modelLabel       = 'Заявка (Организация)';
    protected static ?string $pluralModelLabel = 'Заявки — Организации';
    protected static ?int    $navigationSort   = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = OrganizationOwner::where('is_confirmed', false)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(OrganizationOwner::query()->with(['user', 'organization', 'documents']))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Пользователь')
                    ->searchable()
                    ->description(fn ($record) => $record->user?->email),

                TextColumn::make('organization.name')
                    ->label('Организация')
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
                    ->limit(50)
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Дата заявки')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_confirmed')
                    ->label('Статус')
                    ->trueLabel('Подтверждённые')
                    ->falseLabel('Ожидают проверки'),
            ])
            ->actions([
                Action::make('review')
                    ->label('Открыть заявку')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->color('primary')
                    ->modalHeading(fn ($record) => 'Заявка: ' . ($record->organization?->name ?? '—'))
                    ->modalWidth('2xl')
                    ->form(function ($record) {
                        $documents = $record->documents ?? collect();

                        if ($documents->isEmpty()) {
                            $docsHtml = '<span style="color:#999;">Документы не загружены</span>';
                        } else {
                            $docsHtml = '';
                            foreach ($documents as $doc) {
                                $url     = Storage::url($doc->path);
                                $name    = e($doc->original_name ?: 'Документ #' . $doc->id);
                                $comment = $doc->comment
                                    ? '<div style="font-size:12px;color:#666;margin-left:20px;">' . e($doc->comment) . '</div>'
                                    : '';
                                $docsHtml .= '<div style="margin-bottom:6px;">'
                                    . '<a href="' . $url . '" target="_blank" style="color:#2563eb;text-decoration:underline;">📄 ' . $name . '</a>'
                                    . $comment
                                    . '</div>';
                            }
                        }

                        return [
                            Placeholder::make('user_info')
                                ->label('Пользователь')
                                ->content($record->user?->name . ' (' . $record->user?->email . ')'),

                            Placeholder::make('organization_name')
                                ->label('Организация')
                                ->content($record->organization?->name ?? '—'),

                            Placeholder::make('documents_list')
                                ->label('Загруженные документы')
                                ->content(new \Illuminate\Support\HtmlString($docsHtml)),

                            Toggle::make('is_confirmed')
                                ->label('Подтверждено')
                                ->default((bool) $record->is_confirmed)
                                ->onColor('success')
                                ->offColor('danger'),

                            Checkbox::make('is_rejected')
                                ->label('Отказано')
                                ->default((bool) $record->is_rejected)
                                ->helperText('Пользователь сможет подать повторную заявку через 7 дней'),

                            Placeholder::make('chat_history')
                                ->label('💬 Переписка с пользователем')
                                ->content(function () use ($record) {
                                    $messages = $record->messages()->with('user')->get();
                                    if ($messages->isEmpty()) {
                                        return new \Illuminate\Support\HtmlString('<span style="color:#999;">Сообщений пока нет</span>');
                                    }
                                    $html = '<div style="max-height:220px;overflow-y:auto;border:1px solid #e5e7eb;border-radius:8px;padding:10px;background:#f9fafb;">';
                                    foreach ($messages as $m) {
                                        $isAdmin = $m->is_admin;
                                        $align   = $isAdmin ? 'left' : 'right';
                                        $bg      = $isAdmin ? '#fff' : '#dbeafe';
                                        $author  = $isAdmin ? '👤 Администратор' : ('🧑 ' . e($m->user->name ?? 'Пользователь'));
                                        $html .= '<div style="text-align:' . $align . ';margin-bottom:8px;">'
                                            . '<div style="display:inline-block;max-width:80%;text-align:left;background:' . $bg . ';border:1px solid #e5e7eb;border-radius:8px;padding:6px 10px;">'
                                            . '<div style="font-size:11px;font-weight:600;color:#6b7280;">' . $author . '</div>'
                                            . '<div style="font-size:13px;">' . nl2br(e($m->message)) . '</div>'
                                            . '<div style="font-size:10px;color:#9ca3af;text-align:right;">' . $m->created_at->format('d.m.Y H:i') . '</div>'
                                            . '</div></div>';
                                    }
                                    $html .= '</div>';
                                    return new \Illuminate\Support\HtmlString($html);
                                }),

                            Textarea::make('admin_reply')
                                ->label('Ответить пользователю')
                                ->placeholder('Введите сообщение для пользователя...')
                                ->rows(2),

                            Textarea::make('admin_comment')
                                ->label('Комментарий администратора (виден в статусе заявки)')
                                ->default($record->admin_comment)
                                ->placeholder('Причина отклонения или примечание...')
                                ->rows(3),
                        ];
                    })
                    ->action(function (array $data, $record) {
                        $isRejected  = (bool) ($data['is_rejected'] ?? false);
                        $isConfirmed = (bool) ($data['is_confirmed'] ?? false);

                        // Если подтверждён — снимаем отказ
                        if ($isConfirmed) {
                            $isRejected = false;
                        }

                        $record->update([
                            'is_confirmed'  => $isConfirmed,
                            'is_rejected'   => $isRejected,
                            'rejected_at'   => $isRejected && !$record->is_rejected
                                ? now()
                                : ($isRejected ? $record->rejected_at : null),
                            'admin_comment' => $data['admin_comment'] ?? null,
                        ]);

                        // Если админ написал ответ — сохраняем его как сообщение в чате
                        if (!empty($data['admin_reply'])) {
                            \App\Models\OwnerClaimMessage::create([
                                'claimable_type' => get_class($record),
                                'claimable_id'   => $record->id,
                                'user_id'        => auth()->id(),
                                'is_admin'       => true,
                                'message'        => $data['admin_reply'],
                                'is_read'        => false,
                            ]);
                        }

                        Notification::make()
                            ->title($isConfirmed ? '✅ Право подтверждено' : ($isRejected ? '❌ Заявка отклонена' : '⚠️ Статус обновлён'))
                            ->success()
                            ->send();
                    }),


                DeleteAction::make()->label('Удалить'),
            ])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                Placeholder::make('user_info')
                    ->label('Пользователь')
                    ->content(fn ($record) => $record
                        ? ($record->user?->name . ' (' . $record->user?->email . ')')
                        : '—'
                    ),
                Placeholder::make('organization_name')
                    ->label('Организация')
                    ->content(fn ($record) => $record?->organization?->name ?? '—'),
                Toggle::make('is_confirmed')
                    ->label('Подтверждено')
                    ->onColor('success')
                    ->offColor('danger'),
                Textarea::make('admin_comment')
                    ->label('Комментарий администратора')
                    ->rows(3),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrganizationOwners::route('/'),
            'edit'  => Pages\EditOrganizationOwner::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
