<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RandomNumberController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\CityController;




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

require __DIR__.'/auth.php';

// Маршруты аутентификации
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/', [DoctorController::class, 'index']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::get('pets', function () {    return view('pages.pets');})->name('pets');


// Защищённый маршрут
Route::get('/home', function () {
    return view('welcome');
})->middleware('auth');

Route::get('/profile', function () {
    // Только для аутентифицированных пользователей
})->middleware('auth');

Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
Route::post('/cities/set', [CityController::class, 'set'])->name('cities.set');


Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
Route::get('/cities/search', [CityController::class, 'search'])->name('cities.search');



use App\Models\City;

Route::get('/test-city', function () {
    return City::count();
});
