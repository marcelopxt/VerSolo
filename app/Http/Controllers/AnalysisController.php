<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    public function uploadFiles()
    {
        return view('analisys.blade.php');
    }

    public function reportFiles()
    {
        // manda o request para PdfController que retorna o $report
        return view('analisys.blade.php');
        // return view('analisys.blade.php', $report); <- $report conterá os dados de resultado da consulta (protocolo, )
    }
}
