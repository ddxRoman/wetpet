<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;
use App\Models\ReviewPhoto;
use App\Models\ReviewReceipt;

class AccountController extends Controller
{
    // Отображение страницы аккаунта
    public function index()
    {
        $user = Auth::user();
        return view('account', compact('user'));
    }

    public function updateCity(Request $request)
    {
        $request->validate([
            'city_slug' => 'required|string',
        ]);

        $city = \App\Models\City::where('slug', $request->city_slug)->first();

        if (!$city) {
            return response()->json([
                'success' => false,
                'message' => 'Город не найден'
            ]);
        }

        $user = auth()->user();
        $user->city_id = $city->id;
        $user->save();

        return response()->json(['success' => true]);
    }

    // Обновление данных профиля
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

    // Получение отзывов пользователя (с фото и чеками)
public function getReviews($userId)
{
    // \Log::info("getReviews вызван пользователем ID $userId");

    try {
        $reviews = Review::where('user_id', $userId)
            ->with([
                'reviewable:id,name,region,city,street,house',
                'photos:id,review_id,photo_path',
                'receipts:id,review_id,path'
            ])
            ->latest()
            ->get(['id', 'user_id', 'reviewable_id', 'liked', 'disliked', 'content', 'rating', 'created_at']);

        $formatted = $reviews->map(function ($r) {
            $clinic = $r->reviewable;
            return [
                'id'           => $r->id,
                'clinic_id'    => $clinic->id ?? null,
                'clinic_name'  => $clinic->name ?? '—',
                'region'       => $clinic->region ?? null,
                'city'         => $clinic->city ?? null,
                'street'       => $clinic->street ?? null,
                'house'        => $clinic->house ?? null,
                'liked'        => $r->liked,
                'disliked'     => $r->disliked,
                'content'      => $r->content,
                'rating'       => $r->rating,
                'created_at'   => $r->created_at,
                'photos'       => $r->photos->map(fn($p) => [
                    'id' => $p->id,
                    'photo_path' => $p->photo_path,
                ]),
                'receipts'     => $r->receipts->map(fn($f) => [
                    'id' => $f->id,
                    'receipt_path' => $f->path,
                ]),
            ];
        });

        return response()->json($formatted);
    } catch (\Throwable $e) {
        \Log::error('Ошибка getReviews: '.$e->getMessage().' ('.$e->getFile().':'.$e->getLine().')');
        return response()->json(['error' => 'Ошибка загрузки отзывов'], 500);
    }
}



    // === Обновление отзыва ===
    public function updateReview(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        $this->authorize('update', $review);

        $request->validate([
            'liked'    => 'nullable|string|max:500',
            'disliked' => 'nullable|string|max:500',
            'content'  => 'nullable|string|max:2000',
            'rating'   => 'nullable|numeric|min:1|max:5',
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
        $this->authorize('delete', $review);

        // Удаляем фото и чеки
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

    // === Удаление отдельного фото ===
    public function deletePhoto($id)
    {
        $photo = ReviewPhoto::findOrFail($id);
        Storage::delete('public/' . $photo->photo_path);
        $photo->delete();
        return response()->json(['success' => true]);
    }

    // === Удаление отдельного чека ===
    public function deleteReceipt($id)
    {
        $receipt = ReviewReceipt::findOrFail($id);
        Storage::delete('public/' . $receipt->receipt_path);
        $receipt->delete();
        return response()->json(['success' => true]);
    }
}
