<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\{
    ClinicController,
    AuthController,
    AccountController,
    DoctorController,
    CityController,
    Auth\ForgotPasswordController,
    Auth\ResetPasswordController,
    ProfileController,
    PetController,
    ReviewController
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

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
Route::post('/cities/set', [CityController::class, 'set'])->name('cities.set');
Route::get('/cities/search', [CityController::class, 'search'])->name('cities.search');

// 📧 Сброс пароля
Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('reset/password', [ResetPasswordController::class, 'reset'])->name('password.update');

// ======================================================
// 🔒 ЗАЩИЩЁННЫЕ маршруты (только для авторизованных)
// ======================================================
Route::middleware(['auth'])->group(function () {

    // 👤 Личный кабинет
    Route::get('/account', [AccountController::class, 'index'])->name('account');
    Route::post('/account/profile', [AccountController::class, 'updateProfile'])->name('account.updateProfile');
    Route::post('/account/update-city', [AccountController::class, 'updateCity'])->name('account.updateCity');

    // 🧾 Отзывы пользователя
    Route::get('/account/reviews/{user}', [AccountController::class, 'getReviews'])
        ->name('account.reviews');

    // ✅ Обновление отзыва (используется при сохранении)
    Route::post('/reviews/{id}', [AccountController::class, 'updateReview'])
        ->name('reviews.update');

    // ✅ Удаление отзыва (fetch DELETE)
    Route::delete('/reviews/{id}', [AccountController::class, 'deleteReview'])
        ->name('reviews.delete');

    // ✅ Удаление фото из отзыва
    Route::delete('/review_photos/{id}', [AccountController::class, 'deletePhoto'])
        ->name('review_photos.delete');

    // ✅ Удаление чека из отзыва
    Route::delete('/review_receipts/{id}', [AccountController::class, 'deleteReceipt'])
        ->name('review_receipts.delete');

    // 🐾 Питомцы
    Route::get('/pets', [PetController::class, 'index'])->name('pets.index');
    Route::post('/pets', [PetController::class, 'store'])->name('pets.store');
    Route::get('/pets/{pet}', [PetController::class, 'show'])->name('pets.show');
    Route::put('/pets/{pet}', [PetController::class, 'update'])->name('pets.update');
    Route::delete('/pets/{pet}', [PetController::class, 'destroy'])->name('pets.destroy');



Route::middleware(['auth'])->group(function () {
    Route::get('/pets', [PetController::class, 'index']);
    Route::post('/pets', [PetController::class, 'store']);
    Route::delete('/pets/{id}', [PetController::class, 'destroy']);
});


    // 🧑‍⚕️ Профиль пользователя
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});

Route::get('/breeds', [PetController::class, 'getBreeds']);


// 🏥 Клиники и отзывы (публичные)
Route::resource('clinics', ClinicController::class);
Route::resource('reviews', ReviewController::class);
Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

// 👤 Страница пользователя
Route::get('/user/{id}', function ($id) {
    $user = \App\Models\User::findOrFail($id);
    return view('pages.user.profile', compact('user'));
})->name('user.profile');
