<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Organization extends Model
{
    protected $fillable = [
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
        'website',
        'schedule',
        'workdays',
        'type'
    ];

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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($organization) {
            // Формируем строку: Название-Город-Улица-Дом
            $sourceString = sprintf(
                '%s-%s-%s-%s',
                $organization->name,
                $organization->city,
                $organization->street,
                $organization->house
            );

            $slug = Str::slug($sourceString);

            // Проверяем, существует ли уже такой слаг
            // Если есть, добавляем счетчик (напр. wrg-astrakhan-40-let-1, wrg-astrakhan-40-let-2)
            $originalSlug = $slug;
            $count = 1;

            while (static::where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-{$count}";
                $count++;
            }

            $organization->slug = $slug;
        });
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

}