<?php

namespace App\Http\UseCase;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Cookie;


class Authorization
{

    /**
     * Получаем каптчу
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function captcha(Request $request, Client $client)
    {
        $cookies = session('cookies');

        $jar = new CookieJar();
        $responseCaptchaType = $client->request('GET', "https://esia.gosuslugi.ru/captcha/api/public/v2/type", [
            'cookies' => $cookies,
        ]);

        $bodyContents = json_decode($responseCaptchaType->getBody()->getContents());

        if (isset($bodyContents)) {
            $responseCaptchaImage = $client->request('GET', "https://esia.gosuslugi.ru/captcha/api/public/v2/image", [
                // 'cookies' => $jar,
                'headers' => [
                    "User-Agent" => $request->headers->get('user-agent'),
                    "Accept" => "*/*",
                    "Accept-Language" => "en-US,en;q=0.5",
                    "pragma" => "no-cache",
                    "expires" => "-1",
                    "cache-control" => "no-cache, no-store, must-revalidate",
                    "captchasession" => $bodyContents->captchaSession,
                    "x-requested-with" => "XMLHttpRequest",
                    "Sec-Fetch-Dest" => "empty",
                    "Sec-Fetch-Mode" => "cors",
                    "Sec-Fetch-Site" => "same-origin"
                ]

            ]);
        }

        $image = $responseCaptchaImage->getBody()->getContents();
        $base64 = base64_encode($image);
        $mime = "image/jpeg";
        $img = ('data:' . $mime . ';base64,' . $base64);
        // $image = "<img src=$img alt='ok'>";
        return view("captcha", [
            "imageCaptcha" => $img
        ]);
    }


    public function sendCaptcha()
    {
        // https: //esia.gosuslugi.ru/captcha/api/public/v2/verify
        // ответ
        // verify_token	"32dfb67f-bade-4bd2-c3c3-301505bc4953"
        // запрос
        // https://esia.gosuslugi.ru/aas/oauth2/api/anomaly/captcha/verify?guid=45972c0d-2273-e744-bd76-912230559830&verify_token=32dfb67f-bade-4bd2-c3c3-301505bc4953

        // await fetch("https://esia.gosuslugi.ru/captcha/api/public/v2/verify", {
        //     "credentials": "include",
        //     "headers": {
        //         "User-Agent": "Mozilla/5.0 (X11; Linux x86_64; rv:108.0) Gecko/20100101 Firefox/108.0",
        //         "Accept": "*/*",
        //         "Accept-Language": "en-US,en;q=0.5",
        //         "pragma": "no-cache",
        //         "expires": "-1",
        //         "cache-control": "no-cache, no-store, must-revalidate",
        //         "captchasession": "cc7718e7-abba-163d-d7cc-2111204adcfd",
        //         "x-requested-with": "XMLHttpRequest",
        //         "content-type": "application/json;charset=utf-8",
        //         "Sec-Fetch-Dest": "empty",
        //         "Sec-Fetch-Mode": "cors",
        //         "Sec-Fetch-Site": "same-origin"
        //     },
        //     "referrer": "https://esia.gosuslugi.ru/login/",
        //     "body": "{\"answer\":\"шд1и4ф\",\"captchaType\":\"esiacaptcha\"}",
        //     "method": "POST",
        //     "mode": "cors"
        // });
    }

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
        $client = new Client([
            'headers' => [
                "User-Agent" => $request->headers->get('user-agent'),
            ],
            'cookies' => $jar
        ]);

        $responseLk = $client->request("GET", "https://lk.stavmirsud.ru/lk", [
            'cookies' => $jar,
        ]);

        $jarCookie = new CookieJar();
        $responseLogin = $client->request("GET", "https://lk.stavmirsud.ru/login", [
            'cookies' => $jarCookie,
        ]);

        $responseGosLogin = $client->request('POST', "https://esia.gosuslugi.ru/aas/oauth2/api/login", [
            'body' => json_encode($dataRequest),
            'cookies' => $jarCookie,
        ]);
        // сохраняем куки только от госуслуг
        session(['cookies' => $jarCookie]);

        $bodyContent = $responseGosLogin->getBody()->getContents();
        $bodyContent = json_decode($bodyContent);
        Log::debug("Содержание тела ", (array) $bodyContent);

        // todo - обходим капчу, если есть
        // todo - надо тут еще забрать guid: 6de0f0a8-1155-91ed-b9f7-802ddf624241
        if (isset($bodyContent->action) && $bodyContent->action == "SOLVE_ANOMALY_REACTION") {
            return $this->captcha($request, $client);
        }

        Log::debug("Статус проверки пароля и почты https://esia.gosuslugi.ru/login/ ", (array) $responseGosLogin->getStatusCode());
        Log::debug("Тело ответа https://esia.gosuslugi.ru/login/ ", (array) $responseGosLogin);

        return redirect()->route("login-sms");

    }

    /**
     * Отправляем код из СМС
     * @param Request $request
     */
    public function sendCodeFromSms(Request $request)
    {
        $cookies = session('cookies');
        $client = new Client([
            'headers' => [
                "User-Agent" => $request->headers->get('user-agent'),
            ],
            'cookies' => $cookies
        ]);
        Log::debug("Куки из сессии: ", (array) $cookies);

        $responseLoginCode = $client->post("https://esia.gosuslugi.ru/aas/oauth2/api/login/otp/verify/", [
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
                "Referer" => 'https://esia.gosuslugi.ru/login/'
            ],
            "mode" => "cors",
            "credentials" => "include",
        ]);

        $bodyContent = $responseLoginCode->getBody()->getContents();
        $bodyContent = json_decode($bodyContent);
        $redirectUrl = $bodyContent->redirect_url;

        Log::debug("Статус отправки кода из СМС ", (array) $responseLoginCode->getStatusCode());
        Log::debug("Содержание тело ответа: ", (array) $bodyContent);
        Log::debug("Ответ: ", (array) $responseLoginCode);

        // Переходим на сайт stavmirsud
        $jar = new CookieJar();
        $responseStavmirsud = $client->request("GET", $redirectUrl, [
            'cookies' => $jar,
        ]);

        // сохраняем куки от stavmirsud
        session(['cookies' => $jar]);

        Log::debug("Сайт судей: ", (array) $responseStavmirsud);
        Log::debug("Куки: ", (array) $jar);

        return redirect()->action([ReceptionDocumentController::class, 'upload']);

        // foreach ($jar->toArray() as $cookie) {
        //     $cookieSet = new SetCookie($cookie);
        // }

        // не переходит по кукам
        // return redirect("https://lk.stavmirsud.ru/lk")->withCookie($cookieSet->__toString());
    }
}