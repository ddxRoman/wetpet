<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'clinic_id',
        'path',
        'status',
    ];

public function review()
{
    return $this->belongsTo(Review::class);
}


    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    protected static function booted()
    {
        // Когда чек сохранён или обновлён
        static::saved(function ($receipt) {
            if ($receipt->review) {
                $receipt->review->update([
                    'receipt_verified' => $receipt->status, // ✅ статус из чека в отзыв
                ]);
            }
        });

        // Когда чек удалён
        static::deleted(function ($receipt) {
            if ($receipt->review) {
                $receipt->review->update([
                    'receipt_verified' => 'pending', // 🔁 возвращаем в "ожидание"
                ]);
            }
        });
    }
}
