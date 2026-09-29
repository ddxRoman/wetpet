<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldOfActivity extends Model
{
    protected $fillable = ['name', 'type', 'activity'];

    /**
     * Значения поля `activity`, при которых сфера деятельности организации
     * считается «Ветеринарная клиника» (такие карточки живут в таблице clinics
     * и в разделе «Клиники»). В сидере это 'doctor', в остальном коде — 'vetclinic'.
     */
    public const VET_CLINIC_ACTIVITIES = ['vetclinic', 'doctor'];

    /**
     * Название сферы деятельности «Ветеринарная клиника».
     */
    public const VET_CLINIC_NAME = 'Ветеринарная клиника';

    /**
     * Эта сфера деятельности означает «Ветеринарная клиника»?
     */
    public function isVetClinic(): bool
    {
        return $this->type === 'organization'
            && (
                in_array($this->activity, self::VET_CLINIC_ACTIVITIES, true)
                || mb_strtolower(trim((string) $this->name)) === mb_strtolower(self::VET_CLINIC_NAME)
            );
    }

    /**
     * Названия специализаций людей (врачей/специалистов), связанных с направлением
     * организации по колонке `activity`: у «Груминг салон» activity = grooming и у
     * «Грумер» activity = grooming; у «Ветеринарная клиника» и всех врачей activity = doctor.
     */
    public static function specialistNamesForActivity(?string $activity): array
    {
        if ($activity === null || $activity === '') {
            return [];
        }

        return static::where('type', 'specialist')
            ->where('activity', $activity)
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    /**
     * Сфера «Ветеринарная клиника» из справочника (для предвыбора в форме клиники).
     */
    public static function vetClinic(): ?self
    {
        return static::where('type', 'organization')
            ->where(function ($q) {
                $q->whereIn('activity', self::VET_CLINIC_ACTIVITIES)
                  ->orWhere('name', self::VET_CLINIC_NAME);
            })
            ->orderByRaw('name = ? DESC', [self::VET_CLINIC_NAME])
            ->first();
    }
    
public function doctors()
{
    return $this->hasMany(Doctor::class, 'field_of_activity', 'id');
}
public function organizations()
{
    // У одного типа деятельности может быть много организаций
    return $this->hasMany(Organization::class, 'field_of_activity_id');
}

// Корректная связь "многие ко многим" с врачами через пивот-таблицу
// (используется, например, для подсчёта, сколько врачей выбрали это направление).
public function doctorsPivot()
{
    return $this->belongsToMany(Doctor::class, 'doctor_field_of_activity', 'field_of_activity_id', 'doctor_id');
}

// Аналогичная связь "многие ко многим" со специалистами (не врачами) через пивот-таблицу.
public function specialistsPivot()
{
    return $this->belongsToMany(\App\Models\Specialist::class, 'specialist_field_of_activity', 'field_of_activity_id', 'specialist_id');
}

}