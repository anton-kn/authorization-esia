<?php

namespace App\Http\Controllers;

use App\Http\UseCase\Authorization;
use Illuminate\Http\Request;

/**
 * Авторизация
 */
class AuthorizationController extends Controller
{
    /**
     * Отправляем код из СМС
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function sendCode(Request $request)
    {
        (new Authorization())->sendCodeFromSms($request);

        return redirect()->action([ReceptionDocumentController::class, 'upload']);
    }
}