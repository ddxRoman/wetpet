<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    // имя таблицы (по умолчанию Laravel сам подставит "doctors", можно не указывать)
    protected $table = 'doctors';

    // какие поля можно массово заполнять (для create(), update())
    protected $fillable = [
        'name',
        'photo',
        'description',
        'specialization',
        'experience',
        'photo',
    ];
public function services()
{
    return $this->belongsToMany(Service::class, 'doctor_service', 'doctor_id', 'service_id');
}



public function reviews()
{
    return $this->morphMany(Review::class, 'reviewable');
}
public function city()
{
    return $this->belongsTo(City::class);
}

public function clinic()
{
    return $this->belongsTo(\App\Models\Clinic::class);
}
public function reviewable()
{
    return $this->morphTo();
}




}
