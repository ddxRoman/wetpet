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
    $credentials = $request->validate([
        'login' => 'required|string', // Может быть email или phone
        'password' => 'required|string',
    ]);
    

    // Определяем, что введено - email или телефон
    $field = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
    
    if (Auth::attempt([$field => $credentials['login'], 'password' => $credentials['password']], $request->remember)) {
        $request->session()->regenerate();
        return redirect()->intended('/home');
    }

    return back()->withErrors([
        'login' => 'Неверные учетные данные.',
    ]);
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