<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'nickname',
        'email',
        'password',
        'phone',
        'avatar',
        'city_id',
        'birth_date',
        'status',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    /* ================= Filament ================= */

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin === true;
    }

    /* ================= ТВОЙ КОД ================= */

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
        return $this->hasMany(\App\Models\Pet::class, 'user_id');
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }
}
