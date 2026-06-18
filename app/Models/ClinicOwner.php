<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicOwner extends Model
{
    protected $table = 'clinic_owners';

    protected $fillable = [
        'clinic_id',
        'user_id',
        'is_confirmed',
        'admin_comment',
    ];

    protected $casts = [
        'is_confirmed' => 'boolean',
    ];

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
}
