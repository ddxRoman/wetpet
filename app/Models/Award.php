<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    use HasFactory;

    protected $fillable = ['clinic_id', 'title', 'description', 'image'];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
    public function awards()
{
    return $this->hasMany(Award::class);
}

}
