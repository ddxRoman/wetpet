<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Animal extends Model
{
    use HasFactory;

    protected $fillable = ['species', 'breed'];

    public function pets()
    {
        return $this->hasMany(Pet::class);
    }

// В модели Animal
public function details()
{
    // Второй параметр — это имя колонки в таблице animal_details
    return $this->hasOne(AnimalDetail::class, 'animal_breed');
}

public function reviews()
{
    return $this->hasMany(AnimalReview::class);
}

protected static function booted()
{
    static::creating(function ($animal) {
        // Словарь для красивых слагов категорий
        $speciesMap = [
            'Собака' => 'dog',
            'Кошка'  => 'cat',
            'Птица'  => 'bird',
            'Грызун' => 'rodent',
            'Муравей' => 'ant',
            'Бабочка' => 'butterfly',
            'Волк' => 'wolf',
            'Варан' => 'varan',
            'Обезьяна' => 'monkey',
            'Рыбка' => 'fish',
            'Лисица' => 'fox',
            'Норка' => 'mink',
            'Енот' => 'raccoon',
            'Насекомое' => 'insect',
            'Змея' => 'snake',
            'Ящерица' => 'lizard',
            'Суслик' => 'gopher',
            'Паук' => 'spider',
            'Минипиг' => 'minipig',
            'Лошадь' => 'horse',
            'Кролик' => 'rabbit',
            'Черепаха' => 'turtle',
            'Попугай' => 'parrot',
        ];

        // 1. Обработка вида (species)
        if (!$animal->species_slug) {
            // Если вид есть в словаре — берем перевод, иначе — обычный транслит
            $name = $animal->species;
            $animal->species_slug = $speciesMap[$name] ?? Str::slug($name);
        }

        // 2. Обработка породы (breed)
        if (!$animal->breed_slug) {
            $animal->breed_slug = Str::slug($animal->breed);
        }
    });
}
}