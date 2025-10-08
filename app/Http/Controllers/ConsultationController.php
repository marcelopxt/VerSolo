<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\Company;
use App\Models\Consultation;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultationController extends Controller
{
    public function store(array $allAnalyses)
    {
        $employee = Auth::user();

        DB::transaction(function () use ($allAnalyses, $employee) {
            $totalProtocols = 0;

            // dentro de all analysys tem um array p cada arquivo
            foreach ($allAnalyses as $fileAnalysis) {
                // dentro de cada array de arquivo tem o array de diagnósticos por análise
                if (is_array($fileAnalysis)) {
                    $totalProtocols += count($fileAnalysis);
                }
            }

            $consultation = Consultation::create([
                'qt_analysis' => $totalProtocols,
                'date' => now(),
                'employee_id' => $employee->id,
                'company_id' => $employee->company_id
            ]);

       
            foreach ($allAnalyses as $fileAnalysis) {
                // dentro de cada array de arquivo tem o array de diagnósticos por análise
                if (is_array($fileAnalysis)) {
                    $totalProtocols += count($fileAnalysis);
                }
            }

            foreach ($allAnalyses as $fileName => $fileAnalysis) {
                if (!is_array($fileAnalysis)) continue;

                foreach ($fileAnalysis as $protocol => $report) {
                    // ignora se tiver erro na leitura e a análise nao foi feita
                    if (isset($report['erro'])) continue;

                    Analysis::create([
                        'consultation_id' => $consultation->id,
                        'protocol' => $protocol,
                        'report' => json_encode($report),
                        'status' => $report['status']
                    ]);
                }
            }
        });
    }
}
