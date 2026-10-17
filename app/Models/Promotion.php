<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'promotable_type',
        'promotable_id',
        'title',
        'description',
        'old_price',
        'new_price',
        'badge',
        'expires_at',
        'is_active',
        'show_on_home',
    ];

    protected $casts = [
        'old_price'  => 'decimal:2',
        'new_price'  => 'decimal:2',
        'expires_at' => 'date',
        'is_active'  => 'boolean',
        'show_on_home' => 'boolean',
    ];

    /** Максимум акций одной карточки, которые показываются на главной. */
    public const HOME_PER_CARD = 3;

    /** Максимум акций в блоке на главной (3 ряда по 5). */
    public const HOME_TOTAL = 15;

    /** Только акции, отмеченные «Выводить на главной». */
    public function scopeForHome($query)
    {
        return $query->where('show_on_home', true);
    }

    public function promotable()
    {
        return $this->morphTo();
    }

    /**
     * Только активные и не истёкшие акции (без проверки пакета).
     * Используется в личном кабинете владельца.
     */
    public function scopeActiveForOwner($query)
    {
        return $query->where('is_active', true)
                     ->where(function ($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>=', now()->toDateString());
                     });
    }

    /**
     * Активные и не истёкшие акции: у клиник, организаций, врачей и специалистов — с активным пакетом «Выделение».
     * Используется на главной странице, в каталогах и на публичных show-страницах.
     *
     * Владелец определяется через поле created_by на самой сущности
     * (Clinic/Organization/Doctor/Specialist), а не через owner-таблицы,
     * так как owner-таблицы используются для верификации "Это я" и не
     * обязательно содержат подтверждённую запись для создателя карточки.
     */
    public function scopeActive($query)
    {
        $today = now()->toDateString();

        return $query
            ->where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', $today);
            })
            ->where(function ($outer) use ($today) {
                // Клиники, организации, врачи и специалисты: акции видны только с активным пакетом «Выделение»
                $outer->whereHasMorph('promotable', [Clinic::class], fn ($q) => $q->hasBoost('featured'))
                    ->orWhereHasMorph('promotable', [Organization::class], fn ($q) => $q->hasBoost('featured'))
                    ->orWhereHasMorph('promotable', [Doctor::class], fn ($q) => $q->hasBoost('featured'))
                    ->orWhereHasMorph('promotable', [Specialist::class], fn ($q) => $q->hasBoost('featured'));
            });
    }
}