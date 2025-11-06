<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'specialization'];

public function doctors()
{
    return $this->belongsToMany(Doctor::class, 'doctor_service');
}
 public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'clinic_service', 'service_id', 'clinic_id');
    }
public function prices()
{
    return $this->hasMany(Price::class);
}

}
