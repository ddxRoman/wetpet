<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerClaimMessage extends Model
{
    protected $fillable = [
        'claimable_type',
        'claimable_id',
        'user_id',
        'is_admin',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'is_read'  => 'boolean',
    ];

    public function claimable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
