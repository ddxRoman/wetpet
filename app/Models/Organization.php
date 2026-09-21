<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Organization extends Model
{
   protected $fillable = [
        'is_verified',
        'created_by',
    'name',
    'slug',
    'country',
    'region',
    'city',
    'street',
    'house',
    'address_comment',
    'logo',
    'description',
    'phone1',
    'phone2',
    'email',
    'telegram',
    'whatsapp',
    'max',
    'website',
    'schedule',
    'workdays',
    'field_of_activity_id', // Убедитесь, что это поле здесь есть
    'seo_title', 
    'seo_description'
];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

protected static function boot()
{
    parent::boot();

    static::creating(function ($organization) {
        $organization->slug = static::generateUniqueSlug(
            static::buildSlugSource($organization->name, $organization->city, $organization->street, $organization->house),
            $organization->id
        );
    });

    static::updating(function ($organization) {
        if ($organization->isDirty(['name', 'city', 'street', 'house'])) {
            $organization->slug = static::generateUniqueSlug(
                static::buildSlugSource($organization->name, $organization->city, $organization->street, $organization->house),
                $organization->id
            );
        }
    });
}

/**
 * Собирает и транслитерирует исходную строку для слага из названия и адреса
 * (город, улица, дом). Используется и при автосохранении, и в реактивной
 * форме админки (см. OrganizationResource).
 */
public static function buildSlugSource(?string $name, ?string $city = null, ?string $street = null, ?string $house = null): string
{
    $source = collect([$name, $city, $street, $house])->filter()->implode('-');

    // Транслитерация кириллицы
    $map = [
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo',
        'ж'=>'zh','з'=>'z','и'=>'i','й'=>'j','к'=>'k','л'=>'l','м'=>'m',
        'н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u',
        'ф'=>'f','х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch',
        'ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
    ];
    $translit = mb_strtolower($source);

    return strtr($translit, $map);
}

/**
 * Слагифицирует источник (см. buildSlugSource) и гарантирует уникальность,
 * при необходимости исключая текущую запись ($ignoreId) из проверки.
 */
public static function generateUniqueSlug(string $source, $ignoreId = null): string
{
    $originalSlug = \Illuminate\Support\Str::slug($source);

    // Если slug пустой — берём id/время как запасной вариант
    if (empty($originalSlug)) {
        $originalSlug = 'org-' . ($ignoreId ?? time());
    }

    $slug = $originalSlug;
    $count = 1;

    while (static::where('slug', $slug)->where('id', '!=', $ignoreId ?? 0)->exists()) {
        $slug = "{$originalSlug}-{$count}";
        $count++;
    }

    return $slug;
}

    public function owners()
    {
        return $this->belongsToMany(
            User::class,
            'organization_owners'
        )->withTimestamps();
    }

public function fieldOfActivity(): BelongsTo
{
    return $this->belongsTo(FieldOfActivity::class, 'field_of_activity_id');
}




    public function activityType()
{
    // Организация принадлежит к одному типу деятельности
    return $this->belongsTo(FieldOfActivity::class, 'field_of_activity_id');
}

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Слаг города для сегмента маршрута /organizations/{city}/{slug}.
     */
    public function getCitySlugAttribute(): string
    {
        return Str::slug($this->city ?? '');
    }
    
public function prices()
{
    // Важно: второй параметр 'priceable' должен совпадать с тем, что в миграции
    return $this->morphMany(\App\Models\Price::class, 'priceable');
}
/**
 * Специалисты, работающие в этой организации (specialists.organization_id)
 */
public function specialists()
{
    return $this->hasMany(Specialist::class, 'organization_id');
}

public function reviews()
{
    // 'reviewable' — это название префикса для полей reviewable_type и reviewable_id
    return $this->morphMany(Review::class, 'reviewable');
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