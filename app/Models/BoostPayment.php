<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoostPayment extends Model
{
    protected $fillable = [
        'user_id',
        'entity_type',
        'entity_id',
        'package',
        'period',
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

    public function packageLabel(): string
    {
        return config("boost.plans.{$this->package}.title", $this->package);
    }

    public function periodLabel(): string
    {
        return config("boost.periods.{$this->period}.label", $this->period);
    }
}
