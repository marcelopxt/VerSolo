<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;

class PdfController extends Controller
{
    public function readPdf(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf'
        ]);

        $pdfFile = $request->file('file')->getPathname();

        $parser = new Parser();
        $pdf = $parser->parseFile($pdfFile);

        $text = $pdf->getText();
        $metadata = $pdf->getDetails();

        // dd($text, $metadata);

        return $text;
    }
}
