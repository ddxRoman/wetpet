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
    ];
}
