<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'photo_path',
    ];

public function photos()
{
    return $this->hasMany(\App\Models\ReviewPhoto::class);
}
    public function review()
    {
        return $this->belongsTo(Review::class);
    }

}
