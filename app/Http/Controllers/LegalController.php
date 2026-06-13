<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Faq;

class LegalController extends Controller
{
    // Твой оригинальный метод — не тронут
    public function show($slug)
    {
        $page = DB::table('legal_pages')->where('slug', $slug)->first();
        abort_if(!$page, 404);
        return view('pages.legal.template', compact('page'));
    }

    // ───── Новый метод для FAQ ─────

    private const FAQ_CATEGORIES = [
        'general'  => 'Общие вопросы',
        'account'  => 'Аккаунт',
        'clinics'  => 'Клиники и врачи',
        'reviews'  => 'Отзывы',
        'payments' => 'Оплата',
    ];

    public function faq()
    {
        $currentCategory = request('category');

        $query = Faq::active()->ordered();

        if ($currentCategory && array_key_exists($currentCategory, self::FAQ_CATEGORIES)) {
            $query->where('category', $currentCategory);
        }

        $faqs = $query->get();

        // Только категории у которых есть активные вопросы
        $categories = Faq::active()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter(fn($c) => array_key_exists($c, self::FAQ_CATEGORIES))
            ->mapWithKeys(fn($c) => [$c => self::FAQ_CATEGORIES[$c]]);

        return view('pages.legal.faq', [
            'faqs'            => $faqs,
            'categories'      => $categories,
            'categoryLabels'  => self::FAQ_CATEGORIES,
            'currentCategory' => $currentCategory,
        ]);
    }
}
