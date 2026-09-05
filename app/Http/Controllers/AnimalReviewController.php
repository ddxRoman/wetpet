<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnimalReview;
use Illuminate\Support\Facades\Auth;

class AnimalReviewController extends Controller
{
    /**
     * Общие правила валидации для полей отзыва.
     * pet_weight — вводится в килограммах, максимум с 2 знаками после запятой (0.45, 12.30 ...).
     * pet_age — число лет с максимум одним знаком после запятой (0.5, 1.5, 0.3 ...).
     */
    protected function rules(): array
    {
        return [
            'pet_weight'    => ['nullable', 'numeric', 'min:0', 'max:999.999', 'regex:/^\d+(\.\d{1,3})?$/'],
            'pet_age'       => ['nullable', 'numeric', 'min:0', 'max:99.9', 'regex:/^\d+(\.\d)?$/'],
            'temperament'   => 'required|string',
            'trainability'  => 'required|integer|between:1,5',
            'intelligence'  => 'nullable|integer|between:1,5',
            'sociability'   => 'nullable|integer|between:1,5',
            'comment'       => 'required|string|min:10',
            'health_issues' => 'nullable|string',
        ];
    }

    protected function ruleMessages(): array
    {
        return [
            'pet_weight.regex' => 'Вес можно указывать максимум с тремя знаками после запятой (например: 0.450 или 12.3).',
            'pet_age.regex'    => 'Возраст можно указывать максимум с одним знаком после запятой (например: 0.5 или 1.5).',
        ];
    }

    /**
     * В БД pet_weight хранится в граммах (целое число),
     * а в форму вводится в килограммах — конвертируем.
     */
    protected function weightToGrams(?string $kilograms): ?int
    {
        return $kilograms !== null && $kilograms !== ''
            ? (int) round(((float) $kilograms) * 1000)
            : null;
    }

    public function store(Request $request, $animal_id)
    {
        $validated = $request->validate($this->rules(), $this->ruleMessages());

        $healthIssues = $request->filled('health_issues')
            ? array_map('trim', explode(',', $request->health_issues))
            : [];

        AnimalReview::create([
            'animal_id'     => $animal_id,
            'user_id'       => Auth::id(),
            'pet_weight'    => $this->weightToGrams($validated['pet_weight'] ?? null),
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

    public function update(Request $request, AnimalReview $review)
    {
        abort_if($review->user_id !== Auth::id(), 403, 'Вы не можете редактировать чужой отзыв.');

        $validated = $request->validate($this->rules(), $this->ruleMessages());

        $healthIssues = $request->filled('health_issues')
            ? array_map('trim', explode(',', $request->health_issues))
            : [];

        $review->update([
            'pet_weight'    => $this->weightToGrams($validated['pet_weight'] ?? null),
            'pet_age'       => $validated['pet_age'],
            'temperament'   => $validated['temperament'],
            'trainability'  => $validated['trainability'],
            'intelligence'  => $request->intelligence ?? 3,
            'sociability'   => $request->sociability ?? 3,
            'comment'       => $validated['comment'],
            'health_issues' => $healthIssues,
        ]);

        return redirect()->back()->with('success', 'Отзыв обновлён!');
    }

    public function destroy(AnimalReview $review)
    {
        abort_if($review->user_id !== Auth::id(), 403, 'Вы не можете удалить чужой отзыв.');

        $review->delete();

        return redirect()->back()->with('success', 'Отзыв удалён.');
    }
}