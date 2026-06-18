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

    protected $table = 'doctors';

    protected $fillable = [
        'name', 'slug', 'specialization', 'date_of_birth', 'city_id',
        'clinic_id', 'experience', 'exotic_animals',
        'On_site_assistance', 'photo', 'description', 'seo_title',
        'seo_description'
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
        // БЕЗОПАСНОЕ ПОЛУЧЕНИЕ НАЗВАНИЙ (без падения из-за ленивой загрузки)
        $cityName = '';
        if ($doctor->city_id) {
            $cityName = $doctor->relationLoaded('city') 
                ? $doctor->city?->name 
                : \App\Models\City::find($doctor->city_id)?->name;
        }

        $clinicName = '';
        if ($doctor->clinic_id) {
            $clinicName = $doctor->relationLoaded('clinic') 
                ? $doctor->clinic?->name 
                : \App\Models\Clinic::find($doctor->clinic_id)?->name;
        }

        $base = implode(' ', array_filter([
            $doctor->name,
            $cityName,
            $clinicName,
        ]));

        $slug     = Str::slug($base);
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

    // ── связи ────────────────────────────────────────────────

    public function services()
    {
        return $this->belongsToMany(Service::class, 'doctor_service', 'doctor_id', 'service_id');
    }

    public function contacts()
    {
        return $this->hasOne(DoctorContact::class, 'doctor_id');
    }

    public function prices()
    {
        return $this->morphMany(Price::class, 'priceable');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owners()
    {
        return $this->belongsToMany(
            User::class,
            'doctor_owners',
            'doctor_id',
            'user_id'
        )
        ->withPivot('is_confirmed')
        ->withTimestamps();
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

    // УДАЛЕНО: public function reviewable() { return $this->morphTo(); }
    // Он тут не нужен, так как Doctor — это и есть целевой объект для Review (reviewable_type)

    public function awards()
    {
        return $this->hasMany(Award::class);
    }
}