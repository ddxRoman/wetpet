<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PetController;
use Illuminate\Support\Facades\Auth;
use App\Models\City;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
Route::post('/test-pets', function (\Illuminate\Http\Request $r) {
    return response()->json(['ok' => true, 'data' => $r->all()]);
});


// 🏠 Главная
Route::get('/', [DoctorController::class, 'index'])->name('home');

// 🔐 Аутентификация
Auth::routes();
require __DIR__.'/auth.php';

// Регистрация / Логин / Логаут
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 🏙 Города
Route::get('/cities/all', [CityController::class, 'all'])->name('cities.all');
Route::post('/cities/add', [CityController::class, 'add'])->name('cities.add');
Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
Route::post('/cities/set', [CityController::class, 'set'])->name('cities.set');Route::get('/cities/search', [CityController::class, 'search'])->name('cities.search');
Route::post('/account/update-city', [AccountController::class, 'updateCity'])->name('account.updateCity');

// 📧 Сброс пароля
Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('reset/password', [ResetPasswordController::class, 'reset'])->name('password.update');

// 🔒 Защищённые маршруты
Route::middleware(['auth'])->group(function () {

    // 👤 Личный кабинет
    Route::get('/account', [AccountController::class, 'index'])->name('account');
    Route::post('/account/profile', [AccountController::class, 'updateProfile'])->name('account.updateProfile');

    // 🐾 Питомцы
    Route::get('/pets', [PetController::class, 'index'])->name('pets.index');
    Route::post('/pets', [PetController::class, 'store'])->name('pets.store');

    // 🧑‍⚕️ Профиль пользователя
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});

Route::delete('/pets/{pet}', [App\Http\Controllers\PetController::class, 'destroy'])->name('pets.destroy');


Route::get('/pets/{pet}', [PetController::class, 'show'])->name('pets.show');
Route::put('/pets/{pet}', [PetController::class, 'update'])->name('pets.update');

