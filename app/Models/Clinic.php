<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'country',
        'region',
        'city',
        'street',
        'house',
        'address_comment',
        'logo',
        'description',
        'phone1',
        'phone2',
        'email',
        'telegram',
        'whatsapp',
        'website',
        'schedule',
        'workdays',
    ];

    /* ===============================
       AUTO SLUG GENERATION
    =============================== */
    protected static function booted()
    {
        static::creating(function ($clinic) {
            if (empty($clinic->slug)) {
                $clinic->slug = static::generateUniqueSlug($clinic->name);
            }
        });
    }

    private static function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i++;
        }

        return $slug;
    }

    protected $casts = [];

    /**
     * Полный адрес клиники
     */
    public function getFullAddressAttribute(): string
    {
        return "{$this->country}, {$this->region}, {$this->city}, {$this->street} {$this->house}";
    }

    /**
     * Связь многие-ко-многим с таблицей услуг
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'clinic_service', 'clinic_id', 'service_id');
    }
    public function prices()
{
    return $this->hasMany(Price::class);
}

public function reviews()
{
    return $this->morphMany(Review::class, 'reviewable');
}


    public function getRouteKeyName(): string
    {
        return 'slug';
    }

public function awards()
{
    return $this->hasMany(Award::class);
}

    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }

}
