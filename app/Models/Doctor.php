<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;


class Doctor extends Model
{
    use HasFactory;
        use Notifiable;

    // имя таблицы (по умолчанию Laravel сам подставит "doctors", можно не указывать)
    protected $table = 'doctors';

    // какие поля можно массово заполнять (для create(), update())

    protected $fillable = [
        'name', 'slug','specialization', 'date_of_birth', 'city_id',
        'clinic_id', 'experience', 'exotic_animals',
        'On_site_assistance', 'photo', 'description'
    ];

    protected static function booted()
    {
        static::creating(function ($doctor) {
            $doctor->slug = self::makeSlug($doctor);
        });

        static::updating(function ($doctor) {
            if ($doctor->isDirty(['name', 'city_id', 'clinic_id'])) {
                $doctor->slug = self::makeSlug($doctor, $doctor->id);
            }
        });
    }

    protected static function makeSlug($doctor, $ignoreId = null)
    {
        $city = optional($doctor->city)->name;
        $clinic = optional($doctor->clinic)->name;

        $base = implode(' ', array_filter([
            $doctor->name,
            $city,
            $clinic,
        ]));

        $slug = Str::slug($base);
        $original = $slug;
        $i = 1;

        while (
            self::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $original . '-' . $i++;
        }

        return $slug;
    }

    // связи
public function services()
{
    return $this->belongsToMany(Service::class, 'doctor_service', 'doctor_id', 'service_id');
}

public function contacts()
{
    return $this->hasOne(DoctorContact::class);
}


    public function getRouteKeyName(): string
    {
        return 'slug';
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
