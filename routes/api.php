<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API 管理
Route::pattern('apiAdmin', 'admin');
Route::group(['namespace' => 'App\Http\Controllers\Admin', 'prefix' => '{apiAdmin}'], function() {
    // カレンダー情報取得
    Route::post('calendar/fetch', 'CalendarController@fetch')->name('calendar_fetch');
    // 予約登録(カレンダー用)
    Route::post('calendar/add_reserve', 'CalendarController@addRserve')->name('calendar_add_reserve');
    // 予約キャンセル(カレンダー用)
    Route::post('calendar/cancel_reserve', 'CalendarController@cancelRserve')->name('calendar_cancel_reserve');
});