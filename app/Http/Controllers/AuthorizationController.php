<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Summary of AuthorizationController
 */
class AuthorizationController extends Controller
{

    /**
     * Вход на ресурс
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function login(Request $request)
    {
        $dataRequest = [
            "login" => $request->email,
            "password" => $request->password,
        ];

        $jar = new CookieJar();
        $client = new Client();
        $response = $client->request("GET", "https://lk.stavmirsud.ru/lk", [
            'cookies' => $jar
        ]);

        $response = $client->request("GET", "https://esia.gosuslugi.ru/login/", [
            'cookies' => $jar
        ]);

        $res = $client->request('POST', "https://esia.gosuslugi.ru/aas/oauth2/api/login", [
            'body' => json_encode($dataRequest),
            'cookies' => $jar
        ]);

        Log::debug("1. Статус проверки пароля и почты ", (array) $response->getStatusCode());

        session(['cookies' => $jar]);

        return redirect()->route("login-sms");

    }

    /**
     * Отправляем код из СМС
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function sendCodeFromSms(Request $request)
    {
        $cookies = session('cookies');

        $client = new Client();

        Log::debug("2. cookies: ", (array) $cookies);

        $res = $client->request('POST', "https://esia.gosuslugi.ru/aas/oauth2/api/login/otp/verify/", [
            'cookies' => $cookies,
            'query' => ["code" => $request->code],
            'headers' => [
                "User-Agent" => $request->headers->get('user-agent'),
                "Accept" => "application/json, text/plain, */*",
                "Accept-Language" => "en-US,en;q=0.5",
                "Content-Type" => "application/json",
                "Cache-Control" => "no-cache",
                "Sec-Fetch-Dest" => "empty",
                "Sec-Fetch-Mode" => "cors",
                "Sec-Fetch-Site" => "same-origin",
            ],
        ]);

        Log::debug("3. Результат: ", (array) $res);
        Log::debug("4. Статус отправки СМС ", (array) $res->getStatusCode());

        $responseStavmirsud = $client->request("GET", "https://lk.stavmirsud.ru/lk/account", [
            'cookies' => $cookies
        ]);
        Log::debug("5. responseStavmirsud: ", (array) $responseStavmirsud);
        Log::debug("6. cookies: ", (array) $cookies);

        $cookieAr = [];
        foreach ($cookies->toArray() as $cookie) {
            $cookieSet = new SetCookie($cookie);
            $cookieAr[] = Cookie::fromString($cookieSet->__toString());
        }

        return redirect("https://lk.stavmirsud.ru/lk/account")->withCookies($cookieAr);
    }
}