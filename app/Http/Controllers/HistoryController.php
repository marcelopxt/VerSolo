<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consultation;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function history()
    {
        $employee = Auth::user();
        
        // Buscar consultas do funcionário atual com suas análises
        $consultations = Consultation::where('employee_id', $employee->id)
            ->with(['analyses'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('history', compact('consultations'));
    }
}
