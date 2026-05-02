<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ad extends Model
{
    use SoftDeletes;

    // Разрешаем массовое заполнение всех полей для удобства
    protected $guarded = [];
    
    protected $casts = [
        'photos' => 'array',
    ];
    protected $fillable = [
    'title', 'description', 'price', 'price_type', 
    'phone', 'city', 'address', 'condition', 'animal_id', 'photos'
];

    /**
     * Связь с моделью Animal
     * Каждое объявление принадлежит одному типу животного
     */
    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    /**
     * Связь с пользователем (Автор объявления)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Автоматическое приведение JSON из базы к массиву для фото
     */

}