<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GlossaryTerm extends Model
{
    protected $fillable = [
        'term',
        'slug',
        'definition',
        'letter',
        'category',
        'is_active',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const CATEGORIES = [
        'general'    => 'Общие термины',
        'anatomy'    => 'Анатомия',
        'diseases'   => 'Болезни и симптомы',
        'procedures' => 'Процедуры и лечение',
        'legal'      => 'Юридические термины',
        'platform'   => 'Термины платформы',
    ];

    public static function categories(): array
    {
        return self::CATEGORIES;
    }

    protected static function booted(): void
    {
        static::saving(function (GlossaryTerm $model) {
            $model->letter = mb_strtoupper(mb_substr($model->term, 0, 1));

            // Адрес страницы термина: если не задан вручную — формируется из термина
            if (blank($model->slug)) {
                $model->slug = static::makeUniqueSlug($model->term, $model->id);
            }
        });
    }

    public static function makeUniqueSlug(string $term, ?int $ignoreId = null): string
    {
        $base = Str::slug($term) ?: 'termin';
        $slug = $base;
        $i    = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
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
        return self::CATEGORIES[$this->category] ?? '—';
    }
}