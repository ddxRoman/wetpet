<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;
use App\Models\ReviewPhoto;
use App\Models\ReviewReceipt;
use App\Models\Pet;
use Illuminate\Support\Facades\DB;
use App\Models\Organization;
use App\Models\Specialist;
use App\Models\ClinicOwner;
use App\Models\OrganizationOwner;
use App\Models\SpecialistOwner;
use App\Models\Clinic;
use App\Models\City;
use App\Models\FieldOfActivity;


class AccountController extends Controller
{
    
    // === Страница аккаунта ===

public function index()
{
    $user = Auth::user();
    $pets = Pet::where('user_id', $user->id)->get();

    // =============================
    // ФЛАГИ ДЛЯ ВКЛАДОК
    // =============================
    $hasClinic = ClinicOwner::where('user_id', $user->id)->exists();
    $hasOrganization = OrganizationOwner::where('user_id', $user->id)->exists();
    
    $specialistOwner = SpecialistOwner::where('user_id', $user->id)->first();
    $hasSpecialistProfile = (bool) $specialistOwner;

    // =============================
    // ДАННЫЕ ДЛЯ ВКЛАДКИ СПЕЦИАЛИСТА
    // =============================
    $specialist = null;
    $groupedFields = collect(); // Сгруппированные специализации
    $regions = collect();
    $cities = collect();
    $organizations = collect();
    $currentCity = null;

    // Подгружаем специализации ВСЕГДА (даже если профиля еще нет, для формы создания/редактирования)
// 1. Получаем все поля
$allFields = FieldOfActivity::where('type', 'specialist')
    ->orderBy('name')
    ->get();
    
// 2. Группируем вручную: если activity == doctor, оставляем "Врачи", иначе всё в "Другие специалисты"
$groupedFields = $allFields->groupBy(function ($item) {
    return ($item->activity === 'doctor') ? 'Врачи' : 'Другие специалисты';
});
// 3. Сортируем группы, чтобы "Врачи" всегда были первыми
$groupedFields = $groupedFields->sortByDesc(function ($value, $key) {
    return $key === 'Врачи';
});
    // Подгружаем регионы для селекта
    $regions = City::select('region')
        ->whereNotNull('region')
        ->distinct()
        ->orderBy('region')
        ->pluck('region');

    if ($specialistOwner) {
        $specialist = Specialist::find($specialistOwner->specialist_id);

        if ($specialist) {
            // Текущий город специалиста для инициализации селектов
            $currentCity = City::find($specialist->city_id);

            if ($currentCity) {
                // Города того же региона, что и у специалиста
                $cities = City::where('region', $currentCity->region)
                    ->pluck('name', 'id');

                // Организации в городе специалиста
                // ВАЖНО: убедись, что в таблице организаций поле называется 'city' (как в твоем исходном коде) 
                // или 'city_id'. Если не сработает, проверь имя столбца.
                $organizations = Organization::where('city', $specialist->city_id)
                    ->pluck('name', 'id');
            }
        }
    }

    return view('account', [
        'user' => $user,
        'pets' => $pets,

        // 🔹 флаги вкладок
        'hasClinic' => $hasClinic,
        'hasOrganization' => $hasOrganization,
        'hasSpecialistProfile' => $hasSpecialistProfile,

        // 🔹 specialist tab
        'specialist' => $specialist,
        'groupedFields' => $groupedFields, // Твоя новая переменная для Blade
        'regions' => $regions,
        'cities' => $cities,
        'organizations' => $organizations,
        'currentCity' => $currentCity,
    ]);
}





    // === Обновление города ===
    public function updateCity(Request $request)
    {
        $request->validate([
            'city_slug' => 'required|string',
        ]);

        $city = \App\Models\City::where('slug', $request->city_slug)->first();

        if (!$city) {
            return response()->json(['success' => false, 'message' => 'Город не найден']);
        }

        $user = auth()->user();
        $user->city_id = $city->id;
        $user->save();

        return response()->json(['success' => true]);
    }

