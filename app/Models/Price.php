<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;

protected $fillable = ['service_id', 'price', 'currency', 'priceable_id', 'priceable_type'];

    // Обратная связь к услуге (чтобы взять название)
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Связь с владельцем цены (клиника/врач)
    public function priceable()
    {
        return $this->morphTo();
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function organization()
{
    return $this->belongsTo(Organization::class);
}

}
