<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'breed', 'birth_date', 'age', 'color', 'photo'];

    public function owners()
    {
        return $this->belongsToMany(User::class);
    }
}
