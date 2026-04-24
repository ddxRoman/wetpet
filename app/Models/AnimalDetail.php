<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnimalDetail extends Model
{
    protected $fillable = [
        'animal_id', 
        'weight_range', 
        'height_range', 
        'lifespan', 
        'photo', 
        'short_description', 
        'full_description', 
        'features'
    ];

    protected $casts = [
        'features' => 'array', // Это критично для Filament
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}