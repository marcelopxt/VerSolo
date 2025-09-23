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
        $file = $request->file('file');

        if ($file) {
            $filename = $file->getClientOriginalName();
        }

        return view('analysis', ['filename' => $filename]);
    }

}
