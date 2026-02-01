<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialistContact extends Model
{
    use HasFactory;
    protected $fillable = ['specialist_id', 'phone', 'email', 'telegram', 'whatsapp', 'max'];
}
