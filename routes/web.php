<?php

use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\ReceptionDocumentController;
use Illuminate\Support\Facades\Route;

// Route::get('/test', [AuthorizationController::class, 'test']);

Route::get('/', function () {
    return view('home');
});

Route::get('/login', function () {
    return view("login");
})->name('login');

Route::get('/login-sms', function () {
    return view("login-sms");
})->name('login-sms');

Route::get('/captcha', [AuthorizationController::class, 'captcha'])->name('captcha');

Route::post('/login', [AuthorizationController::class, 'login'])->name('login');

Route::get('/document', function () {
    return view("login");
})->name('document');

Route::post('/document', [ReceptionDocumentController::class, 'store'])->name('store');

Route::get('/test', [ReceptionDocumentController::class, 'test']);

Route::get('/upload/document', [ReceptionDocumentController::class, 'upload']);

Route::post('/login-sms', [AuthorizationController::class, 'sendCodeFromSms'])->name('sendCode');