<?php

namespace App\Http\Controllers;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
public function index()
{
    // Получаем название города из сессии (или подставляем значение по умолчанию)
    $currentCityName = session('city_name', 'Выберите город');


    $topRated = Review::query()
        ->select(
            'reviewable_id',
            'reviewable_type',
            DB::raw('AVG(rating) as avg_rating'),
            DB::raw('COUNT(*) as reviews_count')
        )
        ->whereNotNull('rating')
        ->groupBy('reviewable_id', 'reviewable_type')
        ->orderByDesc('avg_rating')
        ->inRandomOrder()
        ->limit(10)
        ->get();

    // Подгружаем сами модели (Doctor, Clinic, ...)
    $topItems = $topRated->map(function ($row) {
        $model = $row->reviewable_type::find($row->reviewable_id);

        if (!$model) {
            return null;
        }

        $model->avg_rating = round($row->avg_rating, 1);
        $model->reviews_count = $row->reviews_count;
        $model->reviewable_type = class_basename($row->reviewable_type);

        return $model;
    })->filter();

    return view('welcome', compact('topItems'));
}


}
