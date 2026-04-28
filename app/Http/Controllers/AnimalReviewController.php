<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnimalReview;
use Illuminate\Support\Facades\Auth;

class AnimalReviewController extends Controller
{
public function store(Request $request, $animal_id)
{
    // 1. Валидация (убрали pet_name)
    $validated = $request->validate([
        'pet_weight'    => 'nullable|numeric',
        'pet_age'       => 'nullable|integer',
        'temperament'   => 'required|string',
        'trainability'  => 'required|integer|between:1,5',
        'intelligence'  => 'nullable|integer|between:1,5',
        'sociability'   => 'nullable|integer|between:1,5',
        'comment'       => 'required|string|min:10',
        'health_issues' => 'nullable|string',
    ]);

    $healthIssues = $request->filled('health_issues') 
        ? array_map('trim', explode(',', $request->health_issues)) 
        : [];

    // 2. Сохранение (убрали pet_name)
    AnimalReview::create([
        'animal_id'     => $animal_id,
        'user_id'       => Auth::id(),
        'pet_weight'    => $validated['pet_weight'],
        'pet_age'       => $validated['pet_age'],
        'temperament'   => $validated['temperament'],
        'trainability'  => $validated['trainability'],
        'intelligence'  => $request->intelligence ?? 3,
        'sociability'   => $request->sociability ?? 3,
        'comment'       => $validated['comment'],
        'health_issues' => $healthIssues,
    ]);

    return redirect()->back()->with('success', 'Спасибо за отзыв!');
}
}