<?php

namespace App\Filament\Resources;

use App\Models\SpecialistOwner;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\SpecialistOwnerResource\Pages;
use Filament\Forms\Components\Toggle;
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

class SpecialistOwnerResource extends Resource
{
    protected static ?string $model = SpecialistOwner::class;
    protected static ?string $navigationIcon   = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup  = 'Верификация';
    protected static ?string $navigationLabel  = 'Специалисты';
    protected static ?string $modelLabel       = 'Заявка (Специалист)';
    protected static ?string $pluralModelLabel = 'Заявки — Специалисты';
    protected static ?int    $navigationSort   = 4;

    public static function getNavigationBadge(): ?string
    {
        $count = SpecialistOwner::where('is_confirmed', false)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(SpecialistOwner::query()->with(['user', 'specialist', 'documents']))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Пользователь')
                    ->searchable()
                    ->description(fn ($record) => $record->user?->email),

                TextColumn::make('specialist.name')
                    ->label('Специалист')
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
                    ->modalHeading(fn ($record) => 'Заявка: ' . ($record->specialist?->name ?? '—'))
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

                            Placeholder::make('specialist_name')
                                ->label('Специалист')
                                ->content($record->specialist?->name ?? '—'),

                            Placeholder::make('documents_list')
                                ->label('Загруженные документы')
                                ->content(new \Illuminate\Support\HtmlString($docsHtml)),

                            Toggle::make('is_confirmed')
                                ->label('Подтверждено')
                                ->default((bool) $record->is_confirmed)
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
                        $record->update([
                            'is_confirmed'  => $data['is_confirmed'],
                            'admin_comment' => $data['admin_comment'] ?? null,
                        ]);

                        Notification::make()
                            ->title($data['is_confirmed'] ? '✅ Право подтверждено' : '⚠️ Статус обновлён')
                            ->success()
                            ->send();
                    }),

                EditAction::make()->label('Изменить'),
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
                Placeholder::make('specialist_name')
                    ->label('Специалист')
                    ->content(fn ($record) => $record?->specialist?->name ?? '—'),
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
            'index' => Pages\ListSpecialistOwners::route('/'),
            'edit'  => Pages\EditSpecialistOwner::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
