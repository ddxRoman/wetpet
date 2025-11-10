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
        ], [
            'rating.required' => 'Пожалуйста, выберите оценку от 1 до 5 звёзд.',
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

        // 📎 Сохраняем чек
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('reviews/receipts', 'public');
            ReviewReceipt::create([
                'review_id' => $review->id,
                'clinic_id' => $review->reviewable_id,
                'path' => $path,
                'status' => 'pending',
            ]);
        }

        // 🖼 Фото
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
            ->to(url("/clinics/{$review->reviewable_id}#reviews"))
            ->with('success', 'Спасибо! Ваш отзыв успешно добавлен.');
    }

    /**
     * Обновление отзыва
     */
public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        $review->update([
            'liked' => $request->liked,
            'disliked' => $request->disliked,
            'content' => $request->content,
            'rating' => $request->rating,
        ]);

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

        $review->delete();

        return response()->json(['success' => true]);
    }



}
