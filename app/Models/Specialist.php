<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Concerns\HasPracticeExperience;

class Specialist extends Model
{
use HasPracticeExperience;

protected $fillable = [
        'is_verified',
        'created_by',
    'name', 
    'specialization', 
    'city_id', 
    'organization_id', 
    'street',
    'house',
    'practice_started_at',
    'description', 
    'slug',
    'photo',
    'date_of_birth',
    'exotic_animals',
    'On_site_assistance',
    'seo_title', 
    'seo_description',
];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    public function owners()
{
    return $this->belongsToMany(
        User::class,
        'specialist_owners',
        'specialist_id',
        'user_id'
    )
    ->withPivot('is_confirmed')
    ->withTimestamps();
}

public function organization()
    {
        // Предполагается, что в таблице specialists есть поле organization_id
        return $this->belongsTo(Organization::class, 'organization_id');
    }

// Внутри класса Specialist
public function city()
{
    return $this->belongsTo(City::class);
}

public function contacts()
{
    // Убедись, что связь с контактами тоже прописана
    return $this->hasOne(SpecialistContact::class);
}

// Множественные специализации (справочник field_of_activities)
public function specializations()
{
    return $this->belongsToMany(
        \App\Models\FieldOfActivity::class,
        'specialist_field_of_activity',
        'specialist_id',
        'field_of_activity_id'
    )->withTimestamps();
}

// Читаемый список специализаций через запятую.
// Старая строковая колонка 'specialization' синхронизируется в
// SpecialistResource\Pages\CreateSpecialist/EditSpecialist, чтобы поиск,
// подбор услуг, SEO-шаблоны и уведомления продолжали работать без изменений.
public function getSpecializationLabelAttribute(): string
{
    if ($this->relationLoaded('specializations') || $this->exists) {
        $names = $this->specializations->pluck('name');
        if ($names->isNotEmpty()) {
            return $names->implode(', ');
        }
    }

    return $this->specialization ?? '';
}



/**
 * Slug из ФИО + организации (если нет — из адреса частной практики).
 * При совпадении добавляет числовой суффикс.
 */
public static function generateSlug(?string $name, $organizationId = null, ?string $street = null, ?string $house = null, ?int $ignoreId = null): string
{
    $suffix = $organizationId
        ? Organization::whereKey($organizationId)->value('name')
        : trim(($street ?? '') . ' ' . ($house ?? ''));

    $base = Str::slug(trim(($name ?? '') . ' ' . ($suffix ?? '')), '-', 'ru');

    if ($base === '') {
        return '';
    }

    $slug = $base;
    $i = 2;
    while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
        $slug = $base . '-' . $i++;
    }

    return $slug;
}

protected static function boot()
{
    parent::boot();

    static::saving(function ($specialist) {
        if (empty($specialist->slug)) {
            $specialist->slug = Str::slug($specialist->name);
        }
    });
}

public function prices()
{
    return $this->morphMany(Price::class, 'priceable');
}

// В модели Specialist.php (и в Doctor.php)
public function reviews()
{
    return $this->morphMany(Review::class, 'reviewable');
}

/**
 * Определяет поле для поиска модели в маршрутах.
 */
public function getRouteKeyName()
{
    return 'slug';
}


    public function promotions()
    {
        return $this->morphMany(\App\Models\Promotion::class, 'promotable');
    }

        public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
