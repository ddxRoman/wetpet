<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    // Список новостей
public function index()
{
    $news = News::where('is_published', true)->orderBy('created_at', 'desc')->paginate(9);
    
    // ВСТАВЬ СЮДА ЭТУ СТРОКУ ДЛЯ ТЕСТА:
    // dd($news->toArray());

    $seoMeta = [
        'title' => 'Новости и статьи — Зверозор',
        'description' => 'Актуальные новости из мира ветеринарии и полезные статьи о питомцах.',
        'image' => asset('images/default-news-share.webp')
    ];
    
    return view('pages.legal.news', compact('news', 'seoMeta'));
}

    // Сохранение новости (загрузка главной обложки + JSON галереи)
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'excerpt' => 'nullable|string|max:500',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gallery_files.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $newsData = [
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            // ИСПРАВЛЕНО: если галочки нет, запишется false. Если в форме её вообще нет — по умолчанию true
            'is_published' => $request->boolean('is_published', true), // Если поля нет в запросе — по умолчанию true, если есть — приведёт к true/false автоматически
        ];

        // 1. Загрузка главного фото новости (обложка)
        if ($request->hasFile('main_image')) {
            $newsData['image'] = $request->file('main_image')->store('news/covers', 'public');
        }

        // 2. Загрузка дополнительных картинок в массив для JSON
        $uploadedImages = [];
        if ($request->hasFile('gallery_files')) {
            foreach ($request->file('gallery_files') as $file) {
                $uploadedImages[] = $file->store('news/galleries', 'public');
            }
        }

        // Записываем массив в поле images (Laravel сам превратит его в JSON благодаря cast в модели)
        $newsData['images'] = $uploadedImages;

        News::create($newsData);

        return redirect()->route('news.index')->with('success', 'Новость успешно опубликована!');
    }

    // Детальная страница новости

public function show($slug)
{
    // Меняем $article на $news
    $news = News::where('slug', $slug)->where('is_published', true)->firstOrFail();
    
    $news->increment('views');

    $description = $news->excerpt;
    if (empty($description)) {
        $cleanedContent = strip_tags($news->content);
        $description = mb_substr($cleanedContent, 0, 160) . '...';
    }

    $shareImage = $news->image ? asset('storage/' . $news->image) : asset('images/default-animal.webp');

    $seoMeta = [
        'title' => $news->title . ' — Зверозор',
        'description' => $description,
        'image' => $shareImage
    ];

    // Передаем как 'news' вместо 'article'
    return view('pages.legal.news-show', compact('news', 'seoMeta'));
}

    // Обновление новости
    public function update(Request $request, $id)
    {
        $article = News::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'excerpt' => 'nullable|string|max:500',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gallery_files.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $newsData = [
            'title' => $request->title,
            'slug' => Str::slug($request->title), // Внимание: изменение ломает старые ссылки, если новость старая!
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'is_published' => $request->has('is_published'), // обновляем статус публикации по галочке
        ];

        // Обновление обложки
        if ($request->hasFile('main_image')) {
            // Удаляем старую обложку, если она есть
            if ($article->image) {
                Storage::disk('public')->delete($article->image);
            }
            $newsData['image'] = $request->file('main_image')->store('news/covers', 'public');
        }

        // Обновление галереи
        if ($request->hasFile('gallery_files')) {
            // Удаляем старые картинки из галереи физически
            if (is_array($article->images)) {
                foreach ($article->images as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            }

            $uploadedImages = [];
            foreach ($request->file('gallery_files') as $file) {
                $uploadedImages[] = $file->store('news/galleries', 'public');
            }
            $newsData['images'] = $uploadedImages;
        }

        $article->update($newsData);

        return redirect()->route('news.index')->with('success', 'Новость успешно обновлена!');
    }
}