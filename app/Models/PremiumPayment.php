<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PremiumPayment extends Model
{
    protected $fillable = [
        'user_id',
        'plan',
        'amount',
        'currency',
        'status',
        'gateway',
        'external_id',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function planLabel(): string
    {
        return config("premium.plans.{$this->plan}.label", $this->plan);
    }
}
