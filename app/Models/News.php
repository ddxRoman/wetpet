<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';

    protected $fillable = [
        'title',
        'slug',
        'image',
        'images',
        'excerpt',
        'content',
        'is_published',
        'views',
        'region_id',
        // SEO поля
        'seo_title',
        'seo_description',
        'og_image',
    ];

    protected $casts = [
        'images'       => 'array',
        'is_published' => 'boolean',
    ];

    // ──────────────────────────────────────────
    // SEO-хелперы — вся логика фолбэков здесь,
    // контроллер просто вызывает методы
    // ──────────────────────────────────────────

    /**
     * SEO-заголовок страницы.
     * Приоритет: seo_title → title + бренд
     */
    public function getSeoTitleAttribute(): string
    {
        if (!empty($this->attributes['seo_title'])) {
            return $this->attributes['seo_title'];
        }
        return $this->title . ' — Зверозор';
    }

    /**
     * SEO-описание страницы.
     * Приоритет: seo_description → excerpt → первые 160 символов content
     */
    public function getSeoDescriptionAttribute(): string
    {
        if (!empty($this->attributes['seo_description'])) {
            return $this->attributes['seo_description'];
        }
        if (!empty($this->excerpt)) {
            return mb_substr(strip_tags($this->excerpt), 0, 160);
        }
        return mb_substr(strip_tags($this->content), 0, 160) . '…';
    }

    /**
     * OG-картинка для соцсетей.
     * Приоритет: og_image → image → дефолтная картинка сайта
     */
    public function getOgImageAttribute(): string
    {
        if (!empty($this->attributes['og_image'])) {
            return asset('storage/' . $this->attributes['og_image']);
        }
        if (!empty($this->image)) {
            return asset('storage/' . $this->image);
        }
        return asset('images/default-news-share.webp');
    }

    /**
     * Canonical URL новости.
     */
    public function getCanonicalUrlAttribute(): string
    {
        return route('news.show', $this->slug);
    }

    /**
     * JSON-LD Schema.org для статьи (для Google).
     */
    public function getSchemaJsonAttribute(): string
    {
        return json_encode([
            '@context'         => 'https://schema.org',
            '@type'            => 'NewsArticle',
            'headline'         => $this->title,
            'description'      => $this->seo_description,
            'image'            => [$this->og_image],
            'datePublished'    => $this->created_at?->toIso8601String(),
            'dateModified'     => $this->updated_at?->toIso8601String(),
            'author'           => [
                '@type' => 'Organization',
                'name'  => 'Зверозор',
                'url'   => config('app.url'),
            ],
            'publisher'        => [
                '@type' => 'Organization',
                'name'  => 'Зверозор',
                'logo'  => [
                    '@type' => 'ImageObject',
                    'url'   => asset('images/logo.png'),
                ],
            ],
            'mainEntityOfPage' => [
                '@type' => '@WebPage',
                '@id'   => $this->canonical_url,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Готовый массив $seoMeta для передачи в шаблон.
     * Использование в контроллере: compact('article', 'seoMeta')
     */
    public function toSeoMeta(): array
    {
        return [
            'title'                   => $this->seo_title,
            'description'             => $this->seo_description,
            'canonical'               => $this->canonical_url,
            'robots'                  => 'index, follow',
            'og_type'                 => 'article',
            'og_title'                => $this->seo_title,
            'og_description'          => $this->seo_description,
            'image'                   => $this->og_image,
            'og_article_published_at' => $this->created_at?->toIso8601String(),
            'og_article_modified_at'  => $this->updated_at?->toIso8601String(),
            'schema'                  => $this->schema_json,
        ];
    }

    // ── Scopes ──────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
