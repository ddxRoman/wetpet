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

    // Собираем рейтинг/кол-во отзывов по каждому типу отдельно —
    // сразу и с фильтром по городу, и без него (нужно для фолбэка ниже,
    // если в городе не наберётся достаточно записей).
    $statsCity = collect();
    $statsAll  = collect();

    foreach ($reviewableTypes as $modelClass => $cityFilter) {
        $table = (new $modelClass())->getTable();

        $baseQuery = fn () => Review::query()
            ->join($table, "{$table}.id", '=', 'reviews.reviewable_id')
            ->where('reviews.reviewable_type', $modelClass)
            ->whereNotNull('reviews.rating')
            ->groupBy('reviews.reviewable_id')
            ->select(
                'reviews.reviewable_id',
                DB::raw('AVG(reviews.rating) as avg_rating'),
                DB::raw('COUNT(*) as reviews_count')
            );

        // Без фильтра по городу — на случай фолбэка
        $rowsAll = $baseQuery()->get()->each(function ($row) use ($modelClass) {
            $row->reviewable_type = $modelClass;
        });
        $statsAll = $statsAll->merge($rowsAll);

        // С фильтром по городу
        if (!empty($cityFilter['value'])) {
            $rowsCity = $baseQuery()
                ->where("{$table}.{$cityFilter['column']}", $cityFilter['value'])
                ->get()
                ->each(function ($row) use ($modelClass) {
                    $row->reviewable_type = $modelClass;
                });
            $statsCity = $statsCity->merge($rowsCity);
        }
    }

    // Порог, при котором городской подбор считаем "рабочим"
    $minCandidates = 5;

    $cityHasEnough = $statsCity->filter(fn ($row) => $row->reviews_count >= 5)->count() >= $minCandidates;

    // Если в городе достаточно кандидатов — используем городскую статистику,
    // иначе показываем лучших по всей базе, чтобы блок не был пустым.
    $stats = $cityHasEnough ? $statsCity : $statsAll;

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

    // ВРЕМЕННАЯ ОТЛАДКА — удалить после диагностики.
    // Открыть на проде: https://zverozor.ru/?debug_recs=1
    if (request()->has('debug_recs')) {
        dd([
            'city_id'              => $cityId,
            'city_name_raw'        => $currentCityNameRaw,
            'statsAll_total'       => $statsAll->count(),
            'statsAll_qualified'   => $statsAll->filter(fn($r) => $r->reviews_count >= 5)->count(),
            'statsCity_total'      => $statsCity->count(),
            'statsCity_qualified'  => $statsCity->filter(fn($r) => $r->reviews_count >= 5)->count(),
            'cityHasEnough'        => $cityHasEnough,
            'withEnoughReviews'    => $withEnoughReviews->count(),
            'highRated'            => $highRated->count(),
            'chosen_count'         => $chosen->count(),
            'topItems_count'       => $topItems->count(),
            'topItems_sample'      => $topItems->take(2)->map(fn($m) => [
                'type' => $m->reviewable_type,
                'id'   => $m->id,
                'name' => $m->name,
            ]),
        ]);
    }

    // Добавляем 'news' и 'currentCityName' (если она нужна в шаблоне) в compact
    return view('welcome', compact('topItems', 'news', 'currentCityName'));
}


}