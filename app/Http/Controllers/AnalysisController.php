<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\PdfController;

class AnalysisController extends Controller
{
    public function uploadFiles()
    {
        return view('analysis');
    }

    public function reportFiles(Request $request)
    {
        // $request->validate([
        //     'file' => 'required|mimes:pdf'
        // ]);

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $report = []; 

        try {
            $pdfController = new PdfController();
            $report = $pdfController->readPdf($request);

        } catch (\Exception $e) {
            dd($e);
        }

        return view('analysis', [
            'filename' => $filename,
            'report' => $report
        ]);
    }

}
