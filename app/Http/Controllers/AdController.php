<?php

namespace App\Http\Controllers;
use App\Models\Ad;
use App\Models\Animal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
// Импортируем классы v4 напрямую
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AdController extends Controller
{
    // Список всех объявлений (Каталог)
/**
     * Список всех объявлений (Каталог)
     */
    public function index(Request $request)
    {
        // Используем Eager Loading (with), чтобы избежать ошибки "property of null" 
        // и оптимизировать запросы к БД
        $query = Ad::with(['animal', 'user'])
            ->where('is_active', true)
            ->whereNotNull('moderated_at');

        // Фильтр по городу
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        // Фильтр по ID животного
        if ($request->filled('animal_id')) {
            $query->where('animal_id', $request->animal_id);
        }

        // НОВОЕ: Фильтр "Бесплатно"
    $query->when($request->is_free, function ($q) {
        return $q->where('price_type', 'free');
    });

// Поиск по названию (Улучшенный)
if ($request->filled('search')) {
    $searchTerm = $request->search;
    
    // Обрезаем последнюю букву, если слово длиннее 4 символов (простой способ искать корни)
    $stem = mb_strlen($searchTerm) > 4 ? mb_substr($searchTerm, 0, -1) : $searchTerm;

    $query->where(function($q) use ($searchTerm, $stem) {
        $q->where('title', 'like', '%' . $searchTerm . '%') // Точное совпадение
          ->orWhere('title', 'like', '%' . $stem . '%');   // Совпадение по корню
    });
}

        // Пагинация и сортировка (сначала новые)
        $ads = $query->latest()->paginate(51);

        // Получаем уникальные виды животных по полю species для фильтра
        // Используем groupBy, чтобы схлопнуть дубликаты названий
$animals = Animal::selectRaw('MAX(id) as id, species')
    ->groupBy('species')
    ->orderBy('species', 'asc')
    ->get();

        return view('pages.ads.index', compact('ads', 'animals'));
    }

    public function show(Ad $ad)
{
    // Загружаем связи, чтобы не было ошибок при обращении к $ad->animal или $ad->user
    $ad->load(['animal', 'user']);

    return view('pages.ads.show', compact('ad'));
}

public function create()
{
    // Используем ту же логику группировки, что и в index/edit для красоты списка
    $animals = Animal::selectRaw('MAX(id) as id, species')
        ->groupBy('species')
        ->orderBy('species', 'asc')
        ->get();

    // Исправляем путь к шаблону на pages.ads.create
    return view('pages.ads.create', compact('animals'));
}

public function store(Request $request)
{
    $validated = $request->validate([
        'title'       => 'required|string|max:255',
        'description' => 'required|string',
        'city'        => 'required|string|max:255',
        'address'     => 'nullable|string|max:255',
        'condition'   => 'required|in:new,used',
        'animal_id'   => 'required|exists:animals,id',
        'phone'       => 'required|string|max:255',
        'price'       => 'nullable|numeric|min:0',
        'photos.*'    => 'image|mimes:jpeg,png,jpg,webp|max:5120',
    ]);

    try {
        $ad = new \App\Models\Ad();
        $ad->user_id = auth()->id();
        $ad->fill($validated);

        // Логика цены (Zverozor стандарт)
        $price = $request->input('price', 0);
        if ($request->has('is_exchange')) {
            $ad->price_type = 'exchange';
            $ad->price = $price;
        } elseif (empty($price) || $price == 0) {
            $ad->price_type = 'free';
            $ad->price = 0;
        } else {
            $ad->price_type = 'fixed';
            $ad->price = $price;
        }

        // ОБРАБОТКА ФОТО (Версия 3.0 Final approach)
        if ($request->hasFile('photos')) {
            $paths = [];
            
            $manager = new \Intervention\Image\ImageManager(
                new \Intervention\Image\Drivers\Gd\Driver()
            );

            $storageFolder = storage_path('app/public/ads');
            if (!file_exists($storageFolder)) {
                mkdir($storageFolder, 0755, true);
            }

            foreach ($request->file('photos') as $photo) {
                $filename = uniqid() . '.webp';
                $savePath = 'ads/' . $filename;
                $fullPath = storage_path('app/public/' . $savePath);

                // 1. Декодируем
                $image = $manager->decode($photo->getRealPath());
                
                // 2. Масштабируем
                $image->scale(width: 1200);
                
                // 3. Кодируем через объект Encoder (гарантированный способ в 3.x)
                $encoded = $image->encode(
                    new \Intervention\Image\Encoders\WebpEncoder(quality: 80)
                );
                
                // 4. Сохраняем бинарные данные
                file_put_contents($fullPath, (string) $encoded);

                $paths[] = $savePath;
            }
            $ad->photos = $paths;
        }

        $ad->is_active = true;
        $ad->moderated_at = now();
        $ad->save();

        return redirect()->route('ads.index')->with('success', 'Объявление опубликовано!');

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Zverozor Store Error: ' . $e->getMessage());
        return back()->withInput()->with('error', 'Ошибка: ' . $e->getMessage());
    }
}

/**
 * Показать форму редактирования объявления
 */
public function edit(Ad $ad)
{
    // Проверка: редактировать может только автор
    if (auth()->id() !== $ad->user_id) {
        abort(403, 'У вас нет прав для редактирования этого объявления.');
    }

    // Получаем уникальные виды животных для выпадающего списка
    $animals = Animal::selectRaw('MAX(id) as id, species')
        ->groupBy('species')
        ->get();

    return view('pages.ads.edit', compact('ad', 'animals'));
}

public function update(Request $request, Ad $ad)
{
    // Валидация
    $validated = $request->validate([
        'title' => 'required|max:255',
        'description' => 'required',
        'city' => 'required',
        'animal_id' => 'required|exists:animals,id',
        'price' => 'nullable|integer|min:0',
        'phone' => 'required',
    ]);

    // Логика определения типа цены
    $price = $request->input('price');
    
    if ($price == 0 && !is_null($price)) {
        $ad->price_type = 'free';
        $ad->price = 0;
    } elseif ($request->has('is_exchange')) {
        $ad->price_type = 'exchange';
        $ad->price = $price;
    } else {
        $ad->price_type = 'fixed';
        $ad->price = $price;
    }

    $ad->fill($validated);
    $ad->save();

    return redirect()->route('ads.show', $ad->id)->with('success', 'Объявление обновлено!');
}

    // Удаление (Мягкое)
    public function destroy(Ad $ad)
    {
        // Проверка: только автор может удалить
        if (auth()->id() !== $ad->user_id) {
            abort(403);
        }

        $ad->update(['is_active' => false]); // Снимаем с публикации
        $ad->delete(); // Soft Delete (сохраняется в базе)

        return redirect()->route('ads.index')->with('success', 'Объявление удалено');
    }
}