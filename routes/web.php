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
    ReviewController,
    FieldOfActivityController,
    AddDoctorController,
    OrganizationController,
    HomeController,
    testController,
    SpecialistCreateController,
    SpecialistController,
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 🏠 Главная
Route::get('/', [DoctorController::class, 'welcome'])->name('welcome');
Route::get('/', [HomeController::class, 'index']);
Route::get('/home', [DoctorController::class, 'welcome'])->name('welcome');
Route::get('/test', [testController::class, 'test'])->name('test');

Route::get('legal/about', function () {
    return view('pages.legal.about');
})->name('legal/about');
Route::get('legal/faq', function () {
    return view('pages.legal.faq');
})->name('legal/faq');
Route::get('legal/terms', function () {
    return view('pages.legal.terms');
})->name('legal/terms');
Route::get('legal/glossary', function () {
    return view('pages.legal.glossary');
})->name('legal/glossary');

Route::get('legal/privacy', function () {
    return view('pages.legal.privacy');
})->name('legal/privacy');
Route::get('legal/news', function () {
    return view('pages.legal.news');
})->name('legal/news');

// 🔐 Аутентификация
Auth::routes();
require __DIR__ . '/auth.php';

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
    // 🧑‍⚕️ Профиль пользователя
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});
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
Route::get('/animals', [PetController::class, 'showAnimalTypes'])->name('animals.index');
Route::get('/pets', [PetController::class, 'index'])->name('pets.index');
Route::post('/pets', [PetController::class, 'store'])->name('pets.store');
Route::get('/pets/{pet}', [PetController::class, 'show'])->name('pets.show');
Route::put('/pets/{pet}', [PetController::class, 'update'])->name('pets.update');
Route::delete('/pets/{pet}', [PetController::class, 'destroy'])->name('pets.destroy');

// Страница списка пород
Route::get('/animals/{species_slug}', [PetController::class, 'showBreeds'])->name('animals.breeds');

// Страница конкретной породы 
Route::get('/animals/{species_slug}/{breed_slug}', [PetController::class, 'showBreedPage'])->name('animals.breed.details');


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

Route::get('/doctors/update/{slug}', [DoctorController::class, 'update'])->name('doctors.update');
// Доктор Редактирование
Route::post('/doctors/{id}/update', [DoctorController::class, 'update'])
    ->name('doctor.update')
    ->middleware('auth'); // при необходимости добавь middleware
Route::post('/doctors/store', [DoctorController::class, 'store'])->name('doctors.store');
Route::post('/add-doctor', [AddDoctorController::class, 'store'])->name('add.doctor');
Route::post('/clinics/store', [ClinicController::class, 'store'])->name('clinics.store');
Route::get('/doctors/{doctor:slug}', [DoctorController::class, 'show'])
    ->name('doctors.show');

Route::get('/api/fields/vetclinic', [FieldOfActivityController::class, 'getVetclinic']);
Route::get('/api/fields/specialists', [FieldOfActivityController::class, 'getSpecialists']);


// возвращает города для региона (используется в модалке)
Route::get('/api/cities/by-region/{region}', [\App\Http\Controllers\CityController::class, 'citiesByRegion']);


Route::post('/add-organization', [OrganizationController::class, 'submit'])
    ->name('add-organization');
Route::post('/submit-organization', [OrganizationController::class, 'submit'])->name('submit-organization');

Route::get('/clinics/{clinic:slug}', [ClinicController::class, 'show'])
    ->name('clinics.show');

Route::post('/add-specialist', [SpecialistCreateController::class, 'store']);

Route::middleware(['auth'])->group(function () {

    // создание (у тебя уже используется)
    Route::post('/specialist', [SpecialistController::class, 'store'])
        ->name('specialist.store');

    // редактирование профиля специалиста
    Route::get('/account/specialist/{specialist}/edit', [SpecialistController::class, 'edit'])
        ->name('specialist.edit');

    Route::put('/account/specialist/{specialist}/update', [SpecialistController::class, 'update'])
        ->name('specialist.update');

    Route::resource('organizations-profile', OrganizationController::class);
    // Маршрут для показа формы редактирования
    Route::get('/organizations-profile/{id}/edit', [OrganizationController::class, 'edit'])->name('organizations-profile.edit');

    // Маршрут для сохранения
    Route::put('/organizations-profile/{id}', [OrganizationController::class, 'update'])->name('organizations-profile.update');
});

Route::get('/get-organizations/{city_id}', function ($city_id) {
    $organizations = \App\Models\Organization::where('city_id', $city_id)
        ->select('id', 'name')
        ->get();
    return response()->json($organizations);
});

Route::delete('/specialist/{specialist}', [SpecialistController::class, 'destroy'])->name('specialist.destroy');

Route::get('/api/clinics-search', [ClinicController::class, 'liveSearch'])->name('api.clinics.search');

Route::get('/get-organizations-by-city-id/{city_id}', function ($city_id) {
    $city = \App\Models\City::find($city_id);
    if (!$city) return response()->json([]);

    // Ищем организации, где текстовое поле city совпадает с именем города
    $organizations = \App\Models\Organization::where('city', $city->name)
        ->select('id', 'name')
        ->orderBy('name')
        ->get();

    return response()->json($organizations);
});
Route::get('/doctors/{specialist:slug}', [DoctorController::class, 'show'])
    ->name('doctors.show');

Route::delete('/organizations/{id}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');


// Было (примерно):
Route::get('/organizations/{organization}', [OrganizationController::class, 'show']);

// Нужно сделать (добавить ->name):
Route::get('/organizations/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');
Route::put('/doctor/{doctor}', [DoctorController::class, 'update'])->name('doctor.update');
Route::delete('/doctor/{doctor}', [DoctorController::class, 'destroy'])->name('doctor.destroy');

// Маршрут для страницы всех специалистов
Route::get('/specialists', [SpecialistController::class, 'index'])->name('specialists.index');
// Просмотр карточки конкретного специалиста
Route::get('/specialists/{slug}', [App\Http\Controllers\SpecialistController::class, 'show'])->name('specialists.show');
