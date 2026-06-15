<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlossaryTerm extends Model
{
    protected $fillable = [
        'term',
        'definition',
        'letter',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Вместо const — статический метод, работает везде без проблем
    public static function categories(): array
    {
        return [
            'general'    => 'Общие термины',
            'anatomy'    => 'Анатомия',
            'diseases'   => 'Болезни и симптомы',
            'procedures' => 'Процедуры и лечение',
            'legal'      => 'Юридические термины',
            'platform'   => 'Термины платформы',
        ];
    }

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

    public function getCategoryLabelAttribute(): string
    {
        return self::categories()[$this->category] ?? '—';
    }
}
