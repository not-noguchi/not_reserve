<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Route::get('/home', function () {
//     return view('home');
// })->middleware(['auth', 'verified'])->name('home');

Route::namespace('App\Http\Controllers')->middleware('auth')->group(function () {
    Route::get('/profile', 'ProfileController@edit')->name('profile.edit');
    Route::patch('/profile', 'ProfileController@update')->name('profile.update');
    Route::delete('/profile', 'ProfileController@destroy')->name('profile.destroy');
});

//Route::namespace('App\Http\Controllers\User')->middleware('auth')->group(function() {
Route::group(['namespace' => 'App\Http\Controllers\User', 'middleware' => ['auth']], function() {
    // Home情報取得
    Route::get('/home', 'HomeController@index')->name('home.index');
    // カレンダー情報取得
    Route::get('/reserve', 'ReserveController@index')->name('reserve.index');
});


require __DIR__.'/auth.php';

Route::get('/admin/calendar', function () {
    return view('admin_calendar');
});
