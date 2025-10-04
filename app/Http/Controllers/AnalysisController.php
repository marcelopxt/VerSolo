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
            'file' => 'required|mimes:pdf'
        ]);

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $rawText = [];

        try {
            $pdfController = new PdfController();
            $rawText = $pdfController->readPdf($request);

        } catch (\Exception $e) {
            dd($e);
        }

        $extractedData = AnalysisController::extractRegex($rawText);
        $dataDisgnosis = AnalysisController::reportInterpretation($extractedData);

        return view('analysis', [
            'filename' => $filename,
            'analise' => $dataDisgnosis
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

        $hasError = false;
        $generalRules = config('analise_parametros._regras_gerais');
        $intervalRules = config('analise_parametros.intervalos');
        $phRelationshipV = config('analise_parametros._relacoes.ph_por_v');

        foreach ($json as $protocol => &$dataSample) {

            // Regras Gerais
            foreach ($dataSample as $key => &$result) {
                if (!isset($result['valor']))
                    continue;

                $value = $result['valor'];

                if ($generalRules['nao_negativo'] && $value < 0) {
                    $result['diagnostico'] = 'Valor inválido (negativo)';
                    $hasError = true;
                    continue;
                }

                if ($generalRules['nao_zero'] && $value == 0) {
                    $result['diagnostico'] = 'Valor inválido (zero)';
                    $hasError = true;
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
                            $diagnosisPh = "Abaixo do ideal para V=$valueV (faixa ideal: {$range['ph_min']}-{$range['ph_max']}";
                            $hasError = true;
                        } elseif ($valuePh > $range['ph_max']) {
                            $diagnosisPh = "Acima do ideal para V=$valueV (faixa ideal: {$range['ph_min']}-{$range['ph_max']}";
                            $hasError = true;
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
                        $hasError = true;
                    } elseif (isset($param['max']) && $value > $param['max']) {
                        $result['diagnostico'] = "Acima do ideal (mínimo: {$param['max']})";
                        $hasError = true;
                    } else {
                        $result['diagnostico'] = "Dentro do ideal";
                    }
                }
            }

            return $json;
        }
    }
}
