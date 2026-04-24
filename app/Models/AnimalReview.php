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
}
