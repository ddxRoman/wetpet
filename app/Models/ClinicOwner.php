<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ClinicOwner extends Model
{
    protected $table = 'clinic_owners';

    protected $fillable = [
        'user_id',
        'clinic_id',
        'is_confirmed',
        'is_rejected',
        'rejected_at',
        'admin_comment',
    ];

    protected $casts = [
        'is_confirmed' => 'boolean',
        'is_rejected'  => 'boolean',
        'rejected_at'  => 'datetime',
    ];

    /**
     * Может ли пользователь подать повторную заявку.
     * Для специалистов/врачей — только через 7 дней после отказа.
     */
    public function canReapply(): bool
    {
        if (!$this->is_rejected) {
            return false;
        }
        if (!$this->rejected_at) {
            return true;
        }
        return Carbon::now()->diffInDays($this->rejected_at) >= 7;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function documents()
    {
        return $this->morphMany(OwnershipDocument::class, 'ownerable');
    }

    public function deleteFile(): void {}
}
