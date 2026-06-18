<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OwnershipDocument extends Model
{
    protected $table = 'ownership_documents';

    protected $fillable = [
        'ownerable_type',
        'ownerable_id',
        'path',
        'original_name',
        'comment',
    ];

    public function ownerable()
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
