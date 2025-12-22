<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['name','slug','region','verified','user_id'];
    // app/Models/City.php
public function users()
{
    return $this->hasMany(User::class);
}


    protected $casts = [
        'large_city' => 'boolean',
    ];


}
