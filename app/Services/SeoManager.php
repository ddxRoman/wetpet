<?php

namespace App\Services;

use App\Models\SeoStatic;
use Illuminate\Support\Facades\Route;

class SeoManager
{
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