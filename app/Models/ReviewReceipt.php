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

    /**
     * Отзыв, к которому относится чек
     */
    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * Клиника, к которой относится чек
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}
