<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;



class Doctor extends Model
{
    use HasFactory;
        use Notifiable;

    // имя таблицы (по умолчанию Laravel сам подставит "doctors", можно не указывать)
    protected $table = 'doctors';

    // какие поля можно массово заполнять (для create(), update())

    protected $fillable = [
        'name', 'specialization', 'date_of_birth', 'city_id',
        'clinic_id', 'experience', 'exotic_animals',
        'On_site_assistance', 'photo', 'description'
    ];

public function services()
{
    return $this->belongsToMany(Service::class, 'doctor_service', 'doctor_id', 'service_id');
}

public function contacts()
{
    return $this->hasOne(DoctorContact::class);
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
public function awards()
{
    return $this->hasMany(Award::class);
}







}
