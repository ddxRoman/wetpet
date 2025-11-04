<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'country', 'region', 'city', 'street', 'house', 'address_comment',
        'logo', 'description', 'services', 'doctors',
        'phone1', 'phone2', 'email', 'telegram', 'whatsapp',
        'schedule', 'workdays'
    ];

    protected $casts = [
        'services' => 'array',
        'doctors' => 'array',
    ];

    public function getFullAddressAttribute()
    {
        return "{$this->country}, {$this->region}, {$this->city}, {$this->street} {$this->house}";
    }
}
