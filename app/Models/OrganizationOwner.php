<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationOwner extends Model
{
    protected $table = 'organization_owners';

    protected $fillable = [
        'organization_id',
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

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function documents()
    {
        return $this->morphMany(OwnershipDocument::class, 'ownerable');
    }
}
