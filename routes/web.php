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
Route::get('/', [DoctorController::class, 'welcome'])->name('welcome');

Route::get('/test', function () {
    return view('pages.clinics.tabs.test');
})->name('clinics.tabs.test');



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
Route::post('/account/update-city', [ProfileController::class, 'updateCity'])->name('account.updateCity');

Route::get('/api/clinics/by-city/{city}', function ($cityId) {
    return \App\Models\Clinic::where('city', $cityId)->get(['id', 'name']);
});




Route::get('/api/clinics/by-city/{cityId}', [ClinicController::class, 'clinicsByCity']);



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

    // 🧾 Отзывы пользователя (всегда для текущего пользователя)
    Route::get('/account/reviews', [AccountController::class, 'getReviews'])
        ->name('account.reviews');

    // ✅ Обновление, удаление и управление отзывами
    Route::post('/reviews/{id}', [AccountController::class, 'updateReview'])->name('reviews.update');
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy');



    // ✅ Удаление фото и чеков
    Route::delete('/review_photos/{id}', [AccountController::class, 'deletePhoto'])->name('review_photos.delete');
    Route::delete('/review_receipts/{id}', [AccountController::class, 'deleteReceipt'])->name('review_receipts.delete');


    // 🐾 Питомцы
    Route::get('/pets', [PetController::class, 'index'])->name('pets.index');
    Route::post('/pets', [PetController::class, 'store'])->name('pets.store');
    Route::get('/pets/{pet}', [PetController::class, 'show'])->name('pets.show');
    Route::put('/pets/{pet}', [PetController::class, 'update'])->name('pets.update');
    Route::delete('/pets/{pet}', [PetController::class, 'destroy'])->name('pets.destroy');

    // 🧑‍⚕️ Профиль пользователя
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});

Route::get('/breeds', [PetController::class, 'getBreeds']);

// 🏥 Клиники и отзывы (публичные)
Route::resource('clinics', ClinicController::class);
Route::resource('reviews', ReviewController::class);
Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

// 👤 Публичный профиль пользователя
Route::get('/user/{id}', function ($id) {
    $user = \App\Models\User::findOrFail($id);
    return view('pages.user.profile', compact('user'));
})->name('user.profile');

// Страница всех докторов
Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors.index');

// Страница одного доктора
Route::get('/doctors/{id}', [DoctorController::class, 'show'])->name('doctors.show');
Route::get('/doctors/update/{id}', [DoctorController::class, 'update'])->name('doctors.update');


// Доктор Редактирование
Route::post('/doctors/{doctor}/update', [DoctorController::class, 'update'])
    ->name('doctor.update')
    ->middleware('auth'); // при необходимости добавь middleware


Route::post('/doctors/store', [DoctorController::class, 'store'])->name('doctors.store');
Route::post('/clinics/store', [ClinicController::class, 'store'])->name('clinics.store');
