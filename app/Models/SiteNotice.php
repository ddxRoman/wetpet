<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Уведомление на сайте: модальное окно поверх страницы (потерялся питомец, эпидемия, новый закон…).
 * Управляется из админки Filament → «Контент» → «Уведомления».
 */
class SiteNotice extends Model
{
    /** Часовой пояс, в котором админ задаёт период и время суток (в базе даты хранятся в UTC). */
    public const TIMEZONE = 'Europe/Moscow';

    public const FREQUENCIES = [
        'once'    => 'Один раз каждому посетителю',
        'session' => 'Один раз за визит (пока открыт браузер)',
        'hours'   => 'Раз в N часов',
        'days'    => 'Раз в N дней',
        'always'  => 'При каждой загрузке страницы',
    ];

    public const SCOPES = [
        'all'    => 'Всем посетителям',
        'auth'   => 'Только зарегистрированным',
        'guests' => 'Только гостям (не вошедшим)',
    ];

    protected $fillable = [
        'title', 'body', 'image', 'is_active', 'priority',
        'starts_at', 'ends_at', 'daily_from', 'daily_until',
        'frequency_type', 'frequency_value',
        'user_scope', 'species', 'regions', 'city_ids',
        'close_delay',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'priority'        => 'integer',
        'starts_at'       => 'datetime',
        'ends_at'         => 'datetime',
        'frequency_value' => 'integer',
        'close_delay'     => 'integer',
        'species'         => 'array',
        'regions'         => 'array',
        'city_ids'        => 'array',
    ];

    protected static function booted(): void
    {
        // Пустые списки храним как NULL, а значения очищаем от пустых строк
        static::saving(function (SiteNotice $notice) {
            foreach (['species', 'regions', 'city_ids'] as $field) {
                $values = array_values(array_filter((array) $notice->{$field}, fn ($v) => $v !== null && $v !== ''));
                $notice->{$field} = $values ?: null;
            }

            if (! in_array($notice->frequency_type, ['hours', 'days'], true)) {
                $notice->frequency_value = null;
            }
        });
    }

    public function views()
    {
        return $this->hasMany(SiteNoticeView::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    /** Данные для браузера. */
    public function toPayload(bool $preview = false): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'body'        => $this->body,
            'image_url'   => $this->image_url,
            'close_delay' => (int) $this->close_delay,
            'frequency'   => $this->frequency_type,
            'preview'     => $preview,
        ];
    }

    /* ---------- статус и подписи для админки ---------- */

    /** disabled | scheduled | expired | active */
    public function getStatusAttribute(): string
    {
        if (! $this->is_active) {
            return 'disabled';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'scheduled';
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'expired';
        }

        return 'active';
    }

    public static function statusLabel(string $status): string
    {
        return [
            'disabled'  => 'Выключено',
            'scheduled' => 'Ожидает начала',
            'expired'   => 'Завершено',
            'active'    => 'Показывается',
        ][$status] ?? $status;
    }

    public function frequencyLabel(): string
    {
        return match ($this->frequency_type) {
            'hours' => 'Раз в ' . (int) $this->frequency_value . ' ч.',
            'days'  => 'Раз в ' . (int) $this->frequency_value . ' дн.',
            default => self::FREQUENCIES[$this->frequency_type] ?? (string) $this->frequency_type,
        };
    }

    public function scheduleLabel(): string
    {
        $parts = [];
        $tz = self::TIMEZONE;

        if ($this->starts_at || $this->ends_at) {
            $parts[] = ($this->starts_at ? 'с ' . $this->starts_at->copy()->timezone($tz)->format('d.m.Y H:i') : '')
                . ($this->starts_at && $this->ends_at ? ' ' : '')
                . ($this->ends_at ? 'до ' . $this->ends_at->copy()->timezone($tz)->format('d.m.Y H:i') : '');
        }

        if ($this->daily_from || $this->daily_until) {
            $parts[] = 'ежедневно ' . ($this->daily_from ? 'с ' . substr($this->daily_from, 0, 5) : '')
                . ($this->daily_from && $this->daily_until ? ' ' : '')
                . ($this->daily_until ? 'до ' . substr($this->daily_until, 0, 5) : '');
        }

        return $parts ? implode('; ', $parts) : 'Без ограничений по времени';
    }

    public function audienceLabel(): string
    {
        $parts = [self::SCOPES[$this->user_scope] ?? 'Всем'];

        if ($this->species) {
            $parts[] = 'владельцы: ' . implode(', ', $this->species);
        }

        $where = [];
        if ($this->city_ids) {
            $names = City::whereIn('id', $this->city_ids)->pluck('name')->all();
            $where[] = 'города: ' . Str::limit(implode(', ', $names), 60);
        }
        if ($this->regions) {
            $where[] = 'регионы: ' . Str::limit(implode(', ', $this->regions), 60);
        }
        if ($where) {
            $parts[] = implode('; ', $where);
        }

        return implode(' · ', $parts);
    }
}
