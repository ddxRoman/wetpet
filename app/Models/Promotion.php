<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'promotable_type',
        'promotable_id',
        'title',
        'description',
        'old_price',
        'new_price',
        'badge',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'old_price'  => 'decimal:2',
        'new_price'  => 'decimal:2',
        'expires_at' => 'date',
        'is_active'  => 'boolean',
    ];

    public function promotable()
    {
        return $this->morphTo();
    }

    /**
     * Только активные и не истёкшие акции
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where(function ($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>=', now()->toDateString());
                     });
    }
}
