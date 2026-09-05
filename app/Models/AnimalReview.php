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
 * pet_age хранится в годах (с точностью до 1 знака после запятой).
 * Если получившееся количество месяцев <= 12 — выводим в месяцах,
 * иначе — в годах с 1 знаком после запятой.
 */
public function getFormattedAgeAttribute(): ?string
{
    if ($this->pet_age === null) {
        return null;
    }

    $years = (float) $this->pet_age;
    $months = round($years * 12);

    if ($months <= 12) {
        return (int) $months . ' мес.';
    }

    return number_format($years, 1, '.', ' ') . ' г.';
}
}