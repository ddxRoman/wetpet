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
        $organization->slug = static::generateUniqueSlug($organization);
    });

    static::updating(function ($organization) {
        if ($organization->isDirty(['name', 'city', 'street', 'house'])) {
            $organization->slug = static::generateUniqueSlug($organization);
        }
    });
}

private static function generateUniqueSlug($organization)
{
    $source = collect([
        $organization->name,
        $organization->city,
        $organization->street,
        $organization->house
    ])->filter()->implode('-');

    // Транслитерация кириллицы
    $map = [
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo',
        'ж'=>'zh','з'=>'z','и'=>'i','й'=>'j','к'=>'k','л'=>'l','м'=>'m',
        'н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u',
        'ф'=>'f','х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch',
        'ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
    ];
    $translit = mb_strtolower($source);
    $translit = strtr($translit, $map);

    $originalSlug = \Illuminate\Support\Str::slug($translit);

    // Если slug пустой — берём transliterated первое слово или id
    if (empty($originalSlug)) {
        $originalSlug = 'org-' . ($organization->id ?? time());
    }

    $slug = $originalSlug;
    $count = 1;

    while (static::where('slug', $slug)->where('id', '!=', $organization->id ?? 0)->exists()) {
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
    
public function prices()
{
    // Важно: второй параметр 'priceable' должен совпадать с тем, что в миграции
    return $this->morphMany(\App\Models\Price::class, 'priceable');
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