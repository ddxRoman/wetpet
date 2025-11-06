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
        ]);

        $review = new Review();
        $review->user_id = Auth::id();
        $review->reviewable_id = $validated['reviewable_id'];
        $review->reviewable_type = str_replace('\\\\', '\\', $validated['reviewable_type']); // исправляем двойные слеши
        $review->rating = $validated['rating'];
        $review->liked = $validated['liked'] ?? null;
        $review->disliked = $validated['disliked'] ?? null;
        $review->content = $validated['content'] ?? null;
        $review->pet_id = $validated['pet_id'] ?? null;
        $review->review_date = now();
        $review->save();

        // 📎 Сохраняем чек в отдельную таблицу
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('reviews/receipts', 'public');

            ReviewReceipt::create([
                'review_id' => $review->id,
                'clinic_id' => $review->reviewable_id,
                'path' => $path,
                'status' => 'pending',
            ]);
        }

        // 🖼 Сохраняем фото
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('reviews/photos', 'public');
                ReviewPhoto::create([
                    'review_id' => $review->id,
                    'photo_path' => $path,
                ]);
            }
        }

        // 🔁 Возвращаем пользователя на вкладку "Отзывы"
        return redirect()
            ->to(url()->previous() . '#directions')
            ->with('success', 'Спасибо! Ваш отзыв успешно добавлен.');
    }

    /**
     * Обновление существующего отзыва
     */
    public function update(Request $request, Review $review)
    {
        $validated = $request->validate([
            'rating' => 'integer|min:1|max:5',
            'liked' => 'nullable|string|max:255',
            'disliked' => 'nullable|string|max:255',
            'content' => 'nullable|string|max:2000',
            'photos.*' => 'nullable|image|max:5120',
        ]);

        $review->update($validated);

        // Добавляем новые фото (если есть)
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('reviews/photos', 'public');
                ReviewPhoto::create([
                    'review_id' => $review->id,
                    'photo_path' => $path,
                ]);
            }
        }

        return redirect()
            ->to(url()->previous() . '#directions')
            ->with('success', 'Отзыв успешно обновлён.');
    }

    /**
     * Удаление отзыва
     */
    public function destroy(Review $review)
    {
        if (Auth::id() !== $review->user_id) {
            return back()->withErrors(['error' => 'Нет прав для удаления этого отзыва.']);
        }

        $review->delete();

        return redirect()
            ->to(url()->previous() . '#directions')
            ->with('success', 'Отзыв удалён.');
    }
}
