<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'country',
        'region',
        'city',
        'street',
        'house',
        'description',
        'logo',
        'schedule',
        'workdays',
        'phone',
        'email',
        'type',
    ];
}
