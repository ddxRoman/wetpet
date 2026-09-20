<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    protected $fillable = [
        'user_id',
        'animal_id',
        'name',
        'breed',
        'birth_date',
        'death_date',
        'gender',
        'age',
        'photo',
        'seo_title', 
    'seo_description'
    ];

    // Формат Y-m-d важен: pets.js кладёт значение в <input type="date">
    protected $casts = [
        'death_date' => 'date:Y-m-d',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function animal()
    {
            return $this->belongsTo(Animal::class, 'animal_id');
    }
}
