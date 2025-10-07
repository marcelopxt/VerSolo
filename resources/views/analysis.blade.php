@extends('layouts.app')

@section('title', 'VerSolo | Início')

@section('content')
    <div class="container mt-4">
        @if (!empty($filename) && !empty($analise))
            <h2 class="mb-4">Arquivo: {{ $filename }}</h2>

            @if (isset($analise['erro']))
                <div class="alert alert-danger">
                    <strong>Ocorreu um erro na extração dos dados:</strong> {{ $analise['erro'] }}
                </div>
            @else
                @foreach ($analise as $protocolo => $dadosAmostra)
                    @php
                        $protocoloComErro = false;
                        foreach ($dadosAmostra as $resultado) {
                            if (isset($resultado['diagnostico'])) {
                                if (
                                    str_contains($resultado['diagnostico'], 'inválido') ||
                                    str_contains($resultado['diagnostico'], 'Abaixo') ||
                                    str_contains($resultado['diagnostico'], 'Acima') ||
                                    str_contains($resultado['diagnostico'], 'Irregular')
                                ) {
                                    $protocoloComErro = true;
                                    break; 
                                }
                            }
                        }
                    @endphp

                    <div class="d-flex align-items-center mt-5">
                        <h4 class="mb-0">Protocolo: {{ $protocolo }}</h4>
                        @if ($protocoloComErro)
                            <span class="badge bg-danger ml-3">Contém Irregularidades</span>
                        @else
                            <span class="badge bg-success ml-3">Dentro dos Padrões</span>
                        @endif
                    </div>

                    <table class="table table-hover table-sm mt-2 table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Análise</th>
                                <th class="text-center">Valor</th>
                                <th>Unidade</th>
                                <th>Diagnóstico</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dadosAmostra as $chave => $resultado)
                                @php
                                    $classeDiagnostico = 'text-body'; 
                                    if (isset($resultado['diagnostico'])) {
                                        if (
                                            str_contains($resultado['diagnostico'], 'inválido') ||
                                            str_contains($resultado['diagnostico'], 'Abaixo') ||
                                            str_contains($resultado['diagnostico'], 'Acima')
                                        ) {
                                            $classeDiagnostico = 'text-danger font-weight-bold'; 
                                        } elseif (str_contains($resultado['diagnostico'], 'Dentro do ideal')) {
                                            $classeDiagnostico = 'text-success';
                                        }
                                    }
                                @endphp

                                <tr class="{{ $classeDiagnostico }}">
                                    <td>{{ $chave }}</td>
                                    <td class="text-center">{{ $resultado['valor'] ?? 'N/A' }}</td>
                                    <td>{{ $resultado['unidade'] ?? '' }}</td>
                                    <td>{{ $resultado['diagnostico'] ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            @endif
        @else
            <div class="alert alert-info mt-4">
                Nenhum dado de análise para exibir. Verifique o arquivo enviado.
            </div>
        @endif
    </div>
    <form action="/report" method="post" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="" class="form-label">Choose file</label>
            <input type="file" class="form-control" name="file" id="input-file" />
            <br>
            <button type="submit" class="btn btn-outline-dark">Enviar</button>
        </div>
    </form>
@endsection
