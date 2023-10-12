<?php

namespace App\Http\UseCase;

use Illuminate\Http\Request;


/**
 * Подача документов
 */
class ReceptionDocument
{

    public function handler(Request $request)
    {
        dd($request);
    }
}