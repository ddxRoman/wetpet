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

    public function details()
{
    return $this->hasOne(AnimalDetail::class);
}

public function reviews()
{
    return $this->hasMany(AnimalReview::class);
}

}