    // === Обновление профиля ===
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'       => 'required|string|max:255',
            'nickname'   => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'birth_date' => 'nullable|date',
            'avatar'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        // Аватар
        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::exists('public/' . $user->avatar)) {
                Storage::delete('public/' . $user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->fill($request->only('name', 'nickname', 'email', 'birth_date'));

        if ($request->filled('city_slug')) {
            $city = \App\Models\City::where('slug', $request->city_slug)->first();
            if ($city) $user->city_id = $city->id;
        }

        $user->save();

        return redirect()->back()->with('success', 'Профиль обновлён');
    }

    // === Получение отзывов текущего пользователя ===
public function getReviews()
{
    $userId = auth()->id();

    try {
        $reviews = Review::where('user_id', $userId)
            ->with([
                 'reviewable', // Работает для клиник и врачей, если у них есть эти поля
                'photos:id,review_id,photo_path',
                'receipts:id,review_id,path'
            ])
            ->latest()
            ->get();

        if ($reviews->isEmpty()) {
            return response()->json([]);
        }

        $formatted = $reviews->map(function ($r) {
            $target = $r->reviewable; // Клиника или врач

            return [
                'id' => $r->id,
                'target_id' => $target?->id,
                'target_type' => class_basename($target), // Clinic или Doctor, полезно для фронтенда
                'target_name' => $target?->name ?? '—',
                'region' => $target?->region ?? null,
                'city' => $target?->city ?? null,
                'street' => $target?->street ?? null,
                'house' => $target?->house ?? null,
                'liked' => $r->liked,
                'disliked' => $r->disliked,
                'content' => $r->content,
                'rating' => $r->rating,
                'created_at' => $r->created_at->toDateTimeString(),
                'photos' => $r->photos->map(fn($p) => [
                    'id' => $p->id,
                    'photo_path' => $p->photo_path,
                ]),
                'receipts' => $r->receipts->map(fn($f) => [
                    'id' => $f->id,
                    'receipt_path' => $f->path,
                ]),
            ];
        });

        return response()->json($formatted);
    } catch (\Throwable $e) {
        \Log::error('Ошибка getReviews: ' . $e->getMessage());
        return response()->json(['error' => 'Ошибка загрузки отзывов'], 500);
    }
}

    // === Обновление отзыва ===
    public function updateReview(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        if ($review->user_id !== auth()->id()) {
            abort(403, 'Нет прав для изменения этого отзыва.');
        }

        $request->validate([
            'liked' => 'nullable|string|max:500',
            'disliked' => 'nullable|string|max:500',
            'content' => 'nullable|string|max:2000',
            'rating' => 'nullable|numeric|min:1|max:5',
            'photos.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'receipts.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $review->update($request->only('liked', 'disliked', 'content', 'rating'));

        // Фото
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('review_photos', 'public');
                ReviewPhoto::create([
                    'review_id' => $review->id,
                    'photo_path' => $path,
                ]);
            }
        }

        // Чеки
        if ($request->hasFile('receipts')) {
            foreach ($request->file('receipts') as $file) {
                $path = $file->store('review_receipts', 'public');
                ReviewReceipt::create([
                    'review_id' => $review->id,
                    'path' => $path,
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    // === Удаление отзыва ===
    public function deleteReview($id)
    {
        $review = Review::findOrFail($id);

        if ($review->user_id !== auth()->id()) {
            abort(403, 'Нет прав для удаления этого отзыва.');
        }

        foreach ($review->photos as $photo) {
            Storage::delete('public/' . $photo->photo_path);
            $photo->delete();
        }

        foreach ($review->receipts as $receipt) {
            Storage::delete('public/' . $receipt->path);
            $receipt->delete();
        }

        $review->delete();

        return response()->json(['success' => true]);
    }

    // === Удаление фото ===
    public function deletePhoto($id)
    {
        $photo = ReviewPhoto::findOrFail($id);
        Storage::delete('public/' . $photo->photo_path);
        $photo->delete();
        return response()->json(['success' => true]);
    }

    // === Удаление чека ===
    public function deleteReceipt($id)
    {
        $receipt = ReviewReceipt::findOrFail($id);
        Storage::delete('public/' . $receipt->path);
        $receipt->delete();
        return response()->json(['success' => true]);
    }
}
