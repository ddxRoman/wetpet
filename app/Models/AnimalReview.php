<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimalReview extends Model
{
    use HasFactory;

    protected $fillable = ['animal_id', 'user_id', 'pet_name', 'pet_weight', 'pet_age', 'temperament', 'trainability', 'intelligence', 'sociability', 'comment', 'health_issues'];

protected $casts = [
    'health_issues' => 'array'
];

public function user()
{
    return $this->belongsTo(User::class);
}

public function animal()
{
    return $this->belongsTo(Animal::class);
}

/**
 * pet_weight хранится в граммах.
 * Если меньше 1000 г — выводим целым числом граммов,
 * иначе — в кг с 3 знаками после запятой.
 */
public function getFormattedWeightAttribute(): ?string
{
    if ($this->pet_weight === null) {
        return null;
    }

    $grams = (float) $this->pet_weight;

    if ($grams < 1000) {
        return number_format($grams, 0, '.', ' ') . ' г';
    }

    return number_format($grams / 1000, 3, '.', ' ') . ' кг';
}

/**
 * pet_age хранится в годах, шаг 0.5 (полгода): 0, 0.5, 1, 1.5, 2 ...
 * Меньше года — показываем в месяцах, иначе — в годах
 * (целое число лет без ".0", либо с ".5" для половины года).
 */
public function getFormattedAgeAttribute(): ?string
{
    if ($this->pet_age === null) {
        return null;
    }

    $years = (float) $this->pet_age;
    $months = (int) round($years * 12);

    if ($months < 12) {
        return $months . ' мес.';
    }

    if ($months % 12 === 0) {
        return ($months / 12) . ' г.';
    }

    return number_format($years, 1, '.', ' ') . ' г.';
}
}