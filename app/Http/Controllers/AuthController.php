<?php

namespace App\Http\Controllers;
    use App\Rules\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

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
        'phone' => 'required', new PhoneNumber, 'unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = User::create([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'phone' => $validatedData['phone'],
        'password' => bcrypt($validatedData['password']),
    ]);

    Auth::login($user);

    return redirect('/home');
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
    return redirect()->intended('/');
}

    // Выход
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

}