<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewPhoto;
use App\Models\ReviewReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Сохранение нового отзыва
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'reviewable_id' => 'required|integer',
        'reviewable_type' => 'required|string',
        'rating' => 'required|integer|min:1|max:5',
        'liked' => 'nullable|string|max:255',
        'disliked' => 'nullable|string|max:255',
        'content' => 'nullable|string|max:2000',
        'pet_id' => 'nullable|integer',
        'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        'photos.*' => 'nullable|image|max:5120',
        'redirect_slug'   => 'required|string',
    ]);

    $review = new Review();
    $review->user_id = Auth::id();
    $review->reviewable_id = $validated['reviewable_id'];
    $review->reviewable_type = str_replace('\\\\', '\\', $validated['reviewable_type']);
    $review->rating = $validated['rating'];
    $review->liked = $validated['liked'] ?? null;
    $review->disliked = $validated['disliked'] ?? null;
    $review->content = $validated['content'] ?? null;
    $review->pet_id = $validated['pet_id'] ?? null;
    $review->review_date = now();
    $review->save();

    // чек
    if ($request->hasFile('receipt')) {
        $path = $request->file('receipt')->store('reviews/receipts', 'public');

        ReviewReceipt::create([
            'review_id' => $review->id,
            'path' => $path,
            'status' => 'pending',
        ]);
    }

    // фотографии
    if ($request->hasFile('photos')) {
        foreach ($request->file('photos') as $photo) {
            $path = $photo->store('reviews/photos', 'public');
            ReviewPhoto::create([
                'review_id' => $review->id,
                'photo_path' => $path,
            ]);
        }
    }

    // === КОРРЕКТНЫЙ РЕДИРЕКТ ===
    $model = $review->reviewable;
    $route = $model instanceof \App\Models\Doctor
        ? 'doctors.show'
        : 'clinics.show';

    return redirect()
        ->route($route, [$model->slug, 'tab' => 'reviews'])
        ->with('success', 'Спасибо! Ваш отзыв успешно добавлен.');
}


    /**
     * Обновление отзыва
     */
public function update(Request $request, $id)
{
    $review = Review::findOrFail($id);

    // Проверка владельца
    if ($review->user_id !== auth()->id()) {
        abort(403);
    }

    // Обновляем основные поля
    $review->update([
        'liked' => $request->input('liked'),
        'disliked' => $request->input('disliked'),
        'content' => $request->input('content'),
        'rating' => $request->input('rating'),
    ]);

    // === 📸 Добавляем новые фото ===
    if ($request->hasFile('photos')) {
        foreach ($request->file('photos') as $photo) {
            $path = $photo->store('reviews/photos', 'public');
            $review->photos()->create([
                'photo_path' => $path,
            ]);
        }
    }

    // === 🧾 Добавляем новые чеки ===
// === 🧾 Добавляем новые чеки ===
if ($request->hasFile('receipts')) {
    foreach ($request->file('receipts') as $file) {
        $path = $file->store('reviews/receipts', 'public');
        ReviewReceipt::create([
            'review_id' => $review->id,
            'clinic_id' => $review->reviewable_id,
            'path' => $path,
            'status' => 'pending',
        ]);
    }
}


    return response()->json(['success' => true]);
}

    /**
     * Удаление отзыва
     */
public function destroy($id)
{
    $review = Review::findOrFail($id);

    if ($review->user_id !== auth()->id()) {
        abort(403);
    }

    $model = $review->reviewable;
    $review->delete();

    $route = $model instanceof \App\Models\Doctor
        ? 'doctors.show'
        : 'clinics.show';

    return redirect()
        ->route($route, $model->slug)
        ->with('success', 'Отзыв удалён');
}





}
