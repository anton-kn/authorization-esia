<?php

namespace App\Http\Controllers;

use App\Http\UseCase\Authorization;
use GuzzleHttp\Cookie\SetCookie;
use Illuminate\Http\Request;
use App\Http\UseCase\ReceptionDocument;

/**
 * Подача документов
 */
class ReceptionDocumentController extends Controller
{
    /**
     * Сохраняем документы
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function store(Request $request)
    {
        // сохраним документы
        (new ReceptionDocument())->storeDocument($request);

        // авторизуемся на сайте через госуслуги
        (new Authorization())->login($request);

        return redirect()->route("login-sms");
    }


    /**
     * Загружаем документы на stavmirsud после регистрации
     * @param Request $request
     * @return string
     */
    public function upload(Request $request): string
    {
        (new ReceptionDocument())->uploadDocument($request);

        $cookies = session('cookies');
        foreach ($cookies->toArray() as $cookie) {
            $cookieSet = new SetCookie($cookie);
        }

        return "Ok";
    }
}