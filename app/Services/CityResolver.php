<?php

namespace App\Services;

use App\Models\City;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Поиск и создание городов в таблице cities.
 *
 * Город всегда привязан к региону: два города с одинаковым названием в разных
 * регионах — разные записи (например, «Новоалександровка»). Сравнение названий и
 * регионов — без учёта регистра и лишних пробелов.
 */
class CityResolver
{
    /**
     * Пометка для Telegram-уведомления о новой организации/клинике/враче/специалисте:
     * возвращает текст, только если город был создан прямо сейчас (в этом запросе).
     */
    public static function newCityNote(?City $city): string
    {
        if (! $city || ! $city->wasRecentlyCreated) {
            return '';
        }

        $region = $city->region ? " ({$city->region})" : '';

        return "📍 <b>В НОВОМ НАСЕЛЕННОМ ПУНКТЕ:</b> {$city->name}{$region}\n\n";
    }

    /** Убирает лишние пробелы и делает первую букву заглавной. */
    public function normalizeName(?string $name): string
    {
        $name = trim(preg_replace('/\s+/u', ' ', (string) $name));

        if ($name === '') {
            return '';
        }

        return mb_strtoupper(mb_substr($name, 0, 1)) . mb_substr($name, 1);
    }

    /**
     * Поиск по НЕпрямому вхождению (LIKE %term%): «динск» найдёт «Динская».
     * Сначала идут города, название которых начинается с запроса.
     */
    public function search(string $term, ?string $region = null, int $limit = 10): Collection
    {
        $term = trim($term);

        $query = City::query()->select(['id', 'name', 'region']);

        if ($region !== null && trim($region) !== '') {
            $query->whereRaw('LOWER(TRIM(region)) = ?', [mb_strtolower(trim($region))]);
        }

        if ($term !== '') {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
            $query->where('name', 'LIKE', '%' . $escaped . '%')
                ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', [$escaped . '%']);
        }

        return $query->orderBy('name')->limit($limit)->get();
    }

    /** Точное совпадение названия (и региона, если он указан) без учёта регистра. */
    public function find(string $name, ?string $region = null): ?City
    {
        $name = $this->normalizeName($name);

        if ($name === '') {
            return null;
        }

        return City::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->when($region !== null && trim($region) !== '',
                fn ($q) => $q->whereRaw('LOWER(TRIM(region)) = ?', [mb_strtolower(trim($region))]))
            ->orderBy('id')
            ->first();
    }

    /** Есть ли такой регион в справочнике городов (без учёта регистра). */
    public function regionExists(?string $region): bool
    {
        $region = trim((string) $region);

        return $region !== ''
            && City::whereRaw('LOWER(TRIM(region)) = ?', [mb_strtolower($region)])->exists();
    }

    /** Регион в том написании, в каком он записан в cities (чтобы не плодить дубли). */
    public function canonicalRegion(string $region): string
    {
        $region = trim($region);

        $existing = City::whereRaw('LOWER(TRIM(region)) = ?', [mb_strtolower($region)])
            ->orderBy('id')
            ->value('region');

        return $existing !== null ? trim($existing) : $region;
    }

    /**
     * Находит город по названию в регионе или создаёт новый (large_city = 0).
     *
     * @param string|null $verified  confirmed — создан админом, unconfirmed — пользователем
     */
    public function findOrCreate(string $name, string $region, ?int $userId = null, string $verified = 'unconfirmed'): City
    {
        $name   = $this->normalizeName($name);
        $region = $this->canonicalRegion($region);

        $city = $this->find($name, $region);
        if ($city) {
            return $city;
        }

        $city = City::create([
            'name'       => $name,
            'slug'       => $this->uniqueSlug($name, $region),
            'region'     => $region,
            'country'    => 'Россия',
            'large_city' => false,
            'verified'   => $verified,
            'user_id'    => $userId,
        ]);

        // Отдельное Telegram-уведомление о городе не шлём: пометка «В НОВОМ НАСЕЛЕННОМ ПУНКТЕ»
        // добавляется в уведомление о самой записи (см. newCityNote). Админам в Filament о новом
        // непроверенном городе сообщает модель City (бейдж + колокольчик).

        return $city;
    }

    /**
     * Для сохранения клиники/организации, где город и регион хранятся текстом:
     * гарантирует, что такой город есть в cities. Ничего не создаёт, если региона
     * нет в справочнике (чтобы опечатка в регионе не породила новый «регион»).
     */
    public function ensureExists(?string $name, ?string $region): ?City
    {
        $name   = $this->normalizeName($name);
        $region = trim((string) $region);

        if ($name === '' || $region === '') {
            return null;
        }

        $existing = $this->find($name, $region);
        if ($existing) {
            return $existing;
        }

        if (! $this->regionExists($region)) {
            Log::info('CityResolver: регион не найден в cities, город не создан', compact('name', 'region'));
            return null;
        }

        // Город, добавленный админом, считается проверенным; остальные — на модерацию
        $verified = auth()->user()?->isAdmin() ? 'confirmed' : 'unconfirmed';

        return $this->findOrCreate($name, $region, auth()->id(), $verified);
    }

    /**
     * Город из запроса: выбранный из списка (city_id) или введённый вручную (city_name + region).
     * Если название введено вручную — оно главнее выбора в списке; такой город ищется в базе
     * и создаётся, только если его там нет.
     *
     * @throws ValidationException если не указано ни то ни другое
     */
    public function fromRequest($request, string $idKey = 'city_id', string $nameKey = 'city_name', string $regionKey = 'region'): City
    {
        $typed = $this->normalizeName($request->input($nameKey));

        if ($typed !== '') {
            $region = trim((string) $request->input($regionKey));

            if ($region === '' || ! $this->regionExists($region)) {
                throw ValidationException::withMessages([
                    $regionKey => 'Выберите регион, в котором находится город.',
                ]);
            }

            $verified = auth()->user()?->isAdmin() ? 'confirmed' : 'unconfirmed';

            return $this->findOrCreate($typed, $region, auth()->id(), $verified);
        }

        $city = $request->filled($idKey) ? City::find($request->input($idKey)) : null;

        if (! $city) {
            throw ValidationException::withMessages([
                $idKey => 'Выберите город или введите его название.',
            ]);
        }

        return $city;
    }

    private function uniqueSlug(string $name, string $region): string
    {
        $base = Str::slug($name) ?: 'city';

        // Одинаковое название в разных регионах: добавляем регион к slug
        $candidates = [$base, $base . '-' . (Str::slug($region) ?: 'region')];

        foreach ($candidates as $candidate) {
            if (! City::where('slug', $candidate)->exists()) {
                return $candidate;
            }
        }

        $i = 2;
        do {
            $candidate = $candidates[1] . '-' . $i++;
        } while (City::where('slug', $candidate)->exists());

        return $candidate;
    }
}
