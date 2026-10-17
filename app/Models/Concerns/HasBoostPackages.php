<?php

namespace App\Models\Concerns;

use App\Models\BoostSubscription;
use Illuminate\Database\Eloquent\Builder;

/**
 * Пакеты продвижения карточки (Clinic / Organization / Doctor / Specialist).
 *
 *  - $card->hasBoostPackage('manage')  — доступны ли функции пакета этой карточке;
 *  - Clinic::query()->hasBoost('top')            — только карточки с активным пакетом;
 *  - Clinic::query()->boostedFirst('top')        — карточки с активным пакетом идут первыми;
 *  - Clinic::query()->withBoost('top')           — добавляет в выборку флаг boost_top (0/1).
 *
 * Пакеты: manage (Управление), top (Продвижение), featured (Выделение).
 */
trait HasBoostPackages
{
    public function boostType(): string
    {
        return match (true) {
            $this instanceof \App\Models\Clinic     => 'clinic',
            $this instanceof \App\Models\Doctor     => 'doctor',
            $this instanceof \App\Models\Specialist => 'specialist',
            default                                   => 'organization',
        };
    }

    public function boostSubscriptions()
    {
        return BoostSubscription::where('entity_type', $this->boostType())
            ->where('entity_id', $this->getKey());
    }

    public function hasBoostPackage(string $package): bool
    {
        return BoostSubscription::activeFor($this->boostType(), (int) $this->getKey(), $package);
    }

    /** SQL-условие «у карточки активный пакет» (без привязки к конкретному запросу). */
    private function boostExistsSql(): string
    {
        $table = $this->getTable();

        return "EXISTS (SELECT 1 FROM boost_subscriptions bs"
            . " WHERE bs.entity_type = ? AND bs.entity_id = {$table}.id"
            . " AND bs.package = ? AND bs.expires_at > ?)";
    }

    private function boostBindings(string $package): array
    {
        return [$this->boostType(), $package, now()->toDateTimeString()];
    }

    /** Только карточки с активным пакетом. */
    public function scopeHasBoost(Builder $query, string $package): Builder
    {
        return $query->whereRaw($this->boostExistsSql(), $this->boostBindings($package));
    }

    /** Карточки с активным пакетом — выше остальных (порядок между собой не меняется). */
    public function scopeBoostedFirst(Builder $query, string $package = 'top'): Builder
    {
        return $query->orderByRaw($this->boostExistsSql() . ' DESC', $this->boostBindings($package));
    }

    /** Добавляет в выборку поле boost_{package} = 1|0. */
    public function scopeWithBoost(Builder $query, string $package = 'top'): Builder
    {
        if (empty($query->getQuery()->columns)) {
            $query->select($this->getTable() . '.*');
        }

        return $query->selectRaw(
            '(' . $this->boostExistsSql() . ') as boost_' . $package,
            $this->boostBindings($package)
        );
    }
}
