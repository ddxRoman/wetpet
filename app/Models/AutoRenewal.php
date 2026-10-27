<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoRenewal extends Model
{
    protected $fillable = [
        'user_id', 'scope_key', 'kind', 'entity_type', 'entity_id', 'package', 'plan',
        'payment_method_id', 'payment_method_title', 'status', 'attempts', 'last_attempt_at', 'canceled_at',
    ];

    protected $casts = [
        'last_attempt_at' => 'datetime',
        'canceled_at'     => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && filled($this->payment_method_id);
    }

    public static function premiumKey(int $userId): string
    {
        return "premium:{$userId}";
    }

    public static function boostKey(string $type, int $id, string $package): string
    {
        return "boost:{$type}:{$id}:{$package}";
    }

    /** Активное автопродление Премиума пользователя (или null). */
    public static function activePremium(int $userId): ?self
    {
        return static::where('scope_key', self::premiumKey($userId))->where('status', 'active')->first();
    }

    /** Любая запись (в т.ч. отключённая) — чтобы показать пользователю, почему автопродление выключилось. */
    public static function forPremium(int $userId): ?self
    {
        return static::where('scope_key', self::premiumKey($userId))->first();
    }

    public function planLabel(): string
    {
        return $this->kind === 'premium'
            ? config("premium.plans.{$this->plan}.label", $this->plan)
            : config("boost.periods.{$this->plan}.label", $this->plan);
    }

    /** Название для писем и уведомлений. */
    public function title(): string
    {
        if ($this->kind === 'premium') {
            return 'Премиум-подписка';
        }

        $name = (new BoostSubscription(['entity_type' => $this->entity_type, 'entity_id' => $this->entity_id]))
            ->entityModel()?->name;

        return trim(config("boost.plans.{$this->package}.title", 'Пакет продвижения') . ($name ? " — {$name}" : ''));
    }
}
