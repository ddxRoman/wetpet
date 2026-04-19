<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Specialist extends Model
{
protected $fillable = [
    'name', 
    'specialization', 
    'city_id', 
    'organization_id', 
    'street',
    'house',
    'experience', 
    'description', 
    'slug',
    'photo',
    'date_of_birth',
    'exotic_animals',
    'On_site_assistance',
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

public function reviews()
{
    // Укажите правильный класс модели отзывов и внешний ключ, если он отличается
    return $this->hasMany(Review::class); 
}

/**
 * Определяет поле для поиска модели в маршрутах.
 */
public function getRouteKeyName()
{
    return 'slug';
}

}
