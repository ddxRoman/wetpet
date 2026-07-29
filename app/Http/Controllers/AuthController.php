<?php

namespace App\Http\Controllers;
    use App\Rules\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\TelegramService;


class AuthController extends Controller
{
    // Показать форму регистрации
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    // Обработка регистрации
public function register(Request $request)
{
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'phone' => ['nullable', 'sometimes', new PhoneNumber, 'unique:users,phone'],

        'password' => 'required|string|min:8|confirmed',
        'personal_data_agreement' => 'required|accepted',
    ], [
        'personal_data_agreement.required' => 'Необходимо согласие на обработку персональных данных.',
        'personal_data_agreement.accepted' => 'Необходимо согласие на обработку персональных данных.',
    ]);

    $user = User::create([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'phone' => $validatedData['phone'] ?? null,
        'password' => bcrypt($validatedData['password']),
    ]);

        // ✅ Telegram-уведомление о регистрации
$phoneText = $user->phone ? $user->phone : 'не указан';

TelegramService::send(
    "🎉 <b>Новая регистрация</b>\n\n" .
    "👤 Имя: {$user->name}\n" .
    "📧 Email: {$user->email}\n" .
    "📱 Телефон: {$phoneText}\n" .
    "🕒 Дата: " . now()->format('d.m.Y H:i')
);


    Auth::login($user);


    return redirect()->intended(
        $request->get('redirect', '/')
    );

}

    // Показать форму входа
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Обработка входа
public function login(Request $request)
{
    $request->validate([
        'login' => 'required|string',
        'password' => 'required|string',
    ]);

    // Проверяем существует ли пользователь
    $user = User::where('email', $request->login)->first();

    if (!$user) {
        return back()->withInput()->with('login_error', 'Пользователь не найден');
    }

    // Проверяем правильность пароля
    if (!Auth::attempt(['email' => $request->login, 'password' => $request->password], $request->remember)) {
        return back()->withInput()->with('password_error', 'Неверный пароль');
    }

    // Авторизация успешна
    return redirect()->intended(
        $request->get('redirect', '/')
    );
}

    // Выход
public function logout(Request $request)
{
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // получаем redirect из формы
    $redirect = $request->input('redirect', '/');

    // защита от внешних редиректов
    if (! str_starts_with($redirect, '/')) {
        $redirect = '/';
    }

    return redirect($redirect);
    
}


}