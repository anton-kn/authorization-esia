<?php

use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\ReceptionDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/test', [ReceptionDocumentController::class, 'test']);

Route::get('/', function () {
    return view('home');
});

// загрузка документов
Route::get('/document', function () {
    return view("upload-document");
})->name('document');
Route::post('/document', [ReceptionDocumentController::class, 'store'])->name('store');

// авторизация
Route::post('/login', [AuthorizationController::class, 'login']);
Route::get('/login-sms', function () {
    return view("login-sms");
})->name('login-sms');
Route::post('/login-sms', [AuthorizationController::class, 'sendCode'])->name('sendCode');

//Route::get('/captcha', [AuthorizationController::class, 'captcha'])->name('captcha');

Route::get('/document/upload', [ReceptionDocumentController::class, 'upload']);