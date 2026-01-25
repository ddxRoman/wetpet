<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicOwner extends Model
{
    protected $table = 'clinic_owners';

    protected $fillable = [
        'clinic_id',
        'user_id',
        'is_confirmed',
    ];
}
