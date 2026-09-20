<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldOfActivity extends Model
{
    protected $fillable = ['name', 'type', 'activity'];
    
public function doctors()
{
    return $this->hasMany(Doctor::class, 'field_of_activity', 'id');
}
public function organizations()
{
    // У одного типа деятельности может быть много организаций
    return $this->hasMany(Organization::class, 'field_of_activity_id');
}

// Корректная связь "многие ко многим" с врачами через пивот-таблицу
// (используется, например, для подсчёта, сколько врачей выбрали это направление).
public function doctorsPivot()
{
    return $this->belongsToMany(Doctor::class, 'doctor_field_of_activity', 'field_of_activity_id', 'doctor_id');
}

// Аналогичная связь "многие ко многим" со специалистами (не врачами) через пивот-таблицу.
public function specialistsPivot()
{
    return $this->belongsToMany(\App\Models\Specialist::class, 'specialist_field_of_activity', 'field_of_activity_id', 'specialist_id');
}

}