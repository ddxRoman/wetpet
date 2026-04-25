<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

}
