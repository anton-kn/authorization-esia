<?php

namespace App\Http\Controllers;

use App\Http\UseCase\Authorization;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Http\Request;
use App\Http\UseCase\ReceptionDocument;

/**
 * Подача документов
 */
class ReceptionDocumentController extends Controller
{

    public function test(Request $request)
    {
        // dd($request->getSession());
        // $body = Utils::tryFopen('../storage/app/public/files/Test.pdf', 'r');
        // dd($body);
        return redirect()->action([ReceptionDocumentController::class, 'upload']);
    }

    /**
     * Сохраняем документы
     * @param Request $request
     */
    public function store(Request $request)
    {
        // сохраним документы
        (new ReceptionDocument())->storeDocument($request);

        // авторизуемся на сайте через госуслуги
        (new Authorization())->login($request);
    }

    /**
     * Загружаем документы на stavmirsud после регистрации
     */
    public function upload(Request $request)
    {
        (new ReceptionDocument())->uploadDocument($request);
    }
}