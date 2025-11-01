<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        // Валидация данных
        $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'required|string|max:100',
            'birth_date' => 'nullable|date',
            'city' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|max:2048', // до 2MB
        ]);

        // Обновление данных пользователя
        $user->name = $request->name;
        $user->nickname = $request->nickname;
        $user->birth_date = $request->birth_date;
        $user->email = $request->email;
        $user->city = $request->city; // если это текстовое поле, а не отдельная таблица

        // Загрузка нового аватара (если был отправлен)
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return back()->with('success', 'Профиль успешно обновлён!');
    }
}
