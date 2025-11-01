<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pet;
use Illuminate\Support\Facades\Storage;

class PetController extends Controller
{
 public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string|max:100',
            'breed' => 'required|string|max:100',
            'birth_date' => 'nullable|date',
            'age' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'photo' => 'nullable|image|max:2048',
        ]);

        $pet = new Pet($request->only(['type', 'breed', 'birth_date', 'age', 'color']));

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('pets', 'public');
            $pet->photo = $path;
        }

        $pet->save();

        // Привязка к текущему пользователю
        $pet->owners()->attach(Auth::id());

        return back()->with('success', 'Питомец добавлен и привязан к вашему профилю!');
    }
}
