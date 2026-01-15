<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
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

    /* ================= 1 к 1 ================= */

    public function doctorOwner()
    {
        return $this->hasOne(\App\Models\DoctorOwner::class);
    }

    public function specialistOwner()
    {
        return $this->hasOne(\App\Models\SpecialistOwner::class);
    }

    /**
     * Есть ли уже связь "я — специалист"
     */
    public function hasSelfSpecialist(): bool
    {
        return $this->doctorOwner()->exists()
            || $this->specialistOwner()->exists();
    }

    /**
     * Можно ли показывать чекбокс "Добавляю себя"
     */
    public function canAddSelfSpecialist(): bool
    {
        return ! $this->hasSelfSpecialist();
    }

    /* ================= Остальное — БЕЗ ИЗМЕНЕНИЙ ================= */

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            if (!Auth::check()) {
                abort(403);
            }
            return $this->is_admin === true;
        }
        return true;
    }

    public function ownedOrganizations()
    {
        return $this->belongsToMany(
            Organization::class,
            'organization_owners'
        )->withTimestamps();
    }

    public function ownedClinics()
    {
        return $this->belongsToMany(Clinic::class, 'clinic_owners')
            ->withPivot('is_confirmed')
            ->withTimestamps();
    }

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
