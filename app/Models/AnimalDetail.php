<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnimalDetail extends Model
{
    protected $fillable = [
        'animal_breed',
        'weight_range', 
        'height_range', 
        'lifespan', 
        'type',
        'photo', 
        'short_description', 
        'full_description', 
        'features',
        'seo_title', 
    'seo_description'
    ];

    protected $casts = [
        'features' => 'array', // Это критично для Filament
    ];

// В модели AnimalDetail
public function animal()
{
    return $this->belongsTo(Animal::class, 'animal_breed');
}

protected static function booted()
{
    // Страховка: если карточку породы удалили в обход админских действий (другой экран, tinker и т. п.),
    // вместе с ней удаляем и саму породу из animals. Но только если на неё ничего не ссылается:
    // питомцы и отзывы удалились бы каскадом, а объявления заблокировали бы удаление.
    // В админке удаление с переносом записей на другую породу делает AnimalDetailResource.
    static::deleted(function (AnimalDetail $detail) {
        $animal = Animal::find($detail->animal_breed);

        if (! $animal) {
            return;
        }

        $hasDependents = \App\Models\Pet::where('animal_id', $animal->id)->exists()
            || \App\Models\Ad::where('animal_id', $animal->id)->exists()
            || \App\Models\AnimalReview::where('animal_id', $animal->id)->exists();

        if (! $hasDependents) {
            $animal->delete();
        }
    });
}
}