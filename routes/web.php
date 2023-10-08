<?php

use App\Http\Controllers\AuthorizationController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('home');
});

Route::get('/login', function () {
    return view("login");
})->name('login');

Route::get('/login-sms', function () {
    return view("login-sms");
})->name('login-sms');

Route::post('/login', [AuthorizationController::class, 'login'])->name('login');

Route::post('/login-sms', [AuthorizationController::class, 'sendCodeFromSms'])->name('sendCode');