<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'phone',
        'email',
        'telegram',
        'whatsapp',
        'max',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
