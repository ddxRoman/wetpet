<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Specialist extends Model
{
    protected $fillable = [
        'name',
        'specialization',
        'date_of_birth',
        'city_id',
        'organization_id',
        'experience',
        'exotic_animals',
        'On_site_assistance',
        'photo',
        'description',
        'field_of_activity_id',
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

public function contacts()
{
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

/**
 * Определяет поле для поиска модели в маршрутах.
 */
public function getRouteKeyName()
{
    return 'slug';
}

}
