<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
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
        $review->reviewable_type = $validated['reviewable_type'];
        $review->rating = $validated['rating'];
        $review->liked = $validated['liked'] ?? null;
        $review->disliked = $validated['disliked'] ?? null;
        $review->content = $validated['content'] ?? null;
        $review->pet_id = $validated['pet_id'] ?? null;
        $review->review_date = now();

        // 📎 Загрузка чека
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('reviews/receipts', 'public');
            $review->receipt_path = $path;
        }

        $review->save();

        // 🖼 Загрузка фото
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('reviews/photos', 'public');
                \App\Models\ReviewPhoto::create([
                    'review_id' => $review->id,
                    'path' => $path,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Спасибо! Ваш отзыв успешно отправлен.');
    


    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Review $review)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        //
    }
}
