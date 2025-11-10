<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Pet;
use App\Models\Animal;

class PetController extends Controller
{
    // === Получение питомцев и всех животных ===
    public function index()
    {
        $user = Auth::user();

        $pets = $user->pets()->with('animal')->get();
        $animals = Animal::select('id', 'species', 'breed')->get();

        return response()->json([
            'success' => true,
            'pets' => $pets,
            'animals' => $animals,
        ]);
    }

    // === Добавление питомца ===
    public function store(Request $request)
    {
        try {
            // 🔹 Если на фронте передаётся type и breed — подбираем animal_id
            $animalId = $request->input('animal_id');

            if (!$animalId && $request->filled(['type', 'breed'])) {
                $animal = Animal::where('species', $request->type)
                    ->where('breed', $request->breed)
                    ->first();

                if ($animal) {
                    $animalId = $animal->id;
                }
            }

            if (!$animalId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Не удалось определить животное (тип/порода)',
                ], 422);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'birth_date' => 'nullable|date',
                'age' => 'nullable|integer|min:0',
                'photo' => 'nullable|image|max:4096',
                'gender' => 'nullable|string|max:10',
            ]);

            $pet = new Pet();
            $pet->user_id = auth()->id();
            $pet->animal_id = $animalId;
            $pet->name = $request->name;
            $pet->birth_date = $request->birth_date;
            $pet->age = $request->age;
            $pet->gender = $request->gender;

            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('pets', 'public');
                $pet->photo = $path;
            }

            $pet->save();

            return response()->json([
                'success' => true,
                'message' => 'Питомец добавлен',
                'pet' => $pet->load('animal'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при добавлении питомца',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // === Просмотр одного питомца ===
    public function show($id)
    {
        $pet = Pet::with('animal')->where('user_id', auth()->id())->find($id);

        if (!$pet) {
            return response()->json(['success' => false, 'message' => 'Питомец не найден'], 404);
        }

        return response()->json(['success' => true, 'pet' => $pet]);
    }

    // === Обновление питомца ===
    public function update(Request $request, Pet $pet)
    {
        $user = Auth::user();
        if ($pet->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Нет доступа'], 403);
        }

        $request->validate([
            'animal_id' => 'nullable|exists:animals,id',
            'name' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'age' => 'nullable|integer|min:0',
            'photo' => 'nullable|image|max:4096',
            'gender' => 'nullable|string|max:10',
        ]);

        $data = $request->only(['animal_id', 'name', 'birth_date', 'age', 'gender']);

        if ($request->hasFile('photo')) {
            if ($pet->photo && Storage::disk('public')->exists($pet->photo)) {
                Storage::disk('public')->delete($pet->photo);
            }
            $data['photo'] = $request->file('photo')->store('pets', 'public');
        }

        $pet->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Данные питомца обновлены',
            'pet' => $pet->load('animal'),
        ]);
    }

    // === Удаление питомца ===
    public function destroy(Pet $pet)
    {
        if ($pet->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Нет доступа'], 403);
        }

        if ($pet->photo && Storage::disk('public')->exists($pet->photo)) {
            Storage::disk('public')->delete($pet->photo);
        }

        $pet->delete();

        return response()->json(['success' => true, 'message' => 'Питомец удалён']);
    }

    // === Получение списка пород по типу ===
public function getBreeds(Request $request)
{
    $type = $request->query('type');

    if (!$type) {
        return response()->json(['success' => false, 'message' => 'Тип животного не указан']);
    }

    $breeds = Animal::where('species', $type)
        ->whereNotNull('breed')
        ->where('breed', '<>', '')
        ->select('id', 'breed as name')
        ->distinct()
        ->orderBy('name')
        ->get();

    // Возвращаем в том виде, который ожидает фронт
    return response()->json($breeds);
}

}
