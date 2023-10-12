<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\UseCase\ReceptionDocument;

/**
 * Подача документов
 */
class ReceptionDocumentController extends Controller
{
    /**
     * Подать документы
     * @param Request $request
     */
    public function begin(Request $request)
    {
        $reception = new ReceptionDocument();
        $reception->handler($request);
    }
}