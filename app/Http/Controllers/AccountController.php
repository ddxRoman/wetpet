<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


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
        'city_slug' => 'required|string',
    ]);

    $city = \App\Models\City::where('slug', $request->city_slug)->first();

    if (!$city) {
        return response()->json([
            'success' => false,
            'message' => 'Город не найден'
        ]);
    }

    $user = auth()->user();
    $user->city_id = $city->id;
    $user->save();

    return response()->json(['success' => true]);
}




    // Обновление данных профиля


public function updateProfile(Request $request)
{
    $user = auth()->user();

    $request->validate([
        'name' => 'required|string|max:255',
        'nickname' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'birth_date' => 'nullable|date',
        'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
    ]);


    // 🔹 Сохраняем аватар, если загружен
    if ($request->hasFile('avatar')) {
        // Удаляем старый (если не дефолт)
        if ($user->avatar && Storage::exists('public/' . $user->avatar)) {
            Storage::delete('public/' . $user->avatar);
        }

        // Сохраняем новый в `storage/app/public/avatars`
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
    }

    // 🔹 Обновляем остальные данные
    $user->name = $request->name;
    $user->nickname = $request->nickname;
    $user->email = $request->email;
    $user->birth_date = $request->birth_date;

if ($request->filled('city_slug')) {
    $city = \App\Models\City::where('slug', $request->city_slug)->first();
    if ($city) {
        $user->city_id = $city->id;
    }
}


    $user->save();

    return redirect()->back()->with('success', 'Профиль обновлён');
}

}
