<?php

namespace App\Models;
use App\Notifications\ResetPasswordNotification;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use HasFactory, Notifiable;

protected $fillable = ['name','email','email_verified_at','password','phone','city_id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    // app/Models/User.php
public function city()
{
    return $this->belongsTo(City::class);
}

public function sendPasswordResetNotification($token)
{
    $this->notify(new ResetPasswordNotification($token));
}

public function pets()
{
    return $this->belongsToMany(Pet::class);
}


}