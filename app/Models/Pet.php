<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    protected $fillable = [
        'user_id',
        'animal_id',
        'name',
        'breed',
        'birth_date',
        'death_date',
        'gender',
        'age',
        'photo',
        'seo_title', 
    'seo_description'
    ];

    // Формат Y-m-d важен: pets.js кладёт значение в <input type="date">
    protected $casts = [
        'death_date' => 'date:Y-m-d',
    ];

    // Чтобы photo_url и age_label всегда приезжали вместе с моделью
    // (в т.ч. в JSON-ответах /pets)
    protected $appends = [
        'photo_url',
        'age_label',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function animal()
    {
            return $this->belongsTo(Animal::class, 'animal_id');
    }

    /**
     * Фото питомца, либо дефолтная картинка по виду животного:
     * кошка -> default-cat.png, собака -> default-dog.png, иначе -> default-pet.jpg
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }

        $slug = $this->animal?->species_slug;

        return match ($slug) {
            'cat' => asset('storage/pets/default-cat.png'),
            'dog' => asset('storage/pets/default-dog.png'),
            default => asset('storage/pets/default-pet.png'),
        };
    }

    /**
     * Возраст питомца в виде «1 год 3 месяца» (на сегодня, либо на момент
     * смерти, если известна дата смерти). Та же логика, что раньше жила
     * только в UserProfileController — вынесена сюда, чтобы работать
     * везде, где используется питомец (в т.ч. в отзывах).
     */
    public function getAgeLabelAttribute(): ?string
    {
        if ($this->birth_date) {
            return \App\Support\AgeFormatter::since($this->birth_date, false, $this->death_date) ?: 'меньше месяца';
        }

        if ($this->age) {
            return $this->age . ' ' . \App\Support\AgeFormatter::plural((int) $this->age, 'год', 'года', 'лет');
        }

        return null;
    }
}
