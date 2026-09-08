<?php

namespace App\Services;

use App\Models\SeoStatic;
use App\Models\SeoCatalogPage;
use Illuminate\Support\Facades\Route;

class SeoManager
{
    /**
     * Дефолтные значения переменных, когда для них нет данных
     * (например, у пользователя не выбран город).
     */
    private const VARIABLE_FALLBACKS = [
        'city'           => 'вашем городе',
        'specialization' => 'ветеринарных специалистов',
        'activity_type'  => 'организации',
    ];

    /**
     * SEO для каталожных страниц (/doctors, /clinics, /specialists,
     * /organizations, /ads) и их фильтров. Шаблон берётся из БД
     * (редактируется в Filament: "SEO Каталожных страниц"), переменные
     * вида {city}, {specialization}, {activity_type} подставляются из $vars.
     *
     * @param string $key  Один из ключей App\Models\SeoCatalogPage::KEYS
     * @param array  $vars ['city' => 'Москве', 'specialization' => 'Гастроэнтеролог', ...]
     */
    public function getCatalogMeta(string $key, array $vars = []): array
    {
        $page = SeoCatalogPage::query()->where('key', $key)->first();

        [$title, $description] = $page
            ? [$page->title, $page->description]
            : $this->catalogFallback($key);

        return $this->build(
            $this->applyVariables($title, $vars),
            $this->applyVariables($description, $vars)
        );
    }

    private function applyVariables(string $text, array $vars): string
    {
        foreach (self::VARIABLE_FALLBACKS as $var => $fallback) {
            $value = trim((string) ($vars[$var] ?? ''));
            $text = str_replace('{' . $var . '}', $value !== '' ? $value : $fallback, $text);
        }

        // Убираем двойные пробелы, которые могли образоваться из-за пустых переменных
        return trim(preg_replace('/\s{2,}/', ' ', $text));
    }

    /**
     * Значения на случай, если запись в БД ещё не создана/была удалена.
     */
    private function catalogFallback(string $key): array
    {
        $defaults = [
            'doctors' => [
                'Ветеринарные врачи в {city} — рейтинг и отзывы | Зверозор',
                'Каталог ветеринарных врачей в {city}. Рейтинги, отзывы, специализации и контакты на Зверозор.',
            ],
            'doctors_specialization' => [
                '{specialization} в {city} — рейтинг врачей и отзывы | Зверозор',
                'Ветеринарные врачи по специализации «{specialization}» в {city}. Рейтинги, отзывы, контакты на Зверозор.',
            ],
            'clinics' => [
                'Ветеринарные клиники в {city} — рейтинг и отзывы | Зверозор',
                'Каталог ветеринарных клиник в {city}. Отзывы, рейтинги, услуги и контакты на Зверозор.',
            ],
            'specialists' => [
                'Специалисты по животным в {city} — рейтинг и отзывы | Зверозор',
                'Каталог специалистов по уходу за животными в {city}. Рейтинги, отзывы и контакты на Зверозор.',
            ],
            'specialists_specialization' => [
                '{specialization} в {city} — рейтинг и отзывы | Зверозор',
                'Специалисты «{specialization}» в {city}. Рейтинги, отзывы, контакты на Зверозор.',
            ],
            'organizations' => [
                'Организации для животных в {city} — рейтинг и отзывы | Зверозор',
                'Каталог организаций для животных в {city}. Отзывы, рейтинги и контакты на Зверозор.',
            ],
            'organizations_activity' => [
                '{activity_type} в {city} — рейтинг и отзывы | Зверозор',
                'Организации «{activity_type}» в {city}. Отзывы, рейтинги и контакты на Зверозор.',
            ],
            'ads' => [
                'Объявления о животных в {city} | Зверозор',
                'Объявления о животных в {city}: продажа, передача в добрые руки, вязка. Зверозор.',
            ],
        ];

        return $defaults[$key] ?? ['Зверозор', 'Честный рейтинг ветеринарных клиник, врачей и специалистов.'];
    }

    public function getMeta($model = null): array
    {
        // 1. Модель с явными SEO-полями — высший приоритет
        if ($model && !empty($model->seo_title)) {
            return $this->build(
                $model->seo_title,
                $model->seo_description ?: mb_substr(strip_tags($model->description ?? ''), 0, 160)
            );
        }

        // 2. Авто-генерация title/description по типу модели
        if ($model) {
            return $this->fromModel($model);
        }

        // 3. Статические страницы из БД (по имени роута или URL)
        $routeName = Route::currentRouteName();
        $path = '/' . request()->path();

        $static = SeoStatic::where('route_name', $routeName)
            ->orWhere('url_path', $path)
            ->first();

        if ($static) {
            return $this->build($static->title, $static->description);
        }

        // 4. Дефолт
        return $this->build(
            'Зверозор — сайт про домашних животных',
            'Честный рейтинг ветеринарных клиник, врачей и специалистов рядом с вами.'
        );
    }

    private function fromModel($model): array
    {
        $class = class_basename($model);
        $name  = $model->name ?? $model->breed ?? '';
        $city  = $model->city ?? '';
        $spec  = $model->specialization ?? '';

        switch ($class) {
            case 'Clinic':
                return $this->build(
                    "Ветеринарная клиника «{$name}» — {$city} | Зверозор",
                    "Ветеринарная клиника «{$name}» в {$city}. Расписание, контакты, отзывы пациентов. Запишитесь онлайн на Зверозор."
                );
            case 'Doctor':
                return $this->build(
                    "Ветеринарный врач {$name}" . ($spec ? " — {$spec}" : '') . " | Зверозор",
                    "Ветеринарный врач {$name}" . ($spec ? ", специализация: {$spec}" : '') . ($city ? ", {$city}" : '') . ". Отзывы, контакты, запись на приём."
                );
            case 'Organization':
                return $this->build(
                    "«{$name}»" . ($city ? " — {$city}" : '') . " | Зверозор",
                    "«{$name}»" . ($city ? " в {$city}" : '') . ". Услуги, контакты, отзывы клиентов на Зверозор."
                );
            case 'Specialist':
                return $this->build(
                    "Специалист {$name}" . ($spec ? " — {$spec}" : '') . " | Зверозор",
                    "Специалист {$name}" . ($spec ? ", {$spec}" : '') . ($city ? ", {$city}" : '') . ". Контакты, отзывы, запись на Зверозор."
                );
            case 'Breed':
                $breedName = $model->breed ?? $name;
                return $this->build(
                    "Порода {$breedName} — описание, характер, уход | Зверозор",
                    "Полное описание породы {$breedName}: характер, уход, кормление, болезни. Отзывы владельцев на Зверозор."
                );
            default:
                return $this->build(
                    ($name ? "{$name} | " : '') . 'Зверозор',
                    mb_substr(strip_tags($model->description ?? ''), 0, 160)
                );
        }
    }

    private function build(string $title, string $description = ''): array
    {
        return [
            'title'          => $title,
            'description'    => $description,
            'og_title'       => $title,
            'og_description' => $description,
            'robots'         => 'index, follow',
        ];
    }
}