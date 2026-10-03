<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Log;

/**
 * Карточку (клинику, организацию, врача, специалиста) создаёт пользователь, и при создании он
 * записывается её владельцем с is_confirmed = false. Когда админ верифицирует саму карточку
 * (is_verified: false → true), право создателя на неё тоже считается подтверждённым —
 * иначе у него остаётся плашка «На проверке», хотя карточка уже проверена.
 *
 * Модель обязана реализовать ownerConfirmationTarget(): [класс записи владельца, колонка с id карточки].
 * Чужие заявки («Это я» от других пользователей) не затрагиваются — их подтверждает админ отдельно.
 */
trait ConfirmsCreatorOwnership
{
    abstract protected static function ownerConfirmationTarget(): array;

    public static function bootConfirmsCreatorOwnership(): void
    {
        static::updated(function ($model) {
            if (! $model->wasChanged('is_verified') || ! $model->is_verified || ! $model->created_by) {
                return;
            }

            $model->confirmCreatorOwnership();
        });
    }

    public function confirmCreatorOwnership(): void
    {
        try {
            [$ownerClass, $column] = static::ownerConfirmationTarget();

            $ownerClass::where($column, $this->getKey())
                ->where('user_id', $this->created_by)
                ->where('is_confirmed', false)
                ->where(fn ($q) => $q->where('is_rejected', false)->orWhereNull('is_rejected'))
                ->update(['is_confirmed' => true]);
        } catch (\Throwable $e) {
            Log::warning('Не удалось подтвердить владельца верифицированной карточки: ' . $e->getMessage());
        }
    }
}
