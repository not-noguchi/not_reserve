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

Route::get('/admin/calendar', function () {
    return view('admin_calendar');
});


Route::get('/calendar', function () {
    return view('calendar');
});

Route::get('/calendar2', function () {
    return view('calendar2');
});
