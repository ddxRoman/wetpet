<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldOfActivity extends Model
{
    protected $fillable = ['name', 'type', 'activity '];
    
public function doctors()
{
    return $this->hasMany(Doctor::class, 'field_of_activity', 'id');
}
public function organizations()
{
    // У одного типа деятельности может быть много организаций
    return $this->hasMany(Organization::class, 'field_of_activity_id');
}

}
