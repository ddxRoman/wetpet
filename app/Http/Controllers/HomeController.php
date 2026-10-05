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
    // Город: выбранный на сайте (при выборе он же сохраняется в профиль пользователя),
    // а если в сессии пусто — город из профиля. Рекомендации строятся ТОЛЬКО по нему.
    $cityId = session('city_id') ?: auth()->user()?->city_id;
    $city   = $cityId ? \App\Models\City::find($cityId) : null;

    // Название города для слайдера «Рекомендации для вас» (null — город ещё не выбран)
    $recommendationCity = $city?->name;
    $currentCityName    = $recommendationCity ?: 'Выберите город';

    // Только клиники этого города. Если отзывов в городе пока нет — коллекция пустая,
    // а шаблон покажет «Пока в вашем городе нет отзывов» и кнопку в каталог.
    $topItems = $city ? $this->topClinicsInCity($city->name) : collect();

    // Загружаем 3 последние опубликованные новости для блока на главной
    $news = News::where('is_published', true)
        ->orderBy('created_at', 'desc')
        ->take(3)
        ->get();

    // Добавляем 'news' и 'currentCityName' (если она нужна в шаблоне) в compact
    return view('welcome', compact('topItems', 'news', 'currentCityName', 'recommendationCity'));
}

    /**
     * Лучшие клиники одного города по отзывам (до 5 штук).
     * Город клиники хранится текстом (clinics.city), поэтому сравниваем названия без учёта регистра
     * и пробелов по краям. Скрытые на время обжалования отзывы не учитываются (глобальный скоуп Review).
     */
    private function topClinicsInCity(string $cityName, int $limit = 5): \Illuminate\Support\Collection
    {
        $cityName = mb_strtolower(trim($cityName));

        if ($cityName === '') {
            return collect();
        }

        $rows = Review::query()
            ->join('clinics', 'clinics.id', '=', 'reviews.reviewable_id')
            ->where('reviews.reviewable_type', \App\Models\Clinic::class)
            ->whereNotNull('reviews.rating')
            ->whereRaw('LOWER(TRIM(clinics.city)) = ?', [$cityName])
            ->groupBy('reviews.reviewable_id')
            ->select(
                'reviews.reviewable_id as id',
                DB::raw('AVG(reviews.rating) as avg'),
                DB::raw('COUNT(*) as cnt')
            )
            ->get()
            ->map(fn ($row) => ['id' => (int) $row->id, 'avg' => (float) $row->avg, 'count' => (int) $row->cnt])
            ->all();

        $picked = \App\Support\RecommendationPicker::pick($rows, $limit);

        if (! $picked) {
            return collect();
        }

        $clinics = \App\Models\Clinic::whereIn('id', array_column($picked, 'id'))->get()->keyBy('id');

        return collect($picked)
            ->map(function ($row) use ($clinics) {
                $clinic = $clinics->get($row['id']);

                if (! $clinic) {
                    return null;
                }

                $clinic->avg_rating      = round($row['avg'], 1);
                $clinic->reviews_count   = $row['count'];
                $clinic->reviewable_type = 'Clinic';   // по нему шаблон выбирает логотип и ссылку

                return $clinic;
            })
            ->filter()
            ->values();
    }

}
