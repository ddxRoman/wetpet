<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlossaryTerm extends Model
{
    protected $fillable = [
        'term',
        'definition',
        'letter',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Автоматически заполняем поле letter из первой буквы термина
    protected static function booted(): void
    {
        static::saving(function (GlossaryTerm $model) {
            $model->letter = mb_strtoupper(mb_substr($model->term, 0, 1));
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('letter')->orderBy('term');
    }
}
