<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialistOwner extends Model
{
    protected $table = 'specialist_owners';

    protected $fillable = [
        'user_id',
        'specialist_id',
        'is_confirmed',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function specialist()
    {
        return $this->belongsTo(Specialist::class);
    }
}
