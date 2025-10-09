<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\File;

class AnalysisController extends Controller
{
    public function uploadFiles()
    {
        return view('analysis');
    }

    public function reportFiles(Request $request)
    {
        // Se não houver novos arquivos, redireciona para o histórico.
        if (!$request->hasFile('file')) {
             return redirect()->route('history')->with('success', 'Redirecionado para o histórico!');
        }

        $request->validate([
            'file' => 'required|array',
            'file.*' => 'required|mimes:pdf'
        ]);

        $allAnalyses = [];
        $fileNames = [];

        foreach ($request->file('file') as $file) {
            $filename = $file->getClientOriginalName();

            try {
                $pdfController = new PdfController();
                $singleFileRequest = new Request();
                $singleFileRequest->files->set('file', $file);
                $rawText = $pdfController->readPdf($singleFileRequest);

            } catch (\Exception $e) {
                $allAnalyses[$filename] = ['erro' => 'Não foi possível ler o arquivo: ' . $e->getMessage()];
                continue;
            }

            $extractedData = self::extractRegex($rawText);
            $dataDisgnosis = self::reportInterpretation($extractedData);
            $allAnalyses[$filename] = $dataDisgnosis;
            $fileNames[] = $filename;
        }

        // Salvar automaticamente no histórico após processamento
        $consultationId = null;
        $autoSaved = false;
        
        if (!empty($allAnalyses)) {
            try {
                $consultationController = new ConsultationController();
                $consultationId = $consultationController->store($allAnalyses);
                $autoSaved = true;
                
                // Salvar ID da consulta na sessão para evitar duplicatas
                session(['last_consultation_id' => $consultationId]);
            } catch (\Exception $e) {
                $autoSaved = false;
                \Log::error('Erro ao salvar automaticamente no histórico: ' . $e->getMessage());
            }
        }

        return view('analysis', [
            'filenames' => $fileNames,
            'allAnalyses' => $allAnalyses,
            'autoSaved' => $autoSaved,
            'consultationId' => $consultationId
        ]);
    }

    public static function extractRegex($text)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf_text_');
        try {
            if (!empty($text)) {
                if (is_array($text)) {
                    $text = implode("\n", $text);
                }

                $textUtf8 = mb_convert_encoding($text, 'UTF-8', mb_detect_encoding($text));
                file_put_contents($tempFile, $textUtf8);

                $regexFilePath = base_path('resources/js/regex.cjs');
                $command = "node " . escapeshellarg($regexFilePath) . " " . escapeshellarg($tempFile);
                $result = shell_exec($command);

                if ($result === null) {
                    return ['erro' => 'A execução do script Node.js falhou.'];
                }

                return json_decode($result, true);
            }
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
        return null;
    }

