<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Редактируемое SEO для каталожных страниц (/doctors, /clinics, /specialists,
 * /organizations, /ads) и их фильтров. Управляется через Filament:
 * "SEO Каталожных страниц".
 */
class SeoCatalogPage extends Model
{
    protected $fillable = [
        'key',
        'label',
        'title',
        'description',
    ];

    /**
     * Технические ключи записей — используются в контроллерах, менять нельзя.
     * Значение — список переменных, доступных для подстановки в title/description.
     */
    public const KEYS = [
        'doctors'                     => ['city'],
        'doctors_specialization'      => ['city', 'specialization'],
        'clinics'                     => ['city'],
        'specialists'                 => ['city'],
        'specialists_specialization'  => ['city', 'specialization'],
        'organizations'               => ['city'],
        'organizations_activity'      => ['city', 'activity_type'],
        'ads'                         => ['city'],
    ];

    /**
     * Человекочитаемые названия переменных для подсказок в админке.
     */
    public const VARIABLE_LABELS = [
        'city'           => 'Город пользователя',
        'specialization' => 'Специализация (из фильтра)',
        'activity_type'  => 'Тип деятельности организации (из фильтра)',
    ];

    public function availableVariables(): array
    {
        return self::KEYS[$this->key] ?? [];
    }
}
