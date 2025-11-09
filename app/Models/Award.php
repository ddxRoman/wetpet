<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    protected $fillable = ['clinic_id', 'title', 'description', 'image'];

public function clinic()
{
    return $this->belongsTo(Clinic::class);
}

}
