<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reviewable_id',
        'reviewable_type',
        'review_date',
        'rating',
        'content',
        'liked',
        'disliked',
        'receipt_path',
        'receipt_verified',
        'pet_id',
    ];

    protected $casts = [
        'review_date' => 'date',
        // ❌ убираем boolean, потому что это ENUM
    ];

    // Связь с пользователем
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Полиморфная связь
    public function reviewable()
    {
        return $this->morphTo();
    }

    // Фото, прикреплённые к отзыву
    public function photos()
    {
        return $this->hasMany(ReviewPhoto::class);
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }

    public function receipts()
    {
            return $this->hasMany(ReviewReceipt::class, 'review_id');
    }
public function clinic()
{
    return $this->belongsTo(\App\Models\Clinic::class, 'reviewable_id');
}

}
