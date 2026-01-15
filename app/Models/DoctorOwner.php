<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorOwner extends Model
{
    protected $table = 'doctor_owners';

    protected $fillable = [
        'user_id',
        'doctor_id',
        'is_confirmed',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
