<?php

namespace App\Http\Controllers;

use App\Filament\Resources\ReviewDisputeResource;
use App\Models\Review;
use App\Models\ReviewDispute;
use App\Models\ReviewDisputeMessage;
use App\Models\User;
use App\Services\TelegramService;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Обжалование отзывов.
 *
 *  store   — владелец карточки оспаривает отзыв: отзыв скрывается, спор уходит в админку;
 *  show    — пользователь открывает свой спор: текст отзыва + его диалог с админом;
 *  message — пользователь пишет админу (текст и файлы).
 */
class ReviewDisputeController extends Controller
{
    private const MAX_FILES   = 5;
    private const MAX_FILE_KB = 10240;
    private const MIMES       = 'jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,txt';

    private const TYPE_LABELS = [
        \App\Models\Clinic::class       => 'Клиника',
        \App\Models\Organization::class => 'Организация',
        \App\Models\Doctor::class       => 'Врач',
        \App\Models\Specialist::class   => 'Специалист',
    ];

    /** Владелец оспаривает отзыв. {review} не найдёт уже скрытый отзыв — глобальный скоуп вернёт 404. */
    public function store(Request $request, Review $review): JsonResponse
    {
        $user = $request->user();

        if (! $review->canBeDisputedBy($user)) {
            return response()->json([
                'message' => 'Оспорить этот отзыв нельзя: оспаривать отзывы может только подтверждённый владелец карточки.',
            ], 403);
        }

        $data   = $request->validate(['reason' => 'nullable|string|max:2000']);
        $reason = trim((string) ($data['reason'] ?? ''));

        $dispute = DB::transaction(function () use ($review, $user, $reason) {
            $dispute = ReviewDispute::create([
                'review_id' => $review->id,
                'opened_by' => $user->id,
                'status'    => ReviewDispute::STATUS_OPEN,
                'reason'    => $reason !== '' ? $reason : null,
            ]);

            // Отзыв скрывается сразу, до выяснения обстоятельств
            $review->forceFill(['disputed_at' => now()])->saveQuietly();

            // Причина, указанная владельцем, — первое сообщение в его диалоге с админом
            if ($reason !== '') {
                ReviewDisputeMessage::create([
                    'review_dispute_id' => $dispute->id,
                    'party'             => ReviewDisputeMessage::PARTY_OWNER,
                    'user_id'           => $user->id,
                    'is_admin'          => false,
                    'message'           => $reason,
                    'is_read'           => false,
                ]);
            }

            return $dispute;
        });

        $this->notifyAdmins($dispute, 'Отзыв оспорен',
            "Владелец {$user->name} оспорил отзыв пользователя {$review->user?->name}. Отзыв скрыт до выяснения обстоятельств.");

        $this->notifyTelegram($review, $user);

        return response()->json([
            'ok'      => true,
            'message' => 'Отзыв скрыт до выяснения обстоятельств. Мы свяжемся с вами и с автором отзыва.',
        ]);
    }

    /** Данные для модалки: отзыв сверху и диалог пользователя с админом. */
    public function show(Request $request, ReviewDispute $dispute): JsonResponse
    {
        $user  = $request->user();
        $party = $dispute->partyOf($user);

        abort_unless($party, 403);

        $dispute->load(['review.user', 'review.reviewable', 'review.photos']);
        $review = $dispute->review;

        // Открыл диалог — ответы админа прочитаны
        ReviewDisputeMessage::where('review_dispute_id', $dispute->id)
            ->where('party', $party)
            ->where('is_admin', true)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = $dispute->messages()
            ->where('party', $party)
            ->with(['files', 'user'])
            ->get()
            ->map(fn ($m) => $m->toDialogArray(false))
            ->values();

        return response()->json([
            'id'         => $dispute->id,
            'party'      => $party,
            'status'     => $dispute->status,
            'status_label' => $dispute->status_label,
            'is_open'    => $dispute->isOpen(),
            'review'     => [
                'rating'    => (int) $review?->rating,
                'content'   => $review?->content,
                'liked'     => $review?->liked,
                'disliked'  => $review?->disliked,
                'date'      => $review?->review_date?->format('d.m.Y'),
                'author'    => $review?->user?->name,
                'entity'    => $review?->reviewable?->name,
                'entity_type' => self::TYPE_LABELS[$review?->reviewable_type] ?? '',
                'photos'    => $review?->photos->map(fn ($p) => asset('storage/' . $p->photo_path))->values()->all() ?? [],
            ],
            'messages'   => $messages,
        ]);
    }

