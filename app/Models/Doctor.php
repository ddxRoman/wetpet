<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    // имя таблицы (по умолчанию Laravel сам подставит "doctors", можно не указывать)
    protected $table = 'doctors';

    // какие поля можно массово заполнять (для create(), update())
    protected $fillable = [
        'name',
        'photo',
        'description',
    ];
    public function services()
{
    return $this->belongsToMany(Service::class, 'doctor_service');
}


public function reviews()
{
    return $this->morphMany(Review::class, 'reviewable');
}


}