    public static function reportInterpretation($json)
    {
        if (empty($json) || isset($json['erro'])) {
            return ['erro' => 'Os dados para análise não foram recebidos corretamente.'];
        }

        $generalRules = config('analise_parametros._regras_gerais');
        $intervalRules = config('analise_parametros.intervalos');
        $phRelationshipV = config('analise_parametros._relacoes.ph_por_v');
        $comparisonRules = config('analise_parametros.comparacoes');
        $rulesbyRangeCa = config('analise_parametros.regras_por_faixa_ca');

        foreach ($json as $protocol => &$dataSample) {
            if (!is_array($dataSample)) continue;

            $hasError = false;

            foreach ($dataSample as $key => &$result) {
                if (!is_array($result) || !isset($result['valor'])) continue;
                $value = $result['valor'];

                if ($generalRules['nao_negativo'] && $value < 0) {
                    $result['diagnostico'] = 'Valor inválido (negativo)';
                    $hasError = true;
                }
                if ($generalRules['nao_zero'] && $value == 0) {
                    $result['diagnostico'] = 'Valor inválido (zero)';
                    $hasError = true;
                }
            }
            unset($result);

            if (isset($dataSample['V'], $dataSample['PH em água'])) {
                $valueV = $dataSample['V']['valor'];
                $valuePh = $dataSample['PH em água']['valor'];
                $diagnosisPh = "N/A";
                foreach ($phRelationshipV as $range) {
                    if ($valueV >= $range['v_min'] && $valueV <= $range['v_max']) {
                        if ($valuePh < $range['ph_min']) {
                            $diagnosisPh = "Abaixo do ideal para V=$valueV (faixa ideal: {$range['ph_min']}-{$range['ph_max']})";
                            $hasError = true;
                        } elseif ($valuePh > $range['ph_max']) {
                            $diagnosisPh = "Acima do ideal para V=$valueV (faixa ideal: {$range['ph_min']}-{$range['ph_max']})";
                            $hasError = true;
                        }
                        break;
                    }
                }
                $dataSample['PH em água']['diagnostico'] = $diagnosisPh;
            }

            foreach ($dataSample as $key => &$result) {
                if (!is_array($result) || !isset($result['valor'])) continue;
                $value = $result['valor'];
                $param = $intervalRules[$key] ?? null;

                if ($param) {
                    if (isset($param['min']) && $value < $param['min']) {
                        $result['diagnostico'] = "Abaixo do ideal (mínimo: {$param['min']})";
                        $hasError = true;
                    } elseif (isset($param['max']) && $value > $param['max']) {
                        $result['diagnostico'] = "Acima do ideal (máximo: {$param['max']})";
                        $hasError = true;
                    }
                }
            }
            unset($result);

            foreach ($comparisonRules as $rule) {
                $el1 = $rule['elemento1'];
                $el2 = $rule['elemento2'];
                if (isset($dataSample[$el1], $dataSample[$el2])) {
                    $val1 = $dataSample[$el1]['valor'];
                    $val2 = $dataSample[$el2]['valor'];
                    if ($val1 >= $val2) {
                        $dataSample[$el1]['diagnostico'] = $rule['mensagem'];
                        $hasError = true;
                    }
                }
            }

            if (isset($dataSample['Ca 2+'])) {
                $valueCa = $dataSample['Ca 2+']['valor'];
                foreach ($rulesbyRangeCa as $condicionalRule) {
                    $range = $condicionalRule['ca_faixa'];
                    $caMin = $range['min'] ?? -INF;
                    $caMax = $range['max'] ?? INF;
                    if ($valueCa >= $caMin && $valueCa < $caMax) {
                        foreach ($condicionalRule['regras'] as $chave => $subRegra) {
                            if (isset($dataSample[$chave])) {
                                $valueEl = $dataSample[$chave]['valor'];
                                $subMin = $subRegra['min'] ?? -INF;
                                $subMax = $subRegra['max'] ?? INF;
                                if ($valueEl < $subMin || $valueEl > $subMax) {
                                    $dataSample[$chave]['diagnostico'] = "Irregular para a faixa de Ca ($valueCa)";
                                    $hasError = true;
                                }
                            }
                        }
                    }
                }
            }

            $dataSample['status'] = $hasError ? 'rejected' : 'approved';
        }
        unset($dataSample);

        return $json;
    }

    public function saveAnalysis(Request $request)
    {
        try {
            $analysisData = $request->input('analysis_data');
            $ignoredErrors = $request->input('ignored_errors', []);
            $consultationId = $request->input('consultation_id');

            if (!empty($analysisData)) {
                $analysisData = json_decode($analysisData, true);
                
                // Aplicar erros ignorados
                if (!empty($ignoredErrors)) {
                    foreach ($analysisData as $filename => &$fileAnalysis) {
                        if (!is_array($fileAnalysis)) continue;

                        foreach ($fileAnalysis as $protocol => &$protocolData) {
                            if (!is_array($protocolData)) continue;

                            $countError = 0;
                            foreach ($protocolData as $key => &$analysisItem) {
                                if ($key === 'status' || !is_array($analysisItem)) continue;

                                if (isset($ignoredErrors[$filename][$protocol][$key])) {
                                    $analysisItem['diagnostico'] = 'N/A';
                                }

                                // Recontar erros após aplicar ignorados
                                if (isset($analysisItem['diagnostico']) && 
                                    $analysisItem['diagnostico'] !== 'N/A' &&
                                    (str_contains($analysisItem['diagnostico'], 'inválido') ||
                                     str_contains($analysisItem['diagnostico'], 'Abaixo') ||
                                     str_contains($analysisItem['diagnostico'], 'Acima') ||
                                     str_contains($analysisItem['diagnostico'], 'Irregular'))) {
                                    $countError++;
                                }
                            }
                            unset($analysisItem);

                            // Atualizar status baseado na contagem de erros
                            $protocolData['status'] = $countError == 0 ? 'approved' : 'rejected';
                        }
                        unset($protocolData);
                    }
                    unset($fileAnalysis);
                }

                $consultationController = new ConsultationController();
                $consultationController->store($analysisData, $consultationId);

                return response()->json([
                    'success' => true,
                    'message' => 'Análise atualizada no histórico com sucesso!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Nenhum dado para salvar.'
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Erro ao salvar análise: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ], 500);
        }
    }
}
