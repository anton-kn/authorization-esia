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
     * Суть заявления
     */
    private const STATEMENT = 'STATEMENT';

    /**
     * Приложения к заявлению
     */
    private const ADD_STATEMENT = 'ADD_STATEMENT';

    /**
     * Сохраняем документы
     * @param Request $request
     */
    public function storeDocument(Request $request)
    {
        // сохраним файлы
        $clientFile = [];
        foreach ($request->file("file") as $key => $file) {
            if ($key == self::STATEMENT) {
                $clientFile['STATEMENT'] = $file->getClientOriginalName();
            } else {
                $clientFile['ADD_STATEMENT'][] = $file->getClientOriginalName();
            }
            $file->storeAs("files", $file->getClientOriginalName());
        }

        session(['files' => $clientFile]);
    }

    /**
     * Загружаем документы
     */
    public function uploadDocument(Request $request)
    {
        $cookies = session('cookies');
        $files = session('files');
        $client = new Client([
            'headers' => [
                "User-Agent" => $request->headers->get('user-agent'),
            ],
            'cookies' => $cookies
        ]);

        // переходим на эту страницу, чтобы получить токен - formToken
        $response = $client->get("https://lk.stavmirsud.ru/lk/docs/add", [
            'cookies' => $cookies,
            'headers' => [
                "User-Agent" => $request->headers->get('user-agent'),
                "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8",
                "Accept-Language" => "en-US,en;q=0.5",
                "Upgrade-Insecure-Requests" => "1",
                "Sec-Fetch-Dest" => "document",
                "Sec-Fetch-Mode" => "navigate",
                "Sec-Fetch-Site" => "same-origin"
            ],
        ]);

        // находим токен
        preg_match('/("formToken"+:"+[0-9a-zA-Z._]+")/', (string) $response->getBody()->getContents(), $matches);
        preg_match('/(?<="formToken":")([0-9a-zA-Z._]{0,})/', $matches[0], $token);
        Log::debug("Токен ", (array) $token);

        Log::debug("Куки из сессии для stavmirsud ", (array) $cookies);

        // заявление (в переменной $token два одинаковых номера, нам нужен один $token[0] )
        $this->uloadFile($client, $files[self::STATEMENT], $request, $token[0]);

        // приложения к заявлению
        foreach ($files[self::ADD_STATEMENT] as $key => $file) {

            $this->uloadFile($client, $file, $request, $token[0]);
        }
    }

    /**
     * Guzzle загрузка файлов
     * @param Client $client
     * @param string $file
     * @param Request $request
     * @param string $token
     */
    private function uloadFile(Client $client, string $file, Request $request, string $token)
    {
        $body = Utils::tryFopen('../storage/app/public/files/' . $file, 'r');
        $resUploadFile = $client->post("https://lk.stavmirsud.ru/lk/upload", [
            'body' => '-----------------------------36921582892111143459949962695' . "\r\n" . 'Content-Disposition: form-data;
            name="formToken"' . "\r\n\r\n" . $token . "\r\n" . '-----------------------------36921582892111143459949962695' . "\r\n" . 'Content-Disposition: form-data;
            name="chunk"; filename="blob"' . "\r\n" . 'Content-Type: application/octet-stream' . "\r\n\r\n" . $body . "\n\r\n-----------------------------36921582892111143459949962695--\r\n",
            'headers' => [
                "User-Agent" => $request->headers->get('user-agent'),
                "Accept" => "application/json, text/plain, */*",
                "Accept-Language" => "en-US,en;q=0.5",
                "Content-Disposition" => 'attachment; filename=\"' . $file . '\"',
                "Content-Type" => "multipart/form-data; boundary=---------------------------36921582892111143459949962695",
                "Sec-Fetch-Dest" => "empty",
                "Sec-Fetch-Mode" => "cors",
                "Sec-Fetch-Site" => "same-origin",
                "Referer" => 'https://lk.stavmirsud.ru/lk/docs/add',
            ],
            "mode" => "cors",
            "credentials" => "include",
        ]);

        $bodyContent = $resUploadFile->getBody()->getContents();

        Log::debug("Статус загрузки заявления: {$file}", (array) $resUploadFile->getStatusCode());
        Log::debug("Тело ответа результата загрузки заявления {$file}", (array) $bodyContent);
    }
}