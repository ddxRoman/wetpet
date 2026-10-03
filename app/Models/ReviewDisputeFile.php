<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ReviewDisputeFile extends Model
{
    protected $fillable = ['review_dispute_message_id', 'path', 'original_name', 'mime', 'size'];

    public function message()
    {
        return $this->belongsTo(ReviewDisputeMessage::class, 'review_dispute_message_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }
}
