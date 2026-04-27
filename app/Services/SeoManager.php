<?php

namespace App\Services;

use App\Models\SeoStatic;
use Illuminate\Support\Facades\Route;

class SeoManager
{
    public function getMeta($model = null)
    {
        // 1. Если передана модель (Доктор, Клиника, Порода) - высший приоритет
        if ($model && (isset($model->seo_title) || isset($model->seo_description))) {
            return [
                'title' => $model->seo_title ?: $model->name ?? $model->breed,
                'description' => $model->seo_description ?: mb_substr(strip_tags($model->short_description ?? ''), 0, 160),
            ];
        }

        // 2. Ищем в статических страницах по имени роута или URL
        $routeName = Route::currentRouteName();
        $path = '/' . request()->path();

        $static = SeoStatic::where('route_name', $routeName)
            ->orWhere('url_path', $path)
            ->first();

        if ($static) {
            return [
                'title' => $static->title,
                'description' => $static->description,
            ];
        }

        // 3. Совсем дефолт, если ничего не нашли
        return [
            'title' => 'Зверозор — сайт про домашних животных',
            'description' => 'Честный рейтинг ветеринарных клиник и врачей.',
        ];
    }
}