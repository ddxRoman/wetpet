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
        'gender',
        'age',
        'photo',
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
