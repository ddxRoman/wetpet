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
    ];

    protected $casts = [
        'old_price'  => 'decimal:2',
        'new_price'  => 'decimal:2',
        'expires_at' => 'date',
        'is_active'  => 'boolean',
    ];

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
     * Активные и не истёкшие акции у пользователей с активным рекламным пакетом.
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
            ->whereHasMorph('promotable', [
                Clinic::class,
                Organization::class,
                Doctor::class,
                Specialist::class,
            ], function ($q) use ($today) {
                $q->whereHas('creator', function ($u) use ($today) {
                    $u->where('has_promo_package', true)
                      ->where(function ($d) use ($today) {
                          $d->whereNull('promo_package_expires_at')
                            ->orWhere('promo_package_expires_at', '>=', $today);
                      });
                });
            });
    }
}