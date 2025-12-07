<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Specialist extends Model
{
    protected $fillable = [
        'name',
        'specialization',
        'date_of_birth',
        'city_id',
        'clinic_id',
        'experience',
        'exotic_animals',
        'On_site_assistance',
        'photo',
        'description'
    ];
}