    /** Пользователь пишет админу: текст и/или файлы. */
    public function message(Request $request, ReviewDispute $dispute): JsonResponse
    {
        $user  = $request->user();
        $party = $dispute->partyOf($user);

        abort_unless($party, 403);

        if (! $dispute->isOpen()) {
            return response()->json(['message' => 'Обжалование уже закрыто — написать сообщение нельзя.'], 422);
        }

        $request->validate([
            'message'   => 'nullable|string|max:3000',
            'files'     => 'nullable|array|max:' . self::MAX_FILES,
            'files.*'   => 'file|max:' . self::MAX_FILE_KB . '|mimes:' . self::MIMES,
        ], [
            'files.max'      => 'Можно приложить не больше ' . self::MAX_FILES . ' файлов.',
            'files.*.max'    => 'Файл слишком большой — не больше 10 МБ.',
            'files.*.mimes'  => 'Допустимые файлы: фото, PDF, Word, Excel, TXT.',
            'message.max'    => 'Сообщение слишком длинное.',
        ]);

        $text = trim((string) $request->input('message'));

        if ($text === '' && ! $request->hasFile('files')) {
            return response()->json(['message' => 'Напишите сообщение или приложите файл.'], 422);
        }

        $message = DB::transaction(function () use ($request, $dispute, $party, $user, $text) {
            $message = ReviewDisputeMessage::create([
                'review_dispute_id' => $dispute->id,
                'party'             => $party,
                'user_id'           => $user->id,
                'is_admin'          => false,
                'message'           => $text !== '' ? $text : null,
                'is_read'           => false,
            ]);

            foreach ((array) $request->file('files', []) as $file) {
                $message->files()->create([
                    'path'          => $file->store('review-disputes/' . $dispute->id, 'public'),
                    'original_name' => $file->getClientOriginalName(),
                    'mime'          => $file->getMimeType(),
                    'size'          => $file->getSize(),
                ]);
            }

            return $message;
        });

        $role = $party === ReviewDisputeMessage::PARTY_OWNER ? 'владельца карточки' : 'автора отзыва';
        $this->notifyAdmins($dispute, 'Новое сообщение по спорному отзыву', "Сообщение от {$role}: {$user->name}.");

        return response()->json(['message' => $message->load('files')->toDialogArray(false)]);
    }

    /** Колокольчик в Filament всем админам. */
    private function notifyAdmins(ReviewDispute $dispute, string $title, string $body): void
    {
        try {
            $admins = User::where('is_admin', true)->get();

            FilamentNotification::make()
                ->title($title)
                ->body($body)
                ->icon('heroicon-o-scale')
                ->warning()
                ->actions([
                    NotificationAction::make('open')
                        ->label('Открыть')
                        ->url(ReviewDisputeResource::getUrl('view', ['record' => $dispute]))
                        ->button(),
                ])
                ->sendToDatabase($admins);
        } catch (\Throwable $e) {
            Log::warning('Не удалось уведомить админов об обжаловании отзыва: ' . $e->getMessage());
        }
    }

    private function notifyTelegram(Review $review, User $owner): void
    {
        try {
            $e = fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
            $entity = $review->reviewable?->name ?? '—';
            $type   = self::TYPE_LABELS[$review->reviewable_type] ?? '';

            TelegramService::send(
                "⚖️ <b>Отзыв оспорен</b>\n\n" .
                "{$e($type)}: {$e($entity)}\n" .
                "Владелец: {$e($owner->name)}\n" .
                "Автор отзыва: {$e($review->user?->name)}\n" .
                "Оценка: {$e($review->rating)}/5\n\n" .
                "Отзыв скрыт до выяснения обстоятельств."
            );
        } catch (\Throwable $e) {
            Log::warning('Не удалось отправить Telegram-уведомление об обжаловании: ' . $e->getMessage());
        }
    }
}
