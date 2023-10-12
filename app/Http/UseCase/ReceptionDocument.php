<?php

namespace App\Http\UseCase;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


/**
 * Подача документов
 */
class ReceptionDocument
{

    /**
     * Сохраняем документы
     * @param Request $request
     */
    public function storeDocument(Request $request)
    {
        // сохраним файлы
        $clientFile = [];
        foreach ($request->file("file") as $file) {
            $clientFile[] = $file->getClientOriginalName();
            $file->storeAs("files", $file->getClientOriginalName());
        }

        session(['file' => $clientFile]);

    }

    /**
     * Загружаем документы
     */
    public function uploadDocument(Request $request)
    {
        $cookies = session('cookies');
        $file = session('file');

        $client = new Client([
            'headers' => [
                "User-Agent" => $request->headers->get('user-agent'),
            ],
            'cookies' => $cookies
        ]);



        Log::debug("Куки из сессии для stavmirsud ", (array) $cookies);

        $body = Utils::tryFopen('../storage/app/public/files/' . $file[0], 'r');
        $resUploadFile = $client->post("https://lk.stavmirsud.ru/lk/upload", [
            'cookies' => $cookies,
            'body' => $body,
            'headers' => [
                "User-Agent" => $request->headers->get('user-agent'),
                // "Accept" => "application/json, text/plain, */*",
                // "Accept-Language" => "en-US,en;q=0.5",
                // "Content-Type" => "application/json",
                // "Cache-Control" => "no-cache",
                // "Sec-Fetch-Dest" => "empty",
                // "Sec-Fetch-Mode" => "cors",
                // "Sec-Fetch-Site" => "same-origin",
                // "Referer" => 'https://lk.stavmirsud.ru/lk/docs/add'
            ],
            "mode" => "cors",
            "credentials" => "include",
        ]);

        $bodyContent = $resUploadFile->getBody()->getContents();

        Log::debug("Содержание ответа отправки первого файла", (array) $bodyContent);

        $body = Utils::tryFopen('../storage/app/public/files/' . $file[1], 'r');
        $resUploadFile = $client->post("https://lk.stavmirsud.ru/lk/upload", [
            'cookies' => $cookies,
            'body' => $body,
            'headers' => [
                "User-Agent" => $request->headers->get('user-agent'),
                // "Accept" => "application/json, text/plain, */*",
                // "Accept-Language" => "en-US,en;q=0.5",
                // "Content-Type" => "application/json",
                // "Cache-Control" => "no-cache",
                // "Sec-Fetch-Dest" => "empty",
                // "Sec-Fetch-Mode" => "cors",
                // "Sec-Fetch-Site" => "same-origin",
                // "Referer" => 'https://lk.stavmirsud.ru/lk/docs/add'
            ],
            "mode" => "cors",
            "credentials" => "include",
        ]);

        $bodyContent = $resUploadFile->getBody()->getContents();

        Log::debug("Содержание ответа отправки второго файла", (array) $bodyContent);





    }
}