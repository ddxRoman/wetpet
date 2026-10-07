<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * Несколько мест работы у врача (клиники) или специалиста (организации).
 *
 * Полный список хранится в сводной таблице (связь workplaces()), а старая колонка
 * (doctors.clinic_id / specialists.organization_id) остаётся «основным местом
 * работы»: от неё зависят slug, поиск и старые формы.
 *
 * Модель обязана реализовать:
 *   - workplaces(): BelongsToMany  — связь на клиники/организации;
 *   - workplaceColumn(): string    — имя колонки основного места работы.
 */
trait HasWorkplaces
{
    /** Отключает автосинхронизацию сводной таблицы (нужно самому syncWorkplaces). */
    public bool $skipWorkplaceSync = false;

    /** Основное место работы до сохранения (для старых форм с одним селектом). */
    public ?int $previousPrimaryWorkplace = null;

    abstract public function workplaces(): BelongsToMany;

    abstract public static function workplaceColumn(): string;

    public static function bootHasWorkplaces(): void
    {
        static::updating(function ($model) {
            $column = static::workplaceColumn();
            $old    = $model->getOriginal($column);

            $model->previousPrimaryWorkplace = ($model->isDirty($column) && $old) ? (int) $old : null;
        });

        // Новая запись с основным местом работы — сразу заносим его в сводную таблицу.
        static::created(function ($model) {
            $column = static::workplaceColumn();

            if ($model->{$column}) {
                $model->workplaces()->syncWithoutDetaching([(int) $model->{$column}]);
            }
        });

        // Старый код (модалки «Добавить врача/специалиста», старые формы) пишет только
        // основную колонку — держим сводную таблицу в согласии с ней.
        static::saved(function ($model) {
            if ($model->skipWorkplaceSync) {
                return;
            }

            $column = static::workplaceColumn();

            if (! $model->wasChanged($column)) {
                return;
            }

            $new = $model->{$column} ? (int) $model->{$column} : null;
            $old = $model->previousPrimaryWorkplace;

            if ($old && $old !== $new) {
                $model->workplaces()->detach($old);
            }

            if ($new) {
                $model->workplaces()->syncWithoutDetaching([$new]);
                return;
            }

            // Основное место убрали, но остались другие — назначаем основным первое из них.
            $related = $model->workplaces()->getRelated();
            $firstId = $model->workplaces()->orderBy($related->getQualifiedKeyName())->value($related->getQualifiedKeyName());

            if ($firstId) {
                $model->skipWorkplaceSync = true;
                $model->forceFill([$column => $firstId])->saveQuietly();
                $model->skipWorkplaceSync = false;
            }
        });
    }

    /**
     * Все места работы — и клиники, и организации (места работы перекрёстные).
     * Каждый элемент: ['key' => 'clinic:5' | 'organization:7', 'type' => класс модели, 'id', 'name', 'city'].
     * Основное место (старая колонка clinic_id / organization_id) добавляется, если его нет в сводных таблицах.
     */
    public function allWorkplaces(): Collection
    {
        $clinics = $this->clinics()->orderBy('clinics.name')->get();
        $organizations = $this->organizations()->orderBy('organizations.name')->get();

        $primaryClinicId = $this->getAttributes()['clinic_id'] ?? null;
        if ($primaryClinicId && ! $clinics->contains('id', (int) $primaryClinicId)) {
            $primary = \App\Models\Clinic::find($primaryClinicId);
            if ($primary) {
                $clinics->push($primary);
            }
        }

        $primaryOrgId = $this->getAttributes()['organization_id'] ?? null;
        if ($primaryOrgId && ! $organizations->contains('id', (int) $primaryOrgId)) {
            $primary = \App\Models\Organization::find($primaryOrgId);
            if ($primary) {
                $organizations->push($primary);
            }
        }

        $map = fn (string $kind, string $type) => fn ($m) => [
            'key'  => $kind . ':' . $m->id,
            'type' => $type,
            'id'   => (int) $m->id,
            'name' => $m->name,
            'city' => $m->city,
        ];

        return $clinics->map($map('clinic', \App\Models\Clinic::class))
            ->concat($organizations->map($map('organization', \App\Models\Organization::class)))
            ->values();
    }

    /**
     * Полностью заменяет список мест работы. Основное место остаётся прежним,
     * если оно есть в новом списке (чтобы не менялся slug), иначе — первое из списка.
     */
    public function syncWorkplaces(array $ids): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        $column = static::workplaceColumn();

        $this->workplaces()->sync($ids);

        $current = $this->{$column} ? (int) $this->{$column} : null;
        $primary = in_array($current, $ids, true) ? $current : ($ids[0] ?? null);

        if ($primary !== $current) {
            $this->skipWorkplaceSync = true;
            $this->{$column} = $primary;
            $this->save();
            $this->skipWorkplaceSync = false;
        }
    }

    /**
     * Убирает клинику/организацию из мест работы всех врачей/специалистов
     * (вызывается при удалении или переносе карточки).
     */
    public static function releaseWorkplace(int $workplaceId): void
    {
        $column = static::workplaceColumn();

        static::query()
            ->where($column, $workplaceId)
            ->orWhereHas('workplaces', fn ($q) => $q->whereKey($workplaceId))
            ->get()
            ->each(function ($model) use ($workplaceId) {
                $related = $model->workplaces()->getRelated();
                $keep = $model->workplaces()
                    ->pluck($related->getQualifiedKeyName())
                    ->map(fn ($id) => (int) $id)
                    ->reject(fn ($id) => $id === $workplaceId)
                    ->all();

                $model->syncWorkplaces($keep);
            });
    }
}
