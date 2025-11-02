<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    // Отображение страницы аккаунта
    public function index()
    {
        $user = Auth::user(); // Получаем текущего пользователя
        return view('account', compact('user'));
    }


    public function updateCity(Request $request)
{
    $request->validate([
        'city_id' => 'required|exists:cities,id',
    ]);

    $user = auth()->user();
    $user->city_id = $request->city_id;
    $user->save();

    return response()->json(['success' => true]);
}


    // Обновление данных профиля
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'required|string|max:255|unique:users,nickname,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'birth_date' => 'nullable|date',
            'city' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Если загружен аватар
        if ($request->hasFile('avatar')) {
            // Удаляем старый аватар, если он был
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Сохраняем новый
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        // Обновляем профиль
        $user->update($validated);

        return back()->with('success', 'Профиль успешно обновлён!');
    }
}
