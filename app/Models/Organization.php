<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'country',
        'region',
        'city',
        'street',
        'house',
        'type',
        'description',
        'logo',
        'schedule',
        'workdays',
        'phone',
        'email',
        'slug',
    ];

    public function owners()
{
    return $this->belongsToMany(
        User::class,
        'organization_owners'
    )->withTimestamps();
}
protected static function boot()
{
    parent::boot();

    static::creating(function ($organization) {
        if (empty($organization->slug)) {
            $organization->slug = \Illuminate\Support\Str::slug($organization->name);
        }
    });
}
public function getRouteKeyName()
{
    return 'slug';
}
}
