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

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');


Route::namespace('App\Http\Controllers')->middleware('auth')->group(function () {
    Route::get('/profile', 'ProfileController@edit')->name('profile.edit');
    Route::patch('/profile', 'ProfileController@update')->name('profile.update');
    Route::delete('/profile', 'ProfileController@destroy')->name('profile.destroy');
});

Route::group(['namespace' => 'App\Http\Controllers\User', 'middleware' => ['auth']], function() {
    // Home情報取得
    Route::get('/', 'HomeController@index')->name('home.index01');
    Route::get('/home', 'HomeController@index')->name('home.index02');
    // カレンダー情報取得
    Route::get('/reserve', 'ReserveController@index')->name('reserve.index');
});

require __DIR__.'/auth.php';

// 管理画面
Route::group(['namespace' => 'App\Http\Controllers\Admin', 'middleware' => ['basicAuthAdmin']], function() {
    // 予約カレンダー
    Route::get('/admin/calendar', 'CalendarController@index')->name('admin.calendar');
    // スケジュール設定
    Route::get('/admin/schedule', 'ScheduleController@index')->name('admin.schedule');
});