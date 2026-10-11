<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetPhoto extends Model
{
    protected $fillable = [
        'pet_id',
        'path',
        'sort_order',
    ];

    protected $appends = ['url'];

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}
