<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pet;
use App\Models\Animal;
use Illuminate\Support\Facades\Auth;

class PetController extends Controller
{
public function index()
{
    $user = Auth::user();
    $pets = $user->pets()->with('animal')->get();
    $animals = Animal::all();

    return response()->json([
        'pets' => $pets,
        'animals' => $animals,
    ]);
}

public function store(Request $request)
{
    $request->validate([
        'animal_id' => 'required|exists:animals,id',
        'name' => 'required|string|max:255',
        'birth_date' => 'nullable|date',
        'age' => 'nullable|integer|min:0',
        'photo' => 'nullable|image|max:2048',
        'gender' => 'nullable|string|max:10', // ✅ добавляем
    ]);

    $pet = new Pet();
    $pet->user_id = auth()->id();
    $pet->animal_id = $request->animal_id;
    $pet->name = $request->name;
    $pet->birth_date = $request->birth_date;
    $pet->age = $request->age;
        $pet->gender = $request->gender; // ✅ добавляем

    if ($request->hasFile('photo')) {
        $path = $request->file('photo')->store('pets', 'public');
        $pet->photo = $path;
    }

    $pet->save();

    return response()->json(['success' => true, 'pet' => $pet->load('animal')]);
}

public function show($id)
{
    $pet = Pet::with('animal')->where('user_id', auth()->id())->find($id);

    if (!$pet) {
        return response()->json(['error' => 'Питомец не найден'], 404);
    }

    return response()->json($pet);
}


public function update(Request $request, Pet $pet)
{
    $user = Auth::user();
    if ($pet->user_id !== $user->id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $data = $request->only(['animal_id','name','birth_date','age']);

    if ($request->hasFile('photo')) {
        $path = $request->file('photo')->store('pets', 'public');
        $data['photo'] = $path;
    }

    $pet->update($data);

    return response()->json(['success' => true, 'pet' => $pet->load('animal')]);
}



}
