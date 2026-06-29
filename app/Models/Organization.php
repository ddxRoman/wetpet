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
    // Используем фильтрацию, чтобы не было лишних дефисов при пустых полях
    $source = collect([
        $organization->name,
        $organization->city,
        $organization->street,
        $organization->house
    ])->filter()->implode('-');

    $originalSlug = \Illuminate\Support\Str::slug($source);
    $slug = $originalSlug;
    $count = 1;

    while (static::where('slug', $slug)->where('id', '!=', $organization->id)->exists()) {
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

// Исправь сам метод:
public function fieldOfActivity(): BelongsTo
{
    // Важно: проверь, как называется колонка в базе. 
    // Если в таблице организаций колонка называется 'type', то пиши 'type'.
    return $this->belongsTo(FieldOfActivity::class, 'type'); 
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