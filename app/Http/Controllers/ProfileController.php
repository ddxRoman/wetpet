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
$user->city_id = $request->city_id;
        // Валидация данных
        $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'required|string|max:100',
            'birth_date' => 'nullable|date',
            'city_id' => 'nullable|exists:cities,id|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:webp|max:5120', // до 2MB
        ]);

        // Обновление данных пользователя
        $user->name = $request->name;
        $user->nickname = $request->nickname;
        $user->birth_date = $request->birth_date;
        $user->email = $request->email;
if ($request->filled('city_id')) {
    $user->city_id = $request->city_id;
}

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
    public function updateCity(Request $request)
{
    $request->validate(['city_id' => 'required|exists:cities,id']);

    $user = Auth::user();
    $user->city_id = $request->city_id;
    $user->save();

    return response()->json(['success' => true, 'city_id' => $user->city_id]);
}

}
