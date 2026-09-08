<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Faq;
use App\Models\GlossaryTerm;

class LegalController extends Controller
{
    // Страницы, которые не должны попадать в поисковую выдачу
    // (закрыты и в robots.txt — здесь дублируем через noindex как подстраховку,
    // на случай если бот всё же откроет страницу по внешней ссылке).
    private const NOINDEX_SLUGS = [
        'privacy',
        'terms',
        'personal-data-agreement',
        'privacy-politics', // обрабатывает /legal/personal-data-agreement (obrabotka-personalnyh-dannyh)
        'cookies',
        'content-rules',
    ];

    // ─── Твой оригинальный метод — не тронут ───
    public function show($slug)
    {
        $page = DB::table('legal_pages')->where('slug', $slug)->first();
        abort_if(!$page, 404);

        // seoMeta передаём ТОЛЬКО когда нужно закрыть страницу от индексации —
        // иначе затрём автогенерацию title/description из View::composer('*')
        // в AppServiceProvider (она пропускает шаг, если seoMeta уже задан).
        if (in_array($slug, self::NOINDEX_SLUGS, true)) {
            return view('pages.legal.template', [
                'page'    => $page,
                'seoMeta' => ['robots' => 'noindex, follow'],
            ]);
        }

        return view('pages.legal.template', compact('page'));
    }

    // ─── FAQ ───
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

    // ─── Глоссарий ───
    public function glossary()
    {
        $currentCategory = request('category');

        $query = GlossaryTerm::active()->ordered();

        // Фильтр по категории — свободная строка из БД
        if ($currentCategory) {
            $query->where('category', $currentCategory);
        }

        $terms = $query->get();

        $grouped = $terms->groupBy('letter');
        $letters = $grouped->keys();

        // Берём уникальные категории прямо из БД — без фиксированного массива
        $categories = GlossaryTerm::active()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('pages.legal.glossary', [
            'terms'           => $terms,
            'grouped'         => $grouped,
            'letters'         => $letters,
            'categories'      => $categories,
            'currentCategory' => $currentCategory,
            'seoMeta'         => ['robots' => 'noindex, follow'],
        ]);
    }
}
