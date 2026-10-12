<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoostSubscription extends Model
{
    protected $fillable = ['entity_type', 'entity_id', 'package', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function isActive(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isFuture();
    }

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /** Есть ли у карточки активный пакет. */
    public static function activeFor(string $type, int $id, string $package): bool
    {
        return static::where('entity_type', $type)
            ->where('entity_id', $id)
            ->where('package', $package)
            ->active()
            ->exists();
    }

    /** Карточка (клиника или организация), к которой относится подписка. */
    public function entityModel(): ?Model
    {
        return $this->entity_type === 'clinic'
            ? Clinic::find($this->entity_id)
            : Organization::find($this->entity_id);
    }

    public function packageLabel(): string
    {
        return config("boost.plans.{$this->package}.title", $this->package);
    }
}
