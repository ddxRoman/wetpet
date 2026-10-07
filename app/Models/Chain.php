<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Сеть филиалов: объединяет клиники и/или организации (clinics.chain_id / organizations.chain_id).
 * Состав может быть смешанным: при смене сферы деятельности карточка переезжает
 * между разделами «Клиники» и «Организации», но остаётся в своей сети.
 */
class Chain extends Model
{
    protected $fillable = ['name'];

    public function clinics()
    {
        return $this->hasMany(Clinic::class);
    }

    public function organizations()
    {
        return $this->hasMany(Organization::class);
    }

    /**
     * Остальные (проверенные) филиалы сети из того же города, что и $except.
     * Возвращает коллекцию массивов [name, address, type, url].
     */
    public function branchesInCity(Model $except): Collection
    {
        $cityKey = mb_strtolower(trim((string) $except->city));

        if ($cityKey === '') {
            return collect();
        }

        $address = fn ($item) => trim(trim((string) $item->street) . ' ' . trim((string) $item->house));

        $clinics = $this->clinics()
            ->where('is_verified', true)
            ->whereRaw('LOWER(TRIM(city)) = ?', [$cityKey])
            ->when($except instanceof Clinic, fn ($q) => $q->whereKeyNot($except->getKey()))
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => [
                'name'    => $c->name,
                'address' => $address($c),
                'type'    => 'Клиника',
                'url'     => route('clinics.show', ['city' => $c->city_slug, 'clinic' => $c->slug]),
            ]);

        $organizations = $this->organizations()
            ->where('is_verified', true)
            ->whereRaw('LOWER(TRIM(city)) = ?', [$cityKey])
            ->when($except instanceof Organization, fn ($q) => $q->whereKeyNot($except->getKey()))
            ->orderBy('name')
            ->get()
            ->map(fn ($o) => [
                'name'    => $o->name,
                'address' => $address($o),
                'type'    => 'Организация',
                'url'     => route('organizations.show', ['city' => $o->city_slug, 'slug' => $o->slug]),
            ]);

        return $clinics->concat($organizations)->values();
    }
}
