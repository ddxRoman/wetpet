<?php

namespace App\Http\Controllers;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\News;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
public function __construct()
{
    $this->middleware('auth')->except(['index']);
}


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */


public function index()
{
    // Название города — для заголовка (с запасным вариантом для отображения)
    $currentCityNameRaw = session('city_name');
    $currentCityName    = $currentCityNameRaw ?: 'Выберите город';
    $cityId             = session('city_id');

    // У Doctor/Specialist город хранится как city_id, у Clinic/Organization — как строка city
    $reviewableTypes = [
        \App\Models\Doctor::class       => ['column' => 'city_id', 'value' => $cityId],
        \App\Models\Specialist::class   => ['column' => 'city_id', 'value' => $cityId],
        \App\Models\Clinic::class       => ['column' => 'city',    'value' => $currentCityNameRaw],
        \App\Models\Organization::class => ['column' => 'city',    'value' => $currentCityNameRaw],
    ];

    // Собираем рейтинг/кол-во отзывов по каждому типу отдельно, с фильтром по городу
    $stats = collect();

    foreach ($reviewableTypes as $modelClass => $cityFilter) {
        $table = (new $modelClass())->getTable();

        $query = Review::query()
            ->join($table, "{$table}.id", '=', 'reviews.reviewable_id')
            ->where('reviews.reviewable_type', $modelClass)
            ->whereNotNull('reviews.rating')
            ->groupBy('reviews.reviewable_id')
            ->select(
                'reviews.reviewable_id',
                DB::raw('AVG(reviews.rating) as avg_rating'),
                DB::raw('COUNT(*) as reviews_count')
            );

        if (!empty($cityFilter['value'])) {
            $query->where("{$table}.{$cityFilter['column']}", $cityFilter['value']);
        }

        $rows = $query->get()->each(function ($row) use ($modelClass) {
            $row->reviewable_type = $modelClass;
        });

        $stats = $stats->merge($rows);
    }

    // Минимум 5 отзывов — обязательное условие в любом случае (и для основного отбора, и для fallback)
    $withEnoughReviews = $stats->filter(function ($row) {
        return $row->reviews_count >= 5;
    });

    // Кандидаты с рейтингом 4.7–5
    $highRated = $withEnoughReviews->filter(function ($row) {
        return $row->avg_rating >= 4.7 && $row->avg_rating <= 5;
    });

    if ($highRated->count() >= 5) {
        // Достаточно записей — берём 5 случайных из них
        $chosen = $highRated->shuffle()->take(5);
    } else {
        // Записей с рейтингом 4.7–5 не хватает — дополняем самыми рейтинговыми
        // (но по-прежнему только среди записей с минимум 5 отзывами).
        // Сначала перемешиваем, а затем сортируем по рейтингу — так записи
        // с одинаковым рейтингом каждый раз идут в случайном порядке,
        // и подборка меняется при каждой перезагрузке страницы.
        $chosen = $withEnoughReviews->shuffle()->sortByDesc('avg_rating')->values()->take(5);
    }

    // Подгружаем сами модели (Doctor, Clinic, ...)
    $topItems = $chosen->map(function ($row) {
        $model = $row->reviewable_type::find($row->reviewable_id);

        if (!$model) {
            return null;
        }

        $model->avg_rating = round($row->avg_rating, 1);
        $model->reviews_count = $row->reviews_count;
        $model->reviewable_type = class_basename($row->reviewable_type);

        return $model;
    })->filter()->values();

    // Загружаем 3 последние опубликованные новости для блока на главной
    $news = News::where('is_published', true)
        ->orderBy('created_at', 'desc')
        ->take(3)
        ->get();

    // Добавляем 'news' и 'currentCityName' (если она нужна в шаблоне) в compact
    return view('welcome', compact('topItems', 'news', 'currentCityName'));
}


}