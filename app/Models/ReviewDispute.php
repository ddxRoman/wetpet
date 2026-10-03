<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReviewDispute extends Model
{
    public const STATUS_OPEN     = 'open';       // отзыв скрыт, идёт разбирательство
    public const STATUS_RESTORED = 'restored';   // жалоба отклонена — отзыв возвращён на страницу
    public const STATUS_REMOVED  = 'removed';    // жалоба принята — отзыв остаётся скрытым

    public const STATUS_LABELS = [
        self::STATUS_OPEN     => 'На рассмотрении',
        self::STATUS_RESTORED => 'Отзыв возвращён',
        self::STATUS_REMOVED  => 'Отзыв удалён',
    ];

    protected $fillable = ['review_id', 'opened_by', 'status', 'reason', 'resolved_at', 'resolved_by'];

    protected $casts = ['resolved_at' => 'datetime'];

    /** Отзыв скрыт глобальным скоупом, поэтому здесь его нужно загружать без скоупов. */
    public function review()
    {
        return $this->belongsTo(Review::class)->withoutGlobalScopes();
    }

    /** Владелец карточки, оспоривший отзыв. */
    public function opener()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function messages()
    {
        return $this->hasMany(ReviewDisputeMessage::class)->orderBy('created_at')->orderBy('id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    /** Автор оспариваемого отзыва. */
    public function author(): ?User
    {
        return $this->review?->user;
    }

    /** Роль пользователя в споре: owner | author | null. */
    public function partyOf(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        if ((int) $this->opened_by === (int) $user->id) {
            return ReviewDisputeMessage::PARTY_OWNER;
        }

        if ((int) ($this->review?->user_id) === (int) $user->id) {
            return ReviewDisputeMessage::PARTY_AUTHOR;
        }

        return null;
    }

    /** Споры, в которых пользователь участвует (как владелец или как автор отзыва). */
    public function scopeInvolving(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $q) use ($userId) {
            $q->where('opened_by', $userId)
              ->orWhereIn('review_id', Review::withoutGlobalScopes()->where('user_id', $userId)->select('id'));
        });
    }

    /** Непрочитанные пользователем сообщения администратора в его диалоге. */
    public static function unreadForUser(int $userId): int
    {
        return ReviewDisputeMessage::query()
            ->where('is_admin', true)
            ->where('is_read', false)
            ->whereHas('dispute', function (Builder $d) use ($userId) {
                $d->involving($userId);
            })
            ->where(function (Builder $m) use ($userId) {
                $m->where(function (Builder $o) use ($userId) {
                    $o->where('party', ReviewDisputeMessage::PARTY_OWNER)
                      ->whereHas('dispute', fn (Builder $d) => $d->where('opened_by', $userId));
                })->orWhere(function (Builder $a) use ($userId) {
                    $a->where('party', ReviewDisputeMessage::PARTY_AUTHOR)
                      ->whereHas('dispute', fn (Builder $d) => $d->whereIn(
                          'review_id',
                          Review::withoutGlobalScopes()->where('user_id', $userId)->select('id')
                      ));
                });
            })
            ->count();
    }

    /**
     * Сводка для шапки: показывать ли пункт «Отзывы на рассмотрении» и сколько новых ответов админа.
     * Любая ошибка (например, не выполнена миграция) не должна ронять страницу.
     *
     * @return array{show: bool, unread: int}
     */
    public static function headerSummary(int $userId): array
    {
        try {
            return [
                'show'   => static::involving($userId)->exists(),
                'unread' => static::unreadForUser($userId),
            ];
        } catch (\Throwable $e) {
            return ['show' => false, 'unread' => 0];
        }
    }
}
