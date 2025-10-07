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
        $request->validate([
            'file' => 'required|array',
            'file.*' => 'required|mimes:pdf'
        ]);

        $allAnalyses = [];
        $fileNames = [];

        foreach ($request->file('file') as $file) {
            $filename = $file->getClientOriginalName();
            $rawText = [];

            try {

                $pdfController = new PdfController();
                $singleFileRequest = new Request();
                $singleFileRequest->files->set('file', $file);
                $rawText = $pdfController->readPdf($singleFileRequest);

            } catch (\Exception $e) {
                $allAnalyses[$filename] = ['erro' => 'Não foi possível ler o arquivo: ' . $e->getMessage()];
                continue;
            }

            $extractedData = AnalysisController::extractRegex($rawText);
            $dataDisgnosis = AnalysisController::reportInterpretation($extractedData);

            $allAnalyses[$filename] = $dataDisgnosis;
            $fileNames[] = $filename;
        }

        return view('analysis', [
            'filenames' => $fileNames,
            'allAnalyses' => $allAnalyses
        ]);
    }

    public static function extractRegex($text)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf_text_');

        // DEIXEI LINHA COMENTADA SIM MAS É PRA DEBUG, SE PRECISAR TA AQUI

        // $debugPath = storage_path('app/debug');
        // File::ensureDirectoryExists($debugPath);
        // $debugFileName = uniqid('extracao_', true) . '.txt';
        // $tempFile = $debugPath . '/' . $debugFileName;

        try {
            if (!empty($text)) {
                if (is_array($text)) {
                    $text = implode("\n", $text);
                }

                $textUtf8 = mb_convert_encoding($text, 'UTF-8', mb_detect_encoding($text));
                file_put_contents($tempFile, $textUtf8);

                $regexFilePath = base_path('resources\js\regex.cjs');

                $command = "node " . escapeshellarg($regexFilePath) . " " . escapeshellarg($tempFile);

                $result = shell_exec($command);

                if ($result == null) {
                    return ['erro' => 'A execução do script Node.js falhou.'];
                }

                return json_decode($result, true);
            }
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
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

            // Regras Gerais
            foreach ($dataSample as $key => &$result) {
                if (!isset($result['valor']))
                    continue;

                $value = $result['valor'];

                if ($generalRules['nao_negativo'] && $value < 0) {
                    $result['diagnostico'] = 'Valor inválido (negativo)';
                    continue;
                }

                if ($generalRules['nao_zero'] && $value == 0) {
                    $result['diagnostico'] = 'Valor inválido (zero)';
                    continue;
                }
            }

            // Regra Condicional (pH por V%)
            if (isset($dataSample['V']) && isset($dataSample['PH em água'])) {
                $valueV = $dataSample['V']['valor'];
                $valuePh = $dataSample['PH em água']['valor'];
                $diagnosisPh = "Sem referência de V%";

                foreach ($phRelationshipV as $range) {
                    if ($valueV >= $range['v_min'] && $valueV <= $range['v_max']) {
                        if ($valuePh < $range['ph_min']) {
                            $diagnosisPh = "Abaixo do ideal para V=$valueV (faixa ideal: {$range['ph_min']}-{$range['ph_max']})";

                        } elseif ($valuePh > $range['ph_max']) {
                            $diagnosisPh = "Acima do ideal para V=$valueV (faixa ideal: {$range['ph_min']}-{$range['ph_max']})";

                        } else {
                            $diagnosisPh = 'Dentro do ideal para o V%';
                        }
                        break;
                    }
                }
                $dataSample['PH em água']['diagnostico'] = $diagnosisPh;
            }

            // Regras de Intervalo Simples
            foreach ($dataSample as $key => $result) {
                $value = $result['valor'];
                $param = $intervalRules[$key] ?? null;

                if ($param) {
                    if (isset($param['min']) && $value < $param['min']) {
                        $result['diagnostico'] = "Abaixo do ideal (mínimo: {$param['min']})";

                    } elseif (isset($param['max']) && $value > $param['max']) {
                        $result['diagnostico'] = "Acima do ideal (mínimo: {$param['max']})";

                    } else {
                        $result['diagnostico'] = "Dentro do ideal";
                    }
                }
            }

            // Regras de comparativo
            foreach ($comparisonRules as $rule) {
                $el1 = $rule['elemento1'];
                $el2 = $rule['elemento2'];

                if (isset($dataSample[$el1], $dataSample[$el2])) {
                    $val1 = $dataSample[$el1]['valor'];
                    $val2 = $dataSample[$el2]['valor'];

                    // A regra é 'Mg < Ca', então se Mg for >= Ca, está irregular
                    if ($val1 >= $val2) {
                        $dataSample[$el1]['diagnostico'] = $rule['mensagem'];
                    }
                }
            }

            // Regras Condicionais por Faixa de Ca ---
            if (isset($dataSample['Ca 2+'])) {
                $valueCa = $dataSample['Ca 2+']['valor'];

                foreach ($rulesbyRangeCa as $condicionalRule) {
                    $range = $condicionalRule['ca_faixa'];
                    $caMin = $range['min'] ?? -INF;
                    $caMax = $range['max'] ?? INF;

                    // Verifica se o Ca da amostra está na faixa da regra
                    if ($valueCa >= $caMin && $valueCa < $caMax) {
                        // Se estiver, aplica as sub-regras para Alumínio (e pode ser aplicada a outro sub-elemento)
                        foreach ($condicionalRule['regras'] as $chave => $subRegra) {
                            if (isset($dataSample[$chave])) {
                                $valueEl = $dataSample[$chave]['valor'];
                                $subMin = $subRegra['min'] ?? -INF;
                                $subMax = $subRegra['max'] ?? INF;

                                if ($valueEl < $subMin || $valueEl > $subMax) {
                                    $dataSample[$chave]['diagnostico'] = "Irregular para a faixa de Ca ($valueCa)";
                                }
                            }
                        }
                    }
                }
            }

            return $json;
        }
    }
}
