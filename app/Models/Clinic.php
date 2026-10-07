<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Concerns\HasGalleryPhotos;
use App\Models\Concerns\HasLocalityScopes;


class Clinic extends Model
{
    use HasFactory;
    use HasGalleryPhotos, HasLocalityScopes;

    protected $fillable = [
        'is_verified',
        'created_by',
        'chain_id',
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
        'max',
        'website',
        'schedule',
        'workdays',
        'seo_title', 
    'seo_description'
    ];

    /* ===============================
       AUTO SLUG GENERATION
    =============================== */
    protected static function booted()
    {
        static::creating(function ($clinic) {
            if (empty($clinic->slug)) {
                $clinic->slug = static::generateUniqueSlug(
                    static::buildSlugSource($clinic->name, $clinic->city, $clinic->street, $clinic->house)
                );
            }
        });
    }

    /**
     * Собирает исходную строку для слага из названия и адреса
     * (город, улица, дом) — чтобы слаги разных клиник с похожим
     * названием не совпадали и не плодили числовые суффиксы.
     */
    public static function buildSlugSource(?string $name, ?string $city = null, ?string $street = null, ?string $house = null): string
    {
        return trim(implode(' ', array_filter([$name, $city, $street, $house])));
    }

    public static function generateUniqueSlug(string $source): string
    {
        $slug = Str::slug($source);
        $original = $slug;
        $i = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i++;
        }

        return $slug;
    }

    /**
     * Слаг города для сегмента маршрута /clinics/{city}/{slug}.
     */
    public function getCitySlugAttribute(): string
    {
        // Никогда не возвращаем пустую строку: это сегмент обязательного параметра
        // маршрута {city}, и route() бросает UrlGenerationException, если итоговый
        // URI остаётся с незаполненным {city} (а пустая строка после Str::slug()
        // от city=null/'' — это и есть фактически "не заполнено").
        $slug = Str::slug($this->city ?? '');

        return $slug !== '' ? $slug : 'goroda-net';
    }

    protected $casts = [
        'is_verified' => 'boolean',
    ];

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
    // Важно: второй параметр 'priceable' должен совпадать с тем, что в миграции
    return $this->morphMany(\App\Models\Price::class, 'priceable');
}

public function reviews()
{
    return $this->morphMany(Review::class, 'reviewable');
}


    public function getRouteKeyName(): string
    {
        return 'slug';
    }


public function owners()
{
    return $this->belongsToMany(
        User::class,
        'clinic_owners',
        'clinic_id', // FK клиники
        'user_id'    // FK пользователя
    )
    ->withPivot('is_confirmed')
    ->withTimestamps();
}


public function awards()
{
    return $this->hasMany(Award::class);
}

    /**
     * Все врачи, которые работают в этой клинике (сводная таблица clinic_doctor —
     * см. App\Models\Concerns\HasWorkplaces у Doctor). Врач может работать в
     * нескольких клиниках одновременно, поэтому это belongsToMany, а не hasMany
     * по doctors.clinic_id — та колонка хранит только «основное» место работы.
     */
    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'clinic_doctor', 'clinic_id', 'doctor_id')
            ->withTimestamps();
    }


    /**
     * Сеть филиалов, в которую входит запись (null — не входит).
     */
    public function chain()
    {
        return $this->belongsTo(\App\Models\Chain::class);
    }

    /**
     * Специалисты (не врачи), которые работают в этой клинике
     * (сводная таблица clinic_specialist).
     */
    public function specialists()
    {
        return $this->belongsToMany(Specialist::class, 'clinic_specialist', 'clinic_id', 'specialist_id')
            ->withTimestamps();
    }

    public function promotions()
    {
        return $this->morphMany(\App\Models\Promotion::class, 'promotable');
    }

        public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

}