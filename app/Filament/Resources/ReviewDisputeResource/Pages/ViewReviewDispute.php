<?php

namespace App\Filament\Resources\ReviewDisputeResource\Pages;

use App\Filament\Resources\ReviewDisputeResource;
use App\Models\Review;
use App\Models\ReviewDispute;
use App\Models\ReviewDisputeMessage;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Страница спорного отзыва: сверху сам отзыв, ниже в две колонки диалоги админа —
 * с владельцем карточки (левая колонка) и с автором отзыва (правая).
 */
class ViewReviewDispute extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ReviewDisputeResource::class;

    protected static string $view = 'filament.resources.review-dispute.view';

    /** Состояние форм двух диалогов. */
    public ?array $ownerData  = [];
    public ?array $authorData = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        // Админ открыл спор — сообщения пользователей прочитаны
        ReviewDisputeMessage::where('review_dispute_id', $this->record->id)
            ->where('is_admin', false)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $this->ownerForm->fill();
        $this->authorForm->fill();
    }

    public function getTitle(): string
    {
        return 'Спорный отзыв №' . $this->getRecord()->id;
    }

    protected function getForms(): array
    {
        return ['ownerForm', 'authorForm'];
    }

    public function ownerForm(Form $form): Form
    {
        return $form->schema($this->messageSchema())->statePath('ownerData');
    }

    public function authorForm(Form $form): Form
    {
        return $form->schema($this->messageSchema())->statePath('authorData');
    }

    private function messageSchema(): array
    {
        return [
            Textarea::make('message')
                ->label('Сообщение')
                ->rows(3)
                ->maxLength(3000),

            FileUpload::make('files')
                ->label('Файлы (необязательно)')
                ->multiple()
                ->maxFiles(5)
                ->maxSize(10240)
                ->disk('public')
                ->directory('review-disputes/' . $this->getRecord()->id)
                ->acceptedFileTypes([
                    'image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'text/plain',
                ])
                // Сохраняем оригинальное имя файла (с коротким префиксом против совпадений)
                ->getUploadedFileNameForStorageUsing(
                    fn (TemporaryUploadedFile $file): string => Str::random(8) . '-' . $file->getClientOriginalName()
                )
                ->downloadable()
                ->openable(),
        ];
    }

    /** Сообщения одного из диалогов: owner | author. */
    public function dialogMessages(string $party)
    {
        return $this->getRecord()
            ->messages()
            ->where('party', $party)
            ->with(['files', 'user'])
            ->get();
    }

    public function sendToOwner(): void
    {
        $this->sendMessage(ReviewDisputeMessage::PARTY_OWNER, $this->ownerForm);
    }

    public function sendToAuthor(): void
    {
        $this->sendMessage(ReviewDisputeMessage::PARTY_AUTHOR, $this->authorForm);
    }

    private function sendMessage(string $party, Form $form): void
    {
        $state = $form->getState();
        $text  = trim((string) ($state['message'] ?? ''));
        $files = array_values(array_filter((array) ($state['files'] ?? [])));

        if ($text === '' && ! $files) {
            Notification::make()->title('Напишите сообщение или приложите файл')->warning()->send();
            return;
        }

        DB::transaction(function () use ($party, $text, $files) {
            $message = ReviewDisputeMessage::create([
                'review_dispute_id' => $this->getRecord()->id,
                'party'             => $party,
                'user_id'           => auth()->id(),
                'is_admin'          => true,
                'message'           => $text !== '' ? $text : null,
                'is_read'           => false,
            ]);

            foreach ($files as $path) {
                $message->files()->create([
                    'path'          => $path,
                    'original_name' => Str::after(basename($path), '-'),
                    'mime'          => Storage::disk('public')->mimeType($path) ?: null,
                    'size'          => Storage::disk('public')->size($path),
                ]);
            }
        });

        $form->fill();

        Notification::make()
            ->title($party === ReviewDisputeMessage::PARTY_OWNER ? 'Сообщение отправлено владельцу' : 'Сообщение отправлено автору отзыва')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('restore')
                ->label('Вернуть отзыв')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Вернуть отзыв на страницу?')
                ->modalDescription('Жалоба владельца будет отклонена, отзыв снова станет виден всем. Обе стороны получат сообщение о решении.')
                ->visible(fn () => $this->getRecord()->isOpen())
                ->action(fn () => $this->resolveDispute(ReviewDispute::STATUS_RESTORED)),

            Action::make('remove')
                ->label('Удалить отзыв')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Удалить отзыв?')
                ->modalDescription('Жалоба владельца будет принята, отзыв останется скрытым навсегда. Обе стороны получат сообщение о решении.')
                ->visible(fn () => $this->getRecord()->isOpen())
                ->action(fn () => $this->resolveDispute(ReviewDispute::STATUS_REMOVED)),
        ];
    }

    /** Решение по спору: вернуть отзыв или оставить скрытым; обеим сторонам уходит сообщение. */
    private function resolveDispute(string $status): void
    {
        $dispute  = $this->getRecord();
        $restored = $status === ReviewDispute::STATUS_RESTORED;

        $texts = $restored
            ? [
                ReviewDisputeMessage::PARTY_OWNER  => 'Обжалование рассмотрено: жалоба отклонена, отзыв возвращён на страницу карточки.',
                ReviewDisputeMessage::PARTY_AUTHOR => 'Ваш отзыв проверен и возвращён на страницу карточки.',
            ]
            : [
                ReviewDisputeMessage::PARTY_OWNER  => 'Обжалование рассмотрено: жалоба принята, отзыв удалён.',
                ReviewDisputeMessage::PARTY_AUTHOR => 'По результатам рассмотрения жалобы ваш отзыв удалён.',
            ];

        DB::transaction(function () use ($dispute, $status, $restored, $texts) {
            $dispute->update([
                'status'      => $status,
                'resolved_at' => now(),
                'resolved_by' => auth()->id(),
            ]);

            // Вернули — отзыв снова виден всем; удалили — остаётся скрытым (disputed_at не трогаем)
            if ($restored) {
                Review::withoutGlobalScopes()->whereKey($dispute->review_id)->update(['disputed_at' => null]);
            }

            foreach ($texts as $party => $text) {
                ReviewDisputeMessage::create([
                    'review_dispute_id' => $dispute->id,
                    'party'             => $party,
                    'user_id'           => auth()->id(),
                    'is_admin'          => true,
                    'message'           => $text,
                    'is_read'           => false,
                ]);
            }
        });

        $this->record = $this->resolveRecord($dispute->getKey());

        Notification::make()
            ->title($restored ? 'Отзыв возвращён на страницу' : 'Отзыв удалён')
            ->success()
            ->send();
    }
}
