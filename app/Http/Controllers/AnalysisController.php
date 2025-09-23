<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    public function uploadFiles()
    {
        return view('analysis');
    }

    public function reportFiles(Request $request)
    {
        // manda o request para PdfController que retorna o $report
        $pdfController = new PdfController();
        $report = $pdfController->readPdf($request);
        return view('analysis', compact('report'));
        // return view('analisys.blade.php', $report); <- $report conterá os dados de resultado da consulta (protocolo, )
    }
}
