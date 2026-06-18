<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialistOwner extends Model
{
    protected $table = 'specialist_owners';

    protected $fillable = [
        'user_id',
        'specialist_id',
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

    public function specialist()
    {
        return $this->belongsTo(Specialist::class);
    }

    public function documents()
    {
        return $this->morphMany(OwnershipDocument::class, 'ownerable');
    }
}
