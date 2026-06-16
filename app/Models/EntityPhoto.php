<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EntityPhoto extends Model
{
    protected $table = 'entity_photos';

    protected $fillable = [
        'photoable_type',
        'photoable_id',
        'path',
        'caption',
        'sort_order',
        'is_main',
    ];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    public function photoable()
    {
        return $this->morphTo();
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    public function deleteFile(): void
    {
        Storage::disk('public')->delete($this->path);
    }
}
